<?php
    global $bookingpress_slugs;
    $request_module = ( ! empty($_REQUEST['page']) && ( $_REQUEST['page'] != 'bookingpress' ) ) ? sanitize_text_field(str_replace('bookingpress_', '', $_REQUEST['page'])) : 'dashboard'; //// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['page'] sanitized properly
    $request_action = ( ! empty($_REQUEST['action']) ) ? sanitize_text_field($_REQUEST['action']) : 'forms'; //// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['action'] sanitized properly

if(!empty($request_module) && $request_module != 'lite_wizard'){
?>
<nav class="bpa-header-navbar">
    <div class="bpa-header-navbar-wrap">
        <?php
        if (current_user_can('bookingpress') ) {
            ?>
        <div class="bpa-navbar-brand">
            <a href="<?php echo esc_url(admin_url() . 'admin.php?page=bookingpress'); ?>" class="navbar-logo">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="64" height="64" rx="12"/>
                    <path d="M50 18.9608V47.2745C50 49.3359 48.325 51 46.25 51H17.75C15.675 51 14 49.3359 14 47.2745V18.9608C14 16.8993 15.675 15.2353 17.75 15.2353H23V14.1176C23 13.7451 23.375 13 24.125 13C24.875 13 25.25 13.7451 25.25 14.1176V18.5882C25.25 18.9608 24.875 19.7059 24.125 19.7059C23.375 19.7059 23 18.9608 23 18.5882V17.4706H18.5C17.25 17.4706 16.25 18.4641 16.25 19.7059V46.5294C16.25 47.7712 17.25 48.7647 18.5 48.7647H45.5C46.75 48.7647 47.75 47.7712 47.75 46.5294V19.7059C47.75 18.4641 46.75 17.4706 45.5 17.4706H41C41 17.4706 41 18.0418 41 18.5882C41 18.9608 40.625 19.7059 39.875 19.7059C39.125 19.7059 38.75 18.9608 38.75 18.5882V17.4706H33.125C32.5 17.4706 32 16.9739 32 16.3529C32 15.732 32.5 15.2353 33.125 15.2353H38.75V14.1176C38.75 13.7451 39.125 13 39.875 13C40.625 13 41 13.7451 41 14.1176V15.2353H46.25C48.325 15.2353 50 16.8993 50 18.9608Z" fill="white"/>
                    <path d="M37.2501 30.8823C37.2501 30.8823 38.0001 30.1372 38.0001 27.9019C38.0001 24.1765 35.7501 23.4314 32.7501 23.4314H26.0001V39.0784H30.5001V43.549H32.7501V39.0784C35.3501 39.0784 37.1751 39.0784 38.5251 37.4144C39.1751 36.6196 39.5001 35.6013 39.5001 34.5582C39.5001 34.0118 39.4251 33.4654 39.3001 33.1176C38.9751 32.2732 38.7501 31.6274 37.2501 30.8823ZM35.0001 36.8431C34.2501 36.8431 32.7501 36.8431 32.7501 36.8431C32.7501 36.8431 32.7501 36.098 32.7501 34.6078C32.7501 33.366 33.7501 32.3725 35.0001 32.3725C36.2001 32.3725 37.2501 33.3412 37.2501 34.6078C37.2501 35.9242 36.1501 36.8431 35.0001 36.8431ZM33.1751 30.6836C32.8001 30.8575 32.4251 31.081 32.0751 31.3294C31.2501 31.9503 30.5001 32.8444 30.5001 34.6078V36.8431H28.2501V25.6667H32.7501C34.5501 25.6667 35.7501 26.4118 35.7501 27.9019C35.7501 29.268 34.7251 29.9137 33.1751 30.6836Z" fill="white"/>
                </svg>
            </a>
        </div>
        <?php } ?>
        <div class="bpa-navbar-nav" id="bpa-navbar-nav">
            <div class="bpa-menu-toggle" id="bpa-mobile-menu">
                <span class="bpa-mm-bar"></span>
                <span class="bpa-mm-bar"></span>
                <span class="bpa-mm-bar"></span>
            </div>
            <ul>
                <?php if (current_user_can('bookingpress_calendar') ) { ?>
                <li class="bpa-nav-item <?php echo ( 'calendar' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
                    <a href="<?php echo add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_calendar), esc_url(admin_url() . 'admin.php?page=bookingpress')); ?>" class="bpa-nav-link">
                        <span class="material-icons-round">calendar_today</span>
                    <?php esc_html_e('Calendar', 'bookingpress-appointment-booking'); ?>
                    </a>
                </li>
                    <?php
                }
                if (current_user_can('bookingpress_appointments') ) {
                    ?>
                <li class="bpa-nav-item <?php echo ( 'appointments' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
                    <a href="<?php echo add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_appointments), esc_url(admin_url() . 'admin.php?page=bookingpress')); ?>" class="bpa-nav-link">
                        <span class="material-icons-round">insert_invitation</span>
                    <?php esc_html_e('Appointments', 'bookingpress-appointment-booking'); ?>
                    </a>
                </li>
                    <?php
                }
                if (current_user_can('bookingpress_payments') ) {
                    ?>
                <li class="bpa-nav-item <?php echo ( 'payments' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
                    <a href="<?php echo add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_payments), esc_url(admin_url() . 'admin.php?page=bookingpress')); ?>" class="bpa-nav-link">
                        <span class="material-icons-round">monetization_on</span>
                    <?php esc_html_e('Payments', 'bookingpress-appointment-booking'); ?>
                    </a>
                </li>
                    <?php
                }
                if (current_user_can('bookingpress_customers') ) {
                    ?>
                <li class="bpa-nav-item <?php echo ( 'customers' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
                    <a href="<?php echo add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_customers), esc_url(admin_url() . 'admin.php?page=bookingpress')); ?>" class="bpa-nav-link">
                        <span class="material-icons-round">supervisor_account</span>
                    <?php esc_html_e('Customers', 'bookingpress-appointment-booking'); ?>
                    </a>
                </li>
                    <?php
                }
                if (current_user_can('bookingpress_services') ) {
                    ?>
                <li class="bpa-nav-item <?php echo ( 'services' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
                    <a href="<?php echo add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_services), esc_url(admin_url() . 'admin.php?page=bookingpress')); ?>" class="bpa-nav-link">
                        <span class="material-icons-round">ballot</span>
                    <?php esc_html_e('Services', 'bookingpress-appointment-booking'); ?>
                    </a>
                </li>
                    <?php
                }
                if (current_user_can('bookingpress_notifications') ) {
                    ?>
                <li class="bpa-nav-item <?php echo ( 'notifications' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
                    <a href="<?php echo add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_notifications), esc_url(admin_url() . 'admin.php?page=bookingpress')); ?>" class="bpa-nav-link">
                        <span class="material-icons-round">mark_email_unread</span>
                    <?php esc_html_e('Notifications', 'bookingpress-appointment-booking'); ?>
                    </a>
                </li>
                    <?php
                }
                if (current_user_can('bookingpress_customize') ) {
                    ?>
                <li class="bpa-nav-item <?php echo ( 'customize' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>                                        
                    <el-dropdown class="bpa-nav-item-dropdown" trigger="hover">                        
                        <a href="#" class="bpa-nav-link">
                            <span class="material-icons-round">color_lens</span>
                        <?php esc_html_e('Customize', 'bookingpress-appointment-booking'); ?>
                        </a>
                        <el-dropdown-menu slot="dropdown" class="bpa-ni-dropdown-menu">                           
                            <el-dropdown-item class="bpa-ni-dropdown-menu--item <?php echo ( 'forms' == $request_action ) ? '__active' : ''; ?>">
                                <a href="<?php echo add_query_arg( array( 'page'=> $bookingpress_slugs->bookingpress_customize,'action' => 'forms'), esc_url( admin_url() . 'admin.php?page=bookingpress' ) );  // phpcs:ignore ?>" class="bpa-dm--item-link">
                                    <span>
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14.6667 0.666992C15.1267 0.666992 15.5 1.04033 15.5 1.50033V4.63116L8.00083 12.1312L7.99583 15.6628L11.5342 15.6678L15.5 11.702V16.5003C15.5 16.9603 15.1267 17.3337 14.6667 17.3337H1.33333C0.873333 17.3337 0.5 16.9603 0.5 16.5003V1.50033C0.5 1.04033 0.873333 0.666992 1.33333 0.666992H14.6667ZM16.1483 6.34033L17.3267 7.51866L10.845 14.0003L9.665 13.9987L9.66667 12.822L16.1483 6.34033ZM8 9.00033H3.83333V10.667H8V9.00033ZM10.5 5.66699H3.83333V7.33366H10.5V5.66699Z" />
                                        </svg>
                                    </span>    
                                    <?php esc_html_e( 'Forms', 'bookingpress-appointment-booking' ); ?>
                                </a>
                            </el-dropdown-item>                           
                            <el-dropdown-item class="bpa-ni-dropdown-menu--item  <?php echo ( 'form_fields' == $request_action ) ? '__active' : ''; ?>">
                                <a href="<?php echo add_query_arg( array( 'page'=> $bookingpress_slugs->bookingpress_customize,'action' => 'form_fields'), esc_url( admin_url() . 'admin.php?page=bookingpress' ) );  // phpcs:ignore ?>" class="bpa-dm--item-link">
                                    <span>
                                        <svg width="19" height="18" viewBox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12.3334 9.83366L18.1367 13.2187L15.6592 13.927L17.4301 16.9945L15.9867 17.8278L14.2159 14.7612L12.3634 16.5528L12.3334 9.83366ZM10.6667 4.00033H12.3334V5.66699H16.5001C16.7211 5.66699 16.9331 5.75479 17.0893 5.91107C17.2456 6.06735 17.3334 6.27931 17.3334 6.50033V9.83366H15.6667V7.33366H7.33342V15.667H10.6667V17.3337H6.50008C6.27907 17.3337 6.06711 17.2459 5.91083 17.0896C5.75455 16.9333 5.66675 16.7213 5.66675 16.5003V12.3337H4.00008V10.667H5.66675V6.50033C5.66675 6.27931 5.75455 6.06735 5.91083 5.91107C6.06711 5.75479 6.27907 5.66699 6.50008 5.66699H10.6667V4.00033ZM2.33341 10.667V12.3337H0.666748V10.667H2.33341ZM2.33341 7.33366V9.00033H0.666748V7.33366H2.33341ZM2.33341 4.00033V5.66699H0.666748V4.00033H2.33341ZM2.33341 0.666992V2.33366H0.666748V0.666992H2.33341ZM5.66675 0.666992V2.33366H4.00008V0.666992H5.66675ZM9.00008 0.666992V2.33366H7.33342V0.666992H9.00008ZM12.3334 0.666992V2.33366H10.6667V0.666992H12.3334Z" />
                                        </svg>
                                    </span>
                                    <?php esc_html_e( 'Field Settings', 'bookingpress-appointment-booking' ); ?>
                                </a>
                            </el-dropdown-item>                            
                    </el-dropdown>
                </li>
                    <?php
                }
                if (current_user_can('bookingpress_settings') ) {
                    ?>
                <li class="bpa-nav-item <?php echo ( 'settings' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
                    <a href="<?php echo add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_settings), esc_url(admin_url() . 'admin.php?page=bookingpress')); ?>" class="bpa-nav-link">
                        <span class="material-icons-round">settings</span>
                    <?php esc_html_e('Settings', 'bookingpress-appointment-booking'); ?>
                    </a>
                </li>
                <?php } ?>
                <li class="bpa-nav-item bpa-nav-item__is-go-premium">					
                    <a href="javascript:void(0)" class="bpa-nav-link" @click="open_premium_modal">
                        <span class="material-icons-round">diamond</span>
                        <?php esc_html_e('Go Premium', 'bookingpress-appointment-booking'); ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="bpa-mob-nav-overlay" id="bpa-mob-nav-overlay"></div>
<?php } ?>

