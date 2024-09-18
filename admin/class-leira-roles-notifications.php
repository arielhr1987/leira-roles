<?php

/**
 * This class handle all notifications inside to end user within the plugin.
 * The logic is based on BuddyPress core functionality
 *
 * @link       https://github.com/arielhr1987/leira-roles
 * @since      1.1.3
 * @package    Leira_Roles
 * @subpackage Leira_Roles/admin
 * @author     Ariel <arielhr1987@gmail.com>
 */
class Leira_Roles_Notifications{

	/**
	 * Cookie name to use
	 *
	 * @since      1.1.3
	 * @var string
	 */
	protected $cookie = 'leira_roles_notification';

	/**
	 * All types of notifications allowed
	 *
	 * @since      1.1.3
	 * @var string[]
	 */
	protected $types = array( 'error', 'success', 'warning', 'info' );

	/**
	 * All current messages
	 *
	 * @since      1.1.3
	 * @var array
	 */
	protected $messages = array();

	/**
	 * Leira_Roles_Notifications constructor.
	 *
	 * @since      1.1.3
	 */
	public function __construct() {
		/**
		 * Read cookie if exist
		 */
		if ( isset( $_COOKIE[ $this->cookie ] ) ) {
			$messages = $_COOKIE[ $this->cookie ];
			$messages = @json_decode( $messages, true );
			if ( is_array( $messages ) ) {
				$this->messages = $messages;
			}

			/**
			 * Delete the cookie by setting an expiration time before current time
			 */
			if ( ! headers_sent() ) {
				@setcookie( $this->cookie, '', strtotime( '-1 month' ) );
			}
		}
	}

	/**
	 * Display all messages
	 *
	 * @since      1.1.3
	 */
	public function display() {
		$html = '';
		foreach ( $this->types as $type ) {
			$messages = $this->get( $type );
			foreach ( $messages as $message ) {
				if ( is_string( $message ) ) {
					$html .= wp_kses_post( sprintf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $type, urldecode( $message ) ) );
				}
			}
		}

		return $html;
	}

	/**
	 * Get all messages for a given type
	 *
	 * @param string $type
	 *
	 * @return array The messages
	 * @since      1.1.3
	 */
	protected function get( $type ) {
		$messages = array();
		if ( isset( $this->messages[ $type ] ) && is_array( $this->messages[ $type ] ) ) {
			$messages = $this->messages[ $type ];
		}

		return $messages;
	}

	/**
	 * @param string $type The type of notification to show to the user
	 *                     [error|success|warning|info]
	 * @param string $msg  The message to show to the user
	 *
	 * @return bool If notification was added successfully
	 * @since      1.1.3
	 */
	public function add( $type, $msg ) {
		if ( ! in_array( $type, $this->types ) || ! is_string( $msg ) ) {
			return false;
		}

		$messages   = $this->get( $type );
		$messages[] = $msg;

		// Update the messages
		$this->messages[ $type ] = $messages;

		if ( ! headers_sent() ) {
			/**
			 * Set the cookie to read in the next call
			 * Expiration time is set to a long number to avoid timezone differences
			 */
			@setcookie( $this->cookie, wp_json_encode( $this->messages ), strtotime( '+1 month' ) );
		}

		return true;
	}

	/**
	 * Show an error message
	 *
	 * @param string $msg
	 *
	 * @since      1.1.3
	 */
	public function error( $msg ) {
		$this->add( 'error', $msg );
	}

	/**
	 * Show a success message
	 *
	 * @param string $msg
	 *
	 * @since      1.1.3
	 */
	public function success( $msg ) {
		$this->add( 'success', $msg );
	}

	/**
	 * Show a warning message
	 *
	 * @param string $msg
	 *
	 * @since      1.1.3
	 */
	public function warning( $msg ) {
		$this->add( 'warning', $msg );
	}

	/**
	 * Show an info message
	 *
	 * @param string $msg
	 *
	 * @since      1.1.3
	 */
	public function info( $msg ) {
		$this->add( 'info', $msg );
	}
}
