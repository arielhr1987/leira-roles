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
class Leira_Roles_Manager {

	/**
	 * Built-in system capabilities defined by WordPress and the corresponding description
	 *
	 * @var array
	 */
	public $system_capabilities = array();

	/**
	 * Leira_Roles_Manager constructor.
	 */
	public function __construct() {

		$this->system_capabilities = array(
			'activate_plugins'       => __( 'Allow users to activate plugins.', 'leira-roles' ),
			'create_users'           => __( 'Allow users to create users within the site.', 'leira-roles' ),
			'delete_others_pages'    => __( 'Enables permission to delete others pages.', 'leira-roles' ),
			'delete_others_posts'    => __( 'Enables permission to delete others posts.', 'leira-roles' ),
			'delete_pages'           => __( 'Enables permission to delete pages.', 'leira-roles' ),
			'delete_plugins'         => __( 'Allow users to delete plugins.', 'leira-roles' ),
			'delete_posts'           => __( 'Enables permission to delete posts.', 'leira-roles' ),
			'delete_private_pages'   => __( 'Enables permission to delete pages marked as private.', 'leira-roles' ),
			'delete_private_posts'   => __( 'Enables permission to delete posts marked as private.', 'leira-roles' ),
			'delete_published_pages' => __( 'Enables permission to delete published pages.', 'leira-roles' ),
			'delete_published_posts' => __( 'Enables permission to delete published posts.', 'leira-roles' ),
			'delete_themes'          => __( 'Allows access to delete themes.', 'leira-roles' ),
			'delete_users'           => __( 'Allow users to delete users within the site.', 'leira-roles' ),
			'edit_dashboard'         => __( 'Allows access to edit dashboard widgets and its settings.', 'leira-roles' ),
			'edit_files'             => __( 'Deprecated', 'leira-roles' ),
			'edit_others_pages'      => __( 'Enables permission to edit others pages.', 'leira-roles' ),
			'edit_others_posts'      => __( 'Enables permission to edit others posts.', 'leira-roles' ),
			'edit_pages'             => __( 'Enables permission to edit pages.', 'leira-roles' ),
			'edit_plugins'           => __( 'Allow users to edit plugin files.', 'leira-roles' ),
			'edit_posts'             => __( 'Allows access to “Posts”, “Posts > Add New”, “Comments” and “Comments > Awaiting Moderation”', 'leira-roles' ),
			'edit_private_pages'     => __( 'Enables permission to edit pages marked as private.', 'leira-roles' ),
			'edit_private_posts'     => __( 'Enables permission to edit posts marked as private.', 'leira-roles' ),
			'edit_published_pages'   => __( 'Enables permission to edit published pages.', 'leira-roles' ),
			'edit_published_posts'   => __( 'Enables permission to edit published posts.', 'leira-roles' ),
			'edit_theme_options'     => __( 'Allow access to “Widgets”, “Menus”, “Customize”, “Background” and “Header” under “Appearance”.', 'leira-roles' ),
			'edit_themes'            => __( 'Allows access to “Appearance > Theme Editor” to edit theme files.', 'leira-roles' ),
			'edit_users'             => __( 'Allow users to edit users within the site.', 'leira-roles' ),
			'export'                 => __( 'Allows access to “Tools > Export”.', 'leira-roles' ),
			'import'                 => __( 'Allows access to “Tools > Import”.', 'leira-roles' ),
			'install_plugins'        => __( 'Allow users to install new plugins.', 'leira-roles' ),
			'install_themes'         => __( 'Allows access to install themes.', 'leira-roles' ),
			'level_0'                => __( 'Deprecated', 'leira-roles' ),
			'level_1'                => __( 'Deprecated', 'leira-roles' ),
			'level_2'                => __( 'Deprecated', 'leira-roles' ),
			'level_3'                => __( 'Deprecated', 'leira-roles' ),
			'level_4'                => __( 'Deprecated', 'leira-roles' ),
			'level_5'                => __( 'Deprecated', 'leira-roles' ),
			'level_6'                => __( 'Deprecated', 'leira-roles' ),
			'level_7'                => __( 'Deprecated', 'leira-roles' ),
			'level_8'                => __( 'Deprecated', 'leira-roles' ),
			'level_9'                => __( 'Deprecated', 'leira-roles' ),
			'level_10'               => __( 'Deprecated', 'leira-roles' ),
			'list_users'             => __( 'Allow users to list users within the site', 'leira-roles' ),
			'manage_categories'      => __( 'Enables permission to “Posts > Categories” and “Links > Categories”( Links not available since v3.5).', 'leira-roles' ),
			'manage_links'           => __( 'Used by the Link Manager in WordPress. Since WordPress version 3.0 Link Manager is not available', 'leira-roles' ),
			'manage_options'         => __( 'Allows access to “Settings” section', 'leira-roles' ),
			'moderate_comments'      => __( 'Allow users to moderate comments through the “Comments” menu. But it also requires edit_posts capability.', 'leira-roles' ),
			'promote_users'          => __( 'Allow users to promote users within the site.', 'leira-roles' ),
			'publish_pages'          => __( 'Enables permission to publish pages.', 'leira-roles' ),
			'publish_posts'          => __( 'Allows access to publish posts, including XML - RPC publish', 'leira-roles' ),
			'read'                   => __( 'Allows access to menu items “Dashboard” and “Users > Your Profile”.', 'leira-roles' ),
			'read_private_pages'     => __( 'Enables permission to read pages marked as private.', 'leira-roles' ),
			'read_private_posts'     => __( 'Enables permission to read posts marked as private.', 'leira-roles' ),
			'remove_users'           => __( 'Not used', 'leira-roles' ),
			'switch_themes'          => __( 'Allows access to “Appearance” and “Appearance > Theme Editor” menus.', 'leira-roles' ),
			'unfiltered_html'        => __( 'Allows the user to post any HTML data including JavaScript. In WordPress Multisite, only Super Admins have this capability', 'leira-roles' ),
			'unfiltered_upload'      => __( 'This capability is not available to any role by default ( including Super Admins). The capability needs to be enabled by defining the following constant in wp-config. define( ‘ALLOW_UNFILTERED_UPLOADS’ )', 'leira-roles' ),
			'update_core'            => __( 'Allows to upgrade WordPress core.', 'leira-roles' ),
			'update_plugins'         => __( 'Allow users to install new plugins.', 'leira-roles' ),
			'update_themes'          => __( 'Allows access to update themes.', 'leira-roles' ),
			'upload_files'           => __( 'Enables permission to “Media” and “Media > Add New”.', 'leira-roles' ),
			'edit_comment'           => __( 'edit_comment is a meta capability . It gets re-mapped to another meta capability.', 'leira-roles' ),
			'add_users'              => __( 'Not used', 'leira-roles' ),
		);
	}

