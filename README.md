# if-elseif-else Shortcode
* Contributors: n8kowald
* Donate link: https://www.paypal.me/nkowald
* Tags: shortcode
* Requires at least: 5.6
* Tested up to: 7.0
* Requires PHP: 8.0
* Stable tag: 0.3.0
* License: GPLv3 or later
* License URI: https://www.gnu.org/licenses/gpl-3.0.html

Allows you to use if-elseif-else conditions in your editor content via an [if] shortcode.

## Description
This plugin adds a new `[if]` shortcode that allows you to use if-elseif-else shortcodes in your editor content.

```
[if is_super_admin]
    Hello Admin
[elseif is_user_logged_in]
    Hello registered user
[else]
    Hello, please log in
[/if]
```

The shortcode tag defaults to `[if]`. If this clashes with another plugin you can change it with the `if_elseif_else_shortcode_tag` filter.

The following callables are allowed by default.
```
comments_open
get_field
is_404
is_admin
is_archive
is_author
is_category
is_day
is_feed
is_front_page
is_home
is_month
is_page
is_search
is_single
is_singular
is_sticky
is_super_admin
is_tag
is_tax
is_time
is_user_logged_in
is_year
pings_open
```

Note: `get_field` is provided by the Advanced Custom Fields (ACF) plugin and is only callable when ACF is active.

To allow other callables you can use the `if_elseif_else_shortcode_allowed_callables` filter.

```
<?php
add_filter( 'if_elseif_else_shortcode_allowed_callables', function( $whitelist ) {
	$whitelist[] = 'your_callable_here';

	return $whitelist;
});
```

## Installation

1. Clone the plugin to the `/wp-content/plugins/` directory: `git clone https://github.com/n8kowald/if-elseif-else-shortcode
`
2. Activate the plugin through the 'Plugins' menu in WordPress, or use WP-CLI: `wp plugin activate if-elseif-else-shortcode`

## Frequently Asked Questions

### What callables are supported?

String callables are supported:
* Function names: is_user_logged_in
* Static class method calls (>=PHP 5.2.3): MyClass::myCallbackMethod

```
[if is_user_logged_in]
    Hello user
[elseif UserAccessControl::is_member_logged_in]
    Hello member
[else]
    Hello, please log in
[/if]
```

Note: in the above example, you would need to add `UserAccessControl::is_member_logged_in` to the allowed callables whitelist.
You can do this using the `if_elseif_else_shortcode_allowed_callables` filter.

### Can I pass parameters to callables?
Yes.

```
[if is_singular books]
	Related books here.
[/if]
```

If your callable accepts multiple parameters you can pass them in as a space separated string.
```
function is_garfield( $animal, $colour ) {
	return $animal === 'cat' && $colour === 'orange';
}

[if is_garfield cat orange]
	Yes, this is garfield.
[/if]
```

## Changelog

### 0.3.0
* Require PHP 8.0 or later (the plugin now uses `str_contains()`).
* Update WordPress "Tested up to" version to 7.0.
* Add ACF `get_field` to the default allowed callables list (only callable when ACF is active).
* Make the shortcode tag filterable via the `if_elseif_else_shortcode_tag` filter (default `if`).
* Add a direct-file-access (ABSPATH) guard.
* PHP 8 compatibility: return early on null content for self-closing tags, and pass attributes through `array_values()` so keyed attributes are not treated as named arguments.
* Harden `[else]` / `[elseif]` parsing so the matched branch indexes stay aligned, guarded with `isset()`.
* Use strict comparison when validating allowed callables.
* Escape error strings with `esc_html__()` and add typed docblocks.
* Add Composer dev dependencies (PHPUnit 9) for running the test suite. Note: the test suite requires PHP 8.1.
* Replace the Grunt-based i18n tooling with WP-CLI (`wp i18n make-pot`); remove the `grunt-wp-i18n` and `grunt-wp-readme-to-markdown` dev dependencies.
* Format plugin code to follow WordPress coding standard conventions.

### 0.2.0
* Update WordPress version tested up to: 5.9.1.
* Add composer --dev dependencies for running PHPUnit 9.
* Add Advanced Custom Field's get_field function to the allowed callables list.
* Format plugin code to follow WordPress coding standard conventions.

### 0.1.0
* Committed the plugin.

## Upgrade Notice

## Testing
If you want to simplify the `if_elseif_else_statement` function or fix a bug, a WordPress test class exists for you to test your refactored code.

Note: running the test suite requires PHP 8.1 or later. The dev dependencies (PHPUnit 9 via `doctrine/instantiator` 2.0) require PHP 8.1, even though the plugin itself only requires PHP 8.0 at runtime.

### Install test framework and database
`./bin/install-wp-tests.sh {db-name} {db-user} {db-pass} [db-host] [wp-version] [skip-database-creation]`

#### Install PHPUnit and PHPUnit Polyfills with composer
Run this from the plugin directory: `composer install`

#### Run PHPUnit
vendor/bin/phpunit

## Generating translations
Translations are managed with [WP-CLI](https://make.wordpress.org/cli/handbook/), which replaces the old Grunt / `grunt-wp-i18n` workflow. Run this from the plugin directory to (re)build the translation template:

`wp i18n make-pot . languages/if-elseif-else-shortcode.pot --exclude=bin,tests,vendor,node_modules`
