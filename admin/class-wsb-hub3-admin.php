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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wsb-hub3-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.2
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wsb-hub3-admin.js', array( 'wp-color-picker' ), false, true);

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
					'default' 	  => '',
					'desc_tip'=> true
				),
				'wsb_hub3_barcode_text' => array(
                    'name'        => __( 'Text above barcode', 'wsb-hub3' ),
                    'type'        => 'textarea',
                    'desc'        => __( 'Text to be shown above barcode', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_barcode_text',
					'default' 	  => '',
					'desc_tip'=> true
				),
				'wsb_hub3_display_details_thankyou' => array(
                    'name'    => __( 'Show on thankyou page', 'wsb-hub3' ),
                    'type'    => 'select',
                    'class'   => 'wsb-hub3-admin-tab-field',
                    'desc'    => __( '', 'wsb-hub3' ),
                    'id'      => 'wsb_hub3_display_details_thankyou',
                    'options' => array(
                      'html'    => __( 'Html text + large barcode', 'wsb-hub3' ),
					  'hub3' 	=> __( 'HUB-3A slip + large barcode', 'wsb-hub3' ),
					  'barcode' => __( 'Large barcode only', 'wsb-hub3' ),
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
				'wsb_hub3_slip_width' => array(
                    'name'        => __( 'HUB-3A slip width', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'HUB-3A slip width in pixels', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_slip_width',
					'default' 	  => '1100',
					'placeholder' => '1100',
					'desc_tip'=> true
				),
				'wsb_hub3_slip_width_email' => array(
                    'name'        => __( 'HUB-3A slip width in email', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Width in pixels of HUB-3A slip sent in email ', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_slip_width_email',
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
					'default' 	  => '400',
					'placeholder' => '400',
					'desc_tip'=> true
				),
				'wsb_hub3_barcode_width_email' => array(
                    'name'        => __( 'Width (email)', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Width in pixels of barcode sent in email', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_barcode_width_email',
					'default' 	  => '400',
					'placeholder' => '400',
					'desc_tip'=> true
				),
				'wsb_hub3_img_padding' => array(
                    'name'        => __( 'Padding', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Barcode padding in pixels', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_img_padding',
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
					'default' 	  => '',
					'desc_tip'=> true
				),
				'wsb_hub3_receiver_address' => array(
                    'name'        => __( 'Address', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Recipient address', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_receiver_address',
					'default' 	  => '',
					'desc_tip'=> true
				),
				'wsb_hub3_receiver_postcode' => array(
                    'name'        => __( 'Postcode', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Recipient postcode (5 digits)', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_receiver_postcode',
					'default' 	  => '',
					'placeholder' => '00000',
					'desc_tip'=> true
				),
				'wsb_hub3_receiver_city' => array(
                    'name'        => __( 'City', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Recipient city', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_receiver_city',
					'default' 	  => '',
					'desc_tip'=> true
                ),
                'wsb_hub3_receiver_iban' => array(
                    'name'        => __( 'IBAN', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'IBAN', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_receiver_iban',
					'default' 	  => 'HR0000000000000000000',
					'desc_tip'=> true
				),
				'wsb_hub3_receiver_model' => array(
                    'name'        => __( 'Model', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Payment model (2 digits)', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_receiver_model',
					'default' 	  => '',
					'placeholder' => '00',
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
					'default' 	  => '',
					'placeholder' => '000000',
					'desc_tip'=> true
				),
				'wsb_hub3_receiver_reference_sufix' => array(
                    'name'        => __( 'Reference sufix', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Numeric value up to 6 digits', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_receiver_reference_sufix',
					'default' 	  => '',
					'placeholder' => '000000',
					'desc_tip'=> true
				),
				'wsb_hub3_payment_purpose' => array(
                    'name'        => __( 'Purpose code', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'Payment purpose code. Format: 4 capital letters.', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_payment_purpose',
					'default' 	  => '',
					'placeholder' => 'OTHR',
					'desc_tip'=> true
				),
				'wsb_hub3_payment_description' => array(
                    'name'        => __( 'Description', 'wsb-hub3' ),
                    'type'        => 'text',
                    'desc'        => __( 'You can use placeholder for Order ID: [order]', 'wsb-hub3' ),
                    'id'          => 'wsb_hub3_payment_description',
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
			if(!$reference) {	
				unset($receiver_settings['wsb_hub3_receiver_reference_date']);
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
	}

}