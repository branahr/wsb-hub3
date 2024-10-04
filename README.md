# Woocommerce WSB HUB3 plugin

WSB HUB3 is a plugin for Woocommerce that shows all needed data for Croatian banks direct transfer payment, including on-the-fly generated barcode for mobile banking payment. Payment details and barcode are shown on thankyou page, order details page and also in notification email. Details are visible only if selected payment method is Bank transfer and order status is the one selected in general settings (default: on-hold).
Plugin uses [bigfish.software](https://hub3.bigfish.software]) API to generate barcodes.
You can display payment details to the customer either in text/html format, or generated HUB-3A slip in jpg with all details on it.
If admin updates an order from the backend (i.e. adds a new product to the order or apply a coupon code), barcode and HUB3 slip will be re-created.

Plugin page in WordPress repository: [WSB HUB3](https://hr.wordpress.org/plugins/wsb-hub3/)

## Requirements

    - PHP version 5.6 and above
    - GD library installed on server
    - Wordpress version 5.0 and above
    - Woocommerce plugin installed and enabled (v 4.0 or greater)
    - Direct Bank Transfer payment plugin (BACS) enabled
    - EUR as a default payment currency

### Features

- Sequential order number plugins supported
- Multiple IBANs (Works only for standard checkout, not with BLOCKS!)
- Selectable display options for payment details
- JPG, PNG or GIF format can be selected for barcode image
- Adjustable reference number pattern
- Payment details on thankyou page, order details page and in notification email.
- Barcode image in your favorite color
- Payment details shown only to Croatian customers
- Placeholder [order] can be used in payment description (order ID)
- Shortcodes for HUB3 slip and barcode display on custom thankyou page

### Reference number pattern

For payment reference number you can select one of several predefined patterns:

- order (can be custom order number provided by other plugin)
- date
- order-date
- date-order

If you use date in the reference number, then you can select its format:

- ddmmyyyy
- ddmmyy
- ddmm
- mmyyyy
- mmyy
- yyyy
- yy

Also you can add sufix and/or prefix to the reference number (up to 6 digits for each)

## Installation

1. Upload entire `wsb-hub3` folder to your site's `/wp-content/plugins/` directory. You can also use the _Add new_
   option in the _Plugins_ menu in WordPress.
2. Activate the plugin from the _Plugins_ menu in WordPress.
3. Find _HUB3_ tab under Woocommerce settings for HUB3 options

## Changelog

### 3.0.0

- Enhancement: Compatibility with Woocommerce HPOS
- Fix:

### 2.0.5

- Fix: warning in BACS foreach loop
- Enhancement: Show/Hide list of bank accounts on thankyou page

### 2.0.4

- Fix: warning in BACS foreach loop
- Enhancement: WP 6.2 compatibility

### 2.0.3

- Fix: 1 cent rounding error after conversion to EUR
- Enhancement: validation improved and adapted to https://hub3.bigfish.software/ API
- Enhancement: company name and receiver name can now contain "&" character
- Enhancement: slip template size in KB reduced

### 2.0.2

- Fix: Payment short description not shown

### 2.0.1

- Fix: Decimal places round error

## 2.0

- Feature: added support for many sequential order number plugins
- Feature: multiple bank accounts / IBANs
- Enhancement: company name can be shown on a payment slip
- Enhancement: added support for EUR currency

## 1.2.1

- Fix: No HUB3 slip created if "allow_url_fopen" is set to "Off"

### 1.2

- Enhancement: Checking if WooCommerce is active

## 1.1

- Fix: API server SSL bug

### 1.0.7

- Enhancement: added option to use the plugin only for customers from Croatia
- Fix: error in validation regex pattern

## 1.0.6

- Feature: added support for order numbers generated by plugin "Booster For WooCommerce"
- Feature: shortcodes to show HUB3 slip and/or barcode on custom thankyou page
- Fix: empty model changed to 00 by default

### 1.0.5

- Fix: limited number of characters according to barcode API specification

### 1.0.4

- Feature: admin can enable or disable sending HUB3 slip and barcode in admin notification email
- Fix: "-" (minus) and "," (comma) signs can be used now in recipient name

### 1.0.3

- Enhancement: re-create a barcode and HUB3 slip on admin manual order update
- Fix: loading css and js plugin files only on pages where needed
- Fix: disabled sending hub3 slip and barcode to the admin email

## 1.0.2

- Enhancement: Added option for width in pixels for HUB-3A slip and barcode
- Enhancement: Added link to enlarged slip in a separate window
- Fix: missing default values for several plugin options
- Fix: color picker for barcode color

## 1.0.1

- Feature: added HUB-3A generator for slip with all payment details
- Enhancement: Selectable display options for payment details
- Fix: correction to readme.txt description

## 1.0.0

- Initial release of the plugin.
