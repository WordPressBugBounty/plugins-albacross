<?php
/**
 * Albacross admin settings screen.
 *
 * Drop-in replacement for admin.php. Self-contained: it defines its own
 * constants and helpers when they are not already present, and registers its
 * own hooks, so it works both alongside the rest of the refreshed plugin and
 * on the currently released code.
 *
 * BRANDING: paste the two SVGs into ALBACROSS_MENU_ICON_SVG and
 * ALBACROSS_LOGO_SVG below, or drop the files into assets/. Both are
 * optional — see the notes on each.
 *
 * @package Albacross
 */

defined( 'ABSPATH' ) || exit;

/*
 * ---------------------------------------------------------------------------
 * Branding
 * ---------------------------------------------------------------------------
 *
 * Admin menu icon. Paste the Albacross mark here as a single line of SVG, or
 * leave empty and ship assets/albacross-icon.svg instead.
 *
 * Requirements:
 *   - square viewBox, ideally "0 0 20 20"
 *   - ONE flat colour, fill="#a7aaad". WordPress renders this inside an <img>
 *     and only varies its opacity, so it cannot pick up the admin colour
 *     scheme. A full-colour or dark mark will look like a smudge against the
 *     dark menu bar.
 *   - mark only, no wordmark — it renders at 20x20
 *   - no <style> blocks, no external references
 *
 * Falls back to a WordPress Dashicon when neither is present.
 */
if ( ! defined( 'ALBACROSS_MENU_ICON_SVG' ) ) {
	define( 'ALBACROSS_MENU_ICON_SVG', '' );
}

/*
 * Logo shown at the top of the settings screen. Full horizontal lock-up,
 * full brand colour. Renders at a max width of 168px. Falls back to
 * assets/albacross-logo.svg, then to a text wordmark.
 */
if ( ! defined( 'ALBACROSS_LOGO_SVG' ) ) {
	define( 'ALBACROSS_LOGO_SVG', '' );
}

/*
 * ---------------------------------------------------------------------------
 * Constants — only defined if the main plugin file has not already done so
 * ---------------------------------------------------------------------------
 */

if ( ! defined( 'ALBACROSS_OPTION_CLIENT_ID' ) ) {
	define( 'ALBACROSS_OPTION_CLIENT_ID', 'albacross_client_id' );
}

if ( ! defined( 'ALBACROSS_MENU_SLUG' ) ) {
	define( 'ALBACROSS_MENU_SLUG', 'albacross' );
}

if ( ! defined( 'ALBACROSS_SETTINGS_GROUP' ) ) {
	define( 'ALBACROSS_SETTINGS_GROUP', 'albacross-group-settings' );
}

/**
 * Deep link to the identified companies view in the Albacross app.
 */
if ( ! defined( 'ALBACROSS_APP_COMPANIES_URL' ) ) {
	define( 'ALBACROSS_APP_COMPANIES_URL', 'https://app.albacross.com/companies/all' );
}

/*
 * ---------------------------------------------------------------------------
 * Shared helpers — guarded so they don't collide with insert-code.php
 * ---------------------------------------------------------------------------
 */

if ( ! function_exists( 'albacross_sanitize_client_id' ) ) {
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
}

if ( ! function_exists( 'albacross_get_client_id' ) ) {
	/**
	 * Return the configured Albacross Client ID.
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
}

/*
 * ---------------------------------------------------------------------------
 * Menu and settings registration
 * ---------------------------------------------------------------------------
 */

/**
 * Register the top-level Albacross menu and its submenu items.
 *
 * The menu slug is a plain string. Earlier releases passed __FILE__, which
 * produced an unstable slug derived from the server path.
 *
 * @return void
 */
function albacross_admin_create_menu() {
	add_menu_page(
		__( 'Albacross Settings', 'albacross' ),
		__( 'Albacross', 'albacross' ),
		'manage_options',
		ALBACROSS_MENU_SLUG,
		'albacross_settings_page',
		albacross_get_menu_icon(),
		80
	);

	/*
	 * Adding any submenu makes WordPress duplicate the parent as the first
	 * entry, labelled with the parent's menu title. Registering it explicitly
	 * relabels it to something more useful than a second "Albacross".
	 */
	add_submenu_page(
		ALBACROSS_MENU_SLUG,
		__( 'Albacross Settings', 'albacross' ),
		__( 'Settings', 'albacross' ),
		'manage_options',
		ALBACROSS_MENU_SLUG,
		'albacross_settings_page'
	);

	// Only worth showing once there is data to look at.
	if ( '' === albacross_get_client_id() ) {
		return;
	}

	/*
	 * Passing a full URL as the menu slug makes WordPress render the item as
	 * a direct link rather than an admin.php?page= route. The new tab is
	 * handled separately, in albacross_admin_menu_script().
	 */
	add_submenu_page(
		ALBACROSS_MENU_SLUG,
		__( 'Website Visitors', 'albacross' ),
		__( 'Website Visitors', 'albacross' ),
		'manage_options',
		ALBACROSS_APP_COMPANIES_URL
	);
}

/**
 * Register the Client ID setting.
 *
 * @return void
 */
function albacross_register_settings() {
	register_setting(
		ALBACROSS_SETTINGS_GROUP,
		ALBACROSS_OPTION_CLIENT_ID,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'albacross_sanitize_client_id',
			'default'           => '',
			'show_in_rest'      => false,
		)
	);
}

