<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://github.com/arielhr1987
 * @since      1.0.0
 * @package    Leira_Roles
 * @subpackage Leira_Roles/admin
 * @author     Ariel <arielhr1987@gmail.com>
 */
class Leira_Roles_Manager{

	/**
	 * Returns an array of all the available roles.
	 * This method is used to show the roles list table.
	 *
	 * @return array[]
	 */
	public function get_roles_for_list_table() {
		$roles            = get_editable_roles();
		$count            = count_users(); //user count by role, TODO: Check performance when lot of user
		$all_capabilities = $this->get_all_capabilities();
		$res              = array();
		foreach ( $roles as $role => $detail ) {
			$capabilities = empty( $detail['capabilities'] ) ? array() : $detail['capabilities'];
			$capabilities = array_merge( $all_capabilities, $capabilities );
			$res[]        = array(
				'role'         => $role,
				'name'         => $detail['name'],
				'capabilities' => $capabilities,
				'count'        => isset( $count['avail_roles'][ $role ] ) ? $count['avail_roles'][ $role ] : 0,
				'is_system'    => $this->is_system_role( $role )
			);
		}

		return $res;
	}

	/**
	 * Array containing all default wordpress roles
	 *
	 * @return array
	 */
	public function get_system_roles() {

		$roles = array(
			'administrator',
			'editor',
			'author',
			'contributor',
			'subscriber'
		);

		$roles = apply_filters( 'leira-roles-get-system-roles', $roles );

		return $roles;
	}

	/**
	 * Checks if the given role is a system role
	 *
	 * @param $role
	 *
	 * @return bool
	 */
	public function is_system_role( $role ) {

		$is = in_array( $role, $this->get_system_roles() );

		$is = apply_filters( 'leira-roles-is-system-role', $is, $role );

		return $is;
	}

	/**
	 * Checks if he provided role exist
	 *
	 * @param $role
	 *
	 * @return bool
	 */
	public function is_role( $role ) {
		return wp_roles()->is_role( $role );
	}

	/**
	 * Get role object from role
	 *
	 * @param $role
	 *
	 * @return WP_Role|null
	 */
	public function get_role( $role ) {
		return wp_roles()->get_role( $role );
	}

	/**
	 * Get role name string form a role
	 *
	 * @param $role
	 *
	 * @return string
	 */
	public function get_role_name( $role ) {
		if ( $this->is_role( $role ) ) {
			return wp_roles()->role_names[ $role ];
		}

		return $role;
	}

	/**
	 * Clone a role into an other one. The new role identifier will contain a "-" followed by a number
	 *
	 * @param $role
	 *
	 * @return bool|WP_Role|null
	 */
	public function clone_role( $role ) {
		$clone = $this->get_role( $role );
		if ( ! ( $clone instanceof WP_Role ) ) {
			return false;
		}

		$temp_id = preg_replace( '/\-\d*$/i', '', $role );
		$id      = $temp_id;
		$i       = 0;
		while( $this->is_role( $id ) ){
			$i ++;
			$id = $temp_id . '-' . $i;
		}

		$name         = $this->get_role_name( $role );
		$name         = preg_replace( '/\-\d*$/i', '', $name ) . '-' . $i;
		$capabilities = is_array( $clone->capabilities ) ? $clone->capabilities : array();

		$result = add_role( $id, $name, $capabilities );

		return $result;
	}

	/**
	 * Add role to the system
	 *
	 * @param $role
	 * @param $name
	 *
	 * @return WP_Role|null
	 */
	public function add_role( $role, $name ) {
		$result = add_role( $role, $name );

		return $result;
	}

	/**
	 * Deletes a role from the system
	 *
	 * @param $role
	 *
	 * @return bool
	 */
	public function delete_role( $role ) {
		remove_role( $role );

		return ! $this->is_role( $role );
	}

