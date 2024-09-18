<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://github.com/arielhr1987/leira-roles
 * @since      1.0.0
 * @package    Leira_Roles
 * @subpackage Leira_Roles/admin
 * @author     Ariel <arielhr1987@gmail.com>
 */
class Leira_Roles_Actions{

	/**
	 * @var string
	 */
	protected $capability = 'manage_options';

	/**
	 * @var Leira_Roles_Manager
	 */
	protected $manager = null;

	/**
	 * @var array
	 */
	protected $actions = array(
		'leira-roles-add-role',
		'leira-roles-clone-role',
		'leira-roles-delete-role',
		'leira-roles-quick-edit-role',
		'leira-roles-quick-edit-user-capabilities',
		'leira-roles-add-capability',
		'leira-roles-delete-capability',
	);

	/**
	 * Leira_Roles_Actions constructor.
	 */
	public function __construct() {
		$this->manager = leira_roles()->manager;
	}

	/**
	 * Is ajax request
	 *
	 * @return bool
	 */
	protected function is_ajax() {
		return ( defined( 'DOING_AJAX' ) && DOING_AJAX );
	}

	/**
	 * Handle post actions
	 */
	public function handle() {
		$current_action = $this->current_action();

		if ( in_array( $current_action, $this->actions ) ) {
			$current_action = str_replace( 'leira-roles-', '', $current_action );
			$current_action = str_replace( '-', '_', $current_action );
			$this->$current_action();
		}
	}

