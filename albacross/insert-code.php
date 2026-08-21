<?php
/**
 * Front-end output of the Albacross tracking script.
 *
 * @package Albacross
 */

defined( 'ABSPATH' ) || exit;

define( 'ALBACROSS_SCRIPT_HANDLE', 'albacross-tracker' );
define( 'ALBACROSS_SCRIPT_URL', 'https://serve.albacross.com/track.js' );

/**
 * Return the configured Albacross Client ID.
 *
 * Filterable so that the ID can be set in code (staging environments,
 * multi-brand setups, environment variables, and so on).
 *
 * @return string
 */
function albacross_get_client_id() {
	$client_id = get_option( ALBACROSS_OPTION_CLIENT_ID, '' );
	$client_id = is_scalar( $client_id ) ? trim( (string) $client_id ) : '';

	/**
	 * Filters the Albacross Client ID used for tracking.
	 *
	 * @param string $client_id The Client ID saved in the plugin settings.
	 */
	$client_id = apply_filters( 'albacross_client_id', $client_id );

	return albacross_sanitize_client_id( $client_id );
}

/**
 * Whether the tracking script should be loaded for the current request.
 *
 * Consent management plugins should hook this filter and return false until
 * the visitor has given consent for marketing / tracking cookies.
 *
 * @return bool
 */
function albacross_should_load() {
	$should_load = '' !== albacross_get_client_id();

	/**
	 * Filters whether the Albacross tracking script is loaded.
	 *
	 * @param bool $should_load True when a Client ID is configured.
	 */
	return (bool) apply_filters( 'albacross_should_load', $should_load );
}

/**
 * Enqueue the tracking script and its inline configuration.
 *
 * @return void
 */
function albacross_enqueue_tracking_script() {
	if ( ! albacross_should_load() ) {
		return;
	}

	$loader_url = add_query_arg(
    array(
			'client_id'      => albacross_get_client_id(),
			'plugin_version' => ALBACROSS_PLUGIN_VERSION,
    ),
    plugins_url( 'assets/js/albacross-loader.js', ALBACROSS_PLUGIN_FILE )
	);

	wp_enqueue_script(
		ALBACROSS_SCRIPT_HANDLE,
		$loader_url,
		array(),
		ALBACROSS_PLUGIN_VERSION,
		true
	);
}

/**
 * Build the inline configuration that must run before track.js.
 *
 * @return string
 */
function albacross_get_inline_config() {
	return sprintf(
		'window._nQc=%s;window._nQs=%s;window._nQsv=%s;',
		wp_json_encode( albacross_get_client_id() ),
		wp_json_encode( 'WordPress-Plugin' ),
		wp_json_encode( ALBACROSS_PLUGIN_VERSION )
	);
}

/**
 * Add the async attribute to the tracking script tag.
 *
 * Done via script_loader_tag rather than the WP 6.3+ strategy argument so the
 * plugin keeps working on older supported versions of WordPress.
 *
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @param string $src    The script source.
 * @return string
 */
function albacross_async_script_tag( $tag, $handle, $src ) {
	if ( ALBACROSS_SCRIPT_HANDLE !== $handle || false !== strpos( $tag, ' async' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' async src=', $tag );
}

/**
 * Sanitize a Client ID.
 *
 * @param mixed $client_id Raw value.
 * @return string
 */
function albacross_sanitize_client_id( $client_id ) {
	if ( ! is_scalar( $client_id ) ) {
		return '';
	}

	$client_id = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $client_id );

	return substr( (string) $client_id, 0, 64 );
}