<el-dialog custom-class="bpa-dialog bpa-dialog--upgrade-to-premium" modal-append-to-body=false :visible.sync="premium_modal" :close-on-press-escape="close_modal_on_esc">
    <div class="bpa-dialog-heading">
        <div class="bpa-dialog-utp__head-wrap">
            <h3><?php esc_html_e('Unlock the Powerful Pro Features', 'bookingpress-appointment-booking'); ?></h3>
        </div>
    </div>
    <div class="bpa-dialog-body">
        <div class="bpa-utp__body-item-wrap">
            <h4><?php esc_html_e('Scale your appointment scheduling business', 'bookingpress-appointment-booking'); ?></h4>
            <p><?php esc_html_e('Simplify the booking experiences for your customers, automate employee', 'bookingpress-appointment-booking'); ?></p>
            <p style="line-height: 0 !important; margin-top: -20px !important;"><?php esc_html_e('management, and grow your business with even more features.', 'bookingpress-appointment-booking'); ?></p>
            <div class="bpa-utp__key-features">
                <h5><?php esc_html_e('Amazing Features', 'bookingpress-appointment-booking'); ?></h5>
                <div class="bpa-kf__item-row">
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('Award Winning Design', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('33+ Premium add-ons totally free', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                </div>
                <div class="bpa-kf__item-row">
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('15+ Payment gateways completely free', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('Effective employee scheduling', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                </div>
                <div class="bpa-kf__item-row">
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('Smooth two-way sync of bookings across calendars', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('Understand your business better with reports', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                </div>
                <div class="bpa-kf__item-row">
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('Email, Whatsapp & SMS Notification', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('24/7 Real-time Support', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                </div>
            </div> 
            <div class="bpa-utp-comparison-btns">
                <h4><?php esc_html_e('Check out our complete comparison', 'bookingpress-appointment-booking'); ?></h4>
                <div class="boa-cb__wrap">
                    <el-button class="bpa-btn bpa-btn--primary" @click="bookingpress_redirect_lite_vs_preminum_page" >
                        <?php esc_html_e('Compare Lite vs Premium', 'bookingpress-appointment-booking'); ?>
                    </el-button>
                    <el-button class="bpa-btn bpa-btn--secondary" @click="bookingpress_redirect_lite_vs_other_page">
                        <?php esc_html_e('BookingPress vs Others', 'bookingpress-appointment-booking'); ?>
                    </el-button>
                </div>
            </div>
        </div>
    </div>
    <div class="bpa-dialog-footer">
        <el-button class="bpa-btn bpa-btn--primary" @click="bookingpress_redirect_premium_page">
            <?php esc_html_e('Upgrade to BookingPress Pro Now', 'bookingpress-appointment-booking'); ?>
        </el-button>
    </div>
</el-dialog>