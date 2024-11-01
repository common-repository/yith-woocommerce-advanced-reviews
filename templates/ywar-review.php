<?php
/**
 * Review template
 *
 * @package yith-woocommerce-advanced-reviews\templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $YWAR_AdvancedReview;// phpcs:ignore WordPress.NamingConventions

$rating      = $YWAR_AdvancedReview->get_meta_value_rating( $review->ID );// phpcs:ignore WordPress.NamingConventions
$approved    = $YWAR_AdvancedReview->get_meta_value_approved( $review->ID );// phpcs:ignore WordPress.NamingConventions
$product_id  = $YWAR_AdvancedReview->get_meta_value_product_id( $review->ID );// phpcs:ignore WordPress.NamingConventions
$review_date = mysql2date( get_option( 'date_format' ), $review->post_date );

$author = $YWAR_AdvancedReview->get_meta_value_author( $review->ID );// phpcs:ignore WordPress.NamingConventions
$user   = isset( $author['review_user_id'] ) ? get_userdata( $author['review_user_id'] ) : null;

if ( $user ) {
	$author_name = $user->display_name;
} elseif ( isset( $author['review_user_id'] ) ) {
	$author_name = $author['review_author'];
} else {
	$author_name = esc_html__( 'Anonymous', 'yith-woocommerce-advanced-reviews' );
}

?>

<?php apply_filters( 'yith_advanced_reviews_before_review', $review ); ?>

<li itemprop="review" itemscope itemtype="http://schema.org/Review" id="li-comment-<?php echo esc_attr( $review->ID ); ?>">

	<div id="comment-<?php echo esc_attr( $review->ID ); ?>" class="comment_container">

		<?php
		if ( $user ) :
			echo get_avatar( $user->ID, apply_filters( 'woocommerce_review_gravatar_size', '60' ) );
		else :
			echo get_avatar( $author['review_author_email'], apply_filters( 'woocommerce_review_gravatar_size', '60' ) );
		endif;
		?>

		<div class="comment-text">

			<?php if ( $rating && get_option( 'woocommerce_enable_review_rating' ) === 'yes' ) : ?>

				<div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="star-rating" title="
				<?php
				echo sprintf(
					/* translators: %s: Name of a city */
					esc_html__( 'Rated %d out of 5', 'yith-woocommerce-advanced-reviews' ),
					esc_attr( $rating )
				);
				?>
					">
					<span style="width:<?php echo ( esc_attr( $rating ) / 5 ) * 100; ?>%"><strong
							itemprop="ratingValue"><?php echo esc_attr( $rating ); ?></strong> <?php esc_html_e( 'out of 5', 'yith-woocommerce-advanced-reviews' ); ?></span>
				</div>

			<?php endif; ?>

			<?php if ( '0' === $approved ) : ?>

				<p class="meta"><em><?php esc_html_e( 'Your comment is waiting for approval', 'yith-woocommerce-advanced-reviews' ); ?></em></p>

			<?php else : ?>

				<p class="meta">
					<strong itemprop="author"><?php echo wp_kses( $author_name, 'post' ); ?></strong> 
														<?php

														if ( $user && get_option( 'woocommerce_review_rating_verification_label' ) === 'yes' ) {
															if ( wc_customer_bought_product( $user->user_email, $user->ID, $product_id ) ) {
																echo '<em class="verified">(' . esc_html__( 'verified owner', 'yith-woocommerce-advanced-reviews' ) . ')</em> ';
															}
														}
														?>
					<time itemprop="datePublished"
						datetime="<?php echo esc_attr( mysql2date( 'c', ( $review_date ) ) ); ?>"><?php echo wp_kses( $review_date, 'post' ); ?></time>
				</p>

			<?php endif; ?>

			<div itemprop="description" class="description">
				<p><?php echo wp_kses( apply_filters( 'yith_advanced_reviews_review_content', $review ), 'post' ); ?></p>
			</div>
		</div>
	</div>
</li>
