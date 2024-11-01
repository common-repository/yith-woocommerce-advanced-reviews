<?php
/**
 * Reviews template
 *
 * @package yith-woocommerce-advanced-reviews\templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


global $review_stats;
global $product;
$product_id             = yit_get_prop( $product, 'id' );
$customer_reviews_label = apply_filters( 'ywar_customer_review_label', esc_html__( 'Customers\' review', 'yith-woocommerce-advanced-reviews' ) );
?>

<div id="reviews_summary">

	<h3><?php echo esc_html( $customer_reviews_label ); ?></h3>

	<?php do_action( 'ywar_summary_prepend', $product ); ?>

	<div class="reviews_bar">

		<?php
		for ( $i = 5; $i >= 1; $i-- ) :
			$perc = ( 0 === $review_stats['total'] ) ? 0 : floor( $review_stats[ $i ] / $review_stats['total'] * 100 );
			?>

			<div class="ywar_review_row">
				<?php do_action( 'ywar_summary_row_prepend', $i, $product_id ); ?>

				<span
					class="ywar_stars_value"><?php printf( _n( '%s star', '%s stars', $i, 'yith-woocommerce-advanced-reviews' ), $i ); //phpcs:ignore --_n scaping?></span>
				<span class="ywar_num_reviews"><?php echo wp_kses( $review_stats[ $i ], 'post' ); ?></span>
				<span class="ywar_rating_bar">
					<span style="background-color:<?php echo esc_attr( get_option( 'ywar_summary_bar_color' ) ); ?>"
						class="ywar_scala_rating">
						<span class="ywar_perc_rating"
							style="width: <?php echo esc_attr( $perc ); ?>%; background-color:<?php echo esc_attr( get_option( 'ywar_summary_percentage_bar_color' ) ); ?>">
							<?php if ( 'yes' === get_option( 'ywar_summary_percentage_value' ) ) : ?>
								<span style="color:<?php echo esc_attr( get_option( 'ywar_summary_percentage_value_color' ) ); ?>"
									class="ywar_perc_value"><?php printf( '%s %%', wp_kses( $perc, 'post' ) ); ?></span>
							<?php endif; ?>
						</span>
					</span>
				</span>

				<?php do_action( 'ywar_summary_row_append', $i, $product_id ); ?>
			</div>
		<?php endfor; ?>
	</div>

	<?php do_action( 'ywar_summary_append' ); ?>

	<div id="reviews_header">
		<?php do_action( 'ywar_reviews_header', $review_stats ); ?>
	</div>
</div>