	/**
	 * Returns an array of all the available roles.
	 * This method is used to show the roles list table.
	 *
	 * @return array[]
	 */
	public function get_roles_for_list_table() {
		$roles            = get_editable_roles();
		$count            = count_users(); // user count by role, TODO: Check performance when lot of user
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
				'is_system'    => $this->is_system_role( $role ),
			);
		}

		return $res;
	}

	/**
	 * Array containing all default WordPress roles
	 *
	 * @return array
	 */
	public function get_system_roles() {

		$roles = array(
			'administrator',
			'editor',
			'author',
			'contributor',
			'subscriber',
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
	 * Clone a role into another one. The new role identifier will contain a "-" followed by a number
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
		while ( $this->is_role( $id ) ) {
			++$i;
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
			// we can change capabilities and name
			$new_role = $old_role;

			if ( 'administrator' === $new_role ) {
				// dont disallow system capabilities for administrators
				$capabilities = array_merge( $capabilities, $this->get_system_capabilities( true ) );
			}
		} elseif ( $old_role !== $new_role ) {
			if ( $this->is_role( $new_role ) ) {
				// we can't update to a new role that already exists. Avoids duplicate roles
				return false;
			}
			unset( $roles->roles[ $old_role ] );
		}

		/**
		 * Lets check if the capabilities provided by the user does not contain unknown capabilities
		 * Just in case someone tries to change the checkbox input value in the browser
		 */
		$capabilities = array_filter(
			$capabilities,
			function ( $value, $key ) {
				return $this->is_capability( $key );
			},
			ARRAY_FILTER_USE_BOTH
		);

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
			// make sure to make only the db request with the last capability
			$roles->use_db = false;
			// merge with the rest of the capabilities
			if ( $remove_missing ) {
				$caps = array_merge( $this->get_all_capabilities(), $caps );
			}
			$last_cap = key( array_slice( $caps, - 1, 1, true ) );
			foreach ( $caps as $cap => $grant ) {
				if ( $cap === $last_cap ) {
					// use db, next call to add_cap will generate a db query
					$roles->use_db = true;
				}
				$role_obj->add_cap( $cap, $grant );
			}
			// make sure we put it back to true
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
				'is_system'  => $this->is_system_capability( $capability ),
			);
		}

		return $res;
	}

	/**
	 * Update a user capabilities.
	 * This is not an easy script so be careful while editing
	 * The input capabilities are those selected by the user in the frontend. We will check for the default user role
	 * capabilities. If the provided capabilities contains the same values as the role we will not set it, because by
	 * default the user has access to it, in case the capability value differs (true|false) we will set according.
	 * In case other role capabilities we will set it tu true, no need to set to false because the user dow not have
	 * access to it by default
	 * In case is not a capability, its a user defined capability we need to set it to true or false otherwise we might
	 * loose the cap when saving
	 *
	 * @param string|WP_User $user         The user to update capabilities
	 * @param array          $capabilities The new user capabilities
	 *
	 * @return bool|WP_User
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
		if ( get_current_user() == $user ) {

		}

		/**
		 * In multisite check if user belongs to the current site. Except if super admin is editing
		 */

		/**
		 * Remove roles from capabilities. Make sure we dont insert a role as capability
		 */
		$capabilities = array_filter(
			$capabilities,
			function ( $cap ) {
				return ! $this->is_role( $cap );
			},
			ARRAY_FILTER_USE_KEY
		);

		/**
		 * This array will contain the new array of capabilities
		 */
		$update = array();

		/**
		 * Get user role capabilities. All capabilities that the role allows or deny
		 */
		$user_role_capabilities = array();
		foreach ( $user->roles as $role ) {
			$role_obj = $this->get_role( $role );
			$caps     = isset( $role_obj->capabilities ) && is_array( $role_obj->capabilities ) ? $role_obj->capabilities : array();

			$user_role_capabilities = array_merge( $user_role_capabilities, $caps );

			// add roles by default
			$update[ $role ] = true;
		}

		$all_capabilities = $this->get_all_capabilities();
		foreach ( $all_capabilities as $all_capability => $all_value ) {
			/**
			 * Check is not a role
			 */
			if ( $this->is_role( $all_capability ) ) {
				continue;
			}

			if ( isset( $user_role_capabilities[ $all_capability ] ) ) {
				/**
				 * Is a user role capability
				 */
				$role_value = $user_role_capabilities[ $all_capability ];

				if ( isset( $capabilities[ $all_capability ] ) ) {
					// the user checked a cb
					// lets check if was check or the user checked
					if ( $capabilities[ $all_capability ] === $role_value ) {
						// the user leave the cb checked as it was
					} else {
						// the user change the cb to checked
						$update[ $all_capability ] = ! $role_value;
					}
				} else {
					// the user did'nt check the cb
					// lets check if was uncheck or the user unchecked
					if ( false === $role_value ) {
						// the user leave the cb unchecked
					} else {
						// the user changed the cb to unchecked
						$update[ $all_capability ] = ! $role_value;

					}
				}
			} else {
				/**
				 * Is others role capability
				 */
				if ( isset( $capabilities[ $all_capability ] ) ) {

					$update[ $all_capability ] = true;
				}
			}
		}

		foreach ( $user->allcaps as $capability => $value ) {

			if ( ! $this->is_capability( $capability ) && ! $this->is_role( $capability ) ) {
				/**
				 * Its a user specific capability
				 * Now lets check for the value. false means the user didnt check the cb
				 */
				$user_value = isset( $capabilities[ $capability ] ) ? (bool) $capabilities[ $capability ] : false;
				if ( $user_value == $value ) {
					// no change in the capability value
					$update[ $capability ] = $value;
				} else {
					// user changed
					$update[ $capability ] = ! $value;
				}

				// simplified version, lets keep it the other way to better understanding
				// $update[ $capability ] = $user_value == $value ? $value : ! $value;
			}

			// We make sure with this logic that if the user changes the value of a cb the system wont create the capability
		}

		// Update user meta
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
		// System
		$system_capabilities = $this->get_system_capabilities( $default );

		// Role defined
		$role_capabilities = $this->get_role_capabilities( $default );

		// All
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
		// build full capabilities list from all roles
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
		$capabilities = array_keys( $this->system_capabilities );

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

		$capabilities = array_filter(
			$capabilities,
			function ( $value, $cap ) {
				return ! $this->is_system_capability( $cap );
			},
			ARRAY_FILTER_USE_BOTH
		);

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

		$capabilities = array_filter(
			$capabilities,
			function ( $cap ) {
				return ( $this->is_capability( $cap ) && ! $this->is_system_capability( $cap ) );
			}
		);

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

	/**
	 * Gets a description for the capability
	 *
	 * @param string $capability The capability
	 *
	 * @return string The description for the provided capability
	 */
	public function get_capability_description( $capability ) {
		$description = '';
		if ( isset( $this->system_capabilities[ $capability ] ) ) {
			$description = $this->system_capabilities[ $capability ];
		}

		$description = apply_filters( 'leira-roles-get-capability-description', $description, $capability );

		return $description;
	}
}