	/**
	 * Update a role. If the role is a system role it will update the display name and capabilities only
	 *
	 * @param string $old_role     The role to update
	 * @param string $new_role     The new role identifier
	 * @param string $name         The new role display name
	 * @param array  $capabilities The capabilities for the role
	 *
	 * @return bool|WP_Role
	 */
	public function update_role( $old_role, $new_role, $name, $capabilities ) {

		if ( ! $this->is_role( $old_role ) || empty( $new_role ) || empty( $name ) ) {
			return false;
		}
		if ( ! is_array( $capabilities ) || empty( $capabilities ) ) {
			$capabilities = array();
		}
		$roles = wp_roles();

		/**
		 * Some validation to avoid messing with system roles, removing system capabilities or duplicating roles
		 */
		if ( $this->is_system_role( $old_role ) ) {
			//we can change capabilities and name
			$new_role = $old_role;

			if ( $new_role === 'administrator' ) {
				//dont disallow system capabilities for administrators
				$capabilities = array_merge( $capabilities, $this->get_system_capabilities( true ) );
			}
		} else if ( $old_role !== $new_role ) {
			if ( $this->is_role( $new_role ) ) {
				//we can't update to a new role that already exists. Avoids duplicate roles
				return false;
			}
			unset( $roles->roles[ $old_role ] );
		}

		/**
		 * Lets check if the capabilities provided by the user does not contain unknown capabilities
		 * Just in case someone tries to change the checkbox input value in the browser
		 */
		$capabilities = array_filter( $capabilities, function( $value, $key ) {
			return $this->is_capability( $key );
		}, ARRAY_FILTER_USE_BOTH );

		/**
		 * We need to make sure to set non system capabilities to false to preserve them.
		 * If we dont do it we may lost some capabilities
		 */
		$non_system_capabilities = $this->get_non_system_capabilities( false );
		$capabilities            = array_merge( $non_system_capabilities, $capabilities );

		/**
		 * Update roles manually
		 * Documented in /wp-includes/class-wp-roles.php
		 */
		$roles->roles[ $new_role ] = array(
			'name'         => $name,
			'capabilities' => $capabilities,
		);

		if ( $roles->use_db ) {
			update_option( $roles->role_key, $roles->roles );
		}
		$roles->role_objects[ $new_role ] = new WP_Role( $new_role, $capabilities );
		$roles->role_names[ $new_role ]   = $name;

		return $roles->role_objects[ $new_role ];
	}

	/**
	 * Update role capabilities. NOT IN USE
	 *
	 * @param string $role           The role to update capabilities
	 * @param array  $caps           The new capabilities to set
	 * @param bool   $remove_missing If $caps array is missing some capabilities, put those caps as false
	 *
	 * @return bool
	 */
	public function update_role_capabilities( $role, $caps, $remove_missing = true ) {

		$roles    = wp_roles();
		$role_obj = $roles->get_role( $role );
		$updated  = false;
		if ( $role_obj instanceof WP_Role ) {
			//make sure to make only the db request with the last capability
			$roles->use_db = false;
			//merge with the rest of the capabilities
			if ( $remove_missing ) {
				$caps = array_merge( $this->get_all_capabilities(), $caps );
			}
			$last_cap = key( array_slice( $caps, - 1, 1, true ) );
			foreach ( $caps as $cap => $grant ) {
				if ( $cap === $last_cap ) {
					//use db, next call to add_cap will generate a db query
					$roles->use_db = true;
				}
				$role_obj->add_cap( $cap, $grant );
			}
			//make sure we put it back to true
			$roles->use_db = true;

			$updated = true;
		}

		return $updated;
	}

	/**
	 * Get the list of capabilities to show in the capabilities table
	 *
	 * @return array
	 */
	public function get_capabilities_for_list_table() {

		$capabilities = $this->get_all_capabilities();
		$res          = array();
		foreach ( $capabilities as $capability => $grant ) {
			$res[] = array(
				'capability' => $capability,
				'name'       => $capability,
				'is_system'  => $this->is_system_capability( $capability )
			);
		}

		return $res;
	}

