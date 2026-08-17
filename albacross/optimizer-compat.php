<?php
/**
 * Compatibility with JavaScript optimization plugins and CDNs.
 *
 * @package Albacross
 */

defined( 'ABSPATH' ) || exit;

/**
 * Strings that uniquely identify the Albacross scripts.
 *
 * @return string[]
 */
function albacross_optimizer_keywords() {
	return array(
		'albacross-tracker',
		'serve.albacross.com/track.js',
		'window._nQc',
	);
}

/**
 * Attributes recognized by common JavaScript optimizers.
 *
 * @return array<string, string>
 */
function albacross_optimizer_attributes() {
	return array(
		// Cloudflare Rocket Loader.
		'data-cfasync'            => 'false',

		// LiteSpeed Cache.
		'data-no-optimize'        => '1',
		'data-no-defer'           => '1',

		// Autoptimize.
		'data-noptimize'          => '1',

		// WP Rocket.
		'data-nowprocket'         => '1',

		// Jetpack Boost.
		'data-jetpack-boost'      => 'ignore',

		// Apache mod_pagespeed.
		'data-pagespeed-no-defer' => '1',
	);
}

/**
 * Add an attribute immediately after the opening script element.
 *
 * Cloudflare requires data-cfasync to appear before src.
 *
 * @param string $tag   Script HTML.
 * @param string $name  Attribute name.
 * @param string $value Attribute value.
 * @return string
 */
function albacross_add_script_tag_attribute( $tag, $name, $value ) {
	if (
		preg_match(
			'/\s' . preg_quote( $name, '/' ) . '(?:\s|=|>)/i',
			$tag
		)
	) {
		return $tag;
	}

	$updated = preg_replace(
		'/<script\b/i',
		'<script ' . $name . '="' . esc_attr( $value ) . '"',
		$tag,
		1
	);

	return is_string( $updated ) ? $updated : $tag;
}

/**
 * Mark the external Albacross tracker.
 *
 * This runs before albacross_async_script_tag(), which currently uses
 * priority 10.
 *
 * @param string $tag    Script HTML.
 * @param string $handle WordPress script handle.
 * @param string $src    Script URL.
 * @return string
 */
function albacross_optimizer_script_loader_tag( $tag, $handle, $src ) {
	if ( ALBACROSS_SCRIPT_HANDLE !== $handle ) {
		return $tag;
	}

	foreach ( albacross_optimizer_attributes() as $name => $value ) {
		$tag = albacross_add_script_tag_attribute( $tag, $name, $value );
	}

	return $tag;
}

/**
 * Mark only the inline Albacross configuration block.
 *
 * @param array  $attributes Inline script attributes.
 * @param string $data       Inline JavaScript.
 * @return array
 */
function albacross_optimizer_inline_script_attributes( $attributes, $data ) {
	if (
		! is_string( $data ) ||
		false === strpos( $data, 'window._nQc=' )
	) {
		return $attributes;
	}

	$attributes = is_array( $attributes ) ? $attributes : array();

	return array_merge(
		$attributes,
		albacross_optimizer_attributes()
	);
}

/**
 * Append Albacross identifiers to array-based exclusion filters.
 *
 * @param mixed $exclusions Existing exclusions.
 * @return array
 */
function albacross_append_optimizer_exclusions( $exclusions ) {
	$exclusions = is_array( $exclusions ) ? $exclusions : array();

	return array_values(
		array_unique(
			array_merge(
				$exclusions,
				albacross_optimizer_keywords()
			)
		)
	);
}

/**
 * Autoptimize accepts either a comma-separated string or an associative array.
 *
 * For an array, Autoptimize treats the array keys as exclusion patterns.
 *
 * @param mixed $exclusions Existing exclusions.
 * @return string|array
 */
function albacross_autoptimize_js_exclusions( $exclusions ) {
	$keywords = albacross_optimizer_keywords();

	if ( is_array( $exclusions ) ) {
		$normalized = array();

		foreach ( $exclusions as $key => $value ) {
			if ( is_int( $key ) ) {
				$normalized[ (string) $value ] = true;
			} else {
				$normalized[ $key ] = $value;
			}
		}

		foreach ( $keywords as $keyword ) {
			if ( ! array_key_exists( $keyword, $normalized ) ) {
				$normalized[ $keyword ] = true;
			}
		}

		return $normalized;
	}

	$items = array_filter(
		array_map(
			'trim',
			explode( ',', (string) $exclusions )
		)
	);

	return implode(
		',',
		array_unique(
			array_merge( $items, $keywords )
		)
	);
}

/**
 * Add the WordPress handle to handle-based exclusion lists.
 *
 * Used by SiteGround and Jetpack Boost.
 *
 * @param mixed $handles Existing handles.
 * @return array
 */
function albacross_optimizer_handle_exclusions( $handles ) {
	$handles   = is_array( $handles ) ? $handles : array();
	$handles[] = ALBACROSS_SCRIPT_HANDLE;

	return array_values( array_unique( $handles ) );
}

/**
 * SiteGround external-script exclusions.
 *
 * @param mixed $paths Existing paths.
 * @return array
 */
function albacross_siteground_external_exclusions( $paths ) {
	$paths   = is_array( $paths ) ? $paths : array();
	$paths[] = 'serve.albacross.com/track.js';

	return array_values( array_unique( $paths ) );
}

