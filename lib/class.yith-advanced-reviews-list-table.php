<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_Advanced_Reviews_List_Table class
 *
 * @package yith-woocommerce-advanced-reviews\lib
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access forbidden.' );
}

global $YWAR_AdvancedReview; // phpcs:ignore WordPress.NamingConventions


if ( ! class_exists( 'YITH_Advanced_Reviews_List_Table' ) ) {
	/**
	 * YITH_Advanced_Reviews_List_Table
	 *
	 * @class class.yith-advanced-reviews-list-table.php
	 * @package    Yithemes
	 * @since      Version 1.0.0
	 * @author     Your Inspiration Themes
	 */
	class YITH_Advanced_Reviews_List_Table extends WP_List_Table {

		/**
		 * Ywar
		 *
		 * @var object store the Advanced reviews class object, used as shortcut to full singleton name
		 */
		private $ywar;


		/**
		 * Construct
		 */
		public function __construct() {
			global $YWAR_AdvancedReview; // phpcs:ignore WordPress.NamingConventions

			$this->ywar = $YWAR_AdvancedReview; // phpcs:ignore WordPress.NamingConventions

			// Set parent defaults.
			parent::__construct(
				array(
					'singular' => 'review', // singular name of the listed records.
					'plural'   => 'reviews', // plural name of the listed records.
					'ajax'     => false, // does this table support ajax?.
				)
			);
		}

		/**
		 * Returns columns available in table
		 *
		 * @return array Array of columns of the table
		 * @since 1.0.0
		 */
		public function get_columns() {
			$columns = array(
				'cb'                               => '<input type="checkbox" />',
				$this->ywar->custom_column_review  => esc_html__( 'Review', 'yith-woocommerce-advanced-reviews' ),
				$this->ywar->custom_column_author  => esc_html__( 'Author', 'yith-woocommerce-advanced-reviews' ),
				$this->ywar->custom_column_date    => esc_html__( 'Date', 'yith-woocommerce-advanced-reviews' ),
				$this->ywar->custom_column_rating  => esc_html__( 'Rate', 'yith-woocommerce-advanced-reviews' ),
				$this->ywar->custom_column_product => esc_html__( 'Product', 'yith-woocommerce-advanced-reviews' ),
			);

			return apply_filters( 'yith_advanced_reviews_custom_column', $columns );
		}


		/**
		 * Generate row actions div
		 *
		 * @since 3.1.0
		 * @access protected
		 *
		 * @param array $actions The list of actions.
		 * @param bool  $always_visible Whether the actions should be always visible.
		 *
		 * @return string
		 */
		protected function row_actions( $actions, $always_visible = false ) {
			$action_count = count( $actions );
			$i            = 0;

			if ( ! $action_count ) {
				return '';
			}

			$out = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';
			foreach ( $actions as $action => $link ) {
				++$i;
				( $i === $action_count ) ? $sep = '' : $sep = ' | ';
				$out                           .= "<span class='$action'>$link$sep</span>";
			}
			$out .= '</div>';

			return $out;
		}

		/**
		 * Get a parameter array to be passed to get_posts
		 *
		 * @param args $args parameters for filtering the review list.
		 * 'page' => 0 = show all, any positive value ask for paginated results.
		 * 'post_status' => 'all', //value are all, publish,future...
		 * 'post_parent' => -1, //-1 stands for all post parent, any non negative value is intended as a specifici parent.
		 * 'review_status' => 'all',
		 * 'items_for_page => 0 = show all on 1 page, any positive value is the number of items to show.
		 */
		private function get_params_for_current_view( $args ) {
			// Start the filters array, selecting the review post type.
			$params = array(
				'post_type'        => 'ywar_reviews',
				'suppress_filters' => false,
			);

			// Show a single page or all items.
			$params['numberposts'] = -1;
			if ( isset( $args['page'] ) && ( $args['page'] > 0 ) && isset( $args['items_for_page'] ) && ( $args['items_for_page'] > 0 ) ) {

				// Set number of posts and offset.
				$current_page          = $args['page'];
				$items_for_page        = $args['items_for_page'];
				$offset                = ( $current_page * $items_for_page ) - $items_for_page;
				$params['offset']      = $offset;
				$params['numberposts'] = $items_for_page;

			} else {
				$params['offset'] = 0;
			}

			// Choose post status.
			if ( isset( $args['post_status'] ) && ( 'all' !== $args['post_status'] ) ) {
				$params['post_status'] = $args['post_status'];
			}

			if ( isset( $args['post_parent'] ) && ( $args['post_parent'] >= 0 ) ) {
				$params['post_parent'] = $args['post_parent'];
			}

			$order           = isset( $args['order'] ) ? $args['order'] : 'DESC';
			$params['order'] = $order;

			if ( isset( $args['orderby'] ) ) {
				$order_by = $args['orderby'];
				switch ( $order_by ) {
					case $this->ywar->custom_column_rating:
						$params['meta_key'] = $this->ywar->meta_key_rating; //phpcs:ignore slow query ok.
						$params['orderby']  = 'meta_value_num';
						break;

					case $this->ywar->custom_column_date:
						$params['orderby'] = 'post_date';
						break;

					default:
						$params = apply_filters( 'yith_advanced_reviews_column_sort', $params, $order_by );
				}
			}

			if ( isset( $args['review_status'] ) ) {

				switch ( $args['review_status'] ) {
					case 'all':
						break;

					case 'trash':
						$params['post_status'] = 'trash';

						break;

					case 'not_approved':
						$params['meta_query'][] = array(
							'key'     => $this->ywar->meta_key_approved,
							'value'   => 1,
							'compare' => '!=',
							'type'    => 'numeric',
						);
						break;

					default:
						$params = apply_filters( 'yith_advanced_reviews_filter_view', $params, $args['review_status'] );
				}
			}

			return $params;
		}

		/**
		 * Filter_reviews_by_search_term
		 *
		 * @param  mixed $where where.
		 * @return where
		 */
		public function filter_reviews_by_search_term( $where ) {
			$filter_content = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification
			$terms          = explode( '+', $filter_content );
			global $wpdb;
			$where_clause = '';
			foreach ( $terms as $term ) {
				if ( ! empty( $where_clause ) ) {
					$where_clause .= ' OR ';
				}
				$where_clause .= "( {$wpdb->prefix}posts.post_content LIKE '%$term%' ) or ({$wpdb->prefix}posts.post_title like '%$term%') ";
			}

			$where = "$where AND ($where_clause)";

			return $where;
		}

		/**
		 * Perform custom bulk actions, if there are some
		 */
		public function process_bulk_action() {
			switch ( $this->current_action() ) {

				case 'untrash':
					$reviews = isset( $_GET['reviews'] ) ? $_GET['reviews'] : '';//phpcs:ignore --Sanitize doenst´t work. Nonce
					foreach ( $reviews as $review_id ) {//phpcs:ignore WordPress.Security.NonceVerification
						$my_post = array(
							'ID'          => $review_id,
							'post_status' => 'publish',
						);

						// Update the post into the database.
						wp_update_post( $my_post );
					}

					break;

				case 'trash':
					$reviews = isset( $_GET['reviews'] ) ? $_GET['reviews'] : ''; //phpcs:ignore --Sanitize doenst´t work. Nonce
					foreach ( $reviews as $review_id ) {
						$my_post = array(
							'ID'          => $review_id,
							'post_status' => 'trash',
						);

						// Update the post into the database.
						wp_update_post( $my_post );
					}

					break;

				case 'delete':
					$reviews = isset( $_GET['reviews'] ) ? $_GET['reviews'] : '';//phpcs:ignore --Sanitize doenst´t work. Nonce

					foreach ( $reviews as $review_id ) {
						wp_delete_post( $review_id );
					}

					break;

				case 'approve':
					$reviews = isset( $_GET['reviews'] ) ? $_GET['reviews'] : '';//phpcs:ignore --Sanitize doenst´t work. Nonce

					foreach ( $reviews as $review_id ) {
						update_post_meta( $review_id, $this->ywar->meta_key_approved, 1 );
					}

					break;

				case 'unapprove':
					$reviews = isset( $_GET['reviews'] ) ? $_GET['reviews'] : '';//phpcs:ignore --Sanitize doenst´t work. Nonce

					foreach ( $reviews as $review_id ) {
						update_post_meta( $review_id, $this->ywar->meta_key_approved, 0 );
					}

					break;

				default:
					if ( isset( $_GET['reviews'] ) ) {//phpcs:ignore WordPress.Security.NonceVerification
						do_action( 'yith_advanced_reviews_process_bulk_actions', $this->current_action(), sanitize_text_field( wp_unslash( $_GET['reviews'] ) ) );//phpcs:ignore WordPress.Security.NonceVerification
					}
			}
		}

		/**
		 * Prepare items for table
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function prepare_items() {
			$this->process_bulk_action();

			// Sets pagination arguments.
			$current_page = absint( $this->get_pagenum() );

			// Sets columns headers.
			$columns               = $this->get_columns();
			$hidden                = array();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			$review_status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'all';//phpcs:ignore WordPress.Security.NonceVerification
			$orderby       = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';//phpcs:ignore WordPress.Security.NonceVerification
			$order         = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'desc';//phpcs:ignore WordPress.Security.NonceVerification

			// Start the filters array, selecting the review post type.
			$params = array(
				'post_type'      => 'ywar_reviews',
				'items_for_page' => $this->ywar->items_for_page,
				'review_status'  => $review_status,
				'orderby'        => $orderby,
				'order'          => $order,
			);

			// Retrieve the number of items for the current filters.
			$args           = $this->get_params_for_current_view( $params );
			$args['fields'] = 'ids';
			$total_items    = count( get_posts( $args ) );

			// Retrieve only a page for the current filter.
			$params['page'] = $current_page;
			$args           = $this->get_params_for_current_view( $params );

			$filter_content = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';//phpcs:ignore WordPress.Security.NonceVerification
			if ( ! empty( $filter_content ) ) {
				// Add a filter to alter WHERE clause on following get_posts call.
				add_filter( 'posts_where', array( $this, 'filter_reviews_by_search_term' ) );
			}

			$this->items = get_posts( $args );

			// Remove the previous filter, not needed anymore.
			remove_filter( 'posts_where', array( $this, 'filter_reviews_by_search_term' ) );

			$total_pages = ceil( $total_items / $this->ywar->items_for_page );

			// Set the pagination.
			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $this->ywar->items_for_page,
					'total_pages' => $total_pages,
				)
			);
		}


		/**
		 * Decide which columns to activate the sorting functionality on
		 *
		 * @return array $sortable, the array of columns that can be sorted by the user
		 */
		public function get_sortable_columns() {

			$columns = array(

				$this->ywar->custom_column_rating => array( $this->ywar->custom_column_rating, false ),
				$this->ywar->custom_column_date   => array( $this->ywar->custom_column_date, false ),
			);

			return apply_filters( 'yith_advanced_reviews_sortable_custom_columns', $columns );
		}

		/**
		 * Sets bulk actions for table
		 *
		 * @return array Array of available actions
		 * @since 1.0.0
		 */
		public function get_bulk_actions() {
			$actions = array();

			$actions['untrash'] = esc_html__( 'Restore', 'yith-woocommerce-advanced-reviews' );
			$actions['trash']   = esc_html__( 'Move to bin', 'yith-woocommerce-advanced-reviews' );

			$actions['delete']    = esc_html__( 'Delete permanently', 'yith-woocommerce-advanced-reviews' );
			$actions['approve']   = esc_html__( 'Approve reviews', 'yith-woocommerce-advanced-reviews' );
			$actions['unapprove'] = esc_html__( 'Unapprove reviews', 'yith-woocommerce-advanced-reviews' );

			return apply_filters( 'yith_advanced_reviews_bulk_actions', $actions );
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination
		 *
		 * @since 3.1.0
		 * @access protected
		 */
		protected function get_views() {
			$views = array(
				'all'          => esc_html__( 'All', 'yith-woocommerce-advanced-reviews' ),
				'trash'        => esc_html__( 'Bin', 'yith-woocommerce-advanced-reviews' ),
				'not_approved' => esc_html__( 'Not approved', 'yith-woocommerce-advanced-reviews' ),
			);

			$views = apply_filters( 'yith_advanced_reviews_table_views', $views );

			$current_view = $this->get_current_view();
			$args         = array( 'status' => 0 );

			$args['user_id'] = get_current_user_id();

			unset( $views['processing'] );

			foreach ( $views as $id => $view ) {
				// number of items for the view.
				$args           = $this->get_params_for_current_view(
					array(
						'review_status' => $id,
					)
				);
				$args['fields'] = 'ids';

				// retrieve the number of items for the current filters.
				$total_items = count( get_posts( $args ) );

				$href           = esc_url( add_query_arg( 'status', $id ) );
				$class          = $id === $current_view ? 'current' : '';
				$args['status'] = 'unpaid' === $id ? array( $id, 'processing' ) : $id;
				$views[ $id ]   = sprintf( "<a href='%s' class='%s'>%s <span class='count'>(%d)</span></a>", $href, $class, $view, $total_items );
			}

			return $views;
		}

		/**
		 * Column_default
		 *
		 * Print the columns information
		 *
		 * @param  mixed $review review.
		 * @param  mixed $column_name column_name.
		 * @return void
		 */
		public function column_default( $review, $column_name ) {

			switch ( $column_name ) {

				case $this->ywar->custom_column_review:
					$post = get_post( $review->ID );
					if ( empty( $post->post_title ) && empty( $post->post_content ) ) {
						return;
					}

					$edit_link = get_edit_post_link( $review->ID );
					echo '<a class="row-title" href="' . esc_attr( $edit_link ) . '">';

					if ( ! empty( $post->post_title ) ) {

						echo '<span class="review-title">' . wp_kses( wc_trim_string( $post->post_title, 80 ), 'post' ) . '</span>';
						echo '<br>';
					}

					if ( ! empty( $post->post_content ) ) {

						echo '<span class="review-content">' . wp_kses( wc_trim_string( $post->post_content, 80 ), 'post' ) . '</span>';
					}

					echo '</a>';

					$post_type_object = get_post_type_object( $this->ywar->post_type_name );

					if ( 'trash' === $post->post_status ) {
						$actions['untrash'] = "<a title='" . esc_attr__( 'Restore this item from the bin' ) . "' href='" . $this->ywar->untrash_review_url( $post ) . "'>" . esc_html__( 'Restore' ) . '</a>';
					} elseif ( EMPTY_TRASH_DAYS ) {
						$actions['trash'] = "<a class='submitdelete' title='" . esc_attr__( 'Move this item to the bin' ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . esc_html__( 'Bin' ) . '</a>';
					}
					if ( 'trash' === $post->post_status || ! EMPTY_TRASH_DAYS ) {
						$actions['delete'] = "<a class='submitdelete' title='" . esc_attr__( 'Delete this item permanently' ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . esc_html__( 'Delete permanently' ) . '</a>';
					}

					$actions = apply_filters( 'yith_advanced_reviews_row_actions', $actions, $post );

					echo wp_kses( $this->row_actions( $actions ), 'post' );

					break;

				case $this->ywar->custom_column_rating:
					if ( 0 === $review->post_parent ) {
						$rating = get_post_meta( $review->ID, $this->ywar->meta_key_rating, true );

						echo '<div class="woocommerce"><div class="star-rating" title="' . sprintf(
						/* translators: %d: rating */
							esc_html__( 'Rated %d out of 5', 'yith-woocommerce-advanced-reviews' ),
							wp_kses( $rating, 'post' )
						) . '">
                        <span style="width:' . ( ( wp_kses( $rating, 'post' ) / 5 ) * 100 ) . '%"><strong>' . wp_kses( $rating, 'post' ) . '</strong>' . esc_html__( 'out of 5', 'yith-woocommerce-advanced-reviews' ) . ' </span>
                        </div>
                        </div>';
					}

					break;

				case $this->ywar->custom_column_product:
					$product_id = get_post_meta( $review->ID, $this->ywar->meta_key_product_id, true );

					echo wp_kses( get_the_title( $product_id ), 'post' ) . '<br>';

					if ( current_user_can( 'edit_post', $product_id ) ) {
						echo "<a class='edit-product-review' href='" . esc_attr( get_edit_post_link( $product_id ) ) . "'>" . esc_html__( 'Edit', 'yith-woocommerce-advanced-reviews' ) . '</a>';
					}

					echo "<a class='view-product-review' href='" . esc_attr( get_permalink( $product_id ) ) . "' target='_blank'>" . esc_html__( 'View', 'yith-woocommerce-advanced-reviews' ) . '</a>';

					break;

				case $this->ywar->custom_column_author:
					if ( $review->post_author ) {
						$user        = get_userdata( $review->post_author );
						$author_name = $user ? $user->display_name : esc_html__( 'Anonymous', 'yith-woocommerce-advanced-reviews' );
					} else {
						$user = yit_get_post_meta( $review->ID, $this->ywar->meta_key_review_author );

						if ( $user ) {
							$author_name = $user ? $user : esc_html__( 'Anonymous', 'yith-woocommerce-advanced-reviews' );
						}
					}

					echo wp_kses( $author_name, 'post' );

					break;

				case $this->ywar->custom_column_date:
					$t_time = get_the_time( __( 'Y/m/d g:i:s a' ) );
					$m_time = $review->post_date;
					$time   = get_post_time( 'G', true, $review );

					$time_diff = time() - $time;

					if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
						/* translators: %s: Time */
						$h_time = sprintf( esc_html__( '%s ago' ), human_time_diff( $time ) );
					} else {
						$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
					}

					echo '<abbr title="' . esc_attr( $t_time ) . '">' . wp_kses( $h_time, 'post' ) . '</abbr>';
					break;

				default:
					do_action( 'yith_advanced_reviews_show_advanced_reviews_columns', $column_name, $review->ID );
			}

			return null;
		}

		/**
		 * Prints column cb
		 *
		 * @param rec $rec Object Item to use to print CB record.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function column_cb( $rec ) {

			return sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" />',
				$this->_args['plural'], // Let's simply repurpose the table's plural label.
				$rec->ID // The value of the checkbox should be the record's id.
			);
		}

		/**
		 * Message to be displayed when there are no items
		 *
		 * @since 3.1.0
		 * @access public
		 */
		public function no_items() {
			esc_html_e( 'No reviews found.', 'yith-woocommerce-advanced-reviews' );
		}


		/**
		 * Extra controls to be displayed between bulk actions and pagination
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 *
		 * @return string The view name
		 */
		public function get_current_view() {
			return empty( $_GET['status'] ) ? 'all' : sanitize_text_field( wp_unslash( $_GET['status'] ) );//phpcs:ignore WordPress.Security.NonceVerification
		}

		/**
		 * Generate the table navigation above or below the table
		 *
		 * @since 3.1.0
		 * @access protected
		 *
		 * @param string $which which.
		 */
		protected function display_tablenav( $which ) {
			if ( 'top' === $which ) {
				wp_nonce_field( 'bulk-' . $this->_args['plural'] );
			}
			?>
			<div class="tablenav <?php echo esc_attr( $which ); ?>">

				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
				<?php
				$this->extra_tablenav( $which );
				$this->pagination( $which );
				?>

				<br class="clear"/>
			</div>
			<?php
		}

			/**
			 * Generates content for a single row of the table
			 *
			 * @since 3.1.0
			 * @access public
			 *
			 * @param object $item The current item.
			 */
		public function single_row( $item ) {
			$approved = 1 === intval( get_post_meta( $item->ID, $this->ywar->meta_key_approved, true ) );
			if ( ! $approved ) {
				echo '<tr class="review-unapproved">';
			} else {
				echo '<tr>';
			}

			$this->single_row_columns( $item );
			echo '</tr>';
		}
	}
}