	/**
	 * Update a user capabilities.
	 *
	 * @param string|WP_User $user         The user to update capabilities
	 * @param array          $capabilities The new user capabilities
	 *
	 * @return bool
	 */
	public function update_user_capabilities( $user, $capabilities = array() ) {
		if ( is_string( $user ) || is_integer( $user ) ) {
			$user = get_user_by( 'ID', $user );
		}

		if ( ! $user instanceof WP_User ) {
			return false;
		}

		/**
		 * You are editing your own capabilities. Lets check that you dont break anything
		 */
		if ( $user == get_current_user() ) {

		}

		/**
		 * In multisite check if user belongs to the current site. Except if super admin is editing
		 */


		/**
		 * Remove roles from capabilities. Make sure we dont insert a role as capability
		 */
		$capabilities = array_filter( $capabilities, function( $cap ) {
			return ! $this->is_role( $cap );
		}, ARRAY_FILTER_USE_KEY );

		/**
		 * This array will contain the new array of capabilities
		 */
		$update = array();

		/**
		 * Get user role capabilities. All capabilities that the role allows or deny
		 */
		$role_capabilities = array();
		foreach ( $user->roles as $role ) {
			$role_obj = $this->get_role( $role );
			$caps     = isset( $role_obj->capabilities ) && is_array( $role_obj->capabilities ) ? $role_obj->capabilities : [];

			$role_capabilities = array_merge( $role_capabilities, $caps );

			//add roles by default
			$update[ $role ] = true;
		}

		foreach ( $role_capabilities as $role_capability => $role_value ) {
			/**
			 * Check is not a role
			 */
			if ( $this->is_role( $role_capability ) ) {
				continue;
			}

			if ( isset( $capabilities[ $role_capability ] ) ) {
				//the user checked a cb
				//lets check if was check or the user checked
				if ( $capabilities[ $role_capability ] === $role_value ) {
					//the user leave the cb checked as it was
				} else {
					//the user change the cb to checked
					$update[ $role_capability ] = ! $role_value;
				}

			} else {
				//the user did'nt check the cb
				//lets check if was uncheck or the user unchecked
				if ( $role_value === false ) {
					//the user leave the cb unchecked
				} else {
					//the user changed the cb to unchecked
					$update[ $role_capability ] = ! $role_value;

				}
			}
		}

		/**
		 * Handles the case of other role capabilities or user defined capabilities
		 */
		foreach ( $capabilities as $capability => $value ) {
			if ( ! isset( $role_capabilities[ $capability ] ) ) {

				$update[ $capability ] = true;
			}
		}

		//update user meta
		update_user_meta( $user->ID, $user->cap_key, $update );
		$user->caps = $update;
		$user->update_user_level_from_caps();
		$user->get_role_caps();

		return $user;
	}

	/**
	 * Get all available capabilities
	 *
	 * @param bool $default The default value for the capability
	 *
	 * @return array
	 */
	public function get_all_capabilities( $default = false ) {
		//System
		$system_capabilities = $this->get_system_capabilities( $default );

		//Role defined
		$role_capabilities = $this->get_role_capabilities( $default );

		//All
		$capabilities = array_merge( $system_capabilities, $role_capabilities );

		$capabilities = apply_filters( 'leira-roles-get-all-capabilities', $capabilities );

		return $capabilities;
	}

	/**
	 * @param bool $default
	 *
	 * @return array
	 */
	public function get_role_capabilities( $default = false ) {
		$wp_roles = wp_roles();
		//build full capabilities list from all roles
		$capabilities = array();
		foreach ( $wp_roles->roles as $role ) {
			// validate if capabilities is an array
			if ( isset( $role['capabilities'] ) && is_array( $role['capabilities'] ) ) {
				foreach ( $role['capabilities'] as $capability => $value ) {
					if ( ! isset( $capabilities[ $capability ] ) ) {
						$capabilities[ $capability ] = $default;
					}
				}
			}
		}
		$capabilities = apply_filters( 'leira-roles-get-role-capabilities', $capabilities );

		return $capabilities;
	}

