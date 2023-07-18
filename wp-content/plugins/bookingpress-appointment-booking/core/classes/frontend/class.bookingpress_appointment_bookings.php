<?php
if (! class_exists('bookingpress_appointment_bookings')  && class_exists('BookingPress_Core')) {
    class bookingpress_appointment_bookings Extends BookingPress_Core
    {
        var $bookingpress_form_category;
        var $bookingpress_form_service;
        var $bookingpress_hide_category_service;
        var $bookingpress_default_date_format;
        var $bookingpress_default_time_format;
        var $bookingpress_form_fields_error_msg_arr;
        var $bookingpress_form_fields_new;
        var $bookingpress_is_service_load_from_url;

        var $bookingpress_mybooking_random_id;
        var $bookingpress_mybooking_default_date_format;
        var $bookingpress_mybooking_customer_username;
        var $bookingpress_mybooking_customer_email;
        var $bookingpress_mybooking_login_user_id;
        var $bookingpress_mybooking_wpuser_id;
        var $bookingpress_delete_customer_profile;
        var $bookingpress_calendar_list;

        function __construct()
        {
            global $BookingPress;

            $this->bookingpress_form_category               = 0;
            $this->bookingpress_form_service                = 0;
            $this->bookingpress_hide_category_service       = 0;
            $this->bookingpress_default_date_format         = get_option('date_format');
            $this->bookingpress_default_time_format         = get_option('time_format');
            $this->bookingpress_form_fields_error_msg_arr   = array();
            $this->bookingpress_form_fields_new             = array();
            $this->bookingpress_is_service_load_from_url    = 0;
            $this->bookingpress_calendar_list               = '';
            $this->bookingpress_mybooking_customer_username = '';
            $this->bookingpress_mybooking_customer_email    = '';
            $this->bookingpress_mybooking_wpuser_id         = 0;

            add_filter('bookingpress_front_booking_dynamic_data_fields', array( $this, 'bookingpress_booking_dynamic_data_fields_func' ), 10, 5);

            add_filter('bookingpress_front_booking_dynamic_helper_vars', array( $this, 'bookingpress_booking_dynamic_helper_vars_func' ), 10, 1);

            if( $BookingPress->bpa_is_pro_exists() && $BookingPress->bpa_is_pro_active() ){
                if( !empty( $BookingPress->bpa_pro_plugin_version() ) && version_compare( $BookingPress->bpa_pro_plugin_version(), '1.5', '>' ) ){
                    add_filter( 'bookingpress_front_booking_dynamic_on_load_methods', array( $this, 'bookingpress_booking_dynamic_on_load_methods_func_with_pro'));
                } else {
                    add_filter('bookingpress_front_booking_dynamic_on_load_methods', array( $this, 'bookingpress_booking_dynamic_on_load_methods_func' ), 10, 1);
                }
            } else {
                add_filter('bookingpress_front_booking_dynamic_on_load_methods', array( $this, 'bookingpress_booking_dynamic_on_load_methods_func' ), 10, 1);
            }
            add_filter( 'bookingpress_front_booking_dynamic_on_load_methods', array( $this, 'bookingpress_call_autofocus_method'), 100 );
            
            add_filter('bookingpress_front_booking_dynamic_vue_methods', array( $this, 'bookingpress_booking_dynamic_vue_methods_func' ), 10, 1);
            add_action('media_buttons', array( $this, 'bookingpress_insert_shortcode_button' ), 20);

            add_shortcode('bookingpress_form', array( $this, 'bookingpress_front_booking_form' ));
            add_shortcode('bookingpress_company_avatar', array( $this, 'bookingpress_company_avatar_func' ));
            add_shortcode('bookingpress_company_name', array( $this, 'bookingpress_company_name_func' ));
            add_shortcode('bookingpress_company_website', array( $this, 'bookingpress_company_website_func' ));
            add_shortcode('bookingpress_company_address', array( $this, 'bookingpress_company_address_func' ));
            add_shortcode('bookingpress_company_phone', array( $this, 'bookingpress_company_phone_func' ));
            add_shortcode('bookingpress_appointment_service', array( $this, 'bookingpress_appointment_service_func' ));
            add_shortcode('bookingpress_appointment_datetime', array( $this, 'bookingpress_appointment_datetime_func' ));
            add_shortcode('bookingpress_appointment_customername', array( $this, 'bookingpress_appointment_customername_func' ));
            add_shortcode('bookingpress_my_appointments', array( $this, 'bookingpress_my_appointments_func' ));
            add_shortcode('bookingpress_delete_account', array($this, 'bookingpress_delete_account_func'));
            add_shortcode('booking_id', array($this, 'bookingpress_booking_id_func'));

            add_action('wp_ajax_bookingpress_front_get_category_services', array( $this, 'bookingpress_get_category_service_data' ), 10);
            add_action('wp_ajax_nopriv_bookingpress_front_get_category_services', array( $this, 'bookingpress_get_category_service_data' ), 10);

            add_action('wp_ajax_bookingpress_front_get_timings', array( $this, 'bookingpress_retrieve_timeslots' ), 10);
            add_action('wp_ajax_nopriv_bookingpress_front_get_timings', array( $this, 'bookingpress_retrieve_timeslots' ), 10);

            add_action('wp_ajax_bookingpress_front_save_appointment_booking', array( $this, 'bookingpress_save_appointment_booking_func' ), 10);
            add_action('wp_ajax_nopriv_bookingpress_front_save_appointment_booking', array( $this, 'bookingpress_save_appointment_booking_func' ), 10);

            add_action('wp_ajax_bookingpress_before_book_appointment', array( $this, 'bookingpress_before_book_appointment_func' ), 10);
            add_action('wp_ajax_nopriv_bookingpress_before_book_appointment', array( $this, 'bookingpress_before_book_appointment_func' ), 10);

            add_action('wp_ajax_bookingpress_cancel_appointment', array( $this, 'bookingpress_cancel_appointment' ), 10);
            add_action('wp', array( $this, 'bookingpress_cancel_appointment_func' ), 10);

            /* fornt-end mybooking */

            add_action('bookingpress_front_appointments_dynamic_data_fields', array( $this, 'bookingpress_front_appointments_dynamic_data_fields_func' ));
            add_action('bookingpress_front_appointments_dynamic_helper_vars', array( $this, 'bookingpress_front_appointments_dynamic_helper_vars_func' ));
            add_action('bookingpress_front_appointments_dynamic_on_load_methods', array( $this, 'bookingpress_front_appointments_dynamic_on_load_methods_func' ));
            add_action('bookingpress_front_appointments_dynamic_vue_methods', array( $this, 'bookingpress_front_appointments_dynamic_vue_methods_func' ));

            add_action('wp_ajax_bookingpress_get_customer_appointments', array( $this, 'bookingpress_get_customer_appointments_func' ), 10);

            add_action('wp_ajax_bookingpress_get_disable_date', array( $this, 'bookingpress_get_disable_date_func' ), 10);
            add_action('wp_ajax_nopriv_bookingpress_get_disable_date', array( $this, 'bookingpress_get_disable_date_func' ), 10);                      
            // New action for BG Calls to check disable dates for full day booking
            add_action('wp_ajax_bookingpress_get_whole_day_appointments', array( $this, 'bookingpress_get_whole_day_appointments_func' ), 10);
            add_action('wp_ajax_nopriv_bookingpress_get_whole_day_appointments', array( $this, 'bookingpress_get_whole_day_appointments_func' ), 10);                      

            /** Calendar Integration Data */
			add_shortcode( 'bookingpress_appointment_calendar_integration', array( $this, 'bookingpress_booking_calendar_options' ) );
			add_action( 'init', array( $this, 'bookingpress_download_ics_file' ) );
            add_action('wp_ajax_bookingpress_get_appointment_details_for_calendar', array($this, 'bookingpress_get_appointment_details_for_calendar_func'));
            add_action('wp_ajax_nopriv_bookingpress_get_appointment_details_for_calendar', array($this, 'bookingpress_get_appointment_details_for_calendar_func'));

            add_action('wp_ajax_bookingpress_delete_account', array($this, 'bookingpress_delete_customer_account_func'));
        }
        
        /**
         * Used for delete customer account
         *
         * @return void
         */
        function bookingpress_delete_customer_account_func(){
            global $wpdb, $tbl_bookingpress_customers, $tbl_bookingpress_appointment_bookings, $BookingPress, $tbl_bookingpress_customers_meta;
            $response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');

            if (! $bpa_verify_nonce_flag ) {
                $response['variant']      = 'error';
                $response['title']        = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']          = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                wp_send_json($response);
                die();
            }

            $response['variant']    = 'error';
            $response['title']      = 'Error';
            $response['msg']        = 'Something went wrong....';

            $bookingpress_login_user_id = get_current_user_id();

            if(!empty($bookingpress_login_user_id)){
                $bookingpress_customer_rows = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id = %d ORDER BY bookingpress_customer_id DESC", $bookingpress_login_user_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm
                
                do_action('bookingpress_delete_customer_log',$bookingpress_customer_rows,$_REQUEST);

                if(!empty($bookingpress_customer_rows)){
                    do_action( 'bookingpress_before_delete_customer', $bookingpress_customer_rows['bookingpress_customer_id'] );
                    $delete = $wpdb->delete( $tbl_bookingpress_customers, array( 'bookingpress_customer_id' => $bookingpress_customer_rows['bookingpress_customer_id'] ), array( '%d' ) );
                    if ( $delete > 0 ) {
                        $delete1 = $wpdb->delete( $tbl_bookingpress_customers_meta, array( 'bookingpress_customer_id' => $bookingpress_customer_rows['bookingpress_customer_id'] ), array( '%d' ) );
                        $delete2 = $wpdb->delete( $tbl_bookingpress_appointment_bookings, array( 'bookingpress_customer_id' => $bookingpress_customer_rows['bookingpress_customer_id'] ), array( '%d' ) );
                        $customer_role_id = new WP_User( $bookingpress_login_user_id );
                        $customer_role_id->remove_role('bookingpress-customer');

                        wp_logout();

                        $response['variant'] = 'success';
                        $response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
                        $response['msg'] = esc_html__('Account Deleted Successfully', 'bookingpress-appointment-booking');
                    }
                }else{
                    $response['msg'] = esc_html__('No customers exist in BookingPress', 'bookingpress-appointment-booking');    
                }
            }else{
                $response['msg'] = esc_html__('Please login to your account for delete BookingPress account', 'bookingpress-appointment-booking');
            }

            echo wp_json_encode($response);
            exit;
        }
        
        /**
         * Customer delete account shortcode callable function
         *
         * @param  mixed $atts
         * @param  mixed $content
         * @param  mixed $tag
         * @return void
         */
        function bookingpress_delete_account_func($atts, $content, $tag){
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries,$bookingpress_global_options;
            $BookingPress->set_front_css(1);
            $BookingPress->set_front_js(1); 
            $BookingPress->bookingpress_load_mybooking_custom_css();
            //$BookingPress->bookingpress_load_mybookings_custom_js();

            $bookingpress_short_atts = array(
                'cancel_button_text' => esc_html__('Cancel', 'bookingpress-appointment-booking'),
                'delete_button_text' => esc_html__('Delete', 'bookingpress-appointment-booking'),
            );

            $atts = shortcode_atts($bookingpress_short_atts, $atts, $tag);

            $bookingpress_nonce = esc_html(wp_create_nonce('bpa_wp_nonce'));

            $content = '<div class="bpa-front-dcw__body-btn-group">';
            $content .= '<button class="el-button bpa-front-btn bpa-front-btn__medium" onclick="bookingpress_cancel_delete_acc()"><span>'.$atts['cancel_button_text'].'</span></button>';
            $content .= '<button class="el-button bpa-front-btn bpa-front-btn__medium bpa-front-btn--danger" onclick="bookingpress_delete_account()"><span>'.$atts['delete_button_text'].'</span></button>';
            $content .= '</div>';

            $content .= '<script type="text/javascript">';
            $content .= 'function bookingpress_cancel_delete_acc(){
                location.reload();
                //window.app._data.bookingpress_my_booking_current_tab = "my_appointment"
            }';
            $content .= 'function bookingpress_delete_account(){
                var bkp_wpnonce_pre = "' . $bookingpress_nonce . '";
                var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
                {
                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
                }
                else {
                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
                }
                var postData = { action: "bookingpress_delete_account", _wpnonce:bkp_wpnonce_pre_fetch };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    if(response.variant != "error"){
                        location.reload();
                    }else{
                        vm.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: response.data.variant,
                            customClass: "error_notification",
                        });
                    }
                }.bind(this) )
                .catch( function (error) {
                    console.log(error);
                });
            }';
            $content .= '</script>';

            return do_shortcode($content);
        }
        
        /**
         * Get appointment details for thank you page calendar
         *
         * @return void
         */
        function bookingpress_get_appointment_details_for_calendar_func(){
            global $wpdb, $tbl_bookingpress_entries, $tbl_bookingpress_appointment_bookings, $BookingPress;
            $response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');

            if (! $bpa_verify_nonce_flag ) {
                $response['variant']      = 'error';
                $response['title']        = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']          = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                wp_send_json($response);
                die();
            }

            $response['variant']    = 'error';
            $response['title']      = 'Error';
            $response['msg']        = 'Something went wrong....';
            $response['google_calendar_link'] = '';
            $response['yahoo_calendar_link'] = '';

            $bookingpress_appointment_id = !empty($_POST['bookingpress_appointment_id']) ? intval($_POST['bookingpress_appointment_id']) : 0;
            if(!empty($bookingpress_appointment_id)){
                $appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d OR bookingpress_entry_id = %d", $bookingpress_appointment_id, $bookingpress_appointment_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

                if(!empty($appointment_data)){
                    $service_id = intval( $appointment_data['bookingpress_service_id'] );

                    $bookingpress_start_time = $start_time =  sanitize_text_field( $appointment_data['bookingpress_appointment_time'] );
                    $bookingpress_end_time = $end_time = sanitize_text_field( $appointment_data['bookingpress_appointment_end_time'] );

                    $service_duration = sanitize_text_field( $appointment_data['bookingpress_service_duration_val'] );

                    $service_duration_unit = sanitize_text_field( $appointment_data['bookingpress_service_duration_unit'] );

                    $service_end_time = $BookingPress->bookingpress_get_service_end_time( $service_id, $service_start_time, $service_duration, $service_duration_unit );

                    $bookingpress_start_time_for_yahoo = date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . 'T' . date( 'His', strtotime( $bookingpress_start_time ) ) . 'Z';

                    $bookingpress_start_time = $this->bookingpress_convert_date_time_to_utc( $appointment_data['bookingpress_appointment_date'], $bookingpress_start_time );
                    $bookingpress_end_time = $this->bookingpress_convert_date_time_to_utc( $appointment_data['bookingpress_appointment_date'], $bookingpress_end_time );                    

                    $bookingpress_service_name = ! empty( $appointment_data['bookingpress_service_name'] ) ? stripslashes_deep($appointment_data['bookingpress_service_name']) : '';
                                         
                    if ( 'd' != $service_duration_unit ) {
                        $bookingpress_tmp_start_time = new DateTime($start_time);
                        $bookingpress_tmp_end_time = new DateTime($end_time);
                        $booking_date_interval = $bookingpress_tmp_start_time->diff($bookingpress_tmp_end_time);
                        $service_duration_time = ($booking_date_interval->h * 60) + ($booking_date_interval->i);
                        $service_duration_time = '00'.$service_duration_time;                        
                    } else {
                        $service_duration_time = $service_duration . '00';
                    }

                    $response['variant'] = 'success';
                    $response['title'] = 'Success';
                    $response['msg'] = 'Links generated successfully';
                    $response['google_calendar_link'] = urlencode($bookingpress_service_name)."&dates=".esc_html($bookingpress_start_time)."/".esc_html($bookingpress_end_time);
                    $response['yahoo_calendar_link'] = urlencode($bookingpress_service_name)."&st=".esc_html($bookingpress_start_time_for_yahoo)."&dur=".esc_html($service_duration_time);
                }
            }

            echo wp_json_encode($response);
            exit;
        }


        /**
         * Background function for disable future dates
         *
         * @return void
         */
        function bookingpress_get_whole_day_appointments_func() {
            // phpcs:ignore WordPress.Security.NonceVerification
            global $BookingPress;

            $month_check = !empty( $_POST['next_month'] ) ? intval( $_POST['next_month'] ) : date('m', current_time('timestamp') ); // phpcs:ignore WordPress.Security.NonceVerification
            if( !empty( $_POST['appointment_data_obj'] ) && !is_array( $_POST['appointment_data_obj'] ) ){
                $_POST['appointment_data_obj'] = json_decode( stripslashes_deep( $_POST['appointment_data_obj'] ), true ); //phpcs:ignore
                $_POST['appointment_data_obj'] =  !empty($_POST['appointment_data_obj']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_POST['appointment_data_obj'] ) : array(); // phpcs:ignore
            }

            $bookingpress_disabled_dates = !empty($_POST['days_off_disabled_dates']) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), explode(',',$_POST['days_off_disabled_dates']) ) : array(); // phpcs:ignore
            $daysoff_dates = $bookingpress_disabled_dates;

            $first_date_of_month = date('Y', current_time('timestamp') ) . '-' . $month_check . '-01';
            $last_date_of_month = date('Y-m-t', strtotime( $first_date_of_month ) );

            $start_date = new DateTime( $first_date_of_month );
            $end_date = new DateTime( $last_date_of_month );

            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod( $start_date, $interval, $end_date );

            foreach( $period as $dt ){
                $current_date = $dt->format("Y-m-d H:i:s");
                $date_t = date('c', strtotime( $current_date ) );
                if( !in_array( $date_t, $daysoff_dates ) ){
                    $current_selected_date = $dt->format( 'Y-m-d' );
                    $is_time_slot_available = $this->bookingpress_retrieve_timeslots( $current_selected_date, true, true );
                    if( false == $is_time_slot_available ){
                        $daysoff_dates[] = $date_t;
                    }
                }
            }

            $max_available_month = !empty( $_POST['max_available_month'] ) ? sanitize_text_field( $_POST['max_available_month'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
            $response['prevent_next_month_check']  = false;
            if( !empty( $max_available_month ) && $max_available_month == $month_check && $_POST['max_available_year'] < date('Y', current_time('timestamp') ) ){ // phpcs:ignore WordPress.Security.NonceVerification
                $response['prevent_next_month_check']  = true;
            }

            $bookingpress_selected_service = !empty($_REQUEST['selected_service']) ? intval($_REQUEST['selected_service']) : '';

            $bookingpress_appointment_data = !empty($_POST['appointment_data_obj']) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_data_obj'] ) : array(); // phpcs:ignore
            
            if(empty($bookingpress_selected_service)){
                $bookingpress_selected_service = $bookingpress_appointment_data['selected_service'];
            }

            $daysoff_dates = apply_filters( 'bookingpress_modify_disable_dates_with_staffmember', $daysoff_dates, $bookingpress_selected_service, $first_date_of_month );

            $response[ 'days_off_disabled_dates' ] = implode( ',', $daysoff_dates );
            $response['next_month'] = date( 'm', strtotime( $first_date_of_month . '+1 month') );

            $response = array_merge( $_POST, $response ); // phpcs:ignore

            echo json_encode( $response );

            die;
        }
        
        /**
         * Get default disable dates
         *
         * @return void
         */
        function bookingpress_get_disable_date_func( $bpa_selected_date = '', $consider_selected_date = false, $counter = 1, $bpa_total_booked_appointment = array() ) {
            
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs;            
            $response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');

            if (! $bpa_verify_nonce_flag ) {
                $response['variant']      = 'error';
                $response['title']        = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']          = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                $response['redirect_url'] = '';
                wp_send_json($response);
                die();
            }

            $response['variant']    = 'error';
            $response['title']      = 'Error';
            $response['msg']        = 'Something went wrong....';

            
            if( !empty( $_POST['appointment_data_obj'] ) && !is_array( $_POST['appointment_data_obj'] ) ){
               $_POST['appointment_data_obj'] = json_decode( stripslashes_deep( $_POST['appointment_data_obj'] ), true ); //phpcs:ignore
               $_POST['appointment_data_obj'] =  !empty($_POST['appointment_data_obj']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_POST['appointment_data_obj'] ) : array(); // phpcs:ignore
               $_REQUEST['appointment_data_obj']  = $_POST['appointment_data_obj'] ;
            }
            
            $bookingpress_appointment_data = !empty($_POST['appointment_data_obj']) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_data_obj'] ) : array(); // phpcs:ignore
            $bookingpress_selected_date = !empty($_REQUEST['selected_date']) ? sanitize_text_field($_REQUEST['selected_date']) : '';
            
            if(!empty($bookingpress_selected_date)){
                $bookingpress_selected_date = date('Y-m-d', strtotime($bookingpress_selected_date));
            }
            
            if( "NaN-NaN-NaN" == $bookingpress_selected_date || '1970-01-01' == $bookingpress_selected_date || !preg_match('/(\d{4}\-\d{2}\-\d{2})/', $bookingpress_selected_date ) ){
                $bookingpress_selected_date = date('Y-m-d', current_time('timestamp') );
            }
            
            $bookingpress_selected_service= !empty($_REQUEST['selected_service']) ? intval($_REQUEST['selected_service']) : '';

            if(empty($bookingpress_selected_service)){
                $bookingpress_selected_service = $bookingpress_appointment_data['selected_service'];
            }

            if(empty($bookingpress_appointment_data['selected_service_duration_unit']) || empty($bookingpress_appointment_data['selected_service_duration']) ){
                $bookingpress_service_data = $BookingPress->get_service_by_id($bookingpress_selected_service);
                if(!empty($bookingpress_service_data['bookingpress_service_duration_unit'])){
                    $bookingpress_appointment_data['selected_service_duration_unit'] = $bookingpress_service_data['bookingpress_service_duration_unit'];
                    $bookingpress_appointment_data['selected_service_duration'] = intval($bookingpress_service_data['bookingpress_service_duration_val']);
                }
            }

            if(empty($bookingpress_selected_date)){
                $bookingpress_selected_date = !empty( $bookingpress_appointment_data['selected_date'] ) ? $bookingpress_appointment_data['selected_date'] : date('Y-m-d', current_time('timestamp') );
            }
            if( true == $consider_selected_date && !empty( $bpa_selected_date ) ){
                $bookingpress_selected_date = $bpa_selected_date;
            }

            if( "NaN-NaN-NaN" == $bookingpress_selected_date || '1970-01-01' == $bookingpress_selected_date || !preg_match('/(\d{4}\-\d{2}\-\d{2})/', $bookingpress_selected_date ) ){
                $bookingpress_selected_date = date('Y-m-d', current_time('timestamp') );
            }


            $bookingpress_selected_staffmember_id = !empty($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) ? intval($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) : '';
            

            $is_multiple_day_event = false;
            if( !empty( $bookingpress_appointment_data['selected_service_duration_unit'] ) && 'd' == $bookingpress_appointment_data['selected_service_duration_unit'] && 1 < $bookingpress_appointment_data['selected_service_duration'] ){                
                $is_multiple_day_event = true;
            }
            
            if(!empty($bookingpress_selected_service)) {
                $bookingpress_disable_date = $BookingPress->bookingpress_get_default_dayoff_dates('','',$bookingpress_selected_service,$bookingpress_selected_staffmember_id);

                $bookingpress_disable_date = apply_filters('bookingpress_modify_disable_dates', $bookingpress_disable_date, $bookingpress_selected_service, $bookingpress_selected_date, $bookingpress_appointment_data);
                
                $bookingpress_start_date = date('Y-m-d', current_time('timestamp'));
                if( true == $consider_selected_date && !empty( $bpa_selected_date ) ){
                    $bookingpress_start_date = $bpa_selected_date;
                }
                $bookingpress_end_date = date('Y-m-d', strtotime('last day of this month', strtotime( $bookingpress_start_date )));
                
                $next_month = date( 'm', strtotime( $bookingpress_end_date . '+1 day' ) );
                $next_year = date( 'Y', strtotime( $bookingpress_end_date . '+1 day' ) );
                
                $bookingpress_total_booked_appointment_where_clause = '';
                $bookingpress_total_booked_appointment_where_clause = apply_filters( 'bookingpress_total_booked_appointment_where_clause', $bookingpress_total_booked_appointment_where_clause );
                $bookingpress_total_appointment = $wpdb->get_results($wpdb->prepare("SELECT bookingpress_appointment_date,bookingpress_service_duration_val FROM " . $tbl_bookingpress_appointment_bookings . " WHERE (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s) AND bookingpress_service_id= %d AND bookingpress_appointment_date BETWEEN %s AND %s ".$bookingpress_total_booked_appointment_where_clause." GROUP BY bookingpress_appointment_date",'1','2',$bookingpress_selected_service,$bookingpress_start_date, $bookingpress_end_date), ARRAY_A); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                
                /** reputelog - check for this loop as it's adding the current selected date to disabled time slots if an appointment is booked in the selected date */
                if( !empty( $bpa_total_booked_appointment ) ){

                    $bookingpress_total_appointment = array_merge( $bookingpress_total_appointment, $bpa_total_booked_appointment );
                    //$bookingpress_total_appointment = array_unique( $bookingpress_total_appointment );
                }
                foreach($bookingpress_total_appointment as $key  => $value) {
                    if(!empty($value['bookingpress_appointment_date'])){
                        $bookingpress_appointment_date = !empty($value['bookingpress_appointment_date']) ? $value['bookingpress_appointment_date'] : '';
                        //$bookingpress_time_slot_data = $BookingPress->bookingpress_get_service_available_time($bookingpress_selected_service,$bookingpress_appointment_date );
                        $bookingpress_time_slots = $this->bookingpress_check_booked_appointments( $bookingpress_appointment_date );
                        
                        $bookingpress_time_slot_data = array_merge(
                            $bookingpress_time_slots['morning_time'],
                            $bookingpress_time_slots['afternoon_time'],
                            $bookingpress_time_slots['evening_time'],
                            $bookingpress_time_slots['night_time']
                        );
                        
                        if(!empty($bookingpress_time_slot_data)) {
                            $is_booked = 1;   
                            foreach($bookingpress_time_slot_data as $key2 => $value2) {                            
                                if( ( isset($value2['is_booked']) && $value2['is_booked'] == 0 ) || empty( $value2['is_booked'] ) ){
                                    if( isset( $value2['max_capacity'] ) && isset( $value2['total_booked'] ) && $value2['total_booked'] >= $value2['max_total_capacity'] ) {
                                        /** Do nothing */
                                    } else {
                                        $is_booked = 0;
                                        break;
                                    }
                                }
                            }                                                 
                            if($is_booked == 1) {
                                $bookingpress_disable_date[] = date('c', strtotime( $bookingpress_appointment_date));
                            }
                        } else {
                            if( $is_multiple_day_event ){
                                $service_duration_val = $value['bookingpress_service_duration_val'];
                                
                                $bookingpress_disable_date[] = date('c', strtotime( $bookingpress_appointment_date));
                                for( $d = 1; $d < $service_duration_val; $d++ ){
                                    $bookingpress_disable_date[] = date( 'c', strtotime( $bookingpress_appointment_date . '+' . $d . ' days' ));
                                }
                                
                                for( $dm = $service_duration_val - 1; $dm > 0; $dm-- ){
                                    $bookingpress_disable_date[] = date( 'c', strtotime( $bookingpress_appointment_date . '-' . $dm . ' days'));
                                }
                            } else {
                                $bookingpress_disable_date[] = date('c', strtotime( $bookingpress_appointment_date));
                            }
                        }
                    }
                };

                $bookingpress_disable_date = apply_filters( 'bookingpress_modify_disable_dates_with_staffmember', $bookingpress_disable_date, $bookingpress_selected_service);
                
                $bookingpress_selected_date = $BookingPress->bookingpress_select_date_before_load($bookingpress_selected_date,$bookingpress_disable_date);
                
                $response['variant']    = 'success';
                $response['title']      = 'Success';
                $response['msg']        = 'Data reterive successfully';                            
                $response['days_off_disabled_dates']  =  implode(',',array_unique( $bookingpress_disable_date ) );                            
                $response['selected_date']  = date('Y-m-d', strtotime($bookingpress_selected_date));
                $response['next_month'] = $next_month;
                $response['next_year'] = $next_year;
                $response['msg']        = 'Data reterive successfully';                            
            }

	        // store disabled dates array to session for final validations upon booking
            if(session_id() == '' OR session_status() === PHP_SESSION_NONE) {
				session_start();
			}
            
            $_SESSION['disable_dates'] = array();    
            $_SESSION['disable_dates'] = $bookingpress_disable_date;
            /** Get Front Timings Changes Start */
            $get_period_available_for_booking = $BookingPress->bookingpress_get_settings('period_available_for_booking', 'general_setting');
            
            $response['prevent_next_month_check'] = false;
            if( !empty( $get_period_available_for_booking ) ){           
                $bookingpress_current_date = date('Y-m-d', current_time('timestamp') );
                $max_available_date = date('Y-m-d', strtotime( $bookingpress_current_date . '+' . $get_period_available_for_booking . ' days') );
                $response['max_available_date'] = $max_available_date;
                $response['max_available_month'] = date('m', strtotime( $max_available_date ) );
                $response['max_available_year'] = date('Y', strtotime( $max_available_date ) );
                if( $max_available_date < $response['selected_date'] ){
                    $response['front_timings'] = array();
                    $response['next_month'] = $next_month;
                    wp_send_json( $response );
                    die;
                }
            }
            
            if( !empty( $response['max_available_month'] ) && $next_month > $response['max_available_month'] && $response['max_available_year'] < date('Y', current_time('timestamp') ) ){
                $response['prevent_next_month_check'] = true;
            }
            $response['check_for_multiple_days_event'] = false;
            if( !empty( $bookingpress_appointment_data['selected_service_duration_unit'] ) && 'd' == $bookingpress_appointment_data['selected_service_duration_unit'] ){
                $response['check_for_multiple_days_event'] = true;
            }
            

            /** multiple days event */
            $multiple_days_event = false;
            if( !empty( $_POST['appointment_data_obj']['selected_service_duration_unit'] ) && $_POST['appointment_data_obj']['selected_service_duration_unit'] == 'd' ){
                $multiple_days_event = true;
            }

            $front_timings = $this->bookingpress_retrieve_timeslots( $response['selected_date'], true );

            $is_custom_duration = ( !empty( $front_timings['is_custom_duration'] ) && 1 == $front_timings['is_custom_duration'] ) ? true : false;
            if( !empty( $front_timings ) && !$is_custom_duration ){
                
                $is_front_timings_empty = false;
                $total_time_slots = 0;
                $total_booked_time_slots = 0;
                $total_timings = count( $front_timings );
                $empty_slots = 0;

                foreach( $front_timings as $k => $val ){
                    
                    if( !empty( $val ) && count( $val ) > 0 ){
                        foreach( $val as $ik => $iv ){
                            if( 1 == $iv['is_booked'] ){
                                $total_booked_time_slots++;
                            } else if( isset( $iv['max_capacity'] ) && isset( $iv['total_booked'] ) && $iv['total_booked'] >= $iv['max_total_capacity'] ){
                                $total_booked_time_slots++;
                            }
                            $total_time_slots++;
                        }
                    } else if( empty( $val ) ){
                        $empty_slots++;
                    }
                }
                if( ( $total_time_slots == $total_booked_time_slots && 0 < $total_time_slots && $total_booked_time_slots ) || $total_timings == $empty_slots ){
                    $is_front_timings_empty = true;
                }
                
                if( $is_front_timings_empty && $multiple_days_event ){
                    $is_front_timings_empty = false;
                }
                
                $response['front_timings'] = $front_timings;
                if( true == $is_front_timings_empty  ){
                    $response['empty_front_timings'] = true;
                    if( true == $consider_selected_date ){
                        $posted_selected_date = $bpa_selected_date;
                    } else {
                        $posted_selected_date = !empty($_REQUEST['selected_date']) ? sanitize_text_field($_REQUEST['selected_date']) : '';
                    }
                    //$bookingpress_selected_date = $BookingPress->bookingpress_select_date_before_load($posted_selected_date,$bookingpress_disable_date); /** reputelog - need to check with pro version data */                    
                    $response['next_available_date'] = date('Y-m-d', strtotime($posted_selected_date.'+1 day') );
                    
                    $this->bookingpress_get_disable_date_func( $response['next_available_date'], true, $counter++, $bookingpress_total_appointment );
                    
                }
            }

            
            /** Get Front Timings Changes End */            

            $response = apply_filters('bookingpress_modify_disable_date_data',$response);    

            
            
            wp_send_json($response);
            exit;
        }

        function bookingpress_check_booked_appointments( $disabled_date ){
            return $this->bookingpress_retrieve_timeslots( $disabled_date, true );
        }

                
        /**
         * Insert shortcode from classic editor
         *
         * @param  mixed $content
         * @return void
         */
        function bookingpress_insert_shortcode_button( $content )
        {
            global $bookingpress_global_options;
            $allowed_pages_for_media_button = array( 'post.php', 'post-new.php' );

            if (isset($_SERVER['PHP_SELF']) && ! in_array(basename($_SERVER['PHP_SELF']), $allowed_pages_for_media_button) ) {
                return;
            }
            if (! isset($post_type) ) {
                $post_type = '';
            }
            if (isset($_SERVER['PHP_SELF']) && basename(sanitize_text_field($_SERVER['PHP_SELF'])) == 'post.php' ) {
                $post_id   = isset($_REQUEST['post']) ? sanitize_text_field($_REQUEST['post']) : 0;
                $post_type = get_post_type($post_id);
            }
            if (isset($_SERVER['PHP_SELF']) && basename(sanitize_text_field($_SERVER['PHP_SELF'])) == 'post-new.php' ) {
                if (isset($_REQUEST['post_type']) ) {
                    $post_type = sanitize_text_field($_REQUEST['post_type']);
                } else {
                    $post_type = 'post';
                }
            }

            if( $content != 'content'){
                return;
            }

            $allowed_post_types = array( 'post', 'page' );

            if (! in_array($post_type, $allowed_post_types) ) {
                return;
            }
            if (! wp_script_is('jquery', 'enqueued') ) {
                wp_enqueue_script('jquery');
            }
            if (! wp_style_is('bookingpress_tinymce', 'enqueued') ) {
                wp_enqueue_style('bookingpress_tinymce', BOOKINGPRESS_URL . '/css/bookingpress_tinymce.css', array(), BOOKINGPRESS_VERSION);
            }
            wp_register_script('bookingpress_vue_js', BOOKINGPRESS_URL . '/js/bookingpress_vue.min.js', array(), BOOKINGPRESS_VERSION, 0);
            wp_register_script('bookingpress_element_js', BOOKINGPRESS_URL . '/js/bookingpress_element.js', array( '' ), '2.51.5', 0);
            wp_register_script('bookingpress_element_en_js', BOOKINGPRESS_URL . '/js/bookingpress_element_en.js', array( '' ), '2.51.5', 0);
            wp_register_script('bookingpress_wordpress_vue_helper_js', BOOKINGPRESS_URL . '/js/bookingpress_wordpress_vue_qs_helper.js', array( '' ), '6.5.1', 0);

            wp_enqueue_script('bookingpress_vue_js');
            wp_enqueue_script('bookingpress_element_js');
            wp_enqueue_script('bookingpress_element_en_js');
            wp_enqueue_script('bookingpress_wordpress_vue_helper_js');

            wp_register_style('bookingpress_element_css', BOOKINGPRESS_URL . '/css/bookingpress_element_theme.css', array(), BOOKINGPRESS_VERSION);
            wp_enqueue_style('bookingpress_element_css');

            if (wp_script_is('bookingpress_vue_js', 'enqueued') ) {
                $this->bookingpress_insert_shortcode_popup();
            }

            $bookingpress_site_current_language = $bookingpress_global_options->bookingpress_get_site_current_language();

            $bpa_inline_script_data = '         				        					        		
					var lang = ELEMENT.lang.' . $bookingpress_site_current_language . '
					ELEMENT.locale(lang)			
					var app = new Vue({						
						el: "#bookingpress_shortcode_form",
						data() {
							var bookingpress_return_data = {
								open_bookingpress_shortcode_modal: false,
								close_modal_on_esc: true,
								centerDialogVisible: false,
								selected_bookingpress_shortcode: "", 
								append_modal_to_body: true,
							};
							return bookingpress_return_data;			
						},
						mounted(){
						},
						methods: {							
							model_action() {
								const vm= this
								if(vm.open_bookingpress_shortcode_modal == true ) {
									vm.open_bookingpress_shortcode_modal = false;		
								} else {
									vm.open_bookingpress_shortcode_modal = true;
								}					
							},
							bookingpress_open_form_shortcode_popup(){
								this.model_action();
							},
							add_bookingpress_shortcode(){
								const vm = this
								if(vm.selected_bookingpress_shortcode != "") {
									if(tinyMCE.activeEditor != null){
										var editorContent = tinyMCE.activeEditor.getContent()
										editorContent += "["+vm.selected_bookingpress_shortcode+"]"
										tinyMCE.activeEditor.setContent(editorContent)
									}
									else{
										var textEditorContent = document.getElementById("content").innerHTML
										textEditorContent += "\n["+vm.selected_bookingpress_shortcode+"]"
										document.getElementById("content").innerHTML = textEditorContent
									}
									vm.model_action();
								}
							}
						},
					});';

            wp_add_inline_script('bookingpress_wordpress_vue_helper_js', $bpa_inline_script_data);
        }
        
        /**
         * Load HTML content of classic editor button view
         *
         * @return void
         */
        function bookingpress_insert_shortcode_popup()
        {
            if (file_exists(BOOKINGPRESS_VIEWS_DIR . '/bookingpress_tinymce_options_shortcodes.php') ) {
                include BOOKINGPRESS_VIEWS_DIR . '/bookingpress_tinymce_options_shortcodes.php';
            }
            ?>
            <?php
        }
        
        /**
         * Server Side Validaton - Backend Side Validation
         *
         * @return void
         */
        function bookingpress_before_book_appointment_func()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs,$tbl_bookingpress_customers,$bookingpress_payment_gateways;
            $response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $response['variant']      = 'error';
                $response['title']        = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']          = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                $response['redirect_url'] = '';
                wp_send_json($response);
                die();
            }
            $response['variant']    = 'success';
            $response['title']      = '';
            $response['msg']        = '';
            $response['error_type'] = '';
            
            if( !empty( $_REQUEST['appointment_data'] ) && !is_array( $_REQUEST['appointment_data'] ) ){
                $_REQUEST['appointment_data'] = json_decode( stripslashes_deep( $_REQUEST['appointment_data'] ), true ); //phpcs:ignore                
                $_REQUEST['appointment_data'] =  !empty($_REQUEST['appointment_data']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_REQUEST
                ['appointment_data'] ) : array(); // phpcs:ignore
                $_POST['appointment_data'] = $_REQUEST['appointment_data'];
            }
            
            $no_service_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_service_selected_for_the_booking', 'message_setting');

            $no_appointment_date_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_appointment_date_selected_for_the_booking', 'message_setting');

            $no_appointment_time_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_appointment_time_selected_for_the_booking', 'message_setting');

            $no_payment_method_is_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_payment_method_is_selected_for_the_booking', 'message_setting');

            $duplicate_email_address_found = $BookingPress->bookingpress_get_settings('duplicate_email_address_found', 'message_setting');

            $unsupported_currecy_selected_for_the_payment = $BookingPress->bookingpress_get_settings('unsupported_currecy_selected_for_the_payment', 'message_setting');

            $duplidate_appointment_time_slot_found = $BookingPress->bookingpress_get_settings('duplidate_appointment_time_slot_found', 'message_setting');

            $bookingpress_service_price = isset($_REQUEST['appointment_data']['service_price_without_currency']) ? floatval($_REQUEST['appointment_data']['service_price_without_currency']) : 0;

            if (empty($_POST['appointment_data']['selected_service']) ) {
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html($no_service_selected_for_the_booking);
                echo json_encode($response);
                exit();
            }

            if (empty($_POST['appointment_data']['selected_date']) ) {
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html($no_appointment_date_selected_for_the_booking);
                echo json_encode($response);
                exit();
            }

            if (empty($_POST['appointment_data']['selected_start_time']) || empty($_POST['appointment_data']['selected_end_time']) ) {
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html($no_appointment_time_selected_for_the_booking);
                echo json_encode($response);
                exit();
            }

            if (empty($_POST['appointment_data']['selected_payment_method']) && $bookingpress_service_price > 0 ) {
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html($no_payment_method_is_selected_for_the_booking);
                echo json_encode($response);
                exit();
            }

            $bookingpress_fullname  = ! empty($_POST['appointment_data']['customer_name']) ? trim(sanitize_text_field($_POST['appointment_data']['customer_name'])) : '';
            $bookingpress_firstname = ! empty($_POST['appointment_data']['customer_firstname']) ? trim(sanitize_text_field($_POST['appointment_data']['customer_firstname'])) : '';
            $bookingpress_lastname  = ! empty($_POST['appointment_data']['customer_lastname']) ? trim(sanitize_text_field($_POST['appointment_data']['customer_lastname'])) : '';
            $bookingpress_email     = ! empty($_POST['appointment_data']['customer_email']) ? sanitize_email($_POST['appointment_data']['customer_email']) : '';

            if (strlen($bookingpress_fullname) > 255 ) {
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Fullname is too long...', 'bookingpress-appointment-booking');
                echo json_encode($response);
                exit();
            }
            if (strlen($bookingpress_firstname) > 255 ) {
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Firstname is too long...', 'bookingpress-appointment-booking');
                echo json_encode($response);
                exit();
            }
            if (strlen($bookingpress_lastname) > 255 ) {
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Lastname is too long...', 'bookingpress-appointment-booking');
                echo json_encode($response);
                exit();
            }
            if (strlen($bookingpress_email) > 255 ) {
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Email address is too long...', 'bookingpress-appointment-booking');
                echo json_encode($response);
                exit();
            }
            $bookingpress_selected_payment_method = sanitize_text_field($_POST['appointment_data']['selected_payment_method']);
            $bookingpress_currency_name           = $BookingPress->bookingpress_get_settings('payment_default_currency', 'payment_setting');

            $bookingpress_paypal_currency = $bookingpress_payment_gateways->bookingpress_paypal_supported_currency_list();            
            if ($bookingpress_selected_payment_method == 'paypal' && !in_array($bookingpress_currency_name,$bookingpress_paypal_currency ) ) {
                
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html($unsupported_currecy_selected_for_the_payment);
                echo json_encode($response);
                exit();
            }

            $appointment_service_id    = intval($_POST['appointment_data']['selected_service']);
            $appointment_selected_date = date('Y-m-d', strtotime(sanitize_text_field($_POST['appointment_data']['selected_date'])));
            $appointment_start_time    = date('H:i:s', strtotime(sanitize_text_field($_POST['appointment_data']['selected_start_time'])));
            $appointment_end_time      = date('H:i:s', strtotime(sanitize_text_field($_POST['appointment_data']['selected_end_time'])));

            $is_appointment_exists = $BookingPress->bookingpress_is_appointment_booked($appointment_service_id, $appointment_selected_date, $appointment_start_time, $appointment_end_time);
            if ($is_appointment_exists) {
                $response['variant']              = 'error';
                $response['title']                = 'Error';
                $response['msg']                  = esc_html($duplidate_appointment_time_slot_found);
                echo json_encode($response);
                exit();
            }

            // If selected date is day off then display error.
            $bookingpress_search_query              = preg_quote($appointment_selected_date, '~');
            $bookingpress_get_default_daysoff_dates = $BookingPress->bookingpress_get_default_dayoff_dates();
            $bookingpress_search_date               = preg_grep('~' . $bookingpress_search_query . '~', $bookingpress_get_default_daysoff_dates);
            if (! empty($bookingpress_search_date) ) {
                $booking_dayoff_msg     = esc_html__('Selected date is off day', 'bookingpress-appointment-booking');
                $booking_dayoff_msg    .= '. ' . esc_html__('So please select new date', 'bookingpress-appointment-booking') . '.';
                $response['error_type'] = 'dayoff';
                $response['variant']    = 'error';
                $response['title']      = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']        = $booking_dayoff_msg;
                echo json_encode($response);
                exit();
            }

            // If payment gateway is disable then return error
            if ($bookingpress_selected_payment_method == 'on-site' && $bookingpress_service_price > 0 ) {
                $on_site_payment = $BookingPress->bookingpress_get_settings('on_site_payment', 'payment_setting');
                if (empty($on_site_payment) || ( $on_site_payment == 'false' ) ) {
                    $response['variant'] = 'error';
                    $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                    $response['msg']     = __('On site payment gateway is not active', 'bookingpress-appointment-booking') . '.';
                    echo json_encode($response);
                    exit();
                }
            } elseif ($bookingpress_selected_payment_method == 'paypal' && $bookingpress_service_price > 0 ) {
                $paypal_payment = $BookingPress->bookingpress_get_settings('paypal_payment', 'payment_setting');
                if (empty($paypal_payment) || ( $paypal_payment == 'false' ) ) {
                    $response['variant'] = 'error';
                    $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                    $response['msg']     = __('PayPal payment gateway is not active', 'bookingpress-appointment-booking') . '.';
                    echo json_encode($response);
                    exit();
                }

                if ($bookingpress_service_price < floatval('0.1') ) {
                    $response['variant'] = 'error';
                    $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                    $response['msg']     = esc_html__('Paypal supports minimum amount 0.1', 'bookingpress-appointment-booking');
                    echo json_encode($response);
                    exit();
                }
            }

            do_action('bookingpress_validate_booking_form', $_POST);

        }
        
        /**
         * Cancel appointment from customer cancel link
         *
         * @return void
         */
        function bookingpress_cancel_appointment_func()
        {               
            if( !empty($_SERVER["HTTP_USER_AGENT"]) && $_SERVER["HTTP_USER_AGENT"] == 'WhatsApp' ){
                return;
            }
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $bookingpress_email_notifications, $bookingpress_services;
            $cancel_id    = ! empty($_REQUEST['bpa_cancel']) ? intval(base64_decode($_REQUEST['bpa_cancel'])) : 0; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['bpa_cancel'] is sanitized properly
            $cancel_token = ! empty($_REQUEST['cancel_id']) ? sanitize_text_field($_REQUEST['cancel_id']) : '';

            if (! empty($cancel_id) && ! empty($cancel_token) ) {
                // Get payment log id and insert canceled appointment entry
                $payment_log_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_appointment_booking_ref = %d", $cancel_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
                if (! empty($payment_log_data) ) {
                    $bookingpress_customer_data = $BookingPress->get_customer_details($payment_log_data['bookingpress_customer_id']);
                    $bookingpress_customer_id   = $bookingpress_customer_data['bookingpress_customer_id'];
                    $bookingpress_wpuser_id     = $bookingpress_customer_data['bookingpress_wpuser_id'];

                    $customer_cancel_token = $BookingPress->get_bookingpress_customersmeta($bookingpress_customer_id, 'bpa_cancel_id');
                    if (! empty($customer_cancel_token) && ( $customer_cancel_token == $cancel_token ) ) {
                        $bookingpress_appointment_date = $payment_log_data['bookingpress_appointment_date'];
                        $bookingpress_appointment_time = $payment_log_data['bookingpress_appointment_start_time'];
                        $bookingpress_appointment_datetime = $bookingpress_appointment_date." ".$bookingpress_appointment_time;
                        $bookingpress_service_id = $payment_log_data['bookingpress_service_id'];
                        $current_datetime = date( 'Y-m-d H:i:s', current_time('timestamp') );

                        $allow_cancel_appointment = true;
                        if( $bookingpress_appointment_datetime <= $current_datetime ){
							$allow_cancel_appointment = false;
						}

                        if($allow_cancel_appointment == true){
                            $bookingpress_min_time_before_cancel = $BookingPress->bookingpress_get_settings('default_minimum_time_for_canceling', 'general_setting');

                            //Check service level minimum time required before cancel
                            $bookingpress_service_min_time_require_before_cancel = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'minimum_time_required_before_cancelling');
                            if(!empty($bookingpress_service_min_time_require_before_cancel)){
                                if($bookingpress_service_min_time_require_before_cancel == 'disabled'){
                                    $bookingpress_min_time_before_cancel = 'disabled';
                                }else if($bookingpress_service_min_time_require_before_cancel != 'inherit'){
                                    $bookingpress_min_time_before_cancel = $bookingpress_service_min_time_require_before_cancel;
                                }
                            }

                            //Check minimum cancel time
                            if($allow_cancel_appointment && !empty($bookingpress_min_time_before_cancel) && $bookingpress_min_time_before_cancel != 'disabled'){
                                $bookingpress_from_time = current_time('timestamp');
                                $bookingpress_to_time = $bookingpress_appointment_datetime;
                                $bookingpress_time_diff_for_cancel = round(abs($bookingpress_to_time - $bookingpress_from_time) / 60, 2);

                                if($bookingpress_time_diff_for_cancel < $bookingpress_min_time_before_cancel){
                                    $allow_cancel_appointment = false;
                                }
                            }
                        }

                        if($allow_cancel_appointment){
                            $bookingress_customer_email = $payment_log_data['bookingpress_customer_email'];

                            $bookingpress_after_canceled_payment_page_id = $BookingPress->bookingpress_get_customize_settings('after_cancelled_appointment_redirection', 'booking_my_booking');                        
                            $bookingpress_after_canceled_payment_url     = get_permalink($bookingpress_after_canceled_payment_page_id);
                            $bookingpress_after_canceled_payment_url = ! empty($bookingpress_after_canceled_payment_url) ? $bookingpress_after_canceled_payment_url : BOOKINGPRESS_HOME_URL;

                            $wpdb->update($tbl_bookingpress_appointment_bookings, array( 'bookingpress_appointment_status' => '3' ), array( 'bookingpress_appointment_booking_id' => $cancel_id ));

                            $bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification('Appointment Canceled', $cancel_id, $bookingress_customer_email);

                            $BookingPress->delete_bookingpress_customersmeta($bookingpress_customer_id, 'bpa_cancel_id');

                            do_action('bookingpress_after_cancel_appointment', $cancel_id);

                            wp_redirect($bookingpress_after_canceled_payment_url);
                        }else{
                            $bookingpress_alert_msg = esc_html__("We're sorry! you can't cancel this appointment because minimum required time for cancellation is already passed", "bookingpress-appointment-booking");

                            $bookingpress_alert_script = "<script>";
                            $bookingpress_alert_script .= "alert('".$bookingpress_alert_msg."')";
                            $bookingpress_alert_script .= "</script>";

                            echo $bookingpress_alert_script; // phpcs:ignore
                        }
                    }
                }
            }
        }
        
        /**
         * Cancel appointment from ajax request
         *
         * @return void
         */
        function bookingpress_cancel_appointment()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $bookingpress_email_notifications;
            $response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $response['variant']      = 'error';
                $response['title']        = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']          = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                $response['redirect_url'] = '';
                wp_send_json($response);
                die();
            }

            $response['variant'] = 'error';
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');

            $appointment_cancelled_successfully = $BookingPress->bookingpress_get_settings('appointment_cancelled_successfully', 'message_setting');
            $cancel_id                          = ! empty($_REQUEST['cancel_id']) ? intval($_REQUEST['cancel_id']) : 0;

            if (! empty($cancel_id) ) {
                $bookingpress_after_canceled_payment_page_id = $BookingPress->bookingpress_get_customize_settings('after_cancelled_appointment_redirection', 'booking_my_booking');
                $bookingpress_after_canceled_payment_url = get_permalink($bookingpress_after_canceled_payment_page_id);

                $bookingpress_after_canceled_payment_url = ! empty($bookingpress_after_canceled_payment_url) ? $bookingpress_after_canceled_payment_url : BOOKINGPRESS_HOME_URL;

                $wpdb->update($tbl_bookingpress_appointment_bookings, array( 'bookingpress_appointment_status' => '3' ), array( 'bookingpress_appointment_booking_id' => $cancel_id ));

                // Get payment log id and insert canceled appointment entry
                $payment_log_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_appointment_booking_ref = %d", $cancel_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
                if (! empty($payment_log_data) ) {
                    $bookingress_customer_email = $payment_log_data['bookingpress_customer_email'];

                    $bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification('Appointment Canceled', $cancel_id, $bookingress_customer_email);

                    do_action('bookingpress_after_cancel_appointment', $cancel_id);
                }

                $response['variant']      = 'success';
                $response['title']        = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']          = esc_html($appointment_cancelled_successfully);
                $response['redirect_url'] = $bookingpress_after_canceled_payment_url;
            }

            echo json_encode($response);
            exit();
        }
        
        /**
         * My appointment shortcode callback function
         *
         * @param  mixed $atts
         * @param  mixed $content
         * @param  mixed $tag
         * @return void
         */
        function bookingpress_my_appointments_func( $atts, $content, $tag )
        {   

                global $wpdb, $BookingPress,$bookingpress_global_options;

                $this->bookingpress_mybooking_login_user_id = get_current_user_id();                
                $bookingpress_global_options_arr       = $bookingpress_global_options->bookingpress_global_options();
                $bookingpress_default_date_time_format = $bookingpress_global_options_arr['wp_default_date_format'];
                $bookingpress_default_date_format = 'MMMM D, YYYY';  
                if ($bookingpress_default_date_time_format == 'F j, Y' ) {
                    $bookingpress_default_date_format = 'MMMM D, YYYY';
                } elseif ($bookingpress_default_date_time_format == 'Y-m-d' ) {
                    $bookingpress_default_date_format = 'YYYY-MM-DD';
                } elseif ($bookingpress_default_date_time_format == 'm/d/Y' ) {
                    $bookingpress_default_date_format = 'MM/DD/YYYY';
                } elseif($bookingpress_default_date_time_format == 'd/m/Y') {
                    $bookingpress_default_date_format = 'DD/MM/YYYY';
                } elseif ($bookingpress_default_date_time_format == 'd.m.Y') {
                    $bookingpress_default_date_format = 'DD.MM.YYYY';
                } elseif ($bookingpress_default_date_time_format == 'd-m-Y') {
                    $bookingpress_default_date_format = 'DD-MM-YYYY';
                }

                $this->bookingpress_mybooking_default_date_format = $bookingpress_default_date_format;

                $BookingPress->set_front_css(1);
                $BookingPress->set_front_js(1);
                $BookingPress->bookingpress_load_mybooking_custom_css();
               // $BookingPress->bookingpress_load_mybookings_custom_js();

                $bookingpress_uniq_id = uniqid();
                ob_start();
                $bookingpress_my_appointments_file_url = BOOKINGPRESS_VIEWS_DIR . '/frontend/appointment_my_appointments.php';
                $bookingpress_my_appointments_file_url = apply_filters('bookingpress_change_my_appointmens_shortcode_file_url', $bookingpress_my_appointments_file_url);
                include $bookingpress_my_appointments_file_url;
                $content .= ob_get_clean();

                add_action(
                    'wp_footer',
                    function () use ( &$bookingpress_uniq_id ) {
                        global $bookingpress_global_options , $BookingPress;
                        $bookingpress_global_details     = $bookingpress_global_options->bookingpress_global_options();
                        $bookingpress_formatted_timeslot = $bookingpress_global_details['bpa_time_format_for_timeslot'];
                        $requested_module                = 'front_appointments';
                        ?>
                        <script>
                            window.addEventListener('DOMContentLoaded', function() {
                                var bpa_customer_username = '<?php echo esc_html($this->bookingpress_mybooking_customer_email); ?>';
                                var bpa_customer_email = '<?php echo esc_html($this->bookingpress_mybooking_customer_email); ?>';
                                var bpa_customer_id = '<?php echo esc_html($this->bookingpress_mybooking_wpuser_id); ?>';
                        <?php do_action('bookingpress_' . $requested_module . '_dynamic_helper_vars'); ?>
                            var app = new Vue({
                                    el: '#bookingpress_booking_form_<?php echo esc_html($bookingpress_uniq_id); ?>',
                                    directives: { <?php do_action('bookingpress_' . $requested_module . '_dynamic_directives'); ?> },
                                    components: { <?php do_action('bookingpress_' . $requested_module . '_dynamic_components'); ?> },
                                data() {
                                        var bookingpress_return_data = <?php do_action('bookingpress_' . $requested_module . '_dynamic_data_fields'); ?>;
                                    bookingpress_return_data['is_display_loader'] = '0';
                                    bookingpress_return_data['bookingpress_uniq_id'] = '<?php echo esc_html($bookingpress_uniq_id); ?>';
                                    bookingpress_return_data['pickerOptions'] = 
                                    {                                         
                                        disabledDate(Time) 
                                        {                        
                                            var dd = String(Time.getDate()).padStart(2, '0');
                                            var mm = String(Time.getMonth() + 1).padStart(2, '0'); //January is 0!
                                            var yyyy = Time.getFullYear();
                                            var time = yyyy+ '-' + mm + '-' + dd ;                        
                                            var disable_date= bookingpress_return_data['disabledDates'].indexOf(time)>-1;
                                            var date = new Date();
                                             date.setDate(date.getDate()-1);
                                            var disable_past_date = Time.getTime() < date.getTime();
                                            if(disable_date == true) {
                                                return disable_date; 
                                            } else {
                                                return disable_past_date;
                                            }
                                        },                                         
                                    }
                                    return bookingpress_return_data;
                                },
                                filters:{
                                    bookingpress_format_date: function(value){
                                            var default_date_format = '<?php echo esc_html($this->bookingpress_mybooking_default_date_format); ?>'
                                        return moment(String(value)).format(default_date_format)
                                    },
                                    bookingpress_format_time: function(value){
                                            var default_time_format = '<?php echo esc_html($bookingpress_formatted_timeslot); ?>'
                                        return moment(String(value), "HH:mm:ss").format(default_time_format)
                                    }
                                },
                                beforeCreate(){
                                    this.is_front_appointment_empty_loader = '1';
                                },
                                created(){
                                },
                                mounted() {
                        <?php do_action('bookingpress_' . $requested_module . '_dynamic_on_load_methods'); ?>            
                                },
                                methods: {
                        <?php do_action('bookingpress_' . $requested_module . '_dynamic_vue_methods'); ?>
                                },
                            });
                        });
                        </script>
                        <?php
                    },
                    100
                );

                $bookingpress_custom_css = $BookingPress->bookingpress_get_customize_settings('custom_css', 'booking_form');
                $bookingpress_custom_css = !empty($bookingpress_custom_css) ? stripslashes_deep($bookingpress_custom_css) : '';
                wp_add_inline_style( 'bookingpress_front_mybookings_custom_css', $bookingpress_custom_css, 'after' );            
            return do_shortcode($content);
        }
        
        /**
         * Get customers my appointments list
         *
         * @return void
         */
        function bookingpress_get_customer_appointments_func()
        {
            global $BookingPress,$wpdb,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_customers,$bookingpress_global_options, $tbl_bookingpress_payment_logs;
            $response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                wp_send_json($response);
                die();
            }
            $bpa_login_customer_id             = get_current_user_id();
            $bookingpress_get_customer_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id =%d ORDER BY bookingpress_customer_id DESC", $bpa_login_customer_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
            $bookingpress_current_user_id      = ! empty($bookingpress_get_customer_details['bookingpress_customer_id']) ? $bookingpress_get_customer_details['bookingpress_customer_id'] : 0;                               
            $bookingpress_customer_data = $appointments_data = array();
            $bookingpress_customer_data['bookingpress_user_email'] = '';
            $bookingpress_customer_data['bookingpress_user_fullname'] = '';
            $bookingpress_customer_data['bookingpress_avatar_url'] = BOOKINGPRESS_IMAGES_URL . '/default-avatar.jpg';

            $bookingpress_total_appointments = 0;

            $perpage     = isset($_POST['perpage']) ? intval($_POST['perpage']) : 10;
            $currentpage = isset($_POST['currentpage']) ? intval($_POST['currentpage']) : 1;
            $offset      = ( ! empty($currentpage) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;

            if (! empty($bookingpress_current_user_id) ) {
                $bpa_customer_firstname = ! empty($bookingpress_get_customer_details['bookingpress_user_firstname']) ? esc_html($bookingpress_get_customer_details['bookingpress_user_firstname']) : '';
                $bpa_customer_lastname = ! empty($bookingpress_get_customer_details['bookingpress_user_lastname']) ? esc_html($bookingpress_get_customer_details['bookingpress_user_lastname']) : '';		
                $bookingpress_user_fullname = $bpa_customer_firstname.' '.$bpa_customer_lastname ;    
                $bookingpress_user_email = ! empty( $bookingpress_get_customer_details['bookingpress_user_email'] ) ? sanitize_email( $bookingpress_get_customer_details['bookingpress_user_email'] ) : '';            
                $bpa_avatar_url = get_avatar_url( $bpa_login_customer_id );

                $bookingpress_get_existing_avatar_url = $BookingPress->get_bookingpress_customersmeta( $bookingpress_current_user_id, 'customer_avatar_details' );
                $bookingpress_get_existing_avatar_url = ! empty( $bookingpress_get_existing_avatar_url ) ? maybe_unserialize( $bookingpress_get_existing_avatar_url ) : array();            
                if ( ! empty( $bookingpress_get_existing_avatar_url[0]['url'] ) ) {
                    $bookingpress_user_avatar = $bookingpress_get_existing_avatar_url[0]['url'];
                } else {
                    $bookingpress_user_avatar = BOOKINGPRESS_IMAGES_URL . '/default-avatar.jpg';
                }
                $bookingpress_customer_data['bookingpress_user_email'] = $bookingpress_user_email;
                $bookingpress_customer_data['bookingpress_user_fullname'] = $bookingpress_user_fullname;
                $bookingpress_customer_data['bookingpress_avatar_url'] = $bookingpress_user_avatar;         

                 // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['search_data'] contains mixed array and it's been sanitized properly using 'appointment_sanatize_field' function
                $bookingpress_search_data        = ! empty($_REQUEST['search_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data']) : array();
                $bookingpress_search_query       = '';
                $bookingpress_search_query_where = "WHERE 1=1 AND (bookingpress_customer_id={$bookingpress_current_user_id}) ";

                if (! empty($bookingpress_search_data) ) {
                    if (! empty($bookingpress_search_data['search_appointment']) ) {
                        $bookingpress_search_string       = $bookingpress_search_data['search_appointment'];
                        $bookingpress_search_query_where .= "AND (bookingpress_service_name LIKE '%{$bookingpress_search_string}%') ";
                    }
                    if ( !empty ( $bookingpress_search_data['selected_date_range'] ) && ! empty($bookingpress_search_data['selected_date_range'][0] && $bookingpress_search_data['selected_date_range'][1]) ) {                        
                        $bookingpress_search_date         = $bookingpress_search_data['selected_date_range'];
                        $start_date                       = date('Y-m-d', strtotime($bookingpress_search_date[0]));
                        $end_date                         = date('Y-m-d', strtotime($bookingpress_search_date[1]));
                        $bookingpress_search_query_where .= "AND (bookingpress_appointment_date BETWEEN '{$start_date}' AND '{$end_date}')";
                    }
                }

                $bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();
                $bookingpress_date_format = $bookingpress_global_data['wp_default_date_format'];
                $bookingpress_time_format = $bookingpress_global_data['wp_default_time_format'];
                $bookingpress_appointment_statuses = $bookingpress_global_data['appointment_status'];
                $bookingpress_payment_statuses = $bookingpress_global_data['payment_status'];
                
                $bookingpress_total_appointments = $wpdb->get_var("SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} {$bookingpress_search_query} {$bookingpress_search_query_where} ORDER BY bookingpress_appointment_date DESC"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
                $appointments_data = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_appointment_bookings} {$bookingpress_search_query} {$bookingpress_search_query_where} ORDER BY bookingpress_appointment_date DESC LIMIT {$offset} , {$perpage}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

                if(!empty($appointments_data) && is_array($appointments_data) ){
                    foreach($appointments_data as $k => $v){
                        $bookingpress_appointment_date = date($bookingpress_date_format, strtotime($v['bookingpress_appointment_date']));
                        $appointments_data[$k]['bookingpress_appointment_formatted_date'] = $bookingpress_appointment_date;

                        $bookingpress_appointment_start_time = date($bookingpress_time_format, strtotime($v['bookingpress_appointment_time']));
                        $appointments_data[$k]['bookingpress_appointment_formatted_start_time'] = $bookingpress_appointment_start_time;
                        $bookingpress_appointment_end_time = date($bookingpress_time_format, strtotime($v['bookingpress_appointment_end_time']));
                        $appointments_data[$k]['bookingpress_appointment_formatted_end_time'] = $bookingpress_appointment_end_time;

                        $bookingpress_appointment_duration_unit_label = esc_html__('Minutes', 'bookingpress-appointment-booking');
                        if($v['bookingpress_service_duration_unit'] == 'h'){
                            $bookingpress_appointment_duration_unit_label = esc_html__('Hours', 'bookingpress-appointment-booking');
                        } else if( 'd' == $v['bookingpress_service_duration_unit'] ){
                            $bookingpress_appointment_duration_unit_label = esc_html__('Days', 'bookingpress-appointment-booking');
                        }

                        $appointments_data[$k]['bookingpress_service_duration_label'] = $bookingpress_appointment_duration_unit_label;

                        $bookingpress_appointment_status_label = '';
                        foreach($bookingpress_appointment_statuses as $k2 => $v2){
                            if($v2['value'] == $v['bookingpress_appointment_status']){
                                $bookingpress_appointment_status_label = $v2['text'];
                            }
                        }
                        $appointments_data[$k]['bookingpress_appointment_status_label'] = $bookingpress_appointment_status_label;

                        $currency_name   = $v['bookingpress_service_currency'];
                        $currency_symbol = $BookingPress->bookingpress_get_currency_symbol($currency_name);
                        $bookingpress_paid_price_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($v['bookingpress_paid_amount'], $currency_symbol);
                        $appointments_data[$k]['bookingpress_paid_price_with_currency'] = $bookingpress_paid_price_with_currency;

                        //get payment log details
                        $bookingpress_appointment_id = intval($v['bookingpress_appointment_booking_id']);
                        $bookingpress_payment_log_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_appointment_booking_ref = %d", $bookingpress_appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

                        $appointments_data[$k]['booking_id'] = !empty($v['bookingpress_booking_id']) ? sanitize_text_field($v['bookingpress_booking_id']) : 1;

                        $bookingpress_payment_status = $bookingpress_payment_status_label = $bookingpress_payment_method = '';
                        if(!empty($bookingpress_payment_log_details)){
                            $bookingpress_payment_method = $bookingpress_payment_log_details['bookingpress_payment_gateway'];
                            $bookingpress_payment_status = $bookingpress_payment_log_details['bookingpress_payment_status'];

                            foreach($bookingpress_payment_statuses as $k2 => $v2){
                                if($v2['value'] == $bookingpress_payment_status){
                                    $bookingpress_payment_status_label = $v2['text'];
                                }
                            }
                        }
                        $appointments_data[$k]['bookingpress_payment_status'] = $bookingpress_payment_status;
                        $appointments_data[$k]['bookingpress_payment_status_label'] = $bookingpress_payment_status_label;
                        $appointments_data[$k]['bookingpress_payment_method'] = $bookingpress_payment_method;
                    }
                }
            }
            $data['customer_details'] = $bookingpress_customer_data;
            $data['items'] = $appointments_data;
            $data['total_records'] = $bookingpress_total_appointments;

            $data = apply_filters('bookingpress_modify_my_appointments_data', $data);

            wp_send_json($data);
            exit;
        }
                
        /**
         * Callback function of [bookingpress_appointment_service] shortcode
         *
         * @param  mixed $atts
         * @param  mixed $content
         * @param  mixed $tag
         * @return void
         */
        function bookingpress_appointment_service_func( $atts, $content, $tag )
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries;
            $BookingPress->set_front_css(1);
            $BookingPress->set_front_js(1);
            $BookingPress->bookingpress_load_booking_form_custom_css();
            $bookingpress_short_atts = array(
            'appointment_id' => 0,
            );

            $atts           = shortcode_atts($bookingpress_short_atts, $atts, $tag);
            $appointment_id = $atts['appointment_id'];

            $bookingpress_uniq_id = !empty($_POST['bookingpress_uniq_id']) ? sanitize_text_field($_POST['bookingpress_uniq_id']) : '';

            if(!empty($bookingpress_uniq_id)){
                if(!empty($_COOKIE['bookingpress_cart_id'])) {
                    $appointment_id = base64_decode($_COOKIE['bookingpress_cart_id']); //phpcs:ignore
                } else {
                    $bookingpress_cookie_name = $bookingpress_uniq_id."_appointment_data";
                    if(!empty($_COOKIE[$bookingpress_cookie_name])){
                        $bookingpress_cookie_value = sanitize_text_field($_COOKIE[$bookingpress_cookie_name]);
                        $bookingpress_entry_id = base64_decode($bookingpress_cookie_value);

                        if(!empty($bookingpress_entry_id)){
                            $bookingpress_entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_entry_id = %d",$bookingpress_entry_id ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm

                            if(!empty($bookingpress_entry_details['bookingpress_appointment_booking_id'])){
                                $appointment_id = $bookingpress_entry_details['bookingpress_appointment_booking_id'];
                            }
                        }
                    }
                }    
            }

            $appointment_data = array();

            $bookingpress_nonce_val = !empty($_GET['bp_tp_nonce']) ? sanitize_text_field($_GET['bp_tp_nonce']) : '';
            $bookingpress_verification_hash = !empty($_GET['appointment_id']) ? md5(base64_decode(sanitize_text_field($_GET['appointment_id']))) : '';
            $bookingpress_nonce_verification = wp_verify_nonce($bookingpress_nonce_val, 'bpa_nonce_url-'.$bookingpress_verification_hash);

            if (empty($appointment_id) && !empty($_GET['appointment_id']) && $bookingpress_nonce_verification) {
                $appointment_id = intval(base64_decode($_GET['appointment_id'])); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_GET['appointment_id'] sanitized properly

                
                $bookingpress_entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = %d",$appointment_id ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
                    
                if (! empty($bookingpress_entry_details) ) {
                    $bookingpress_service_id         = $bookingpress_entry_details['bookingpress_service_id'];
                    $bookingpress_appointment_date   = $bookingpress_entry_details['bookingpress_appointment_date'];
                    $bookingpress_appointment_time   = $bookingpress_entry_details['bookingpress_appointment_time'];
                    $bookingpress_appointment_status = $bookingpress_entry_details['bookingpress_appointment_status'];

                    
                    //$appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_service_id = %d AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_status = %s", $bookingpress_service_id, $bookingpress_appointment_date, $bookingpress_appointment_time, $bookingpress_appointment_status ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

                    $bookingpress_entry_id = $appointment_id;
                    $appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_entry_id = %d", $bookingpress_entry_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                    
                    if (empty($appointment_data) ) {
                        // If no appointment data found then display data from entries table.
                        $appointment_data = $bookingpress_entry_details;
                    }
                }
            } else {
                $appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d",$appointment_id ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
            }

            $appointment_data = apply_filters('bookingpress_modify_service_shortcode_details', $appointment_data, $appointment_id);

            $content .= '<div class="bookingpress_service_shortcode_container">';
            if (! empty($appointment_data) ) {
                if(empty($appointment_data['bookingpress_service_name'])){
                    foreach($appointment_data as $appointment_data_key => $appointment_data_val){
                        $content .= "<div class='bookingpress_service_name_div'>";
                        $content .= "<span class='bookingpress_service_name'>" . stripslashes_deep(esc_html($appointment_data_val['bookingpress_service_name'])) . '</span>';
                        $content .= '</div><br/>';
                    }
                }else{
                    $content .= "<div class='bookingpress_service_name_div'>";
                    $content .= "<span class='bookingpress_service_name'>" . stripslashes_deep(esc_html($appointment_data['bookingpress_service_name'])) . '</span>';
                    $content .= '</div>';
                }
            }
            $content .= '</div>';

            return do_shortcode($content);
        }
        
        /**
         * Callback function of [bookingpress_appointment_datetime] shortcode
         *
         * @param  mixed $atts
         * @param  mixed $content
         * @param  mixed $tag
         * @return void
         */
        function bookingpress_appointment_datetime_func( $atts, $content, $tag )
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries,$bookingpress_global_options;
            $BookingPress->set_front_css(1);
            $BookingPress->set_front_js(1);
            $BookingPress->bookingpress_load_booking_form_custom_css();

            $bookingpress_short_atts = array(
            'appointment_id' => 0,
            );

            $atts           = shortcode_atts($bookingpress_short_atts, $atts, $tag);
            $appointment_id = $atts['appointment_id'];

            $bookingpress_uniq_id = !empty($_POST['bookingpress_uniq_id']) ? sanitize_text_field($_POST['bookingpress_uniq_id']) : '';
            if(!empty($bookingpress_uniq_id)){
                if(!empty($_COOKIE['bookingpress_cart_id'])) {
                    $appointment_id = base64_decode($_COOKIE['bookingpress_cart_id']); //phpcs:ignore
                } else {
                    $bookingpress_cookie_name = $bookingpress_uniq_id."_appointment_data";
                    if(!empty($_COOKIE[$bookingpress_cookie_name])){
                        $bookingpress_cookie_value = sanitize_text_field($_COOKIE[$bookingpress_cookie_name]);
                        $bookingpress_entry_id = base64_decode($bookingpress_cookie_value);

                        if(!empty($bookingpress_entry_id)){
                            $bookingpress_entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_entry_id = %d",$bookingpress_entry_id ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm

                            if(!empty($bookingpress_entry_details['bookingpress_appointment_booking_id'])){
                                $appointment_id = $bookingpress_entry_details['bookingpress_appointment_booking_id'];
                            }
                        }
                    }
                }    
            }

            $appointment_data = array();

            $bookingpress_nonce_val = !empty($_GET['bp_tp_nonce']) ? sanitize_text_field($_GET['bp_tp_nonce']) : '';
            $bookingpress_verification_hash = !empty($_GET['appointment_id']) ? md5(base64_decode(sanitize_text_field($_GET['appointment_id']))) : '';
            $bookingpress_nonce_verification = wp_verify_nonce($bookingpress_nonce_val, 'bpa_nonce_url-'.$bookingpress_verification_hash);

            if (empty($appointment_id) && ! empty($_GET['appointment_id']) && $bookingpress_nonce_verification ) {
                $appointment_id = intval(base64_decode($_GET['appointment_id']));// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_GET['appointment_id'] sanitized properly
                
                $bookingpress_entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = %d",$appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
                
                if (! empty($bookingpress_entry_details) ) {
                    $bookingpress_service_id         = $bookingpress_entry_details['bookingpress_service_id'];
                    $bookingpress_appointment_date   = $bookingpress_entry_details['bookingpress_appointment_date'];
                    $bookingpress_appointment_time   = $bookingpress_entry_details['bookingpress_appointment_time'];
                    $bookingpress_appointment_status = $bookingpress_entry_details['bookingpress_appointment_status'];

                    //$appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_service_id = %d AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_status = %s", $bookingpress_service_id, $bookingpress_appointment_date, $bookingpress_appointment_time, $bookingpress_appointment_status ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

                    $bookingpress_entry_id = $appointment_id;
                    $appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_entry_id = %d", $bookingpress_entry_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                    
                    if (empty($appointment_data) ) {
                        // If no appointment data found then display data from entries table.
                        $appointment_data = $bookingpress_entry_details;
                    }
                }
            } else {
                $appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d",$appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
            }

            $appointment_data = apply_filters('bookingpress_modify_datetime_shortcode_data', $appointment_data, $appointment_id);

            $content .= '<div class="bookinpress-datetime-container">';
            if (! empty($appointment_data) ) {
                $bookingpress_global_options_arr       = $bookingpress_global_options->bookingpress_global_options();
                $bookingpress_default_date_time_format = $bookingpress_global_options_arr['wp_default_date_format'] . ' ' . $bookingpress_global_options_arr['wp_default_time_format'];

                if(empty($appointment_data['bookingpress_appointment_date'])){
                    foreach($appointment_data as $appointment_data_key => $appointment_data_val){
                        $content .= "<div class='bookingpress_appointment_datetime_div'>";
                        $booked_appointment_datetime = esc_html($appointment_data_val['bookingpress_appointment_date']) . ' ' . esc_html($appointment_data_val['bookingpress_appointment_time']);

                        if(empty($bookingpress_entry_details['bookingpress_customer_timezone'])){
                            $bookingpress_entry_id = !empty($appointment_data_val['bookingpress_entry_id']) ? intval($appointment_data_val['bookingpress_entry_id']) : 0;
                            if(!empty($bookingpress_entry_id)){

                                //Get entries details
                                $bookingpress_entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = %d",$bookingpress_entry_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
                            }
                        }
                
                        $booked_appointment_datetime = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booked_appointment_datetime, $bookingpress_entry_details['bookingpress_customer_timezone'], $bookingpress_entry_details );
                        
                        $booked_appointment_date = date($bookingpress_default_date_time_format, strtotime($booked_appointment_datetime));
                        
                        $content .= "<span class='bookingpress_appointment_datetime'>" . $booked_appointment_date . '</span>';
                        $content .= '</div><br/>';
                    }
                }else{
                    $booked_appointment_datetime = esc_html($appointment_data['bookingpress_appointment_date']) . ' ' . esc_html($appointment_data['bookingpress_appointment_time']);

                    if(empty($bookingpress_entry_details['bookingpress_customer_timezone'])){
                        $bookingpress_entry_id = !empty($appointment_data['bookingpress_entry_id']) ? intval($appointment_data['bookingpress_entry_id']) : 0;
                        if(!empty($bookingpress_entry_id)){

                            //Get entries details
                            $bookingpress_entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = %d",$bookingpress_entry_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
                        }
                    }
                    
                    $booked_appointment_datetime = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booked_appointment_datetime, $bookingpress_entry_details['bookingpress_customer_timezone'], $bookingpress_entry_details );
                    
                    $booked_appointment_date = date($bookingpress_default_date_time_format, strtotime($booked_appointment_datetime));
                    $content .= "<div class='bookingpress_appointment_datetime_div'>";
                    $content .= "<span class='bookingpress_appointment_datetime'>" . $booked_appointment_date . '</span>';
                    $content .= '</div>';
                }
            }
            $content .= '</div>';

            return do_shortcode($content);
        }
        
        /**
         * Callback function of [booking_id] shortcode
         *
         * @param  mixed $atts
         * @param  mixed $content
         * @param  mixed $tag
         * @return void
         */
        function bookingpress_booking_id_func($atts, $content, $tag){
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries, $tbl_bookingpress_payment_logs;
            $BookingPress->set_front_css(1);
            $BookingPress->set_front_js(1);
            $BookingPress->bookingpress_load_booking_form_custom_css();

            $bookingpress_short_atts = array(
                'appointment_id' => 0,
            );

            $atts           = shortcode_atts($bookingpress_short_atts, $atts, $tag);
            $appointment_id = $atts['appointment_id'];

            $bookingpress_uniq_id = !empty($_POST['bookingpress_uniq_id']) ? sanitize_text_field($_POST['bookingpress_uniq_id']) : '';
            if(!empty($bookingpress_uniq_id)){
                if(!empty($_COOKIE['bookingpress_cart_id'])) {
                    $appointment_id = base64_decode($_COOKIE['bookingpress_cart_id']); //phpcs:ignore
                } else {
                    $bookingpress_cookie_name = $bookingpress_uniq_id."_appointment_data";
                    if(!empty($_COOKIE[$bookingpress_cookie_name])){
                        $bookingpress_cookie_value = sanitize_text_field($_COOKIE[$bookingpress_cookie_name]);
                        $bookingpress_entry_id = base64_decode($bookingpress_cookie_value);

                        if(!empty($bookingpress_entry_id)){
                            $bookingpress_entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_entry_id = %d",$bookingpress_entry_id ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm

                            if(!empty($bookingpress_entry_details['bookingpress_appointment_booking_id'])){
                                $appointment_id = $bookingpress_entry_details['bookingpress_appointment_booking_id'];
                            }
                        }
                    }
                }    
            }

            if(empty($appointment_id) && !empty($_GET['appointment_id']) ){
                $appointment_id = intval(base64_decode($_GET['appointment_id']));// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_GET['appointment_id'] sanitized properly

                $bookingpress_nonce_val = !empty($_GET['bp_tp_nonce']) ? sanitize_text_field($_GET['bp_tp_nonce']) : '';
                $bookingpress_verification_hash = !empty($_GET['appointment_id']) ? md5(base64_decode(sanitize_text_field($_GET['appointment_id']))) : '';
                $bookingpress_nonce_verification = wp_verify_nonce($bookingpress_nonce_val, 'bpa_nonce_url-'.$bookingpress_verification_hash);

                if(!$bookingpress_nonce_verification){
                    return do_shortcode($content);
                }
            }

            $appointment_data  = array();
            $bookingpress_booking_id = '';
            if(!empty($appointment_id)){
                //Get appointment details
                $bookingpress_appointment_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                
                $bookingpress_booking_id = !empty($bookingpress_appointment_details['bookingpress_booking_id']) ? $bookingpress_appointment_details['bookingpress_booking_id'] : '';
            }

            $bookingpress_booking_id = apply_filters('bookingpress_modify_booking_id_shortcode_data', $bookingpress_booking_id, $appointment_id);

            if(!empty($bookingpress_booking_id)){
                $content .= '#'.$bookingpress_booking_id;
            }
            return do_shortcode($content);
        }
        
        /**
         * Callback function of [bookingpress_appointment_customername] shortcode
         *
         * @param  mixed $atts
         * @param  mixed $content
         * @param  mixed $tag
         * @return void
         */
        function bookingpress_appointment_customername_func( $atts, $content, $tag )
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries;
            $BookingPress->set_front_css(1);
            $BookingPress->set_front_js(1);
            $BookingPress->bookingpress_load_booking_form_custom_css();

            $bookingpress_short_atts = array(
            'appointment_id' => 0,
            );

            $atts           = shortcode_atts($bookingpress_short_atts, $atts, $tag);
            $appointment_id = $atts['appointment_id'];

            $bookingpress_uniq_id = !empty($_POST['bookingpress_uniq_id']) ? sanitize_text_field($_POST['bookingpress_uniq_id']) : '';

            if(!empty($bookingpress_uniq_id)){
                if(!empty($_COOKIE['bookingpress_cart_id'])) {
                    $appointment_id = base64_decode($_COOKIE['bookingpress_cart_id']); //phpcs:ignore
                } else {
                    $bookingpress_cookie_name = $bookingpress_uniq_id."_appointment_data";
                    if(!empty($_COOKIE[$bookingpress_cookie_name])){
                        $bookingpress_cookie_value = sanitize_text_field($_COOKIE[$bookingpress_cookie_name]);
                        $bookingpress_entry_id = base64_decode($bookingpress_cookie_value);
                        if(!empty($bookingpress_entry_id)){
                            $bookingpress_entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_entry_id = %d",$bookingpress_entry_id ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm

                            if(!empty($bookingpress_entry_details['bookingpress_appointment_booking_id'])){
                                $appointment_id = $bookingpress_entry_details['bookingpress_appointment_booking_id'];
                            }
                        }
                    }
                }
            }            

            $appointment_data  = array();

            $bookingpress_nonce_val = !empty($_GET['bp_tp_nonce']) ? sanitize_text_field($_GET['bp_tp_nonce']) : '';
            $bookingpress_verification_hash = !empty($_GET['appointment_id']) ? md5(base64_decode(sanitize_text_field($_GET['appointment_id']))) : '';
            $bookingpress_nonce_verification = wp_verify_nonce($bookingpress_nonce_val, 'bpa_nonce_url-'.$bookingpress_verification_hash);

            $customer_fullname = '';
            if (empty($appointment_id) && ! empty($_GET['appointment_id']) && $bookingpress_nonce_verification ) {
                $appointment_id = intval(base64_decode($_GET['appointment_id']));// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_GET['appointment_id'] sanitized properly
                $bookingpress_entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = %d",$appointment_id ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm

                if (! empty($bookingpress_entry_details) ) {
                    $bookingpress_service_id         = $bookingpress_entry_details['bookingpress_service_id'];
                    $bookingpress_appointment_date   = $bookingpress_entry_details['bookingpress_appointment_date'];
                    $bookingpress_appointment_time   = $bookingpress_entry_details['bookingpress_appointment_time'];
                    $bookingpress_appointment_status = $bookingpress_entry_details['bookingpress_appointment_status'];

                    //$appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_service_id = %d AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_status = %s", $bookingpress_service_id, $bookingpress_appointment_date, $bookingpress_appointment_time, $bookingpress_appointment_status ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

                    $bookingpress_entry_id = $appointment_id;
                    $appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_entry_id = %d", $bookingpress_entry_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                    
                    if (empty($appointment_data) ) {
                        // If no data found from appointments then display data from entries table.
                        $appointment_data = $bookingpress_entry_details;
                    }
                }
            } else {
                $appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d",$appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
            }

            $appointment_data = apply_filters('bookingpress_modify_customer_details_shortcode_data', $appointment_data, $appointment_id);

            $content .= "<div class='bookingpress-appointment-customer-container'>";
            if (! empty($appointment_data) ) {

                $customer_firstname = ! empty($appointment_data['bookingpress_customer_firstname']) ? $appointment_data['bookingpress_customer_firstname'] : '';
                $customer_lastname  = ! empty($appointment_data['bookingpress_customer_lastname']) ? $appointment_data['bookingpress_customer_lastname'] : '';
                $customer_email     = ! empty($appointment_data['bookingpress_customer_email']) ? $appointment_data['bookingpress_customer_email'] : '';                
                $customer_fullname = !empty($appointment_data['bookingpress_customer_name']) ? $appointment_data['bookingpress_customer_name'] : ($customer_firstname . ' ' . $customer_lastname);
                if(empty($appointment_data['bookingpress_customer_name']) && empty($customer_firstname) && empty($customer_lastname) ) {
                    $customer_fullname = $customer_email;
                }
                $content .= "<div class='bookingpress_appointment_customername_div'>";
                $content .= "<span class='bookingpress_appointment_customername'>" . stripslashes_deep(esc_html($customer_fullname)) . '</span>';
                $content .= '</div>';
            }
            $content .= "</div>";

            return do_shortcode($content);
        }
                
        /**
         * Callback function of [bookingpress_company_avatar] shortcode
         *
         * @return void
         */
        function bookingpress_company_avatar_func()
        {
            global $BookingPress;
            $BookingPress->set_front_css(1);
            $BookingPress->set_front_js(1);
            $BookingPress->bookingpress_load_booking_form_custom_css();

            $content                         = '';
            $bookingpress_company_avatar_url = $BookingPress->bookingpress_get_settings('company_avatar_url', 'company_setting');
            if ($bookingpress_company_avatar_url != '' ) {
                $bookingpress_company_avatar_url = esc_url($bookingpress_company_avatar_url);
                $content                         = '<img src=' . $bookingpress_company_avatar_url . ' width=100 height=100 >';
            } else {
                $content = esc_html_e('Company avatar not found', 'bookingpress-appointment-booking');
            }
            return do_shortcode($content);
        }
                
        /**
         * Callback function of [bookingpress_company_name] shortcode
         *
         * @return void
         */
        function bookingpress_company_name_func()
        {
            global $BookingPress;
            $BookingPress->set_front_css(1);
            $BookingPress->set_front_js(1);
            $BookingPress->bookingpress_load_booking_form_custom_css();

            $content                   = '';
            $bookingpress_company_name = $BookingPress->bookingpress_get_settings('company_name', 'company_setting');
            if ($bookingpress_company_name != '' ) {
                $content = esc_html($bookingpress_company_name);
            } else {
                $content = esc_html_e('Company name not found', 'bookingpress-appointment-booking');
            }
            return do_shortcode($content);
        }
                
        /**
         * Callback function of [bookingpress_company_website] shortcode
         *
         * @return void
         */
        function bookingpress_company_website_func()
        {
            global $BookingPress;
            $BookingPress->set_front_css(1);
            $BookingPress->set_front_js(1);
            $BookingPress->bookingpress_load_booking_form_custom_css();

            $content                      = '';
            $bookingpress_company_website = $BookingPress->bookingpress_get_settings('company_website', 'company_setting');
            if ($bookingpress_company_website != '' ) {
                $content = esc_html($bookingpress_company_website);
            } else {
                $content = esc_html_e('Company website name not found', 'bookingpress-appointment-booking');
            }
            return do_shortcode($content);
        }

        
        /**
         * Callback function of [bookingpress_company_address] shortcode
         *
         * @return void
         */
        function bookingpress_company_address_func()
        {
            global $BookingPress;
            $BookingPress->set_front_css(1);
            $BookingPress->set_front_js(1);
            $BookingPress->bookingpress_load_booking_form_custom_css();

            $content                      = '';
            $bookingpress_company_address = $BookingPress->bookingpress_get_settings('company_address', 'company_setting');
            if ($bookingpress_company_address != '' ) {
                $content = esc_html($bookingpress_company_address);
            } else {
                $content = esc_html_e('Company address not found', 'bookingpress-appointment-booking');
            }
            return do_shortcode($content);
        }
                
        /**
         * Callback function of [bookingpress_company_phone] shortcode
         *
         * @return void
         */
        function bookingpress_company_phone_func()
        {
            global $BookingPress;
            $BookingPress->set_front_css(1);
            $BookingPress->set_front_js(1);
            $BookingPress->bookingpress_load_booking_form_custom_css();

            $content                      = '';
            $bookingpress_company_phone   = $BookingPress->bookingpress_get_settings('company_phone_number', 'company_setting');
            $bookingpress_company_country = $BookingPress->bookingpress_get_settings('company_phone_country', 'company_setting');

            if ($bookingpress_company_phone != '' ) {
                $content = esc_html($bookingpress_company_phone);
            } else {
                $content = esc_html_e('Company phone number not found', 'bookingpress-appointment-booking');
            }
            return do_shortcode($content);
        }
 
        /**
         * Function for add/update appointment
         *
         * @return void
         */
        function bookingpress_save_appointment_booking_func()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_services, $tbl_bookingpress_customer_bookings, $tbl_bookingpress_customers, $bookingpress_payment_gateways, $bookingpress_debug_payment_log_id;
            $response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                wp_send_json($response);
                die();
            }
            $response['variant']       = 'error';
            $response['title']         = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']           = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
            $response['is_redirect']   = 0;
            $response['redirect_data'] = '';
            $response['is_spam']       = 1;

            if( !empty( $_REQUEST['appointment_data'] ) && !is_array( $_REQUEST['appointment_data'] ) ){
                $_REQUEST['appointment_data'] = json_decode( stripslashes_deep( $_REQUEST['appointment_data'] ), true ); //phpcs:ignore
                $_REQUEST['appointment_data'] =  !empty($_REQUEST['appointment_data']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_REQUEST['appointment_data'] ) : array(); // phpcs:ignore                
            }        

            $response = apply_filters('bookingpress_validate_spam_protection', $response, array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['appointment_data'])); // phpcs:ignore

            $appointment_booked_successfully = $BookingPress->bookingpress_get_settings('appointment_booked_successfully', 'message_setting');

            if (! empty($_REQUEST) && ! empty($_REQUEST['appointment_data']) ) {
             
                $bookingpress_appointment_data            = array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['appointment_data']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_GET['appointment_data'] contains mixed array and sanitized properly using 'appointment_sanatize_field' function
                $bookingpress_payment_gateway             = ! empty($bookingpress_appointment_data['selected_payment_method']) ? $bookingpress_appointment_data['selected_payment_method'] : '';
                $bookingpress_appointment_on_site_enabled = ( $bookingpress_appointment_data['selected_payment_method'] == 'onsite' ) ? 1 : 0;
                $payment_gateway                          = ( $bookingpress_appointment_on_site_enabled ) ? 'on-site' : $bookingpress_payment_gateway;

                $bookingpress_service_price = isset($bookingpress_appointment_data['service_price_without_currency']) ? floatval($bookingpress_appointment_data['service_price_without_currency']) : 0;
                if ($bookingpress_service_price == 0 ) {
                    $payment_gateway = ' - ';
                }

                $bookingpress_return_data = apply_filters('bookingpress_validate_submitted_form', $payment_gateway, $bookingpress_appointment_data);

                if ($payment_gateway == 'on-site' && $bookingpress_service_price > 0 ) {
                    $entry_id = ! empty($bookingpress_return_data['entry_id']) ? $bookingpress_return_data['entry_id'] : 0;
                    $bookingpress_appointment_status = $BookingPress->bookingpress_get_settings('onsite_appointment_status', 'general_setting');

                    if($bookingpress_appointment_status ==  '1' ) {               
                        $bookingpress_payment_gateways->bookingpress_confirm_booking($entry_id, array(), '1', '', '', 1);
                        $bookingpress_redirect_url = $bookingpress_return_data['approved_appointment_url'];
                    } else {                    
                        $bookingpress_payment_gateways->bookingpress_confirm_booking($entry_id, array(), '2', '', '', 1);
                        $bookingpress_redirect_url = $bookingpress_return_data['pending_appointment_url'];
                    }
                    if (! empty($bookingpress_redirect_url) ) {
                        $response['variant']       = 'redirect_url';
                        $response['title']         = '';
                        $response['msg']           = '';
                        $response['is_redirect']   = 1;
                        $response['redirect_data'] = $bookingpress_redirect_url;
                    } else {
                        $response['variant'] = 'success';
                        $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                        $response['msg']     = esc_html($appointment_booked_successfully);
                    }
                } elseif ($bookingpress_service_price == 0 ) {
                    $entry_id = ! empty($bookingpress_return_data['entry_id']) ? $bookingpress_return_data['entry_id'] : 0;
                    $bookingpress_payment_gateways->bookingpress_confirm_booking($entry_id, array(), '1', '', '', 1);

                    $redirect_url                    = $bookingpress_return_data['approved_appointment_url'];
                    $bookingpress_appointment_status = $BookingPress->bookingpress_get_settings('appointment_status', 'general_setting');
                    if ($bookingpress_appointment_status == 'Pending' ) {
                        $redirect_url = $bookingpress_return_data['pending_appointment_url'];
                    }

                    $bookingpress_redirect_url = $redirect_url;
                    if (! empty($bookingpress_redirect_url) ) {
                        $response['variant']       = 'redirect_url';
                        $response['title']         = '';
                        $response['msg']           = '';
                        $response['is_redirect']   = 1;
                        $response['redirect_data'] = $bookingpress_redirect_url;
                    } else {
                        $response['variant'] = 'success';
                        $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                        $response['msg']     = esc_html($appointment_booked_successfully);
                    }
                } else {
                    if ($payment_gateway == 'paypal' ) {
                        $bookingpress_payment_mode    = $BookingPress->bookingpress_get_settings('paypal_payment_mode', 'payment_setting');
                        $bookingpress_is_sandbox_mode = ( $bookingpress_payment_mode != 'live' ) ? true : false;
                        $bookingpress_gateway_status  = $BookingPress->bookingpress_get_settings('paypal_payment', 'payment_setting');
                        $bookingpress_merchant_email  = $BookingPress->bookingpress_get_settings('paypal_merchant_email', 'payment_setting');
                        $bookingpress_api_username    = $BookingPress->bookingpress_get_settings('paypal_api_username', 'payment_setting');
                        $bookingpress_api_password    = $BookingPress->bookingpress_get_settings('paypal_api_password', 'payment_setting');
                        $bookingpress_api_signature   = $BookingPress->bookingpress_get_settings('paypal_api_signature', 'payment_setting');

                        $bookingpress_paypal_error_msg  = esc_html__('PayPal Configuration Error', 'bookingpress-appointment-booking');
                        $bookingpress_paypal_error_msg .= ': ';
                        if (empty($bookingpress_merchant_email) ) {
                               $bookingpress_paypal_error_msg .= esc_html__('Please configure merchant email address', 'bookingpress-appointment-booking');

                               $response['variant']       = 'error';
                               $response['title']         = esc_html__('Error', 'bookingpress-appointment-booking');
                               $response['msg']           = $bookingpress_paypal_error_msg;
                               $response['is_redirect']   = 0;
                               $response['redirect_data'] = '';
                               $response['is_spam']       = 0;

                               echo json_encode($response);
                               exit;
                        }

                        if (empty($bookingpress_api_username) ) {
                            $bookingpress_paypal_error_msg .= esc_html__('Please configure PayPal API Username', 'bookingpress-appointment-booking');

                            $response['variant']       = 'error';
                            $response['title']         = esc_html__('Error', 'bookingpress-appointment-booking');
                            $response['msg']           = $bookingpress_paypal_error_msg;
                            $response['is_redirect']   = 0;
                            $response['redirect_data'] = '';
                            $response['is_spam']       = 0;

                            echo json_encode($response);
                            exit;
                        }

                        if (empty($bookingpress_api_password) ) {
                            $bookingpress_paypal_error_msg .= esc_html__('Please configure PayPal API Password', 'bookingpress-appointment-booking');

                            $response['variant']       = 'error';
                            $response['title']         = esc_html__('Error', 'bookingpress-appointment-booking');
                            $response['msg']           = $bookingpress_paypal_error_msg;
                            $response['is_redirect']   = 0;
                            $response['redirect_data'] = '';
                            $response['is_spam']       = 0;

                            echo json_encode($response);
                            exit;
                        }

                        if (empty($bookingpress_api_signature) ) {
                            $bookingpress_paypal_error_msg .= esc_html__('Please configure PayPal API Signature', 'bookingpress-appointment-booking');

                            $response['variant']       = 'error';
                            $response['title']         = esc_html__('Error', 'bookingpress-appointment-booking');
                            $response['msg']           = $bookingpress_paypal_error_msg;
                            $response['is_redirect']   = 0;
                            $response['redirect_data'] = '';
                            $response['is_spam']       = 0;

                            echo json_encode($response);
                            exit;
                        }

                        $entry_id                          = $bookingpress_return_data['entry_id'];
                        $currency                          = $bookingpress_return_data['currency'];
                        $currency_symbol                   = $BookingPress->bookingpress_get_currency_code($currency);
                        $bookingpress_final_payable_amount = isset($bookingpress_return_data['payable_amount']) ? $bookingpress_return_data['payable_amount'] : 0;
                        $customer_details                  = $bookingpress_return_data['customer_details'];
                        $customer_email                    = ! empty($customer_details['customer_email']) ? $customer_details['customer_email'] : '';

                        $bookingpress_service_name = ! empty($bookingpress_return_data['service_data']['bookingpress_service_name']) ? $bookingpress_return_data['service_data']['bookingpress_service_name'] : __('Appointment Booking', 'bookingpress-appointment-booking');

                        $custom_var = $entry_id;

                        $sandbox = $bookingpress_is_sandbox_mode ? 'sandbox.' : '';

                        $notify_url = $bookingpress_return_data['notify_url'];

                        $redirect_url                    = $bookingpress_return_data['approved_appointment_url'];
                        $bookingpress_appointment_status = $BookingPress->bookingpress_get_settings('appointment_status', 'general_setting');
                        if ($bookingpress_appointment_status == 'Pending' ) {
                            $redirect_url = $bookingpress_return_data['pending_appointment_url'];
                        }

                        $bookingpress_paypal_cancel_url_id = $BookingPress->bookingpress_get_customize_settings('after_failed_payment_redirection', 'booking_form');
                        $bookingpress_paypal_cancel_url = get_permalink($bookingpress_paypal_cancel_url_id);
                        $cancel_url                     = ! empty($bookingpress_paypal_cancel_url) ? $bookingpress_paypal_cancel_url : BOOKINGPRESS_HOME_URL;
                        $cancel_url                     = add_query_arg('is_cancel', 1, esc_url($cancel_url));

                        $cmd          = '_xclick';
                        $paypal_form  = '<form name="_xclick" id="bookingpress_paypal_form" action="https://www.' . $sandbox . 'paypal.com/cgi-bin/webscr" method="post">';
                        $paypal_form .= '<input type="hidden" name="cmd" value="' . $cmd . '" />';
                        $paypal_form .= '<input type="hidden" name="amount" value="' . $bookingpress_final_payable_amount . '" />';
                        $paypal_form .= '<input type="hidden" name="business" value="' . $bookingpress_merchant_email . '" />';
                        $paypal_form .= '<input type="hidden" name="notify_url" value="' . $notify_url . '" />';
                        $paypal_form .= '<input type="hidden" name="cancel_return" value="' . $cancel_url . '" />';
                        $paypal_form .= '<input type="hidden" name="return" value="' . $redirect_url . '" />';
                        $paypal_form .= '<input type="hidden" name="rm" value="2" />';
                        $paypal_form .= '<input type="hidden" name="lc" value="en_US" />';
                        $paypal_form .= '<input type="hidden" name="no_shipping" value="1" />';
                        $paypal_form .= '<input type="hidden" name="custom" value="' . $custom_var . '" />';
                        $paypal_form .= '<input type="hidden" name="on0" value="user_email" />';
                        $paypal_form .= '<input type="hidden" name="os0" value="' . $customer_email . '" />';
                        $paypal_form .= '<input type="hidden" name="currency_code" value="' . $currency_symbol . '" />';
                        $paypal_form .= '<input type="hidden" name="page_style" value="primary" />';
                        $paypal_form .= '<input type="hidden" name="charset" value="UTF-8" />';
                        $paypal_form .= '<input type="hidden" name="item_name" value="' . $bookingpress_service_name . '" />';
                        $paypal_form .= '<input type="hidden" name="item_number" value="1" />';
                        $paypal_form .= '<input type="submit" value="Pay with PayPal!" />';
                        $paypal_form .= '</form>';

                        do_action('bookingpress_payment_log_entry', 'paypal', 'payment form redirected data', 'bookingpress', $paypal_form, $bookingpress_debug_payment_log_id);

                        $paypal_form .= '<script type="text/javascript">document.getElementById("bookingpress_paypal_form").submit();</script>';

                        $response['variant']       = 'redirect';
                        $response['title']         = '';
                        $response['msg']           = '';
                        $response['is_redirect']   = 1;
                        $response['redirect_data'] = $paypal_form;
                        $response['entry_id']      = $entry_id;
                    }
                }
            }

            echo json_encode($response);
            exit();
        }

                
        /**
         * Function for retrieve booking time slots
         *
         * @param  mixed $selected_date          Pass selected date
         * @param  mixed $return                 If paramter set to true then timeslots data will return
         * @param  mixed $check_for_whole_days   Time should be check for whole day or not
         * @return void
         */
        function bookingpress_retrieve_timeslots( $selected_date = '' , $return = false, $check_for_whole_days = false ){
            
            global $wpdb, $BookingPress, $tbl_bookingpress_services, $bookingpress_global_options, $bookingpress_other_debug_log_id, $tbl_bookingpress_appointment_bookings, $bookingpress_services, $bookingpress_other_debug_log_id;

            $response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                echo json_encode($response);
                exit();
            }
            if(!empty($_POST['appointment_data_obj']) && !is_array($_POST['appointment_data_obj'])) {
                $_POST['appointment_data_obj'] = !empty( $_POST['appointment_data_obj'] ) ? json_decode( stripslashes_deep( $_POST['appointment_data_obj'] ), true ) : array(); //phpcs:ignore
				$_POST['appointment_data_obj'] =  !empty($_POST['appointment_data_obj']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_POST['appointment_data_obj'] ) : array(); // phpcs:ignore
            }

            $selected_service_id = ! empty($_POST['service_id']) ? intval($_POST['service_id']) : 0;
            if( empty( $selected_date ) ){
                $selected_date       = ! empty($_POST['selected_date']) ? date('Y-m-d', strtotime(sanitize_text_field($_POST['selected_date']))) : date('Y-m-d',current_time('timestamp'));
            }
            $service_timings = array();

            $service_timings_data = array(
                'is_daysoff' => false,
                'service_timings' => array()
            );

            /** filter to check minimum time requirement */
            $minimum_time_required = 'disabled';
            $minimum_time_required = apply_filters( 'bookingpress_retrieve_minimum_required_time', $minimum_time_required, $selected_service_id );

            /** Check for the available capacity */
            $max_service_capacity = 1;
            $max_service_capacity = apply_filters( 'bookingpress_retrieve_capacity', $max_service_capacity, $selected_service_id );
            
            /** timeslot steps settings */
            $bookingpress_show_time_as_per_service_duration = $BookingPress->bookingpress_get_settings( 'show_time_as_per_service_duration', 'general_setting' );
            $bookingpress_shared_service_timeslot = $BookingPress->bookingpress_get_settings('share_timeslot_between_services', 'general_setting');
            /** total booked appointment of the selected date */
            $where_clause = '';
            if( 'true' != $bookingpress_shared_service_timeslot ){
                $where_clause = $wpdb->prepare( ' AND bookingpress_service_id = %d ', $selected_service_id );
                $where_clause = apply_filters( 'bookingpress_booked_appointment_where_clause', $where_clause );
            }

            $where_clause .= $wpdb->prepare( ' AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s)', '1', '2' );

            $bookingpress_hide_already_booked_slot = $BookingPress->bookingpress_get_customize_settings( 'hide_already_booked_slot', 'booking_form' );
            $bookingpress_hide_already_booked_slot = ( $bookingpress_hide_already_booked_slot == 'true' ) ? 1 : 0;
                

            $bpa_appointment_edit_id = !empty( $_POST['appointment_data_obj']['appointment_update_id'] ) ? intval( $_POST['appointment_data_obj']['appointment_update_id'] ) : 0;

            if( !empty( $bpa_appointment_edit_id ) ){
                $where_clause .= $wpdb->prepare( ' AND bookingpress_appointment_booking_id != %d', $bpa_appointment_edit_id );
            }

            $total_booked_appiontments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date = %s $where_clause", $selected_date), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

            $shared_quantity = apply_filters('bookingpress_get_shared_capacity_data', 'true' );

            /** service buffer times ends */

            /** retrieving staff member and service time slots */
            $service_timings_data = apply_filters('bookingpress_retrieve_pro_modules_timeslots', $service_timings_data, $selected_service_id, $selected_date, $minimum_time_required, $max_service_capacity, $bookingpress_show_time_as_per_service_duration );
            
            if( empty( $service_timings_data['service_timings'] ) && false == $service_timings_data['is_daysoff'] ){
                $service_timings = $BookingPress->bookinpgress_retrieve_default_workhours($selected_service_id, $selected_date, $minimum_time_required, $max_service_capacity, $bookingpress_show_time_as_per_service_duration);
            } else {
                $service_timings = $service_timings_data['service_timings'];
            }


            $total_booked_appiontments = apply_filters( 'bookingpress_modify_booked_appointment_data', $total_booked_appiontments, $selected_date, $service_timings, $selected_service_id );

            /** Remove Booked Time Slots from the final service timings - start */
            
            if( !empty( $total_booked_appiontments ) && !empty( $service_timings ) ){
                
                
                foreach( $total_booked_appiontments as $booked_appointment_data ){
                    $total_guests = 0;
                    $booked_appointment_start_time = $booked_appointment_data['bookingpress_appointment_time'];
                    $booked_appointment_end_time = $booked_appointment_data['bookingpress_appointment_end_time'];
                    foreach( $service_timings as $sk => $time_slot_data ){
                        $current_time_start = $time_slot_data['store_start_time'].':00';
                        $current_time_end = $time_slot_data['store_end_time'].':00';
                        
                        if( ( $booked_appointment_start_time >= $current_time_start && $booked_appointment_end_time <= $current_time_end ) || ( $booked_appointment_start_time < $current_time_end && $booked_appointment_end_time > $current_time_start) ){

                            $service_timings[ $sk ]['total_booked']++;
                            $capacity_count = 1;
                            /** increase capacity count if booked appointment has the extra members */                            
                            
                            if( $BookingPress->bpa_is_pro_exists() && $BookingPress->bpa_is_pro_active() ){
                                if( !empty( $BookingPress->bpa_pro_plugin_version() ) && version_compare( $BookingPress->bpa_pro_plugin_version(), '1.5', '>' ) ){
                                    $booked_appointment_data['bookingpress_selected_extra_members'] = isset($booked_appointment_data['bookingpress_selected_extra_members']) ? intval($booked_appointment_data['bookingpress_selected_extra_members']) - 1 : 0;
                                }
                            }

                            if( isset($booked_appointment_data['bookingpress_selected_extra_members']) && $booked_appointment_data['bookingpress_selected_extra_members'] > 0 ){
                                $capacity_count += $booked_appointment_data['bookingpress_selected_extra_members'];
                                $total_guests += $booked_appointment_data['bookingpress_selected_extra_members'];
                                $service_timings[ $sk ]['guest_members'] = $total_guests;
                            } else {                                
                                $service_timings[ $sk ]['guest_members'] = $total_guests;
                            }
                            
                            if( 'true' == $shared_quantity ){
                                $service_timings[ $sk ]['max_capacity'] -= $capacity_count;
                                if( $service_timings[ $sk ]['max_capacity'] < 0 ){
                                    $service_timings[ $sk ]['max_capacity'] = 0;
                                }
                                $service_timings[ $sk ]['is_reduced_capacity'] = true;
                            } else {
                                if( $booked_appointment_start_time == $current_time_start && $booked_appointment_end_time == $current_time_end ){
                                    
                                    $service_timings[ $sk ]['max_capacity'] -= $capacity_count; // reduce capacity for exact time slot
                                    if( $service_timings[ $sk ]['max_capacity'] < 0 ){
                                        $service_timings[ $sk ]['max_capacity'] = 0;
                                    }

                                    if( 0 == $service_timings[ $sk ]['max_capacity'] ){
                                        $service_timings[ $sk ]['is_booked'] = 1;
                                    }
                                } else {
                                    /** Removed time slot for booking if the booked appointment's time slots are crossed between time slots and capacity is not sharing */
                                    unset( $service_timings[ $sk ] );
                                }
                            }

                            /** Filter to Check BAWY */
                            $service_timings = apply_filters( 'bookingpress_modify_timeslot_data_for_bawy', $service_timings, $sk );
                            
                            /** shared timeslot */
                            
                            if( 'true' == $bookingpress_shared_service_timeslot && !empty( $service_timings[$sk] )  ){
                                if( empty( $service_timings[ $sk ]['reason_for_not_available'] ) ){
                                    $service_timings[ $sk ]['reason_for_not_available'] = array( 'Due to shared time slot from ' . $booked_appointment_start_time . ' to  ' . $booked_appointment_end_time );
                                } else {
                                    $service_timings[ $sk ]['reason_for_not_available'][] = 'Due to shared time slot from ' . $booked_appointment_start_time . ' to  ' . $booked_appointment_end_time;
                                }
                                if( 0 == $service_timings[ $sk ]['max_capacity'] ){
                                    $service_timings[ $sk ]['is_booked'] = 1;
                                }
                            }
                            $service_timings[ $sk ]['is_booked_appointment'] = true;
                        }

                        $service_timings[ $sk ]['max_total_capacity'] = $max_service_capacity;

                        if(!isset( $service_timings[ $sk ]['store_start_time'] ) || !isset($service_timings[ $sk ]['store_end_time'])) { 
                            unset( $service_timings[ $sk ] );
                        }

                        /** setting up buffer times */
                        $service_timings = apply_filters( 'bookingpress_modify_service_time_with_buffer', $service_timings, $sk, $selected_service_id, $booked_appointment_start_time, $booked_appointment_end_time, $current_time_start, $current_time_end );

                    }
                }
            }

            /** Remove Booked Time Slots from the final service timings - end */
            
            $service_timings = apply_filters( 'bookingpress_check_available_timings_with_staffmember', $service_timings, $selected_service_id, $selected_date );

            $service_timings = array_values( $service_timings );

            if( true == $check_for_whole_days ){
                $is_available = false;
                foreach( $service_timings as $sk => $time_slot_data ){                   
                    if( !empty( $time_slot_data['guest_members'] ) ){
                        $time_slot_data['total_booked'] += $time_slot_data['guest_members'];
                    }
                    $current_time_start  = $time_slot_data['start_time'];
                    $current_time_end  = $time_slot_data['end_time'];
                    $service_max_capacity = $time_slot_data['max_capacity'];
                    $total_booked = $time_slot_data['total_booked'];
                    $is_booked = isset( $time_slot_data['is_booked'] ) ? $time_slot_data['is_booked'] : false;
                    
                    if( !$is_booked && $total_booked < $service_max_capacity ){
                        $is_available = true;
                        break;
                    }
                }
                return $is_available;
            }

            $bookingpress_global_details = $bookingpress_global_options->bookingpress_global_options();
            $bpa_wp_default_time_format  = $bookingpress_global_details['wp_default_time_format'];
            $bpa_wp_default_time_format = apply_filters('bookingpress_change_time_slot_format',$bpa_wp_default_time_format);

            if(session_id() == '' OR session_status() === PHP_SESSION_NONE) {
				session_start();
			}
            $_SESSION['front_timings'] = array();
            $_SESSION['front_timings'] = $service_timings;

            $morning_time   = array();
            $afternoon_time = array();
            $evening_time   = array();
            $night_time     = array();

            if (! empty($service_timings) ) {
                foreach ( $service_timings as $service_time_key => $service_time_val ) {
                    $service_start_time = date('H', strtotime($service_time_val['start_time']));
                    $service_end_time   = date('H', strtotime($service_time_val['end_time']));

                    $service_formatted_start_time = date($bpa_wp_default_time_format, strtotime($service_time_val['start_time']));
                    $service_formatted_end_time   = date($bpa_wp_default_time_format, strtotime($service_time_val['end_time']));
                    $service_time_arr = $service_time_val;
                    if( !empty( $service_time_arr['guest_members'] ) ){
                        $service_time_arr['total_booked'] += $service_time_arr['guest_members'];
                    }
                    $service_time_arr['disable_flag_timeslot'] = false;
                    if( $service_time_arr['total_booked'] >= $max_service_capacity ){
                        /** Remove timeslot when Hide already booked time slot option is enabled */   
                        if( !empty( $bookingpress_hide_already_booked_slot ) && 1 == $bookingpress_hide_already_booked_slot ){
                            unset( $service_timings[ $service_time_key ] );
                            continue;
                        } else {
                            $service_time_arr['disable_flag_timeslot'] = true;
                        }
                    }
                    if( !isset( $service_time_arr['max_total_capacity'] ) ){    
                        $service_time_arr['max_total_capacity'] = $max_service_capacity;
                    }
                    if ($service_start_time >= 0 && $service_start_time < 12 ) {
                        
                        $morning_time[] = array_merge( $service_time_arr, array(
                            'formatted_start_time' => $service_formatted_start_time,
                            'formatted_end_time'   => $service_formatted_end_time." ".($service_time_arr['end_time'] == "24:00" ? "( ".esc_html__('next day', 'bookingpress-appointment-booking')." )" : ''),
                            'class'                => ( $service_time_arr['is_booked'] ) ? '__bpa-is-disabled' : '',
                        ) );
                    } elseif ($service_start_time >= 12 && $service_start_time < 16 ) {
                        $afternoon_time[] = array_merge( $service_time_arr, array(
                            'formatted_start_time' => $service_formatted_start_time,
                            'formatted_end_time'   => $service_formatted_end_time." ".($service_time_arr['end_time'] == "24:00" ? "( ".esc_html__('next day', 'bookingpress-appointment-booking')." )" : ''),
                            'class'                => ( $service_time_arr['is_booked'] ) ? '__bpa-is-disabled' : '',
                        ) );
                    } elseif ($service_start_time >= 16 && $service_start_time < 20 ) {
                        $evening_time[] = array_merge( $service_time_arr, array(
                            'formatted_start_time' => $service_formatted_start_time,
                            'formatted_end_time'   => $service_formatted_end_time." ".($service_time_arr['end_time'] == "24:00" ? "( ".esc_html__('next day', 'bookingpress-appointment-booking')." )" : ''),
                            'class'                => ( $service_time_arr['is_booked'] ) ? '__bpa-is-disabled' : '',
                        ) );
                    } else {
                        $night_time[] = array_merge( $service_time_arr, array(
                            'formatted_start_time' => $service_formatted_start_time,
                            'formatted_end_time'   => $service_formatted_end_time." ".($service_time_arr['end_time'] == "24:00" ? "( ".esc_html__('next day', 'bookingpress-appointment-booking')." )" : ''),
                            'class'                => ( $service_time_arr['is_booked'] ) ? '__bpa-is-disabled' : '',
                        ) );
                    }
                }
            }

            $bookingpress_timeslot_counts = count($service_timings);
            $bookingpress_selected_start_time = "";
            $bookingpress_selected_end_time = "";
            if($bookingpress_timeslot_counts == 1 && (!empty($service_timings[0]['end_time']) && $service_timings[0]['end_time'] == "24:00") && ($service_timings[0]['is_booked'] == 0) ){
                $bookingpress_selected_start_time = $service_timings[0]['start_time'];
                $bookingpress_selected_end_time = $service_timings[0]['end_time'];
            }

            $return_data = array(
                'morning_time'   => $morning_time,
                'afternoon_time' => $afternoon_time,
                'evening_time'   => $evening_time,
                'night_time'     => $night_time,
            );

            if( !empty( $service_timings_data['is_custom_duration'] ) ){
                $return_data['is_custom_duration'] = true;
            }


            //$return_data = apply_filters('bookingpress_modify_service_return_timings_filter', $return_data, $selected_service_id, $selected_date, $_POST, $max_service_capacity);

            if( true == $return ){
                return $return_data;
            } else {
                echo json_encode($return_data);
                exit();
            }

        }
        
        /**
         * Get category specific service
         *
         * @return void
         */
        function bookingpress_get_category_service_data()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta, $bookingpress_other_debug_log_id;
            do_action('bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Get category service posted data', 'bookingpress_bookingform', $_REQUEST, $bookingpress_other_debug_log_id);
            $response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                echo json_encode($response);
                exit();
            }            
			if ( ! empty( $_POST['category_id'] ) || intval($_POST['category_id']) == 0 ) {
                $selected_category_id        = intval($_POST['category_id']);
                $bookingpress_posted_data = !empty($_POST['posted_data']) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['posted_data'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                $bookingpress_total_services = 0;
                if (! empty($_POST['total_service']) ) {
                    $bookingpress_total_services = sanitize_text_field($_POST['total_service']);
                }
				if ( ! empty( $_POST['total_category'] ) ) {
					$bookingpress_total_category = sanitize_text_field( $_POST['total_category'] );
				}
                // Fetch services of selected categories
                $bookingpress_search_query_where       = '';
				if ( ! empty( $selected_category_id ) && $selected_category_id != 0 ) {
					$bookingpress_search_query_where .= " WHERE (bookingpress_category_id = '{$selected_category_id}')";
				}
                $bookingpress_search_query_placeholder = '';
				if ( ! empty( $bookingpress_total_category ) && $bookingpress_total_category != 0 ) {
					$bookingpress_search_query_where       .= ! empty( $bookingpress_search_query_where ) ? ' AND' : ' WHERE';
					$bookingpress_search_query_placeholder  = ' bookingpress_category_id IN (';
					$bookingpress_total_category_arr        = explode( ',', $bookingpress_total_category );
					$bookingpress_search_query_placeholder .= rtrim( str_repeat( '%d,', count( $bookingpress_total_category_arr ) ), ',' );
					$bookingpress_search_query_placeholder .= ')';
					array_unshift( $bookingpress_total_category_arr, $bookingpress_search_query_placeholder );
					$bookingpress_search_query_where .= call_user_func_array( array( $wpdb, 'prepare' ), $bookingpress_total_category_arr );
				}
				$bookingpress_search_query_placeholder = '';
                if (! empty($bookingpress_total_services) && $bookingpress_total_services != 0 ) {
					$bookingpress_search_query_where       .= ! empty( $bookingpress_search_query_where ) ? ' AND' : ' WHERE';
					$bookingpress_search_query_placeholder  = ' bookingpress_service_id IN (';
                    $bookingpress_total_services_arr        = explode(',', $bookingpress_total_services);
                    $bookingpress_search_query_placeholder .= rtrim(str_repeat('%d,', count($bookingpress_total_services_arr)), ',');
                    $bookingpress_search_query_placeholder .= ')';
                    array_unshift($bookingpress_total_services_arr, $bookingpress_search_query_placeholder);
					$bookingpress_search_query_where .= call_user_func_array( array( $wpdb, 'prepare' ), $bookingpress_total_services_arr );
                }

				$service_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_services} {$bookingpress_search_query_where} ORDER BY bookingpress_service_position ASC" ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally and $bookingpress_search_query_where is properly prepared. False Positive alarm

                $bookingpress_display_service_description = $BookingPress->bookingpress_get_customize_settings('display_service_description', 'booking_form');

                foreach ( $service_data as $service_key => $service_val ) {
                    $service_data[ $service_key ]['bookingpress_service_price']     = $BookingPress->bookingpress_price_formatter_with_currency_symbol($service_val['bookingpress_service_price']);
                    $service_data[ $service_key ]['service_price_without_currency'] = (float) $service_val['bookingpress_service_price'];
                    $service_data[ $service_key ]['bookingpress_service_name'] = stripslashes_deep($service_val['bookingpress_service_name']);
                    $service_id                              = $service_val['bookingpress_service_id'];
                    $service_meta_details                    = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = %d AND bookingpress_servicemeta_name = 'service_image_details'", $service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_servicesmeta is table name defined globally. False Positive alarm
                    $service_img_details                     = ! empty($service_meta_details['bookingpress_servicemeta_value']) ? maybe_unserialize($service_meta_details['bookingpress_servicemeta_value']) : array();
                    $service_data[ $service_key ]['img_url'] = ! empty($service_img_details[0]['url']) ? $service_img_details[0]['url'] : BOOKINGPRESS_URL . '/images/placeholder-img.jpg';
                    $service_data[ $service_key ]['bookingpress_service_description'] = stripslashes_deep($service_data[ $service_key ]['bookingpress_service_description']);
                    if ($bookingpress_display_service_description == 'false' ) {
                        $service_data[ $service_key ]['display_read_more_less'] = 1;
                        $default_service_description   = $service_data[ $service_key ]['bookingpress_service_description'];
                        if (strlen($default_service_description) > 140 ) {
                               $service_data[ $service_key ]['bookingpress_service_description_with_excerpt'] = substr($default_service_description, 0, 140);
                               $service_data[ $service_key ]['display_details_more']                          = 0;
                               $service_data[ $service_key ]['display_details_less']                          = 1;
                        } else {
                            $service_data[ $service_key ]['display_read_more_less'] = 0;
                        }
                    }
                }        
                $service_data = apply_filters('bookingpress_modify_service_data_on_category_selection', $service_data, $selected_category_id, $bookingpress_posted_data);

                do_action('bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Get category service - service data', 'bookingpress_bookingform', $service_data, $bookingpress_other_debug_log_id);

                echo wp_json_encode($service_data);
                exit();
            }
        }
        
        /**
         * Callback function for [bookingpress_form] shortcode
         *
         * @param  mixed $atts
         * @param  mixed $content
         * @param  mixed $tag
         * @return void
         */
        function bookingpress_front_booking_form( $atts, $content, $tag )
        {
            global $wpdb, $BookingPress, $bookingpress_common_date_format, $tbl_bookingpress_form_fields, $tbl_bookingpress_services, $tbl_bookingpress_customers, $bookingpress_global_options,$bookingpress_front_vue_data_fields;
            $defaults = array(
            'service'  => 0,
            'category' => 0,
            'selected_service' => 0,
            );
            $args     = shortcode_atts($defaults, $atts, $tag);
            extract($args);
            $Bookingpress_service  = 0;
            $Bookingpress_category = 0;
            $selected_category = 0;
            if (! empty($category) && $category != 0 ) {
                $Bookingpress_category            = $category;
                $this->bookingpress_form_category = $category;
            }            
            if( ! empty($service) && $service != 0 ) {
                $Bookingpress_service            = $service;
                $this->bookingpress_form_service = $service;
            }

            

            if (( ! empty($service) && $service != 0 ) || ( !empty($selected_service) && $selected_service != 0 ) || ( isset($_GET['bpservice_id']) && ! empty($_GET['bpservice_id']) ) || ( isset($_GET['s_id']) && ! empty($_GET['s_id']) ) ) {
                $total_service = array();   
                $bookingpress_is_service_load_from_url = 0;             
                $bookingpress_search_query_where = 'WHERE 1=1 ';
                if (!empty($_GET['bpservice_id']) || !empty($_GET['s_id']) ) {
                    if(!empty($_GET['bpservice_id']) && !isset($_GET['s_id']) ){
                        $selected_service = intval($_GET['bpservice_id']);
                    }else if(!empty($_GET['s_id'])){
                        $selected_service = intval($_GET['s_id']);
                        $category = 0;
                        $service = 0;
                        $this->bookingpress_form_service = 0;
                        $this->bookingpress_form_category = 0;
                    }
                    $bookingpress_is_service_load_from_url = 1;
                } else {
                    $selected_service = intval($selected_service);                                 
                    $this->bookingpress_form_service = $service;    
                }
                if(!empty($category)) {
                   $bookingpress_search_query_where .= " AND bookingpress_category_id IN ({$category})";
                }
                if(!empty($service)) {
                    $bookingpress_search_query_where .= " AND bookingpress_service_id IN ({$service})";
                }                   
                $is_service_exist = ''; 
                if(!empty($selected_service)) {                   
                    
                    $bookingpress_search_query_where .= " AND bookingpress_service_id = {$selected_service}";
                    $is_service_exist = $wpdb->get_row( "SELECT bookingpress_service_id FROM ".$tbl_bookingpress_services." ".$bookingpress_search_query_where); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm                
                    
                    if(!empty($selected_service) && !empty($is_service_exist)) {
                        // Get category id
                        $bookingpress_service_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $selected_service ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
                        if (! empty($bookingpress_service_details) ) {
                            $selected_category = $bookingpress_service_details['bookingpress_category_id'];
                        }
                        if($bookingpress_is_service_load_from_url == 1) {
                            $this->bookingpress_is_service_load_from_url = 1;
                        }
                    } else{
                        $selected_service = 0;
                    }
                } 
            }

            $bookingpress_service_details = $BookingPress->get_bookingpress_service_data_group_with_category();

            // Get labels and tabs names generated from customize
            // -----------------------------------------------------

            $bookingpress_customize_settings = $BookingPress->bookingpress_get_customize_settings(
                array(
                    'service_title',
                    'datetime_title',
                    'basic_details_title',
                    'summary_title',
                    'category_title',
                    'service_heading_title',
                    'timeslot_text',
                    'summary_content_text',
                    'service_duration_label',
                    'service_price_label',
                    'paypal_text',
                    'locally_text',
                    'total_amount_text',
                    'service_text',
                    'customer_text',
                    'date_time_text',
                    'appointment_details',
                    'payment_method_text',
                    'morning_text',
                    'afternoon_text',
                    'evening_text',
                    'night_text',
                    'goback_button_text',
                    'next_button_text',
                    'book_appointment_btn_text',
                    'booking_form_tabs_position',
                    'hide_category_service_selection',                    
                    'title_font_family',
                    'content_font_family',
                    'display_service_description',
                    'all_category_title'
                ),
                'booking_form'
            );         

            $bookingpress_first_tab_name  = stripslashes_deep($bookingpress_customize_settings['service_title']);//$BookingPress->bookingpress_get_customize_settings('service_title', 'booking_form');
            $bookingpress_second_tab_name = stripslashes_deep($bookingpress_customize_settings['datetime_title']);//$BookingPress->bookingpress_get_customize_settings('', 'booking_form');
            $bookingpress_third_tab_name  = stripslashes_deep($bookingpress_customize_settings['basic_details_title']);//$BookingPress->bookingpress_get_customize_settings('basic_details_title', 'booking_form');
            $bookingpress_fourth_tab_name = stripslashes_deep($bookingpress_customize_settings['summary_title']);//$BookingPress->bookingpress_get_customize_settings('summary_title', 'booking_form');
	        $bookingpress_all_category_title = stripslashes_deep($bookingpress_customize_settings['all_category_title']);//$BookingPress->bookingpress_get_customize_settings('all_category_title', 'booking_form');
            $bookingpress_category_title       = stripslashes_deep($bookingpress_customize_settings['category_title']);//$BookingPress->bookingpress_get_customize_settings('category_title', 'booking_form');
            $bookingpress_services_title       = stripslashes_deep($bookingpress_customize_settings['service_heading_title']);//$BookingPress->bookingpress_get_customize_settings('service_heading_title', 'booking_form');
            $bookingpress_timeslot_title       = stripslashes_deep($bookingpress_customize_settings['timeslot_text']);//$BookingPress->bookingpress_get_customize_settings('timeslot_text', 'booking_form');
            $bookingpress_summary_content_text = stripslashes_deep($bookingpress_customize_settings['summary_content_text']);//$BookingPress->bookingpress_get_customize_settings('summary_content_text', 'booking_form');

            $bookingpress_service_duration_text = !empty($bookingpress_customize_settings['service_duration_label']) ? stripslashes_deep($bookingpress_customize_settings['service_duration_label']) : '';//$BookingPress->bookingpress_get_customize_settings('service_duration_label', 'booking_form');
            if (empty($bookingpress_service_duration_text) ) {
                $bookingpress_service_duration_text = __('Duration', 'bookingpress-appointment-booking') . ':';
            }
            $bookingpress_service_price_text = !empty($bookingpress_customize_settings['service_price_label']) ? stripslashes_deep($bookingpress_customize_settings['service_price_label']) : '';//$BookingPress->bookingpress_get_customize_settings('service_price_label', 'booking_form');
            if (empty($bookingpress_service_price_text) ) {
                $bookingpress_service_price_text = __('Price', 'bookingpress-appointment-booking') . ':';
            }

            $bookingpress_paypal_text = stripslashes_deep($bookingpress_customize_settings['paypal_text']);//$BookingPress->bookingpress_get_customize_settings('paypal_text', 'booking_form');
            if (empty($bookingpress_paypal_text) ) {
                $bookingpress_paypal_text = __('PayPal', 'bookingpress-appointment-booking');
            }

            $bookingpress_locally_text = stripslashes_deep($bookingpress_customize_settings['locally_text']);//$BookingPress->bookingpress_get_customize_settings('locally_text', 'booking_form');
            if (empty($bookingpress_locally_text) ) {
                $bookingpress_locally_text = __('Pay Locally', 'bookingpress-appointment-booking');
            }

            $bookingpress_total_amount_text = stripslashes_deep($bookingpress_customize_settings['total_amount_text']);//$BookingPress->bookingpress_get_customize_settings('total_amount_text', 'booking_form');
            if (empty($bookingpress_total_amount_text) ) {
                $bookingpress_total_amount_text = __('Total Amount Payable', 'bookingpress-appointment-booking');
            }

            $bookingpress_service_text = stripslashes_deep($bookingpress_customize_settings['service_text']);//$BookingPress->bookingpress_get_customize_settings('service_text', 'booking_form');
            if (empty($bookingpress_service_text) ) {
                $bookingpress_service_text = __('Service', 'bookingpress-appointment-booking');
            }

            $bookingpress_customer_text = stripslashes_deep($bookingpress_customize_settings['customer_text']);//$BookingPress->bookingpress_get_customize_settings('customer_text', 'booking_form');
            if (empty($bookingpress_customer_text) ) {
                $bookingpress_customer_text = __('Customer', 'bookingpress-appointment-booking');
            }

            $bookingpress_date_time_text = stripslashes_deep($bookingpress_customize_settings['date_time_text']);//$BookingPress->bookingpress_get_customize_settings('date_time_text', 'booking_form');
            if (empty($bookingpress_date_time_text) ) {
                $bookingpress_date_time_text = __('Date &amp; Time', 'bookingpress-appointment-booking');
            }

            $bookingpress_appointment_details_title_text = stripslashes_deep($bookingpress_customize_settings['appointment_details']);//$BookingPress->bookingpress_get_customize_settings('date_time_text', 'booking_form');
            if (empty($bookingpress_appointment_details_title_text) ) {
                $bookingpress_appointment_details_title_text = __('Appointment Details', 'bookingpress-appointment-booking');
            }

            $bookingpress_payment_method_text = stripslashes_deep($bookingpress_customize_settings['payment_method_text']);//$BookingPress->bookingpress_get_customize_settings('payment_method_text', 'booking_form');
            if (empty($bookingpress_payment_method_text) ) {
                $bookingpress_payment_method_text = __('Select Payment Method', 'bookingpress-appointment-booking');
            }

            $bookingpress_morning_text = stripslashes_deep($bookingpress_customize_settings['morning_text']);//$BookingPress->bookingpress_get_customize_settings('morning_text', 'booking_form');
            if (empty($bookingpress_morning_text) ) {
                $bookingpress_morning_text = esc_html__('Morning', 'bookingpress-appointment-booking');
            }
            $bookingpress_afternoon_text = stripslashes_deep($bookingpress_customize_settings['afternoon_text']);//$BookingPress->bookingpress_get_customize_settings('afternoon_text', 'booking_form');
            if (empty($bookingpress_afternoon_text) ) {
                $bookingpress_afternoon_text = esc_html__('Afternoon', 'bookingpress-appointment-booking');
            }
            $bookingpress_evening_text = stripslashes_deep($bookingpress_customize_settings['evening_text']);//$BookingPress->bookingpress_get_customize_settings('evening_text', 'booking_form');
            if (empty($bookingpress_evening_text) ) {
                $bookingpress_evening_text = esc_html__('Evening', 'bookingpress-appointment-booking');
            }
            $bookingpress_night_text = stripslashes_deep($bookingpress_customize_settings['night_text']);//$BookingPress->bookingpress_get_customize_settings('night_text', 'booking_form');
            if (empty($bookingpress_night_text) ) {
                $bookingpress_night_text = esc_html__('Night', 'bookingpress-appointment-booking');
            }

            $bookingpress_goback_btn_text           = stripslashes_deep($bookingpress_customize_settings['goback_button_text']);//$BookingPress->bookingpress_get_customize_settings('goback_button_text', 'booking_form');
            $bookingpress_next_btn_text             = stripslashes_deep($bookingpress_customize_settings['next_button_text']);//$BookingPress->bookingpress_get_customize_settings('next_button_text', 'booking_form');
            $bookingpress_book_appointment_btn_text = stripslashes_deep($bookingpress_customize_settings['book_appointment_btn_text']);//$BookingPress->bookingpress_get_customize_settings('book_appointment_btn_text', 'booking_form');
            $bookingpress_tabs_position             = $bookingpress_customize_settings['booking_form_tabs_position'];//$BookingPress->bookingpress_get_customize_settings('booking_form_tabs_position', 'booking_form');

            $bookingpress_hide_category_service       = $bookingpress_customize_settings['hide_category_service_selection'];//$BookingPress->bookingpress_get_customize_settings('hide_category_service_selection', 'booking_form');

            
            $bookingpress_hide_category_service       = ( $bookingpress_hide_category_service == 'true' ) ? 1 : 0;
            $bookingpress_loaded_from_share_url       = false;
            if(!empty($_GET['s_id']) && ( isset($_GET['allow_modify']) && $_GET['allow_modify'] == '0' ) ){
                $bookingpress_hide_category_service = 1;
                $bookingpress_loaded_from_share_url = true;
            } else if(!empty($_GET['s_id']) && ( isset($_GET['allow_modify']) && $_GET['allow_modify'] == '1' ) ){
                $bookingpress_hide_category_service = 0;
                $bookingpress_loaded_from_share_url = true;
            }

            
            if( 1 == $bookingpress_hide_category_service && $bookingpress_loaded_from_share_url ){
                if( $selected_service == 0 ){
                    $bookingpress_hide_category_service = 0;
                }
            }

            $this->bookingpress_hide_category_service = $bookingpress_hide_category_service;

            $bookingpress_global_options_arr       = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_default_date_time_format = $bookingpress_global_options_arr['wp_default_date_format'];
            
            $bookingpress_default_date_format = 'MMMM D, YYYY';
            if ($bookingpress_default_date_time_format == 'F j, Y' ) {
                $bookingpress_default_date_format = 'MMMM D, YYYY';
            } elseif ($bookingpress_default_date_time_format == 'Y-m-d' ) {
                $bookingpress_default_date_format = 'YYYY-MM-DD';
            } elseif ($bookingpress_default_date_time_format == 'm/d/Y' ) {
                $bookingpress_default_date_format = 'MM/DD/YYYY';
            } elseif($bookingpress_default_date_time_format == 'd/m/Y') {
                $bookingpress_default_date_format = 'DD/MM/YYYY';
            } elseif ($bookingpress_default_date_time_format == 'd.m.Y') {
                $bookingpress_default_date_format = 'DD.MM.YYYY';
            } elseif ($bookingpress_default_date_time_format == 'd-m-Y') {
                $bookingpress_default_date_format = 'DD-MM-YYYY';
            }

            $this->bookingpress_default_date_format = $bookingpress_default_date_format;

            // -----------------------------------------------------

            // Get form fields details
            // -----------------------------------------------------

            /** Check if Pro version is exists but not activated */
            if( $BookingPress->bpa_is_pro_exists() && !$BookingPress->bpa_is_pro_active() ){
                if( empty( $BookingPress->bpa_pro_plugin_version() ) ){
                    $bookingpress_form_fields               = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_form_fields} ORDER BY bookingpress_field_position ASC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
                } else {
                    $bookingpress_form_fields               = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_is_default = %d ORDER BY bookingpress_field_position ASC", 1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
                }
            } else {
                $bookingpress_form_fields               = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_form_fields} ORDER BY bookingpress_field_position ASC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
            }


            $bookingpress_form_fields_error_msg_arr = $bookingpress_form_fields_new = array();
            
            $bookingpress_form_fields        = apply_filters('bookingpress_modify_field_data_before_prepare', $bookingpress_form_fields);
            
            foreach ( $bookingpress_form_fields as $bookingpress_form_field_key => $bookingpress_form_field_val ) {

                if($bookingpress_form_field_val['bookingpress_field_is_hide'] == 0) {

                    $bookingpress_v_model_value = '';
                    if ($bookingpress_form_field_val['bookingpress_form_field_name'] == 'fullname' ) {
                        $bookingpress_v_model_value = 'customer_name';
                    } elseif ($bookingpress_form_field_val['bookingpress_form_field_name'] == 'firstname' ) {
                        $bookingpress_v_model_value = 'customer_firstname';
                    } elseif ($bookingpress_form_field_val['bookingpress_form_field_name'] == 'lastname' ) {
                        $bookingpress_v_model_value = 'customer_lastname';
                    } elseif ($bookingpress_form_field_val['bookingpress_form_field_name'] == 'email_address' ) {
                        $bookingpress_v_model_value = 'customer_email';
                    } elseif ($bookingpress_form_field_val['bookingpress_form_field_name'] == 'phone_number' ) {
                        $bookingpress_v_model_value = 'customer_phone';
                    } elseif ($bookingpress_form_field_val['bookingpress_form_field_name'] == 'note' ) {
                        $bookingpress_v_model_value = 'appointment_note';
                    } else {
                        $bookingpress_v_model_value = $bookingpress_form_field_val['bookingpress_field_meta_key'];
                    }

                    $bookingpress_front_vue_data_fields['appointment_step_form_data'][$bookingpress_v_model_value] = '';

                    $bookingpress_field_type = '';
                    if ($bookingpress_form_field_val['bookingpress_form_field_name'] == 'fullname' ) {
                        $bookingpress_field_type = 'Text';
                    } elseif ($bookingpress_form_field_val['bookingpress_form_field_name'] == 'firstname' ) {
                        $bookingpress_field_type = 'Text';
                    } elseif ($bookingpress_form_field_val['bookingpress_form_field_name'] == 'lastname' ) {
                        $bookingpress_field_type = 'Text';
                    } elseif ($bookingpress_form_field_val['bookingpress_form_field_name'] == 'email_address' ) {
                        $bookingpress_field_type = 'Email';
                    } elseif ($bookingpress_form_field_val['bookingpress_form_field_name'] == 'phone_number' ) {
                        $bookingpress_field_type = 'Dropdown';
                    } elseif ($bookingpress_form_field_val['bookingpress_form_field_name'] == 'note' ) {
                        $bookingpress_field_type = 'Textarea';
                    } else {
                        $bookingpress_field_type = $bookingpress_form_field_val['bookingpress_field_type'];
                    }

                    $bookingpress_field_setting_fields_tmp                   = array();
                    $bookingpress_field_setting_fields_tmp['id']             = intval($bookingpress_form_field_val['bookingpress_form_field_id']);
                    $bookingpress_field_setting_fields_tmp['field_name']     = $bookingpress_form_field_val['bookingpress_form_field_name'];
                    $bookingpress_field_setting_fields_tmp['field_type']     = $bookingpress_field_type;
                    $bookingpress_field_setting_fields_tmp['is_edit']        = false;

                    $bookingpress_field_setting_fields_tmp['is_required']    = ( $bookingpress_form_field_val['bookingpress_field_required'] == 0 ) ? false : true;
                    $bookingpress_field_setting_fields_tmp['label']          = stripslashes_deep($bookingpress_form_field_val['bookingpress_field_label']);
                    $bookingpress_field_setting_fields_tmp['placeholder']    = stripslashes_deep($bookingpress_form_field_val['bookingpress_field_placeholder']);
                    $bookingpress_field_setting_fields_tmp['error_message']  = stripslashes_deep($bookingpress_form_field_val['bookingpress_field_error_message']);
                    $bookingpress_field_setting_fields_tmp['is_hide']        = ( $bookingpress_form_field_val['bookingpress_field_is_hide'] == 0 ) ? false : true;
                    $bookingpress_field_setting_fields_tmp['field_position'] = floatval($bookingpress_form_field_val['bookingpress_field_position']);
                    $bookingpress_field_setting_fields_tmp['v_model_value']  = $bookingpress_v_model_value;                    

                    $bookingpress_field_setting_fields_tmp = apply_filters( 'bookingpress_arrange_form_fields_outside', $bookingpress_field_setting_fields_tmp, $bookingpress_form_field_val);

                    $bookingpress_front_vue_data_fields['appointment_step_form_data'] = apply_filters('bookingpress_add_appointment_step_form_data_filter',$bookingpress_front_vue_data_fields['appointment_step_form_data'],$bookingpress_field_setting_fields_tmp);
                    
                    array_push( $bookingpress_form_fields_new, $bookingpress_field_setting_fields_tmp );

                    if ($bookingpress_form_field_val['bookingpress_field_required'] == '1' ) {
                        if ($bookingpress_v_model_value == 'customer_email' ) {
                            $bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ] = array(
                                array(
                                'required' => true,
                                'message'  => stripslashes_deep($bookingpress_form_field_val['bookingpress_field_error_message']),
                                'trigger'  => 'blur',
                                ),
                                array(
                                'type'    => 'email',
                                'message' => esc_html__('Please enter valid email address', 'bookingpress-appointment-booking'),
                                'trigger' => 'blur',
                                ),
                            );
                        } else {                 
                            $bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][] = array(
                                'required' => true,
                                'message'  => stripslashes_deep($bookingpress_form_field_val['bookingpress_field_error_message']),
                                'trigger'  => 'blur',
                            );                                                       
                        }

                        if(isset($bookingpress_form_fields_error_msg_arr[$bookingpress_v_model_value][0]['message']) && $bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value][0]['message'] == '') {
                            $bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message'] = !empty($bookingpress_form_field_val['bookingpress_field_label']) ?  stripslashes_deep($bookingpress_form_field_val['bookingpress_field_label']).' '.__('is required','bookingpress-appointment-booking') : '';
                        }           
                    }                                       
                    $bookingpress_form_fields_error_msg_arr = apply_filters( 'bookingpress_modify_form_fields_rules_arr', $bookingpress_form_fields_error_msg_arr,$bookingpress_field_setting_fields_tmp );
                }    
            }

            $this->bookingpress_form_fields_error_msg_arr = apply_filters( 'bookingpress_modify_form_fields_msg_array', $bookingpress_form_fields_error_msg_arr );
                      
            $this->bookingpress_form_fields_new           = $bookingpress_form_fields_new;
            
            // -----------------------------------------------------

            if (is_user_logged_in() ) {
                $bookingpress_wp_user_id              = get_current_user_id();
                $bookingpress_check_user_exist_or_not = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_customer_id) as total FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id = %d AND bookingpress_user_status = 0 AND bookingpress_user_type = 0", $bookingpress_wp_user_id ));  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
                if ($bookingpress_check_user_exist_or_not > 0 ) {
                    $bookingpress_update_customer_data = array(
                    'bookingpress_user_status' => 1,
                    'bookingpress_user_type'   => 2,
                    );

                    $bookingpress_where_condition = array(
                    'bookingpress_wpuser_id' => $bookingpress_wp_user_id,
                    );

                    $wpdb->update($tbl_bookingpress_customers, $bookingpress_update_customer_data, $bookingpress_where_condition);
                }
            }

            $bookingpress_uniq_id = uniqid();

            $BookingPress->set_front_css(1);
            $BookingPress->set_front_js(1);
            $BookingPress->bookingpress_load_booking_form_custom_css();
			//Code for modify front shortcode data from outside
			//-------------------------------------------------------
				$bookingpress_class_vars_val = array(
					'form_category' => $this->bookingpress_form_category,
					'form_service' => $this->bookingpress_form_service,
					'hide_category_service' => $this->bookingpress_hide_category_service,
					'default_date_format' => $this->bookingpress_default_date_format,
					'default_time_format' => $this->bookingpress_default_time_format,
					'form_field_err_msg_arr' => $this->bookingpress_form_fields_error_msg_arr,
					'form_fields_new' => $this->bookingpress_form_fields_new,
					'is_service_load_from_url' => $this->bookingpress_is_service_load_from_url,
				);

				do_action('bookingpress_add_dynamic_details_booking_shortcode', $bookingpress_uniq_id, $bookingpress_class_vars_val, $args);
			//-------------------------------------------------------	

            ob_start();
            $bookingpress_shortcode_file_url = BOOKINGPRESS_VIEWS_DIR . '/frontend/appointment_booking_form.php';
            $bookingpress_shortcode_file_url = apply_filters('bookingpress_change_booking_shortcode_file_url', $bookingpress_shortcode_file_url);
            include $bookingpress_shortcode_file_url;
            $content .= ob_get_clean();

            // Main data loading script
            $bookingpress_global_details     = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_formatted_timeslot = $bookingpress_global_details['bpa_time_format_for_timeslot'];

            $bookingpress_site_current_language = get_locale();
            if ($bookingpress_site_current_language == 'ru_RU' ) {
                $bookingpress_site_current_language = 'ru';
            } elseif ($bookingpress_site_current_language == 'ar' ) {
                $bookingpress_site_current_language = 'ar'; // arabic
            } elseif ($bookingpress_site_current_language == 'bg_BG' ) {
                $bookingpress_site_current_language = 'bg'; // Bulgeria
            } elseif ($bookingpress_site_current_language == 'ca' ) {
                $bookingpress_site_current_language = 'ca'; // Canada
            } elseif ($bookingpress_site_current_language == 'da_DK' || $bookingpress_site_current_language == 'de_AT' || $bookingpress_site_current_language == 'de_CH' || $bookingpress_site_current_language == 'de_DE_formal' ) {
                $bookingpress_site_current_language = 'da'; // Denmark
            } elseif ($bookingpress_site_current_language == 'de_DE' || $bookingpress_site_current_language == 'de_CH_informal' ) {
                $bookingpress_site_current_language = 'de'; // Germany
            } elseif ($bookingpress_site_current_language == 'el' ) {
                $bookingpress_site_current_language = 'el'; // Greece
            } elseif ($bookingpress_site_current_language == 'es_ES' ) {
                $bookingpress_site_current_language = 'es'; // Spain
            } elseif ($bookingpress_site_current_language == 'fr_FR' ) {
                $bookingpress_site_current_language = 'fr'; // France
            } elseif ($bookingpress_site_current_language == 'hr' ) {
                $bookingpress_site_current_language = 'hr'; // Croatia
            } elseif ($bookingpress_site_current_language == 'hu_HU' ) {
                $bookingpress_site_current_language = 'hu'; // Hungary
            } elseif ($bookingpress_site_current_language == 'id_ID' ) {
                $bookingpress_site_current_language = 'id'; // Indonesia
            } elseif ($bookingpress_site_current_language == 'is_IS' ) {
                $bookingpress_site_current_language = 'is'; // Iceland
            } elseif ($bookingpress_site_current_language == 'it_IT' ) {
                $bookingpress_site_current_language = 'it'; // Italy
            } elseif ($bookingpress_site_current_language == 'ja' ) {
                $bookingpress_site_current_language = 'ja'; // Japan
            } elseif ($bookingpress_site_current_language == 'ka_GE' ) {
                $bookingpress_site_current_language = 'ka'; // Georgia
            } elseif ($bookingpress_site_current_language == 'ko_KR' ) {
                $bookingpress_site_current_language = 'ko'; // Korean
            } elseif ($bookingpress_site_current_language == 'lt_LT' ) {
                $bookingpress_site_current_language = 'lt'; // Lithunian
            } elseif ($bookingpress_site_current_language == 'mn' ) {
                $bookingpress_site_current_language = 'mn'; // Mongolia
            } elseif ($bookingpress_site_current_language == 'nl_NL' ) {
                $bookingpress_site_current_language = 'nl'; // Netherlands
            } elseif ($bookingpress_site_current_language == 'nn_NO' ) {
                $bookingpress_site_current_language = 'no'; // Norway
            } elseif ($bookingpress_site_current_language == 'pl_PL' ) {
                $bookingpress_site_current_language = 'pl'; // Poland
            } elseif ($bookingpress_site_current_language == 'pt_BR' ) {
                $bookingpress_site_current_language = 'pt-br'; // Portuguese
            } elseif ($bookingpress_site_current_language == 'ro_RO' ) {
                $bookingpress_site_current_language = 'ro'; // Romania
            } elseif ($bookingpress_site_current_language == 'sk_SK' ) {
                $bookingpress_site_current_language = 'sk'; // Slovakia
            } elseif ($bookingpress_site_current_language == 'sl_SI' ) {
                $bookingpress_site_current_language = 'sl'; // Slovenia
            } elseif ($bookingpress_site_current_language == 'sq' ) {
                $bookingpress_site_current_language = 'sq'; // Albanian
            } elseif ($bookingpress_site_current_language == 'sr_RS' ) {
                $bookingpress_site_current_language = 'sr'; // Suriname
            } elseif ($bookingpress_site_current_language == 'sv_SE' ) {
                $bookingpress_site_current_language = 'sv'; // El Salvador
            } elseif ($bookingpress_site_current_language == 'tr_TR' ) {
                $bookingpress_site_current_language = 'tr'; // Turkey
            } elseif ($bookingpress_site_current_language == 'uk' ) {
                $bookingpress_site_current_language = 'uk'; // Ukrain
            } elseif ($bookingpress_site_current_language == 'vi' ) {
                $bookingpress_site_current_language = 'vi'; // Virgin Islands (U.S.)
            } elseif ($bookingpress_site_current_language == 'zh_CN' ) {
                $bookingpress_site_current_language = 'zh-cn'; // Chinese
            } else {
                $bookingpress_site_current_language = 'en';
            }

            $no_appointment_time_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_appointment_time_selected_for_the_booking', 'message_setting');

            $no_service_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_service_selected_for_the_booking', 'message_setting');

            $bookingpress_script_return_data = '';

            $bookingpress_front_booking_dynamic_helper_vars = '';
            $bookingpress_front_booking_dynamic_helper_vars = apply_filters('bookingpress_front_booking_dynamic_helper_vars', $bookingpress_front_booking_dynamic_helper_vars);

            $bookingpress_vue_root_element_id = '#bookingpress_booking_form_' . $bookingpress_uniq_id;

            $bookingpress_dynamic_directive_data = '';
            $bookingpress_dynamic_directive_data = apply_filters('bookingpress_front_booking_dynamic_directives', $bookingpress_dynamic_directive_data);

            $bookingpress_dynamic_data_fields = '';
            $bookingpress_dynamic_data_fields = apply_filters('bookingpress_front_booking_dynamic_data_fields', $bookingpress_dynamic_data_fields, $this->bookingpress_form_category, $this->bookingpress_form_service,$selected_service,$selected_category);

            $bookingpress_dynamic_on_load_methods_data = '';
            $bookingpress_dynamic_on_load_methods_data = apply_filters('bookingpress_front_booking_dynamic_on_load_methods', $bookingpress_dynamic_on_load_methods_data);

            $bookingpress_vue_methods_data = '';
            $bookingpress_vue_methods_data = apply_filters('bookingpress_front_booking_dynamic_vue_methods', $bookingpress_vue_methods_data);

            if (! empty($bookingpress_front_booking_dynamic_helper_vars) ) {
                $bookingpress_script_return_data .= $bookingpress_front_booking_dynamic_helper_vars;
            }

            $bookingpress_script_return_data .= "var bookingpress_uniq_id_js_var = '" . $bookingpress_uniq_id . "';";

            $bookingpress_nonce = esc_html(wp_create_nonce('bpa_wp_nonce'));

            $bookingpress_site_date = date('Y-m-d H:i:s', current_time( 'timestamp') );
            $bookingpress_site_date = apply_filters( 'bookingpress_modify_current_date', $bookingpress_site_date );
            $bookingpress_site_date = str_replace('-', '/', $bookingpress_site_date);

            $bpa_allow_modify_from_url = !empty($_GET['allow_modify']) ? 1 : 0;

            if( ( isset($_GET['bpservice_id']) ) || isset($_GET['s_id']) ){
                $this->bookingpress_is_service_load_from_url = 1;
            }

            if( 1 == $this->bookingpress_is_service_load_from_url ){
                if( empty( $selected_service ) ){
                    $this->bookingpress_is_service_load_from_url = 0;
                }
            }
            
            
            $bookingpress_script_return_data .= 'app = new Vue({ 
				el: "' . $bookingpress_vue_root_element_id . '",
				components: { "vue-cal": vuecal },
				directives: { ' . $bookingpress_dynamic_directive_data . ' },
				data(){
					var bookingpress_return_data = ' . $bookingpress_dynamic_data_fields . ';
					bookingpress_return_data["jsCurrentDate"] = new Date('. ( !empty( $bookingpress_site_date ) ? '"'.$bookingpress_site_date.'"' : '' ) .');
					bookingpress_return_data["appointment_step_form_data"]["stime"] = ' . ( time() + 14921 ) . ';
					bookingpress_return_data["appointment_step_form_data"]["spam_captcha"] = "";
					bookingpress_return_data["hide_category_service"] = "' . $this->bookingpress_hide_category_service . '";
					bookingpress_return_data["default_date_format"] = "' . $this->bookingpress_default_date_format . '";
					bookingpress_return_data["customer_details_rule"] = ' . json_encode($this->bookingpress_form_fields_error_msg_arr) . ';
					bookingpress_return_data["customer_form_fields"] = ' . json_encode($this->bookingpress_form_fields_new) . ';
					bookingpress_return_data["is_error_msg"] = "";
					bookingpress_return_data["is_display_error"] = "0";
					bookingpress_return_data["is_service_loaded_from_url"] = "' . $this->bookingpress_is_service_load_from_url . '";
					bookingpress_return_data["booking_cal_maxdate"] = new Date().addDays(730);
                    bookingpress_return_data["is_booking_form_empty_loader"] = "1";
                    bookingpress_return_data["bpa_allow_modify_from_url"] = "'.$bpa_allow_modify_from_url.'";

					bookingpress_return_data["site_locale"] = "' . $bookingpress_site_current_language . '";    
					bookingpress_return_data["appointment_step_form_data"]["bookingpress_uniq_id"] = "' . $bookingpress_uniq_id . '";
					var bookingpress_captcha_key = "bookingpress_captcha_' . $bookingpress_uniq_id . '";
					bookingpress_return_data["appointment_step_form_data"][bookingpress_captcha_key] = "";

					return bookingpress_return_data
				},
				filters: {
					bookingpress_format_date: function(value){
						var default_date_format = "' . $this->bookingpress_default_date_format . '";
						return moment(String(value)).format(default_date_format)
					},
					bookingpress_format_time: function(value){
						var default_time_format = "' . $bookingpress_formatted_timeslot . '";
						return moment(String(value), "HH:mm:ss").format(default_time_format)
					}
				},
                beforeCreate(){
					this.is_booking_form_empty_loader = "1";
				},
				created(){
					this.bookingpress_load_booking_form();
				},
				mounted(){
                    const vm_onload = this
                    if(vm_onload.is_service_loaded_from_url){
                        var bpa_selected_service_category_id = "";
                        vm_onload.all_services_data.forEach(function(currentValue, index, arr){
                            if(currentValue.bookingpress_service_id == vm_onload.appointment_step_form_data.selected_service){
                                bpa_selected_service_category_id = currentValue.bookingpress_category_id;
                            }
                        });
                        
                        if(bpa_selected_service_category_id != "" && bpa_selected_service_category_id == "0"){
                            vm_onload.selectStepCategory(0);
                        }else if(bpa_selected_service_category_id != "" && bpa_selected_service_category_id == "0"){
                            vm_onload.selectStepCategory(bpa_selected_service_category_id);
                        }
                    }
					this.loadSpamProtection()
					' . $bookingpress_dynamic_on_load_methods_data . '
                    
					/*if(this.hide_category_service == "1" || this.is_service_loaded_from_url == "1"){
						this.bookingpress_current_tab = "datetime";
                        this.bookingpress_disable_date('.$selected_service.');
					}*/
				},
				methods: {
                    bookingpress_load_booking_form(){
                        const vm = this;
                        setTimeout(function(){
                            vm.is_booking_form_empty_loader = "0"
                            setTimeout(function(){
                                var elms = document.querySelectorAll("#bpa-front-tabs");
                                for(var i = 0; i < elms.length; i++)  {
                                    elms[i].style.display = "flex";
                                }
                                
                                
                                var elms2 = document.querySelectorAll("#bpa-front-data-empty-view");
                                for(var i = 0; i < elms2.length; i++)  {
                                   elms2[i].style.display = "flex";
                                }

                            }, 500);
                        }, 2000);
                    },
					generateSpamCaptcha(){
						const vm = this;
                        var bkp_wpnonce_pre = "' . $bookingpress_nonce . '";
                        var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                        if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
                        {
                            bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
                        }
                        else {
                            bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
                        }
						var postData = { action: "bookingpress_generate_spam_captcha", _wpnonce:bkp_wpnonce_pre_fetch };
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
						.then( function (response) {
							if(response.variant != "error" && (response.data.captcha_val != "" && response.data.captcha_val != undefined)){
								vm.appointment_step_form_data.spam_captcha = response.data.captcha_val;
							}else{
                                var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                                if(typeof bkp_wpnonce_pre_fetch!="undefined" && bkp_wpnonce_pre_fetch!=null && response.data.updated_nonce!="")
                                {
                                    document.getElementById("_wpnonce").value = response.data.updated_nonce;
                                } else {
                                    vm.$notify({
                                        title: response.data.title,
                                        message: response.data.msg,
                                        type: response.data.variant,
                                        customClass: "error_notification"
                                    });
                                }
							}
						}.bind(this) )
						.catch( function (error) {
							console.log(error);
						});
					},
					loadSpamProtection(){
						const vm = this;
						vm.generateSpamCaptcha();
					},
					' . $bookingpress_vue_methods_data . '
				},
			});';

            $bpa_script_data = " var app;  
            window.addEventListener('DOMContentLoaded', function() {
                {$bookingpress_script_return_data}
            });";

            wp_add_inline_script('bookingpress_elements_locale', $bpa_script_data, 'after');

            $bookingpress_custom_css = $BookingPress->bookingpress_get_customize_settings('custom_css', 'booking_form');            
            $bookingpress_custom_css = !empty($bookingpress_custom_css) ? stripslashes_deep( $bookingpress_custom_css ) : '';
            wp_add_inline_style( 'bookingpress_front_custom_css', $bookingpress_custom_css, 'after' );


            $this->bookingpress_form_category = 0;
            $this->bookingpress_form_service = 0 ;
            $this->bookingpress_hide_category_service= 0;
            $this->bookingpress_is_service_load_from_url = 0;
            $this->bookingpress_form_fields_error_msg_arr = array();
            $this->bookingpress_form_fields_new = array();

			return do_shortcode( $content );
		}
        
        /**
         * Hook for add data variables for Booking Form shortcode
         *
         * @param  mixed $bookingpress_dynamic_data_fields      Global data variable for Booking Form
         * @param  mixed $bookingpress_category                 Shortcode allowed category
         * @param  mixed $bookingpress_service                  Shortcode allowed service
         * @param  mixed $selected_service                      Shortcode default selected service
         * @param  mixed $selected_category                     Shortcode default selected category
         * @return void
         */
        function bookingpress_booking_dynamic_data_fields_func( $bookingpress_dynamic_data_fields, $bookingpress_category, $bookingpress_service, $selected_service,$selected_category )
        {
            global $wpdb, $BookingPress, $bookingpress_front_vue_data_fields, $tbl_bookingpress_customers, $tbl_bookingpress_categories, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta, $tbl_bookingpress_form_fields, $bookingpress_global_options;
            // Get categories

            $bookingpress_search_query_where = 'WHERE 1=1 ';
            $bookingpress_search_query_join  = '';
            if (! empty($bookingpress_category) ) {
                $bookingpress_search_query_where .= " AND category.bookingpress_category_id IN ({$bookingpress_category})";
		        $bookingpress_front_vue_data_fields['appointment_step_form_data']['total_category'] = $bookingpress_category;
            }
            $bookingpress_search_query_join  .= "LEFT JOIN {$tbl_bookingpress_services} AS service ON category.bookingpress_category_id = service.bookingpress_category_id";
            $bookingpress_search_query_where .= ' AND category.bookingpress_category_id = service.bookingpress_category_id';
            if (! empty($bookingpress_service) ) {
                $bookingpress_search_query_where .= " AND service.bookingpress_service_id IN ({$bookingpress_service})";
                $bookingpress_front_vue_data_fields['appointment_step_form_data']['total_services'] = $bookingpress_service;
            }

            $bookingpress_search_query_where .= ' GROUP BY bookingpress_category_id';
            $bookingpress_service_categories  = $wpdb->get_results("SELECT category.* FROM {$tbl_bookingpress_categories} AS category {$bookingpress_search_query_join} {$bookingpress_search_query_where} ORDER BY bookingpress_category_position ASC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_categories is a table name. false alarm

            foreach (  $bookingpress_service_categories as $key => $val ) {
                $bookingpress_service_categories[$key]['bookingpress_category_name'] = stripslashes_deep($val['bookingpress_category_name']);                
            }
            $bookingpress_front_vue_data_fields['service_categories'] = $bookingpress_service_categories;            
            $default_service_category = ! empty($bookingpress_service_categories[0]['bookingpress_category_id']) ? $bookingpress_service_categories[0]['bookingpress_category_id'] : 0;
            $default_service_category  = empty($selected_category) ? $default_service_category : $selected_category;            
            $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_category'] = $default_service_category;
            $bookingpress_service_search_query_where = '';
            $bookingpress_service_cache_param = $default_service_category;
            
            if (! empty($bookingpress_service) ) {
                $bookingpress_service_search_query_where .= " AND bookingpress_service_id IN ({$bookingpress_service})";
                $bookingpress_service_cache_param .= '_' . $bookingpress_service;
            }

            //$service_data = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_category_id = {$default_service_category} {$bookingpress_service_search_query_where} ORDER BY bookingpress_service_position", ARRAY_A);               
            $all_service_data = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_services} WHERE 1=1 {$bookingpress_service_search_query_where} ORDER BY bookingpress_service_position", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm
            $service_data = array();
            $temp_service_data = array();
            $bpa_services_categories_data = array();
            $all_services_data = array();
            $bookingpress_display_service_description = $BookingPress->bookingpress_get_customize_settings('display_service_description', 'booking_form');

            $all_service_data = apply_filters( 'bookingpress_remove_disabled_services', $all_service_data );

            foreach ( $all_service_data as $service_key => $service_val ) {
                $temp_service_data[ $service_key ] = $all_service_data[ $service_key ];
                $temp_service_data[ $service_key ]['service_position'] = $service_val['bookingpress_service_position'];
                $temp_service_data[ $service_key ]['service_price_without_currency'] = $service_val['bookingpress_service_price'];
                $temp_service_data[ $service_key ]['bookingpress_service_price']     = $BookingPress->bookingpress_price_formatter_with_currency_symbol($service_val['bookingpress_service_price']);
                $temp_service_data[ $service_key ]['bookingpress_service_name'] = stripslashes($service_val['bookingpress_service_name']);
                
                $service_id                              = $service_val['bookingpress_service_id'];
                $service_meta_details                    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = %d AND bookingpress_servicemeta_name = 'service_image_details'", $service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_servicesmeta is table name defined globally. False Positive alarm
                $service_img_details                     = ! empty($service_meta_details['bookingpress_servicemeta_value']) ? maybe_unserialize($service_meta_details['bookingpress_servicemeta_value']) : array();
                $temp_service_data[ $service_key ]['img_url'] = ! empty($service_img_details[0]['url']) ? $service_img_details[0]['url'] : BOOKINGPRESS_URL . '/images/placeholder-img.jpg';
                
                $default_service_description = ! empty($service_val['bookingpress_service_description']) ? $service_val['bookingpress_service_description'] : '';
                
                if ($bookingpress_display_service_description == 'false' ) {
                    $temp_service_data[ $service_key ]['display_read_more_less']           = 1;
                    $temp_service_data[ $service_key ]['bookingpress_service_description'] = stripslashes_deep($default_service_description);
                    if (strlen($default_service_description) > 140 ) {
                        $temp_service_data[ $service_key ]['bookingpress_service_description_with_excerpt'] = stripslashes(substr($default_service_description, 0, 140));
                        $temp_service_data[ $service_key ]['display_details_more']                          = 0;
                        $temp_service_data[ $service_key ]['display_details_less']                          = 1;
                    } else {
                        $temp_service_data[ $service_key ]['display_read_more_less'] = 0;
                    }
                }
                if( $service_val['bookingpress_category_id'] == $default_service_category ){
                    $service_data[ $service_key ] = $temp_service_data[ $service_key ];
                }
                if( empty( $bpa_services_categories_data[ $service_val['bookingpress_category_id'] ] ) ){
                    $bpa_services_categories_data[ $service_val['bookingpress_category_id'] ] = array(); 
                }
                $all_services_data[ $service_key ] = $temp_service_data[ $service_key ];
                $bpa_services_categories_data[ $service_val['bookingpress_category_id'] ][] = $temp_service_data[ $service_key ];
            }

            if ($bookingpress_display_service_description == 'false' ) {
                $bookingpress_front_vue_data_fields['display_service_description'] = '1';
            }

            $bookingpress_front_vue_data_fields['services_data'] = $service_data;
            $bookingpress_front_vue_data_fields['bpa_services_data_from_categories'] = $bpa_services_categories_data;
            $bookingpress_front_vue_data_fields['all_services_data'] = $all_services_data;

            $bookingpress_is_uncategorized_service_added = 0;
            foreach($all_services_data as $ser_key => $ser_val){
                if(empty($ser_val['bookingpress_category_id']) && empty($bookingpress_service) && empty($bookingpress_category)){
                    $bookingpress_is_uncategorized_service_added = 1;
                    break;
                }else if(empty($ser_val['bookingpress_category_id']) && !empty($bookingpress_service) && empty($bookingpress_category)){
                    $bookingpress_is_uncategorized_service_added = 1;
                    break;
                }
            }
            $bookingpress_front_vue_data_fields['is_uncategorize_service_added'] = $bookingpress_is_uncategorized_service_added;
            $default_service_id = 0;
            $default_service_name = $default_price = $default_service_duration = $default_service_duration_unit = $default_price_with_currency = "";
            $service_data= array_values($service_data);
            if(!empty($service_data)) {
                foreach($service_data as $key => $val) {
                    if((!empty($selected_service) ) ) {                    
                        if($selected_service == $val['bookingpress_service_id']) {
                            $default_service_id                                  = ! empty($val['bookingpress_service_id']) ? $val['bookingpress_service_id'] : 0;
                            $default_service_name                                = ! empty($val['bookingpress_service_name']) ? stripslashes_deep($val['bookingpress_service_name']) : '';
                            $default_price                                       = ! empty($val['bookingpress_service_price']) ? $val['bookingpress_service_price'] : 0;
                            $default_price_with_currency                         = ! empty($val['service_price_without_currency']) ? $val['service_price_without_currency'] : 0;
                            $default_service_duration_unit                         = ! empty($val['bookingpress_service_duration_unit']) ? $val
                            ['bookingpress_service_duration_unit'] : '';
                            $default_service_duration                         = ! empty($val['bookingpress_service_duration_val']) ? $val
                            ['bookingpress_service_duration_val'] : '';                            
                            
                        }
                    } else {
                        $default_service_id   = ! empty($service_data[0]['bookingpress_service_id']) ? $service_data[0]['bookingpress_service_id'] : $all_services_data[0]['bookingpress_service_id'];
                        $default_service_name  = ! empty($service_data[0]['bookingpress_service_name']) ? stripslashes($service_data[0]['bookingpress_service_name']) : '';
                        $default_price   = ! empty($service_data[0]['bookingpress_service_price']) ? $service_data[0]['bookingpress_service_price'] : 0;
                        $default_price_with_currency  = ! empty($service_data[0]['service_price_without_currency']) ? $service_data[0]['service_price_without_currency'] : 0;
                        $default_service_duration_unit= ! empty($service_data[0]['bookingpress_service_duration_unit']) ? $service_data[0]
                        ['bookingpress_service_duration_unit'] : '';
                        $default_service_duration                         = ! empty($service_data[0]['bookingpress_service_duration_val']) ? $service_data[0]
                        ['bookingpress_service_duration_val'] : '';                            
                    }
                }
            }

            if(empty($default_service_id) && !empty($selected_service)){
                //If default no service selected and selected service parameter pass from booking form shortcode then this condition will executed
                foreach($all_services_data as $key => $val) {
                    if((!empty($selected_service) ) ) {                    
                        if($selected_service == $val['bookingpress_service_id']) {
                            $default_service_id                                  = ! empty($val['bookingpress_service_id']) ? $val['bookingpress_service_id'] : 0;
                            $default_service_name                                = ! empty($val['bookingpress_service_name']) ? stripslashes_deep($val['bookingpress_service_name']) : '';
                            $default_price                                       = ! empty($val['bookingpress_service_price']) ? $val['bookingpress_service_price'] : 0;
                            $default_price_with_currency                         = ! empty($val['service_price_without_currency']) ? $val['service_price_without_currency'] : 0;
                            $default_service_duration_unit                         = ! empty($val['bookingpress_service_duration_unit']) ? $val
                            ['bookingpress_service_duration_unit'] : '';
                            $default_service_duration                         = ! empty($val['bookingpress_service_duration_val']) ? $val
                            ['bookingpress_service_duration_val'] : '';
                        }
                    }
                }
            }

            $bookingpress_is_hide_category_service_selection = $BookingPress->bookingpress_get_customize_settings('hide_category_service_selection', 'booking_form');

            $bpa_move_from_service_selection_step = false;

			if ( $bookingpress_is_hide_category_service_selection == 'true' || ! empty( $selected_service ) || ( !empty($bookingpress_service) && (count($all_services_data) == 1) && empty($bookingpress_category) ) ) {
                // If hide category service step option enabled then by default service selected
                // If only 1 service display from shortcode parameter then by default that 1 service also selected automatically
                // If there is any service selected from parameter then also service automatically selected
                
                $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service']               = intval($default_service_id);
                $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service_name']          = $default_service_name;
                $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service_price']         = $default_price;
                $bookingpress_front_vue_data_fields['appointment_step_form_data']['service_price_without_currency'] = $default_price_with_currency;
                $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service_duration_unit'] = $default_service_duration_unit;
                $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service_duration'] = $default_service_duration;

                $bookingpress_front_vue_data_fields['displayResponsiveCalendar'] = "1";
                $bpa_move_from_service_selection_step = true;
            }

            $on_site_payment = $BookingPress->bookingpress_get_settings('on_site_payment', 'payment_setting');
            $paypal_payment  = $BookingPress->bookingpress_get_settings('paypal_payment', 'payment_setting');

            $bookingpress_front_vue_data_fields['on_site_payment'] = $on_site_payment;
            $bookingpress_front_vue_data_fields['paypal_payment']  = $paypal_payment;

            $bookingpress_total_configure_gateways = 0;
            $bookingpress_is_only_onsite_enabled   = 0;
            if (( $on_site_payment == 'true' || $on_site_payment == '1' ) && ( $paypal_payment == 'true' || $paypal_payment == '1' ) ) {
                $bookingpress_total_configure_gateways = 2;
                $bookingpress_is_only_onsite_enabled   = 0;
            } elseif (( $on_site_payment == 'true' || $on_site_payment == '1' ) && ( $paypal_payment == 'false' || empty($paypal_payment) ) ) {
                $bookingpress_total_configure_gateways = 1;
                $bookingpress_is_only_onsite_enabled   = 1;
            } elseif (( $on_site_payment == 'false' || empty($on_site_payment) ) && ( $paypal_payment == 'true' || $paypal_payment == '1' ) ) {
                $bookingpress_total_configure_gateways = 1;
                $bookingpress_is_only_onsite_enabled   = 0;
            }

            $bookingpress_front_vue_data_fields['total_configure_gateways'] = $bookingpress_total_configure_gateways;
            $bookingpress_front_vue_data_fields['is_only_onsite_enabled']   = $bookingpress_is_only_onsite_enabled;

            if ($bookingpress_is_only_onsite_enabled == 1 ) {
                $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_payment_method'] = 'on-site';
            }

            if ($bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_payment_method'] == '' && ( $paypal_payment == 'true' ) ) {
                $bookingpress_front_vue_data_fields['paypal_payment'] = 'paypal';
            }

            if (is_user_logged_in() ) {
                $current_user_id               = get_current_user_id();
                $bookingpress_current_user_obj = new WP_User($current_user_id);

                $get_current_user_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id = %d AND bookingpress_user_type = 2", $current_user_id ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
                if (! empty($get_current_user_data) ) {
                        $bookingpress_firstname = stripslashes_deep($get_current_user_data['bookingpress_user_firstname']);
                        $bookingpress_lastname = stripslashes_deep($get_current_user_data['bookingpress_user_lastname']);

                        $bookingpress_customername = stripslashes_deep($get_current_user_data['bookingpress_user_login']);
                        if(!empty($bookingpress_firstname) || !empty($bookingpress_lastname)){
                            $bookingpress_customername = $bookingpress_firstname." ".$bookingpress_lastname;
                        }

                        if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_name'])) {                            
                            $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_name'] = stripslashes_deep($bookingpress_customername);
                        }
                        if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone'])) {                            
                            $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone'] = $get_current_user_data['bookingpress_user_phone'];
                        }
                        if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_email'])) {
                            $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_email'] = stripslashes_deep($get_current_user_data['bookingpress_user_email']);
                        }
                        if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_firstname'])) {
                            $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_firstname'] = stripslashes_deep($bookingpress_firstname);
                        }
                        if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_lastname'])) {
                            $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_lastname']  = stripslashes_deep($bookingpress_lastname);
                        }
                } elseif (! empty($current_user_id) && ! empty($bookingpress_current_user_obj) ) {
                    $bookingpress_customer_name  = ! empty($bookingpress_current_user_obj->data->user_login) ? $bookingpress_current_user_obj->data->user_login : '';
                    $bookingpress_customer_email = ! empty($bookingpress_current_user_obj->data->user_email) ? $bookingpress_current_user_obj->data->user_email : '';
                    $bookingpress_firstname      = get_user_meta($current_user_id, 'first_name', true);
                    $bookingpress_lastname       = get_user_meta($current_user_id, 'last_name', true);
                   
                    if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_name'])) {
                        $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_name'] = stripslashes_deep($bookingpress_customer_name);
                    }                    
                    if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_email'])) {
                        $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_email']     = stripslashes_deep($bookingpress_customer_email);
                    }
                    if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_firstname'])) {
                        $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_firstname'] = stripslashes_deep($bookingpress_firstname);
                    }
                    if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_lastname'])) {
                        $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_lastname']  = stripslashes_deep($bookingpress_lastname);
                    }
                }
            }
            $bookingpress_phone_mandatory_option = $BookingPress->bookingpress_get_settings('phone_number_mandatory', 'general_setting');
            if (! empty($bookingpress_phone_mandatory_option) && $bookingpress_phone_mandatory_option == 'true' ) {
                $mandatory_field_data = array(
                'required' => true,
                'message'  => __('Please enter customer phone number', 'bookingpress-appointment-booking'),
                'trigger'  => 'blur',
                );
                $bookingpress_front_vue_data_fields['customer_details_rule']['customer_phone'] = $mandatory_field_data;
            }

            $bookingpress_phone_country_option = $BookingPress->bookingpress_get_settings('default_phone_country_code', 'general_setting');
            $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone_country'] = $bookingpress_phone_country_option;
            $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone_dial_code'] = '';
            $bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_customer_timezone'] = $bookingpress_global_options->bookingpress_get_site_timezone_offset();

            $bookingpress_front_vue_data_fields['bookingpress_tel_input_props'] = array(
                'defaultCountry' => $bookingpress_phone_country_option,
                'inputOptions'   => array(
                    'placeholder' => '',
                ),
                'validCharactersOnly' => true,
            );

            $default_daysoff_details = $BookingPress->bookingpress_get_default_dayoff_dates();
            $disabled_date           = implode(',', $default_daysoff_details);
            $bookingpress_front_vue_data_fields['days_off_disabled_dates'] = $disabled_date;

            $bookingpress_selected_date = $BookingPress->bookingpress_select_date_before_load();
            if (! empty($bookingpress_selected_date) ) {
                $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_date'] = $bookingpress_selected_date;
            }
            $bookingpress_front_vue_data_fields['bookingpress_activate_payment_gateway_counter'] = 0;

            if($bookingpress_front_vue_data_fields['bookingpress_activate_payment_gateway_counter'] > 0) {
				$bookingpress_front_vue_data_fields['is_only_onsite_enabled']   = 0;
			}

            $bookingpress_customize_settings = $BookingPress->bookingpress_get_customize_settings(
                array(
                    'service_title',
                    'datetime_title',
                    'basic_details_title',
                    'summary_title',
                    'hide_category_service_selection'
                ),
                'booking_form'
            );    
            
            $bookingpress_hide_category_service_selection = stripslashes_deep($bookingpress_customize_settings['hide_category_service_selection']);
            $bookingpress_is_loaded_from_share_url = false;
            if(!empty($_GET['s_id']) && (isset($_GET['allow_modify']) && $_GET['allow_modify'] == '0' ) ){
                $bookingpress_hide_category_service_selection = 'true';
                $bookingpress_is_loaded_from_share_url = true;
            }else if(!empty($_GET['s_id']) && (isset($_GET['allow_modify']) && $_GET['allow_modify'] == '1' ) ){
                $bookingpress_hide_category_service_selection = 'false';
                $bookingpress_is_loaded_from_share_url = true;
            }
            
            if( 'true' == $bookingpress_hide_category_service_selection && empty( $selected_service ) && true == $bookingpress_is_loaded_from_share_url ){
                $bookingpress_hide_category_service_selection = 'false';
            }

            $bookingpress_service_tab_name  = stripslashes_deep($bookingpress_customize_settings['service_title']);
            $bookingpress_datetime_tab_name = stripslashes_deep($bookingpress_customize_settings['datetime_title']);//
            $bookingpress_basic_details_tab_name  = stripslashes_deep($bookingpress_customize_settings['basic_details_title']);
            $bookingpress_summary_tab_name = stripslashes_deep($bookingpress_customize_settings['summary_title']);

            $no_service_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_service_selected_for_the_booking', 'message_setting');

            $no_appointment_date_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_appointment_date_selected_for_the_booking', 'message_setting');

            $no_appointment_time_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_appointment_time_selected_for_the_booking', 'message_setting');

            $no_payment_method_is_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_payment_method_is_selected_for_the_booking', 'message_setting');

            $bookingpress_sidebar_steps_data = array(
                'service' => array(
                    'tab_name' => $bookingpress_service_tab_name,
                    'tab_value' => 'service',
                    'tab_icon' => 'dns',
                    'next_tab_name' => 'datetime',
                    'next_tab_label' => '',
                    'previous_tab_name' => '',
                    'validate_fields' => array(
                        'selected_service',
                    ),
                    'auto_focus_tab_callback' => array(),
                    'validation_msg' => array(
                        'selected_service' => $no_service_selected_for_the_booking,
                    ),
                    'is_allow_navigate' => 1,
                    'is_navigate_to_next' => $bpa_move_from_service_selection_step,
                    'is_display_step' => 1,
                ),
                'datetime' => array(
                    'tab_name' => $bookingpress_datetime_tab_name,
                    'tab_value' => 'datetime',
                    'tab_icon' => 'date_range',
                    'next_tab_name' => 'basic_details',
                    'previous_tab_name' => 'service',
                    'auto_focus_tab_callback' => array(
                        'bookingpress_disable_date' => array()
                    ),
                    'validate_fields' => array(
                        'selected_date',
                        'selected_start_time',
                    ),
                    'validation_msg' => array(
                        'selected_date' => $no_appointment_date_selected_for_the_booking,
                        'selected_start_time' => $no_appointment_time_selected_for_the_booking,
                    ),
                    'is_allow_navigate' => 0,
                    'is_display_step' => 1,
                    'is_navigate_to_next' => false
                ),
                'basic_details' => array(
                    'tab_name' => $bookingpress_basic_details_tab_name,
                    'tab_value' => 'basic_details',
                    'tab_icon' => 'article',
                    'auto_focus_tab_callback' => array(),
                    'next_tab_name' => 'summary',
                    'previous_tab_name' => 'datetime',
                    'validate_fields' => array(),
                    'is_allow_navigate' => 0,
                    'is_display_step' => 1,
                    'is_navigate_to_next' => false
                ),
                'summary' => array(
                    'tab_name' => $bookingpress_summary_tab_name,
                    'tab_value' => 'summary',
                    'tab_icon' => 'assignment_turned_in',
                    'next_tab_name' => 'summary',
                    'auto_focus_tab_callback' => array(),
                    'previous_tab_name' => 'basic_details',
                    'validate_fields' => array(),
                    'is_allow_navigate' => 0,
                    'is_display_step' => 1,
                    'is_navigate_to_next' => false
                ),
            );
            
            
            if($bookingpress_hide_category_service_selection == 'true'){
                if( $BookingPress->bpa_is_pro_exists() && $BookingPress->bpa_is_pro_active() ){
                    if( !empty( $BookingPress->bpa_pro_plugin_version() ) && version_compare( $BookingPress->bpa_pro_plugin_version(), '1.5', '>' ) ){
                        $bookingpress_sidebar_steps_data['service']['is_display_step'] = 0;
                    } else {
                         /** remove service step when pro version is lower than 1.5 */
                        unset( $bookingpress_sidebar_steps_data['service'] );
                    }
                } else {
                    $bookingpress_sidebar_steps_data['service']['is_display_step'] = 0;
                }
            }
            
            $bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data'] = $bookingpress_sidebar_steps_data;
            
            $bookingpress_front_vue_data_fields = apply_filters('bookingpress_frontend_apointment_form_add_dynamic_data', $bookingpress_front_vue_data_fields);
            
            $bookingpress_dynamic_data_fields = wp_json_encode($bookingpress_front_vue_data_fields);
            return $bookingpress_dynamic_data_fields;
        }
        
        /**
         * Callback function for [bookingpress_appointment_calendar_integration] shortcode
         *
         * @param  mixed $atts
         * @param  mixed $content
         * @param  mixed $tag
         * @return void
         */
        function bookingpress_booking_calendar_options($atts, $content, $tag) {
            global $wpdb, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries;
            $defaults = array(
                'gateways'  => 'google,yahoo,outlook,ical',
                'gateways_label' => '',
                'button_spacing' => '12'
            );
            $args = shortcode_atts($defaults, $atts, $tag);
            extract($args);       
            $bookingpress_calendar_list = array();                
            $bookingpress_default_arr = array('google' => __('Google Calendar','bookingpress-appointment-booking'),
                'yahoo'=> __('Yahoo Calendar','bookingpress-appointment-booking'),
                'outlook'=>  __('Outlook Calendar','bookingpress-appointment-booking'),
                'ical'=>  __('iCal Calendar','bookingpress-appointment-booking'),
            );   
            $bookingpress_default_arr2 = array('google' => 'google_calendar' ,
                'yahoo'=> 'yahoo_calendar' ,
                'outlook'=> 'outlook_calendar',
                'ical'=> 'ical_calendar',
            );   
            if(!empty($gateways)) {                
                $gateways = explode(',',$gateways);        
                $gateways_label = explode(',',$gateways_label); 
                foreach($gateways as $key => $value ) {                              
                    if(array_key_exists($value,$bookingpress_default_arr) ) {
                        $label_value =!empty($gateways_label[$key]) ? sanitize_text_field($gateways_label[$key]) : $bookingpress_default_arr[$value];        
                        if(!empty($label_value)) {
                            $bookingpress_calendar_list[] = array(                                                
                                'value' => $bookingpress_default_arr2[$value],                        
                                'name' => $label_value, 
                            );
                        }
                    }
                }          
            }        
            $this->bookingpress_calendar_list = wp_json_encode($bookingpress_calendar_list);              

			global $BookingPress;
			$BookingPress->set_front_css( 1 );
			$BookingPress->set_front_js( 1 );
            $BookingPress->bookingpress_load_booking_form_custom_css();

            $bookingpress_is_render_calendar = 1;
            $bookingpress_calendar_html = "";

            if(!empty($_GET['appointment_id'])){
                $bookingpress_nonce_val = !empty($_GET['bp_tp_nonce']) ? sanitize_text_field($_GET['bp_tp_nonce']) : '';
                $bookingpress_verification_hash = !empty($_GET['appointment_id']) ? md5(base64_decode(sanitize_text_field($_GET['appointment_id']))) : '';
                $bookingpress_nonce_verification = wp_verify_nonce($bookingpress_nonce_val, 'bpa_nonce_url-'.$bookingpress_verification_hash);

                if(!$bookingpress_nonce_verification){
                    $bookingpress_is_render_calendar = 0;
                }
            }
            
            if($bookingpress_is_render_calendar){
                $bookingpress_calendar_html = '<div id="bpa-front-module-calendar-integration">';
                    $bookingpress_calendar_html     .= '<div class="bpa-front-module--atc-wrapper">';					
                                $bookingpress_calendar_html .= '								
                                        <div v-for="item in bookingpress_calendar_list" :class="\'bpa-front-module--atc__item bpa-fm--atc__\'+item.value" style="margin:0px '.$button_spacing.'px '.$button_spacing.'px 0px">
                                            <el-button class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--full-width" id="bookingpress_ical_calendar" v-if="item.value == \'ical_calendar\'">
                                                <span>
                                                    <svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <g clip-path="url(#clip0_1235_2762)">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.21165 1.39313C8.26508 0.00788564 9.72934 0 9.72934 0C9.72934 0 9.94793 1.30376 8.89977 2.55758C7.78313 3.89814 6.51375 3.67734 6.51375 3.67734C6.51375 3.67734 6.2741 2.6233 7.21165 1.39313Z" fill="black"/>
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.64714 4.59251C7.18965 4.59251 8.19568 3.84863 9.50456 3.84863C11.7589 3.84863 12.6438 5.44942 12.6438 5.44942C12.6438 5.44942 10.9109 6.33524 10.9109 8.48014C10.9109 10.901 13.0704 11.7369 13.0704 11.7369C13.0704 11.7369 11.5614 15.9794 9.52037 15.9794C8.58281 15.9794 7.85595 15.3486 6.86836 15.3486C5.86233 15.3486 4.86421 16.0031 4.21372 16.0031C2.35178 16.0004 0 11.9787 0 8.743C0 5.55982 1.99098 3.89069 3.85818 3.89069C5.07226 3.89332 6.01508 4.59251 6.64714 4.59251Z" fill="black"/>
                                                        </g>
                                                        <defs>
                                                        <clipPath id="clip0_1235_2762">
                                                        <rect width="13.0704" height="16" fill="white"/>
                                                        </clipPath>
                                                        </defs>
                                                    </svg>
                                                </span>  
                                                {{ item.name}}
                                            </el-button>           
                                            <el-button class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--full-width" id="bookingpress_google_calendar" v-if="item.value == \'google_calendar\'">
                                                <span>
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15.6444 8.17812C15.6444 7.64479 15.5556 7.02257 15.4667 6.57812H8V9.68924H12.2667C12.0889 10.667 11.5556 11.467 10.6667 12.0892V14.1337H13.3333C14.8444 12.7115 15.6444 10.5781 15.6444 8.17812Z" fill="#4285F4"/>
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.99978 15.9996C10.1331 15.9996 11.9998 15.2885 13.3331 14.0441L10.6664 12.0885C9.95534 12.5329 9.06645 12.8885 7.99978 12.8885C5.95534 12.8885 4.17756 11.4663 3.55534 9.59961H0.888672V11.5552C2.13312 14.2218 4.88867 15.9996 7.99978 15.9996Z" fill="#34A853"/>
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.55556 9.511C3.37778 9.06656 3.28889 8.53322 3.28889 7.99989C3.28889 7.46656 3.37778 6.93322 3.55556 6.48878V4.44434H0.888889C0.355556 5.511 0 6.75545 0 7.99989C0 9.24434 0.266667 10.4888 0.888889 11.5554L3.55556 9.511Z" fill="#FBBC05"/>
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.99978 3.2C9.15534 3.2 10.222 3.64444 11.022 4.35556L13.3331 2.04444C11.9998 0.8 10.1331 0 7.99978 0C4.88867 0 2.13312 1.77778 0.888672 4.44444L3.55534 6.48889C4.17756 4.62222 5.95534 3.2 7.99978 3.2Z" fill="#EA4335"/>
                                                    </svg>
                                                </span>
                                                {{ item.name}}
                                            </el-button>                                             
                                            <el-button class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--full-width" id="bookingpress_outlook_calendar" v-if="item.value ==  \'outlook_calendar\'">                                                
                                                <span>
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <g clip-path="url(#clip0_1235_2768)">
                                                        <path d="M7.57897 0H0V7.57897H7.57897V0Z" fill="#F25022"/>
                                                        <path d="M7.57897 8.4209H0V15.9999H7.57897V8.4209Z" fill="#00A4EF"/>
                                                        <path d="M16.0008 0H8.42188V7.57897H16.0008V0Z" fill="#7FBA00"/>
                                                        <path d="M16.0008 8.4209H8.42188V15.9999H16.0008V8.4209Z" fill="#FFB900"/>
                                                        </g>
                                                        <defs>
                                                        <clipPath id="clip0_1235_2768">
                                                        <rect width="16" height="16" fill="white"/>
                                                        </clipPath>
                                                        </defs>
                                                    </svg>
                                                </span>                                     
                                                {{ item.name}}
                                            </el-button>                                             
                                            <el-button class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--full-width" id="bookingpress_yahoo_calendar" v-if="item.value == 
                                            \'yahoo_calendar\'">                                                
                                                <span>
                                                    <svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <g clip-path="url(#clip0_1235_2766)">
                                                        <path d="M0 3.89506H3.43247L5.43118 9.00836L7.45588 3.89506H10.7976L5.76558 16H2.40215L3.77968 12.7924L0.000106295 3.89506H0ZM14.6891 7.98076H10.9461L14.2682 0L17.9975 0.000159442L14.6891 7.98076V7.98076ZM11.9266 8.74459C13.075 8.74459 14.006 9.67558 14.006 10.8238C14.006 11.9721 13.075 12.9031 11.9266 12.9031C10.7783 12.9031 9.84751 11.9721 9.84751 10.8238C9.84751 9.67558 10.7784 8.74459 11.9266 8.74459Z" fill="#5F01D1"/>
                                                        </g>
                                                        <defs>
                                                        <clipPath id="clip0_1235_2766">
                                                        <rect width="17.9975" height="16" fill="white"/>
                                                        </clipPath>
                                                        </defs>
                                                    </svg>  
                                                </span>                                      
                                                {{ item.name}}
                                            </el-button>  
                                        </div>';
                    $bookingpress_calendar_html             .= '</div>';
                $bookingpress_calendar_html .= '</div>';
        
                add_action(
                    'wp_footer',
                    function() {
                        $appointment_id = ( ! empty( $_REQUEST['appointment_id'] ) ? intval( base64_decode( $_REQUEST['appointment_id'] ) ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                        ?>
                    <script>
                        wp.hooks.addAction('bpa_calendar_js_init' , 'bookingpress-appointment-booking-pro', bookingpress_load_calendar_list, 10, 1 );
                        function bookingpress_load_calendar_list(bookingpress_appointment_id){
                            var app = new Vue({
                                el:'#bpa-front-module-calendar-integration',
                                data(){
                                    var bookingpress_return_data = {};
                                    bookingpress_return_data['bookingpress_calendar_list'] = <?php echo _wp_specialchars($this->bookingpress_calendar_list,ENT_NOQUOTES,'UTF-8', true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
                                    bookingpress_return_data['bookingpress_selected_calendar'] = '';
                                    bookingpress_return_data['bookingpress_appointment_id'] = '<?php echo esc_html($appointment_id); ?>';
                                    bookingpress_return_data['bookingpress_calendar_link'] = '';
                                    return bookingpress_return_data;
                                },
                                mounted(){
                                    const vm = this;
                                    document.getElementById("bpa-front-module-calendar-integration").style.display = "block";
                                    document.getElementById("bookingpress_ical_calendar").addEventListener("click", function(e){
                                        var bookingpress_calendar_link = "<?php echo esc_url(BOOKINGPRESS_HOME_URL)."?page=bookingpress_download&action=generate_ics&state=".esc_html(wp_create_nonce('bookingpress_calendar_ics'))."&appointment_id="; ?>";
                                        bookingpress_calendar_link = bookingpress_calendar_link + bookingpress_appointment_id;
                                        bookingpress_calendar_link = wp.hooks.applyFilters( 'bookingpress_change_calendar_url', bookingpress_calendar_link, 'ical', bookingpress_appointment_id );
                                        window.open(bookingpress_calendar_link, '_self');
                                    });
                                    document.getElementById("bookingpress_google_calendar").addEventListener("click", function(e){
                                        let googleCalendarString = 'https://www.google.com/calendar/render?action=TEMPLATE&text=';
                                        
                                        var bkp_wpnonce_pre = "<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>";
                                        var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                                        if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
                                        {
                                            bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
                                        }
                                        else {
                                            bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
                                        }
                                        var postData = { action:"bookingpress_get_appointment_details_for_calendar", bookingpress_appointment_id: bookingpress_appointment_id, _wpnonce:bkp_wpnonce_pre_fetch };
                                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                                        .then( function (response) {
                                            googleCalendarString = googleCalendarString + response.data.google_calendar_link;
                                            googleCalendarString = wp.hooks.applyFilters( 'bookingpress_change_calendar_url', googleCalendarString, 'google_calendar', bookingpress_appointment_id );
                                            window.open(googleCalendarString, '_blank');
                                        }.bind(this) )
                                        .catch( function (error) {
                                            vm.bookingpress_set_error_msg(error)
                                        });
                                    });
                                    document.getElementById("bookingpress_yahoo_calendar").addEventListener("click", function(e){
                                        let yahooCalendarString = 'http://calendar.yahoo.com/?v=60&view=d&type=20&title=';
                                        var bkp_wpnonce_pre = "<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>";
                                        var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                                        if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
                                        {
                                            bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
                                        }
                                        else {
                                            bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
                                        }
                                        var postData = { action:"bookingpress_get_appointment_details_for_calendar", bookingpress_appointment_id: bookingpress_appointment_id, _wpnonce:bkp_wpnonce_pre_fetch };
                                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                                        .then( function (response) {
                                            yahooCalendarString = yahooCalendarString + response.data.yahoo_calendar_link;
                                            yahooCalendarString = wp.hooks.applyFilters( 'bookingpress_change_calendar_url', yahooCalendarString, 'yahoo_calendar', bookingpress_appointment_id );
                                            window.open(yahooCalendarString, '_blank');
                                        }.bind(this) )
                                        .catch( function (error) {
                                            vm.bookingpress_set_error_msg(error)
                                        });
                                    });
                                    document.getElementById("bookingpress_outlook_calendar").addEventListener("click", function(e){
                                        var bookingpress_calendar_link = "<?php echo esc_url(BOOKINGPRESS_HOME_URL)."?page=bookingpress_download&action=generate_ics&state=".esc_html(wp_create_nonce('bookingpress_calendar_ics'))."&appointment_id="; ?>";
                                        bookingpress_calendar_link = bookingpress_calendar_link + bookingpress_appointment_id;
                                        bookingpress_calendar_link = wp.hooks.applyFilters( 'bookingpress_change_calendar_url', bookingpress_calendar_link, 'outlook_calendar', bookingpress_appointment_id );
                                        window.open(bookingpress_calendar_link, '_self');
                                    });
                                    <?php do_action( 'bookingpress_calendar_integration_events' ); ?>
                                },
                            });
                        }

                        var bookingpress_redirection_mode = '<?php echo esc_html(!empty($appointment_id) ? 'external_redirection' : 'in_built'); ?>';
                        if(bookingpress_redirection_mode == "external_redirection"){
                            var bookingpress_appointment_id = '<?php echo esc_html($appointment_id); ?>';
                            wp.hooks.doAction("bpa_calendar_js_init", bookingpress_appointment_id);
                        }
                    </script>
                        <?php
                    },
                    100
                );
            }

			return $bookingpress_calendar_html;
		}
        
        /**
         * Download ICS file from if it enable in email notification
         *
         * @return void
         */
        function bookingpress_download_ics_file() {

			if ( ! empty( $_GET['page'] ) && 'bookingpress_download' == $_GET['page'] && ! empty( $_GET['action'] ) && 'generate_ics' == $_GET['action'] ) {

				$nonce = ! empty( $_GET['state'] ) ? sanitize_text_field( $_GET['state'] ) : '';
				if ( ! wp_verify_nonce( $nonce, 'bookingpress_calendar_ics' ) ) {
					return false;
				}

				if ( empty( $_GET['appointment_id'] ) ) {
					return false;
				}

				$appointment_id = intval( $_GET['appointment_id'] );

				global $wpdb,$tbl_bookingpress_entries, $tbl_bookingpress_appointment_bookings, $BookingPress, $bookingpress_global_options;
				// $appointment_id = base64_decode( $_REQUEST['appointment_id'] );
				$bookingpress_entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = %d", $appointment_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm

				if ( ! empty( $bookingpress_entry_details ) ) {
					$bookingpress_service_id         = $bookingpress_entry_details['bookingpress_service_id'];
					$bookingpress_appointment_date   = $bookingpress_entry_details['bookingpress_appointment_date'];
					$bookingpress_appointment_time   = $bookingpress_entry_details['bookingpress_appointment_time'];
					$bookingpress_appointment_status = $bookingpress_entry_details['bookingpress_appointment_status'];

					$appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_service_id = %d AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_status = %s", $bookingpress_service_id, $bookingpress_appointment_date, $bookingpress_appointment_time, $bookingpress_appointment_status ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

					if ( ! empty( $appointment_data ) ) {
						$service_id              = intval( $appointment_data['bookingpress_service_id'] );

						$bookingpress_start_time = sanitize_text_field( $appointment_data['bookingpress_appointment_time'] );
                       				 $bookingpress_end_time   = sanitize_text_field( $appointment_data['bookingpress_appointment_end_time'] );

						$bookingpress_start_time = date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . 'T' . date( 'His', strtotime( $bookingpress_start_time ) );

                        			$bookingpress_end_time = date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . 'T' . date( 'His', strtotime( $bookingpress_end_time ) );
						$user_timezone             = $bookingpress_global_options->bookingpress_get_site_timezone_offset();
						$bookingpress_service_name = ! empty( $appointment_data['bookingpress_service_name'] ) ? sanitize_text_field( $appointment_data['bookingpress_service_name'] ) : '';
					}

					$booking_stime = $this->bookingpress_convert_date_time_to_utc( $appointment_data['bookingpress_appointment_date'], $bookingpress_start_time );
					$booking_etime = $this->bookingpress_convert_date_time_to_utc( $appointment_data['bookingpress_appointment_date'], $bookingpress_end_time );
					$current_dtime = $this->bookingpress_convert_date_time_to_utc( date( 'm/d/Y' ), 'g:i A' );

					$string  = "BEGIN:VCALENDAR\r\n";
					$string .= "VERSION:2.0\r\n";
					$string .= 'PRODID:BOOKINGPRESS APPOINTMENT BOOKING\\\\' . get_bloginfo('title') . "\r\n";
					$string .= "X-PUBLISHED-TTL:P1W\r\n";
					$string .= "BEGIN:VEVENT\r\n";
					$string .= 'UID:' . md5( time() ) . "\r\n";
					$string .= 'DTSTART:' . $booking_stime . "\r\n";
					$string .= "SEQUENCE:0\r\n";
					$string .= "TRANSP:OPAQUE\r\n";
					$string .= "DTEND:{$booking_etime}\r\n";
					$string .= "SUMMARY:{$bookingpress_service_name}\r\n";
					$string .= "CLASS:PUBLIC\r\n";
					$string .= "DTSTAMP:{$current_dtime}\r\n";
					$string .= "END:VEVENT\r\n";
					$string .= "END:VCALENDAR\r\n";
                    
                    $string  = apply_filters( 'bpa_add_timezone_parameters_for_ics', $string, $appointment_data );
                    
					header( 'Content-Type: text/calendar; charset=utf-8' );
					header( 'Content-Disposition: attachment; filename="cal.ics"' );


					echo $string; //phpcs:ignore
				}

				die;

			}
		}
                
        /**
         * Convert Date and Time to UTC format
         *
         * @param  mixed $date      Convert date
         * @param  mixed $time      Convert time
         * @param  mixed $formated  Formatted date time should be return
         * @return void
         */
        function bookingpress_convert_date_time_to_utc( $date, $time, $formated = false ) {

			if ( empty( $date ) ) {
				$date = date( 'm/d/Y' );
			}

			if ( empty( $time ) ) {
				$time = date( 'g:i A' );
			}

			$bookingpress_time = date( 'm/d/Y', strtotime( $date ) ) . ' ' . date( 'g:i A', strtotime( $time ) );

			$tz_from = wp_timezone_string();
			$tz_to   = 'UTC';
			if ( $formated ) {
				$format = 'Y-m-d\TH:i:s\Z';
			} else {
				$format = 'Ymd\THis\Z';
			}

			$start_dt = new DateTime( $bookingpress_time, new DateTimeZone( $tz_from ) );
			$start_dt->setTimeZone( new DateTimeZone( $tz_to ) );
			$bookingpress_time = $start_dt->format( $format );

			return $bookingpress_time;

		}
        
        /**
         * Helper variables for Booking Form Shortcode
         *
         * @param  mixed $bookingpress_front_booking_dynamic_helper_vars
         * @return void
         */
        function bookingpress_booking_dynamic_helper_vars_func( $bookingpress_front_booking_dynamic_helper_vars )
        {
            global $bookingpress_global_options;
            $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_locale_lang = $bookingpress_options['locale'];

            $bookingpress_front_booking_dynamic_helper_vars .= 'var lang = ELEMENT.lang.' . $bookingpress_locale_lang . ';';
            $bookingpress_front_booking_dynamic_helper_vars .= 'ELEMENT.locale(lang);';

            $bookingpress_front_booking_dynamic_helper_vars = apply_filters('bookingpress_add_frontbooking_dynamic_helper_vars', $bookingpress_front_booking_dynamic_helper_vars);

            return $bookingpress_front_booking_dynamic_helper_vars;
        }

        
        /**
         * Booking Form Shortcode onload methods
         *
         * @param  mixed $bookingpress_dynamic_on_load_methods_data
         * @return void
         */
        function bookingpress_booking_dynamic_on_load_methods_func( $bookingpress_dynamic_on_load_methods_data )
        {
            $bookingpress_dynamic_on_load_methods_data .= 'this.bookingpress_onload_func();';

            $bookingpress_dynamic_on_load_methods_data .= 'this.appointment_step_form_data.bookingpress_customer_timezone = new Date().getTimezoneOffset();';

            $bookingpress_dynamic_on_load_methods_data .= 'if(this.hide_category_service == "1" || this.is_service_loaded_from_url == "1"){
                this.bookingpress_current_tab = "datetime";
            }';
            $bookingpress_dynamic_on_load_methods_data = apply_filters('bookingpress_add_appointment_booking_on_load_methods', $bookingpress_dynamic_on_load_methods_data);

            return $bookingpress_dynamic_on_load_methods_data;
        }

        /**
         * Booking Form Shortcode onload methods
         *
         * @param  mixed $bookingpress_dynamic_on_load_methods_data
         * @return void
         */
        function bookingpress_booking_dynamic_on_load_methods_func_with_pro( $bookingpress_dynamic_on_load_methods_data ){
            
            $bookingpress_dynamic_on_load_methods_data .= 'this.bookingpress_onload_func();';

            $bookingpress_dynamic_on_load_methods_data .= 'this.appointment_step_form_data.bookingpress_customer_timezone = new Date().getTimezoneOffset();';

            $bookingpress_dynamic_on_load_methods_data = apply_filters('bookingpress_add_appointment_booking_on_load_methods', $bookingpress_dynamic_on_load_methods_data);
            
            $bookingpress_dynamic_on_load_methods_data .= '            
            
            let current_tab = this.bookingpress_current_tab;
            let step_data = this.bookingpress_sidebar_step_data[ current_tab ];
            
            if( "undefined" != typeof step_data.is_navigate_to_next && true == step_data.is_navigate_to_next ){
                this.bookingpress_current_tab = step_data.next_tab_name;
            }
            
            if(this.hide_category_service == "1" || this.is_service_loaded_from_url == "1" ){
                let next_tab = this.bookingpress_sidebar_step_data["service"].next_tab_name;
                if( next_tab != this.bookingpress_current_tab ){
                    this.bookingpress_current_tab = next_tab;
                }
            }
            ';
            /*'if((this.is_staffmember_activated == 1 && typeof this.appointment_step_form_data.hide_staff_selection !== "undefined" && this.appointment_step_form_data.hide_staff_selection == "false" && this.hide_category_service == "1" && this.is_service_loaded_from_url != "1" )) {
                this.bookingpress_current_tab = "staffmembers";
            }'; */

            return $bookingpress_dynamic_on_load_methods_data;
        }

        function bookingpress_call_autofocus_method( $bookingpress_dynamic_on_load_methods_data ){

            $bookingpress_dynamic_on_load_methods_data .= '
                let bpa_current_tab = this.bookingpress_current_tab;

                let bpa_side_bar_step_data = this.bookingpress_sidebar_step_data[bpa_current_tab];
                let bpa_callback_funcs = bpa_side_bar_step_data.auto_focus_tab_callback;
                
                for( let callback in bpa_callback_funcs ){
                    let args = bpa_callback_funcs[callback];
                    
                    this[callback].apply( callback, args );
                }
                
            ';

            return $bookingpress_dynamic_on_load_methods_data;
        }
        
        /**
         * Booking Form Shortcode methods or functions
         *
         * @param  mixed $bookingpress_vue_methods_data
         * @return void
         */
        function bookingpress_booking_dynamic_vue_methods_func( $bookingpress_vue_methods_data )
        {
            global $BookingPress;

            $bookingpress_current_date                    = date('Y-m-d', current_time('timestamp'));
            $no_appointment_time_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_appointment_time_selected_for_the_booking', 'message_setting');
            $no_service_selected_for_the_booking          = $BookingPress->bookingpress_get_settings('no_service_selected_for_the_booking', 'message_setting');

            $bookingpress_nonce = esc_html(wp_create_nonce('bpa_wp_nonce'));

            $bookingpress_current_user_id = get_current_user_id();

            $bookingpress_before_book_appointment_data = '';
            $bookingpress_before_book_appointment_data = apply_filters('bookingpress_before_book_appointment', $bookingpress_before_book_appointment_data);

            $bookingpress_before_selecting_booking_service_data = '';
            $bookingpress_before_selecting_booking_service_data = apply_filters('bookingpress_before_selecting_booking_service', $bookingpress_before_selecting_booking_service_data);

            $bookingpress_after_selecting_booking_service_data = '';
            $bookingpress_after_selecting_booking_service_data = apply_filters('bookingpress_after_selecting_booking_service', $bookingpress_after_selecting_booking_service_data);

            $bookingpress_after_selecting_payment_method_data = '';
            $bookingpress_after_selecting_payment_method_data = apply_filters('bookingpress_after_selecting_payment_method', $bookingpress_after_selecting_payment_method_data);

            $bookingpress_dynamic_add_params_for_timeslot_request = '';
            $bookingpress_dynamic_add_params_for_timeslot_request = apply_filters('bookingpress_dynamic_add_params_for_timeslot_request', $bookingpress_dynamic_add_params_for_timeslot_request);

            $bookingpress_add_data_for_first_step_on_next_page = '';
            $bookingpress_add_data_for_first_step_on_next_page = apply_filters('bookingpress_add_data_for_first_step_on_next_page', $bookingpress_add_data_for_first_step_on_next_page);

            $bookingpress_add_data_for_previous_page = '';
            $bookingpress_add_data_for_previous_page = apply_filters('bookingpress_add_data_for_previous_page', $bookingpress_add_data_for_previous_page);

            $bookingpress_add_data_for_second_step_on_next_page = '';
            $bookingpress_add_data_for_second_step_on_next_page = apply_filters('bookingpress_add_data_for_second_step_on_next_page', $bookingpress_add_data_for_second_step_on_next_page);
	    
            $bookingpress_dynamic_next_page_request_filter = '';
            $bookingpress_dynamic_next_page_request_filter = apply_filters('bookingpress_dynamic_next_page_request_filter', $bookingpress_dynamic_next_page_request_filter);

            $bookingpress_dynamic_validation_for_step_change = '';
            $bookingpress_dynamic_validation_for_step_change = apply_filters('bookingpress_dynamic_validation_for_step_change', $bookingpress_dynamic_validation_for_step_change);

            $bookingpress_disable_date_xhr_data = '';
            $bookingpress_disable_date_xhr_data = apply_filters( 'bookingpress_disable_date_xhr_data', $bookingpress_disable_date_xhr_data );

            $bookingpress_disable_date_pre_xhr_data = '';
            $bookingpress_disable_date_pre_xhr_data = apply_filters( 'bookingpress_disable_date_pre_xhr_data', $bookingpress_disable_date_pre_xhr_data );

            $bookingpress_disable_date_vue_data = '';
            $bookingpress_disable_date_vue_data = apply_filters( 'bookingpress_disable_date_vue_data_modify', $bookingpress_disable_date_vue_data );

            $bookingpress_modify_select_step_category = '';
            $bookingpress_modify_select_step_category = apply_filters('bookingpress_modify_select_step_category', $bookingpress_modify_select_step_category);

            $bookingpress_site_date = date('Y-m-d H:i:s', current_time( 'timestamp') );
            $bookingpress_site_date = apply_filters( 'bookingpress_modify_current_date', $bookingpress_site_date );

            $bookingpress_vue_methods_data .= '
            get_formatted_date(iso_date){

                if( true == /(\d{2})\T/.test( iso_date ) ){
                    let date_time_arr = iso_date.split("T");
                    return date_time_arr[0];
                }
				var __date = new Date(iso_date);
				var __year = __date.getFullYear();
				var __month = __date.getMonth()+1;
				var __day = __date.getDate();
				if (__day < 10) {
					__day = "0" + __day;
				}
				if (__month < 10) {
					__month = "0" + __month;
				}
				var formatted_date = __year+"-"+__month+"-"+__day;
				return formatted_date;
			},
            get_formatted_datetime(iso_date) {			
                var __date = new Date(iso_date);
                var hour = __date.getHours();
                var minute = __date.getMinutes();
                var second = __date.getSeconds();

                if (minute < 10) {
                    minute = "0" + minute;
                }
                if (second < 10) {
                    second = "0" + second;
                }
                var formatted_time = hour + ":" + minute + ":" + second;				
                var __year = __date.getFullYear();
                var __month = __date.getMonth()+1;
                var __day = __date.getDate();
                if (__day < 10) {
                    __day = "0" + __day;
                }
                if (__month < 10) {
                    __month = "0" + __month;
                }

                var formatted_date = __year+"-"+__month+"-"+__day;
                return formatted_date+" "+formatted_time; 
            },
			bookingpress_set_error_msg(error_msg){
				const vm = this
                let container = vm.$el;
                let pos = 0;
                if( null != container ){
                    pos = container.getBoundingClientRect().top + window.scrollY;
                }
				vm.is_display_error = "1"
				vm.is_error_msg = error_msg
				window.scrollTo({
					top: pos,
					behavior: "smooth",
				});
			},
			bookingpress_remove_error_msg(){
				const vm = this
				vm.is_display_error = "0"
				vm.is_error_msg = ""
			},
			checkBeforeBookAppointment(){
				const vm = this;
				' . $bookingpress_before_book_appointment_data . '
				setTimeout(function(){
                    var bkp_wpnonce_pre = "' . $bookingpress_nonce . '";
                    var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                    if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
                    {
                        bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
                    }
                    else {
                        bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
                    }

					var postData = { action:"bookingpress_before_book_appointment",_wpnonce:bkp_wpnonce_pre_fetch };
                    postData.appointment_data = JSON.stringify( vm.appointment_step_form_data );
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.variant == "error"){
							vm.bookingpress_set_error_msg(response.data.msg)
							if(response.data.error_type == "dayoff"){
								vm.service_timing = []
							}
						}else{
							vm.bookingpress_remove_error_msg()
						}
					}.bind(this) )
					.catch( function (error) {
						vm.bookingpress_set_error_msg(error)
					});
				},1500);
			},
			book_appointment(){
				const vm2 = this
				vm2.isLoadBookingLoader = "1"
				vm2.isBookingDisabled = true
				vm2.checkBeforeBookAppointment()
				setTimeout(function(){
					if(vm2.is_display_error != "1"){
                        var bkp_wpnonce_pre = "' . $bookingpress_nonce . '";
                        var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                        if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
                        {
                            bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
                        }
                        else {
                            bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
                        }

						var postData = { action:"bookingpress_front_save_appointment_booking", _wpnonce:bkp_wpnonce_pre_fetch };
                        postData.appointment_data = JSON.stringify( vm2.appointment_step_form_data );
						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
						.then( function (response) {
							vm2.isLoadBookingLoader = "0"
							vm2.isBookingDisabled = false
							if(response.data.variant == "redirect"){
								vm2.bookingpress_remove_error_msg()
								var bookingpress_created_element = document.createElement("div");
								bookingpress_created_element.innerHTML = response.data.redirect_data;
								bookingpress_created_element.className = "bookingpress_runtime_script";
								document.body.appendChild(bookingpress_created_element);
								var scripts = document.getElementsByClassName("bookingpress_runtime_script");
								var text = scripts[scripts.length - 1].textContent;
								eval(text);
							}else if(response.data.variant == "redirect_url"){
								vm2.bookingpress_remove_error_msg()
								window.location.href = response.data.redirect_data
							}else if(response.data.variant == "error"){
								vm2.bookingpress_set_error_msg(response.data.msg)
							}else{
								vm2.bookingpress_remove_error_msg()
							}
						}.bind(this) )
						.catch( function (error) {
							vm2.bookingpress_set_error_msg(error)
						});
					}else{
						vm2.isLoadBookingLoader = "0"
						vm2.isBookingDisabled = false
					}
				}, 3000);
			},
			selectStepCategory(selected_cat_id, selected_cat_name = "", total_services = 0, total_category=""){
				const vm = this
                if( 0 == selected_cat_id ){
                    let temp_services = [];
                    let m = 0;
                    for( let x in vm.bpa_services_data_from_categories ){
                        let service_details = vm.bpa_services_data_from_categories[x];                                                
                        for( let n in service_details ){
                            let current_service = service_details[n];                            
                            if( "undefined" != typeof current_service.bookingpress_staffmembers ){
                                let selected_staffmember = vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id;
                                if( current_service.bookingpress_staffmembers.includes( selected_staffmember ) && selected_staffmember != ""){
                                    var bookingpress_service_pos = parseFloat(current_service.bookingpress_service_position);                                      
                                    temp_services[bookingpress_service_pos] = current_service;                                    
                                } else {
                                    var bookingpress_service_pos = parseFloat(current_service.bookingpress_service_position );                                
                                    temp_services[bookingpress_service_pos] = current_service;
                                } 
                            } else {
                                var bookingpress_service_pos = parseFloat(current_service.bookingpress_service_position );                                
                                temp_services[bookingpress_service_pos] = current_service;                                
                            }
                            '.$bookingpress_modify_select_step_category.'
                            m++;                            
                        }                        
                    }                    
                    var bpa_temp_services= [];                     
                    temp_services.sort();
                    for( let n in temp_services ){                        
                        if(temp_services[n] != "") {                            
                            bpa_temp_services[n] = temp_services[n];   
                        }                        
                    }                    
                    vm.services_data = bpa_temp_services.sort();
                } else {
                    vm.services_data = vm.bpa_services_data_from_categories[selected_cat_id];
                    '.$bookingpress_modify_select_step_category.'
                }

				vm.appointment_step_form_data.selected_category = selected_cat_id;
				vm.appointment_step_form_data.selected_cat_name = selected_cat_name;
                
                var bkp_wpnonce_pre = "' . $bookingpress_nonce . '";
                var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
                {
                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
                }
                else {
                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
                }

				/*var postData = { 
                    action:"bookingpress_front_get_category_services", 
                    category_id: selected_cat_id, 
                    total_category: total_category,
                    total_service: total_services,
                    posted_data: vm.appointment_step_form_data,
                    _wpnonce:bkp_wpnonce_pre_fetch };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					vm.services_data = response.data
				}.bind(this) )
				.catch( function (error) {
					console.log(error);
				}); */
			},
			async selectDate(selected_service_id, service_name, service_price, service_price_without_currency, is_move_to_next, service_duration_val = "",service_duration_unit = ""){
				const vm = this
                if(typeof vm.appointment_step_form_data.cart_items == "undefined" && (selected_service_id != vm.appointment_step_form_data.selected_service && vm.appointment_step_form_data.selected_service != "")){
                    var bookingpress_selected_date = vm.appointment_step_form_data.selected_date;
                    let newDate = new Date('.( !empty( $bookingpress_site_date ) ? '"' . $bookingpress_site_date . '"' : '' ).');
                    let pattern = /(\d{4}\-\d{2}\-\d{2})/;
                    if( !pattern.test( newDate ) ){

                        let sel_month = newDate.getMonth() + 1;
                        let sel_year = newDate.getFullYear();
                        let sel_date = newDate.getDate();

                        if( sel_month < 10 ){
                            sel_month = "0" + sel_month;
                        }

                        if( sel_date < 10 ){
                            sel_date = "0" + sel_date;
                        }
                        
                        newDate = sel_year + "-" + sel_month + "-" + sel_date;
                    }
                    
                    vm.appointment_step_form_data.selected_date = newDate;
                    vm.appointment_step_form_data.selected_start_time = ""
				    vm.appointment_step_form_data.selected_end_time = ""
                }
                '.$bookingpress_before_selecting_booking_service_data.'
                
                vm.appointment_step_form_data.selected_service = selected_service_id
                vm.appointment_step_form_data.selected_service_name = service_name
                vm.appointment_step_form_data.selected_service_price = service_price
                vm.appointment_step_form_data.service_price_without_currency = service_price_without_currency
                vm.appointment_step_form_data.selected_service_duration = service_duration_val
                vm.appointment_step_form_data.selected_service_duration_unit = service_duration_unit
                
                if(vm.previous_selected_tab_id === 1 || vm.previous_selected_tab_id === 2 || vm.current_selected_tab_id === 1){
                    vm.displayResponsiveCalendar = "1"
                }
                
                if(is_move_to_next === "true"){
                    vm.bookingpress_step_navigation(vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].next_tab_name, vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].next_tab_name, vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].previous_tab_name)
                }

                var selected_date = vm.appointment_step_form_data.selected_date
                var formatted_date = vm.get_formatted_date(selected_date)
                vm.bookingpress_remove_error_msg();
                ' . $bookingpress_after_selecting_booking_service_data . '
                return false;
				vm.service_timing = ""
                vm.isLoadTimeLoader = "1"
                setTimeout(function(){
                    var bkp_wpnonce_pre = "' . $bookingpress_nonce . '";
                    var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                    if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
                    {
                        bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
                    }
                    else {
                        bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
                    }

                    var postData = { action:"bookingpress_front_get_timings", service_id: selected_service_id,selected_date: formatted_date, _wpnonce:bkp_wpnonce_pre_fetch };
                    ' . $bookingpress_dynamic_add_params_for_timeslot_request . '
                    postData.appointment_data_obj = JSON.stringify(vm.appointment_step_form_data);
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        vm.service_timing = response.data
                        vm.isLoadTimeLoader = "0"
                        if(vm.previous_selected_tab_id !== 1 && vm.previous_selected_tab_id !== 2 && vm.current_selected_tab_id !== 1 && vm.current_selected_tab_id !== 2 ){
                            vm.displayResponsiveCalendar = "0"
                        }
                        
                    }.bind(this) )
                    .catch( function (error) {
                        vm.isLoadTimeLoader = "0"
                        console.log(error);
                    });
                }, 250);
			},
			get_date_timings(current_selected_date = ""){
				const vm = this
                if( window.innerWidth <= 576 ){
				    vm.service_timing = "-1"
                }else{
                    vm.service_timing = "-2"
                }
                vm.displayResponsiveCalendar = "0"
                
                if( null == vm.appointment_step_form_data.selected_date ){
                    vm.appointment_step_form_data.selected_date = new new Date('.( !empty( $bookingpress_site_date ) ? '"' . $bookingpress_site_date . '"' : '' ).');
                }


				if( current_selected_date == "") {
					current_selected_date =	vm.appointment_step_form_data.selected_date
				}
				vm.appointment_step_form_data.selected_date = current_selected_date
				var selected_date = vm.appointment_step_form_data.selected_date
                let pattern = /(\d{4}\-\d{2}\-\d{2})/;
                if( !pattern.test( selected_date ) ){

                    let sel_month = selected_date.getMonth() + 1;
                    let sel_year = selected_date.getFullYear();
                    let sel_date = selected_date.getDate();

                    if( sel_month < 10 ){
                        sel_month = "0" + sel_month;
                    }

                    if( sel_date < 10 ){
                        sel_date = "0" + sel_date;
                    }
                    
                    selected_date = sel_year + "-" + sel_month + "-" + sel_date;
                }

				vm.appointment_step_form_data.selected_date = selected_date

				vm.appointment_step_form_data.selected_start_time = ""
				vm.appointment_step_form_data.selected_end_time = ""
				var selected_service_id = vm.appointment_step_form_data.selected_service
                
                var bkp_wpnonce_pre = "' . $bookingpress_nonce . '"
                var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce")
                if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
                {
                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre
                }
                else {
                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value
                }

				var postData = { action:"bookingpress_front_get_timings", service_id: selected_service_id, selected_date: selected_date, _wpnonce:bkp_wpnonce_pre_fetch, };
				' . $bookingpress_dynamic_add_params_for_timeslot_request . '                
                postData.appointment_data_obj = JSON.stringify(vm.appointment_step_form_data);
                postData.bpa_change_store_date = false;
                if( "undefined" != typeof vm.bookingpress_timezone_offset ){
                    postData.client_timezone_offset = vm.bookingpress_timezone_offset;
                    postData.bpa_change_store_date = true;                
                }

                if( "undefined" != typeof vm.bookingpress_dst_timezone ){
                    postData.client_dst_timezone = vm.bookingpress_dst_timezone;
                }
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					setTimeout(function(){
						vm.service_timing = response.data
						vm.isLoadTimeLoader = "0"
						vm.displayResponsiveCalendar = "0"
                        if(response.data == ""){
                            vm.service_timing = null;
                        }
					}, 1500);

					' . $bookingpress_after_selecting_booking_service_data . '
				}.bind(this) )
				.catch( function (error) {
					console.log(error);
				});
			},
			selectTiming(selected_start_time, selected_end_time, store_start_time = "", store_end_time = "", store_selected_date = "" ,formated_start_time = "",formated_end_time = ""){
				const vm = this
				vm.appointment_step_form_data.selected_start_time = selected_start_time
				vm.appointment_step_form_data.selected_end_time = selected_end_time           

                if( "" != formated_end_time && "" != formated_start_time ) {                    
                    vm.appointment_step_form_data.selected_formatted_start_time = formated_start_time
                    vm.appointment_step_form_data.selected_formatted_end_time = formated_end_time
                }

                if( /* "undefined" != typeof vm.bookingpress_timezone_offset && */ "" != store_start_time && "" != store_end_time && "" != store_selected_date ){
                    vm.appointment_step_form_data.store_start_time = store_start_time;
                    vm.appointment_step_form_data.store_end_time = store_end_time;
                    vm.appointment_step_form_data.client_offset = vm.bookingpress_timezone_offset;
                    vm.appointment_step_form_data.store_selected_date = store_selected_date;
                }

                
                vm.bookingpress_step_navigation(vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].next_tab_name, vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].next_tab_name, vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].previous_tab_name)
			},
			resetForm(){
				const vm2 = this
				vm2.appointment_formdata.appointment_selected_customer = "' . $bookingpress_current_user_id . '"
				vm2.appointment_formdata.appointment_selected_service = ""
				vm2.appointment_formdata.appointment_booked_date = "' . $bookingpress_current_date . '";
				vm2.appointment_formdata.appointment_booked_time = ""
			},
			select_service(selected_service_id){
				const vm = this
				vm.appointment_step_form_data.selected_service = selected_service_id
			},
			automatic_next_page(next_tab_id){
				const vm = this
                '.$bookingpress_dynamic_next_page_request_filter.'
				vm.current_selected_tab_id = parseInt(next_tab_id);
				vm.bookingpress_remove_error_msg()
                var bookingpress_scroll_pos = document.querySelector("#bookingpress_booking_form_"+vm.appointment_step_form_data.bookingpress_uniq_id);
                bookingpress_scroll_pos = bookingpress_scroll_pos.getBoundingClientRect();
                var bookingpress_scroll_position = (bookingpress_scroll_pos.top + window.scrollY) - 300;
                window.scrollTo({
					top: bookingpress_scroll_position,
				});
			},
			next_page(customer_form = "", current_selected_element = "", next_selection_element = ""){
				const vm = this
				var current_selected_tab = bpa_selected_tab = parseFloat(vm.current_selected_tab_id)
				vm.previous_selected_tab_id = parseInt(current_selected_tab)
				if(current_selected_element != undefined && current_selected_element != null){
					current_selected_tab = parseInt(current_selected_element)
				}
                var bookingpress_scroll_pos = document.querySelector("#bookingpress_booking_form_"+vm.appointment_step_form_data.bookingpress_uniq_id);
                bookingpress_scroll_pos = bookingpress_scroll_pos.getBoundingClientRect();
                var bookingpress_scroll_position = (bookingpress_scroll_pos.top + window.scrollY) - 300;
                window.scrollTo({
					top: bookingpress_scroll_position,
				});

                if(current_selected_tab === 1 || vm.previous_selected_tab_id === 1){
                    vm.is_display_error = "0"
					if(vm.appointment_step_form_data.selected_service == "" || vm.appointment_step_form_data.selected_service == undefined || vm.appointment_step_form_data.selected_service == "undefined"){
						vm.bookingpress_set_error_msg("' . $no_service_selected_for_the_booking . '")
						vm.current_selected_tab_id = 1
						return false;
					}else{
                        '.$bookingpress_add_data_for_first_step_on_next_page.'
						if(next_selection_element != ""){
                            current_selected_tab = next_selection_element
                        }else{
                            current_selected_tab = current_selected_tab;
                        }
					}
				}else if(current_selected_tab === 2){
					if(current_selected_element != undefined && current_selected_element == 2  && vm.appointment_step_form_data.selected_start_time == "" && bpa_selected_tab == "2" && vm.appointment_step_form_data.selected_service_duration_unit != "d") {
						vm.bookingpress_set_error_msg("' . $no_appointment_time_selected_for_the_booking . '")
						vm.current_selected_tab_id = 2
						return false;
					}        
                    if(vm.appointment_step_form_data.selected_service != ""  && vm.appointment_step_form_data.selected_start_time == "" && vm.appointment_step_form_data.selected_service_duration_unit != "d") {
                        vm.selectDate(vm.appointment_step_form_data.selected_service, vm.appointment_step_form_data.selected_service_name, vm.appointment_step_form_data.selected_service_price, vm.appointment_step_form_data.service_price_without_currency, "true",vm.appointment_step_form_data.selected_service_duration,vm.appointment_step_form_data.selected_service_duration_unit);
                    }                                 
					if(vm.is_display_error != "1"){
                        if(next_selection_element != ""){
                            current_selected_tab = next_selection_element
                        }else{
                            current_selected_tab = current_selected_tab;
                        }
						vm.bookingpress_remove_error_msg()
					}else{
						if(vm.is_error_msg == ""){
							vm.bookingpress_set_error_msg("' . esc_html__('Something went wrong', 'bookingpress-appointment-booking') . '")
						}
					}                                   
				}else if(current_selected_tab === 3){
					if(vm.appointment_step_form_data.selected_start_time == "" && vm.appointment_step_form_data.is_enable_validations == 1 && vm.appointment_step_form_data.selected_service_duration_unit != "d"){
						vm.bookingpress_set_error_msg("' . $no_appointment_time_selected_for_the_booking . '")
						vm.current_selected_tab_id = 2                                      
						return false;
					}else{
						vm.$refs[customer_form].validate((valid) => {
							if (valid) {
                                if(next_selection_element != ""){
                                    current_selected_tab = next_selection_element
                                }else{
								    current_selected_tab = current_selected_tab;
                                }
							}
						});	
					}
				}else{
                    if(vm.appointment_step_form_data.selected_start_time == "" && vm.appointment_step_form_data.is_enable_validations == 1 && vm.appointment_step_form_data.selected_service_duration_unit != "d"){
						vm.bookingpress_set_error_msg("' . $no_appointment_time_selected_for_the_booking . '")
						vm.current_selected_tab_id = 2                                      
						return false;
					} else {
                        vm.$refs[customer_form].validate((valid) => {
                            if (valid) {
                                if(next_selection_element != ""){
                                    current_selected_tab = next_selection_element
                                }else{
                                    current_selected_tab = current_selected_tab;
                                }
                            }else{
                                current_selected_tab = 3
                            }
                        });
                    }
				}
				if(current_selected_tab === 2 && vm.appointment_step_form_data.selected_start_time == "" && vm.appointment_step_form_data.selected_date != "" ) {
					vm.get_date_timings()
				}

                vm.current_selected_tab_id = parseInt(current_selected_tab);
                if(current_selected_tab === 2 && vm.appointment_step_form_data.selected_service_duration_unit == "d"){
                    vm.next_selected_tab_id = 3
                }

                '.$bookingpress_dynamic_next_page_request_filter.'
			},
			previous_page(previous_selection_tab_id = ""){                
				const vm = this
                var current_selected_tab = parseFloat(vm.current_selected_tab_id)
                if(previous_selection_tab_id != ""){
                    current_selected_tab = previous_selection_tab_id;
                }else{
				    vm.previous_selected_tab_id = parseInt(current_selected_tab)                    
				    current_selected_tab = current_selected_tab - 1;
                }
                '.$bookingpress_dynamic_next_page_request_filter.'
                
				vm.current_selected_tab_id = parseInt(current_selected_tab);
                if(vm.previous_selected_tab_id == "1"){
                    vm.displayResponsiveCalendar = 1;
                }
                var bookingpress_scroll_pos = document.querySelector("#bookingpress_booking_form_"+vm.appointment_step_form_data.bookingpress_uniq_id);
                bookingpress_scroll_pos = bookingpress_scroll_pos.getBoundingClientRect();
                var bookingpress_scroll_position = (bookingpress_scroll_pos.top + window.scrollY) - 300;
                window.scrollTo({
					top: bookingpress_scroll_position,
				});
			},
			select_payment_method(payment_method){
				const vm = this
				vm.appointment_step_form_data.selected_payment_method = payment_method
				var bookingpress_allowed_payment_gateways_for_card_fields = [];
				' . $bookingpress_after_selecting_payment_method_data . '
				if(bookingpress_allowed_payment_gateways_for_card_fields.includes(payment_method)){
					vm.is_display_card_option = 1;
				}else{
					vm.is_display_card_option = 0;
				}
			},
			displayCalendar(){
				const vm = this
				vm.displayResponsiveCalendar = "1"
			},
			Change_front_appointment_description(service_id) {
				const vm = this;
				vm.services_data.forEach(function(item, index, arr){					
					if(item.bookingpress_service_id == service_id ){						
						if(item.display_details_more == 0 && item.display_details_less == 1) {
							item.display_details_less = 0;
							item.display_details_more = 1;								
						} else {
							item.display_details_more = 0;
							item.display_details_less = 1;
						}
					}					
				});
			},
			bookingpress_phone_country_change_func(bookingpress_country_obj){
				const vm = this
                var bookingpress_selected_country = bookingpress_country_obj.iso2
				vm.appointment_step_form_data.customer_phone_country = bookingpress_selected_country
                vm.appointment_step_form_data.customer_phone_dial_code = bookingpress_country_obj.dialCode;
                let exampleNumber = window.intlTelInputUtils.getExampleNumber( bookingpress_selected_country, true, 1 );                                
                if( typeof vm.bookingpress_phone_default_placeholder == "undefined" &&  "" != exampleNumber ){
                    vm.bookingpress_tel_input_props.inputOptions.placeholder = exampleNumber;
                } else if(vm.bookingpress_phone_default_placeholder == "false" && "" != exampleNumber){
                    vm.bookingpress_tel_input_props.inputOptions.placeholder = exampleNumber;
                }
			},
            async bookingpress_disable_date( bpa_selected_service = "", bpa_selected_date = "" ){
                '.$bookingpress_disable_date_pre_xhr_data.'
                this.bookingpress_disable_date_xhr( bpa_selected_service, bpa_selected_date );
            },
            bookingpress_disable_date_xhr( bpa_selected_service = "", bpa_selected_date = "" ){

                const vm = this;
                vm.isLoadTimeLoader = "1";
                vm.isLoadDateTimeCalendarLoad = "1";

                vm.service_timing = "-3";

                var bkp_wpnonce_pre = "' . $bookingpress_nonce . '"
                var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce")
                if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null){
                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre
                } else {
                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value
                }

                if( "" == bpa_selected_service && "" != vm.appointment_step_form_data.selected_service ){
                    bpa_selected_service = vm.appointment_step_form_data.selected_service;
                }

                if( "undefined" != typeof vm.bookingpress_dst_timezone ){
                    vm.appointment_step_form_data.client_dst_timezone = vm.bookingpress_dst_timezone;
                }
                
                var postData = { action: "bookingpress_get_disable_date", service_id: bpa_selected_service, selected_service:bpa_selected_service, selected_date:bpa_selected_date, service_id:bpa_selected_service,_wpnonce:bkp_wpnonce_pre_fetch };

                postData.appointment_data_obj = JSON.stringify(vm.appointment_step_form_data);
                
                postData.bpa_change_store_date = false;
                if( "undefined" != typeof vm.bookingpress_timezone_offset ){
                    postData.client_timezone_offset = vm.bookingpress_timezone_offset;
                    postData.bpa_change_store_date = true;
                }
                
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) 
                {   
                    vm.service_timing = [];
                    if(response.data.variant == "success" && (response.data.selected_date != undefined && response.data.days_off_disabled_dates != undefined)){
                        '.$bookingpress_disable_date_vue_data.'
                        vm.days_off_disabled_dates = "";
                        vm.days_off_disabled_dates = response.data.days_off_disabled_dates;
                        vm.appointment_step_form_data.selected_date = response.data.selected_date;
                        if( "undefined" != typeof response.data.front_timings ){
                            vm.service_timing = response.data.front_timings;
                        }
                        vm.isLoadTimeLoader = "0"
                        if( "undefined" != typeof response.data.empty_front_timings && true == response.data.empty_front_timings  ){
                            vm.isLoadDateTimeCalendarLoad = "1"
                            vm.appointment_step_form_data.selected_date = response.data.next_available_date;
                            vm.bookingpress_disable_date( bpa_selected_service, response.data.next_available_date );
                            return;
                        } else {
                            /* Check full day appointments block */
                            
                            if( false == response.data.prevent_next_month_check ){
                                let postDataAction = "bookingpress_get_whole_day_appointments";
                                if( true == response.data.check_for_multiple_days_event ){
                                    postDataAction = "bookingpress_get_whole_day_appointments_multiple_days";
                                }

                                var bkp_wpnonce_pre = "' . $bookingpress_nonce . '";
                                var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                                if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
                                {
                                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
                                }
                                else {
                                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
                                }

                                var postData = { action: postDataAction,days_off_disabled_dates: vm.days_off_disabled_dates, service_id: bpa_selected_service, max_available_year: response.data.max_available_year, max_available_month:response.data.max_available_month,  selected_service:bpa_selected_service, selected_date:bpa_selected_date, service_id:bpa_selected_service,_wpnonce:bkp_wpnonce_pre_fetch, "next_month": response.data.next_month, "counter": 1 };

                                postData.appointment_data_obj = JSON.stringify( vm.appointment_step_form_data );
                                '.$bookingpress_disable_date_xhr_data.'
                                vm.bookingpress_retrieve_daysoff_for_booked_appointment( postData );
                            }
                            setTimeout(function(){
                                vm.isLoadDateTimeCalendarLoad = "0"
                            },200);
                        }
                    }
                    
                }.bind(this) )
                .catch( function (error) {
                    console.log(error);
                });
            },
            bookingpress_retrieve_daysoff_for_booked_appointment( postData ){
                const vm = this;
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) ).then( function( response ) {
                    vm.days_off_disabled_dates = response.data.days_off_disabled_dates;
                    
                    if(false == response.data.prevent_next_month_check && response.data.counter < 3 ){ /** Currently data will be checked for next 3 months */
                        postData.days_off_disabled_dates = vm.days_off_disabled_dates;
                        postData.next_month = response.data.next_month;
                        postData.counter++;
                        vm.bookingpress_retrieve_daysoff_for_booked_appointment( postData );
                    }
                });
            },
            bookingpress_get_all_parent_node_with_overflow_hidden( elem ){
                if (!Element.prototype.matches) {
                    Element.prototype.matches = Element.prototype.matchesSelector ||
                        Element.prototype.mozMatchesSelector ||
                        Element.prototype.msMatchesSelector ||
                        Element.prototype.oMatchesSelector ||
                        Element.prototype.webkitMatchesSelector ||
                        function(s) {
                            var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                                i = matches.length;
                            while (--i >= 0 && matches.item(i) !== this) {}
                            return i > -1;
                        };
                }
            
                var parents = [];
            
                for (; elem && elem !== document; elem = elem.parentNode) {
                    let computed_style = getComputedStyle( elem );
                    
                    if( computed_style.overflow == "hidden" || computed_style.overflowX == "hidden" || computed_style.overflowY == "hidden" ){
                        parents.push(elem);
                    }
                }
                return parents;
            },
            bookingpress_onload_func(){
                const vm = this
                if(window.innerWidth <= 576){
                    vm.bookingpress_container_dynamic_class = "";
                    let bookingpress_container = vm.$el;
                    let parents_with_hidden_overflow = vm.bookingpress_get_all_parent_node_with_overflow_hidden( bookingpress_container );
                    let apply_overflow = ( parents_with_hidden_overflow.length > 0 ) ? true : false;
                    window.addEventListener("scroll", function(e){
                        
                        let bookingpress_scrollTop = bookingpress_container.getBoundingClientRect().top;
                        let bookingpress_scrollBottom = bookingpress_container.getBoundingClientRect().bottom;
                        let bpa_current_scroll = window.scrollY;
                        bookingpress_scrollBottom = bpa_current_scroll + bookingpress_scrollBottom + bookingpress_scrollTop;

                        if( bookingpress_scrollTop < 50 && bpa_current_scroll >= bookingpress_scrollTop && bpa_current_scroll <= bookingpress_scrollBottom ){
                            vm.bookingpress_container_dynamic_class = "bpa-front__mc--is-sticky" 
                            vm.bookingpress_footer_dynamic_class = "__bpa-is-sticky" //Change this string
                            if( apply_overflow ){
                                for( let i = 0; i < parents_with_hidden_overflow.length; i++ ){
                                    let parent = parents_with_hidden_overflow[i];
                                    parent.classList.add("--bpa-is-overflow-visible");
                                }
                            }
                        } else {
                            vm.bookingpress_container_dynamic_class = "" 
                            vm.bookingpress_footer_dynamic_class = "" //Change this string
                            if( apply_overflow ){
                                for( let i = 0; i < parents_with_hidden_overflow.length; i++ ){
                                    let parent = parents_with_hidden_overflow[i];
                                    parent.classList.remove("--bpa-is-overflow-visible");
                                }
                            }
                        } 
                    });
                }
                window.addEventListener("resize", function(e){
                    if( window.innerWidth <= 576 ){
                        vm.bookingpress_container_dynamic_class = "";
                        let bookingpress_container = vm.$el;

                        let bookingpress_scrollTop = bookingpress_container.getBoundingClientRect().top;
                        let bookingpress_scrollBottom = bookingpress_container.getBoundingClientRect().bottom;
                        let bpa_current_scroll = window.scrollY;
                        bookingpress_scrollBottom = bpa_current_scroll + bookingpress_scrollBottom + bookingpress_scrollTop;

                        if( bookingpress_scrollTop < 50 && bpa_current_scroll >= bookingpress_scrollTop && bpa_current_scroll <= bookingpress_scrollBottom ){
                            vm.bookingpress_container_dynamic_class = "bpa-front__mc--is-sticky" 
                            vm.bookingpress_footer_dynamic_class = "__bpa-is-sticky" //Change this string
                        } else {
                            vm.bookingpress_container_dynamic_class = "" 
                            vm.bookingpress_footer_dynamic_class = "" //Change this string
                        } 
                    }
                });
            },
            bookingpress_step_navigation(current_tab, next_tab, previous_tab, is_strict_validate = 1){

                const vm = this
                var bookingpress_is_validate = 0;

                vm.bookingpress_remove_error_msg();

                var bookingpress_validate_fields_arr = vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].validate_fields;
                
                if((vm.bookingpress_current_tab == "basic_details") && vm.bookingpress_current_tab != next_tab && current_tab != previous_tab){
                    bookingpress_validate_fields_arr.forEach(function(currentValue, index, arr){
                        if(vm.bookingpress_current_tab == vm.bookingpress_current_tab && vm.appointment_step_form_data[currentValue] == "" && vm.bookingpress_current_tab != next_tab && current_tab != previous_tab){
                            vm.bookingpress_set_error_msg(vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].validation_msg[currentValue]);
                            bookingpress_is_validate = 1;
                        }
                    });

                    if(bookingpress_is_validate == 0 && is_strict_validate == 1){
                        var customer_form = "appointment_step_form_data";
                        vm.$refs[customer_form].validate((valid) => {
                            if (!valid) {
                                bookingpress_is_validate = 1;
                            }else{
                                bookingpress_is_validate = 0;
                            }
                        });
                    }
                }else{
                    if(is_strict_validate == 1){
                        bookingpress_validate_fields_arr.forEach(function(currentValue, index, arr){
                            if(vm.bookingpress_current_tab == vm.bookingpress_current_tab && vm.appointment_step_form_data[currentValue] == "" && vm.bookingpress_current_tab != next_tab && current_tab != previous_tab){
                                if( currentValue == "selected_start_time" && vm.appointment_step_form_data[currentValue] == "" ) {
                                    if( vm.appointment_step_form_data.selected_service_duration_unit != "d" ){
                                        vm.bookingpress_set_error_msg(vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].validation_msg[currentValue]);
                                        bookingpress_is_validate = 1;
                                    }
                                } else {
                                    vm.bookingpress_set_error_msg(vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].validation_msg[currentValue]);
                                    bookingpress_is_validate = 1;
                                }
                            }
                        });
                    }
                    
                    '.$bookingpress_dynamic_validation_for_step_change.'

                    
                    /* if( "undefined" == typeof retrieved_timeslots && "datetime" == next_tab && 0 == bookingpress_is_validate ){
                        let selected_service_id = vm.appointment_step_form_data.selected_service;
                        vm.bookingpress_disable_date(selected_service_id,vm.appointment_step_form_data.selected_date);
                    } */
                }

                if( "service" == current_tab && "service" != vm.bookingpress_current_tab ){
                    var bookingpress_selected_date = vm.appointment_step_form_data.selected_date+"T00:00:00+00:00";
                    var bookingpress_disable_dates_arr = vm.days_off_disabled_dates.split(",");
                    if(bookingpress_disable_dates_arr.includes(bookingpress_selected_date)){
                        let newDate = new Date('.( !empty( $bookingpress_site_date ) ? '"' . $bookingpress_site_date . '"' : '' ).');
                        let pattern = /(\d{4}\-\d{2}\-\d{2})/;
                        if( !pattern.test( newDate ) ){

                            let sel_month = newDate.getMonth() + 1;
                            let sel_year = newDate.getFullYear();
                            let sel_date = newDate.getDate();

                            if( sel_month < 10 ){
                                sel_month = "0" + sel_month;
                            }

                            if( sel_date < 10 ){
                                sel_date = "0" + sel_date;
                            }
                            
                            newDate = sel_year + "-" + sel_month + "-" + sel_date;
                        }
                        
                        vm.appointment_step_form_data.selected_date = newDate;
                    }
                }
                
                if(bookingpress_is_validate == 0){
                    vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].is_allow_navigate = 1;
                    vm.bookingpress_current_tab = current_tab
                    vm.bookingpress_next_tab = next_tab
                    vm.bookngpress_previous_tab = previous_tab
                    vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].is_allow_navigate = 1;
                    if( "datetime" == current_tab ){
                        let selected_service_id = vm.appointment_step_form_data.selected_service;
                        vm.bookingpress_disable_date(selected_service_id,vm.appointment_step_form_data.selected_date);
                    }
                }

                if( window.innerWidth <= 576 ){
                    let container = vm.$el;
                    let pos = 0;
                    if( null != container ){
                        pos = container.getBoundingClientRect().top + window.scrollY;
                    }

                    setTimeout(function(){
                        window.scrollTo({
                            top: pos,
                            behavior: "smooth",
                        });
                    }, 500);
                }

                '.$bookingpress_dynamic_next_page_request_filter.'
            },';

            $bookingpress_vue_methods_data = apply_filters('bookingpress_add_appointment_booking_vue_methods', $bookingpress_vue_methods_data);

            return $bookingpress_vue_methods_data;
        }
        
        /**
         * My Appointments Shortcode Data Fields function
         *
         * @return void
         */
        function bookingpress_front_appointments_dynamic_data_fields_func()
        {
            global $bookingpress_front_appointment_vue_data_fields, $BookingPress, $bookingpress_global_options;
            $default_daysoff_details = $BookingPress->bookingpress_get_default_dayoff_dates();
            if (! empty($default_daysoff_details) ) {
                $default_daysoff_details = array_map(
                    function ( $date ) {
                        return date('Y-m-d', strtotime($date));
                    },
                    $default_daysoff_details
                );
                $bookingpress_front_appointment_vue_data_fields['disabledDates'] = $default_daysoff_details;
            } else {
                $bookingpress_front_appointment_vue_data_fields['disabledDates'] = '';
            }

            $bookingpress_mybooking_title_text      = $BookingPress->bookingpress_get_customize_settings('mybooking_title_text', 'booking_my_booking');
            $bookingpress_hide_customer_details     = $BookingPress->bookingpress_get_customize_settings('hide_customer_details', 'booking_my_booking');            
            $bookingpress_allow_cancel_appointments = $BookingPress->bookingpress_get_customize_settings('allow_to_cancel_appointment', 'booking_my_booking');            
            $bookingpress_reset_button_label        = $BookingPress->bookingpress_get_customize_settings('reset_button_title', 'booking_my_booking');
            $bookingpress_apply_button_label        = $BookingPress->bookingpress_get_customize_settings('apply_button_title', 'booking_my_booking');
            $bookingpress_search_appointment_label  = $BookingPress->bookingpress_get_customize_settings('search_appointment_title', 'booking_my_booking');
            $bookingpress_search_date_title  = $BookingPress->bookingpress_get_customize_settings('search_date_title', 'booking_my_booking');
            $bookingpress_search_end_date_title  = $BookingPress->bookingpress_get_customize_settings('search_end_date_title', 'booking_my_booking');
            $bookingpress_my_appointment_menu_title  = $BookingPress->bookingpress_get_customize_settings('my_appointment_menu_title', 'booking_my_booking');
            $bookingpress_delete_appointment_menu_title  = $BookingPress->bookingpress_get_customize_settings('delete_appointment_menu_title', 'booking_my_booking');
            $confirmation_message_for_the_cancel_appointment = $BookingPress->bookingpress_get_settings('confirmation_message_for_the_cancel_appointment', 'message_setting');

            $bookingpress_mybooking_title_text = !empty($bookingpress_mybooking_title_text) ? stripslashes_deep($bookingpress_mybooking_title_text) : '';
            $bookingpress_reset_button_label = !empty($bookingpress_reset_button_label) ? stripslashes_deep($bookingpress_reset_button_label) : '';
            $bookingpress_apply_button_label = !empty($bookingpress_apply_button_label) ? stripslashes_deep($bookingpress_apply_button_label) : '';
            $bookingpress_search_appointment_label = !empty($bookingpress_search_appointment_label) ? stripslashes_deep($bookingpress_search_appointment_label) : '';
            $bookingpress_search_date_title = !empty($bookingpress_search_date_title) ? stripslashes_deep($bookingpress_search_date_title) : '';
            $bookingpress_search_end_date_title = !empty($bookingpress_search_end_date_title) ? stripslashes_deep($bookingpress_search_end_date_title) : '';
            $bookingpress_my_appointment_menu_title = !empty($bookingpress_my_appointment_menu_title) ? stripslashes_deep($bookingpress_my_appointment_menu_title) : '';
            $bookingpress_delete_appointment_menu_title = !empty($bookingpress_delete_appointment_menu_title) ? stripslashes_deep($bookingpress_delete_appointment_menu_title) : '';
            $confirmation_message_for_the_cancel_appointment = !empty($confirmation_message_for_the_cancel_appointment) ? stripslashes_deep($confirmation_message_for_the_cancel_appointment) : '';
            $bookingpress_hide_customer_details = $bookingpress_hide_customer_details = ( $bookingpress_hide_customer_details == 'true' ) ? 1 : 0;
            $bookingpress_allow_cancel_appointments = $bookingpress_allow_cancel_appointments = ( $bookingpress_allow_cancel_appointments == 'true' ) ? 1 : 0;

            $bookingpress_front_appointment_vue_data_fields['mybooking_title_text'] = $bookingpress_mybooking_title_text;
            $bookingpress_front_appointment_vue_data_fields['hide_customer_details'] = $bookingpress_hide_customer_details;
            $bookingpress_front_appointment_vue_data_fields['allow_cancel_appointments'] = $bookingpress_allow_cancel_appointments;
            $bookingpress_front_appointment_vue_data_fields['reset_button_title'] = $bookingpress_reset_button_label;
            $bookingpress_front_appointment_vue_data_fields['apply_button_title'] = $bookingpress_apply_button_label;
            $bookingpress_front_appointment_vue_data_fields['search_appointment_title'] = $bookingpress_search_appointment_label;
            $bookingpress_front_appointment_vue_data_fields['search_date_title'] = $bookingpress_search_date_title;
            $bookingpress_front_appointment_vue_data_fields['search_end_date_title'] = $bookingpress_search_end_date_title;
            $bookingpress_front_appointment_vue_data_fields['my_appointment_menu_title'] = $bookingpress_my_appointment_menu_title;
            $bookingpress_front_appointment_vue_data_fields['delete_appointment_menu_title'] = $bookingpress_delete_appointment_menu_title;
            $bookingpress_front_appointment_vue_data_fields['confirmation_message_for_the_cancel_appointment'] = $confirmation_message_for_the_cancel_appointment;
            $bookingpress_front_appointment_vue_data_fields['bookingpress_is_user_logged_in'] =  is_user_logged_in() ? '1' : '0';              	                        
            $bookingpress_front_appointment_vue_data_fields['bookingpress_user_fullname'] =  '';
            $bookingpress_front_appointment_vue_data_fields['bookingpress_user_email'] =  '';           
            $bookingpress_front_appointment_vue_data_fields['bookingpress_avatar_url'] = '';
            

            $bookingpress_front_appointment_vue_data_fields["current_screen_size"] = "";
			$bookingpress_front_appointment_vue_data_fields["container_size"] = "";

			$bookingpress_front_appointment_vue_data_fields['bookingpress_myappointment_footer_dynamic_class'] = '';
			$bookingpress_front_appointment_vue_data_fields['bookingpress_myappointment_header_dynamic_class'] = '';

            $bookingpress_front_appointment_vue_data_fields['bookingpress_my_booking_current_tab'] = 'my_appointment';

            $bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_default_date_format = $BookingPress->bookingpress_check_common_date_format($bookingpress_global_options_arr['wp_default_date_format']);

            $bookingpress_front_appointment_vue_data_fields['masks'] = array(
                'input' => strtoupper($bookingpress_default_date_format),
            );

            $bookingpress_delete_account_content  = $BookingPress->bookingpress_get_customize_settings('delete_account_content', 'booking_my_booking');
            $bookingpress_front_appointment_vue_data_fields['delete_account_content'] = do_shortcode(stripslashes($bookingpress_delete_account_content));

            $bookingpress_front_appointment_vue_data_fields['bookingpress_cancel_appointment_drawer'] = false;
            $bookingpress_front_appointment_vue_data_fields['bookingpress_cancel_drawer_direction'] = 'btt';

            $bookingpress_front_appointment_vue_data_fields['bookingpress_previous_row_obj'] = '';

            $bookingpress_front_appointment_vue_data_fields['bookingpress_created_nonce'] = esc_html(wp_create_nonce('bpa_wp_nonce'));

            $bookingpress_front_appointment_vue_data_fields['is_display_pagination'] = 0; 

            $bookingpress_front_appointment_vue_data_fields['disable_my_appointments_apply'] = false;

            $bookingpress_front_appointment_vue_data_fields = apply_filters('bookingpress_front_appointment_add_dynamic_data', $bookingpress_front_appointment_vue_data_fields);            

            echo json_encode($bookingpress_front_appointment_vue_data_fields);
        }
        
        /**
         * My Appointments Shortcode Helper Variables
         *
         * @return void
         */
        function bookingpress_front_appointments_dynamic_helper_vars_func()
        {
            global $bookingpress_global_options;
            $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_locale_lang = $bookingpress_options['locale'];
            ?>
            var lang = ELEMENT.lang.<?php echo esc_html($bookingpress_locale_lang); ?>;
            ELEMENT.locale(lang)
            <?php
            do_action('bookingpress_add_front_appointment_helper_vars');
        }

        /**
         * My Appointments Shortcode On Load Methods
         *
         * @return void
         */
        function bookingpress_front_appointments_dynamic_on_load_methods_func()
        {   if(is_user_logged_in()) {
            ?>            
            this.loadFrontAppointments()
            <?php
             }
            ?>
            this.bookingpress_load_mybooking_form(); 
            this.bookingpress_myappointments_onload_func();
            this.bookingpress_dynamic_add_onload_myappointment_methods_func();
            <?php
            do_action('bookingpress_dynamic_add_onload_myappointment_methods');
        }
        
        /**
         * My Appointments methods or functions
         *
         * @return void
         */
        function bookingpress_front_appointments_dynamic_vue_methods_func()
        {
        ?>
            bookingpress_toggle_calendar(){
                const vm = this
                vm.$refs.bookingpress_range_calendar.togglePopover();
            },
            bookingpress_clear_datepicker(){
                const vm = this;
                vm.appointment_date_range = '';
            },
            bookingpress_myappointments_onload_func(){
                const vm = this
                if(window.innerWidth <= 576){
                    vm.bookingpress_myappointment_header_dynamic_class = ""
                    var bookingpress_uniq_id = vm.bookingpress_uniq_id
                    var bookingpress_container_vars = document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).offsetTop
                    var bookingpress_container_bottom = (document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).offsetTop + document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).offsetHeight) - (window.innerHeight - 100);
                    var bookingpress_container_top = bookingpress_container_vars - 20
                    var current_selected_tab = vm.current_selected_tab_id
                    window.addEventListener("scroll", function(e){
                        bookingpress_container_vars = document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).offsetTop;
                        bookingpress_container_bottom = (document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).offsetTop + document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).offsetHeight) - (window.innerHeight - 100);
                        bookingpress_container_top = bookingpress_container_vars
                            
                        var current_position = window.scrollY;
                        if(current_position >= bookingpress_container_top && current_position <= bookingpress_container_bottom){
                            vm.bookingpress_myappointment_header_dynamic_class = "bpa-front__mc--is-sticky" 
                            vm.bookingpress_myappointment_footer_dynamic_class = "__bpa-is-sticky" //Change this string
                        }else{
                            vm.bookingpress_myappointment_header_dynamic_class = ""
                            vm.bookingpress_myappointment_footer_dynamic_class = ""
                        }
                    });
                }
                window.addEventListener("resize", function(e){ 
                    if(window.innerWidth <= 576){
                        vm.bookingpress_myappointment_header_dynamic_class = ""
                        var bookingpress_uniq_id = vm.bookingpress_uniq_id

                        var bookingpress_container_vars = document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).offsetTop
                        var bookingpress_container_bottom = (document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).offsetTop + document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).offsetHeight);
                        var bookingpress_container_top = bookingpress_container_vars - 20
                        
                        
                        bookingpress_container_vars = document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).offsetTop;
                        bookingpress_container_bottom = (document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).offsetTop + document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).offsetHeight) - (window.innerHeight - 100);
                        bookingpress_container_top = bookingpress_container_vars

                        var current_position = window.scrollY
                        
                        if(current_position >= bookingpress_container_top || current_position <= bookingpress_container_bottom){
                            if( current_position > bookingpress_container_bottom || current_position < bookingpress_container_top ){
                                vm.bookingpress_myappointment_header_dynamic_class = ""
                                vm.bookingpress_myappointment_footer_dynamic_class = ""
                            } else {
                                vm.bookingpress_myappointment_header_dynamic_class = "bpa-front__mc--is-sticky"
                                vm.bookingpress_myappointment_footer_dynamic_class = "__bpa-is-sticky" //Change this string
                            }
                        }else if(vm.bookingpress_myappointment_header_dynamic_class != ""){
                            vm.bookingpress_myappointment_footer_dynamic_class = "__bpa-is-sticky" //Change this string
                        }else{
                            vm.bookingpress_myappointment_header_dynamic_class = ""
                            vm.bookingpress_myappointment_footer_dynamic_class = ""
                        }
                        
                        window.addEventListener("scroll", function(e){
                            bookingpress_container_vars = document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).offsetTop;
                            bookingpress_container_bottom = (document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).offsetTop + document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).offsetHeight) - (window.innerHeight - 100);
                            bookingpress_container_top = bookingpress_container_vars

                            var current_position = window.scrollY
                            
                            if(current_position >= bookingpress_container_top || current_position <= bookingpress_container_bottom){
                                if( current_position > bookingpress_container_bottom || current_position < bookingpress_container_top ){
                                    vm.bookingpress_myappointment_header_dynamic_class = ""
                                    vm.bookingpress_myappointment_footer_dynamic_class = ""
                                } else {
                                    vm.bookingpress_myappointment_header_dynamic_class = "bpa-front__mc--is-sticky"
                                    vm.bookingpress_myappointment_footer_dynamic_class = "__bpa-is-sticky" //Change this string
                                }
                            }else if(vm.bookingpress_myappointment_header_dynamic_class != ""){
                                vm.bookingpress_myappointment_footer_dynamic_class = "__bpa-is-sticky" //Change this string
                            }else{
                                vm.bookingpress_myappointment_header_dynamic_class = ""
                                vm.bookingpress_myappointment_footer_dynamic_class = ""
                            }
                        });
                    }else{
                        vm.bookingpress_myappointment_header_dynamic_class = ""
                        vm.bookingpress_myappointment_footer_dynamic_class = ""
                    }
                });
            },
            bookingpress_dynamic_add_onload_myappointment_methods_func(){
                const vm = this
                vm.current_screen_size = "desktop";
                if(window.outerWidth >= 1200){
                    vm.current_screen_size = "desktop";
                }else if(window.outerWidth < 1200 && window.outerWidth >= 768){
                    vm.current_screen_size = "tablet";
                }else if(window.outerWidth < 768){
                    vm.current_screen_size = "mobile";
                }

                /*setTimeout(function(){
                    vm.current_screen_size = document.getElementById("bpa-front-customer-panel-container").offsetWidth;
                }, 1000);*/
                    
                window.addEventListener('resize', function(event) {
                    if(window.outerWidth >= 1200){
                        vm.current_screen_size = "desktop";
                    }else if(window.outerWidth < 1200 && window.outerWidth >= 768){
                        vm.current_screen_size = "tablet";
                    }else if(window.outerWidth < 768){
                        vm.current_screen_size = "mobile";
                    }
                    //vm.current_screen_size = document.getElementById("bpa-front-customer-panel-container").offsetWidth;
                });
            },
            toggleBusy() {
                if(this.is_display_loader == '1'){
                    this.is_display_loader = '0'
                }else{
                    this.is_display_loader = '1'
                }
            },    
            bookingpress_load_mybooking_form(){
                const vm = this
                setTimeout(function(){
                    vm.is_front_appointment_empty_loader = "0"
                    setTimeout(function(){
                        if(document.getElementById("bpa-front-customer-panel-container") != null){
                            document.getElementById("bpa-front-customer-panel-container").style.display = "block";
                        }
                        if(document.getElementById("bpa-front-data-empty-view--my-bookings") != null){
                            document.getElementById("bpa-front-data-empty-view--my-bookings").style.display = "flex";
                        }
                    }, 500);
                }, 1000);
            },
            loadFrontAppointments() {                    
                const vm = this
                vm.disable_my_appointments_apply = true;
                this.toggleBusy()
                var bookingpress_search_data = { 'search_appointment':this.search_appointment,'selected_date_range': this.appointment_date_range}

                var bkp_wpnonce_pre = vm.bookingpress_created_nonce;
                var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
                {
                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
                }
                else {
                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
                }
                    
                var postData = { action:'bookingpress_get_customer_appointments', perpage:this.per_page, currentpage:this.currentPage, search_data: bookingpress_search_data,_wpnonce:bkp_wpnonce_pre_fetch};
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )                
                .then( function (response) {
                    this.toggleBusy()
                    vm.disable_my_appointments_apply = false;
                    this.items = response.data.items;
                    this.total_records = parseInt(response.data.total_records);                    
                    this.is_display_pagination = 0;
                    if(this.total_records > 10) {
                        this.is_display_pagination = 1
                    }
                    this.bookingpress_user_fullname = response.data.customer_details.bookingpress_user_fullname
                    this.bookingpress_user_email = response.data.customer_details.bookingpress_user_email
                    this.bookingpress_avatar_url = response.data.customer_details.bookingpress_avatar_url
                }.bind(this) )
                .catch( function (error) {     
                    vm.disable_my_appointments_apply = false;               
                    vm.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                    });
                });
            },
            get_formatted_date(iso_date){

                if( true == /(\d{2})\T/.test( iso_date ) ){
                    let date_time_arr = iso_date.split('T');
                    return date_time_arr[0];
                }
                var __date = new Date(iso_date);
                var __year = __date.getFullYear();
                var __month = __date.getMonth()+1;
                var __day = __date.getDate();
                if (__day < 10) {
                    __day = '0' + __day;
                }
                if (__month < 10) {
                    __month = '0' + __month;
                }
                var formatted_date = __year+'-'+__month+'-'+__day;
                return formatted_date;
            },
            cancelAppointment( appointment_id){                
                const vm = new Vue()
                const vm2 = this
                vm2.is_display_loader = '1'
                vm2.is_disabled = true
                var cancel_id = appointment_id

                var bkp_wpnonce_pre = vm2.bookingpress_created_nonce;
                var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
                {
                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
                }
                else {
                    bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
                }

                var appointment_cancel_data = { action: 'bookingpress_cancel_appointment', cancel_id: cancel_id, _wpnonce: bkp_wpnonce_pre_fetch }
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( appointment_cancel_data ) )
                .then(function(response){
                    vm2.is_display_loader = '0'
                    vm2.is_disabled = false
                    if(response.data.variant != 'error'){
                        window.location.href = response.data.redirect_url;
                    }else{
                        vm2.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: response.data.variant,
                            customClass: response.data.variant+'_notification',
                        });
                        vm2.loadFrontAppointments()
                    }
                }).catch(function(error){
                    console.log(error);
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                    });
                });
            },
            resetFilter(){                
                const vm = this
                if(vm.search_appointment != '' || vm.appointment_date_range != '') {                    
                    vm.search_appointment = '';
                    vm.appointment_date_range = ''
                    vm.loadFrontAppointments()
                }
            },
            bookingpress_activate_myboooking_tab(tab_name){
                const vm = this;
                vm.bookingpress_my_booking_current_tab = tab_name;
                <?php
                do_action('bookingpress_activate_my_booking_tab_data');
                ?>
            },
            bookingpress_open_cancel_drawer(){
                const vm = this
                vm.bookingpress_cancel_appointment_drawer = true
            },
            bookingpress_close_cancel_drawer(){
                const vm = this
                vm.bookingpress_cancel_appointment_drawer = false
            },
            bookingpress_full_row_clickable(row, column, event){
                const vm = this
                let target = event.target;
                let getParent = vm.bookingpress_get_parent_node( target, '.bpa-ma--action-btn-wrapper' );
                if( 0 < getParent.length && getParent[0] != null ){
                    /* Do Nothing */
                } else {
                    vm.$refs.multipleTable.toggleRowExpansion(row);
                }
            },
            bookingpress_get_parent_node( elem, selector ){
                if (!Element.prototype.matches) {
                    Element.prototype.matches = Element.prototype.matchesSelector ||
                        Element.prototype.mozMatchesSelector ||
                        Element.prototype.msMatchesSelector ||
                        Element.prototype.oMatchesSelector ||
                        Element.prototype.webkitMatchesSelector ||
                        function(s) {
                            var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                                i = matches.length;
                            while (--i >= 0 && matches.item(i) !== this) {}
                            return i > -1;
                        };
                }
            
                var parents = [];
            
                for (; elem && elem !== document; elem = elem.parentNode) {
                    if (selector) {
                        if (elem.matches(selector)) {
                            parents.push(elem);
                        }
                        continue;
                    }
                    parents.push(elem);
                }
            
                return parents;
            },
            bookingpress_row_expand(row, expanded){
                const vm = this
                if(vm.bookingpress_previous_row_obj != ''){
                    vm.$refs.multipleTable.toggleRowExpansion(vm.bookingpress_previous_row_obj, false);
                    if(vm.bookingpress_previous_row_obj != row){
                        vm.$refs.multipleTable.toggleRowExpansion(vm.bookingpress_previous_row_obj);
                        vm.bookingpress_previous_row_obj = row;
                    }else{
                        if(expanded.length == undefined){
                            vm.$refs.multipleTable.toggleRowExpansion(row);
                        }
                        vm.bookingpress_previous_row_obj = '';
                    }
                }else{
                    if(expanded.length == undefined){
                        vm.$refs.multipleTable.toggleRowExpansion(row);
                    }
                    vm.bookingpress_previous_row_obj = row;
                }
            },        
            <?php
            do_action('bookingpress_front_appointment_add_vue_method');
        }
    }
}

