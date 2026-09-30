# Woocommerce WSB HUB3 plugin

WSB HUB3 is a plugin for Woocommerce that shows all needed data for Croatian banks direct transfer payment, including on-the-fly generated barcode for mobile banking payment. Payment details and barcode are shown on thankyou page, order details page and also in notification email. Details are visible only if selected payment method is Bank transfer and order status is the one selected in general settings (default: on-hold).
Plugin uses [bigfish.software](https://hub3.bigfish.software) API to generate barcodes.
You can display payment details to the customer either in text/html format, or generated HUB-3A slip in jpg with all details on it.
If admin updates an order from the backend (i.e. adds a new product to the order or apply a coupon code), barcode and HUB3 slip will be re-created.

3.0.1: If there is more than one IBAN (bank accounts) in BACS payment method, customer can choose which one to use for payment at checkout. If only one IBAN, no choice is shown.

3.1.0: Big update focused on payments that banks accept, a better customer experience and privacy:

- **FINA rules for payment models and references**: choose HR00, HR01 (check digit added automatically) or HR99. The reference follows FINA rules (at most 3 parts, digits only, 22 characters), and the settings show a live preview for your latest order.
- **HUB-3 validation**: settings accept only values the HUB-3 barcode allows, with live character counters, input masks and IBAN check digit validation. Croatian letters and text pasted from other documents are handled correctly.
- **Bank account choice in block and classic checkout**: with more than one Direct bank transfer account, customers choose one at checkout (radio buttons under the payment methods), and the slip and barcode use that account.
- **Images embedded in emails**: slip and barcode are sent inside the email, so they show even when the website is not reachable (e.g. Cloudflare "Under attack" mode) or the email client blocks external images.
- **Safer image storage**: images are stored in the uploads folder, so plugin updates no longer delete them, and file names contain a random part, so other customers' slips can't be opened by guessing an order number. Missing images are recreated automatically.
- **Shortcodes on custom thankyou pages**: `[wsb_hub3]` and `[wsb_barcode]` now work on any page, shown only with a valid order key or to the customer who placed the order. The new _Nothing (use shortcodes)_ option prevents showing payment details twice.
- **Clear messages when something is wrong**: admin notices for settings that banks or the barcode service would reject, and order notes and WooCommerce logs when a barcode can't be generated or the reference had to be adjusted.
- **Compatibility**: declared compatible with WooCommerce Cart and Checkout blocks and HPOS; bundled Croatian translations fill in strings missing from the WordPress.org language pack.

After updating, check the payment model and reference in HUB3 recipient settings, and the bank accounts in the Direct bank transfer payment method.

Plugin page in WordPress repository: [WSB HUB3](https://hr.wordpress.org/plugins/wsb-hub3/)

## Requirements

- PHP version 7.4 and above
- GD library installed on server
- Wordpress version 5.0 and above
- Woocommerce plugin installed and enabled (v 7.1 or greater)
- Direct Bank Transfer payment plugin (BACS) enabled
- EUR as a default payment currency

### Features

- Sequential order number plugins supported
- Multiple IBANs: customers choose the bank account at checkout (block and classic checkout)
- Selectable display options for payment details
- JPG, PNG or GIF format can be selected for barcode image
- Adjustable reference number pattern with live preview in settings
- Payment models HR00, HR01 (check digit added automatically) and HR99
- Validation according to HUB-3 and FINA rules, with live character counters and input masks in settings
- IBAN check digit validation
- Payment details on thankyou page, order details page and in notification email.
- Slip and barcode embedded in emails, so they show even if the website is not reachable
- Barcode image in your favorite color
- Payment details shown only to Croatian customers (optional)
- Placeholder [order] can be used in payment description (order ID)
- Shortcodes for HUB3 slip and barcode display on custom thankyou page
- Order notes and WooCommerce logs when a barcode can't be generated

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

Also you can add sufix and/or prefix to the reference number (up to 6 digits for each). The recipient settings show a live preview of the reference for your latest order.

The reference follows FINA rules:

- at most 3 parts, so with order-date or date-order use either a prefix or a sufix, not both
- digits only; other characters in order numbers (e.g. WEB-123) are removed
- at most 12 digits per part and 22 characters in total, including hyphens

If a reference would still be too long (e.g. when order numbers grow), the sufix, then the prefix, then the date are left out. The order number is always kept, and a note is added to the order.

### Payment model

- HR00: reference without check digit
- HR01: check digit (MOD11INI) is added to the reference automatically
- HR99: payment without reference

### Recipient data limits

The HUB-3 barcode allows only a limited number of characters, so the settings don't accept longer values:

| Field               | Limit                                                                                                    |
| ------------------- | -------------------------------------------------------------------------------------------------------- |
| Recipient name      | 25 characters                                                                                            |
| Address             | 25 characters                                                                                            |
| Postcode            | 5 digits                                                                                                 |
| City                | 21 characters (postcode and city together can have 27)                                                   |
| IBAN                | HR and 19 digits, with valid check digits                                                                |
| Purpose code        | 4 capital letters (e.g. OTHR)                                                                            |
| Payment description | 35 characters; when [order] is replaced with the order number, the text around it is shortened if needed |

If recipient data saved with an older plugin version is too long, a notice is shown in the admin area.

### Bank accounts (IBAN)

The slip and barcode use the bank accounts of the _Direct bank transfer_ payment method:

- one account: it is used automatically
- more accounts: the customer chooses one at checkout, shown as radio buttons under the payment methods (block checkout) or in the bank transfer description (classic checkout)
- no account with a valid IBAN: the IBAN from HUB3 recipient settings is used

When _Show bank accounts_ is enabled, the chosen account is listed first and marked on the thankyou page and in emails.

### Emails

Slip and barcode are embedded in emails, not linked from the website. They show even when the website is not reachable (e.g. Cloudflare "Under attack" mode) and in email clients that block external images. If your email plugin sends emails through an API that doesn't support embedded images, turn off _Embed images in emails_ in the general settings.

### Shortcodes

- `[wsb_hub3 width="1100"]` shows the HUB-3A slip
- `[wsb_barcode width="400"]` shows the barcode

Use them in the _Order Confirmation_ template (block themes), on the checkout page (classic themes) or on a custom thankyou page that has `order_id` and `key` in its URL (e.g. `?order_id=123&key=wc_order_...`). They show images only for a valid order key in the URL or to the logged-in customer who placed the order. To avoid showing payment details twice, set _Show on thankyou page_ to _Nothing (use shortcodes)_.

### Where are the images stored?

Slips and barcodes are stored in `wp-content/uploads/wsb-hub3/`, with a random part in each file name, so they can't be found by guessing an order number. Images of orders from older plugin versions are moved there or recreated automatically when needed.

## Installation

1. Upload entire `wsb-hub3` folder to your site's `/wp-content/plugins/` directory. You can also use the _Add new_
   option in the _Plugins_ menu in WordPress.
2. Activate the plugin from the _Plugins_ menu in WordPress.
3. Find _HUB3_ tab under Woocommerce settings for HUB3 options

## Frequently Asked Questions

### Slip and barcode are not shown in emails

Since version 3.1.0 images are embedded in emails, so they show even if the website is behind Cloudflare "Under attack" mode or a firewall. Check that _Embed images in emails_ is turned on in the general settings. If your email plugin sends emails through an API that doesn't support embedded images, turn the option off; images are then loaded from the website.

### The barcode is missing. How do I find out why?

Open the order in the admin area: a note explains why the barcode couldn't be generated. Details are also in _WooCommerce > Status > Logs_ (source: wsb-hub3). The most common reason is recipient data or a payment reference that is longer than the HUB-3 standard allows; in that case a notice is also shown in the admin area.

### Why was the payment reference changed?

FINA allows at most 3 parts, 12 digits per part and 22 characters in total. If the reference is longer, the sufix, then the prefix, then the date are left out, so banks accept the payment. A note is added to the order. Use the reference preview in the recipient settings to choose a layout that fits.

### Which IBAN is shown on the slip?

The account the customer chose at checkout from the _Direct bank transfer_ bank accounts. If there is only one account it is used automatically, and if there is none, the IBAN from HUB3 recipient settings is used. Orders placed with the block checkout before version 3.1.0 keep the IBAN from HUB3 recipient settings.

### Which payment model should I use?

Use HR00 if you don't need a check digit, HR01 if you want the bank to check the reference with a check digit (added automatically), or HR99 if you don't use a reference at all.

## Changelog

### 3.1.0

- Feature: Payment model selection according to FINA rules: HR00, HR01 (check digit added automatically) and HR99 (no reference)
- Feature: Live payment reference preview in recipient settings
- Feature: Slip and barcode images are embedded in emails, so they show even when the website is not reachable (e.g. Cloudflare "Under attack" mode) or the email client blocks external images
- Feature: Customers can choose the bank account (IBAN) in the block checkout, shown as radio buttons under the payment methods
- Enhancement: Classic checkout shows the bank accounts as radio buttons instead of a select list
- Enhancement: The chosen bank account is listed first and marked in the bank account list on the thankyou page and in emails
- Enhancement: Admin notices when a Direct bank transfer account has an invalid IBAN, or the IBAN in HUB3 settings is not one of the bank accounts
- Enhancement: Declared compatibility with WooCommerce Cart and Checkout blocks
- Enhancement: Bundled translations are used for strings missing from the WordPress.org language pack
- Enhancement: Payment reference follows FINA rules (at most 3 parts, 12 digits per part, digits only, 22 characters)
- Enhancement: Length limits and live character counters for settings fields, according to HUB-3 limits
- Enhancement: Input masks for IBAN, postcode, reference prefix/sufix and purpose code, with purpose code suggestions
- Enhancement: IBAN check digit validation
- Enhancement: Admin notices when saved recipient data or reference settings don't meet HUB-3 or FINA rules
- Enhancement: Order notes and WooCommerce log entries when a barcode can't be generated or the reference is adjusted
- Enhancement: Long text is scaled to fit on the HUB-3A slip
- Enhancement: Images are stored in uploads/wsb-hub3, so plugin updates no longer delete them
- Enhancement: Missing images are recreated automatically when an order page, email or shortcode needs them
- Fix: Barcode service errors were saved as barcode images
- Fix: Croatian letters counted as two characters in recipient fields, and hidden characters in pasted text were rejected
- Fix: Payer name or address with Croatian letters could break barcode generation
- Fix: "&" in payer name was shown as "&amp;" in the barcode
- Fix: Payment description could exceed 35 characters after inserting the order number
- Fix: Reference date format validation
- Fix: [wsb_hub3] and [wsb_barcode] shortcodes caused a fatal error
- Fix: [wsb_hub3] and [wsb_barcode] shortcodes showed nothing on custom thankyou pages, because they worked only on the checkout page
- Enhancement: New _Show on thankyou page_ option _Nothing (use shortcodes)_, so payment details are not shown twice
- Security: Shortcode width is limited to a number
- Fix: With the block checkout, the slip used the IBAN from HUB3 settings instead of a Direct bank transfer account
- Fix: With one bank account, the classic checkout printed a hidden field outside the payment method
- Security: Only IBANs of Direct bank transfer accounts are accepted as the customer's choice
- Security: Image file names contain a random token, so other customers' slips can't be opened by guessing the order number
- Security: [wsb_hub3] and [wsb_barcode] shortcodes show images only with a valid order key or to the logged-in customer
- Security: SSL certificate verification enabled for barcode service requests

### 3.0.2

- Enhancement: Compatibility with WP 6.8 and WooCommerce 9.8

### 3.0.1

- Fix: Payment description and IBAN select list in frontend

### 3.0.0

- Enhancement: Compatibility with Woocommerce HPOS
- Fix: GD compatibility

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
