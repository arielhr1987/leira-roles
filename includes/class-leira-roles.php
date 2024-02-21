<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/arielhr1987/leira-roles
 * @since      1.0.0
 *
 * @package    Leira_Roles
 * @subpackage Leira_Roles/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Leira_Roles
 * @subpackage Leira_Roles/includes
 * @author     Ariel <arielhr1987@gmail.com>
 *
 * @property Leira_Roles_Admin         admin
 * @property Leira_Roles_Manager       manager
 * @property Leira_Roles_Actions       actions
 * @property Leira_Roles_Notifications notify
 */
class Leira_Roles {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Leira_Roles_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Singleton instance
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * The Singleton method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	protected function __construct() {
		if ( defined( 'LEIRA_ROLES_VERSION' ) ) {
			$this->version = LEIRA_ROLES_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'leira-roles';
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Leira_Roles_Loader. Orchestrates the hooks of the plugin.
	 * - Leira_Roles_i18n. Defines internationalization functionality.
	 * - Leira_Roles_Admin. Defines all hooks for the admin area.
	 * - Leira_Roles_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-leira-roles-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-leira-roles-i18n.php';

		if ( is_admin() ) {
			/**
			 * The class responsible for manage roles and capabilities.
			 */
			require_once plugin_dir_path( __DIR__ ) . 'admin/class-leira-roles-manager.php';
			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once plugin_dir_path( __DIR__ ) . 'admin/class-leira-roles-admin.php';
			/**
			 * The class responsible for handling form submissions
			 */
			require_once plugin_dir_path( __DIR__ ) . 'admin/class-leira-roles-actions.php';
			/**
			 * The class responsible for handling notifications
			 */
			require_once plugin_dir_path( __DIR__ ) . 'admin/class-leira-roles-notifications.php';
		}

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'public/class-leira-roles-public.php';

		$this->loader = new Leira_Roles_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Leira_Roles_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Leira_Roles_i18n();
		$this->get_loader()->set( 'i18n', $plugin_i18n );

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

		if ( is_admin() ) {
			/**
			 * Roles and capabilities manager
			 */
			$manager = new Leira_Roles_Manager();
			$this->loader->set( 'manager', $manager );

			/**
			 * Plugin admin
			 */
			$plugin_admin = new Leira_Roles_Admin( $this->get_plugin_name(), $this->get_version() );
			$this->loader->set( 'admin', $plugin_admin );

			$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
			$this->loader->add_action( 'admin_footer', $plugin_admin, 'quick_edit_user_capabilities_form' );

			$this->loader->add_filter( 'set-screen-option', $plugin_admin, 'filter_set_screen_option', 10, 3 );
			$this->loader->add_filter( 'user_row_actions', $plugin_admin, 'filter_user_row_actions', 10, 2 );

			$this->loader->add_filter( 'admin_footer_text', $plugin_admin, 'admin_footer_text', 1000 );

			/**
			 * Actions to handle
			 */
			$actions = new Leira_Roles_Actions();
			$this->loader->set( 'actions', $actions );
			$this->loader->add_action( 'wp_ajax_leira-roles-add-role', $actions, 'handle' );
			$this->loader->add_action( 'wp_ajax_leira-roles-delete-role', $actions, 'handle' );
			$this->loader->add_action( 'wp_ajax_leira-roles-clone-role', $actions, 'handle' );
			$this->loader->add_action( 'wp_ajax_leira-roles-quick-edit-role', $actions, 'handle' );
			$this->loader->add_action( 'wp_ajax_leira-roles-quick-edit-user-capabilities', $actions, 'handle' );
			$this->loader->add_action( 'wp_ajax_leira-roles-footer-rated', $actions, 'footer_rated' );

			// for future versions add ajax to capability creation
			// $this->loader->add_action( 'wp_ajax_leira-roles-add-capability', $actions, 'handle' );

			$this->loader->add_action( 'load-users.php', $plugin_admin, 'load_users_page' );

			/**
			 * Notifications
			 */
			$notifications = new Leira_Roles_Notifications();
			$this->loader->set( 'notify', $notifications );

		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Leira_Roles_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Gets an instance from the loader
	 *
	 * @param string $key
	 *
	 * @return mixed|null The instance
	 *
	 * @since     1.1.0
	 * @access    public
	 */
	public function __get( $key ) {
		return $this->get_loader()->get( $key );
	}

	/**
	 * Sets an instance in the loader
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @since     1.1.0
	 * @access    public
	 */
	public function __set( $key, $value ) {
		$this->get_loader()->set( $key, $value );
	}
}