global $bookingpress_appointment_bookings, $bookingpress_front_vue_data_fields,$bookingpress_front_appointment_vue_data_fields;
$bookingpress_appointment_bookings = new bookingpress_appointment_bookings();
$bookingpress_options              = $bookingpress_global_options->bookingpress_global_options();
$bookingpress_country_list         = $bookingpress_options['country_lists'];


$bookingpress_front_vue_data_fields             = array(
    'appointment_services_list'   => array(),
    'appointment_formdata'        => array(
        'appointment_selected_customer' => get_current_user_id(),
        'appointment_selected_service'  => '',
        'appointment_booked_date'       => date('Y-m-d', current_time('timestamp')),
        'appointment_booked_time'       => '',
        'appointment_on_site_enabled'   => false,
    ),
    'phone_countries_details'     => json_decode($bookingpress_country_list),
    'final_payable_amount'        => '',
    'activeStepNumber'            => 0,
    'service_categories'          => array(),
    'bookingpress_all_services'   => array(),
    'services_data'               => array(),
    'service_timing'              => array(),
    'on_site_payment'             => false,
    'paypal_payment'              => false,
    'appointment_step_form_data'  => array(
        'selected_category'              => '',
        'selected_cat_name'              => '',
        'selected_service'               => '',
        'selected_service_name'          => '',
        'selected_service_price'         => '',
        'service_price_without_currency' => 0,
        'selected_date'                  => date('Y-m-d', current_time('timestamp')),
        'selected_start_time'            => '',
        'selected_end_time'              => '',
        'customer_email'                 => '',
        'selected_payment_method'        => '',
        'customer_phone_country'         => 'us',
        'total_services'                 => '',
		'total_category'                 => '',
        'selected_service_duration'      => '',
        'selected_service_duration_unit' => '',        
        'is_enable_validations'          => 1,
    ),
    'customer_details_rule'       => array(
        'customer_name'  => array(
            'required' => true,
            'message'  => __('Please enter customer name', 'bookingpress-appointment-booking'),
            'trigger'  => 'blur',
        ),
        'customer_email' => array(
            'required' => true,
            'message'  => __('Please enter customer email', 'bookingpress-appointment-booking'),
            'trigger'  => 'blur',
        ),
    ),
    'current_selected_tab_id'     => 1,
    'previous_selected_tab_id'    => 1,
    'next_selected_tab_id'        => '2',
    'isLoadTimeLoader'            => '0',
    'isServiceLoadTimeLoader'     => '0',
    'isLoadDateTimeCalendarLoad'  => '0',
    'isLoadBookingLoader'         => '0',
    'isBookingDisabled'           => false,
    'displayResponsiveCalendar'   => '0',
    'display_service_description' => '0',
    'bookingpress_container_dynamic_class' => '',
    'bookingpress_footer_dynamic_class' => '',
    'bookingpress_current_tab' => 'service',
    'bookingpress_next_tab' => 'datetime',
    'bookngpress_previous_tab' => '',
);

$bookingpress_front_appointment_vue_data_fields = array(
    'items'                    => array(),
    'search_appointment'       => '',
    'appointment_date_range'   => array(),
    'appointment_service_name' => '',
    'appointment_date'         => '',
    'appointment_duration'     => '',
    'appointment_status'       => '',
    'appointment_payment'      => '',
    'is_disabled'              => false,
    'is_front_appointment_empty_loader' => '1',
    'bookingpress_is_user_logged_in' => '0',
    'per_page' => 10,
    'pagination_length' => 10,
    'currentPage' => 1,
    'total_records' => 0,
    'hide_on_single_page' => true,
);