/**
 * Add a Settings link to the plugin row on the Plugins screen.
 *
 * @param array $links Existing action links.
 * @return array
 */
function albacross_plugin_action_links( $links ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return $links;
	}

	array_unshift(
		$links,
		sprintf(
			'<a href="%s">%s</a>',
			esc_url( albacross_get_settings_url() ),
			esc_html__( 'Settings', 'albacross' )
		)
	);

	return $links;
}

/*
 * ---------------------------------------------------------------------------
 * Branding helpers
 * ---------------------------------------------------------------------------
 */

/**
 * Convert raw SVG markup to a data URI.
 *
 * Used for both the menu icon and the header logo so that neither is ever
 * printed as inline markup, which keeps the escaping story simple.
 *
 * @param string $svg Raw SVG markup.
 * @return string Data URI, or empty string when there is nothing to encode.
 */
function albacross_svg_data_uri( $svg ) {
	$svg = trim( (string) $svg );

	if ( '' === $svg ) {
		return '';
	}

	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Required format for SVG data URIs.
	return 'data:image/svg+xml;base64,' . base64_encode( $svg );
}

/**
 * Return brand SVG markup, preferring the inline constant over a bundled file.
 *
 * @param string $inline        Inline SVG markup, may be empty.
 * @param string $relative_path Path to a bundled SVG, relative to this file.
 * @return string Raw SVG markup, or empty string when neither is available.
 */
function albacross_get_brand_svg( $inline, $relative_path ) {
	$inline = trim( (string) $inline );
	if ( '' !== $inline ) {
		return $inline;
	}
	$path = plugin_dir_path( __FILE__ ) . $relative_path;
	if ( ! is_readable( $path ) ) {
		return '';
	}
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$svg = file_get_contents( $path );
	return is_string( $svg ) ? trim( $svg ) : '';
}

/**
 * Return the admin menu icon, falling back to a Dashicon.
 *
 * @return string
 */
function albacross_get_menu_icon() {
	static $icon = null;

	if ( null !== $icon ) {
		return $icon;
	}

	$data_uri = albacross_svg_data_uri( albacross_get_brand_svg( ALBACROSS_MENU_ICON_SVG, 'assets/albacross-icon.svg' ) );
	$icon     = '' !== $data_uri ? $data_uri : 'dashicons-groups';

	return $icon;
}

/**
 * URL of the plugin settings screen.
 *
 * @return string
 */
function albacross_get_settings_url() {
	return admin_url( 'admin.php?page=' . ALBACROSS_MENU_SLUG );
}

/**
 * Whether the current admin screen is the Albacross settings screen.
 *
 * @return bool
 */
function albacross_is_settings_screen() {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	$screen = get_current_screen();

	return $screen && false !== strpos( (string) $screen->id, ALBACROSS_MENU_SLUG );
}

/*
 * ---------------------------------------------------------------------------
 * Notices and styles
 * ---------------------------------------------------------------------------
 */

/**
 * Show a setup notice while no Client ID has been saved.
 *
 * @return void
 */
function albacross_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( '' !== albacross_get_client_id() || albacross_is_settings_screen() ) {
		return;
	}

	printf(
		'<div class="notice notice-warning is-dismissible"><p>%s <a href="%s">%s</a></p></div>',
		esc_html__( 'Albacross is not identifying visitors yet – you have not added your Client ID.', 'albacross' ),
		esc_url( albacross_get_settings_url() ),
		esc_html__( 'Add it now', 'albacross' )
	);
}

