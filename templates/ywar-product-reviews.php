<?php
/**
 * Product reviews template
 *
 * @package yith-woocommerce-advanced-reviews\templates
 */

global $product;
global $YWAR_AdvancedReview; // phpcs:ignore WordPress.NamingConventions

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$product_id = yit_get_prop( $product, 'id' );
if ( ! comments_open( $product_id ) ) {
	return;
}

$reviews_count = count( $YWAR_AdvancedReview->get_product_reviews_by_rating( $product_id ) ); // phpcs:ignore WordPress.NamingConventions
?>

<?php do_action( 'yith_advanced_reviews_before_reviews' ); ?>
<div id="reviews" class="yith-woocommerce-advanced-reviews">
	<div id="comments">
		<h2>
		<?php
		if ( get_option( 'woocommerce_enable_review_rating' ) === 'yes' && $reviews_count ) {
			/* translators: %1: reviews_count %2: Title */
			printf( _n( '%1$s review for %2$s', '%1$s reviews for %2$s', $reviews_count, 'yith-woocommerce-advanced-reviews' ), $reviews_count, get_the_title() );//phpcs:ignore --_n scaping
		} else {
			esc_html_e( 'Reviews', 'yith-woocommerce-advanced-reviews' );
		}
		?>
		</h2>

		<?php if ( $reviews_count ) : ?>
			<?php do_action( 'yith_advanced_reviews_before_review_list', $product ); ?>

			<ol class="commentlist">
				<?php $YWAR_AdvancedReview->reviews_list( $product_id ); // phpcs:ignore WordPress.NamingConventions ?>
			</ol>
		<?php else : ?>

			<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'yith-woocommerce-advanced-reviews' ); ?></p>

		<?php endif; ?>
	</div>

	<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product_id ) ) : ?>

		<div id="review_form_wrapper">
			<div id="review_form">
				<?php
				$commenter = wp_get_current_commenter();

				$comment_form = array(
					'title_reply'          => $reviews_count ? esc_html__( 'Add a review', 'yith-woocommerce-advanced-reviews' ) : esc_html__( 'Be the first to review', 'yith-woocommerce-advanced-reviews' ) . ' &ldquo;' . get_the_title() . '&rdquo;',
					/* translators: %s: string */
					'title_reply_to'       => esc_html__( 'Write a reply to %s', 'yith-woocommerce-advanced-reviews' ),
					'comment_notes_before' => '',
					'comment_notes_after'  => '',
					'fields'               => array(
						'author' => '<p class="comment-form-author"> <label for="author">' . esc_html__( 'Name', 'yith-woocommerce-advanced-reviews' ) . ' <span class="required">*</span></label> ' .
									'<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" aria-required="true" /></p>',
						'email'  => '<p class="comment-form-email"><label for="email">' . esc_html__( 'Email', 'yith-woocommerce-advanced-reviews' ) . ' <span class="required">*</span></label> ' .
									'<input id="email" name="email" type="text" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" aria-required="true" /></p>',
					),
					'label_submit'         => esc_html__( 'Submit', 'yith-woocommerce-advanced-reviews' ),
					'logged_in_as'         => '',
					'comment_field'        => '',
				);

				$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your Review', 'yith-woocommerce-advanced-reviews' ) . '</label><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>';

				$comment_form['comment_field'] .= '<input type="hidden" name="action" value="submit-form" />';
				comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
				?>
			</div>
		</div>

	<?php else : ?>

		<p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may write a review.', 'yith-woocommerce-advanced-reviews' ); ?></p>

	<?php endif; ?>

	<div class="clear"></div>
</div>
