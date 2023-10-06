<?php
/*
Plugin Name: Barwenock Random Products
Plugin URI: https://some-url.com
Description: An OOP based plugin to show random products
data
Author: Maksym Frolov
Author URI: https://some-url.com
Version: 1.0
*/
class BarwenockRandomProducts {
	function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'barwenock_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'barwenock_assets' ) );
		add_action( 'admin_menu', [ $this, 'barwenock_options_page'] );
		add_action( 'admin_init', [ $this, 'barwenock_settings_init'] );
		add_filter('plugin_action_links_'.plugin_basename(__FILE__), [ $this,
			'barwenock_add_plugin_page_settings_link' ]);
		add_shortcode('barwenock_random_products', array($this, 'barwenock_random_products_shortcode'));

	}
	/**
	 * Enqueue admin assets
	 */
	function barwenock_admin_assets() {
		wp_enqueue_style( 'barwenock-main', plugin_dir_url( __FILE__ ) . 'assets/css/main-admin.css', array(), false, 'all' );
	}

	/**
	 * Enqueue assets
	 */
	function barwenock_assets() {
		wp_enqueue_style( 'barwenock-main', plugin_dir_url( __FILE__ ) . 'assets/css/main.css', array(), false, 'all' );
	}
	/**
	 * custom option and settings
	 */
	function barwenock_settings_init() {
		// Register a new setting for "barwenock" page.
		register_setting( 'barwenock', 'barwenock_options' );

		// Register a new section in the "barwenock" page.
		add_settings_section(
			'barwenock_products_amount',
			__( 'Enter the number of random products to show', 'barwenock' ),
			[ $this, 'barwenock_section_developers_callback' ],
			'barwenock'
		);
		// Register a new field in the "barwenock_section_developers" section, inside the "barwenock" page.
		add_settings_field(
			'barwenock_prod_amount', // As of WP 4.6 this value is used only
			__( 'Products Amount', 'barwenock' ),
			[ $this, 'barwenock_field_product_amount_cb' ],
			'barwenock',
			'barwenock_products_amount',
			array(
				'label_for'         => 'barwenock_prod_amount',
				'class'             => 'barwenock_row',
				'barwenock_custom_data' => 'custom',
			)
		);

	}
	function barwenock_add_plugin_page_settings_link( $links ) {
		$links[] = '<a href="' .
				   admin_url( '/admin.php?page=barwenock_settings' ) .
				   '">' . __('Settings') . '</a>';
		return $links;
	}
	/**
	 * Developers section callback function.
	 *
	 * @param array $args  The settings array, defining title, id, callback.
	 */
	function barwenock_section_developers_callback( $args ) {
		?>
		<?php
	}

	/**
	 * keyword field callback function.
	 *
	 * WordPress has magic interaction with the following keys: label_for, class.
	 * - the "label_for" key value is used for the "for" attribute of the <label>.
	 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
	 * Note: you can add custom key value pairs to be used inside your callbacks.
	 *
	 * @param array $args
	 */
	function barwenock_field_product_amount_cb( $args ) {
		// Get the value of the setting we've registered with register_setting()
		$options = get_option( 'barwenock_options' );
		?>
		<input type="text"
			   value="<?php echo $options['barwenock_prod_amount']; ?>"
			   name="barwenock_options[<?php echo esc_attr( $args['label_for']
			   ); ?>]">
		<?php
	}

	/**
	 * Add the top level menu page.
	 */
	function barwenock_options_page() {
		add_menu_page(
			'Barwenock Random Posts Settings',
			'Barwenock Options',
			'manage_options',
			'barwenock',
			[ $this, 'barwenock_options_page_html']
		);
	}

	/**
	 * Top level menu callback function
	 */
	function barwenock_options_page_html() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// add error/update messages

		// check if the user have submitted the settings
		// WordPress will add the "settings-updated" $_GET parameter to the url
		if ( isset( $_GET['settings-updated'] ) ) {
			// add settings saved message with the class of "updated"
			add_settings_error( 'barwenock_messages', 'barwenock_message', __( 'Settings Saved', 'barwenock' ), 'updated' );
		}

		// show error/update messages
		settings_errors( 'barwenock_messages' );

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
			<?php
			// output security fields for the registered setting "barwenock"
			settings_fields( 'barwenock' );
			// output setting sections and their fields
			// (sections are registered for "barwenock", each field is registered to a specific section)
			do_settings_sections( 'barwenock' );

			submit_button( 'Save Settings' );
			?>
			</form>
			<h2>Preview</h2>
			<?php echo do_shortcode('[barwenock_random_products]'); ?>
		</div>
		<?php
	}

	public function barwenock_random_products_shortcode(){
		$options = get_option( 'barwenock_options' );
		$args = [
				'post_type' => ['product'],
				'orderby' => 'rand',
				'posts_per_page' => $options['barwenock_prod_amount']
		];
		$query = new WP_Query($args);
		$products = $query->posts;
		$output = '<ul class="products-list">';
		foreach ($products as $s_product){
			$s_product_id = $s_product->ID;
			$output .= '<li class="single_product">'
				. get_the_post_thumbnail($s_product_id) .
				  '</li>';

		}
		$output .= '</ul>';

		return $output;
	}
}
new BarwenockRandomProducts();
