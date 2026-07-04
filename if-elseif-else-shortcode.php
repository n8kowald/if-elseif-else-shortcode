<?php
/**
 * Plugin Name:     if-elseif-else Shortcode
 * Plugin URI:      https://www.nathankowald.com/blog/2019/06/wordpress-shortcode-if-elseif-else-statements
 * Description:     Use if-elseif-else conditions in your editor with a shortcode.
 * Author:          Nathan Kowald
 * Author URI:      https://www.nathankowald.com
 * Text Domain:     if-elseif-else-shortcode
 * Domain Path:     /languages
 * Version:         0.3.0
 * Requires at least: 4.5
 * Requires PHP:    8.0
 *
 * @package         If_Elseif_Else_Shortcode
 */

/**
 * Inspired by: https://level7systems.co.uk/wordpress-shortcodes-else-statement
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Register shortcode. The tag is filterable to avoid clashes with other plugins.
add_shortcode( apply_filters( 'if_elseif_else_shortcode_tag', 'if' ), 'iee_if_elseif_else_statement' );

/**
 * Renders the matching if / elseif / else branch of the shortcode content.
 *
 * @param array|string $atts    Shortcode attributes. First value is the callable.
 * @param string|null  $content Enclosed shortcode content.
 *
 * @return string
 */
function iee_if_elseif_else_statement( $atts = array(), $content = null ) {
	if ( empty( $atts ) || null === $content ) {
		return '';
	}

	$pattern_else      = '[else]';
	$pattern_elseif    = "/\[elseif\s([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*)\s?([^\[\]]*)?\]/";
	$callable          = array_shift( $atts );
	$content_else      = '';
	$content_elseif    = '';
	$elseif            = false;
	$elseif_true_index = 0;

	if ( ! iee_is_valid_callable( $callable ) ) {
		return esc_html__( 'If shortcode error: [if] argument must be callable', 'if-elseif-else-shortcode' );
	}

	// array_values() drops any string keys so named attributes are not passed
	// as PHP 8 named arguments (which would trigger a fatal error).
	$if = (bool) call_user_func_array( $callable, array_values( $atts ) );

	// Separate the [else] branch up-front so all if/elseif parsing operates on
	// the same string and index counts stay aligned.
	$content_conditions = $content;
	if ( str_contains( $content, $pattern_else ) ) {
		list( $content_conditions, $content_else ) = explode( $pattern_else, $content, 2 );
	}

	// If condition is false: check elseif condition(s).
	if ( ! $if &&
		preg_match_all(
			$pattern_elseif,
			$content_conditions,
			$matches,
			PREG_SET_ORDER
		) > 0
	) {
		foreach ( $matches as $match ) {
			$callable = $match[1];
			if ( ! iee_is_valid_callable( $callable ) ) {
				return esc_html__( 'If shortcode error: [elseif] argument must be callable', 'if-elseif-else-shortcode' );
			}
			$params          = isset( $match[2] ) ? trim( $match[2] ) : '';
			$callable_params = ( '' === $params ) ? array() : preg_split( '/\s+/', $params );
			$elseif          = (bool) call_user_func_array( $callable, $callable_params );
			if ( $elseif ) {
				// elseif condition is true: no need to check further.
				break;
			}
			$elseif_true_index ++;
		}
	}

	$contents   = preg_split( $pattern_elseif, $content_conditions );
	$content_if = ( false !== $contents ) ? array_shift( $contents ) : $content_conditions;
	if ( $elseif && isset( $contents[ $elseif_true_index ] ) ) {
		$content_elseif = $contents[ $elseif_true_index ];
	}

	return do_shortcode( $if ? $content_if : ( $elseif ? $content_elseif : $content_else ) );
}

/**
 * Checks to see if the arguments passed to [if] and [elseif] are callable.
 *
 * @param string $callable Callable name from the shortcode.
 *
 * @return bool
 */
function iee_is_valid_callable( $callable ) {
	return is_callable( $callable ) && in_array( $callable, iee_get_allowed_callables(), true );
}

/**
 * List of allowed, filterable callables.
 *
 * @return array
 */
function iee_get_allowed_callables() {
	$whitelist = [
		'comments_open',
		'get_field',
		'is_404',
		'is_admin',
		'is_archive',
		'is_author',
		'is_category',
		'is_day',
		'is_feed',
		'is_front_page',
		'is_home',
		'is_month',
		'is_page',
		'is_search',
		'is_single',
		'is_singular',
		'is_sticky',
		'is_super_admin',
		'is_tag',
		'is_tax',
		'is_time',
		'is_user_logged_in',
		'is_year',
		'pings_open',
	];

	return apply_filters( 'if_elseif_else_shortcode_allowed_callables', $whitelist );
}
