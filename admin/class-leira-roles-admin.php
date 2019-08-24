<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/arielhr1987
 * @since      1.0.0
 *
 * @package    Leira_Roles
 * @subpackage Leira_Roles/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * This class is responsible for render admin pages and load dependencies these pages use
 *
 * @package    Leira_Roles
 * @subpackage Leira_Roles/admin
 * @author     Ariel <arielhr1987@gmail.com>
 */
class Leira_Roles_Admin{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The capability
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var string
	 */
	protected $capability = 'manage_options';

	/**
	 * Roles list table instance
	 *
	 * @var null
	 */
	protected $roles_list_table;

	/**
	 * Capabilities list table instance
	 *
	 * @var null
	 */
	protected $capabilities_list_table;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name             = $plugin_name;
		$this->version                 = $version;
		$this->roles_list_table        = null;
		$this->capabilities_list_table = null;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Leira_Roles_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Leira_Roles_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/leira-roles-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @param string $hook The page uri
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {

		if ( $hook === 'users.php' ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/inline-edit-user-capabilities.js', array(
				'jquery',
				'wp-a11y'
			), $this->version, false );
		}

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/leira-roles-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Returns the admin list table instance
	 *
	 * @return Leira_Roles_List_Table
	 */
	public function get_roles_list_table() {
		if ( $this->roles_list_table === null ) {
			if ( ! class_exists( 'Leira_Roles_List_Table' ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'class-leira-roles-list-table.php';
			}
			$this->roles_list_table = new Leira_Roles_List_Table( array(
				'screen' => get_current_screen()
			) );
		}

		return $this->roles_list_table;
	}

	/**
	 * Returns the admin list table instance for capabilities page
	 *
	 * @return Leira_Roles_Capabilities_List_Table
	 */
	public function get_capabilities_list_table() {
		if ( $this->capabilities_list_table === null ) {
			if ( ! class_exists( 'Leira_Roles_Capabilities_List_Table' ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'class-leira-roles-capabilities-list-table.php';
			}
			$this->capabilities_list_table = new Leira_Roles_Capabilities_List_Table( array(
				'screen' => get_current_screen()
			) );
		}

		return $this->capabilities_list_table;
	}

	/**
	 * Add the admin menu items
	 */
	public function admin_menu() {

		/**
		 * Add roles menu item
		 */
		$hook = add_users_page(
			__( 'Roles', 'leira-roles' ),
			__( 'Roles', 'leira-roles' ),
			$this->capability,
			'leira-roles',
			array( $this, 'render_roles_admin_page' ) );

		if ( ! empty( $hook ) ) {
			add_action( "load-$hook", array( $this, 'admin_roles_page_load' ) );
		}

		/**
		 * Add capabilities menu item
		 */
		$hook = add_users_page(
			__( 'Capabilities', 'leira-roles' ),
			__( 'Capabilities', 'leira-roles' ),
			$this->capability,
			'leira-roles-capabilities',
			array( $this, 'render_capabilities_admin_page' ) );

		if ( ! empty( $hook ) ) {
			add_action( "load-$hook", array( $this, 'admin_capabilities_page_load' ) );
		}
	}

	/**
	 * Filter the per_page screen option for the admin list table
	 *
	 * @param        $false
	 * @param string $option The option name
	 * @param string $value  The new value for the option
	 *
	 * @return int
	 */
	public function filter_set_screen_option( $false, $option, $value ) {

		if ( $option === 'users_page_leira_roles_per_page' ) {
			$value = (int) $value;
			if ( $value > 0 && $value < 1000 ) {
				return $value;
			}
		} else if ( $option === 'users_page_leira_roles_capabilities_per_page' ) {
			$value = (int) $value;
			if ( $value > 0 && $value < 1000 ) {
				return $value;
			}
		}

		return $false;
	}

	/**
	 * Show capabilities quick edit row action in the users list page
	 *
	 * @param array   $actions Array of actions for the row
	 * @param WP_User $user    The current user in the row
	 *
	 * @return mixed
	 */
	public function filter_user_row_actions( $actions, $user ) {
		if ( current_user_can( $this->capability ) ) {

			$username = 'user';
			if ( isset( $user->data->user_nicename ) ) {
				$username = $user->data->user_nicename;
				$username = sprintf( '&#8220;%s&#8221;', $username );
			}
			$format     = '<button type="button" class="button-link editinline_capabilities" aria-label="%s" aria-expanded="false">%s</button><div class="hidden" id="inline_capabilities_%s">%s</di>';
			$aria_label = esc_attr( sprintf( __( 'Quick edit %s capabilities', 'leira-roles' ), $username ) );
			$label      = __( 'Capabilities', 'leira-roles' );

			$capabilities = Leira_Roles::instance()->get_loader()->get( 'manager' )->get_all_capabilities();
			if ( isset( $user->allcaps ) ) {
				//remove roles
				$allcaps      = array_filter( $user->allcaps, function( $cap ) {
					return ! Leira_Roles::instance()->get_loader()->get( 'manager' )->is_role( $cap );
				}, ARRAY_FILTER_USE_KEY );
				$capabilities = array_merge( $capabilities, $allcaps );
			}
			ksort( $capabilities );
			$capabilities = json_encode( $capabilities );

			$arr   = array(
				'user_id'      => $user->ID,
				'capabilities' => $capabilities
			);
			$items = '';
			foreach ( $arr as $key => $value ) {
				$items .= sprintf( '<div class="%s">%s</div>', $key, $value );
			}

			$actions['capabilities hide-if-no-js'] = sprintf( $format, $aria_label, $label, $user->ID, $items );
		}

		return $actions;
	}

	/**
	 * Add content to the admin footer
	 */
	public function quick_edit_user_capabilities_form() {
		$screen = get_current_screen();
		if ( $screen && $screen->id === 'users' ) {
			//Output the inline form
			global $wp_list_table;
			$columns_count = $wp_list_table->get_column_count();
			?>
            <form method="get">
                <table style="display: none">
                    <tbody id="inlineeditcapabilities">
                    <tr id="inline-edit-capabilities" class="inline-edit-row" style="display: none">
                        <td colspan="<?php echo $columns_count; ?>" class="colspanchange">

                            <fieldset class="">
                                <legend class="inline-edit-legend"><?php _e( 'Edit Capabilities', 'leira-roles' ); ?></legend>
                                <input type="hidden" name="user_id" value="">
                                <div class="inline-edit-col">
                                    <label>
                                        <span class="title"><?php _e( 'Capabilities', 'leira-roles' ); ?></span>
                                        <span class="input-text-wrap">
                                        <div class="wp-clearfix">
                                            <p class="search-box">
                                                <input type="search" name="capabilities_search_input"
                                                       placeholder="<?php _e( 'Search Capabilities', 'leira-roles' ) ?>">
                                            </p>
                                            <label class="alignleft">
                                                <input type="checkbox" class="cb-capabilities-select-all">
                                                <span class="checkbox-title"><?php _e( 'Select All', 'leira-roles' ) ?> </span>
                                            </label>
                                        </div>
                                        <div class="capabilities-container wp-clearfix">
                                            <div class="notice notice-error notice-alt inline hidden">
                                                <p class="error"><?php _e( 'No capabilities found.', 'leira-roles' ); ?> </p>
                                            </div>
                                        </div>
                                    </span>
                                    </label>

                                </div>
                            </fieldset>
                            <div class="inline-edit-save submit">
                                <button type="button" class="cancel button alignleft">
									<?php _e( 'Cancel', 'leira-roles' ); ?>
                                </button>
                                <button type="button"
                                        class="save button button-primary alignright">
									<?php _e( 'Save', 'leira-roles' ); ?>
                                </button>
                                <span class="spinner"></span>
								<?php wp_nonce_field( 'usercapabilitiesinlineeditnonce', '_inline_edit', false ); ?>
                                <br class="clear"/>
                                <div class="notice notice-error notice-alt inline hidden">
                                    <p class="error"></p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
			<?php
		}
	}

	/**
	 * This method is responsible for render the Roles admin page
	 */
	public function render_roles_admin_page() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'leira-roles' ) );
		}
		?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e( 'Roles', 'leira-roles' ) ?> </h1>
            <!--<a href="#" class="page-title-action">--><?php //_e( 'Add New', 'leira-roles' ) ?><!--</a>-->
			<?php
			if ( isset( $_REQUEST['s'] ) && $search = esc_attr( wp_unslash( $_REQUEST['s'] ) ) ) {
				/* translators: %s: search keywords */
				printf( ' <span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;', 'leira-roles' ) . '</span>', $search );
			}

			//the roles table instance
			$table = $this->get_roles_list_table();
			$table->prepare_items();
			$this->admin_notices();
			?>
            <hr class="wp-header-end">
            <div id="ajax-response"></div>
            <form class="search-form wp-clearfix" method="get">
				<?php $table->search_box( __( 'Search Roles', 'leira-roles' ), 'roles' ); ?>
            </form>
            <div id="col-container" class="wp-clearfix">
                <div id="col-left">
                    <div class="col-wrap">
                        <div class="form-wrap">
                            <h2><?php _e( 'Add New Role', 'leira-roles' ) ?> </h2>
                            <form id="addrole" method="post" action="" class="validate">
                                <input type="hidden" name="action" value="leira-roles-add-role">
                                <input type="hidden" name="screen" value="<?php echo get_current_screen()->id ?>">
								<?php
								wp_nonce_field( 'add-role' );
								?>

                                <div class="form-field form-required">
                                    <label for="role"><?php _e( 'Role', 'leira-roles' ) ?> </label>
                                    <input name="role" id="role" type="text" value="" size="40"
                                           aria-required="true">
                                    <p><?php _e( 'A unique identifier for the new role.', 'leira-roles' ); ?></p>
                                </div>
                                <div class="form-field form-required">
                                    <label for="name"><?php _e( 'Name', 'leira-roles' ) ?> </label>
                                    <input name="name" id="name" type="text" value="" size="40">
                                    <p><?php _e( 'The name is how it appears on your site.', 'leira-roles' ); ?></p>
                                </div>

                                <p class="submit">
                                    <input type="submit" name="submit" id="submit" class="button button-primary"
                                           value="<?php _e( 'Add New Role', 'leira-roles' ); ?> ">
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
                <div id="col-right">
                    <div class="col-wrap">
                        <form action="<?php echo add_query_arg( '', '' ) ?>" method="post">
							<?php $table->display(); //Display the table ?>
                        </form>
                        <div class="form-wrap edit-term-notes">
                            <p>Description here.</p>
                        </div>
                    </div>
                </div>
            </div>
            <form method="get">
				<?php $table->inline_edit() ?>
            </form>

        </div>

		<?php
	}

	/**
	 * Roles page load
	 */
	public function admin_roles_page_load() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'leira-roles' ) );
		}

		//ensure session is started. Its used for user notifications
		if ( session_status() !== PHP_SESSION_ACTIVE ) {
			session_start();
		}

		//enqueue styles
		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/leira-roles-admin.css', array(), $this->version, 'all' );

		//enqueue scripts
		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/leira-roles-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/inline-edit-roles.js', array(
			'jquery',
			'wp-a11y'
		), $this->version, false );

		//initialize table here to be able to register default WP_List_Table screen options
		$this->get_roles_list_table();

		//Handle actions
		$this->handle_actions();

		//Add screen options
		add_screen_option( 'per_page', array( 'default' => 999 ) );
	}

	/**
	 * This method is responsible for render the Capabilities admin page
	 */
	public function render_capabilities_admin_page() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'leira-roles' ) );
		}
		?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e( 'Capabilities', 'leira-roles' ) ?> </h1>
            <!--<a href="#" class="page-title-action">--><?php //_e( 'Add New', 'leira-roles' ) ?><!--</a>-->
			<?php
			if ( isset( $_REQUEST['s'] ) && $search = esc_attr( wp_unslash( $_REQUEST['s'] ) ) ) {
				/* translators: %s: search keywords */
				printf( ' <span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;', 'leira-roles' ) . '</span>', $search );
			}

			//the roles table instance
			$table = $this->get_capabilities_list_table();
			$table->prepare_items();
			$this->admin_notices();
			?>
            <hr class="wp-header-end">
            <div id="ajax-response"></div>
            <form class="search-form wp-clearfix" method="get">
				<?php $table->search_box( __( 'Search Capabilities', 'leira-roles' ), 'roles' ); ?>
            </form>
            <div id="col-container" class="wp-clearfix">
                <div id="col-left">
                    <div class="col-wrap">
                        <div class="form-wrap">
                            <h2><?php _e( 'Add New Capability', 'leira-roles' ) ?> </h2>
                            <form id="add_capability" method="post" action="" class="validate">
                                <input type="hidden" name="action" value="leira-roles-add-capability">
                                <input type="hidden" name="screen" value="<?php echo get_current_screen()->id ?>">
								<?php
								wp_nonce_field( 'add-capability' );
								?>

                                <div class="form-field form-required">
                                    <label for="role"><?php _e( 'Capability', 'leira-roles' ) ?> </label>
                                    <input name="capability" id="capability" type="text" value="" size="40"
                                           aria-required="true">
                                    <p><?php _e( 'A unique identifier for the new capability.', 'leira-roles' ); ?></p>
                                </div>
                                <p class="submit">
                                    <input type="submit" name="submit" id="submit" class="button button-primary"
                                           value="<?php _e( 'Add New Capability', 'leira-roles' ); ?> ">
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
                <div id="col-right">
                    <div class="col-wrap">
                        <form action="<?php echo add_query_arg( '', '' ) ?>" method="post">
							<?php $table->display(); //Display the table ?>
                        </form>
                        <div class="form-wrap edit-term-notes">
                            <p><?php _e( 'Built-in system capabilities are not deletable.', 'leira-roles' ) ?> </p>
                        </div>
                    </div>
                </div>
            </div>
            <form method="get">
				<?php $table->inline_edit() ?>
            </form>

        </div>

		<?php
	}

	/**
	 * Capabilities page load
	 */
	public function admin_capabilities_page_load() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'leira-roles' ) );
		}

		//ensure session is started. Its used for user notifications
		if ( session_status() !== PHP_SESSION_ACTIVE ) {
			session_start();
		}

		//enqueue styles
		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/leira-roles-admin.css', array(), $this->version, 'all' );

		//enqueue scripts
		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/leira-roles-capabilities-admin.js', array( 'jquery' ), $this->version, false );

		//initialize table here to be able to register default WP_List_Table screen options
		$this->get_capabilities_list_table();

		//Handle actions
		$this->handle_actions();

		//Add screen options
		add_screen_option( 'per_page', array( 'default' => 999 ) );
	}

	/**
	 * Display admin flash notices
	 */
	public function admin_notices() {
		$messages = isset( $_SESSION['leira-roles-flash-message'] ) ? $_SESSION['leira-roles-flash-message'] : array();
		foreach ( $messages as $type => $text ) {
			echo sprintf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $type, $text );
		}
		$_SESSION['leira-roles-flash-message'] = array();
	}

	/**
	 * Handle post actions
	 */
	public function handle_actions() {

		$actions = Leira_Roles::instance()->get_loader()->get( 'actions' );

		$actions->handle();
	}
}