/**
 * SiteGround inline-script exclusions.
 *
 * @param mixed $content Existing inline identifiers.
 * @return array
 */
function albacross_siteground_inline_exclusions( $content ) {
	$content   = is_array( $content ) ? $content : array();
	$content[] = 'window._nQc';

	return array_values( array_unique( $content ) );
}

/**
 * Prevent Hummingbird from minifying or combining the external tracker.
 *
 * @param bool   $optimize Whether to optimize.
 * @param string $handle   WordPress handle.
 * @param string $type     Resource type.
 * @param string $url      Resource URL.
 * @return bool
 */
function albacross_hummingbird_resource_optimization(
	$optimize,
	$handle,
	$type,
	$url = ''
) {
	if ( 'scripts' !== $type ) {
		return $optimize;
	}

	if (
		ALBACROSS_SCRIPT_HANDLE === $handle ||
		false !== strpos(
			(string) $url,
			'serve.albacross.com/track.js'
		)
	) {
		return false;
	}

	return $optimize;
}

/**
 * Determine whether an optimizer-list entry contains Albacross code.
 *
 * W3 Total Cache passes strings. Jetpack Boost passes arrays containing the
 * script HTML and its offset.
 *
 * @param mixed $entry Script-list entry.
 * @return bool
 */
function albacross_optimizer_entry_is_albacross( $entry ) {
	if ( is_array( $entry ) && isset( $entry[0] ) ) {
		$tag = $entry[0];
	} else {
		$tag = $entry;
	}

	if ( ! is_string( $tag ) ) {
		return false;
	}

	foreach ( albacross_optimizer_keywords() as $keyword ) {
		if ( false !== stripos( $tag, $keyword ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Remove Albacross tags from an optimizer's processing list.
 *
 * This does not remove them from the webpage. It leaves their original HTML
 * untouched in the page buffer.
 *
 * @param mixed $script_tags Optimizer processing list.
 * @return mixed
 */
function albacross_exclude_optimizer_script_tags( $script_tags ) {
	if ( ! is_array( $script_tags ) ) {
		return $script_tags;
	}

	return array_values(
		array_filter(
			$script_tags,
			function ( $entry ) {
				return ! albacross_optimizer_entry_is_albacross( $entry );
			}
		)
	);
}

/**
 * Register optimizer compatibility hooks.
 *
 * Unknown filters are harmless when their corresponding plugin is inactive.
 */
function albacross_register_optimizer_compatibility() {
	add_filter(
		'script_loader_tag',
		'albacross_optimizer_script_loader_tag',
		9,
		3
	);

	add_filter(
		'wp_inline_script_attributes',
		'albacross_optimizer_inline_script_attributes',
		10,
		2
	);

	$array_exclusion_filters = array(
		// LiteSpeed Cache.
		'litespeed_optimize_js_excludes',
		'litespeed_optm_js_defer_exc',
		'litespeed_optm_gm_js_exc',

		// WP Rocket.
		'rocket_exclude_js',
		'rocket_exclude_defer_js',
		'rocket_defer_inline_exclusions',
		'rocket_delay_js_exclusions',
		'rocket_excluded_inline_js_content',

		// Hummingbird Delay JavaScript.
		'wphb_delay_js_exclusions',

		// Perfmatters.
		'perfmatters_defer_js_exclusions',
		'perfmatters_delay_js_exclusions',
		'perfmatters_minify_js_exclusions',

		// FlyingPress minification.
		'flying_press_exclude_from_minify:js',
	);

	foreach ( $array_exclusion_filters as $filter_name ) {
		add_filter(
			$filter_name,
			'albacross_append_optimizer_exclusions'
		);
	}

	// Autoptimize.
	add_filter(
		'autoptimize_filter_js_exclude',
		'albacross_autoptimize_js_exclusions'
	);

	// SiteGround Speed Optimizer.
	add_filter(
		'sgo_js_minify_exclude',
		'albacross_optimizer_handle_exclusions'
	);
	add_filter(
		'sgo_javascript_combine_exclude',
		'albacross_optimizer_handle_exclusions'
	);
	add_filter(
		'sgo_js_async_exclude',
		'albacross_optimizer_handle_exclusions'
	);
	add_filter(
		'sgo_javascript_combine_excluded_external_paths',
		'albacross_siteground_external_exclusions'
	);
	add_filter(
		'sgo_javascript_combine_excluded_inline_content',
		'albacross_siteground_inline_exclusions'
	);

	// Jetpack Boost.
	add_filter(
		'jetpack_boost_render_blocking_js_exclude_handles',
		'albacross_optimizer_handle_exclusions'
	);
	add_filter(
		'jetpack_boost_render_blocking_js_exclude_scripts',
		'albacross_exclude_optimizer_script_tags'
	);

	// Hummingbird Asset Optimization.
	add_filter(
		'wphb_minify_resource',
		'albacross_hummingbird_resource_optimization',
		10,
		4
	);
	add_filter(
		'wphb_combine_resource',
		'albacross_hummingbird_resource_optimization',
		10,
		4
	);
	add_filter(
		'wphb_defer_resource',
		'albacross_hummingbird_resource_optimization',
		999,
		4
	);

	// W3 Total Cache.
	add_filter(
		'w3tc_minify_js_script_tags',
		'albacross_exclude_optimizer_script_tags'
	);
}

albacross_register_optimizer_compatibility();