	/**
	 * Get the current action selected from the bulk actions' dropdown.
	 *
	 * @return string|false The action name or False if no action was selected
	 */
	protected function current_action() {
		if ( array_key_exists( 'filter_action', $_REQUEST ) && ! empty( $_REQUEST['filter_action'] ) ) {
			return false;
		}

		if ( isset( $_REQUEST['action'] ) && - 1 != $_REQUEST['action'] ) {
			return sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
		}

		if ( isset( $_REQUEST['action2'] ) && - 1 != $_REQUEST['action2'] ) {
			return sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) );
		}

		return false;
	}

	/**
	 * Notify the user with a message. Handles ajax and post requests.
	 *
	 * @param string $message  The message to show to the user.
	 * @param string $type     The type of message to show [error|success|warning\info].
	 * @param bool   $redirect If we should redirect to referrer.
	 */
	protected function notify( $message, $type = 'error', $redirect = true ) {

		if ( ! in_array( $type, array( 'error', 'success', 'warning' ) ) ) {
			$type = 'error';
		}

		if ( $this->is_ajax() ) {
			$format = '<div class="notice notice-%s is-dismissible"><p>%s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . __( 'Dismiss this notice.', 'leira-roles' ) . '</span></button></div>';
			$format = '<div class="notice notice-%s is-dismissible"><p>%s</p></div>';
			wp_send_json_error( sprintf( $format, $type, $message ) );
		} else {
			// enqueue message
			leira_roles()->notify->add( $type, $message );

			$redirect_url = wp_get_referer();
			$redirect_url = wp_get_raw_referer();
			if ( empty( $redirect_url ) ) {
				$params       = array(
					'page' => 'leira-roles', // TODO: redirect to capabilities page too
				);
				$redirect_url = esc_url( add_query_arg( $params, admin_url( 'users.php' ) ) );
			}
			if ( $redirect ) {
				wp_safe_redirect( $redirect_url );
				die();
			}
		}
	}

	/**
	 * Check if the user is able to access this page
	 */
	protected function check_permissions() {

		if ( ! current_user_can( $this->capability ) ) {
			$out = __( 'You do not have sufficient permissions to perform this action.', 'leira-roles' );
			$this->notify( $out );
		}
	}

	/**
	 * Check nonce and notify if error
	 *
	 * @param string $action
	 * @param string $query_arg
	 */
	protected function check_nonce( $action = '-1', $query_arg = '_wpnonce' ) {

		$checked = isset( $_REQUEST[ $query_arg ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ $query_arg ] ) ), $action );
		if ( ! $checked ) {
			$out = __( 'Your link has expired, refresh the page and try again.', 'leira-roles' );
			$this->notify( $out );
		}
	}

	/**
	 * Handles add role action
	 */
	public function add_role() {
		/**
		 * Check capabilities
		 */
		$this->check_permissions();

		/**
		 * Check nonce
		 */
		$this->check_nonce( 'add-role' );

		/**
		 * Validate input data
		 */
		$role = isset( $_REQUEST['role'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['role'] ) ) : false;
		$role = str_replace( ' ', '', $role ); // Remove spaces
		$name = isset( $_REQUEST['name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['name'] ) ) : false;

		if ( empty( $role ) || empty( $name ) ) {
			$out = __( 'Missing parameters, refresh the page and try again.', 'leira-roles' );
			$this->notify( $out );
		}

		/**
		 * Check role doesn't exist
		 */
		if ( $this->manager->is_role( $role ) ) {
			// this role already exists
			/*
			 * translators: The name of the role
			 */
			$out = wp_kses_post( sprintf( __( 'The role <strong>%s</strong> already exist, use another role identifier.', 'leira-roles' ), $role ) );
			$this->notify( $out );
		}

		/**
		 * Add role
		 */
		$result = $this->manager->add_role( $role, $name );

		if ( ! $result instanceof WP_Role ) {
			$out = __( 'Something went wrong, the system wasn\'t able to create the role, refresh the page and try again.', 'leira-roles' );
			$this->notify( $out );
		}

		if ( $this->is_ajax() ) {
			/**
			 * The role row
			 */
			$count_users = count_users();
			ob_start();
			$GLOBALS['hook_suffix'] = '';// avoid warning outputs
			$all_capabilities       = $this->manager->get_all_capabilities();
			$capabilities           = isset( $result->capabilities ) ? $result->capabilities : array();
			$capabilities           = array_merge( $all_capabilities, $capabilities );
			leira_roles()->admin->get_roles_list_table()->single_row(
				array(
					'role'         => $result->name,
					'name'         => $this->manager->get_role_name( $result->name ),
					'count'        => isset( $count_users['avail_roles'][ $result->name ] ) ? $count_users['avail_roles'][ $result->name ] : 0,
					'capabilities' => $capabilities,
					'is_system'    => $this->manager->is_system_role( $result->name ),
				)
			);
			$out = ob_get_clean();

			wp_send_json_success( $out );
		} else {
			/**
			 * Notify user and redirect
			 */
			/*
			 * translators: the role name
			 */
			$out = wp_kses_post( sprintf( __( 'The new role <strong>%s</strong> was created successfully.', 'leira-roles' ), $role ) );
			$this->notify( $out, 'success' );
		}
	}

	/**
	 * Handles clone role post request
	 */
	public function clone_role() {
		/**
		 * Check capabilities
		 */
		$this->check_permissions();

		/**
		 * Check nonce
		 */
		$this->check_nonce( 'bulk-roles' );

		/**
		 * Validate input data
		 */
		$role = isset( $_REQUEST['role'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['role'] ) ) : false;

		if ( empty( $role ) ) {
			$out = __( 'Missing parameters, refresh the page and try again.', 'leira-roles' );
			$this->notify( $out );
		}

		/**
		 * Check role doesn't exist
		 */
		if ( ! $this->manager->is_role( $role ) ) {
			// this role doesn't exist
			/*
			 * translators: the name of the role
			 */
			$out = wp_kses_post( sprintf( __( 'The role <strong>%s</strong> doesn\'t exist.', 'leira-roles' ), $role ) );
			$this->notify( $out );
		}
		/**
		 * Clone the role
		 */
		$result = $this->manager->clone_role( $role );

		if ( ! $result instanceof WP_Role ) {
			$this->notify( esc_html__( 'Something went wrong, the system wasn\'t able to create the role, refresh the page and try again.', 'leira-roles' ) );
		}

		if ( $this->is_ajax() ) {
			/**
			 * The role row
			 */
			$admin       = leira_roles()->admin;
			$count_users = count_users();
			ob_start();
			$GLOBALS['hook_suffix'] = '';// avoid warning outputs
			$all_capabilities       = $this->manager->get_all_capabilities();
			$capabilities           = isset( $result->capabilities ) ? $result->capabilities : array();
			$capabilities           = array_merge( $all_capabilities, $capabilities );
			$admin->get_roles_list_table()->single_row(
				array(
					'role'         => $result->name,
					'name'         => $this->manager->get_role_name( $result->name ),
					'count'        => isset( $count_users['avail_roles'][ $result->name ] ) ? $count_users['avail_roles'][ $result->name ] : 0,
					'capabilities' => $capabilities,
					'is_system'    => $this->manager->is_system_role( $result->name ),
				)
			);
			$out = ob_get_clean();

			wp_send_json_success( $out );
		} else {
			/**
			 * Notify user and redirect
			 */
			$from     = '<strong>' . $role . '</strong>';
			$to       = '<strong>' . $result->name . '</strong>';
			$undo_url = esc_url( add_query_arg(
				array(
					'page'     => 'leira-roles',
					'action'   => 'leira-roles-delete-role',
					'role'     => esc_attr( $result->name ),
					'_wpnonce' => wp_create_nonce( 'bulk-roles' ),
				),
				admin_url( 'users.php' )
			) );
			$undo     = sprintf( '<a href="%s">%s</a>', $undo_url, __( 'Undo', 'leira-roles' ) );
			/*
			 * translators: The role name to clone, into the role. Undo link
			 */
			$out = sprintf( __( 'The role %1$s was cloned successfully into %2$s. %3$s', 'leira-roles' ), $from, $to, $undo );
			$this->notify( $out, 'success' );
		}
	}

	/**
	 *
	 */
	public function delete_role() {
		/**
		 * Check capabilities
		 */
		$this->check_permissions();

		/**
		 * Check nonce
		 */
		$this->check_nonce( 'bulk-roles' );

		/**
		 * Validate input data
		 */
		$roles = array();
		if ( isset( $_REQUEST['role'] ) ) {
			if ( is_string( $_REQUEST['role'] ) ) {
				$input   = sanitize_text_field( wp_unslash( $_REQUEST['role'] ) );
				$roles[] = $input;
			} elseif ( is_array( $_REQUEST['role'] ) ) {
				$roles = array_map( 'sanitize_text_field', $_REQUEST['role'] );
				$roles = array_map( 'wp_unslash', $roles );
			}
		} else {
			return;
		}

		/**
		 * If no roles provided return
		 */
		if ( empty( $roles ) ) {
			$this->notify( esc_html__( 'Missing parameters, refresh the page and try again.', 'leira-roles' ) );
		}

		/**
		 * Check if is a system role
		 */
		foreach ( $roles as $key => $role ) {

			if ( $this->manager->is_system_role( $role ) ) {
				unset( $roles[ $key ] );
			}
		}

		if ( empty( $roles ) ) {
			$out = __( 'Deleting a system role is not allowed.', 'leira-roles' );
			$this->notify( $out );
		}

		/**
		 * Delete roles
		 */
		foreach ( $roles as $role ) {
			$deleted = $this->manager->delete_role( $role );
		}

		/*
		 * translators: The role or the number of roles deleted
		 */
		$out = _n(
			'The role <strong>%s</strong> was successfully deleted.',
			'The selected <strong>%s</strong> roles were successfully deleted.',
			count( $roles ),
			'leira-roles'
		);
		$out = sprintf( $out, count( $roles ) > 1 ? count( $roles ) : $roles[0] );
		if ( $this->is_ajax() ) {
			wp_send_json_success( $out );
		} else {
			$this->notify( $out, 'success' );
		}
	}

	/**
	 * Handle Quick Edit post request
	 */
	public function quick_edit_role() {
		$format = '<div class="notice notice-%1$s is-dismissible"><p class="%1$s">%2$s</p></div>';
		/**
		 * Check capabilities
		 */
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'leira-roles' ) );
		}

		/**
		 * Check nonce
		 */
		$checked = isset( $_REQUEST['_inline_edit'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_inline_edit'] ) ), 'roleinlineeditnonce' );
		if ( ! $checked ) {
			wp_die( esc_html__( 'Your link has expired, refresh the page and try again.', 'leira-roles' ) );
		}

		/**
		 * Validate input data
		 * We use inline_role to avoid conflicts with role table checkbox column
		 */
		$old_role = isset( $_REQUEST['old_role'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['old_role'] ) ) : false;
		$new_role = isset( $_REQUEST['new_role'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['new_role'] ) ) : false;
		$name     = isset( $_REQUEST['name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['name'] ) ) : false;

		if ( empty( $old_role ) || empty( $new_role ) || empty( $name ) ) {
			wp_die( esc_html__( 'Missing parameters, refresh the page and try again.', 'leira-roles' ) );
		}

		/**
		 * Sanitize capabilities
		 */
		$input_capabilities = ( array_key_exists( 'capability', $_REQUEST ) && is_array( $_REQUEST['capability'] ) ) ? $_REQUEST['capability'] : array();
		$capabilities       = array();
		foreach ( $input_capabilities as $capability ) {
			$capabilities[ sanitize_text_field( wp_unslash( $capability ) ) ] = true;
		}

		/**
		 * Some extra checks
		 */
		if ( $this->manager->is_system_role( $old_role ) ) {

		} elseif ( $old_role !== $new_role ) {
			if ( $this->manager->is_role( $new_role ) ) {
				wp_die( esc_html__( 'The provided new role, already exist. Use an other role identifier.', 'leira-roles' ) );
			}
		}

		$result = $this->manager->update_role( $old_role, $new_role, $name, $capabilities );

		/**
		 * Something went wrong
		 */
		if ( false === $result ) {
			wp_die( esc_html__( 'Something went wrong, the system wasn\'t able to update the role, refresh the page and try again.', 'leira-roles' ) );
		}

		/**
		 * Output the row table with the new updated data
		 */
		$admin                  = leira_roles()->admin;
		$GLOBALS['hook_suffix'] = '';// avoid notice error
		$table                  = $admin->get_roles_list_table();
		$count                  = count_users();

		$table->single_row(
			array(
				'role'         => $result->name,
				'name'         => $name,
				'capabilities' => array_merge( $this->manager->get_all_capabilities(), $capabilities ),
				'count'        => isset( $count['avail_roles'][ $new_role ] ) ? $count['avail_roles'][ $new_role ] : 0,
				'is_system'    => $this->manager->is_system_role( $new_role ),
			)
		);
		wp_die();
	}

	/**
	 *
	 */
	public function quick_edit_user_capabilities() {
		/**
		 * Check capabilities
		 */
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'leira-roles' ) );
		}

		/**
		 * Check nonce
		 */
		$checked = array_key_exists( '_inline_edit', $_REQUEST ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_inline_edit'] ) ), 'usercapabilitiesinlineeditnonce' );
		if ( ! $checked ) {
			wp_die( esc_html__( 'Your link has expired, refresh the page and try again.', 'leira-roles' ) );
		}

		/**
		 * Check user exist
		 */
		$user_id = array_key_exists( 'user_id', $_REQUEST ) ? sanitize_text_field( wp_unslash( $_REQUEST['user_id'] ) ) : false;
		$user    = get_user_by( 'id', $user_id );
		if ( ! $user instanceof WP_User ) {
			wp_die( esc_html__( 'User not found, refresh the page and try again.', 'leira-roles' ) );
		}

		/**
		 * You are editing your own capabilities. Lets check that you dont break anything
		 */
		if ( get_current_user_id() == $user->ID ) {
			// What do we do here??? TODO: Lets think about it.
		}

		/**
		 * In multisite check if the user to edit is member of the site
		 */
		if ( is_multisite() ) {
			if ( ! is_user_member_of_blog( $user->ID ) ) {
				wp_die( esc_html__( 'You are not allowed to edit other site users.', 'leira-roles' ) );
			}
		}

		/**
		 * Sanitize capabilities
		 */
		$input_capabilities = ( array_key_exists( 'capability', $_REQUEST ) && is_array( $_REQUEST['capability'] ) ) ? $_REQUEST['capability'] : array();
		$capabilities       = array();
		foreach ( $input_capabilities as $capability ) {
			$capabilities[ sanitize_text_field( $capability ) ] = true;
		}

		/**
		 * Update user capabilities
		 */
		$user = $this->manager->update_user_capabilities( $user, $capabilities );

		/**
		 * Notify user and redirect
		 */
		if ( false === $user ) {
			wp_die( esc_html__( 'Something went wrong, the system wasn\'t able to save the user capabilities, refresh the page and try again.', 'leira-roles' ) );
		}

		/**
		 * Output the row table with the new updated data
		 */
		$GLOBALS['hook_suffix'] = '';// avoid notice error
		$table                  = _get_list_table( 'WP_Users_List_Table' );

		$table->single_row( $user );
		wp_die();
	}

	/**
	 * Handles add role action
	 */
	public function add_capability() {
		/**
		 * Check capabilities
		 */
		$this->check_permissions();

		/**
		 * Check nonce
		 */
		$this->check_nonce( 'add-capability' );

		/**
		 * Validate input data
		 */
		$capability = isset( $_REQUEST['capability'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['capability'] ) ) : false;
		$capability = str_replace( ' ', '', $capability ); // Remove spaces
		$name       = isset( $_REQUEST['name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['name'] ) ) : false;

		if ( empty( $capability ) ) {
			$this->notify( esc_html__( 'Missing parameters, refresh the page and try again.', 'leira-roles' ) );
		}

		/**
		 * Check you are not adding a role as capability
		 */
		if ( $this->manager->is_role( $capability ) ) {
			// this capability already exists
			$this->notify( esc_html__( 'You can\'t add a capability with the same identifier as a role, use other capability identifier.', 'leira-roles' ) );
		}

		/**
		 * Check capability doesn't exist
		 */
		if ( $this->manager->is_capability( $capability ) ) {
			// this capability already exists
			/*
			 * translators: the name of the capability
			 */
			$out = sprintf( esc_html__( 'The capability %s already exist, use other capability identifier.', 'leira-roles' ), '<strong>' . esc_attr( $capability ) . '</strong>' );
			$this->notify( $out );
		}

		/**
		 * Add capability
		 */
		$result = $this->manager->add_capability( $capability );

		if ( ! $result ) {
			$out = esc_html__( 'Something went wrong, the system wasn\'t able to create the capability, refresh the page and try again.', 'leira-roles' );
			$this->notify( $out );
		}

		if ( $this->is_ajax() ) {
			/**
			 * The capability row
			 */
			$admin = leira_roles()->admin;
			ob_start();
			$GLOBALS['hook_suffix'] = '';// avoid warning outputs
			$admin->get_capabilies_list_table()->single_row(
				array(
					'capability' => $capability,
					'name'       => $name,
					'is_system'  => $this->manager->is_system_capability( $capability ),
				)
			);
			$out = ob_get_clean();

			wp_send_json_success( $out );
		} else {
			/**
			 * Notify user and redirect
			 */
			/*
			 * translators: the name of the capability
			 */
			$out = sprintf( esc_html__( 'The new capability %s was created successfully.', 'leira-roles' ), '<strong>' . $capability . '</strong>' );
			$this->notify( $out, 'success' );
		}
	}

	/**
	 * Handles delete role action
	 *
	 * @access public
	 */
	public function delete_capability() {
		/**
		 * Check capabilities
		 */
		$this->check_permissions();

		/**
		 * Check nonce
		 */
		$this->check_nonce( 'bulk-capabilities' );

		/**
		 * Validate input data
		 */
		$input_capabilities = ( isset( $_REQUEST['capability'] ) && is_array( $_REQUEST['capability'] ) ) ? $_REQUEST['capability'] : array();
		$capabilities       = array();
		foreach ( $input_capabilities as $capability ) {
			$capabilities[] = sanitize_text_field( wp_unslash($capability) );
		}

		if ( empty( $capabilities ) ) {
			$out = esc_html__( 'Select valid capabilities and try again.', 'leira-roles' );
			$this->notify( $out );
		}

		/**
		 * Delete capabilities
		 */
		$result = $this->manager->delete_capabilities( $capabilities );

		if ( ! $result ) {
			$out = esc_html__( 'Something went wrong, the system wasn\'t able to delete the selected capabilities, refresh the page and try again.', 'leira-roles' );
			$this->notify( $out );
		}

		/**
		 * Notify the user
		 */
		/*
		 * translators: The capability or the number of capabilities deleted
		 */
		$out = _n(
			'The capability <strong>%s</strong> was successfully deleted.',
			'The selected <strong>%s</strong> capabilities were successfully deleted.',
			count( $capabilities ),
			'leira-roles'
		);
		$out = sprintf( $out, count( $capabilities ) > 1 ? count( $capabilities ) : $capabilities[0] );
		if ( $this->is_ajax() ) {
			wp_send_json_success( $out );
		} else {
			$this->notify( $out, 'success' );
		}
	}

	/**
	 * When user clicks the review link in backend
	 *
	 * @since  1.1.3
	 * @access public
	 */
	public function footer_rated() {
		/**
		 * Check capabilities
		 */
		$this->check_permissions();

		/**
		 * Check nonce
		 */
		$this->check_nonce( 'footer-rated' );

		update_option( 'leira-roles-footer-rated', 1 );
		wp_send_json_success();
	}
}
