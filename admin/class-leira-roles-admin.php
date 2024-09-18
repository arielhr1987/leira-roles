<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/arielhr1987/leira-roles
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

		if ( 'users.php' === $hook ) {
			wp_enqueue_script(
				$this->plugin_name,
				plugin_dir_url( __FILE__ ) . 'js/inline-edit-user-capabilities.js',
				array(
					'jquery',
					'wp-a11y',
				),
				$this->version,
				false
			);

			// wp_localize_script( $this->plugin_name, 'leira_roles_i18n', leira_roles()->manager->system_capabilities );
		}

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/leira-roles-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Returns the admin list table instance
	 *
	 * @return Leira_Roles_List_Table
	 */
	public function get_roles_list_table() {
		if ( null === $this->roles_list_table ) {
			if ( ! class_exists( 'Leira_Roles_List_Table' ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'class-leira-roles-list-table.php';
			}
			$this->roles_list_table = new Leira_Roles_List_Table(
				array(
					'screen' => get_current_screen(),
				)
			);
		}

		return $this->roles_list_table;
	}

	/**
	 * Returns the admin list table instance for capabilities page
	 *
	 * @return Leira_Roles_Capabilities_List_Table
	 */
	public function get_capabilities_list_table() {
		if ( null === $this->capabilities_list_table ) {
			if ( ! class_exists( 'Leira_Roles_Capabilities_List_Table' ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'class-leira-roles-capabilities-list-table.php';
			}
			$this->capabilities_list_table = new Leira_Roles_Capabilities_List_Table(
				array(
					'screen' => get_current_screen(),
				)
			);
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
			array( $this, 'render_roles_admin_page' )
		);

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
			array( $this, 'render_capabilities_admin_page' )
		);

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

		if ( 'users_page_leira_roles_per_page' === $option ) {
			$value = (int) $value;
			if ( $value > 0 && $value < 1000 ) {
				return $value;
			}
		} elseif ( 'users_page_leira_roles_capabilities_per_page' === $option ) {
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
			$format = '<button type="button" class="button-link editinline_capabilities" aria-label="%s" aria-expanded="false">%s</button><div class="hidden" id="inline_capabilities_%s">%s</div>';
			/*
			 * translators: Quick edit the username availability
			 */
			$aria_label = esc_attr( sprintf( __( 'Quick edit %s capabilities', 'leira-roles' ), $username ) );
			$label      = __( 'Capabilities', 'leira-roles' );

			$capabilities = leira_roles()->manager->get_all_capabilities();
			if ( isset( $user->allcaps ) ) {
				// remove roles
				$allcaps      = array_filter(
					$user->allcaps,
					function( $cap ) {
						return ! leira_roles()->manager->is_role( $cap );
					},
					ARRAY_FILTER_USE_KEY
				);
				$capabilities = array_merge( $capabilities, $allcaps );
			}
			ksort( $capabilities, SORT_NATURAL | SORT_FLAG_CASE );
			$capabilities = wp_json_encode( $capabilities );

			$arr   = array(
				'user_id'      => $user->ID,
				'capabilities' => $capabilities,
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
			// Output the inline form
			global $wp_list_table;
			$columns_count = $wp_list_table->get_column_count();
			?>
            <form method="get">
                <table style="display: none">
                    <tbody id="inlineeditcapabilities">
                    <tr id="inline-edit-capabilities" class="inline-edit-row" style="display: none">
                        <td colspan="<?php echo esc_html( $columns_count ); ?>" class="colspanchange">

                            <fieldset class="">
                                <legend class="inline-edit-legend"><?php esc_html_e( 'Edit Capabilities', 'leira-roles' ); ?></legend>
                                <input type="hidden" name="user_id" value="">
                                <div class="inline-edit-col">
                                    <label>
                                        <span class="title"><?php esc_html_e( 'Capabilities', 'leira-roles' ); ?></span>
                                        <span class="input-text-wrap">
										<div class="wp-clearfix">
											<p class="search-box">
												<input type="search" name="capabilities_search_input"
                                                       placeholder="<?php esc_html_e( 'Search Capabilities', 'leira-roles' ); ?>">
											</p>
											<label class="alignleft">
												<input type="checkbox" class="cb-capabilities-select-all">
												<span class="checkbox-title"><?php esc_html_e( 'All', 'leira-roles' ); ?> </span>
											</label>
										</div>
										<div class="capabilities-container wp-clearfix">
											<div class="notice notice-error notice-alt inline hidden">
												<p class="error"><?php esc_html_e( 'No capabilities found.', 'leira-roles' ); ?> </p>
											</div>
										</div>
									</span>
                                    </label>

                                </div>
                            </fieldset>
                            <div class="inline-edit-save submit">
                                <button type="button" class="cancel button alignleft">
									<?php esc_html_e( 'Cancel', 'leira-roles' ); ?>
                                </button>
                                <button type="button"
                                        class="save button button-primary alignright">
									<?php esc_html_e( 'Save', 'leira-roles' ); ?>
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
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'leira-roles' ) );
		}
		?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'Roles', 'leira-roles' ); ?> </h1>
            <!--<a href="#" class="page-title-action">--><?php // esc_html_e( 'Add New', 'leira-roles' ) ?><!--</a>-->
			<?php
			if ( isset( $_REQUEST['s'] ) && $search = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) {
				/*
				 * translators: %s: search keywords
				 */
				printf( ' <span class="subtitle">' . esc_html__( 'Search results for &#8220;%s&#8221;', 'leira-roles' ) . '</span>', esc_html( $search ) );
			}

			// the roles table instance
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
                            <h2><?php esc_html_e( 'Add New Role', 'leira-roles' ); ?> </h2>
                            <form id="addrole" method="post" action="" class="validate">
                                <input type="hidden" name="action" value="leira-roles-add-role">
                                <input type="hidden" name="screen"
                                       value="<?php echo esc_html( get_current_screen()->id ); ?>">
								<?php
								wp_nonce_field( 'add-role' );
								?>

                                <div class="form-field form-required">
                                    <label for="role"><?php esc_html_e( 'Role', 'leira-roles' ); ?> </label>
                                    <input name="role" id="role" type="text" value="" size="40"
                                           aria-required="true" autocomplete="off">
                                    <p><?php esc_html_e( 'A unique identifier for the new role.', 'leira-roles' ); ?></p>
                                </div>
                                <div class="form-field form-required">
                                    <label for="name"><?php esc_html_e( 'Name', 'leira-roles' ); ?> </label>
                                    <input name="name" id="name" type="text" value="" size="40" autocomplete="off">
                                    <p><?php esc_html_e( 'The name is how it appears on your site.', 'leira-roles' ); ?></p>
                                </div>

                                <p class="submit">
                                    <input type="submit" name="submit" id="submit" class="button button-primary"
                                           value="<?php esc_html_e( 'Add New Role', 'leira-roles' ); ?> ">
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
                <div id="col-right">
                    <div class="col-wrap">
                        <form action="<?php echo esc_url( add_query_arg( '', '' ) ); ?>" method="post">
							<?php $table->display(); // Display the table ?>
                        </form>
                        <div class="form-wrap edit-term-notes">
                            <p><?php __( 'Description here.', 'leira-roles' ); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <form method="get">
				<?php $table->inline_edit(); ?>
            </form>

        </div>

		<?php
	}

	/**
	 * Roles page load
	 */
	public function admin_roles_page_load() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'leira-roles' ) );
		}

		// enqueue styles
		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/leira-roles-admin.css', array(), $this->version, 'all' );

		// enqueue scripts
		wp_enqueue_script( $this->plugin_name . '_common', plugin_dir_url( __FILE__ ) . 'js/leira-roles-common.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '_table_edit', plugin_dir_url( __FILE__ ) . 'js/leira-roles-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/inline-edit-roles.js',
			array(
				'jquery',
				'wp-a11y',
			),
			$this->version,
			false
		);

		// Localize
		// wp_localize_script( $this->plugin_name . '_table_edit', 'leira_roles_i18n', leira_roles()->manager->system_capabilities );
		// wp_localize_script( $this->plugin_name, 'leira_roles_i18n', leira_roles()->manager->system_capabilities );

		// initialize table here to be able to register default WP_List_Table screen options
		$this->get_roles_list_table();

		// Handle actions
		$this->handle_actions();

		// Add screen options
		add_screen_option( 'per_page', array( 'default' => 999 ) );

		// Add Help tabs
		$this->add_screen_help_tabs();

		get_current_screen()->add_help_tab(
			array(
				'id'      => 'screen-content',
				'title'   => esc_html__( 'Screen Content', 'leira-roles' ),
				'content' =>
					'<p>' . esc_html__( 'You can customize the display of this screen&#8217;s contents in a number of ways:', 'leira-roles' ) . '</p>' .
					'<ul>' .
					'<li>' . wp_kses_post( __( 'You can hide/display columns based on your needs and decide how many roles to list per screen using the <strong>Screen Options</strong> tab.', 'leira-roles' ) ) . '</li>' .
					'<li>' . wp_kses_post( __( 'The <strong>Search Roles</strong> button will search for roles containing the text you type in the box.', 'leira-roles' ) ) . '</li>' .
					'</ul>',
			)
		);
	}

	/**
	 * This method is responsible for render the Capabilities admin page
	 */
	public function render_capabilities_admin_page() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'leira-roles' ) );
		}
		?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'Capabilities', 'leira-roles' ); ?> </h1>
            <!--<a href="#" class="page-title-action">--><?php // esc_html_e( 'Add New', 'leira-roles' ) ?><!--</a>-->
			<?php
			if ( isset( $_REQUEST['s'] ) && $search = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) {
				/*
				 * translators: %s: search keywords
				 */
				printf( ' <span class="subtitle">' . esc_html__( 'Search results for &#8220;%s&#8221;', 'leira-roles' ) . '</span>', esc_html( $search ) );
			}

			// the roles table instance
			$table = $this->get_capabilities_list_table();
			$table->prepare_items();
			$this->admin_notices();
			?>
            <hr class="wp-header-end">
            <div id="ajax-response"></div>
            <form class="search-form wp-clearfix" method="get">
				<?php $table->search_box( esc_html__( 'Search Capabilities', 'leira-roles' ), 'roles' ); ?>
            </form>
            <div id="col-container" class="wp-clearfix">
                <div id="col-left">
                    <div class="col-wrap">
                        <div class="form-wrap">
                            <h2><?php esc_html_e( 'Add New Capability', 'leira-roles' ); ?> </h2>
                            <form id="add_capability" method="post" action="" class="validate">
                                <input type="hidden" name="action" value="leira-roles-add-capability">
                                <input type="hidden" name="screen"
                                       value="<?php echo esc_html( get_current_screen()->id ); ?>">
								<?php
								wp_nonce_field( 'add-capability' );
								?>

                                <div class="form-field form-required">
                                    <label for="capability"><?php esc_html_e( 'Capability', 'leira-roles' ); ?> </label>
                                    <input name="capability" id="capability" type="text" value="" size="40"
                                           aria-required="true">
                                    <p><?php esc_html_e( 'A unique identifier for the new capability.', 'leira-roles' ); ?></p>
                                </div>
                                <p class="submit">
                                    <input type="submit" name="submit" id="submit" class="button button-primary"
                                           value="<?php esc_html_e( 'Add New Capability', 'leira-roles' ); ?> ">
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
                <div id="col-right">
                    <div class="col-wrap">
                        <form action="<?php echo esc_url( add_query_arg( '', '' ) ); ?>" method="post">
							<?php $table->display(); // Display the table ?>
                        </form>
                        <div class="form-wrap edit-term-notes">
                            <p><?php esc_html_e( 'Built-in system capabilities are not deletable.', 'leira-roles' ); ?> </p>
                        </div>
                    </div>
                </div>
            </div>
            <form method="get">
				<?php $table->inline_edit(); ?>
            </form>

        </div>

		<?php
	}

	/**
	 * Capabilities page load
	 */
	public function admin_capabilities_page_load() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'leira-roles' ) );
		}

		// enqueue styles
		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/leira-roles-admin.css', array(), $this->version, 'all' );

		// enqueue scripts
		wp_enqueue_script( $this->plugin_name . '_common', plugin_dir_url( __FILE__ ) . 'js/leira-roles-common.js', array( 'jquery' ), $this->version, false );
		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/leira-roles-capabilities-admin.js', array( 'jquery' ), $this->version, false );

		// initialize table here to be able to register default WP_List_Table screen options
		$this->get_capabilities_list_table();

		// Handle actions
		$this->handle_actions();

		// Add screen options
		add_screen_option( 'per_page', array( 'default' => 999 ) );

		$this->add_screen_help_tabs();

		get_current_screen()->add_help_tab(
			array(
				'id'      => 'screen-content',
				'title'   => __( 'Screen Content', 'leira-roles' ),
				'content' =>
					'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:', 'leira-roles' ) . '</p>' .
					'<ul>' .
					'<li>' . __( 'The <strong>Search Capabilities</strong> button will search for capabilities containing the text you type in the box.', 'leira-roles' ) . '</li>' .
					'</ul>',
			)
		);
	}

	/**
	 * Display admin flash notices
	 */
	public function admin_notices() {
		echo wp_kses_post( leira_roles()->notify->display() );
	}

	/**
	 * Handle post actions
	 */
	public function handle_actions() {

		$actions = leira_roles()->actions;

		$actions->handle();
	}

	/**
	 * Add help tabs sections to plugin pages
	 */
	public function add_screen_help_tabs() {
		// Add screen Help tabs
		get_current_screen()->add_help_tab(
			array(
				'id'      => 'overview',
				'title'   => __( 'Overview', 'leira-roles' ),
				'content' =>
					'<p>' . __( 'WordPress uses a concept of Roles, designed to give the site owner the ability to control what users can and cannot do within the site.', 'leira-roles' ) . '</p>' .
					'<p>' . __( 'A site owner can manage the user access to such tasks as writing and editing posts, creating Pages, creating categories, moderating comments, managing plugins, managing themes, and managing other users, by assigning a specific role to each of the users.', 'leira-roles' ) . '</p>' .
					'<p>' . __( 'WordPress has six pre-defined roles: Super Admin, Administrator, Editor, Author, Contributor and Subscriber. Each role is allowed to perform a set of tasks called Capabilities.', 'leira-roles' ) . '</p>' .
					'<p>' . __( 'There are many capabilities including “publish_posts“, “moderate_comments“, and “edit_users“. A default set of capabilities is pre-assigned to each role, but other capabilities can be assigned or removed.', 'leira-roles' ) . '</p>' .
					( is_multisite() ? ( '<p>' . __( 'The Super Admin role allows a user to perform all possible capabilities. Each of the other roles has a decreasing number of allowed capabilities. For instance, the Subscriber role has just the “read” capability. One particular role should not be considered to be senior to another role. Rather, consider that roles define the user’s responsibilities within the site.', 'leira-roles' ) . '</p>' ) : '' ) .
					'',
			)
		);
		get_current_screen()->add_help_tab(
			array(
				'id'      => 'roles',
				'title'   => __( 'Roles', 'leira-roles' ),
				'content' =>
					'<p>' . __( 'Upon installing WordPress, an Administrator account is automatically created.', 'leira-roles' ) . '</p>' .
					( ! is_multisite() ? ( '<p>' . __( 'The default role for new users can be set in: ', 'leira-roles' ) . sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php' ), __( 'General Settings', 'leira-roles' ) ) . '</p>' ) : '' ) .
					'<p>' . __( 'WordPress comes with five predefined roles:', 'leira-roles' ) . '</p>' .
					'<ul>' .
					( is_multisite() ? ( '<li>' . sprintf( '<strong>%s</strong>', __( 'Super Admin', 'leira-roles' ) ) . __( ' : somebody with access to the site network administration features and all other features.', 'leira-roles' ) . '</li>' ) : '' ) .
					'<li>' . sprintf( '<strong>%s</strong> (slug: ‘administrator’)', __( 'Administrator', 'leira-roles' ) ) . __( ' : somebody who has access to all the administration features within a single site.', 'leira-roles' ) . '</li>' .
					'<li>' . sprintf( '<strong>%s</strong> (slug: ‘editor’)', __( 'Editor', 'leira-roles' ) ) . __( ' : somebody who can publish and manage posts including the posts of other users.', 'leira-roles' ) . '</li>' .
					'<li>' . sprintf( '<strong>%s</strong> (slug: ‘author’)', __( 'Author', 'leira-roles' ) ) . __( ' : somebody who can publish and manage their own posts.', 'leira-roles' ) . '</li>' .
					'<li>' . sprintf( '<strong>%s</strong> (slug: ‘contributor’)', __( 'Contributor', 'leira-roles' ) ) . __( ' : somebody who can write and manage their own posts but cannot publish them.', 'leira-roles' ) . '</li>' .
					'<li>' . sprintf( '<strong>%s</strong> (slug: ‘subscriber’)', __( 'Subscriber', 'leira-roles' ) ) . __( ' : somebody who can only manage their profile.', 'leira-roles' ) . '</li>' .
					'</ul>' .
					'',
			)
		);

		$capabilities        = '';
		$system_capabilities = leira_roles()->manager->system_capabilities;
		foreach ( $system_capabilities as $capability => $description ) {
			$capabilities .= sprintf( '<li><strong>%s</strong>: %s</li>', $capability, $description );
		}

		get_current_screen()->add_help_tab(
			array(
				'id'      => 'capabilities',
				'title'   => __( 'Capabilities', 'leira-roles' ),
				'content' =>
					'<p>' . __( 'Below is a list with all WordPress capabilities.', 'leira-roles' ) . '</p>' .
					'<div style="max-height: 300px; margin-bottom: 5px">' .
					'<ul>' .
					$capabilities .
					'</ul>' .
					'</div>' .
					'',
			)
		);

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'leira-roles' ) . '</strong></p>' .
			'<p><a href="https://wordpress.org/support/article/roles-and-capabilities/">' . __( 'Roles and Capabilities', 'leira-roles' ) . '</a></p>' .
			'<p><a href="https://wordpress.org/support/plugin/leira-roles/">' . __( 'Support', 'leira-roles' ) . '</a></p>' . // TODO: Change to github plugin page
			'<p><a href="https://github.com/arielhr1987/leira-roles/issues">' . __( 'Report an issue', 'leira-roles' ) . '</a></p>' .
			'<p><a href="https://github.com/arielhr1987/leira-roles/">' . __( 'Development', 'leira-roles' ) . '</a></p>'
		);
	}

	/**
	 * Add help roles capabilities
	 */
	public function load_users_page() {

		// TODO: Improve texts
		get_current_screen()->add_help_tab(
			array(
				'id'       => 'leira-roles-capabilities',
				'title'    => __( 'Capabilities', 'leira-roles' ),
				'content'  =>
					'<p>' . __( 'The <strong>Capabilities</strong> button will help you to allow or revoke specific capabilities for the user.', 'leira-roles' ) . '</p>' .
					'<p>' . __( 'Be aware that if you revoke your user capabilities, you might experience some issues.', 'leira-roles' ) . '</p>' .
					'',
				'priority' => 100,
			)
		);
	}

	/**
	 * Change the admin footer text on Settings page
	 * Give us a rate
	 *
	 * @param $footer_text
	 *
	 * @return string
	 * @since 1.1.3
	 */
	public function admin_footer_text( $footer_text ) {
		$current_screen = get_current_screen();

		// Pages where we are going to show footer review
		$pages = array(
			'users_page_leira-roles',
			'users_page_leira-roles-capabilities',
		);

		if ( isset( $current_screen->id ) && in_array( $current_screen->id, $pages ) ) {
			// Change the footer text
			if ( ! get_option( 'leira-roles-footer-rated' ) ) {

				ob_start();
				?>
                <a href="https://wordpress.org/support/plugin/leira-roles/reviews/?filter=5" target="_blank"
                   class="leira-roles-admin-rating-link"
                   data-rated="<?php esc_attr_e( 'Thanks :)', 'leira-roles' ); ?>"
                   data-nonce="<?php echo esc_html( wp_create_nonce( 'footer-rated' ) ); ?>">&#9733;&#9733;&#9733;&#9733;&#9733;</a>
				<?php
				$link = ob_get_clean();

				ob_start();
				/*
				 * translators: Leave a review for the plugin
				 */
				printf( esc_html__( 'If you like Roles & Capabilities please consider leaving a %s review. It will help us to grow the plugin and make it more popular. Thank you.', 'leira-roles' ), wp_kses_post( $link ) )
				?>

				<?php
				$footer_text = ob_get_clean();
			}
		}

		return $footer_text;
	}
}
