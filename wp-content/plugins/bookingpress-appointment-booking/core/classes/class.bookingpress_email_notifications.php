<?php
if (! class_exists('bookingpress_email_notifications') ) {
    class bookingpress_email_notifications Extends BookingPress_Core
    {
        var $bookingpress_email_notification_type = '';
        var $bookingpress_email_sender_name       = '';
        var $bookingpress_email_sender_email      = '';
        var $bookingpress_admin_email             = '';
        var $bookingpress_smtp_username           = '';
        var $bookingpress_smtp_password           = '';
        var $bookingpress_smtp_host               = '';
        var $bookingpress_smtp_port               = '';
        var $bookingpress_smtp_secure             = '';

        function __construct()
        {
            add_filter('bookingpress_modify_email_notification_content', array( $this, 'bookingpress_modify_email_content_func' ), 10, 3);
        }
        
        /**
         * Initialize email configurations
         *
         * @return void
         */
        function bookingpress_init_emai_config()
        {
            global $BookingPress, $bookingpress_other_debug_log_id;
            $this->bookingpress_email_notification_type = esc_html($BookingPress->bookingpress_get_settings('selected_mail_service', 'notification_setting'));
            $this->bookingpress_email_sender_name       = esc_html($BookingPress->bookingpress_get_settings('sender_name', 'notification_setting'));
            $this->bookingpress_email_sender_email      = esc_html($BookingPress->bookingpress_get_settings('sender_email', 'notification_setting'));

            if ($this->bookingpress_email_notification_type == 'smtp' ) {
                $this->bookingpress_smtp_username = esc_html($BookingPress->bookingpress_get_settings('smtp_username', 'notification_setting'));
                $this->bookingpress_smtp_password = $BookingPress->bookingpress_get_settings('smtp_password', 'notification_setting');
                $this->bookingpress_smtp_host     = $BookingPress->bookingpress_get_settings('smtp_host', 'notification_setting');
                $this->bookingpress_smtp_port     = esc_html($BookingPress->bookingpress_get_settings('smtp_port', 'notification_setting'));
                $this->bookingpress_smtp_secure   = esc_html($BookingPress->bookingpress_get_settings('smtp_secure', 'notification_setting'));
            }

            $bookingpress_debug_log_data = "Notification type => {$this->bookingpress_email_notification_type} | Sender name => {$this->bookingpress_email_sender_name} | Sender Email => {$this->bookingpress_email_sender_email} | SMTP Username => {$this->bookingpress_smtp_username} | SMTP Password => {$this->bookingpress_smtp_password} | SMTP Host => {$this->bookingpress_smtp_host} | SMTP Port => {$this->bookingpress_smtp_port} | SMTP Secure => {$this->bookingpress_smtp_secure}";
            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Init Email Configuration', 'bookingpress_email_notiifcation', $bookingpress_debug_log_data, $bookingpress_other_debug_log_id);
        }
        
        /**
         * Function for send test email notification
         *
         * @param  mixed $smtp_host
         * @param  mixed $smtp_port
         * @param  mixed $smtp_secure
         * @param  mixed $smtp_username
         * @param  mixed $smtp_password
         * @param  mixed $smtp_test_receiver_email
         * @param  mixed $smtp_test_msg
         * @param  mixed $smtp_sender_email
         * @param  mixed $smtp_sender_name
         * @return void
         */
        function bookingpress_send_test_email_notification( $smtp_host, $smtp_port, $smtp_secure, $smtp_username, $smtp_password, $smtp_test_receiver_email, $smtp_test_msg, $smtp_sender_email, $smtp_sender_name )
        {
            global $wpdb, $BookingPress, $wp_version, $bookingpress_other_debug_log_id;

            $bookingpress_debug_log_args_data = func_get_args();
            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Test email notification arguments data', 'bookingpress_email_notiifcation', $bookingpress_debug_log_args_data, $bookingpress_other_debug_log_id);

            $is_mail_sent     = 0;
            $return_error_msg = __('SMTP Test Email cannot sent successfully', 'bookingpress-appointment-booking');
            $return_error_log = '';

            if (! empty($smtp_host) && ! empty($smtp_port) && ! empty($smtp_secure) && ! empty($smtp_username) && ! empty($smtp_password) && ! empty($smtp_test_receiver_email) && ! empty($smtp_test_msg) && ! empty($smtp_sender_email) && ! empty($smtp_sender_name) ) {
                if (version_compare($wp_version, '5.5', '<') ) {
                    include_once ABSPATH . WPINC . '/class-phpmailer.php';
                    include_once ABSPATH . WPINC . '/class-smtp.php';
                    $BookingPressMailer = new PHPMailer();
                } else {
                    include_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
                    include_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
                    include_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
                    $BookingPressMailer = new PHPMailer\PHPMailer\PHPMailer();
                }

                $BookingPressMailer->CharSet   = 'UTF-8';
                $BookingPressMailer->SMTPDebug = 1; // change this value to 1 for debug
                ob_start();
                echo '<span class="bpa-smtp-notification-error-msg">';
             // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - Text is escaped properly
                echo addslashes(esc_html__('The SMTP debugging output is shown below:', 'bookingpress-appointment-booking'));
                echo '</span><pre>';
                $BookingPressMailer->isSMTP();
                $BookingPressMailer->Host     = $smtp_host;
                $BookingPressMailer->SMTPAuth = true;
                $BookingPressMailer->Username = $smtp_username;
                $BookingPressMailer->Password = $smtp_password;
                if (! empty($smtp_secure) && $smtp_secure != 'Disabled' ) {
                    $BookingPressMailer->SMTPSecure = strtolower($smtp_secure);
                }
                if ($smtp_secure == 'Disabled' ) {
                    $BookingPressMailer->SMTPAutoTLS = false;
                }
                $BookingPressMailer->Port = $smtp_port;
                $BookingPressMailer->setFrom($smtp_sender_email, $smtp_sender_name);
                $BookingPressMailer->addReplyTo($smtp_sender_email, $smtp_sender_name);
                $BookingPressMailer->addAddress($smtp_test_receiver_email);
                $BookingPressMailer->isHTML(true);
                $bookingpress_email_subject  = esc_html__('BookingPress SMTP Test Email Notification', 'bookingpress-appointment-booking');
                $BookingPressMailer->Subject = $bookingpress_email_subject;
                $BookingPressMailer->Body    = $smtp_test_msg;

                if (! $BookingPressMailer->send() ) {
                    echo '</pre><span class="bpa-dialog--sns__body--error-title">';
                 // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - Text is escaped properly
                    echo addslashes(esc_html__('The full debugging output is shown below:', 'bookingpress-appointment-booking'));
                    echo '</span>';
                    var_dump($BookingPressMailer);
                    $smtp_debug_log    = ob_get_clean();
                    $return_error_log .= '<pre>';
                    $return_error_log .= $smtp_debug_log;
                    $return_error_log .= '</pre>';
                    $return_error_msg  = $BookingPressMailer->ErrorInfo;
                } else {
                    $smtp_debug_log   = ob_get_clean();
                    $is_mail_sent     = 1;
                    $return_error_msg = '';
                }
            }

            $return_msg = array(
            'is_mail_sent'  => $is_mail_sent,
            'error_msg'     => $return_error_msg,
            'error_log_msg' => $return_error_log,
            );

            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Test email notification send response', 'bookingpress_email_notiifcation', $return_msg, $bookingpress_other_debug_log_id);

            echo wp_json_encode($return_msg);
            exit;
        }

        
        /**
         * Core function for send email notification
         *
         * @param  mixed $template_type
         * @param  mixed $notification_name
         * @param  mixed $appointment_id
         * @param  mixed $receiver_email_id
         * @return void
         */
        function bookingpress_send_email_notification( $template_type, $notification_name, $appointment_id, $receiver_email_id, $cc_emails = array() )
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_notifications, $wp_version, $bookingpress_other_debug_log_id;

            $bookingpress_send_email_notification_debug_log_data = func_get_args();
            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Email notification argument data', 'bookingpress_email_notiifcation', $bookingpress_send_email_notification_debug_log_data, $bookingpress_other_debug_log_id);

            $this->bookingpress_init_emai_config();

            $bookingpress_email_send_res = array(
            'is_mail_sent'   => 0,
            'configurations' => array(
            'notification_type' => $this->bookingpress_email_notification_type,
            'sender_name'       => $this->bookingpress_email_sender_name,
            'sender_email'      => $this->bookingpress_email_sender_email,
            'smtp_username'     => base64_encode($this->bookingpress_smtp_password),
            'smtp_host'         => $this->bookingpress_smtp_host,
            'smtp_port'         => $this->bookingpress_smtp_port,
            'smpt_secure'       => $this->bookingpress_smtp_secure,
            ),
            'error_response' => 'Something went wrong while sending email notification',
            'posted_data'    => array(),
            );

            //$bookingpress_is_notification_enabled = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_notification_id) FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_receiver_type = %s AND bookingpress_notification_name = %s AND bookingpress_notification_status = 1", $template_type, $notification_name)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notifications is table name defined globally. False Positive alarm
            

            $bookingpress_notification_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_notification_id,bookingpress_notification_type FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_receiver_type = %s AND bookingpress_notification_name = %s AND bookingpress_notification_status = 1", $template_type, $notification_name),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notifications is table name defined globally. False Positive alarm            

            $bookingpress_is_notification_enabled = !empty($bookingpress_notification_data['bookingpress_notification_id']) ? $bookingpress_notification_data['bookingpress_notification_id'] : 0 ;                        

            if($bookingpress_is_notification_enabled == 0){
                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Email notification status data', 'bookingpress_email_notiifcation', "Is notification enabled ==> ".$bookingpress_is_notification_enabled, $bookingpress_other_debug_log_id);
                return $bookingpress_email_send_res;
            }

            if (! empty($this->bookingpress_email_notification_type) && ! empty($this->bookingpress_email_sender_name) && ! empty($this->bookingpress_email_sender_email) && ! empty($template_type) && ! empty($notification_name) && ! empty($receiver_email_id) ) {
                $bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id =%d", $appointment_id), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Email notification appointment data', 'bookingpress_email_notiifcation', $bookingpress_appointment_data, $bookingpress_other_debug_log_id);

                $notification_type =  !empty($bookingpress_notification_data['bookingpress_notification_type']) ? $bookingpress_notification_data['bookingpress_notification_type'] : 'default';
                $bookingpress_is_allowed_email_notification = ! empty($bookingpress_appointment_data['bookingpress_appointment_send_notification']) ? 1 : 0;
                $bookingpress_is_allowed_email_notification = apply_filters('bookingpress_modify_allowed_email_notification_flag', $bookingpress_is_allowed_email_notification);

                if ($bookingpress_is_allowed_email_notification ) {
                    $bookingpress_notification_data = array(
                        'notification_name' => $notification_name,
                        'notification_type' => $notification_type,
                    );

                    $bookingpress_notification_data = apply_filters('bookingpress_modify_email_notification_data', $bookingpress_notification_data, $template_type, $notification_name, $bookingpress_appointment_data);

                    $notification_name = ! empty($bookingpress_notification_data['notification_name']) && is_array($bookingpress_notification_data) ? $bookingpress_notification_data['notification_name'] : $notification_name;
                    $notification_type = ! empty($bookingpress_notification_data['notification_type']) && is_array($bookingpress_notification_data) ? $bookingpress_notification_data['notification_type'] : $notification_type;

                    $bookingpress_get_email_template_details = $this->bookingpress_get_email_template_details($template_type, $notification_name, $bookingpress_appointment_data, $notification_type);

                    $bookingpress_email_subject = $bookingpress_email_content = '';
                    if (! empty($bookingpress_get_email_template_details) ) {
                        $bookingpress_email_subject = $bookingpress_get_email_template_details['notification_subject'];
                        $bookingpress_email_content = $bookingpress_get_email_template_details['notification_message'];
                    }

                    $bookingpress_email_send_res['posted_data'] = $bookingpress_get_email_template_details;

                    $attachments = array();
                    $attachments = apply_filters('bookingpress_email_notification_attachment', $attachments, $bookingpress_get_email_template_details, $appointment_id, $template_type, $notification_name, $bookingpress_appointment_data);

                    switch ( $this->bookingpress_email_notification_type ) {
                    case 'php_mail':
                        $bookingpress_email_header_data = 'From: ' . $this->bookingpress_email_sender_name . '<' . $this->bookingpress_email_sender_email . "> \r\n";
                        $bookingpress_email_header_data .= "Content-Type: text/html; charset=UTF-8\r\n";
						
                        if( !empty( $attachments ) ){
                            $attachment_id = rand(100,999);
							
                            $boundary = md5( $attachment_id.'_'.current_time('timestamp') );

                            $bookingpress_email_header_data = "MIME-Version: 1.0\r\n";
                            $bookingpress_email_header_data .= "From: {$this->bookingpress_email_sender_name} <{$this->bookingpress_email_sender_email}> \r\n";
							$bookingpress_email_header_data .= "Content-Transfer-Encoding: 7bit\r\n";
                            $bookingpress_email_header_data .= "Content-Type: multipart/mixed; boundary = \"{$boundary}\"\r\n";

                            if(!empty($cc_emails) && is_array($cc_emails)){
                                $bookingpress_email_header_data .= "Cc: ".implode(',', $cc_emails)."\r\n";
                            }

							$bookingpress_temp_email_content = "--$boundary\r\n";
                            $bookingpress_temp_email_content .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
							$bookingpress_temp_email_content .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                            $bookingpress_email_content = $bookingpress_temp_email_content . $bookingpress_email_content . "\r\n";
							foreach( $attachments as $attachment_file ){
								$attachment_name = basename( $attachment_file );
								$attachment_type = mime_content_type( $attachment_file );

								if (! function_exists('WP_Filesystem') ) {
									include_once ABSPATH . 'wp-admin/includes/file.php';
								}

								WP_Filesystem();
								global $wp_filesystem;

								$file_content  = $wp_filesystem->get_contents($attachment_file);
								$file_content = chunk_split( base64_encode( $file_content ) );

								$bookingpress_email_content .= "--$boundary\r\n";
								$bookingpress_email_content .= "Content-Type: {$attachment_type}; name={$attachment_name}\r\n";
								$bookingpress_email_content .= "Content-Disposition: attachment; filename={$attachment_name}\r\n";
								$bookingpress_email_content .= "Content-Transfer-Encoding: base64\r\n";
								$bookingpress_email_content .= "X-Attachment-Id: {$attachment_id}\r\n\r\n";
								$bookingpress_email_content .= $file_content;
							}
							$bookingpress_email_content .= "\r\n--{$boundary}--\r\n";
						}


                        if (@mail($receiver_email_id, $bookingpress_email_subject, $bookingpress_email_content, $bookingpress_email_header_data) ) {
                             $bookingpress_email_send_res['is_mail_sent'] = 1;
                             $is_mail_sent                                = 1;
                        }
                        break;
                    case 'wp_mail':
                        $bookingpress_email_header_data = 'From: ' . $this->bookingpress_email_sender_name . '<' . $this->bookingpress_email_sender_email . "> \r\n";
                        $bookingpress_email_header_data .= "Content-Type: text/html; charset=UTF-8\r\n";
                        if(!empty($cc_emails) && is_array($cc_emails)){
                            $bookingpress_email_header_data .= "Cc: ".implode(',', $cc_emails)."\r\n";
                        }
                        if (wp_mail($receiver_email_id, $bookingpress_email_subject, $bookingpress_email_content, $bookingpress_email_header_data, $attachments) ) {
                            $bookingpress_email_send_res['is_mail_sent'] = 1;
                            $is_mail_sent                                = 1;
                        }
                        break;
                    case 'smtp':
                        if (! empty($this->bookingpress_smtp_username) && ! empty($this->bookingpress_smtp_password) && ! empty($this->bookingpress_smtp_host) && ! empty($this->bookingpress_smtp_port) && ! empty($this->bookingpress_smtp_secure) ) {
                            if (version_compare($wp_version, '5.5', '<') ) {
                                include_once ABSPATH . WPINC . '/class-phpmailer.php';
                                include_once ABSPATH . WPINC . '/class-smtp.php';
                                $BookingPressMailer = new PHPMailer();
                            } else {
                                include_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
                                include_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
                                include_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
                                $BookingPressMailer = new PHPMailer\PHPMailer\PHPMailer();
                            }

                            $BookingPressMailer->CharSet   = 'UTF-8';
                            $BookingPressMailer->SMTPDebug = 0; // change this value to 1 for debug
                            $BookingPressMailer->isSMTP();
                            $BookingPressMailer->Host     = $this->bookingpress_smtp_host;
                            $BookingPressMailer->SMTPAuth = true;
                            $BookingPressMailer->Username = $this->bookingpress_smtp_username;
                            $BookingPressMailer->Password = $this->bookingpress_smtp_password;
                            if (! empty($this->bookingpress_smtp_secure) && $this->bookingpress_smtp_secure != 'Disabled' ) {
                                $BookingPressMailer->SMTPSecure = strtolower($this->bookingpress_smtp_secure);
                            }
                            if ($this->bookingpress_smtp_secure == 'Disabled' ) {
                                $BookingPressMailer->SMTPAutoTLS = false;
                            }
                                $BookingPressMailer->Port = $this->bookingpress_smtp_port;
                                $BookingPressMailer->setFrom($this->bookingpress_email_sender_email, $this->bookingpress_email_sender_name);
                                $BookingPressMailer->addReplyTo($this->bookingpress_email_sender_email, $this->bookingpress_email_sender_name);
                                $BookingPressMailer->addAddress($receiver_email_id);

                                if(!empty($cc_emails) && is_array($cc_emails)){
                                    foreach($cc_emails as $ccemail ){
                                        $BookingPressMailer->addCC($ccemail);
                                    }
                                }

                            if (! empty($attachments) ) {
                                foreach ( $attachments as $attachment ) {
                                    $BookingPressMailer->addAttachment($attachment);
                                }
                            }
                                $BookingPressMailer->isHTML(true);
                                $BookingPressMailer->Subject = $bookingpress_email_subject;
                                $BookingPressMailer->Body    = $bookingpress_email_content;

                            if ($BookingPressMailer->send() ) {
                                $is_mail_sent                                = 1;
                                $bookingpress_email_send_res['is_mail_sent'] = 1;
                                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Email notification SMTP success response', 'bookingpress_email_notiifcation', $is_mail_sent, $bookingpress_other_debug_log_id);
                            } else {
                                $bookingpressmailer_errorinfo                  = ! empty($BookingPressMailer->ErrorInfo) ? $BookingPressMailer->ErrorInfo : '';
                                $bookingpress_email_send_res['error_response'] = $bookingpressmailer_errorinfo;
                                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Email notification SMTP error response', 'bookingpress_email_notiifcation', $bookingpressmailer_errorinfo, $bookingpress_other_debug_log_id);
                            }
                        }
                        break;
                    }
                    if (! empty($attachments) ) {
                        foreach ( $attachments as $attachment ) {
                            @unlink($attachment);
                        }
                    }
                }
            }

            return $bookingpress_email_send_res;
        }
        
        /**
         * Get email template details
         *
         * @param  mixed $template_type
         * @param  mixed $notification_name
         * @param  mixed $bookingpress_appointment_data
         * @param  mixed $notification_type
         * @return void
         */
        function bookingpress_get_email_template_details( $template_type, $notification_name, $bookingpress_appointment_data, $notification_type = 'default' )
        {
            global $wpdb, $tbl_bookingpress_notifications, $bookingpress_other_debug_log_id;

            $bookingpress_args_data = func_get_args();
            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Get email template details arguments data', 'bookingpress_email_notiifcation', $bookingpress_args_data, $bookingpress_other_debug_log_id);

            $bookingpress_template_data = array();
            if (! empty($template_type) && ! empty($notification_name) ) {
                $bookingpress_email_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_receiver_type = %s AND bookingpress_notification_status = 1 AND bookingpress_notification_type = %s", $notification_name, $template_type, $notification_type ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notifications is table name defined globally. False Positive alarm

                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Get email template data', 'bookingpress_email_notiifcation', $bookingpress_email_data, $bookingpress_other_debug_log_id);

                if (! empty($bookingpress_email_data) ) {
                    $bookingpress_email_data['bookingpress_notification_subject'] = apply_filters('bookingpress_modify_email_notification_content', $bookingpress_email_data['bookingpress_notification_subject'], $bookingpress_appointment_data,$notification_name);
                    $bookingpress_template_data['notification_subject'] = stripslashes_deep($bookingpress_email_data['bookingpress_notification_subject']);

                    $bookingpress_email_data['bookingpress_notification_message'] = apply_filters('bookingpress_modify_email_notification_content', $bookingpress_email_data['bookingpress_notification_message'], $bookingpress_appointment_data,$notification_name);
                    $bookingpress_template_data['notification_message']           = stripslashes_deep($bookingpress_email_data['bookingpress_notification_message']);

                    $bookingpress_template_data = apply_filters('bookingpress_get_email_template_details_filter', $bookingpress_template_data, $bookingpress_email_data);
                }
            }
            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Get email template return data', 'bookingpress_email_notiifcation', $bookingpress_template_data, $bookingpress_other_debug_log_id);

            return $bookingpress_template_data;
        }
        
        /**
         * Filter for modify email content
         *
         * @param  mixed $template_content
         * @param  mixed $bookingpress_appointment_data
         * @param  mixed $notification_name
         * @return void
         */
        function bookingpress_modify_email_content_func( $template_content, $bookingpress_appointment_data,$notification_name = '')
        {
            global $BookingPress,$bookingpress_other_debug_log_id;

            $bookingpress_args_data = func_get_args();

            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Modify email content arguments data', 'bookingpress_email_notiifcation', $bookingpress_args_data, $bookingpress_other_debug_log_id);

            $template_content = $BookingPress->bookingpress_replace_appointment_data($template_content,$bookingpress_appointment_data);

            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Modify email content return data', 'bookingpress_email_notiifcation', $template_content, $bookingpress_other_debug_log_id);

            return $template_content;
        }
        
        /**
         * Function for send email notification after payment log entry
         *
         * @param  mixed $email_notification_type
         * @param  mixed $inserted_booking_id
         * @param  mixed $bookingpress_customer_email
         * @return void
         */
        function bookingpress_send_after_payment_log_entry_email_notification( $email_notification_type, $inserted_booking_id, $bookingpress_customer_email )
        {
            global $wpdb, $BookingPress, $bookingpress_email_notifications, $bookingpress_other_debug_log_id;

            $bookingpress_args_data = func_get_args();
            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send email notification after payment log entry arguments data', 'bookingpress_email_notiifcation', $bookingpress_args_data, $bookingpress_other_debug_log_id);

            if (! empty($email_notification_type) ) {
                // Send customer email notification
                $is_email_sent = $bookingpress_email_notifications->bookingpress_send_email_notification('customer', $email_notification_type, $inserted_booking_id, $bookingpress_customer_email);
                // Send admin email notification
                $bookingpress_admin_emails = esc_html($BookingPress->bookingpress_get_settings('admin_email', 'notification_setting'));
                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send email notification after payment log entry admin emails data', 'bookingpress_email_notiifcation', $bookingpress_admin_emails, $bookingpress_other_debug_log_id);

                $bookingpress_admin_emails = apply_filters('bookingpress_filter_admin_email_data', $bookingpress_admin_emails, $inserted_booking_id, $email_notification_type);
                if (! empty($bookingpress_admin_emails) ) {
                    $bookingpress_cc_emails = array();
                    $bookingpress_cc_emails = apply_filters('bookingpress_add_cc_email_address', $bookingpress_cc_emails, $email_notification_type);
                    $bookingpress_admin_emails = explode(',', $bookingpress_admin_emails);
                    foreach ( $bookingpress_admin_emails as $admin_email_key => $admin_email_val ) {
                        $bookingpress_email_notifications->bookingpress_send_email_notification('employee', $email_notification_type, $inserted_booking_id, $admin_email_val, $bookingpress_cc_emails);
                    }
                }
            }
        }

    }

    global $bookingpress_email_notifications;
    $bookingpress_email_notifications = new bookingpress_email_notifications();
}