/**
 * Enqueue the small amount of CSS used by the settings screen.
 *
 * @return void
 */
function albacross_admin_styles() {
	if ( ! albacross_is_settings_screen() ) {
		return;
	}

	$css = '
	.albacross-page { max-width: 640px; }
	.albacross-brand { margin: 24px 0 16px; }
	.albacross-brand img { max-width: 168px; height: auto; display: block; }
	.albacross-wordmark { font-size: 20px; font-weight: 600; letter-spacing: -0.3px; color: #1d2327; }
	.albacross-page h1 { font-size: 23px; font-weight: 600; margin: 0 0 4px; padding: 0; line-height: 1.3; }
	.albacross-lede { font-size: 14px; color: #50575e; margin: 0 0 20px; max-width: 34em; }
	.albacross-status { display: inline-flex; align-items: center; gap: 6px; font-size: 13px;
		font-weight: 600; padding: 4px 10px; border-radius: 12px; margin: 0 0 20px; }
	.albacross-status--on { background: #edfaef; color: #007017; }
	.albacross-status--off { background: #fcf3e6; color: #8a5700; }
	.albacross-status .dashicons { font-size: 16px; width: 16px; height: 16px; }
	.albacross-steps { margin: 0 0 20px 20px; padding: 0; font-size: 14px; }
	.albacross-steps li { margin-bottom: 6px; }
	.albacross-page .form-table th { width: 170px; padding-left: 0; }
	.albacross-verify { margin-top: 24px; font-size: 13px; }
	.albacross-verify summary { cursor: pointer; font-weight: 600; }
	.albacross-verify ol { margin: 10px 0 0 20px; }
	.albacross-verify code { font-size: 12px; }
	.albacross-footer { margin-top: 28px; padding-top: 16px; border-top: 1px solid #dcdcde;
		font-size: 13px; color: #50575e; }
	.albacross-footer p { margin: 4px 0; }
	';

	wp_add_inline_style( 'common', $css );
}

/**
 * Make the external menu item open in a new tab.
 *
 * The admin menu API has no target parameter, so the attribute is applied to
 * the rendered anchor instead. Runs on every admin screen, since that is where
 * the menu is shown, but does nothing when the item is not present.
 *
 * @return void
 */
function albacross_admin_menu_script() {
	if ( '' === albacross_get_client_id() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$js = sprintf(
		'document.addEventListener("DOMContentLoaded",function(){
			var url=%s;
			var link=document.querySelector(\'#adminmenu a[href="\'+url+\'"]\');
			if(!link){return;}
			link.target="_blank";
			link.rel="noopener noreferrer";
			link.insertAdjacentHTML("beforeend"," <span class=\\"dashicons dashicons-external\\" style=\\"font-size:13px;width:13px;height:13px;vertical-align:-2px;opacity:.7\\" aria-hidden=\\"true\\"></span>");
		});',
		wp_json_encode( ALBACROSS_APP_COMPANIES_URL )
	);

	wp_add_inline_script( 'common', $js );
}

/*
 * ---------------------------------------------------------------------------
 * Settings screen
 * ---------------------------------------------------------------------------
 */

/**
 * Render the settings screen.
 *
 * @return void
 */
function albacross_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to manage Albacross settings.', 'albacross' ) );
	}

	$client_id   = get_option( ALBACROSS_OPTION_CLIENT_ID, '' );
	$is_active   = '' !== albacross_get_client_id();
	$logo_uri    = albacross_svg_data_uri( albacross_get_brand_svg( ALBACROSS_LOGO_SVG, 'assets/albacross-logo.svg' ) );
	$account_url = 'https://app.albacross.com/account/tracking?tab=wp';
	?>
	<div class="wrap albacross-page">

		<div class="albacross-brand">
			<?php if ( '' !== $logo_uri ) : ?>
				<img src="<?php echo esc_attr( $logo_uri ); ?>" alt="<?php esc_attr_e( 'Albacross', 'albacross' ); ?>">
			<?php else : ?>
				<span class="albacross-wordmark"><?php esc_html_e( 'Albacross', 'albacross' ); ?></span>
			<?php endif; ?>
		</div>

		<h1><?php esc_html_e( 'Turn anonymous traffic into pipeline', 'albacross' ); ?></h1>

		<p class="albacross-lede">
			<?php esc_html_e( 'Albacross identifies the businesses visiting your website, shows you what they are interested in, and sends them to your CRM so sales can act while the interest is fresh.', 'albacross' ); ?>
		</p>

		<?php if ( $is_active ) : ?>
			<p class="albacross-status albacross-status--on">
				<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
				<?php esc_html_e( 'Tracking is active on this site', 'albacross' ); ?>
			</p>
		<?php else : ?>
			<p class="albacross-status albacross-status--off">
				<span class="dashicons dashicons-warning" aria-hidden="true"></span>
				<?php esc_html_e( 'Not tracking yet — add your Client ID below', 'albacross' ); ?>
			</p>
		<?php endif; ?>

		<ol class="albacross-steps">
			<li>
				<?php
				printf(
					/* translators: %s: link to the tracking setup page in the Albacross app. */
					esc_html__( 'Open %s in Albacross to find your unique Client ID.', 'albacross' ),
					'<a href="' . esc_url( $account_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Company Details', 'albacross' ) . '</a>'
				);
				?>
			</li>
			<li><?php esc_html_e( 'Paste it below and save to start identifying the companies visiting your website.', 'albacross' ); ?></li>
		</ol>

		<form method="post" action="options.php">
			<?php
			settings_fields( ALBACROSS_SETTINGS_GROUP );
			do_settings_sections( ALBACROSS_SETTINGS_GROUP );
			?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="albacross_client_id"><?php esc_html_e( 'Albacross Client ID', 'albacross' ); ?></label>
					</th>
					<td>
						<input
							type="text"
							id="albacross_client_id"
							name="<?php echo esc_attr( ALBACROSS_OPTION_CLIENT_ID ); ?>"
							class="regular-text"
							value="<?php echo esc_attr( $client_id ); ?>"
							placeholder="<?php esc_attr_e( 'Your Client ID', 'albacross' ); ?>"
							autocomplete="off"
							spellcheck="false"
						>
						<p class="description">
							<?php esc_html_e( '(Leave blank to disable tracking.)', 'albacross' ); ?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Save changes', 'albacross' ) ); ?>
		</form>

		<?php if ( $is_active ) : ?>
			<details class="albacross-verify">
				<summary><?php esc_html_e( 'How do I check that it is working?', 'albacross' ); ?></summary>
				<ol>
					<li><?php esc_html_e( 'Open your site in a private window, and accept cookies if you use a consent banner.', 'albacross' ); ?></li>
					<li>
						<?php
						printf(
							/* translators: 1: script file name, 2: JavaScript variable name. */
							esc_html__( 'In your browser developer tools, check the Network tab for %1$s loading with status 200, then type %2$s in the Console — it should return your Client ID.', 'albacross' ),
							'<code>track.js</code>',
							'<code>window._nQc</code>'
						);
						?>
					</li>
					<li><?php esc_html_e( 'Identified companies usually appear in your Albacross dashboard within 10 minutes.', 'albacross' ); ?></li>
				</ol>
			</details>
		<?php endif; ?>

		<div class="albacross-footer">
			<p>
				<?php
				printf(
					/* translators: %s: link to the Albacross help centre. */
					esc_html__( 'Facing issues? %s', 'albacross' ),
					'<a href="https://help.albacross.com/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Visit our help center', 'albacross' ) . '</a>'
				);
				?>
			</p>
			<?php if ( ! $is_active ) : ?>
				<p>
					<?php
					printf(
						/* translators: %s: link to start an Albacross trial. */
						esc_html__( 'No Albacross account yet? %s', 'albacross' ),
						'<a href="https://www.albacross.com/get-started" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Start a free 14-day trial', 'albacross' ) . '</a>'
					);
					?>
				</p>
			<?php endif; ?>
		</div>

	</div>
	<?php
}

/*
 * ---------------------------------------------------------------------------
 * Hook registration
 * ---------------------------------------------------------------------------
 *
 * Registered here so this file works on its own. add_action() is keyed on the
 * callback name, so registering the same callback again from the main plugin
 * file is a no-op rather than a duplicate.
 */

if ( is_admin() ) {
	add_action( 'admin_menu', 'albacross_admin_create_menu' );
	add_action( 'admin_init', 'albacross_register_settings' );
	add_action( 'admin_notices', 'albacross_admin_notice' );
	add_action( 'admin_enqueue_scripts', 'albacross_admin_styles' );
	add_action( 'admin_enqueue_scripts', 'albacross_admin_menu_script' );

	add_filter(
		'plugin_action_links_' . plugin_basename( dirname( __FILE__ ) . '/plugin.php' ),
		'albacross_plugin_action_links'
	);
}