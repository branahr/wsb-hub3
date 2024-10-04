<?php
/**
 * The file that defines the core plugin class
 *
 * @link       https://www.webstudiobrana.com
 * @since      1.0.0
 *
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/includes
 */

/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/includes
 * @author     Branko Borilovic <brana.hr@gmail.com>
 */
class Wsb_Hub3 {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wsb_Hub3_Loader    $loader   
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name  
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version 
	 */
	protected $version;

	/**
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WSB_HUB3_VERSION' ) ) {
			$this->version = WSB_HUB3_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wsb-hub3';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_shortcodes();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wsb_Hub3_Loader. Orchestrates the hooks of the plugin.
	 * - Wsb_Hub3_i18n. Defines internationalization functionality.
	 * - Wsb_Hub3_Admin. Defines all hooks for the admin area.
	 * - Wsb_Hub3_Public. Defines all hooks for the public side of the site.
	 * - Wsb_Hub3_Shortcodes. Defines all shortcodes.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wsb-hub3-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wsb-hub3-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wsb-hub3-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wsb-hub3-public.php';

		/**
		 * The class responsible for rendering all plugin shortcodes
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wsb-hub3-shortcodes.php';

		$this->loader = new Wsb_Hub3_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wsb_Hub3_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wsb_Hub3_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wsb_Hub3_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_filter( 'woocommerce_settings_tabs_array', $plugin_admin, 'add_wsb_hub3_settings_tab', 100 );
		$this->loader->add_action( 'woocommerce_sections_wsb_hub3_admin_tab', $plugin_admin, 'wsb_hub3_output_sections' );
		$this->loader->add_action( 'woocommerce_settings_wsb_hub3_admin_tab', $plugin_admin, 'wsb_hub3_output_settings'  );
		$this->loader->add_action( 'woocommerce_settings_save_wsb_hub3_admin_tab', $plugin_admin, 'wsb_hub3_save' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'wsb_hub3_notice' );
		

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wsb_Hub3_Public( $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'wsb_hub3_update_barcode_meta', 20, 2 );
		$this->loader->add_action( 'woocommerce_view_order', $plugin_public, 'wsb_hub3_barcode_order_display', 20);
		$this->loader->add_action( 'woocommerce_thankyou', $plugin_public, 'wsb_hub3_barcode_thankyou', 1 );
		$this->loader->add_action( 'woocommerce_email_after_order_table', $plugin_public, 'wsb_hub3_email_after_order_table', 10, 4 );
		$this->loader->add_action( 'woocommerce_update_order', $plugin_public, 'wsb_hub3_admin_order_update', 25, 2 );
		$this->loader->add_filter( 'woocommerce_gateway_description', $plugin_public, 'wsb_hub3_gateway_description', 25, 2 );
		$this->loader->add_filter( 'woocommerce_bacs_account_fields', $plugin_public, 'wsb_remove_bank_details', 10, 2 );
	}

	/**
	 * Register all of the shortcodes. 
	 *
	 * @since    1.0.6
	 * @access   private
	 */
	private function define_shortcodes() {

		$plugin_shortcodes = new Wsb_Hub3_Shortcodes();
		
		$plugin_shortcodes->init();

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}


	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wsb_Hub3_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
