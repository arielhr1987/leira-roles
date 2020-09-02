<?php

/**
 * This class handle all notifications inside to end user within the plugin.
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
	 * @var string
	 */
	protected $cookie = 'leira_roles_notification';

	/**
	 * All types of notifications allowed
	 *
	 * @var string[]
	 */
	protected $types = array( 'error', 'success', 'warning', 'info' );

	/**
	 * All current messages
	 *
	 * @var array
	 */
	protected $messages = array();

	/**
	 * Leira_Roles_Notifications constructor.
	 */
	public function __construct() {
		/**
		 * Read cookie if exist
		 */
		if ( isset( $_COOKIE[ $this->cookie ] ) ) {
			$messages = $_COOKIE[ $this->cookie ];
			$messages = @json_decode( $messages );
			if ( is_array( $messages ) ) {
				$this->messages = $messages;
			}
		}
	}

	/**
	 * Display all messages
	 */
	public function display_all() {
		$html = '';
		foreach ( $this->types as $type ) {
			$messages = $this->get( $type );
			foreach ( $messages as $message ) {
				if ( is_string( $message ) ) {
					$html .= esc_html( sprintf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $type, sanitize_text_field( $message ) ) );
				}
			}
		}

		/**
		 * Delete the cookie by setting an expiration time before current time
		 */
		setcookie( $this->cookie, '', time() + 3600 * 24 * 7 );

		return $html;
	}

	/**
	 * @param string $type
	 */
	public function display( $type ) {

	}

	/**
	 * Get all messages for a given type
	 *
	 * @param string $type
	 *
	 * @return array The messages
	 */
	protected function get( $type ) {
		$messages = array();
		if ( isset( $this->messages[ $type ] ) && is_array( $this->messages[ $type ] ) ) {
			$messages = array();
		}

		return $messages;
	}

	/**
	 * @param string $type The type of notification to show to the user
	 *                     [error|success|warning|info]
	 * @param string $msg  The message to show to the user
	 *
	 * @return bool If notification was added successfully
	 */
	public function add( $type, $msg ) {
		if ( ! in_array( $type, $this->types ) ) {
			return false;
		}
		if ( ! is_string( $msg ) ) {
			return false;
		}
		$messages   = $this->get( $type );
		$messages[] = $msg;

		//Update the messages
		$this->messages[ $type ] = $messages;

		if ( ! headers_sent() ) {
			/**
			 * Set the cookie to read in the next call
			 * Expiration time is set to a long number to avoid timezone differences (7 days)
			 */
			setcookie( $this->cookie, json_encode( $this->messages ), time() + 3600 * 24 * 7 );
		}

		return true;
	}

	/**
	 * Show an error message
	 *
	 * @param string $msg
	 */
	public function error( $msg ) {
		$this->add( 'error', $msg );
	}

	/**
	 * Show a success message
	 *
	 * @param string $msg
	 */
	public function success( $msg ) {
		$this->add( 'success', $msg );
	}

	/**
	 * Show a warning message
	 *
	 * @param string $msg
	 */
	public function warning( $msg ) {
		$this->add( 'warning', $msg );
	}

	/**
	 * Show an info message
	 *
	 * @param string $msg
	 */
	public function info( $msg ) {
		$this->add( 'info', $msg );
	}
}
