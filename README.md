# WooCommerce Expiry Acknowledgement

A lightweight WooCommerce plugin that requires customers to acknowledge the product expiry date before adding eligible products to the cart.

The plugin is useful for stores that sell products where expiry-date visibility matters, such as health, beauty, food, supplement, or inventory-sensitive products.

## Features

- Adds a required expiry-date confirmation checkbox on WooCommerce product pages.
- Validates the confirmation server-side before adding the product to the cart.
- Redirects archive/shop add-to-cart buttons to the product page so the acknowledgement cannot be skipped.
- Works by default for all products.
- Allows disabling the acknowledgement per product from the WooCommerce product editor.
- Includes RTL-friendly styling and WoodMart-compatible CSS variables.

## Installation

1. Download the plugin ZIP.
2. In WordPress, go to **Plugins > Add New > Upload Plugin**.
3. Upload the ZIP file and activate the plugin.
4. Make sure WooCommerce is active.

## Usage

After activation, the acknowledgement checkbox is enabled for all WooCommerce products by default.

To disable it for a specific product:

1. Edit the product in WordPress.
2. Open **Product data > General**.
3. Set **Expiry date acknowledgement** to **Disabled for this product**.
4. Save the product.

## Requirements

- WordPress 6.0 or newer
- WooCommerce 7.0 or newer
- PHP 7.4 or newer

## Repository Name

Recommended GitHub repository name:

```text
woocommerce-expiry-acknowledgement
```

Alternative shorter names:

- `wc-expiry-ack`
- `woocommerce-expiry-confirmation`

## License

GPL-2.0-or-later