	/**
	 * Get all system capabilities
	 *
	 * @param bool $default Default value for the system capabilities
	 *
	 * @return array
	 */
	public function get_system_capabilities( $default = false ) {
		$capabilities = array(
			'activate_plugins',
			'create_users',
			'delete_others_pages',
			'delete_others_posts',
			'delete_pages',
			'delete_plugins',
			'delete_posts',
			'delete_private_pages',
			'delete_private_posts',
			'delete_published_pages',
			'delete_published_posts',
			'delete_themes',
			'delete_users',
			'edit_dashboard',
			'edit_files',
			'edit_others_pages',
			'edit_others_posts',
			'edit_pages',
			'edit_plugins',
			'edit_posts',
			'edit_private_pages',
			'edit_private_posts',
			'edit_published_pages',
			'edit_published_posts',
			'edit_theme_options',
			'edit_themes',
			'edit_users',
			'export',
			'import',
			'install_plugins',
			'install_themes',
			'level_0',
			'level_1',
			'level_2',
			'level_3',
			'level_4',
			'level_5',
			'level_6',
			'level_7',
			'level_8',
			'level_9',
			'level_10',
			'list_users',
			'manage_categories',
			'manage_links',
			'manage_options',
			'moderate_comments',
			'promote_users',
			'publish_pages',
			'publish_posts',
			'read',
			'read_private_pages',
			'read_private_posts',
			'remove_users',
			'switch_themes',
			'unfiltered_html',
			'unfiltered_upload',
			'update_core',
			'update_plugins',
			'update_themes',
			'upload_files'
		);

		$capabilities = array_fill_keys( $capabilities, $default );

		$capabilities = apply_filters( 'leira-roles-get-system-capabilities', $capabilities );

		return $capabilities;
	}

	/**
	 * Get all capabilities that are not in the system by default
	 *
	 * @param bool $default Default value for the capabilities
	 *
	 * @return array
	 */
	public function get_non_system_capabilities( $default = false ) {
		$capabilities = $this->get_all_capabilities( $default );

		$capabilities = array_filter( $capabilities, function( $value, $cap ) {
			return ! $this->is_system_capability( $cap );
		}, ARRAY_FILTER_USE_BOTH );

		$capabilities = array_fill_keys( array_keys( $capabilities ), $default );

		return $capabilities;
	}

	/**
	 * Check if the given capability is a system capability
	 *
	 * @param $capability
	 *
	 * @return bool|mixed|void
	 */
	public function is_system_capability( $capability ) {
		$capabilities = $this->get_system_capabilities();
		$is           = isset( $capabilities[ $capability ] );

		$is = apply_filters( 'leira-roles-is-system-capability', $is, $capability );

		return $is;
	}

	/**
	 * Check if the capability exist
	 *
	 * @param string $capability
	 *
	 * @return bool
	 */
	public function is_capability( $capability ) {
		$capabilities = $this->get_all_capabilities();

		return isset( $capabilities[ $capability ] );
	}

	/**
	 * Add capability. The user defined capabilities is stored in the administrator role with a default values set to
	 * false. We stored in administrator role because this role is not deletable
	 *
	 * @param string $capability The capability to add
	 *
	 * @return bool
	 */
	public function add_capability( $capability ) {


		$administrator = $this->get_role( 'administrator' );

		if ( ! $administrator instanceof WP_Role ) {
			return false;
		}

		$administrator->add_cap( $capability, false );

		return true;
	}

	/**
	 * Delete the capabilities
	 *
	 * @param array $capabilities The array of capability to delete
	 *
	 * @return bool
	 */
	public function delete_capabilities( $capabilities ) {
		if ( ! is_array( $capabilities ) ) {
			return false;
		}

		$capabilities = array_filter( $capabilities, function( $cap ) {
			return ( $this->is_capability( $cap ) && ! $this->is_system_capability( $cap ) );
		} );

		if ( empty( $capabilities ) ) {
			return false;
		}

		$roles = wp_roles();
		foreach ( $roles->roles as $role => $item ) {
			foreach ( $capabilities as $capability ) {

				unset( $roles->roles[ $role ]['capabilities'][ $capability ] );
			}
		}

		if ( $roles->use_db ) {
			update_option( $roles->role_key, $roles->roles );
		}

		return true;
	}
}
