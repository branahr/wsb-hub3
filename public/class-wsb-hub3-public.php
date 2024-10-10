<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.webstudiobrana.com
 * @since      1.0.0
 *
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * @package    Wsb_Hub3
 * @subpackage Wsb_Hub3/public
 * @author     Branko Borilovic <brana.hr@gmail.com>
 */
use Automattic\WooCommerce\Utilities\OrderUtil;
class Wsb_Hub3_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name 
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version   
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

	protected $hpos;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name 
	 * @param      string    $version  
	 */
	public function __construct( $plugin_name, $version ) {
		$this->hpos = OrderUtil::custom_orders_table_usage_is_enabled();
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wsb-hub3-validator.php';
		$this->validator = new Wsb_Hub3_Validator();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		if(is_checkout() || is_account_page()){
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wsb-hub3-public.css', array(), $this->version, 'all' );
		}
		
	}

	/**
	 * Register scripts for frontend.
	 *
	 * @since    1.0.1
	 */
	public function enqueue_scripts() {
		if(is_checkout() || is_account_page()){
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wsb-hub3-public.js', array('jquery'), $this->version, false );
		}
		

	}

	function wsb_hub3_update_barcode_meta( $order_id, $data ) {

		$order = wc_get_order( $order_id );

		if (isset($_POST['_wsb_barcode_iban'])) {
			$iban = esc_attr( $_POST['_wsb_barcode_iban']);
			if($this->validator->is_valid_iban(sanitize_text_field($iban))) {
				$order->update_meta_data( '_wsb_barcode_iban', $iban);
				//$order->save();
				$order->save_meta_data();
			}
		}
		if ($this->hpos) {
			$receiver_iban = $order->get_meta('_wsb_barcode_iban');
		} else {
			$receiver_iban = get_post_meta( $order_id, '_wsb_barcode_iban', true );
		}
		if(!$receiver_iban) $receiver_iban = esc_html(get_option( 'wsb_hub3_receiver_iban' ));

		$order_status = $order->get_status();
		$croatian_only = esc_html(get_option( 'wsb_hub3_croatian_customers_only', 'no' ));
		if( "yes" == $croatian_only ){
			if('HR' != $data['billing_country']){
				return;
			}
		}
		if( 'bacs' != $data['payment_method'] ) {
			return;
		}

		$url = "https://hub3.bigfish.software/api/v2/barcode";

		$img_type = get_option( 'wc_wsb_hub3_admin_tab_img_type', 'png' );
		$img_padding = get_option( 'wsb_hub3_img_padding', '10' );
		$img_color = get_option( 'wsb_hub3_img_color', '#000000' );
		$amount = (float)$order->get_total();
		$barcode_amount = number_format( $amount *= 100, 0, '', '' );
		//$palatali = array("Č", "č", "Ć", "ć", "Ž", "ž", "Š", "š", "Đ", "đ");

        $name_max_chars = 30;
		$street_max_chars = 27;	
		$first_name = $data['billing_first_name'];
		$last_name = $data['billing_last_name'];
		$sender_name = esc_html($first_name . " " . $last_name);
		$sender_street = $data['billing_address_1'];

		if ($this->hpos) {
			$r1_checkbox = $order->get_meta('R1 račun');
		} else {
			$r1_checkbox = get_post_meta( $order_id, 'R1 račun', true );
		}
		if( !empty( $r1_checkbox ) ){
			if ($this->hpos) {
				$sender_name = $order->get_meta('Ime tvrtke');
				if(!$sender_name) $sender_name = get_post_meta( $order_id, 'Ime tvrtke', true );
			} else {
				$sender_name = get_post_meta( $order_id, 'Ime tvrtke', true );
			}
			if ($this->hpos) {
				$sender_street = esc_html($order->get_meta('Adresa tvrtke'));
			} else {
				$sender_street = esc_html(get_post_meta( $order_id, 'Adresa tvrtke', true ));
			}
		} else {
			$company = $order->get_billing_company();
			if(!empty($company)){
				$sender_name = $company;
			} 
		}

		$name_length = strlen($sender_name);
		if($name_length > $name_max_chars){
			$sender_name = substr($sender_name, 0, $name_max_chars);
		}

		$street_length = strlen($sender_street);
		if($street_length > $street_max_chars){
			$sender_street = substr($sender_street, 0, $street_max_chars);
		}

		$place_max_chars = 27;
		$sender_postcode = $data['billing_postcode'];
		$sender_city = $data['billing_city'];
		$sender_place = $sender_postcode . " " . $sender_city;
		$place_length = strlen($sender_place);
		if($place_length > $place_max_chars){
			$sender_place = substr($sender_place, 0, $place_max_chars);
		}

		$receiver_name = get_option( 'wsb_hub3_receiver_name' );
		$receiver_street = get_option( 'wsb_hub3_receiver_address' );
		$receiver_place = get_option( 'wsb_hub3_receiver_postcode' ) . " " . get_option( 'wsb_hub3_receiver_city' );
		$receiver_model = get_option( 'wsb_hub3_receiver_model' );
		$receiver_reference = $this->get_reference($order_id);
		$purpose = get_option( 'wsb_hub3_payment_purpose' );
		$order_number = $order->get_order_number();
		$description = str_replace('[order]', $order_number, get_option( 'wsb_hub3_payment_description' ));

		$hubparams = array();
		$hubparams['renderer'] = 'image';
		$hubparams['options']['format'] = $img_type;
		if(!empty($img_color)){
			$hubparams['options']['color'] = $img_color;
		} else {
			$hubparams['options']['color'] = '#000000';
		}
		
		$hubparams['options']['padding'] = !empty($img_padding) ? (int) $img_padding : 0;
		$hubparams['options']['scale'] = 3;
		$hubparams['options']['ratio'] = 3;
		
		$hubparams['data']['amount'] = (int) $barcode_amount;
		$hubparams['data']['currency'] = get_woocommerce_currency();
		$hubparams['data']['sender']['name'] = $sender_name;
		$hubparams['data']['sender']['street'] = $sender_street;
		$hubparams['data']['sender']['place'] = $sender_place;

		$hubparams['data']['receiver']['name'] = $receiver_name;
		$hubparams['data']['receiver']['street'] = $receiver_street;
		$hubparams['data']['receiver']['place'] = $receiver_place;
		$hubparams['data']['receiver']['iban'] = $receiver_iban;
		$hubparams['data']['receiver']['model'] = $receiver_model;
		$hubparams['data']['receiver']['reference'] = $receiver_reference;
		$hubparams['data']['purpose']= $purpose;
		$hubparams['data']['description']= $description;
		
		$barcode = wp_remote_post( esc_url($url), array(
			'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
			'body'        => json_encode($hubparams),
			'method'      => 'POST',
			'sslverify' => false
		));
		$body = wp_remote_retrieve_body( $barcode );
		$barcode_image = fopen(plugin_dir_path( __DIR__ ) . "barcodes/barcode_" . $order_id . "." . $img_type, "w");
		fwrite($barcode_image, $body);
		fclose($barcode_image);
		$order->update_meta_data( '_wsb_hub3_barcode', 'barcode_' . $order_id . '.' . $img_type);
		$order->update_meta_data( '_wsb_sender_name', sanitize_text_field($sender_name));
		$hub3_image = $this->create_hub3($order_id);
		
		if("" != $hub3_image){
			$order->update_meta_data( '_wsb_hub3_slip', $hub3_image);
		}
		
		//$order->save();
		$order->save_meta_data();
	}

	function wsb_remove_bank_details($accounts, $order_id){
		$show_accounts = get_option( 'wsb_hub3_bank_accounts_display', 'no' );
		if('no' == $show_accounts){
			return array();
		} else {
			return $accounts;
		}
	}

	function wsb_hub3_barcode_thankyou($order_id){
		$order = wc_get_order( $order_id );
		if(!$order) return;
		$payment_method = $order->get_payment_method();
		$order_status = $order->get_status();
		$country = $order->get_billing_country();
		$status_to_display = str_replace("wc-", "", get_option( 'wsb_hub3_order_status', 'on-hold' ));
		$croatian_only = esc_html(get_option( 'wsb_hub3_croatian_customers_only', 'no' ));
		if( "yes" == $croatian_only ){
			if('HR' != $country){
				return;
			}
		}

		if('bacs' != $payment_method || $status_to_display != $order_status) {
			return;
		}
		$display_param = esc_html(get_option( 'wsb_hub3_display_details_thankyou', 'hub3' ));
		if("barcode" != $display_param){ // Hide payment description if set to display barcode only
			echo "<p class='barcode-text'>" . wptexturize(get_option( 'wsb_hub3_description_text' )). "</p>";
		}

		if ($this->hpos) {
			$barcode_image = $order->get_meta('_wsb_hub3_barcode');
		} else {
			$barcode_image = get_post_meta( $order_id, '_wsb_hub3_barcode', true );
		}
		$slip_width = get_option( 'wsb_hub3_slip_width', 800 ) . "px";
		$barcode_width = get_option( 'wsb_hub3_barcode_width', 400 ) . "px";

		if("html" == $display_param){
			echo $this->get_data_html($order_id);
		}
		if("hub3" == $display_param){
			if ($this->hpos) {
				$hub3_image = $order->get_meta('_wsb_hub3_slip');
			} else {
				$hub3_image = get_post_meta( $order_id, '_wsb_hub3_slip', true );
			}
			//var_dump($barcode_image);
			if($hub3_image){
				echo "<div class='slipdiv'><a title='" . __( 'Enlarge (New window)', 'wsb-hub3' ) . "' href='". esc_url(plugin_dir_url( __DIR__ ) . "barcodes/" . $hub3_image ) ."' target='new'><img style='width: " . esc_html($slip_width) . "' src='". esc_url(plugin_dir_url( __DIR__ ) . "barcodes/" . $hub3_image ) ."' alt='HUB-3A' /></a></div>";
			}
			if($barcode_image){
				echo "<p class='barcode-text'><button id='barcode_toggler' class='btn'>" . __( 'Show larger barcode', 'wsb-hub3' ) . "</button></p>";
				echo "<div id='barcodediv' class='barcodediv'>";
				echo "<p class='barcode-text'>" . wptexturize(get_option( 'wsb_hub3_barcode_text' )). "</p>";
				echo "<p class='barcode-text'><img style='width: " . esc_html($barcode_width) . "' src='". esc_url(plugin_dir_url( __DIR__ ) . "barcodes/" . $barcode_image ) ."' alt='barcode' /></p></div>";
			}
		}

		if("html" == $display_param || "barcode" == $display_param){
			if($barcode_image){
				echo "<p class='barcode-text'>" . wptexturize(get_option( 'wsb_hub3_barcode_text' )). "</p>";
				echo "<div class='barcodediv'><img style='width: " . esc_html($barcode_width) . "' src='". esc_url(plugin_dir_url( __DIR__ ) . "barcodes/" . $barcode_image ) ."' alt='barcode' /></div>";
			}
		}
		

	}


	function wsb_hub3_barcode_order_display($order_id){
		$order = wc_get_order( $order_id );
		$payment_method = $order->get_payment_method();
		$order_status = $order->get_status();
		$country = $order->get_billing_country();
		$status_to_display = str_replace("wc-", "", get_option( 'wsb_hub3_order_status', 'on-hold' ));
		$croatian_only = esc_html(get_option( 'wsb_hub3_croatian_customers_only', 'no' ));
		if( "yes" == $croatian_only ){
			if('HR' != $country){
				return;
			}
		}
		if('bacs' != $payment_method || $status_to_display != $order_status) {
			return;
		}

		$display_param = esc_html(get_option( 'wsb_hub3_display_details_order', 'hub3' ));
		if("barcode" != $display_param){ // Hide payment description if set to display barcode only
			echo "<p class='barcode-text'>" . wptexturize(get_option( 'wsb_hub3_description_text' )). "</p>";
		}
		if ($this->hpos) {
			$barcode_image = $order->get_meta('_wsb_hub3_barcode');
		} else {
			$barcode_image = get_post_meta( $order_id, '_wsb_hub3_barcode', true );
		}
		$slip_width = get_option( 'wsb_hub3_slip_width', 800 ) . "px";
		$barcode_width = get_option( 'wsb_hub3_barcode_width', 400 ) . "px";
		
		
		if("html" == $display_param){
			echo $this->get_data_html($order_id);
		}
		if("hub3" == $display_param){
			if ($this->hpos) {
				$hub3_image = $order->get_meta('_wsb_hub3_slip');
			} else {
				$hub3_image = get_post_meta( $order_id, '_wsb_hub3_slip', true );
			}
			if($hub3_image){
				echo "<div class='slipdiv'><a title='" . __( 'Enlarge (New window)', 'wsb-hub3' ) . "' href='". esc_url(plugin_dir_url( __DIR__ ) . "barcodes/" . $hub3_image ) ."' target='new'><img style='width: " . esc_html($slip_width) . "' src='". esc_url(plugin_dir_url( __DIR__ ) . "barcodes/" . $hub3_image ) ."' alt='HUB-3A' /></a></div>";
			}
			if($barcode_image){
				echo "<p class='barcode-text'><button id='barcode_toggler' class='btn'><span class='barcode_btn_text'>" . __( 'Show larger barcode', 'wsb-hub3' ) . "</span></button></p>";
				echo "<div id='barcodediv' class='barcodediv'>";
				echo "<p class='barcode-text'>" . wptexturize(get_option( 'wsb_hub3_barcode_text' )). "</p>";
				echo "<img style='width: " . esc_html($barcode_width) . "' src='". esc_url(plugin_dir_url( __DIR__ ) . "barcodes/" . $barcode_image ) ."' alt='barcode' /></div>";
			}
		}

		if("html" == $display_param || "barcode" == $display_param){
			if($barcode_image){
				echo "<p class='barcode-text'>" . wptexturize(get_option( 'wsb_hub3_barcode_text' )). "</p>";
				echo "<div class='barcodediv'><img style='width: " . esc_html($barcode_width) . "' src='". esc_url(plugin_dir_url( __DIR__ ) . "barcodes/" . esc_html($barcode_image )) ."' alt='barcode' /></div>";
			}
		}

	}

	function wsb_hub3_email_after_order_table( $order, $sent_to_admin, $plain_text, $email ) 
	{
		$croatian_only = esc_html(get_option( 'wsb_hub3_croatian_customers_only', 'no' ));
		if(!$sent_to_admin){
			$status_to_display = str_replace("wc-", "", get_option( 'wsb_hub3_order_status', 'on-hold' ));
			if ( $order->has_status( $status_to_display ) ){
				$order_id = $order->get_id();
				$payment_method = $order->get_payment_method();
				$country = $order->get_billing_country();
				if( ('yes' == $croatian_only && 'HR' == $country && 'bacs' == $payment_method ) || ('no' == $croatian_only && 'bacs' == $payment_method ) ){
					$img_version = date('His'); //added versioning for images to avoid sending of cached images via email on order update
	
					$display_param = esc_html(get_option( 'wsb_hub3_display_details_email', 'hub3' ));
	
					if("barcode" != $display_param){ // Hide payment description if set to display barcode only
						echo "<p style='text-align:center;'>" . wptexturize(get_option( 'wsb_hub3_description_text' )). "</p>";
					}
					if ($this->hpos) {
						$barcode_image = $order->get_meta('_wsb_hub3_barcode');
					} else {
						$barcode_image = get_post_meta( $order_id, '_wsb_hub3_barcode', true );
					}
					$slip_width = get_option( 'wsb_hub3_slip_width_email', 560 );
					$barcode_width = get_option( 'wsb_hub3_barcode_width_email', 400 );
	
					
					if("html" == $display_param){
						echo $this->get_data_html($order_id);
					}
					if("hub3" == $display_param){
						if ($this->hpos) {
							$hub3_image = $order->get_meta('_wsb_hub3_slip');
						} else {
							$hub3_image = get_post_meta( $order_id, '_wsb_hub3_slip', true );
						}
						if($hub3_image){
							echo "<div style='text-align:center;'><img width='". esc_html($slip_width) . "' style='margin: 0 auto; width: " . esc_html($slip_width . "px") . "' src='". esc_url(plugin_dir_url( __DIR__ ) . "barcodes/" . $hub3_image . "?ver=" . $img_version ) ."' alt='HUB-3A' /></div>";
						}
					}
			
					if($barcode_image){
						echo "<p style='text-align:center;'>" . wptexturize(get_option( 'wsb_hub3_barcode_text' )). "</p>";
						echo "<div style='text-align:center;'><img width='". esc_html($barcode_width) . "' style='margin: 0 auto; width: " . esc_html($barcode_width  . "px" ) . "' src='". esc_url(plugin_dir_url( __DIR__ ) . "barcodes/" . $barcode_image . "?ver=" . $img_version ) ."' alt='barcode' /></div>";
					}	
				}

			}
		} else {
			$status_to_display = str_replace("wc-", "", get_option( 'wsb_hub3_order_status', 'on-hold' ));
			if ( $order->has_status( $status_to_display ) ){
				$order_id = $order->get_id();
				$payment_method = $order->get_payment_method();
				$country = $order->get_billing_country();
				
				if( ('yes' == $croatian_only && 'HR' == $country && 'bacs' == $payment_method ) || ('no' == $croatian_only && 'bacs' == $payment_method ) ) {
	
					$img_version = date('His'); //added versioning for images to avoid sending of cached images via email on order update
					if ($this->hpos) {
						$barcode_image = $order->get_meta('_wsb_hub3_barcode');
					} else {
						$barcode_image = get_post_meta( $order_id, '_wsb_hub3_barcode', true );
					}
					$slip_width = get_option( 'wsb_hub3_slip_width_email', 560 );
					$barcode_width = get_option( 'wsb_hub3_barcode_width_email', 400 );
					$send_slip = esc_html(get_option( 'wsb_hub3_send_admin_slip', 'no' ));
					if($send_slip == "yes"){
						if ($this->hpos) {
							$hub3_image = $order->get_meta('_wsb_hub3_slip');
						} else {
							$hub3_image = get_post_meta( $order_id, '_wsb_hub3_slip', true );
						}
						if($hub3_image){
							echo "<div style='text-align:center;'><img width='". esc_html($slip_width) . "' style='margin: 0 auto; width: " . esc_html($slip_width . "px") . "' src='". esc_url(plugin_dir_url( __DIR__ ) . "barcodes/" . $hub3_image . "?ver=" . $img_version ) ."' alt='HUB-3A' /></div>";
						}
					}
			
					$send_barcode = esc_html(get_option( 'wsb_hub3_send_admin_barcode', 'no' ));
					if($send_barcode == "yes"){
						if($barcode_image){
							echo "<div style='text-align:center;'><img width='". esc_html($barcode_width) . "' style='margin: 0 auto; width: " . esc_html($barcode_width  . "px" ) . "' src='". esc_url(plugin_dir_url( __DIR__ ) . "barcodes/" . $barcode_image . "?ver=" . $img_version ) ."' alt='barcode' /></div>";
						}
					}
					
				}
			}
		}
		
	}

	function get_reference($order_id){
		$date = "";
		$order = wc_get_order( $order_id );
		$date_created = strtotime($order->get_date_created());
		$reference_date_format = get_option( 'wsb_hub3_receiver_reference_date', 'ddmmyyyy' );
		switch ($reference_date_format) {
			case 'ddmmyyyy':
				$date = date("dmY", $date_created);
				break;
			case 'ddmmyy':
				$date = date("dmy", $date_created);
				break;
			case 'ddmm':
				$date = date("dm", $date_created);
				break;
			case 'mmyyyy':
				$date = date("mY", $date_created);
				break;
			case 'mmyy':
				$date = date("my", $date_created);
				break;
			case 'yyyy':
				$date = date("Y", $date_created);
				break;
			case 'yy':
				$date = date("y", $date_created);
				break;
			
			default:
				$date = "";
				break;
		}
		
		$order_number = $order->get_order_number();
		$reference = $order_number;

		$reference_format = get_option( 'wsb_hub3_receiver_reference', 'orderid' );
		switch ($reference_format) {
			case 'orderid':
				$reference = $order_number;
				break;
			case 'date':
				$reference = $date;
				break;
			case 'order-date':
				$reference = $order_number . "-" . $date;
				break;
			case 'date-order':
				$reference =  $date. "-" . $order_number;
				break;
		}
		$reference_prefix = !empty(get_option( 'wsb_hub3_receiver_reference_prefix' )) ? get_option( 'wsb_hub3_receiver_reference_prefix' ) . "-" : "";
		$reference_sufix = !empty(get_option( 'wsb_hub3_receiver_reference_sufix' )) ? "-" . get_option( 'wsb_hub3_receiver_reference_sufix' ) : "";
		return esc_html($receiver_reference = $reference_prefix . $reference . $reference_sufix);
	}

	function get_data_html($order_id){
		$order = wc_get_order( $order_id );
		$order_number = $order->get_order_number();
		$reference = $this->get_reference($order_id);
		$description = str_replace('[order]', $order_number, get_option( 'wsb_hub3_payment_description', 'Plaćanje narudžbe br. [order]' ));
		
		$total = $order->get_formatted_order_total();
		if ($this->hpos) {
			$sender_name = $order->get_meta('_wsb_sender_name');
		} else {
			$sender_name = get_post_meta( $order_id, '_wsb_sender_name', true );
		}

		$html = "";
		$html .= "<h2 class='hub3-title'>" . __( 'Payment details', 'wsb-hub3' ) . "</h2>";
		$html .= "<table class='woocommerce-table hub3-table'><tbody>";
		$html .= "<tr><td>" . __( 'Recipient', 'wsb-hub3' ) . ": </td><td>" .  esc_html(get_option( 'wsb_hub3_receiver_name' )) . "<br>" . esc_html(get_option( 'wsb_hub3_receiver_address' )) . "<br>" . esc_html(get_option( 'wsb_hub3_receiver_postcode' )) . " " . esc_html(get_option( 'wsb_hub3_receiver_city' )) . "</td></tr>";
		$html .= "<tr><td>" . __( 'Amount', 'wsb-hub3' ) . ": </td><td>" . $total . "</td></tr>";
		$html .= "<tr><td>" . __( 'IBAN', 'wsb-hub3' ) . ": </td><td>" . esc_html(get_option( 'wsb_hub3_receiver_iban' )) . "</td></tr>";
		if(!empty(get_option( 'wsb_hub3_receiver_model' ))){
			$html .= "<tr><td>" . __( 'Model', 'wsb-hub3' ) . ": </td><td>HR" . esc_html(get_option( 'wsb_hub3_receiver_model' )) . "</td></tr>";
		}
		$html .= "<tr><td>" . __( 'Reference', 'wsb-hub3' ) . ": </td><td>" . esc_html($reference) . "</td></tr>";
		if(!empty(get_option( 'wsb_hub3_payment_purpose' ))){
			$html .= "<tr><td>" . __( 'Purpose code', 'wsb-hub3' ) . ": </td><td>" . esc_html(get_option( 'wsb_hub3_payment_purpose' )) . "</td></tr>";
		}
		$html .= "<tr><td>" . __( 'Description', 'wsb-hub3' ) . ": </td><td>" . esc_html($description) . "</td></tr>";
		$html .= "</tbody></table>";

		return $html;
	}

	 function create_hub3($order_id){
		$order = wc_get_order( $order_id );
		$order_number = $order->get_order_number();
		$recipient = get_option( 'wsb_hub3_receiver_name' );
		$recipient_address = esc_html(get_option( 'wsb_hub3_receiver_address' ));
		$recipient_place = esc_html(get_option( 'wsb_hub3_receiver_postcode' ) . " " . get_option( 'wsb_hub3_receiver_city' ));
		if ($this->hpos) {
			$iban = $order->get_meta('_wsb_barcode_iban');
		} else {
			$iban = get_post_meta( $order_id, '_wsb_barcode_iban', true );
		}
		if(!$iban) $iban = esc_html(get_option( 'wsb_hub3_receiver_iban' ));
		$reference = $reference = $this->get_reference($order_id);
		$description = esc_html(str_replace('[order]', $order_number, get_option( 'wsb_hub3_payment_description' )));
		$model = "HR00";
		if(!empty(get_option( 'wsb_hub3_receiver_model' )) && "" != get_option( 'wsb_hub3_receiver_model' )){
			$model = esc_html("HR" . get_option( 'wsb_hub3_receiver_model' ));
		}
		
		if ($this->hpos) {
			$sender = $order->get_meta('_wsb_sender_name');
		} else {
			$sender = get_post_meta( $order_id, '_wsb_sender_name', true );
		}
		$sender_address = $order->get_billing_address_1(); 
		$sender_address2 = $order->get_billing_address_2();
		$sender_postcode = $order->get_billing_postcode(); 
		$sender_city = $order->get_billing_city();
		$currency = get_woocommerce_currency();
		$total = esc_html( "=" . number_format($order->get_total(),2,"",""));
		$total2 = esc_html( $currency. " = " . number_format($order->get_total(),2,",",""));

		$hub3a = imagecreatefromjpeg(plugin_dir_path(__DIR__) . 'public/img/hub-3a.jpg');
		$black = imagecolorallocate($hub3a, 0x30, 0x30, 0x30);
		$font_roboto = plugin_dir_path( __DIR__ ) . 'public/fonts/RobotoMono-Regular.ttf';
		$font_times = plugin_dir_path( __DIR__ ) . 'public/fonts/times-new-roman.ttf';
		
		$this->imagettftextWsb($hub3a, 18, 0, 402, 54, $black, $font_roboto, $currency, 3);

		$bbox_total = imagettfbbox(18, 0, $font_roboto, $total);
		$x_total = 768 - ( $bbox_total[4] + (strlen($total))*3 );

		$this->imagettftextWsb($hub3a, 18, 0, $x_total, 55, $black, $font_roboto, $total, 3);
		$this->imagettftextWsb($hub3a, 18, 0, 401, 160, $black, $font_roboto, $iban, 3);
		$this->imagettftextWsb($hub3a, 18, 0, 278, 202, $black, $font_roboto, $model, 3);
		$this->imagettftextWsb($hub3a, 18, 0, 384, 202, $black, $font_roboto, $reference, 3);
		if(!empty(get_option( 'wsb_hub3_payment_purpose' ))){
			$purpose = esc_html( get_option( 'wsb_hub3_payment_purpose' ));
			$this->imagettftextWsb($hub3a, 18, 0, 277, 250, $black, $font_roboto, $purpose, 3);
		}
		$this->imagettftextWsb($hub3a, 12, 0, 438, 227, $black, $font_times, $description);
		$this->imagettftextWsb($hub3a, 12, 0, 805, 240, $black, $font_times, $description);

		$bbox_total2 = imagettfbbox(12, 0, $font_times, $total2);
		$x_total2 = 1080 - $bbox_total2[4];
		$this->imagettftextWsb($hub3a, 12, 0, $x_total2, 54, $black, $font_times, $total2);

		$this->imagettftextWsb($hub3a, 14, 0, 35, 60, $black, $font_times, $sender);
		$this->imagettftextWsb($hub3a, 14, 0, 35, 80, $black, $font_times, $sender_address);
		if( "" == $sender_address2 ){
			$this->imagettftextWsb($hub3a, 14, 0, 35, 100, $black, $font_times, $sender_postcode . " " . $sender_city );	
		} else {
			$this->imagettftextWsb($hub3a, 14, 0, 35, 100, $black, $font_times, $sender_address2);
			$this->imagettftextWsb($hub3a, 14, 0, 35, 120, $black, $font_times, $sender_postcode . " " . $sender_city );
		}

		$this->imagettftextWsb($hub3a, 14, 0, 35, 200, $black, $font_times, $recipient);
		$this->imagettftextWsb($hub3a, 14, 0, 35, 220, $black, $font_times, $recipient_address);
		$this->imagettftextWsb($hub3a, 14, 0, 35, 240, $black, $font_times, $recipient_place);

		$bbox_sender2 = imagettfbbox(12, 0, $font_times, $sender . ", " . $sender_city);
		$x_sender2 = 1080 - $bbox_sender2[4];
		$this->imagettftextWsb($hub3a, 12, 0, $x_sender2, 86, $black, $font_times, $sender . ", " . $sender_city);

		$reference2 = $model . " " . $reference;
		$bbox_reference2 = imagettfbbox(12, 0, $font_times, $reference2);
		$x_reference2 = 1080 - $bbox_reference2[4];
		$this->imagettftextWsb($hub3a, 12, 0, $x_reference2, 201, $black, $font_times, $reference2);

		$bbox_iban2 = imagettfbbox(12, 0, $font_times, $iban);
		$x_iban2 = 1080 - $bbox_iban2[4];
		$this->imagettftextWsb($hub3a, 12, 0, $x_iban2, 162, $black, $font_times, $iban);

		$img_path = rtrim(dirname(__DIR__), '/') . '/barcodes/';
		
		if ($this->hpos) {
			$img_file = $order->get_meta('_wsb_hub3_barcode');
		} else {
			$img_file = get_post_meta( $order_id, '_wsb_hub3_barcode', true );
		}
		$barcode_big = $img_path . $img_file;
		$img_type = GetImageSize($barcode_big);
		if($img_type[2] == 1){ //gif
			$barcode_resized = $this->resize_barcode_image(imagecreatefromgif(esc_html($barcode_big)));
		} 
        if($img_type[2] == 2){ //jpg
			$barcode_resized = $this->resize_barcode_image(imagecreatefromjpeg(esc_html($barcode_big)));
		} 
        if($img_type[2] == 3){ //png
			$barcode_resized = $this->resize_barcode_image(imagecreatefrompng(esc_html($barcode_big)));
		}

		if($barcode_resized && $barcode_big){
			imagecopy($hub3a, $barcode_resized, 31, 300, 0, 0, imagesx($barcode_resized), imagesy($barcode_resized));
		}
		
		$hub3_image = "hub-3a-".$order_id.".jpg";
		if(!imagejpeg($hub3a, plugin_dir_path( __DIR__ ) . "barcodes/".$hub3_image, 100)){
			$hub3_image = "";
		}
		imagedestroy($hub3a);
		if($barcode_resized){
			imagedestroy($barcode_resized);
		}
			return $hub3_image;
	}

	private function imagettftextWsb($image, $size, $angle, $x, $y, $color, $font, $text, $spacing = 0)
	{        
		if ($spacing == 0)
		{
			imagettftext($image, $size, $angle, $x, $y, $color, $font, $text);
		}
		else
		{
			$temp_x = $x;
			for ($i = 0; $i < strlen($text); $i++)
			{
				$bbox = imagettftext($image, $size, $angle, (int) $temp_x, (int) $y, $color, $font, $text[$i]);
				$temp_x += $spacing + 14.7;
			}
		}
	}

	private function resize_barcode_image($image) {
		if($new_image = imagescale($image, 305, -1,  IMG_BICUBIC_FIXED)){
			return $new_image;
		} else {
			return false;
		}
	}

	/**
	 * Re-creates a barcode and HUB3 on admin order update.
	 *
	 * @since    1.0.3
	 */
	public function wsb_hub3_admin_order_update($order_id, $order){
		
		//$order = wc_get_order( $order_id );
		
		//exit;
		//remove_action( 'woocommerce_update_order', __FUNCTION__, 25, 2 );
		$data = array();
		$data['payment_method'] = $order->get_payment_method();
		if('bacs' != $data['payment_method']) {
			return;
		}
		$data['billing_country'] = $order->get_billing_country();
		$data['billing_first_name'] = $order->get_billing_first_name();
		$data['billing_last_name'] = $order->get_billing_last_name();
		$data['billing_address_1'] = $order->get_billing_address_1();
		$data['billing_postcode'] = $order->get_billing_postcode();
		$data['billing_city'] = $order->get_billing_city();
		//var_dump($data);
		$this->wsb_hub3_update_barcode_meta($order_id, $data);
		$this->create_hub3($order_id);
	}

	/**
	 * Checkout custom fields.
	 *
	 * @since    2.0
	 */

	function wsb_hub3_gateway_description( $description, $gateway_id ) {
    if ( 'bacs' === $gateway_id ) {
        $payment_method = WC()->payment_gateways->payment_gateways()[ 'bacs' ];
        $accounts = array();

        foreach ( $payment_method->account_details as $bank_account ) {
            if ( empty( $bank_account['iban'] ) ) continue;
            $accounts[ $bank_account['iban'] ] = $bank_account['account_name'];
        }

        // If we have IBANs from BACS to show
        if ( ! empty( $accounts ) ) {
            if ( count( $accounts ) == 1 ) {
                // Single IBAN - hidden field
                $account = key( $accounts );
                $hidden_field = woocommerce_form_field( '_wsb_barcode_iban', array(
                    'type'     => 'hidden',
                    'class'    => array( 'barcode-iban-class hidden-field' ),
                    'required' => true,
					'default'  => $account,
                ), $account );

                return $description . $hidden_field;

            } else {
                // Multiple IBANs - show as select dropdown
                $banks_dropdown = woocommerce_form_field( '_wsb_barcode_iban', array(
                    'type'     => 'select',
                    'class'    => array( 'barcode-iban-class form-row-wide' ),
                    'label'    => __( 'Account to pay', 'wsb-hub3' ),
                    'required' => true,
					'return' => true,
                    'options'  => $accounts,
                ), '' );

                return $description . $banks_dropdown;
            }
        }

        return $description;
    }

    return $description;
}

}