<?php
/**
 * Plugin Name: WooCommerce Expiry Acknowledgement
 * Description: Adds a required expiry-date acknowledgement before customers can add selected WooCommerce products to the cart.
 * Version: 1.0.0
 * Author: Nima Shadman
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * Text Domain: wc-expiry-ack
 */

defined( 'ABSPATH' ) || exit;

final class WC_Expiry_Acknowledgement {
	const PRODUCT_MODE_META = '_wcea_confirmation_mode';
	const FIELD_NAME        = 'wcea_expiry_acknowledged';

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'render_confirmation' ), 25 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_confirmation' ), 10, 5 );
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'replace_loop_button' ), 10, 3 );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'product_option' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_option' ) );
	}

	public function enqueue_styles() {
		if ( is_product() ) {
			wp_enqueue_style(
				'wcea-style',
				plugin_dir_url( __FILE__ ) . 'assets/style.css',
				array(),
				'1.0.0'
			);
		}
	}

	private function needs_confirmation( $product_id ) {
		$product_id = absint( $product_id );
		if ( 'product_variation' === get_post_type( $product_id ) ) {
			$product_id = (int) wp_get_post_parent_id( $product_id );
		}

		$mode = get_post_meta( $product_id, self::PRODUCT_MODE_META, true );
		return 'disabled' !== $mode;
	}

	public function render_confirmation() {
		global $product;
		if ( ! $product instanceof WC_Product || ! $this->needs_confirmation( $product->get_id() ) ) {
			return;
		}

		wp_nonce_field( 'wcea_add_to_cart', 'wcea_nonce' );
		?>
		<label class="wcea-confirmation" for="wcea-expiry-acknowledged">
			<input type="checkbox" id="wcea-expiry-acknowledged" name="<?php echo esc_attr( self::FIELD_NAME ); ?>" value="yes" required aria-required="true">
			<span><?php echo esc_html__( 'تاریخ انقضای درج‌شده در صفحه محصول را مشاهده کرده‌ام.', 'wc-expiry-ack' ); ?></span>
		</label>
		<?php
	}

	public function validate_confirmation( $passed, $product_id, $quantity, $variation_id = 0, $variations = array() ) {
		$checked_product_id = $variation_id ? $variation_id : $product_id;
		if ( ! $this->needs_confirmation( $checked_product_id ) ) {
			return $passed;
		}

		$nonce_valid = isset( $_POST['wcea_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcea_nonce'] ) ), 'wcea_add_to_cart' );
		$confirmed   = isset( $_POST[ self::FIELD_NAME ] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST[ self::FIELD_NAME ] ) );

		if ( ! $nonce_valid || ! $confirmed ) {
			wc_add_notice( __( 'لطفاً مشاهده تاریخ انقضای کالا را تأیید کنید.', 'wc-expiry-ack' ), 'error' );
			return false;
		}

		return $passed;
	}

	public function replace_loop_button( $html, $product, $args ) {
		if ( ! $product instanceof WC_Product || ! $this->needs_confirmation( $product->get_id() ) ) {
			return $html;
		}

		return sprintf(
			'<a href="%1$s" class="button product_type_%2$s">%3$s</a>',
			esc_url( $product->get_permalink() ),
			esc_attr( $product->get_type() ),
			esc_html__( 'مشاهده و انتخاب', 'wc-expiry-ack' )
		);
	}

	public function product_option() {
		woocommerce_wp_select(
			array(
				'id'          => self::PRODUCT_MODE_META,
				'label'       => __( 'تأیید تاریخ انقضا', 'wc-expiry-ack' ),
				'description' => __( 'به‌صورت پیش‌فرض تأیید برای همه محصولات لازم است. برای این محصول می‌توانید آن را غیرفعال کنید.', 'wc-expiry-ack' ),
				'desc_tip'    => true,
				'options'     => array(
					''         => __( 'فعال (پیش‌فرض)', 'wc-expiry-ack' ),
					'disabled' => __( 'غیرفعال برای این محصول', 'wc-expiry-ack' ),
				),
			)
		);
	}

	public function save_product_option( $product_id ) {
		if ( ! current_user_can( 'edit_post', $product_id ) ) {
			return;
		}

		$mode = isset( $_POST[ self::PRODUCT_MODE_META ] ) ? sanitize_key( wp_unslash( $_POST[ self::PRODUCT_MODE_META ] ) ) : '';
		if ( 'disabled' === $mode ) {
			update_post_meta( $product_id, self::PRODUCT_MODE_META, 'disabled' );
		} else {
			delete_post_meta( $product_id, self::PRODUCT_MODE_META );
		}
	}
}

add_action(
	'plugins_loaded',
	static function () {
		if ( class_exists( 'WooCommerce' ) ) {
			new WC_Expiry_Acknowledgement();
		}
	}
);

