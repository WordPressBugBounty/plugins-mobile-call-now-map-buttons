<?php
/**
 * Plugin Name: Mobile Call Now & Map Buttons
 * Plugin URI: https://wordpress.org/plugins/mobile-call-now-map-buttons/
 * Description: Adds custom "Call Now" and/or Google "Directions" buttons for mobile visitors.
 * Version: 1.6.2
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: David Sword, VK Lakkineni
 * Author URI: https://roimediaworks.com/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: mobile-call-now-map-buttons
 *
 * @package Mobile_Call_Now_Map_Buttons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MCNMB_VERSION', '1.6.2' );
define( 'MCNMB_OPTION_NAME', 'RPB_options' );

add_action( 'plugins_loaded', array( 'MCNMB_Plugin', 'get_instance' ) );

/**
 * Main plugin class.
 */
class MCNMB_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var MCNMB_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Plugin options.
	 *
	 * @var array<string, string>
	 */
	private $options = array();

	/**
	 * Get singleton instance.
	 *
	 * @return MCNMB_Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->options = $this->get_options();

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_init', array( $this, 'register_options_settings' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_styles' ) );
		add_action( 'wp_footer', array( $this, 'add_buttons' ), 10 );
	}

	/**
	 * Add settings page.
	 */
	public function add_admin_menu() {
		add_options_page(
			esc_html__( 'Settings Page for Mobile Call Now & Map Plugin', 'mobile-call-now-map-buttons' ),
			esc_html__( 'Mobile Call Now & Map Buttons', 'mobile-call-now-map-buttons' ),
			'manage_options',
			'mobile_contact_buttons',
			array( $this, 'options_page' )
		);
	}

	/**
	 * Add admin resources.
	 *
	 * @param string $hook_suffix Current admin hook suffix.
	 */
	public function admin_scripts( $hook_suffix ) {
		if ( 'settings_page_mobile_contact_buttons' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'mcnmb-admin',
			plugins_url( 'rpb-admin.css', __FILE__ ),
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . 'rpb-admin.css' )
		);

		wp_enqueue_script(
			'mcnmb-admin',
			plugins_url( 'rpb-admin.js', __FILE__ ),
			array( 'jquery', 'wp-color-picker' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'rpb-admin.js' ),
			true
		);

		wp_enqueue_style( 'wp-color-picker' );
	}

	/**
	 * Register settings with sanitization callback.
	 */
	public function register_options_settings() {
		register_setting(
			'rpb_custom_options-group',
			MCNMB_OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_options' ),
			)
		);
	}

	/**
	 * Enqueue frontend stylesheet when the plugin has configured output.
	 */
	public function frontend_styles() {
		if ( ! $this->has_info() ) {
			return;
		}

		wp_enqueue_style(
			'mcnmb-frontend',
			plugins_url( 'rpb.css', __FILE__ ),
			array( 'dashicons' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'rpb.css' )
		);
	}

	/**
	 * Render settings page.
	 */
	public function options_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $_wp_admin_css_colors, $wp_version;
		$admin_color = get_user_option( 'admin_color' );
		$primary_color = isset( $_wp_admin_css_colors[ $admin_color ]->colors[2] ) ? $_wp_admin_css_colors[ $admin_color ]->colors[2] : '#2271b1';
		?>
		<div class="wrap" id="rpb">
			<h1><?php esc_html_e( 'Mobile Call Now & Map Buttons', 'mobile-call-now-map-buttons' ); ?></h1>
			<form method="post" action="options.php" class="form-table">
				<?php settings_fields( 'rpb_custom_options-group' ); ?>

				<h2 class="title"><?php esc_html_e( 'Settings', 'mobile-call-now-map-buttons' ); ?></h2>
				<p><?php esc_html_e( 'Select the range of devices you wish to have the Call Now and Directions buttons show on.', 'mobile-call-now-map-buttons' ); ?></p>
				<table border="0" cellpadding="2" cellspacing="2">
					<tr>
						<th><br /><?php esc_html_e( 'Display on', 'mobile-call-now-map-buttons' ); ?></th>
						<td>
							<div id="rpb_devices">
								<div id="iphone_se" data-size="320" class="rpb_device"><span></span><span>320px</span></div>
								<div id="iphone_6s" data-size="375" class="rpb_device"><span></span><span>375px</span></div>
								<div id="iphone_6sp" data-size="414" class="rpb_device"><span></span><span>414px</span></div>
								<div id="default" data-size="680" class="rpb_device"><span></span><span>680px</span></div>
								<div id="ipad_air2" data-size="768" class="rpb_device"><span></span><span>768px</span></div>
								<div id="ipad_pro" data-size="1024" class="rpb_device"><span></span><span>1024px</span></div>
							</div>
							<br class="clear" /><br class="clear" />
							<input type="hidden" size="4" id="mobile_size_input" name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[mobile_size]" value="<?php echo esc_attr( $this->options['mobile_size'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Bar Background', 'mobile-call-now-map-buttons' ); ?></th>
						<td><input type="text" class="colourme" name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[bg_color]" value="<?php echo esc_attr( $this->options['bg_color'] ); ?>" /></td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Call Now Button', 'mobile-call-now-map-buttons' ); ?></h2>
				<p><?php esc_html_e( 'By adding a phone number, the Call Now button will display when viewing the site with a device in the Display On range.', 'mobile-call-now-map-buttons' ); ?></p>
				<table border="0" cellpadding="2" cellspacing="2">
					<tr>
						<th><?php esc_html_e( 'Text', 'mobile-call-now-map-buttons' ); ?></th>
						<td>
							<input type="text" id="callbutton_text" name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[call_text]" value="<?php echo esc_attr( $this->options['call_text'] ); ?>" placeholder="<?php esc_attr_e( 'Call Now', 'mobile-call-now-map-buttons' ); ?>" /><br />
							<input type="text" class="colourme" name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[call_text_color]" value="<?php echo esc_attr( $this->options['call_text_color'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Button', 'mobile-call-now-map-buttons' ); ?></th>
						<td><input type="text" class="colourme" name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[call_color]" value="<?php echo esc_attr( $this->options['call_color'] ); ?>" /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Phone Number', 'mobile-call-now-map-buttons' ); ?></th>
						<td><input name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[phone_number]" placeholder="555-555-5555" value="<?php echo esc_attr( $this->options['phone_number'] ); ?>" /><br /></td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Map Button', 'mobile-call-now-map-buttons' ); ?></h2>
				<p><?php esc_html_e( 'By adding an address or GPS coordinates, the Directions button will display. You can also add an optional Google Place ID for a more precise Google Maps destination.', 'mobile-call-now-map-buttons' ); ?></p>
				<table border="0" cellpadding="2" cellspacing="2">
					<tr>
						<th><?php esc_html_e( 'Text', 'mobile-call-now-map-buttons' ); ?></th>
						<td>
							<input type="text" id="mapbutton_text" name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[map_text]" value="<?php echo esc_attr( $this->options['map_text'] ); ?>" placeholder="<?php esc_attr_e( 'Directions', 'mobile-call-now-map-buttons' ); ?>" /><br />
							<input type="text" class="colourme" name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[map_text_color]" value="<?php echo esc_attr( $this->options['map_text_color'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Button', 'mobile-call-now-map-buttons' ); ?></th>
						<td><input type="text" class="colourme" name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[map_color]" value="<?php echo esc_attr( $this->options['map_color'] ); ?>" /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Location', 'mobile-call-now-map-buttons' ); ?></th>
						<td>
							<label><input type="radio" name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[location]" value="address" <?php checked( $this->options['location'], 'address' ); ?> /> <?php esc_html_e( 'Street Address', 'mobile-call-now-map-buttons' ); ?></label> &nbsp; &nbsp;
							<label><input type="radio" name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[location]" value="gps" <?php checked( $this->options['location'], 'gps' ); ?> /> <?php esc_html_e( 'GPS Coordinates', 'mobile-call-now-map-buttons' ); ?></label>

							<p class="description mcnmb-place-id-help">
								<?php esc_html_e( 'Optional: add a Google Place ID to make the map destination more precise.', 'mobile-call-now-map-buttons' ); ?>
								<a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Learn about Place IDs', 'mobile-call-now-map-buttons' ); ?></a>
								|
								<a href="https://developers.google.com/maps/documentation/javascript/examples/places-placeid-finder" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get Your Place ID', 'mobile-call-now-map-buttons' ); ?></a>
							</p>

							<div class="location_option" data-type="address">
								<input name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[street]" value="<?php echo esc_attr( $this->options['street'] ); ?>" style="width:98.5%" placeholder="<?php esc_attr_e( 'Number and Street', 'mobile-call-now-map-buttons' ); ?>" /><br />
								<input name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[city]" value="<?php echo esc_attr( $this->options['city'] ); ?>" style="width:67%" placeholder="<?php esc_attr_e( 'City', 'mobile-call-now-map-buttons' ); ?>" />
								<input name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[province]" value="<?php echo esc_attr( $this->options['province'] ); ?>" style="width:30%" placeholder="<?php esc_attr_e( 'Province / State', 'mobile-call-now-map-buttons' ); ?>" /><br />
								<input name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[country]" value="<?php echo esc_attr( $this->options['country'] ); ?>" style="width:47%" placeholder="<?php esc_attr_e( 'Country', 'mobile-call-now-map-buttons' ); ?>" />
								<input name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[postal_code]" value="<?php echo esc_attr( $this->options['postal_code'] ); ?>" style="width:50%" placeholder="<?php esc_attr_e( 'Postal / Zip Code', 'mobile-call-now-map-buttons' ); ?>" />
							</div>

							<div class="location_option" data-type="gps">
								<label class="label"><?php esc_html_e( 'Latitude', 'mobile-call-now-map-buttons' ); ?>:</label>
								<input name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[lat]" class="code" value="<?php echo esc_attr( $this->sanitize_gps( $this->options['lat'] ) ); ?>" placeholder="dd.ddddd" /><br />
								<label class="label"><?php esc_html_e( 'Longitude', 'mobile-call-now-map-buttons' ); ?>:</label>
								<input name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[lng]" class="code" value="<?php echo esc_attr( $this->sanitize_gps( $this->options['lng'] ) ); ?>" placeholder="dd.ddddd" />
							</div>

							<div class="mcnmb-place-id-field">
								<label class="label"><?php esc_html_e( 'Google Place ID', 'mobile-call-now-map-buttons' ); ?>:</label>
								<input name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[place_id]" class="regular-text code" value="<?php echo esc_attr( $this->sanitize_place_id( $this->options['place_id'] ) ); ?>" placeholder="ChIJ..." />
								<p class="description"><?php esc_html_e( 'Optional. Keep your address or GPS coordinates filled in, then add a Place ID to help Google Maps identify the exact business location.', 'mobile-call-now-map-buttons' ); ?></p>
							</div>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Advanced Options', 'mobile-call-now-map-buttons' ); ?></h2>
				<table border="0" cellpadding="2" cellspacing="2">
					<tr>
						<th><?php esc_html_e( 'z-index', 'mobile-call-now-map-buttons' ); ?></th>
						<td><input type="number" id="zindex" name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[zindex]" value="<?php echo esc_attr( $this->options['zindex'] ); ?>" placeholder="998" /><br /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Append to body', 'mobile-call-now-map-buttons' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[forcebtm]" value="1" <?php checked( $this->options['forcebtm'], '1' ); ?> /> <?php esc_html_e( 'Move plugin to absolute bottom', 'mobile-call-now-map-buttons' ); ?></label><br /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Number Sanitizing', 'mobile-call-now-map-buttons' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( MCNMB_OPTION_NAME ); ?>[nosanitizing]" value="1" <?php checked( $this->options['nosanitizing'], '1' ); ?> /> <?php esc_html_e( "Don't reformat my phone number", 'mobile-call-now-map-buttons' ); ?></label><br /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Profile', 'mobile-call-now-map-buttons' ); ?></th>
						<td>
							<div class="code">
								<?php
								$my_theme = wp_get_theme();
								$setup    = array(
									'PHP'       => phpversion(),
									'WordPress' => $wp_version,
									'Theme'     => $my_theme->get( 'Name' ) . ' (' . get_option( 'template' ) . ') ' . $my_theme->get( 'Version' ),
									'URL'       => preg_replace( '#^https?://(www\.)?#', '', home_url() ),
									'Plugins'   => get_option( 'active_plugins' ),
								);
								echo esc_html( wp_json_encode( $setup ) );
								?>
							</div>
						</td>
					</tr>
				</table>

				<p class="submit"><input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'mobile-call-now-map-buttons' ); ?>" /></p>
			</form>
			<p id="streetcred">
				<?php esc_html_e( 'Plugin By', 'mobile-call-now-map-buttons' ); ?>
				<a href="https://lakkineni.com/" target="_blank" rel="noopener noreferrer">V. K. Lakkineni</a> &amp; <a href="https://davidsword.ca/" target="_blank" rel="noopener noreferrer">David Sword</a>
			</p>
		</div>
		<style>
			#rpb #rpb_devices > div.active span:nth-child(1) { border-color: <?php echo esc_html( $primary_color ); ?> !important; }
			#rpb #rpb_devices > div.active span:nth-child(2) { color: <?php echo esc_html( $primary_color ); ?> !important; }
		</style>
		<?php
	}

	/**
	 * Add mobile buttons to footer.
	 */
	public function add_buttons() {
		if ( ! $this->has_info() ) {
			echo "\n<!-- " . esc_html__( 'Mobile Call Now & Map Buttons not displayed because phone number or address values are missing.', 'mobile-call-now-map-buttons' ) . " -->\n";
			return;
		}

		$phone_number = isset( $this->options['phone_number'] ) ? $this->options['phone_number'] : '';
		$tel_number   = $this->sanitize_phone_for_href( $phone_number );
		$directions   = $this->google_map_url();
		?>
		<!-- Mobile Call Now and Map Buttons -->
		<div id="rpb_spacer" aria-hidden="true"></div>
		<div id="rpb" aria-label="<?php esc_attr_e( 'Mobile contact buttons', 'mobile-call-now-map-buttons' ); ?>">
			<?php if ( ! empty( $tel_number ) ) : ?>
				<div>
					<a href="<?php echo esc_url( 'tel:' . $tel_number ); ?>" id="call_now">
						<span class="dashicons dashicons-phone" aria-hidden="true"></span> <?php echo esc_html( $this->options['call_text'] ); ?>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $directions ) ) : ?>
				<div>
					<a href="<?php echo esc_url( $directions ); ?>" id="map_now" target="_blank" rel="noopener noreferrer">
						<span class="dashicons dashicons-location" aria-hidden="true"></span> <?php echo esc_html( $this->options['map_text'] ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
		<style>
			@media screen and (max-width: <?php echo absint( $this->options['mobile_size'] ); ?>px) {
				div#rpb { display: flex !important; background: <?php echo esc_html( $this->sanitize_hex_color_with_default( $this->options['bg_color'], '#1a1919' ) ); ?>; }
				div#rpb_spacer { display: block !important; }
			}
			div#rpb { background: <?php echo esc_html( $this->sanitize_hex_color_with_default( $this->options['bg_color'], '#1a1919' ) ); ?>; <?php echo ! empty( $this->options['zindex'] ) ? 'z-index:' . absint( $this->options['zindex'] ) . ' !important;' : ''; ?> }
			div#rpb div a#call_now { background: <?php echo esc_html( $this->sanitize_hex_color_with_default( $this->options['call_color'], '#0c3' ) ); ?>; color: <?php echo esc_html( $this->sanitize_hex_color_with_default( $this->options['call_text_color'], '#fff' ) ); ?>; }
			div#rpb div a#map_now { background: <?php echo esc_html( $this->sanitize_hex_color_with_default( $this->options['map_color'], '#fc3' ) ); ?>; color: <?php echo esc_html( $this->sanitize_hex_color_with_default( $this->options['map_text_color'], '#fff' ) ); ?>; }
		</style>
		<?php if ( '1' === $this->options['forcebtm'] ) : ?>
			<script>
				window.setTimeout(function() {
					var spacer = document.getElementById('rpb_spacer');
					var buttons = document.getElementById('rpb');
					if (spacer && buttons) {
						document.body.appendChild(spacer);
						document.body.appendChild(buttons);
					}
				}, 500);
			</script>
		<?php endif; ?>
		<!-- /Mobile Call Now and Map Buttons -->
		<?php
	}

	/**
	 * Default option values.
	 *
	 * @return array<string, string>
	 */
	private function defaults() {
		return array(
			'mobile_size'     => '680',
			'bg_color'        => '#1a1919',
			'call_text'       => __( 'Call Now', 'mobile-call-now-map-buttons' ),
			'call_color'      => '#0c3',
			'call_text_color' => '#fff',
			'phone_number'    => '',
			'map_text'        => __( 'Directions', 'mobile-call-now-map-buttons' ),
			'map_text_color'  => '#fff',
			'map_color'       => '#fc3',
			'location'        => 'address',
			'street'          => '',
			'city'            => '',
			'province'        => '',
			'country'         => '',
			'postal_code'     => '',
			'lat'             => '',
			'lng'             => '',
			'place_id'        => '',
			'zindex'          => '',
			'forcebtm'        => '',
			'nosanitizing'    => '',
		);
	}

	/**
	 * Get merged/sanitized options.
	 *
	 * @return array<string, string>
	 */
	private function get_options() {
		$options = get_option( MCNMB_OPTION_NAME, array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		return $this->sanitize_options( wp_parse_args( $options, $this->defaults() ) );
	}

	/**
	 * Sanitize options saved through settings API.
	 *
	 * @param mixed $input Raw input.
	 * @return array<string, string>
	 */
	public function sanitize_options( $input ) {
		$defaults = $this->defaults();
		$input    = is_array( $input ) ? $input : array();
		$output   = array();

		$output['mobile_size']     = isset( $input['mobile_size'] ) ? (string) min( 1920, max( 1, absint( $input['mobile_size'] ) ) ) : $defaults['mobile_size'];
		$output['bg_color']        = $this->sanitize_hex_color_with_default( $input['bg_color'] ?? '', $defaults['bg_color'] );
		$output['call_text']       = sanitize_text_field( $input['call_text'] ?? $defaults['call_text'] );
		$output['call_color']      = $this->sanitize_hex_color_with_default( $input['call_color'] ?? '', $defaults['call_color'] );
		$output['call_text_color'] = $this->sanitize_hex_color_with_default( $input['call_text_color'] ?? '', $defaults['call_text_color'] );
		$output['phone_number']    = sanitize_text_field( $input['phone_number'] ?? '' );
		$output['map_text']        = sanitize_text_field( $input['map_text'] ?? $defaults['map_text'] );
		$output['map_text_color']  = $this->sanitize_hex_color_with_default( $input['map_text_color'] ?? '', $defaults['map_text_color'] );
		$output['map_color']       = $this->sanitize_hex_color_with_default( $input['map_color'] ?? '', $defaults['map_color'] );
		$output['location']        = in_array( $input['location'] ?? 'address', array( 'address', 'gps' ), true ) ? $input['location'] : 'address';
		$output['street']          = sanitize_text_field( $input['street'] ?? '' );
		$output['city']            = sanitize_text_field( $input['city'] ?? '' );
		$output['province']        = sanitize_text_field( $input['province'] ?? '' );
		$output['country']         = sanitize_text_field( $input['country'] ?? '' );
		$output['postal_code']     = sanitize_text_field( $input['postal_code'] ?? '' );
		$output['lat']             = $this->sanitize_gps( $input['lat'] ?? '' );
		$output['lng']             = $this->sanitize_gps( $input['lng'] ?? '' );
		$output['place_id']        = $this->sanitize_place_id( $input['place_id'] ?? '' );
		$output['zindex']          = isset( $input['zindex'] ) && '' !== $input['zindex'] ? (string) absint( $input['zindex'] ) : '';
		$output['forcebtm']        = ! empty( $input['forcebtm'] ) ? '1' : '';
		$output['nosanitizing']    = ! empty( $input['nosanitizing'] ) ? '1' : '';

		return $output;
	}

	/**
	 * Check whether there is enough information to output a button.
	 *
	 * @return bool
	 */
	private function has_info() {
		return ! empty( $this->options['phone_number'] ) || $this->has_map_info();
	}

	/**
	 * Check whether there is enough information to output the map button.
	 *
	 * @return bool
	 */
	private function has_map_info() {
		if ( 'address' === $this->options['location'] ) {
			return ! empty( $this->options['street'] ) || ! empty( $this->options['city'] ) || ! empty( $this->options['province'] ) || ! empty( $this->options['country'] ) || ! empty( $this->options['postal_code'] );
		}

		return ! empty( $this->options['lat'] ) && ! empty( $this->options['lng'] );
	}


	/**
	 * Generate Google Maps link.
	 *
	 * @return string
	 */
	private function google_map_url() {
		$separator = ', ';

		if ( 'address' === $this->options['location'] ) {
			$parts = array_filter(
				array(
					$this->options['street'],
					$this->options['city'],
					$this->options['province'],
					$this->options['country'],
					$this->options['postal_code'],
				)
			);
			$location = implode( $separator, $parts );
		} else {
			$location = trim( $this->sanitize_gps( $this->options['lat'] ) . $separator . $this->sanitize_gps( $this->options['lng'] ), $separator );
		}

		if ( '' === trim( $location ) ) {
			return '';
		}

		$args = array(
			'api'         => '1',
			'destination' => $location,
		);

		if ( ! empty( $this->options['place_id'] ) ) {
			$args['destination_place_id'] = $this->sanitize_place_id( $this->options['place_id'] );
		}

		return add_query_arg( $args, 'https://www.google.com/maps/dir/' );
	}

	/**
	 * Sanitize phone number for href.
	 *
	 * @param string $number Phone number.
	 * @return string
	 */
	private function sanitize_phone_for_href( $number ) {
		if ( '1' === $this->options['nosanitizing'] ) {
			return preg_replace( '/[^0-9+*#,;\-(). ]/', '', $number );
		}

		return preg_replace( '/[^0-9+*#,;]/', '', $number );
	}

	/**
	 * Sanitize Google Place ID.
	 *
	 * @param string $place_id Google Place ID.
	 * @return string
	 */
	private function sanitize_place_id( $place_id ) {
		return preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $place_id );
	}

	/**
	 * Sanitize GPS coordinate.
	 *
	 * @param string $coord Coordinate.
	 * @return string
	 */
	private function sanitize_gps( $coord ) {
		$coord = str_replace( array( '°', 'N', 'E', 'S', 'W', 'n', 'e', 's', 'w', ' ' ), '', (string) $coord );
		return preg_replace( '/[^0-9+\-.]/', '', $coord );
	}

	/**
	 * Sanitize a hex color and fall back when invalid.
	 *
	 * @param string $color Color value.
	 * @param string $default Default color.
	 * @return string
	 */
	private function sanitize_hex_color_with_default( $color, $default ) {
		$sanitized = sanitize_hex_color( $color );
		return $sanitized ? $sanitized : $default;
	}
}
