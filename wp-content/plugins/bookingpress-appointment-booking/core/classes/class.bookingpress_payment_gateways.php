<?php
if (! class_exists('bookingpress_payment_gateways') ) {
    class bookingpress_payment_gateways Extends BookingPress_Core
    {
        function __construct()
        {
            add_filter('bookingpress_validate_submitted_form', array( $this, 'bookingpress_validate_submitted_form_func' ), 10, 2);
            add_action('wp', array( $this, 'bookingpress_paypal_payment_data' ));
        }
        
        /**
         * PayPal Webhook Data verification and Confirm payment
         *
         * @return void
         */
        function bookingpress_paypal_payment_data()
        {
            // phpcs:ignore WordPress.Security.NonceVerification
            global $wpdb, $BookingPress, $bookingpress_debug_payment_log_id;
            if (! empty($_REQUEST['bookingpress-listener']) && ( $_REQUEST['bookingpress-listener'] == 'bpa_paypal_url' ) ) {
                $bookingpress_webhook_data = $_REQUEST;
                do_action('bookingpress_payment_log_entry', 'paypal', 'Paypal Webhook Data', 'bookingpress', $bookingpress_webhook_data, $bookingpress_debug_payment_log_id);
                if (! empty($bookingpress_webhook_data) && !empty($_POST['txn_id']) && ! empty($bookingpress_webhook_data['custom']) ) { // phpcs:ignore WordPress.Security.NonceVerification
                    $req = 'cmd=_notify-validate';
                    foreach ($_POST as $key => $value) { // phpcs:ignore WordPress.Security.NonceVerification
                        $value = urlencode(stripslashes($value));
                        $req .= "&$key=$value";
                    }

                    $request = new WP_Http();
                    /* For HTTP1.0 Request */
                    $requestArr = array(
                        "sslverify" => false,
                        "ssl" => true,
                        "body" => $req,
                        "timeout" => 20,
                    );
                    /* For HTTP1.1 Request */
                    $requestArr_1_1 = array(
                        "httpversion" => '1.1',
                        "sslverify" => false,
                        "ssl" => true,
                        "body" => $req,
                        "timeout" => 20,
                    );
                    $response = array();

                    $bookingpress_payment_mode    = $BookingPress->bookingpress_get_settings('paypal_payment_mode', 'payment_setting');
                    $bookingpress_is_sandbox_mode = ( $bookingpress_payment_mode != 'live' ) ? true : false;

                    if($bookingpress_is_sandbox_mode){
                        $url = "https://www.sandbox.paypal.com/cgi-bin/webscr/";
                        $response_1_1 = $request->post($url, $requestArr_1_1);

                        if (!is_wp_error($response_1_1) && $response_1_1['body'] == 'VERIFIED') {
                            $response = $response_1_1;
                        } else {
                            $response = $request->post($url, $requestArr);
                        }  
                    }else{
                        $url = "https://www.paypal.com/cgi-bin/webscr/";
                        $response_1_0 = $request->post($url, $requestArr);
                        if (!is_wp_error($response_1_0) && $response_1_0['body'] == 'VERIFIED') {
                            $response = $response_1_0;
                        } else {
                            $response = $request->post($url, $requestArr_1_1);
                        }
                    }

                    do_action('bookingpress_payment_log_entry', 'paypal', 'Paypal Webhook Verified Data', 'bookingpress', $response, $bookingpress_debug_payment_log_id);

                    if (!is_wp_error($response) && $response['body'] == 'VERIFIED' && !empty($_POST['txn_type']) && ($_POST['txn_type'] == 'web_accept') ) { // phpcs:ignore WordPress.Security.NonceVerification
                        $entry_id       = intval($bookingpress_webhook_data['custom']);
                        $payment_status = ! empty($bookingpress_webhook_data['payment_status']) ? sanitize_text_field($bookingpress_webhook_data['payment_status']) : '1';
                        if($payment_status == 'Completed'){
                            $payment_status = '1';
                        }else if($payment_status == 'Pending'){
                            $payment_status = '2';
                        }else{
                            $payment_status = '1';
                        }
                        $payer_email    = ! empty($bookingpress_webhook_data['payer_email']) ? sanitize_email($bookingpress_webhook_data['payer_email']) : '';
                        $bookingpress_webhook_data['bookingpress_payer_email'] = $payer_email;
                        $bookingpress_webhook_data                             = array_map(array( $BookingPress, 'appointment_sanatize_field' ), $bookingpress_webhook_data);
                        $payment_log_id                                        = $this->bookingpress_confirm_booking($entry_id, $bookingpress_webhook_data, $payment_status, 'txn_id', '', 1);
                    }
                }
            }
        }
        
        /**
         * Core common function for generate request data which pass to all payment gateways
         *
         * @param  mixed $payment_gateway
         * @param  mixed $posted_data
         * @return void
         */
        public function bookingpress_validate_submitted_form_func( $payment_gateway, $posted_data )
        {
            // phpcs:ignore WordPress.Security.NonceVerification
            global $BookingPress, $wpdb, $tbl_bookingpress_entries,$bookingpress_debug_payment_log_id, $bookingpress_global_options;
            $return_data = array(
            'service_data'     => array(),
            'payable_amount'   => 0,
            'customer_details' => array(),
            'currency'         => '',
            );

            $bookingpress_appointment_data = $posted_data;
            $return_data                   = apply_filters('bookingpress_before_modify_validate_submit_form_data', $return_data);

            if (! empty($posted_data) && ! empty($payment_gateway) ) {
                $bookingpress_selected_service_id     = sanitize_text_field($bookingpress_appointment_data['selected_service']);
                $bookingpress_appointment_booked_date = sanitize_text_field($bookingpress_appointment_data['selected_date']);
                $bookingpress_selected_start_time     = sanitize_text_field($bookingpress_appointment_data['selected_start_time']);
                $bookingpress_selected_end_time       = sanitize_text_field($bookingpress_appointment_data['selected_end_time']);
                $bookingpress_internal_note           = ! empty($bookingpress_appointment_data['appointment_note']) ? sanitize_textarea_field($bookingpress_appointment_data['appointment_note']) : '';
                $service_data                         = $BookingPress->get_service_by_id($bookingpress_selected_service_id);
                $service_duration_vals                = $BookingPress->bookingpress_get_service_end_time($bookingpress_selected_service_id, $bookingpress_selected_start_time);
                $service_data['service_start_time']   = sanitize_text_field($service_duration_vals['service_start_time']);
                $service_data['service_end_time']     = sanitize_text_field($service_duration_vals['service_end_time']);
                $return_data['service_data']          = $service_data;

                $bookingpress_currency_name = $BookingPress->bookingpress_get_settings('payment_default_currency', 'payment_setting');
                $return_data['currency']    = $bookingpress_currency_name;

                $bookingpress_decimal_points = $BookingPress->bookingpress_get_settings('price_number_of_decimals', 'payment_setting');
                $__payable_amount            = $service_data['bookingpress_service_price'];
                if ($bookingpress_decimal_points == '0' ) {
                    $__payable_amount = round($__payable_amount);
                }
                $return_data['payable_amount'] = (float) $__payable_amount;

                if ($return_data['payable_amount'] == 0 ) {
                    $payment_gateway = ' - ';
                }

                $customer_email     = $bookingpress_appointment_data['customer_email'];
                $customer_username  = ! empty($bookingpress_appointment_data['customer_name']) ? sanitize_text_field($bookingpress_appointment_data['customer_name']) : '';
                $customer_firstname = ! empty($bookingpress_appointment_data['customer_firstname']) ? sanitize_text_field($bookingpress_appointment_data['customer_firstname']) : '';
                $customer_lastname  = ! empty($bookingpress_appointment_data['customer_lastname']) ? sanitize_text_field($bookingpress_appointment_data['customer_lastname']) : '';
                $customer_phone     = ! empty($bookingpress_appointment_data['customer_phone']) ? sanitize_text_field($bookingpress_appointment_data['customer_phone']) : '';
                $customer_country   = ! empty($bookingpress_appointment_data['customer_phone_country']) ? sanitize_text_field($bookingpress_appointment_data['customer_phone_country']) : '';
                $customer_phone_dial_code = !empty($bookingpress_appointment_data['customer_phone_dial_code']) ? $bookingpress_appointment_data['customer_phone_dial_code'] : '';
                if( !empty($bookingpress_appointment_data['bookingpress_customer_timezone']) ) {
                    $timezone_minutes = $bookingpress_appointment_data['bookingpress_customer_timezone'];
                    $client_timezone_offset = -1 * ( $timezone_minutes / 60 );
                    
                    $offset_minute = fmod( $client_timezone_offset, 1);
                    $offset_minute = abs($offset_minute);

                    $hours = $client_timezone_offset - $offset_minute;
                    

                    $offset_minute = $offset_minute * 60;

                    if( $hours < 0 ){

                    } else {
                        if( strlen( $hours ) === 1 ){
                            $hours = '+0' . $hours;
                        } else {
                            $hours = '+' . $hours;
                        }
                    }

                    if( strlen( $offset_minute ) == 1 ){
                        $offset_minute = '0' . $offset_minute;
                    }
                    
                    $timezone_offset = $hours.':' . $offset_minute;

                    $customer_timezone = $timezone_offset;
                    
                } else {
                    $customer_timezone = $bookingpress_global_options->bookingpress_get_site_timezone_offset();    
                }

                $return_data['customer_details'] = array(
                'customer_email'    => $customer_email,
                'customer_username' => $customer_username,
                'customer_phone'    => $customer_phone,
                );

                $bookingpress_appointment_status = $BookingPress->bookingpress_get_settings('appointment_status', 'general_setting');

                if ($payment_gateway == 'on-site' ) {
                    $bookingpress_appointment_status = $BookingPress->bookingpress_get_settings('onsite_appointment_status', 'general_setting');
                }

                $bookingpress_customer_id = get_current_user_id();

                // Insert data into entries table.
                $bookingpress_entry_details = array(
                'bookingpress_customer_id'           => $bookingpress_customer_id,
                'bookingpress_customer_name'         => $customer_username,
                'bookingpress_customer_phone'        => $customer_phone,
                'bookingpress_customer_firstname'    => $customer_firstname,
                'bookingpress_customer_lastname'     => $customer_lastname,
                'bookingpress_customer_country'      => $customer_country,
                'bookingpress_customer_phone_dial_code' => $customer_phone_dial_code,
                'bookingpress_customer_email'        => $customer_email,
                'bookingpress_customer_timezone'     => $customer_timezone,
                'bookingpress_service_id'            => $bookingpress_selected_service_id,
                'bookingpress_service_name'          => $service_data['bookingpress_service_name'],
                'bookingpress_service_price'         => $__payable_amount,
                'bookingpress_service_currency'      => $bookingpress_currency_name,
                'bookingpress_service_duration_val'  => $service_data['bookingpress_service_duration_val'],
                'bookingpress_service_duration_unit' => $service_data['bookingpress_service_duration_unit'],
                'bookingpress_payment_gateway'       => $payment_gateway,
                'bookingpress_appointment_date'      => $bookingpress_appointment_booked_date,
                'bookingpress_appointment_time'      => $bookingpress_selected_start_time,
                'bookingpress_appointment_end_time'  => $bookingpress_selected_end_time,
                'bookingpress_appointment_internal_note' => $bookingpress_internal_note,
                'bookingpress_appointment_send_notifications' => 1,
                'bookingpress_appointment_status'    => $bookingpress_appointment_status,
                'bookingpress_paid_amount'           =>   $__payable_amount,
                'bookingpress_created_at'            => current_time('mysql'),
                );

                do_action('bookingpress_payment_log_entry', $payment_gateway, 'submit appointment form front', 'bookingpress', $bookingpress_entry_details, $bookingpress_debug_payment_log_id);

                $wpdb->insert($tbl_bookingpress_entries, $bookingpress_entry_details);
                $entry_id = $wpdb->insert_id;

                $return_data['entry_id'] = $entry_id;
                $bookingpress_entry_hash = md5($entry_id);

                $bookingpress_after_approved_payment_page_id = $BookingPress->bookingpress_get_customize_settings('after_booking_redirection', 'booking_form');
                $bookingpress_after_approved_payment_url     = get_permalink($bookingpress_after_approved_payment_page_id);
                $bookingpress_after_approved_payment_url     = ! empty($bookingpress_after_approved_payment_url) ? $bookingpress_after_approved_payment_url : BOOKINGPRESS_HOME_URL;
                $bookingpress_after_approved_payment_url    = add_query_arg('appointment_id', base64_encode($entry_id), $bookingpress_after_approved_payment_url);
                $bookingpress_after_approved_payment_url = add_query_arg( 'bp_tp_nonce', wp_create_nonce( 'bpa_nonce_url-'.$bookingpress_entry_hash ), $bookingpress_after_approved_payment_url );

                $bookingpress_after_canceled_payment_page_id = $BookingPress->bookingpress_get_customize_settings('after_cancelled_appointment_redirection', 'booking_my_booking');
                $bookingpress_after_canceled_payment_url     = get_permalink($bookingpress_after_canceled_payment_page_id);
                $bookingpress_after_canceled_payment_url     = ! empty($bookingpress_after_canceled_payment_url) ? $bookingpress_after_canceled_payment_url : BOOKINGPRESS_HOME_URL;
                $bookingpress_after_canceled_payment_url     = add_query_arg('appointment_id', base64_encode($entry_id), $bookingpress_after_canceled_payment_url);
                $bookingpress_after_canceled_payment_url = add_query_arg( 'bp_tp_nonce', wp_create_nonce( 'bpa_nonce_url-'.$bookingpress_entry_hash ), $bookingpress_after_canceled_payment_url );

                $return_data['approved_appointment_url'] = $bookingpress_after_approved_payment_url;
                $return_data['pending_appointment_url']  = $bookingpress_after_approved_payment_url;
                $return_data['canceled_appointment_url'] = $bookingpress_after_canceled_payment_url;

                $bookingpress_notify_url   = BOOKINGPRESS_HOME_URL . '/?bookingpress-listener=bpa_' . $payment_gateway . '_url';
                $return_data['notify_url'] = $bookingpress_notify_url;
            }

            $return_data = apply_filters('bookingpress_after_modify_validate_submit_form_data', $return_data);

            return $return_data;
        }

        
        /**
         * Core function for confirm booking and insert appointment and payment details
         *
         * @param  mixed $entry_id
         * @param  mixed $payment_gateway_data
         * @param  mixed $payment_status
         * @param  mixed $transaction_id_field
         * @param  mixed $payment_amount_field
         * @param  mixed $is_front
         * @return void
         */
        public function bookingpress_confirm_booking( $entry_id, $payment_gateway_data, $payment_status, $transaction_id_field = '', $payment_amount_field = '', $is_front = 2 )
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_entries, $tbl_bookingpress_customers, $bookingpress_email_notifications, $bookingpress_debug_payment_log_id, $bookingpress_customers, $tbl_bookingpress_appointment_bookings, $bookingpress_global_options, $bookingpress_other_debug_log_id;

            $bookingpress_confirm_booking_received_data = array(
				'entry_id' => $entry_id,
				'payment_gateway_data' => wp_json_encode($payment_gateway_data),
				'payment_status' => $payment_status,
				'transaction_id_field' => $transaction_id_field,
				'payment_amount_field' => $payment_amount_field,
				'is_front' => $is_front,
			);
			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Booking form confirm booking data', 'bookingpress_complete_appointment', $bookingpress_confirm_booking_received_data, $bookingpress_other_debug_log_id );

            if (! empty($entry_id) ) {
                $entry_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = %d", $entry_id), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
                if (! empty($entry_data) ) {
                    $bookingpress_entry_user_id                  = $entry_data['bookingpress_customer_id'];
                    $bookingpress_customer_name                  = $entry_data['bookingpress_customer_name'];
                    $bookingpress_customer_phone                 = $entry_data['bookingpress_customer_phone'];
                    $bookingpress_customer_firstname             = $entry_data['bookingpress_customer_firstname'];
                    $bookingpress_customer_lastname              = $entry_data['bookingpress_customer_lastname'];
                    $bookingpress_customer_country               = $entry_data['bookingpress_customer_country'];
                    $bookingpress_customer_phone_dial_code       = $entry_data['bookingpress_customer_phone_dial_code'];
                    $bookingpress_customer_email                 = $entry_data['bookingpress_customer_email'];
                    
                    $bookingpress_customer_timezone              = $entry_data['bookingpress_customer_timezone'];
                    
                    $bookingpress_service_id                     = $entry_data['bookingpress_service_id'];
                    $bookingpress_service_name                   = $entry_data['bookingpress_service_name'];
                    $bookingpress_service_price                  = $entry_data['bookingpress_service_price'];
                    $bookingpress_service_currency               = $entry_data['bookingpress_service_currency'];
                    $bookingpress_service_duration_val           = $entry_data['bookingpress_service_duration_val'];
                    $bookingpress_service_duration_unit          = $entry_data['bookingpress_service_duration_unit'];
                    $bookingpress_payment_gateway                = $entry_data['bookingpress_payment_gateway'];
                    $bookingpress_appointment_date               = $entry_data['bookingpress_appointment_date'];
                    $bookingpress_appointment_time               = $entry_data['bookingpress_appointment_time'];
                    $bookingpress_appointment_end_time           = $entry_data['bookingpress_appointment_end_time'];
                    $bookingpress_appointment_internal_note      = $entry_data['bookingpress_appointment_internal_note'];
                    $bookingpress_appointment_send_notifications = $entry_data['bookingpress_appointment_send_notifications'];
                    $bookingpress_appointment_status             = $entry_data['bookingpress_appointment_status'];

                    $transaction_id = ( ! empty($transaction_id_field) && ! empty($payment_gateway_data[ $transaction_id_field ]) ) ? $payment_gateway_data[ $transaction_id_field ] : '';
                    $payable_amount = ( ! empty($payment_amount_field) && ! empty($payment_gateway_data[ $payment_amount_field ]) ) ? $payment_gateway_data[ $payment_amount_field ] : $bookingpress_service_price;

                    $bookingpress_customer_id = $bookingpress_wpuser_id = 0;

                    $bookingpress_customer_details = $bookingpress_customers->bookingpress_create_customer($entry_data, $bookingpress_entry_user_id, $is_front, 0, $bookingpress_customer_timezone);
                    if (! empty($bookingpress_customer_details) ) {
                        $bookingpress_customer_id = $bookingpress_customer_details['bookingpress_customer_id'];
                        $bookingpress_wpuser_id   = $bookingpress_customer_details['bookingpress_wpuser_id'];
                    }

                    $appointment_booking_fields = array(
                     'bookingpress_entry_id'           => $entry_id,
                     'bookingpress_payment_id'         => 0,
                     'bookingpress_customer_id'        => $bookingpress_customer_id,
                     'bookingpress_customer_name'      => $bookingpress_customer_name, 
                     'bookingpress_customer_lastname'  => $bookingpress_customer_lastname,
                     'bookingpress_customer_firstname' => $bookingpress_customer_firstname,
                     'bookingpress_customer_phone'     => $bookingpress_customer_phone,
                     'bookingpress_customer_country'   => $bookingpress_customer_country,
                     'bookingpress_customer_phone_dial_code' => $bookingpress_customer_phone_dial_code,
                     'bookingpress_customer_email'     => $bookingpress_customer_email, 
                     'bookingpress_service_id'         => $bookingpress_service_id,
                     'bookingpress_service_name'       => $bookingpress_service_name,
                     'bookingpress_service_price'      => $bookingpress_service_price,
                     'bookingpress_service_currency'   => $bookingpress_service_currency,
                     'bookingpress_service_duration_val' => $bookingpress_service_duration_val,
                     'bookingpress_service_duration_unit' => $bookingpress_service_duration_unit,
                     'bookingpress_appointment_date'   => $bookingpress_appointment_date,
                     'bookingpress_appointment_time'   => $bookingpress_appointment_time,
                     'bookingpress_appointment_end_time' => $bookingpress_appointment_end_time,
                     'bookingpress_appointment_internal_note' => $bookingpress_appointment_internal_note,
                     'bookingpress_appointment_send_notification' => $bookingpress_appointment_send_notifications,
                     'bookingpress_appointment_status' => $bookingpress_appointment_status,
                     'bookingpress_paid_amount' => $bookingpress_service_price,
                     'bookingpress_appointment_timezone' => $bookingpress_customer_timezone,
                     'bookingpress_created_at'         => current_time('mysql'),
                    );
                    
                    $bookingpress_appointment_data = array();
                    $appointment_booking_fields = apply_filters('bookingpress_modify_appointment_booking_fields', $appointment_booking_fields, $entry_data, $bookingpress_appointment_data);

                    do_action('bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'before insert appointment', 'bookingpress', $appointment_booking_fields, $bookingpress_debug_payment_log_id);

                    $inserted_booking_id = $BookingPress->bookingpress_insert_appointment_logs($appointment_booking_fields);

                    if (! empty($inserted_booking_id) ) {
                        $service_time_details = $BookingPress->bookingpress_get_service_end_time($bookingpress_service_id, $bookingpress_appointment_time, $bookingpress_service_duration_val, $bookingpress_service_duration_unit);
                        $service_start_time   = $service_time_details['service_start_time'];
                        $service_end_time     = $service_time_details['service_end_time'];

                        $payer_email = ! empty($payment_gateway_data['payer_email']) ? $payment_gateway_data['payer_email'] : '';

                        $bookingpress_last_invoice_id = $BookingPress->bookingpress_get_settings('bookingpress_last_invoice_id', 'invoice_setting');
                        $bookingpress_last_invoice_id++;
                        $BookingPress->bookingpress_update_settings('bookingpress_last_invoice_id', 'invoice_setting', $bookingpress_last_invoice_id);

                        $bookingpress_last_invoice_id = apply_filters('bookingpress_modify_invoice_id_externally', $bookingpress_last_invoice_id);

                        $payment_log_data = array(
                        'bookingpress_invoice_id'      => $bookingpress_last_invoice_id,
                        'bookingpress_appointment_booking_ref' => $inserted_booking_id,
                        'bookingpress_customer_id'     => $bookingpress_customer_id,
                        'bookingpress_customer_name'   => $bookingpress_customer_name,     
                        'bookingpress_customer_firstname' => $bookingpress_customer_firstname,
                        'bookingpress_customer_lastname' => $bookingpress_customer_lastname,
                        'bookingpress_customer_phone'     => $bookingpress_customer_phone,
                        'bookingpress_customer_country'   => $bookingpress_customer_country,
                        'bookingpress_customer_phone_dial_code' => $bookingpress_customer_phone_dial_code,
                        'bookingpress_customer_email'  => $bookingpress_customer_email,
                        'bookingpress_service_id'      => $bookingpress_service_id,
                        'bookingpress_service_name'    => $bookingpress_service_name,
                        'bookingpress_service_price'   => $bookingpress_service_price,
                        'bookingpress_payment_currency' => $bookingpress_service_currency,
                        'bookingpress_service_duration_val' => $bookingpress_service_duration_val,
                        'bookingpress_service_duration_unit' => $bookingpress_service_duration_unit,
                        'bookingpress_appointment_date' => $bookingpress_appointment_date,
                        'bookingpress_appointment_start_time' => $bookingpress_appointment_time,
                        'bookingpress_appointment_end_time' => $bookingpress_appointment_end_time,
                        'bookingpress_payment_gateway' => $bookingpress_payment_gateway,
                        'bookingpress_payer_email'     => $payer_email,
                        'bookingpress_transaction_id'  => $transaction_id,
                        'bookingpress_payment_date_time' => current_time('mysql'),
                        'bookingpress_payment_status'  => $payment_status,
                        'bookingpress_payment_amount'  => $payable_amount,
                        'bookingpress_payment_currency' => $bookingpress_service_currency,
                        'bookingpress_payment_type'    => '',
                        'bookingpress_payment_response' => '',
                        'bookingpress_additional_info' => '',
                        'bookingpress_paid_amount' => $bookingpress_service_price,
                        'bookingpress_created_at'      => current_time('mysql'),
                        );

                        do_action('bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'before insert payment', 'bookingpress', $payment_log_data, $bookingpress_debug_payment_log_id);

                        $payment_log_data = apply_filters('bookingpress_modify_payment_log_fields', $payment_log_data, $entry_data);

                        $payment_log_id = $BookingPress->bookingpress_insert_payment_logs($payment_log_data);
                        if(!empty($payment_log_id)){
                            $wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_payment_id' => $payment_log_id), array('bookingpress_appointment_booking_id' => $inserted_booking_id));
                            $wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_booking_id' => $bookingpress_last_invoice_id), array('bookingpress_appointment_booking_id' => $inserted_booking_id));
                        }

                        $bookingpress_email_notification_type = '';
                        if ($bookingpress_appointment_status == '2' ) {
                            $bookingpress_email_notification_type = 'Appointment Pending';
                        } elseif ($bookingpress_appointment_status == '1' ) {
                            $bookingpress_email_notification_type = 'Appointment Approved';
                        } elseif ($bookingpress_appointment_status == '3' ) {
                            $bookingpress_email_notification_type = 'Appointment Canceled';
                        } elseif ($bookingpress_appointment_status == '4' ) {
                            $bookingpress_email_notification_type = 'Appointment Rejected';
                        }                        

                        do_action('bookingpress_after_add_appointment_from_backend', $inserted_booking_id, $bookingpress_appointment_data, $entry_id);
                        
                        $bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification($bookingpress_email_notification_type, $inserted_booking_id, $bookingpress_customer_email);

                        return $payment_log_id;
                    }
                }
            }

            return 0;
        }

                
        /**
         * Return paypal supported currency list
         *
         * @return void
         */
        function bookingpress_paypal_supported_currency_list() {            
            /* 25 currency */
            $bookingpress_currency_list = array('AUD','BRL','CAD','CZK','DKK','EUR','HKD','HUF','ILS','JPY','MYR','MXN','TWD','NZD','NOK','PHP','PLN','GBP','RUB','SGD','SEK','CHF','THB','USD','CNY');
            return $bookingpress_currency_list;
        }        
    }
}

global $bookingpress_payment_gateways;
$bookingpress_payment_gateways = new bookingpress_payment_gateways();
