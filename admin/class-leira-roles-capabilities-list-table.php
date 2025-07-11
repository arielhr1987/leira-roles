<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class Leira_Roles_Capabilities_List_Table
 */
class Leira_Roles_Capabilities_List_Table extends WP_List_Table{

	/**
	 * The roles manager
	 *
	 * @var Leira_Roles_Manager
	 */
	protected $manager;

	/**
	 * Leira_Roles_Capabilities_List_Table constructor.
	 *
	 * @param  array  $args
	 */
	function __construct( $args = array() ) {
		global $status, $page;

		// Set parent defaults
		parent::__construct(
			array(
				'singular' => 'capability',      // singular name of the listed records
				'plural'   => 'capabilities',    // plural name of the listed records
				'ajax'     => true,              // does this table support ajax?
			)
		);

		$this->manager = leira_roles()->manager;
	}

	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {

		return parent::get_table_classes();
	}

	/**
	 * Override base display class
	 */
	public function display() {
		$singular = $this->_args['singular'];

		$this->display_tablenav( 'top' );

		$this->screen->render_screen_reader_content( 'heading_list' );
		?>
		<div class="capabilities-container wp-clearfix <?php echo esc_html( implode( ' ',
			$this->get_table_classes() ) ); ?>">
			<?php //$this->display_rows_or_placeholder(); ?>
			<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
				<tbody id="the-list">
				<tr>
					<td class="check-column">
						<?php $this->display_rows_or_placeholder(); ?>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
		<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Generate the tbody element for the list table.
	 */
	public function display_rows_or_placeholder() {
		if ( $this->has_items() ) {
			$this->display_rows();
		} else {

			echo '<div class="">';
			$this->no_items();
			echo '</div>';
		}
	}

	/**
	 * Show a single row item
	 *
	 * @param  array  $item
	 */
	public function single_row( $item ) {

		$capability = $item['capability'];
		$is_system  = ! empty( $item['is_system'] );

		$classes = [ 'alignleft', 'capability-item' ];
		$id      = 'capability-' . md5( $capability );
		$label   = esc_html( $capability );

		echo sprintf(
			'<label id="%1$s" class="%2$s" title="%3$s">',
			esc_attr( $id ),
			esc_attr( implode( ' ', $classes ) ),
			$label
		);

		if ( $is_system ) {
			echo '<input type="checkbox" name="system-capability[]" disabled value="" />';
		} else {
			echo sprintf(
				'<input type="checkbox" name="capability[]" value="%s" />',
				esc_attr( $capability )
			);
		}

		//Label
		echo sprintf( '<span class="checkbox-title">%s</span>', $label );

		// Tooltip
		$title = $this->manager->get_capability_description( $capability );
		if ( ! empty( $title ) ) {
			echo sprintf(
				'<i class="dashicons dashicons-editor-help leira-tooltip" title="%s"></i>',
				esc_html( $title )
			);
		}

		echo '</label>';
	}

	/**
	 * Get list table columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			// 'cb'    => '<input type="checkbox"/>', //Render a checkbox instead of text
			// 'title' => __( 'Capability', 'leira-roles' ),
			// 'text'  => __( 'Name', 'leira-roles' ),
		);

		return $columns;
	}

	/**
	 * Get a list of sortable columns.
	 *
	 * @return array
	 * @since 3.1.0
	 */
	protected function get_sortable_columns() {
		$sortable_columns = array(
			// 'title' => array( 'capability', true ),
			// 'text'  => array( 'name', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Generates and display row actions links for the list table.
	 *
	 * @param  object  $item  The item being acted upon.
	 * @param  string  $column_name  Current column name.
	 * @param  string  $primary  Primary column name.
	 *
	 * @return string The row actions HTML, or an empty string if the current column is the primary column.
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		// Build row actions
		$actions = array();

		if ( ! $this->manager->is_system_capability( $item['capability'] ) ) {
			// Include these actions if it's not a system capability
			$actions = array_merge(
				$actions,
				array(
					'inline hide-if-no-js' => sprintf(
						'<button type="button" class="button-link editinline" aria-label="%s" aria-expanded="false">%s</button>',
						/*
						 * translators: Quick edit the capability selected by the user
						 */
						esc_attr( sprintf( __( 'Quick edit &#8220;%s&#8221; inline', 'leira-roles' ),
							$item['capability'] ) ),
						__( 'Quick&nbsp;Edit', 'leira-roles' )
					),
					'delete'               => sprintf(
						'<a href="%s" class="delete-capability" onclick="return confirm(\'%s\')">%s</a>',
						esc_url( add_query_arg(
							array(
								'page'       => 'leira-roles',
								'action'     => 'leira-roles-delete-capability',
								'capability' => esc_attr( $item['capability'] ),
								'_wpnonce'   => wp_create_nonce( 'bulk-capabilities' ),
							),
							admin_url( 'users.php' )
						) ),
						__( 'Are you sure you want to delete this capability?', 'leira-roles' ),
						__( 'Delete', 'leira-roles' )
					),
				)
			);
		}

		return $column_name === $primary ? $this->row_actions( $actions, false ) : '';
	}

	/**
	 * Add default
	 *
	 * @param  object  $item
	 * @param  string  $column_name
	 *
	 * @return mixed|string|void
	 */
	protected function column_default( $item, $column_name ) {
		return ! empty( $item[ $column_name ] ) ? $item[ $column_name ] : '&mdash;';
	}

	/**
	 * The checkbox column
	 *
	 * @param  object  $item
	 *
	 * @return string|void
	 */
	protected function column_cb( $item ) {
		$out = '';
		if ( ! $this->manager->is_system_capability( $item['capability'] ) ) {
			$out = sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', 'capability', $item['capability'] );
		}

		return $out;
	}

	/**
	 * The capability column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	protected function column_title( $item ) {

		$out = sprintf( '<strong>%1$s</strong>', $item['capability'] );

		$out .= '<div class="hidden" id="inline_' . md5( $item['capability'] ) . '">';

		if ( $this->manager->is_system_capability( $item['capability'] ) ) {
			// do something with system capabilities
		}
		foreach ( $item as $key => $value ) {
			$out .= sprintf( '<div class="%s">%s</div>', $key, $value );
		}
		$out .= '</div>';

		return $out;
	}

	/**
	 * The action column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	protected function column_text( $item ) {

		return ! empty( $item['name'] ) ? $item['name'] : '&mdash;';
	}

	/**
	 * Get the bulk actions to show in the top page dropdown
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		$actions = array(
			'leira-roles-delete-capability' => __( 'Delete', 'leira-roles' ),
		);

		return $actions;
	}

	/**
	 * Process bulk actions
	 */
	protected function process_bulk_action() {

		$query_arg = '_wpnonce';
		$action    = 'bulk-' . $this->_args['plural'];
		$checked   = isset( $_REQUEST[ $query_arg ] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ $query_arg ] ) ),
			$action ) : false;

		if ( ! $checked ) {
			return;
		}

		$current_action = $this->current_action();
		// Detect when a bulk action is being triggered...
		switch ( $current_action ) {
			case 'delete':
				// wp_die( 'Items deleted (or they would be if we had items to delete)!' );
				break;
			default:
		}
	}

	/**
	 * Determine if a given string contains a given substring.
	 *
	 * @param  string  $haystack
	 * @param  string|array  $needles
	 * @param  bool  $sensitive  Use case-sensitive search
	 *
	 * @return bool
	 */
	public function str_contains( $haystack, $needles, $sensitive = true ) {
		foreach ( (array) $needles as $needle ) {
			$function = $sensitive ? 'mb_strpos' : 'mb_stripos';
			if ( '' !== $needle && false !== $function( $haystack, $needle ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Displays the search box.
	 *
	 * @param  string  $text  The 'submit' button label.
	 * @param  string  $input_id  ID attribute value for the search input field.
	 *
	 * @since 3.1.0
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) . '" />';
		}
		if ( ! empty( $_REQUEST['page'] ) ) {
			echo '<input type="hidden" name="page" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) . '" />';
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text"
				   for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s"
				   value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 *
	 */
	public function prepare_items() {

		/**
		 * First, let's decide how many records per page to show
		 */
		$per_page = $this->get_items_per_page( str_replace( '-', '_', $this->screen->id . '_per_page' ), 999 );

		/**
		 * handle bulk actions.
		 */
		$this->process_bulk_action();

		/**
		 * Fetch the data
		 */
		$data = $this->manager->get_capabilities_for_list_table();

		/**
		 * Handle search
		 */
		if ( ( ! empty( $_REQUEST['s'] ) ) && $search = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) {
			// $_SERVER['REQUEST_URI'] = add_query_arg( 's', $search );
			$data_filtered = array();
			foreach ( $data as $item ) {
				if ( $this->str_contains( $item['capability'], $search, false ) || $this->str_contains( $item['name'],
						$search, false ) ) {
					$data_filtered[] = $item;
				}
			}
			$data = $data_filtered;
		}

		/**
		 * This checks for sorting input and sorts the data in our array accordingly.
		 */
		function usort_reorder( $a, $b ) {
			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'capability'; // If no sort, default to capability
			$order   = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'asc';            // If no order, default to asc

			// $result = strcmp( $a[ $orderby ], $b[ $orderby ] ); //Determine sort order, case sensitive
			// $result = strcasecmp( $a[ $orderby ], $b[ $orderby ] ); //Determine sort order, case-insensitive
			$result = strnatcasecmp( $a[ $orderby ],
				$b[ $orderby ] );                                                                                                      // Determine sort order, case-insensitive, natural order

			return ( 'asc' === $order ) ? $result : - $result; // Send a final sort direction to usort
		}

		usort( $data, 'usort_reorder' );

		/**
		 * Pagination.
		 */
		$current_page = $this->get_pagenum();
		$total_items  = count( $data );

		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to
		 */
		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		/**
		 * Now we can add the data to the item property, where it can be used by the rest of the class.
		 */
		$this->items = $data;

		/**
		 * We also have to register our pagination options and calculations.
		 */
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,                       // calculate the total number of items
				'per_page'    => $per_page,                          // determine how many items to show on a page
				'total_pages' => ceil( $total_items / $per_page ),   // calculate the total number of pages
			)
		);
	}

	/**
	 * Outputs the hidden row displayed when inline editing
	 *
	 * @since 3.1.0
	 */
	public function inline_edit() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>

		<form method="get">
			<table style="display: none">
				<tbody id="inlineedit">
				<tr id="inline-edit" class="inline-edit-row" style="display: none">
					<td colspan="<?php echo esc_html( $this->get_column_count() ); ?>" class="colspanchange">

						<fieldset class="">
							<legend class="inline-edit-legend"><?php esc_html_e( 'Quick Edit' ); ?></legend>
							<div class="inline-edit-col">
								<label>
									<span class="title"><?php esc_html_e( 'Capability', 'leira-roles' ); ?></span>
									<span class="input-text-wrap">
										<input type="hidden" name="old_capability" value=""/>
										<input class="ptitle" type="text" name="new_capability" value=""/>
										<!--<p class="description">
											<small> </small>
										</p>-->
									</span>
								</label>
								<label>
									<span class="title"><?php esc_html_e( 'Name', 'leira-roles' ); ?></span>
									<span class="input-text-wrap">
										<input class="ptitle" type="text" name="name" value=""/>
										<!--<p class="description">
											<small> </small>
										</p>-->
									</span>
								</label>
								<label>
									<span class="title"><?php esc_html_e( 'Capabilities', 'leira-roles' ); ?></span>
									<span class="input-text-wrap">
										<div class="wp-clearfix">
											<p class="search-box">
												<input type="search" name="capabilities_search_input"
													   placeholder="<?php esc_html_e( 'Search Capabilities',
														   'leira-roles' ); ?>">
											</p>
										</div>
										<div class="capabilities-container wp-clearfix">
											<div class="notice notice-error notice-alt inline hidden">
												<p class="error">
													<?php esc_html_e( 'No capabilities found.', 'leira-roles' ); ?>
												</p>
											</div>
										</div>
									</span>
								</label>

							</div>
						</fieldset>
						<?php

						$core_columns = array(
							'cb'          => true,
							'description' => true,
							'name'        => true,
							'slug'        => true,
							'posts'       => true,
						);

						list( $columns ) = $this->get_column_info();

						foreach ( $columns as $column_name => $column_display_name ) {
							if ( isset( $core_columns[ $column_name ] ) ) {
								continue;
							}

							/** This action is documented in wp-admin/includes/class-wp-posts-list-table.php */
							do_action( 'quick_edit_custom_box', $column_name, 'edit-capability', 0 );
						}

						?>

						<div class="inline-edit-save submit">
							<button type="button"
									class="cancel button alignleft"><?php esc_html_e( 'Cancel' ); ?></button>
							<button type="button"
									class="save button button-primary alignright"><?php esc_html_e( 'Save' ); ?></button>
							<span class="spinner"></span>
							<?php wp_nonce_field( 'capabilityinlineeditnonce', '_inline_edit', false ); ?>
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
