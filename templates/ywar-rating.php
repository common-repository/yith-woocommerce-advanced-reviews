<?php
/**
 * Review rating template
 *
 * @package yith-woocommerce-advanced-reviews\templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $product;
global $YWAR_AdvancedReview; // phpcs:ignore WordPress.NamingConventions

if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' ) {
	return;
}
$product_id   = yit_get_prop( $product, 'id' );
$review_count = count( $YWAR_AdvancedReview->get_product_reviews( $product_id ) );// phpcs:ignore WordPress.NamingConventions
$rating_count = $review_count;
$average      = $YWAR_AdvancedReview->get_average_rating( $product_id );// phpcs:ignore WordPress.NamingConventions

if ( $rating_count > 0 ) : ?>
	<div class="woocommerce-product-rating" itemprop="aggregateRating" itemscope
		itemtype="http://schema.org/AggregateRating">
		<div class="star-rating" title="
		<?php
		printf(
			/* translators: %s: average */
			esc_html__( 'Rated %s out of 5', 'yith-woocommerce-advanced-reviews' ),
			wp_kses( $average, 'post' )
		);
		?>
">
			<span style="width:<?php echo( ( wp_kses( $average, 'post' ) / 5 ) * 100 ); ?>%">
				<strong itemprop="ratingValue"
						class="rating"><?php echo esc_html( $average ); ?></strong> 
												<?php
													printf(
													/* translators: %1: html %2: html */
														esc_html__( 'out of %1$s5%2$s', 'yith-woocommerce-advanced-reviews' ),
														'<span itemprop="bestRating">',
														'</span>'
													);
												?>
				<?php printf( _n( 'based on %s customer rating', 'based on %s customer ratings', $rating_count, 'yith-woocommerce-advanced-reviews' ), '<span itemprop="ratingCount" class="rating">' . $rating_count . '</span>' ); //phpcs:ignore --_n scaping?>
			</span>
		</div>

		<?php
		if ( comments_open() ) :
			?>
			<a href="#reviews" class="woocommerce-review-link" rel="nofollow">
			(<?php printf( _n( '%s customer review', '%s customer reviews', $review_count, 'yith-woocommerce-advanced-reviews' ), '<span itemprop="reviewCount" class="count">' . $review_count . '</span>' ); //phpcs:ignore --_n scaping?>
			)</a><?php endif ?>
	</div>

<?php endif; ?>
