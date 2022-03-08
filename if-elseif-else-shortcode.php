<?php
/**
 * Plugin Name:     if-elseif-else Shortcode
 * Plugin URI:      https://www.nathankowald.com/blog/2019/06/wordpress-shortcode-if-elseif-else-statements
 * Description:     Use if-elseif-else conditions in your editor with a shortcode.
 * Author:          Nathan Kowald
 * Author URI:      https://www.nathankowald.com
 * Text Domain:     if-elseif-else-shortcode
 * Domain Path:     /languages
 * Version:         0.2.0
 *
 * @package         If_Elseif_Else_Shortcode
 */

/**
 * Inspired by: https://level7systems.co.uk/wordpress-shortcodes-else-statement
 */

// Register shortcode.
add_shortcode( 'if', 'iee_if_elseif_else_statement' );

/**
 * @param $atts
 * @param $content
 *
 * @return string
 */
function iee_if_elseif_else_statement( $atts, $content ) {
	if ( empty( $atts ) ) {
		return '';
	}

	$pattern_else      = '[else]';
	$pattern_elseif    = "/\[elseif\s([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*)\s?([^\[\]]*)?\]/";
	$callable          = array_shift( $atts );
	$content_if        = $content;
	$content_else      = '';
	$content_elseif    = '';
	$elseif            = false;
	$elseif_true_index = 0;

	if ( ! iee_is_valid_callable( $callable ) ) {
		return __( 'If shortcode error: [if] argument must be callable', 'if-elseif-else-shortcode' );
	}

	$if = (boolean) call_user_func_array( $callable, $atts );

	// If condition is false: check elseif condition(s).
	if ( ! $if &&
	     preg_match_all(
		     $pattern_elseif,
		     $content,
		     $matches,
		     PREG_SET_ORDER
	     ) > 0
	) {
		foreach ( $matches as $match ) {
			$callable = array_values( array_slice( $match, - 2 ) )[0];
			if ( ! iee_is_valid_callable( $callable ) ) {
				return __( 'If shortcode error: [elseif] argument must be callable', 'if-elseif-else-shortcode' );
			}
			$atts            = array_values( array_slice( $match, - 1 ) )[0];
			$callable_params = empty( $atts ) ? [] : explode( ' ', $atts );
			$elseif          = (boolean) call_user_func_array( $callable, $callable_params );
			if ( $elseif ) {
				// elseif condition is true: no need to check further.
				break;
			}
			$elseif_true_index ++;
		}
	}
	if ( strpos( $content, $pattern_else ) !== false ) {
		list( $content_if, $content_else ) = explode( $pattern_else, $content, 2 );
	}
	$contents = preg_split( $pattern_elseif, $content_if );
	if ( $contents !== false ) {
		$content_if = array_shift( $contents );
	}
	if ( $elseif ) {
		$content_elseif = $contents[ $elseif_true_index ];
	}

	return do_shortcode( $if ? $content_if : ( $elseif ? $content_elseif : $content_else ) );
}

/**
 * Checks to see if the arguments passed to [if] and [elseif] are callable.
 *
 * @param $callable
 *
 * @return bool
 */
function iee_is_valid_callable( $callable ) {
	return is_callable( $callable ) && in_array( $callable, iee_get_allowed_callables() );
}

/**
 * List of allowed, filterable callables.
 *
 * @return mixed|void
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
