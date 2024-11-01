<?php
/**
 * Product reviews table
 *
 * @package yith-woocommerce-advanced-reviews\templates
 */

?>

<div class="wrap">

	<h2><?php esc_html_e( 'Product reviews', 'yith-woocommerce-advanced-reviews' ); ?></h2>

	<?php $product_reviews->views(); ?>

	<form id="ywar-reviews" method="get">
		<input type="hidden" name="page" value="<?php echo isset( $_REQUEST['page'] ) ? wp_kses( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ), 'post' ) : ''; //phpcs:ignore WordPress.Security.NonceVerification ?>" />
		<?php $product_reviews->search_box( esc_html__( 'Search reviews', 'yith-woocommerce-advanced-reviews' ), 'yith-woocommerce-advanced-reviews' ); ?>
		<?php $product_reviews->display(); ?>
	</form>
</div>
