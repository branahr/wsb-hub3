<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.webstudiobrana.com
 * @since      1.0.0
 *
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/admin
 * @author     Branko Borilovic <brana.hr@gmail.com>
 */
class Wsb_Hub3_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Validator class
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wsb_Hub3_Validator   $validator  
	 */
	protected $validator;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wsb-hub3-validator.php';
		$this->validator = new Wsb_Hub3_Validator();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wsb-hub3-admin.css', array(), $this->version . '.' . filemtime( plugin_dir_path( __FILE__ ) . 'css/wsb-hub3-admin.css' ), 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.2
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wsb-hub3-admin.js', array( 'wp-color-picker' ), $this->version . '.' . filemtime( plugin_dir_path( __FILE__ ) . 'js/wsb-hub3-admin.js' ), true);
		wp_localize_script( $this->plugin_name, 'wsbHub3', array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'wsb_hub3_reference_preview' ),
			// Common ISO 20022 purpose codes.
			'purposeCodes' => array(
				'OTHR' => __( 'Other', 'wsb-hub3' ),
				'GDSV' => __( 'Purchase of goods and services', 'wsb-hub3' ),
				'GDDS' => __( 'Purchase of goods', 'wsb-hub3' ),
				'SCVE' => __( 'Purchase of services', 'wsb-hub3' ),
				'SUPP' => __( 'Supplier payment', 'wsb-hub3' ),
				'COMC' => __( 'Commercial payment', 'wsb-hub3' ),
			),
		) );

	}

	/**
 	* Adding HUB3 to the Woocommerce settings tabs
 	*/
	 public static function add_wsb_hub3_settings_tab( $tabs ) {
		$tabs['wsb_hub3_admin_tab'] = __( 'HUB3', 'wsb-hub3' );
		return $tabs;
	}

	public function wsb_hub3_get_settings() {

		global $current_section;
		$settings = array();
		if ( $current_section == 'barcode' ) {
			$settings = $this->wsb_hub3_barcode_settings();
		} else if( $current_section == 'receiver' ){
			$settings = $this->wsb_hub3_receiver_settings();
		} else {
			$settings = $this->wsb_hub3_general_settings();
		}

		return apply_filters( 'woocommerce_get_settings_wsb_hub3_admin_tab', $settings );
	}

	public function wsb_hub3_general_settings() {

		$settings = array(
                'section_title' => array(
                    'name'  => __( 'General settings', 'wsb-hub3' ),
                    'type'  => 'title',
                    'desc'  => '',
					'class' => 'wsb-hub3-options-title',
                    'id'    => 'wc_wsb_hub3_admin_tab_section_general_settings_title'
				), 
				'wsb_hub3_croatian_customers_only' => array(
                    'name'    => __( 'For Croatian customers only', 'wsb-hub3' ),
                    'type'    => 'checkbox',
                    'default' => 'no',
                    'desc'    => __( 'Enable only for customers with Croatian billing address', 'wsb-hub3' ),
                    'id'      => 'wsb_hub3_croatian_customers_only',
				),
				'wsb_hub3_order_status' => array(
					'title'       => __( 'Order Status to display data', 'wsb-hub3' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'description' => __( 'Data will be shown only on this order status.', 'wsb-hub3' ),
					'default'     => 'wc-on-hold',
					'options'     => wc_get_order_statuses(),
					'id'		  =>'wsb_hub3_order_status',
					'desc_tip'    => true,
				),
				'wsb_hub3_bank_accounts_display' => array(
                    'name'    => __( 'Show bank accounts', 'wsb-hub3' ),
                    'type'    => 'checkbox',
                    'default' => 'no',
                    'desc'    => __( 'Show the list of BACS bank accounts on thankyou page', 'wsb-hub3' ),
                    'id'      => 'wsb_hub3_bank_accounts_display',
				),
				'wsb_hub3_description_text' => array(
                    'name'        => __( 'Text above payment details', 'wsb-hub3' ),
                    'type'        => 'textarea',
                    'desc'        => __( 'Text to be shown above payment details', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_description_text',
					'custom_attributes' => array( 'maxlength' => 150, 'data-wsb-counter' => '1' ),
					'default' 	  => '',
					'desc_tip'=> true
				),
				'wsb_hub3_barcode_text' => array(
                    'name'        => __( 'Text above barcode', 'wsb-hub3' ),
                    'type'        => 'textarea',
                    'desc'        => __( 'Text to be shown above barcode', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_barcode_text',
					'custom_attributes' => array( 'maxlength' => 150, 'data-wsb-counter' => '1' ),
					'default' 	  => '',
					'desc_tip'=> true
				),
				'wsb_hub3_display_details_thankyou' => array(
                    'name'    => __( 'Show on thankyou page', 'wsb-hub3' ),
                    'type'    => 'select',
                    'class'   => 'wsb-hub3-admin-tab-field',
                    'desc'    => __( 'Choose "Nothing" if you show payment details with the [wsb_hub3] and [wsb_barcode] shortcodes, so they are not shown twice.', 'wsb-hub3' ),
                    'id'      => 'wsb_hub3_display_details_thankyou',
                    'options' => array(
                      'html'    => __( 'Html text + large barcode', 'wsb-hub3' ),
					  'hub3' 	=> __( 'HUB-3A slip + large barcode', 'wsb-hub3' ),
					  'barcode' => __( 'Large barcode only', 'wsb-hub3' ),
					  'none'    => __( 'Nothing (use shortcodes)', 'wsb-hub3' ),
					),
					'default'     => 'hub3',
					'desc_tip'    => false,
				),
				'wsb_hub3_display_details_order' => array(
                    'name'    => __( 'Show on order details page', 'wsb-hub3' ),
                    'type'    => 'select',
                    'class'   => 'wsb-hub3-admin-tab-field',
                    'desc'    => __( '', 'wsb-hub3' ),
                    'id'      => 'wsb_hub3_display_details_order',
                    'options' => array(
                      'html'    => __( 'Html text + large barcode', 'wsb-hub3' ),
					  'hub3' 	=> __( 'HUB-3A slip + large barcode', 'wsb-hub3' ),
					  'barcode' => __( 'Large barcode only', 'wsb-hub3' ),
					),
					'default'     => 'hub3',
					'desc_tip'    => false,
				),
				'wsb_hub3_display_details_email' => array(
                    'name'    => __( 'Send in email', 'wsb-hub3' ),
                    'type'    => 'select',
                    'class'   => 'wsb-hub3-admin-tab-field',
                    'desc'    => __( '', 'wsb-hub3' ),
                    'id'      => 'wsb_hub3_display_details_email',
                    'options' => array(
                      'html'    => __( 'Html text + large barcode', 'wsb-hub3' ),
					  'hub3' 	=> __( 'HUB-3A slip + large barcode', 'wsb-hub3' ),
					  'barcode' => __( 'Large barcode only', 'wsb-hub3' ),
					),
					'default'     => 'hub3',
					'desc_tip'    => false,
				),
				'wsb_hub3_send_admin_slip' => array(
                    'name'    => __( 'Send slip to admin', 'wsb-hub3' ),
                    'type'    => 'checkbox',
                    'default' => 'no',
                    'desc'    => __( 'Send HUB-3A slip to admin in email', 'wsb-hub3' ),
                    'id'      => 'wsb_hub3_send_admin_slip',
				),
				'wsb_hub3_send_admin_barcode' => array(
                    'name'    => __( 'Send barcode to admin', 'wsb-hub3' ),
                    'type'    => 'checkbox',
                    'default' => 'no',
                    'desc'    => __( 'Send barcode to admin in email', 'wsb-hub3' ),
                    'id'      => 'wsb_hub3_send_admin_barcode',
                ),
				'wsb_hub3_email_embed_images' => array(
                    'name'    => __( 'Embed images in emails', 'wsb-hub3' ),
                    'type'    => 'checkbox',
                    'default' => 'yes',
                    'desc'    => __( 'Send slip and barcode inside the email instead of linking to images on the website. Images then also show when the website is not reachable, e.g. behind Cloudflare "Under attack" mode. Turn off only if your email plugin does not support embedded images.', 'wsb-hub3' ),
                    'id'      => 'wsb_hub3_email_embed_images',
                ),
				'wsb_hub3_slip_width' => array(
                    'name'        => __( 'HUB-3A slip width', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'HUB-3A slip width in pixels', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_slip_width',
					'custom_attributes' => array( 'maxlength' => 4 ),
					'default' 	  => '1100',
					'placeholder' => '1100',
					'desc_tip'=> true
				),
				'wsb_hub3_slip_width_email' => array(
                    'name'        => __( 'HUB-3A slip width in email', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Width in pixels of HUB-3A slip sent in email ', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_slip_width_email',
					'custom_attributes' => array( 'maxlength' => 4 ),
					'default' 	  => '560',
					'placeholder' => '560',
					'desc_tip'=> true
				),

                'section_end' => array(
                     'type' => 'sectionend',
                     'id'   => 'wc_wsb_hub3_admin_tab_general_settings_end',
                ),
            );
			return apply_filters( 'wc_wsb_hub3_admin_tab_settings', $settings );
	} 

	public function wsb_hub3_barcode_settings() {

		$settings = array(
                'section_title' => array(
                    'name'  => __( 'Barcode settings', 'wsb-hub3' ),
                    'type'  => 'title',
                    'desc'  => '',
					'class' => 'wsb-hub3-options-title',
                    'id'    => 'wc_wsb_hub3_admin_tab_section_barcode_settings_title'
				), 
				'wsb_hub3_img_type' => array(
                    'name'    => __( 'Barcode image type', 'wsb-hub3' ),
                    'type'    => 'select',
                    'class'   => 'wsb-hub3-admin-tab-field',
                    'desc'    => __( 'For large barcode only', 'wsb-hub3' ),
                    'id'      => 'wc_wsb_hub3_admin_tab_img_type',
                    'options' => array(
                      'jpg'    => __( 'JPG', 'wsb-hub3' ),
					  'png' 	=> __( 'PNG', 'wsb-hub3' ),
					  'gif' 	=> __( 'GIF', 'wsb-hub3' ),
					),
					'default'     => 'png',
					'desc_tip'    => true,
				),
				'wsb_hub3_barcode_width' => array(
                    'name'        => __( 'Width (website)', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Barcode width in pixels', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_barcode_width',
					'custom_attributes' => array( 'maxlength' => 4 ),
					'default' 	  => '400',
					'placeholder' => '400',
					'desc_tip'=> true
				),
				'wsb_hub3_barcode_width_email' => array(
                    'name'        => __( 'Width (email)', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Width in pixels of barcode sent in email', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_barcode_width_email',
					'custom_attributes' => array( 'maxlength' => 4 ),
					'default' 	  => '400',
					'placeholder' => '400',
					'desc_tip'=> true
				),
				'wsb_hub3_img_padding' => array(
                    'name'        => __( 'Padding', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Barcode padding in pixels', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_img_padding',
					'custom_attributes' => array( 'maxlength' => 3 ),
					'default' 	  => '20',
					'placeholder' => '20',
					'desc_tip'=> true
				),
				'wsb_hub3_img_color' => array(
                    'name'        		 => __( 'Color', 'wsb-hub3' ),
                    'type'       		 => 'text',
					'class' 	  		 => 'wsb-color-field',
                    'desc'        		 => __( 'Barcode color', 'wsb-hub3' ),
                    'id'          		 => 'wsb_hub3_img_color',
					'custom_attributes'  => array( 'maxlength' => 7 ),
					'data-default-color' => '#000000',
					'default' => '#000000',
                    'desc_tip'=> true,
                ),
                'section_end' => array(
                     'type' => 'sectionend',
                     'id'   => 'wc_wsb_hub3_admin_tab_barcode_settings_end',
                ),
            );
			return apply_filters( 'wc_wsb_hub3_admin_tab_settings', $settings );
	} 

	public function wsb_hub3_receiver_settings() {

		$settings = array(
                'section_title' => array(
                    'name'  => __( 'Recipient details', 'wsb-hub3' ),
                    'type'  => 'title',
					'class' => 'wsb-hub3-options-title',
                    'id'    => 'wc_wsb_hub3_admin_tab_section_receiver_settings_title'
				), 
				'wsb_hub3_receiver_name' => array(
                    'name'        => __( 'Recipient', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Recipient name', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_receiver_name',
					'custom_attributes' => array( 'maxlength' => Wsb_Hub3_Validator::RECEIVER_NAME_MAX, 'data-wsb-counter' => '1' ),
					'default' 	  => '',
					'desc_tip'=> true
				),
				'wsb_hub3_receiver_address' => array(
                    'name'        => __( 'Address', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Recipient address', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_receiver_address',
					'custom_attributes' => array( 'maxlength' => Wsb_Hub3_Validator::RECEIVER_ADDRESS_MAX, 'data-wsb-counter' => '1' ),
					'default' 	  => '',
					'desc_tip'=> true
				),
				'wsb_hub3_receiver_postcode' => array(
                    'name'        => __( 'Postcode', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Recipient postcode (5 digits)', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_receiver_postcode',
					'custom_attributes' => array( 'maxlength' => 5, 'pattern' => '[0-9]{5}', 'inputmode' => 'numeric', 'data-wsb-mask' => 'digits', 'title' => __( 'Recipient postcode (5 digits)', 'wsb-hub3' ) ),
					'default' 	  => '',
					'placeholder' => '00000',
					'desc_tip'=> true
				),
				'wsb_hub3_receiver_city' => array(
                    'name'        => __( 'City', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Recipient city', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_receiver_city',
					'custom_attributes' => array( 'maxlength' => Wsb_Hub3_Validator::RECEIVER_CITY_MAX, 'data-wsb-counter' => '1' ),
					'default' 	  => '',
					'desc_tip'=> true
                ),
                'wsb_hub3_receiver_iban' => array(
                    'name'        => __( 'IBAN', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Customers pay to the bank accounts of the Direct bank transfer payment method; with more than one account they choose at checkout. This IBAN is used only if Direct bank transfer has no valid account, and for orders placed before the customer could choose.', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_receiver_iban',
					// No maxlength: the browser would cut a pasted IBAN with spaces before the mask removes them.
					'custom_attributes' => array( 'pattern' => '[A-Z]{2}[0-9]{19}', 'data-wsb-mask' => 'iban', 'autocomplete' => 'off', 'spellcheck' => 'false', 'title' => __( 'IBAN is not valid', 'wsb-hub3' ) ),
					'default' 	  => 'HR0000000000000000000',
					'desc_tip'=> true
				),
				'wsb_hub3_receiver_model' => array(
                    'name'        => __( 'Model', 'wsb-hub3' ),
                    'type'        => 'select',
                    'class'       => 'wsb-hub3-admin-tab-field',
                    'desc'        => __( 'Payment model according to FINA rules. HR01 adds a check digit to the reference, HR99 is used without a reference.', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_receiver_model',
                    'options'     => array(
						'00' => __( 'HR00 - without check digit', 'wsb-hub3' ),
						'01' => __( 'HR01 - with check digit', 'wsb-hub3' ),
						'99' => __( 'HR99 - without reference', 'wsb-hub3' ),
					),
					'default' 	  => '00',
					'desc_tip'=> true
				),
				'wsb_hub3_receiver_reference' => array(
                    'name'    => __( 'Reference', 'wsb-hub3' ),
                    'type'    => 'select',
                    'class'   => 'wsb-hub3-admin-tab-field',
                    'desc'    => __( 'Reference format', 'wsb-hub3' ),
                    'id'      => 'wsb_hub3_receiver_reference',
                    'options' => array(
                      'orderid'    => __( 'Order', 'wsb-hub3' ),
					  'date' 	=> __( 'Date', 'wsb-hub3' ),
					  'order-date' 	=> __( 'Order-Date', 'wsb-hub3' ),
					  'date-order' 	=> __( 'Date-Order', 'wsb-hub3' ),
					),
					'default' 	  => 'orderid',
					'desc_tip'    => true,
				),
				'wsb_hub3_receiver_reference_date' => array(
                    'name'    => __( 'Reference date format', 'wsb-hub3' ),
                    'type'    => 'select',
                    'class'   => 'wsb-hub3-admin-tab-field',
                    'desc'    => __( 'd:day, m:month, y:year', 'wsb-hub3' ),
                    'id'      => 'wsb_hub3_receiver_reference_date',
                    'options' => array(
                      'ddmmyyyy'    => __( 'ddmmyyyy', 'wsb-hub3' ),
					  'ddmmyy' 		=> __( 'ddmmyy', 'wsb-hub3' ),
					  'ddmm' 		=> __( 'ddmm', 'wsb-hub3' ),
					  'mmyyyy' 		=> __( 'mmyyyy', 'wsb-hub3' ),
					  'mmyy' 		=> __( 'mmyy', 'wsb-hub3' ),
					  'yyyy' 		=> __( 'yyyy', 'wsb-hub3' ),
					  'yy' 			=> __( 'yy', 'wsb-hub3' ),
					),
					'default' 	  => 'ddmmyyyy',
					'desc_tip'    => true,
				),
				'wsb_hub3_receiver_reference_prefix' => array(
                    'name'        => __( 'Reference prefix', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Numeric value up to 6 digits', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_receiver_reference_prefix',
					'custom_attributes' => array( 'maxlength' => 6, 'pattern' => '[0-9]{1,6}', 'inputmode' => 'numeric', 'data-wsb-mask' => 'digits', 'title' => __( 'Numeric value up to 6 digits', 'wsb-hub3' ) ),
					'default' 	  => '',
					'placeholder' => '000000',
					'desc_tip'=> true
				),
				'wsb_hub3_receiver_reference_sufix' => array(
                    'name'        => __( 'Reference sufix', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Numeric value up to 6 digits', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_receiver_reference_sufix',
					'custom_attributes' => array( 'maxlength' => 6, 'pattern' => '[0-9]{1,6}', 'inputmode' => 'numeric', 'data-wsb-mask' => 'digits', 'title' => __( 'Numeric value up to 6 digits', 'wsb-hub3' ) ),
					'default' 	  => '',
					'placeholder' => '000000',
					'desc_tip'=> true
				),
				'wsb_hub3_reference_preview' => array(
					'title' => __( 'Reference preview', 'wsb-hub3' ),
					'type'  => 'info',
					'text'  => '<span id="wsb-hub3-reference-preview" class="wsb-hub3-reference-preview"></span>',
				),
				'wsb_hub3_payment_purpose' => array(
                    'name'        => __( 'Purpose code', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Payment purpose code. Format: 4 capital letters.', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_payment_purpose',
					'custom_attributes' => array( 'maxlength' => 4, 'pattern' => '[A-Z]{4}', 'data-wsb-mask' => 'upper', 'list' => 'wsb-hub3-purpose-codes', 'autocomplete' => 'off', 'title' => __( 'Payment purpose code. Format: 4 capital letters.', 'wsb-hub3' ) ),
					'default' 	  => '',
					'placeholder' => 'OTHR',
					'desc_tip'=> true
				),
				'wsb_hub3_payment_description' => array(
                    'name'        => __( 'Description', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'You can use placeholder for Order ID: [order]', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_payment_description',
					'custom_attributes' => array( 'maxlength' => 35, 'data-wsb-counter' => '1' ),
					'default' 	  => __( 'Payment for Order [order]', 'wsb-hub3' ),
					'placeholder' => __( 'Order payment', 'wsb-hub3' )
				),
                'section_end' => array(
                     'type' => 'sectionend',
                     'id'   => 'wc_wsb_hub3_admin_tab_receiver_settings_end',
                ),
			);

			return apply_filters( 'wc_wsb_hub3_admin_tab_settings', $settings );
	}

	public function wsb_hub3_output_sections() {
		global $current_section;
		$sections = $this->wsb_hub3_get_sections();
		if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
			return;
		}


		echo '<ul class="subsubsub">';
		$array_keys = array_keys( $sections );
		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=wsb_hub3_admin_tab&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}
		echo '</ul><br class="clear" />';
	}

	/**
	 *	Output the settings
	 */
	public function wsb_hub3_output_settings() {
		$settings = $this->wsb_hub3_get_settings();
		WC_Admin_Settings::output_fields( $settings );
	}

	public function wsb_hub3_get_sections() {
		
		$sections = array(
			'' => __( 'General settings', 'wsb-hub3' ),
			'receiver' => __( 'Recipient', 'wsb-hub3' ),
			'barcode' => __( 'Barcode', 'wsb-hub3' )
			
		);
		return apply_filters( 'woocommerce_get_sections_wsb_hub3_admin_tab', $sections );

	}

	public function wsb_hub3_save() {
		
		global $current_section;
		$settings = $this->wsb_hub3_get_settings();
		if ( $current_section ) {

			if( 'receiver' == $current_section ){
				$this->update_wsb_hub3_receiver_settings();
			} else if ( 'barcode' == $current_section ){
				$this->update_wsb_hub3_barcode_settings();
			} else {
				return false;
			}

		} else {
			$this->update_wsb_hub3_general_settings();
		}
		
	}

	public function update_wsb_hub3_receiver_settings(){

		$receiver_settings = $this->wsb_hub3_receiver_settings();

		// Pasted text may contain decomposed letters (Z + combining caron) or non-breaking spaces.
		foreach ( array( 'wsb_hub3_receiver_name', 'wsb_hub3_receiver_address', 'wsb_hub3_receiver_city' ) as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = (string) $_POST[ $field ];
				if ( class_exists( 'Normalizer' ) ) {
					$value = Normalizer::normalize( $value, Normalizer::FORM_C ) ?: $value;
				}
				$_POST[ $field ] = preg_replace( '/[\s\x{00A0}\x{2000}-\x{200B}\x{202F}\x{FEFF}]+/u', ' ', $value ) ?? $value;
			}
		}
		// Same clean-up as the input masks, for browsers without JavaScript.
		if ( isset( $_POST['wsb_hub3_receiver_iban'] ) ) {
			$_POST['wsb_hub3_receiver_iban'] = strtoupper( preg_replace( '/\s+/', '', (string) $_POST['wsb_hub3_receiver_iban'] ) );
		}
		if ( isset( $_POST['wsb_hub3_payment_purpose'] ) ) {
			$_POST['wsb_hub3_payment_purpose'] = strtoupper( trim( (string) $_POST['wsb_hub3_payment_purpose'] ) );
		}

		if (isset($_POST['wsb_hub3_receiver_name'])) {
			$name = $this->validator->is_valid_receiver_name(sanitize_text_field($_POST['wsb_hub3_receiver_name']));
			if(!$name) {
				unset($receiver_settings['wsb_hub3_receiver_name']);
			}
		}
		if (isset($_POST['wsb_hub3_receiver_address'])) {
			$address = $this->validator->is_valid_receiver_address(sanitize_text_field($_POST['wsb_hub3_receiver_address']));
			if(!$address) {
				unset($receiver_settings['wsb_hub3_receiver_address']);
			}
		}
		if (isset($_POST['wsb_hub3_receiver_city'])) {
			$city = $this->validator->is_valid_city(sanitize_text_field($_POST['wsb_hub3_receiver_city']));
			if(!$city) {
				unset($receiver_settings['wsb_hub3_receiver_city']);
			}
		}
		if (isset($_POST['wsb_hub3_receiver_postcode'])) {
			$postcode = $this->validator->is_valid_postcode(sanitize_text_field($_POST['wsb_hub3_receiver_postcode']));
			if(!$postcode) {
				unset($receiver_settings['wsb_hub3_receiver_postcode']);
			}
		}
		if (isset($_POST['wsb_hub3_receiver_iban'])) {
			$iban = $this->validator->is_valid_iban(sanitize_text_field($_POST['wsb_hub3_receiver_iban']));
			if(!$iban) {	
				unset($receiver_settings['wsb_hub3_receiver_iban']);
			}
		}
		if (isset($_POST['wsb_hub3_receiver_model']) && "" != $_POST['wsb_hub3_receiver_model'] ) {
			$model = $this->validator->is_valid_model(sanitize_text_field($_POST['wsb_hub3_receiver_model']));
			if(!$model) {	
				unset($receiver_settings['wsb_hub3_receiver_model']);
			}
		}
		if ( isset($_POST['wsb_hub3_payment_purpose']) && "" != $_POST['wsb_hub3_payment_purpose'] ) {
			$purpose = $this->validator->is_valid_purpose(sanitize_text_field($_POST['wsb_hub3_payment_purpose']));
			if(!$purpose) {	
				unset($receiver_settings['wsb_hub3_payment_purpose']);
			}
		}
		if ( isset($_POST['wsb_hub3_receiver_reference_prefix']) && "" != $_POST['wsb_hub3_receiver_reference_prefix'] ) {
			$prefix = $this->validator->is_valid_reference_prefix(sanitize_text_field($_POST['wsb_hub3_receiver_reference_prefix']));
			if(!$prefix) {	
				unset($receiver_settings['wsb_hub3_receiver_reference_prefix']);
			}
		}
		if ( isset($_POST['wsb_hub3_receiver_reference_sufix']) && "" != $_POST['wsb_hub3_receiver_reference_sufix'] ) {
			$sufix = $this->validator->is_valid_reference_sufix(sanitize_text_field($_POST['wsb_hub3_receiver_reference_sufix']));
			if(!$sufix) {	
				unset($receiver_settings['wsb_hub3_receiver_reference_sufix']);
			}
		}
		if ( isset($_POST['wsb_hub3_payment_description'])) {
			$description = $this->validator->is_valid_description(sanitize_text_field($_POST['wsb_hub3_payment_description']));
			if(!$description) {	
				unset($receiver_settings['wsb_hub3_payment_description']);
			}
		}
		if (isset($_POST['wsb_hub3_receiver_reference'])) {
			$reference = $this->validator->is_valid_reference(sanitize_text_field($_POST['wsb_hub3_receiver_reference']));
			if(!$reference) {	
				unset($receiver_settings['wsb_hub3_receiver_reference']);
			}
		}
		if (isset($_POST['wsb_hub3_receiver_reference_date'])) {
			$reference_date = $this->validator->is_valid_reference_date(sanitize_text_field($_POST['wsb_hub3_receiver_reference_date']));
			if(!$reference_date) {	
				unset($receiver_settings['wsb_hub3_receiver_reference_date']);
			}
		}

		$posted = function ( $key ) {
			return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		};
		$parts_ok = $this->validator->is_valid_reference_parts(
			$posted( 'wsb_hub3_receiver_model' ),
			$posted( 'wsb_hub3_receiver_reference_prefix' ),
			$posted( 'wsb_hub3_receiver_reference' ),
			$posted( 'wsb_hub3_receiver_reference_sufix' )
		);
		if ( ! $parts_ok ) {
			// Keep the previous reference layout rather than saving one that banks would reject.
			foreach ( array( 'wsb_hub3_receiver_model', 'wsb_hub3_receiver_reference', 'wsb_hub3_receiver_reference_date', 'wsb_hub3_receiver_reference_prefix', 'wsb_hub3_receiver_reference_sufix' ) as $key ) {
				unset( $receiver_settings[ $key ] );
			}
		}

		woocommerce_update_options( $receiver_settings );
	}

	public function update_wsb_hub3_barcode_settings(){
		$barcode_settings = $this->wsb_hub3_barcode_settings();

		if ( isset($_POST['wsb_hub3_img_padding']) && "" != $_POST['wsb_hub3_img_padding'] ) {
			$padding = $this->validator->is_valid_padding(sanitize_text_field($_POST['wsb_hub3_img_padding']));
			if(!$padding) {
				unset($barcode_settings['wsb_hub3_img_padding']);
			}
		}
		if ( isset($_POST['wsb_hub3_img_type'])) {
			$img_type = $this->validator->is_valid_img_type(sanitize_text_field($_POST['wsb_hub3_img_type']));
			if(!$img_type) {
				unset($barcode_settings['wsb_hub3_img_type']);
			}
		}
		if ( isset($_POST['wsb_hub3_img_color']) && "" != $_POST['wsb_hub3_img_color']) {
			$img_color = $this->validator->is_valid_img_color(sanitize_hex_color($_POST['wsb_hub3_img_color']));
			if(!$img_color) {
				unset($barcode_settings['wsb_hub3_img_color']);
			}
		}
		if ( isset($_POST['wsb_hub3_barcode_width']) && "" != $_POST['wsb_hub3_barcode_width']) {
			$img_width = $this->validator->is_valid_img_width(sanitize_text_field($_POST['wsb_hub3_barcode_width']));
			if(!$img_width) {
				unset($barcode_settings['wsb_hub3_barcode_width']);
			}
		}
		if ( isset($_POST['wsb_hub3_barcode_width_email']) && "" != $_POST['wsb_hub3_barcode_width_email']) {
			$img_width = $this->validator->is_valid_img_width(sanitize_text_field($_POST['wsb_hub3_barcode_width_email']));
			if(!$img_width) {
				unset($barcode_settings['wsb_hub3_barcode_width_email']);
			}
		}

		woocommerce_update_options( $barcode_settings );
	}

	public function update_wsb_hub3_general_settings(){
		$general_settings = $this->wsb_hub3_general_settings();

		if (isset($_POST['wsb_hub3_order_status'])) {
			$status = $this->validator->is_valid_status(sanitize_text_field($_POST['wsb_hub3_order_status']));
			if(!$status) {
				unset($general_settings['wsb_hub3_order_status']);
			}
		}
		if (isset($_POST['wsb_hub3_barcode_text']) && "" != $_POST['wsb_hub3_barcode_text']) {
			$barcode_text = $this->validator->is_valid_barcode_text(sanitize_text_field($_POST['wsb_hub3_barcode_text']));
			if(!$barcode_text) {
				unset($general_settings['wsb_hub3_barcode_text']);
			}
		}
		if (isset($_POST['wsb_hub3_description_text']) && "" != $_POST['wsb_hub3_description_text']) {
			$description_text = $this->validator->is_valid_description_text(sanitize_text_field($_POST['wsb_hub3_description_text']));
			if(!$description_text) {
				unset($general_settings['wsb_hub3_description_text']);
			}
		}
		if (isset($_POST['wsb_hub3_display_details_thankyou'])) {
			$display_thankyou = $this->validator->is_valid_display_thankyou(sanitize_text_field($_POST['wsb_hub3_display_details_thankyou']));
			if(!$display_thankyou) {
				unset($general_settings['wsb_hub3_display_details_thankyou']);
			}
		}
		if (isset($_POST['wsb_hub3_display_details_order'])) {
			$display_order = $this->validator->is_valid_display_order(sanitize_text_field($_POST['wsb_hub3_display_details_order']));
			if(!$display_order) {
				unset($general_settings['wsb_hub3_display_details_order']);
			}
		}
		if (isset($_POST['wsb_hub3_display_details_email'])) {
			$display_email = $this->validator->is_valid_display_email(sanitize_text_field($_POST['wsb_hub3_display_details_email']));
			if(!$display_email) {
				unset($general_settings['wsb_hub3_display_details_email']);
			}
		}
		if ( isset($_POST['wsb_hub3_slip_width']) && "" != $_POST['wsb_hub3_slip_width']) {
			$img_width = $this->validator->is_valid_img_width(sanitize_text_field($_POST['wsb_hub3_slip_width']));
			if(!$img_width) {
				unset($general_settings['wsb_hub3_slip_width']);
			}
		}
		if ( isset($_POST['wsb_hub3_slip_width_email']) && "" != $_POST['wsb_hub3_slip_width_email']) {
			$img_width = $this->validator->is_valid_img_width(sanitize_text_field($_POST['wsb_hub3_slip_width_email']));
			if(!$img_width) {
				unset($general_settings['wsb_hub3_slip_width_email']);
			}
		}
		if (isset($_POST['wsb_hub3_send_admin_slip'])) {
			$admin_slip = $this->validator->is_valid_checkbox(sanitize_text_field($_POST['wsb_hub3_send_admin_slip']));
			if(!$admin_slip) {
				unset($general_settings['wsb_hub3_send_admin_slip']);
			}
		}
		if (isset($_POST['wsb_hub3_croatian_customers_only'])) {
			$admin_slip = $this->validator->is_valid_checkbox(sanitize_text_field($_POST['wsb_hub3_croatian_customers_only']));
			if(!$admin_slip) {
				unset($general_settings['wsb_hub3_croatian_customers_only']);
			}
		}
		if (isset($_POST['wsb_hub3_bank_accounts_display'])) {
			$show_accounts = $this->validator->is_valid_checkbox(sanitize_text_field($_POST['wsb_hub3_bank_accounts_display']));
			if(!$show_accounts) {
				unset($general_settings['wsb_hub3_bank_accounts_display']);
			}
		}
		if (isset($_POST['wsb_hub3_send_admin_barcode'])) {
			$admin_barcode = $this->validator->is_valid_checkbox(sanitize_text_field($_POST['wsb_hub3_send_admin_barcode']));
			if(!$admin_barcode) {
				unset($general_settings['wsb_hub3_send_admin_barcode']);
			}
		}
		if (isset($_POST['wsb_hub3_email_embed_images'])) {
			if(!$this->validator->is_valid_checkbox(sanitize_text_field($_POST['wsb_hub3_email_embed_images']))) {
				unset($general_settings['wsb_hub3_email_embed_images']);
			}
		}

		woocommerce_update_options( $general_settings );
	}

	public function wsb_hub3_notice()
	{
		$recipient_name = get_option( 'wsb_hub3_receiver_name' );
		if(!$recipient_name){
			$this->validator->wsb_notices[] = array( 'message' => __( 'Please save your HUB3 recipient settings!', 'wsb-hub3' ), 'type' => 'warning' );
		}
		
		$currency = get_woocommerce_currency();
		if("HRK" != $currency && "EUR" != $currency){
			$this->validator->wsb_notices[] = array( 'message' => __( 'HUB3 plugin works properly only with HRK or EUR as a default currency!', 'wsb-hub3' ), 'type' => 'error' );
		}

		$gateways = WC()->payment_gateways->get_available_payment_gateways();
		$bacs = false;
		foreach ($gateways as $key => $gateway){
			if("bacs" == $key && "yes" == $gateway->enabled){
				$bacs = true;
			}
		}
		if(!$bacs){
			$this->validator->wsb_notices[] = array( 'message' => __( 'HUB3 plugin works only if "Direct bank transfer" payment method is active!', 'wsb-hub3' ), 'type' => 'warning' );
		}
		
		foreach ($this->validator->wsb_notices as $notice) {
			echo '<div class="notice notice-' .esc_html($notice['type']). '"><p>' . esc_html($notice['message']) . '</p></div>';
		}

		// Older versions allowed longer values; the barcode API rejects them.
		if ( $recipient_name && current_user_can( 'manage_woocommerce' ) ) {
			$problems = array();
			$limits   = array(
				'wsb_hub3_receiver_name'    => array( __( 'Recipient', 'wsb-hub3' ), Wsb_Hub3_Validator::RECEIVER_NAME_MAX ),
				'wsb_hub3_receiver_address' => array( __( 'Address', 'wsb-hub3' ), Wsb_Hub3_Validator::RECEIVER_ADDRESS_MAX ),
				'wsb_hub3_receiver_city'    => array( __( 'City', 'wsb-hub3' ), Wsb_Hub3_Validator::RECEIVER_CITY_MAX ),
			);
			$too_long = array();
			foreach ( $limits as $option => $limit ) {
				$length = mb_strlen( (string) get_option( $option ) );
				if ( $length > $limit[1] ) {
					$too_long[] = sprintf( '%s (%d/%d)', $limit[0], $length, $limit[1] );
				}
			}
			if ( $too_long ) {
				/* translators: %s: list of fields with current/maximum length, e.g. "Recipient (27/25)" */
				$problems[] = sprintf( __( 'HUB3 barcodes can not be generated because some recipient data is longer than the HUB-3 standard allows: %s. Please shorten it.', 'wsb-hub3' ), implode( ', ', $too_long ) );
			}

			$saved_model = (string) get_option( 'wsb_hub3_receiver_model' );
			if ( '' !== $saved_model && ! in_array( $saved_model, Wsb_Hub3_Validator::MODELS, true ) ) {
				/* translators: %s: saved payment model number */
				$problems[] = sprintf( __( 'Payment model HR%s is not supported. HR00 is used until you choose HR00, HR01 or HR99.', 'wsb-hub3' ), $saved_model );
			}

			$example = $this->reference_example( $this->saved_reference_settings(), $changes );
			// Non-digit order numbers can't be fixed in these settings, so they only get an order note.
			$changes = array_diff( $changes, array( 'digits' ) );
			if ( $changes ) {
				/* translators: 1: payment reference for the latest order, 2: explanation of the changes */
				$problems[] = sprintf( __( 'With the current settings the HUB3 payment reference for the latest order is adjusted to %1$s. %2$s Please change the reference settings.', 'wsb-hub3' ), $example, implode( ' ', Wsb_Hub3_Validator::reference_change_messages( $changes ) ) );
			}

			$url = admin_url( 'admin.php?page=wc-settings&tab=wsb_hub3_admin_tab&section=receiver' );
			foreach ( $problems as $message ) {
				echo '<div class="notice notice-error"><p>' . esc_html( $message ) . ' <a href="' . esc_url( $url ) . '">' . esc_html__( 'Edit recipient settings', 'wsb-hub3' ) . '</a></p></div>';
			}

			$accounts = Wsb_Hub3_Public::bacs_accounts( $invalid );
			$bacs_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bacs' );
			$bacs_link = ' <a href="' . esc_url( $bacs_url ) . '">' . esc_html__( 'Edit bank accounts', 'wsb-hub3' ) . '</a>';
			// WooCommerce's own (translated) name, as shown in the Payments settings.
			$gateways   = WC()->payment_gateways()->payment_gateways();
			$bacs_title = isset( $gateways['bacs'] ) ? $gateways['bacs']->get_method_title() : 'BACS';
			if ( $invalid ) {
				/* translators: 1: payment method name, 2: names of bank accounts */
				$message = sprintf( __( 'These bank accounts in the %1$s payment method have an invalid IBAN, so customers can\'t choose them for HUB3 payments: %2$s.', 'wsb-hub3' ), $bacs_title, implode( ', ', $invalid ) );
				echo '<div class="notice notice-error"><p>' . esc_html( $message ) . $bacs_link . '</p></div>';
			}
			$settings_iban = (string) get_option( 'wsb_hub3_receiver_iban' );
			// Only relevant while editing HUB3 settings, so it doesn't show on every admin page.
			$on_hub3_tab = isset( $_GET['tab'] ) && 'wsb_hub3_admin_tab' === $_GET['tab'];
			if ( $on_hub3_tab && $accounts && ! in_array( $settings_iban, wp_list_pluck( $accounts, 'iban' ), true ) ) {
				$names = array();
				foreach ( $accounts as $account ) {
					$names[] = $account['account_name'] ?: ( $account['bank_name'] ?: $account['iban'] );
				}
				/* translators: 1: payment method name, 2: names of bank accounts, 3: IBAN from the HUB3 recipient settings */
				$message = sprintf( __( 'Customers pay to the bank accounts set up in the %1$s payment method (%2$s). The IBAN %3$s in the HUB3 recipient settings is not one of them, so it is only used for orders placed before customers could choose an account.', 'wsb-hub3' ), $bacs_title, implode( ', ', $names ), trim( chunk_split( $settings_iban, 4, ' ' ) ) );
				echo '<div class="notice notice-warning"><p>' . esc_html( $message ) . $bacs_link . '</p></div>';
			}
		}
	}

	/**
	 * Moves images from the plugin folder (used before 3.1.0) to uploads, in batches on admin page loads.
	 * @since    3.1.0
	 */
	public function wsb_hub3_migrate_files() {
		if ( 'done' === get_option( 'wsb_hub3_files_migrated' ) || ! function_exists( 'wc_get_order' ) ) {
			return;
		}
		if ( Wsb_Hub3_Files::migrate_legacy() ) {
			update_option( 'wsb_hub3_files_migrated', 'done' );
		}
	}

	private function saved_reference_settings() {
		return array(
			'model'       => Wsb_Hub3_Validator::receiver_model(),
			'prefix'      => (string) get_option( 'wsb_hub3_receiver_reference_prefix' ),
			'format'      => get_option( 'wsb_hub3_receiver_reference', 'orderid' ),
			'date_format' => get_option( 'wsb_hub3_receiver_reference_date', 'ddmmyyyy' ),
			'sufix'       => (string) get_option( 'wsb_hub3_receiver_reference_sufix' ),
		);
	}

	/**
	 * Payment reference the given settings produce for the latest order, dated today.
	 */
	private function reference_example( $settings, &$changes = null, &$order_number = null ) {
		$latest       = wc_get_orders( array( 'limit' => 1, 'orderby' => 'date', 'order' => 'DESC' ) );
		$order_number = $latest ? (string) $latest[0]->get_order_number() : '1';
		return Wsb_Hub3_Validator::build_reference(
			$settings['model'],
			$settings['prefix'],
			$settings['format'],
			Wsb_Hub3_Validator::reference_date( $settings['date_format'], time() ),
			$order_number,
			$settings['sufix'],
			$changes
		);
	}

	/**
	 * AJAX: live preview of the payment reference for unsaved recipient settings.
	 * @since    3.1.0
	 */
	public function wsb_hub3_reference_preview() {
		check_ajax_referer( 'wsb_hub3_reference_preview' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( null, 403 );
		}
		$posted   = function ( $key ) {
			return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		};
		$settings = array(
			'model'       => in_array( $posted( 'model' ), Wsb_Hub3_Validator::MODELS, true ) ? $posted( 'model' ) : '00',
			'prefix'      => substr( preg_replace( '/\D/', '', $posted( 'prefix' ) ), 0, 6 ),
			'format'      => $posted( 'format' ),
			'date_format' => $posted( 'date_format' ),
			'sufix'       => substr( preg_replace( '/\D/', '', $posted( 'sufix' ) ), 0, 6 ),
		);

		$validator = new Wsb_Hub3_Validator();
		$validator->is_valid_reference_parts( $settings['model'], $settings['prefix'], $settings['format'], $settings['sufix'] );
		$problems  = wp_list_pluck( $validator->wsb_notices, 'message' );
		$reference = $this->reference_example( $settings, $changes, $order_number );

		if ( '99' === $settings['model'] ) {
			$note = __( 'HR99 is used without a reference.', 'wsb-hub3' );
		} else {
			/* translators: %s: latest order number */
			$note = sprintf( __( 'Example for the latest order %s, dated today.', 'wsb-hub3' ), $order_number );
		}
		if ( in_array( 'digits', $changes, true ) ) {
			$note .= ' ' . implode( ' ', Wsb_Hub3_Validator::reference_change_messages( array( 'digits' ) ) );
		}
		// The part-count error above already explains any 'parts' change.
		$changes  = array_diff( $changes, $problems ? array( 'digits', 'parts' ) : array( 'digits' ) );
		$problems = array_merge( $problems, Wsb_Hub3_Validator::reference_change_messages( $changes ) );

		wp_send_json_success( array(
			'reference' => trim( 'HR' . $settings['model'] . ' ' . $reference ),
			'length'    => strlen( $reference ),
			'max'       => '99' === $settings['model'] ? 0 : Wsb_Hub3_Validator::REFERENCE_MAX,
			'problems'  => $problems,
			'note'      => $note,
		) );
	}

}