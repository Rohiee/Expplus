<?php
if (! empty($_REQUEST['bookingpress_sysinfo']) && ( $_REQUEST['bookingpress_sysinfo'] == 'bkp999repute' ) ) {
    include '../../../../../../wp-load.php';
    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    $directaccesskey = 'bkp999repute';
    $directaccess    = isset($_REQUEST['bookingpress_sysinfo']) ? sanitize_text_field($_REQUEST['bookingpress_sysinfo']) : '';

    if (is_user_logged_in() || $directaccesskey == $directaccess ) {
    } else {
        $redirect_to = user_admin_url();
        wp_safe_redirect($redirect_to);
    }

    $php_version = phpversion();

    $server_ip = isset($_SERVER['SERVER_ADDR']) ? sanitize_text_field($_SERVER['SERVER_ADDR']) : '';

    $servername = isset($_SERVER['SERVER_NAME']) ? sanitize_text_field($_SERVER['SERVER_NAME']) : '';

    // $server_user = $_ENV["USER"];

    $upload_max_filesize = ini_get('upload_max_filesize');

    $post_max_size = ini_get('post_max_size');

    $max_input_vars = ini_get('max_input_vars');
 
    if (ini_get('safe_mode') ) {
        $safe_mode = 'On';
    } else {
        $safe_mode = 'Off';
    }

    $memory_limit = ini_get('memory_limit');

    $apache_version = '';
    $server_software = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field($_SERVER['SERVER_SOFTWARE']) : '';
    $server_signature  = isset($_SERVER['SERVER_SIGNATURE']) ? sanitize_text_field($_SERVER['SERVER_SIGNATURE']) : '';

    if (function_exists('apache_get_version') ) {
        $apache_version = apache_get_version();
    } else {
        $apache_version = $server_software . '( ' . $server_signature . ' )';
    }

    $system_info = php_uname();

    // $mysql_server_version = mysqli_get_server_info();
    global $wpdb;
    $mysql_server_version = $wpdb->db_version();

    // WordPress details

    $wordpress_version = get_bloginfo('version');

    $wordpress_sitename = get_bloginfo('name');

    $wordpress_sitedesc = get_bloginfo('description');

    $wordpress_wpurl = site_url();

    $wordpress_url = home_url();

    $wordpress_admin_email = get_bloginfo('admin_email');

    $wordpress_language = get_bloginfo('language');

    // $wordpress_templateurl = wp_get_theme();

    $my_theme                      = wp_get_theme();
    $wordpress_templateurl         = $my_theme->get('Name');
    $wordpress_templateurl_version = $my_theme->get('Version');


    $wordpress_charset = get_bloginfo('charset');

    $wordpress_debug = WP_DEBUG;

    if ($wordpress_debug == true ) {
        $wordpress_debug = 'On';
    } else {
        $wordpress_debug = 'Off';
    }

    if (is_multisite() ) {
        $wordpress_multisite = 'Yes';
    } else {
        ( $wordpress_multisite = 'No' );
    }

    $plugin_dir_path      = WP_PLUGIN_DIR;
    $upload_dir_path      = wp_upload_dir();
    $bookingpress_active  = 'Deactive';
    $bookingpress_version = '';
    if (is_plugin_active('bookingpress-appointment-booking/bookingpress-appointment-booking.php') ) {
        $bookingpress_active  = 'Active';
        $bookingpress_version = get_option('bookingpress_version');
    }

    $folderpermission = substr(sprintf('%o', fileperms($upload_dir_path['basedir'])), -4);

    $folderlogpermission = substr(sprintf('%o', fileperms($plugin_dir_path . '/bookingpress-appointment-booking/log/')), -4);

    $folderlogfilepermission = '';
    if (file_exists($plugin_dir_path . '/bookingpress-appointment-booking/log/response.txt') ) {
        $folderlogfilepermission = substr(sprintf('%o', fileperms($plugin_dir_path . '/bookingpress-appointment-booking/log/response.txt')), -4);
    }

    $plugin_list    = get_plugins();
    $plugin_detail         = array();
    $active_plugins = get_option('active_plugins');

    foreach ( $plugin_list as $key => $plugin_detail ) {
        $is_active = in_array($key, $active_plugins);
        // filter for only gravityforms ones, may get some others if using our naming convention
        if ($is_active == 1 ) {
            $name      = substr($key, 0, strpos($key, '/'));
            $plugins_main_arr[] = array(
            'name'      => $plugin_detail['Name'],
            'version'   => $plugin_detail['Version'],
            'is_active' => $is_active,
            );
        }
    }
    $bookingpress_module = array(                    
        'bookingpress_staffmember_module' => 'Staff Member Management',
        'bookingpress_service_extra_module' => 'Service Extra',
        'bookingpress_coupon_module' => 'Coupon Management',
        'bookingpress_deposit_payment_module' => 'Desposit Payment',
        'bookingpress_bring_anyone_with_you_module' => 'Bring Any One With You',                    
    );
    foreach($bookingpress_module as $key => $value) {                    
        $is_module_active = get_option($key);
        if($is_module_active == 'true') {
            $activated_modules[] = $value;
        }
    }
    $activated_modules = !empty($activated_modules) ? implode(', ',$activated_modules) : '';

    ?>

    <style type="text/css">
    table
    {
        border:2px solid #cccccc;
        width:900px;
        font-family:Verdana, Arial, Helvetica, sans-serif;
        font-size:12px;
    }
    .title
    {
        border-bottom:2px solid #cccccc; padding:5px 0px 5px 15px; font-weight:bold;
    }
    .leftrowtitle
    {
        border-bottom:2px solid #cccccc; border-right:2px solid #cccccc; padding:5px 0px 5px 15px; width:250px; background-color:#333333; color:#FFFFFF; font-weight:bold;
    }
    .rightrowtitle
    {
        border-bottom:2px solid #cccccc; padding:5px 0px 5px 15px; width:650px; background-color:#333333;  color:#FFFFFF; font-weight:bold;
    }
    .leftrowdetails
    {
        border-bottom:2px solid #cccccc; border-right:2px solid #cccccc; padding:5px 0px 5px 15px; width:250px;
    }
    .rightrowdetails
    {
        border-bottom:2px solid #cccccc; padding:5px 0px 5px 15px; width:650px;
    }    
    </style>


    <table border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td colspan="2" class="title">Php Details</td>
    </tr>
    <tr>
        <td class="leftrowtitle">Variable Name</td>
        <td class="rightrowtitle">Details</td>
    </tr>
    <tr>
        <td class="leftrowdetails">PHP Version</td>
        <td class="rightrowdetails"><?php echo esc_html($php_version); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">System</td>
        <td class="rightrowdetails"><?php echo esc_html($system_info); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Apache Version</td>
        <td class="rightrowdetails"><?php echo esc_html($apache_version); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Server Ip</td>
        <td class="rightrowdetails"><?php echo esc_html($server_ip); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Server Name</td>
        <td class="rightrowdetails"><?php echo esc_html($servername); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Upload Max Filesize</td>
        <td class="rightrowdetails"><?php echo esc_html($upload_max_filesize); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Post Max Size</td>
        <td class="rightrowdetails"><?php echo esc_html($post_max_size); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Max Input Vars</td>
        <td class="rightrowdetails"><?php echo esc_html($max_input_vars); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Safe Mode</td>
        <td class="rightrowdetails"><?php echo esc_html($safe_mode); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Memory Limit</td>
        <td class="rightrowdetails"><?php echo esc_html($memory_limit); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">MySql Version</td>
        <td class="rightrowdetails"><?php echo esc_html($mysql_server_version); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Allow URL for fopen()</td>
        <td class="rightrowdetails"><?php echo ( 1 == ini_get('allow_url_fopen') ) ? 'Yes' : 'No'; ?></td>
    </tr>
    <tr>
        <td colspan="2" style="border-bottom:2px solid #cccccc;">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" class="title">WordPress Details</td>
    </tr>
    <tr>
        <td class="leftrowtitle">Variable Name</td>
        <td class="rightrowtitle">Details</td>
    </tr>
    <tr>
        <td class="leftrowdetails">Site Title</td>
        <td class="rightrowdetails"><?php echo esc_html($wordpress_sitename); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Tagline</td>
        <td class="rightrowdetails"><?php echo esc_html($wordpress_sitedesc); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Version</td>
        <td class="rightrowdetails"><?php echo esc_html($wordpress_version); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">WordPress address (URL)</td>
        <td class="rightrowdetails"><?php echo esc_url($wordpress_wpurl); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Site address (URL)</td>
        <td class="rightrowdetails"><?php echo esc_url($wordpress_url); ?></td>
    </tr>
    <?php if(current_user_can('administrator')) { ?>
    <tr>
        <td class="leftrowdetails">Admin Email</td>
        <td class="rightrowdetails"><?php echo esc_html($wordpress_admin_email); ?></td>
    </tr>
    <?php } ?>
    <tr>
        <td class="leftrowdetails">Charset</td>
        <td class="rightrowdetails"><?php echo esc_html($wordpress_charset); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Language</td>
        <td class="rightrowdetails"><?php echo esc_html($wordpress_language); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Active theme</td>
        <td class="rightrowdetails"><?php echo esc_url($wordpress_templateurl) . ' (' . esc_html($wordpress_templateurl_version) . ')'; ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Debug Mode</td>
        <td class="rightrowdetails"><?php echo esc_html($wordpress_debug); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Multisite Enable</td>
        <td class="rightrowdetails"><?php echo esc_html($wordpress_multisite); ?></td>
    </tr>
    <tr>
        <td colspan="2" style="border-bottom:2px solid #cccccc;">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" class="title">Bookingpress Details</td>
    </tr>
    <tr>
        <td class="leftrowtitle">Variable Name</td>
        <td class="rightrowtitle">Details</td>
    </tr>
    <tr>
        <td class="leftrowdetails">Bookingpress Status</td>
        <td class="rightrowdetails"><?php echo esc_html($bookingpress_active); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Bookingpress Version</td>
        <td class="rightrowdetails"><?php echo esc_html($bookingpress_version); ?></td>
    </tr>
    <?php if(current_user_can('administrator')) { ?>
    <tr>
        <td class="leftrowdetails">Upload Basedir</td>
        <td class="rightrowdetails"><?php echo esc_html($upload_dir_path['basedir']); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Upload Baseurl</td>
        <td class="rightrowdetails"><?php echo esc_url($upload_dir_path['baseurl']); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Upload Folder Permission</td>
        <td class="rightrowdetails"><?php echo esc_html($folderpermission); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Bookingpress Log Folder Permission</td>
        <td class="rightrowdetails"><?php echo esc_html($folderlogpermission); ?></td>
    </tr>
    <tr>
        <td class="leftrowdetails">Bookingpress Log File Permission</td>
        <td class="rightrowdetails"><?php echo esc_html($folderlogfilepermission); ?></td>
    </tr>
    <?php } ?>    
    <tr>
        <td colspan="2" class="title">Active Plugin List</td>
    </tr>
    
    <?php
    foreach ( $plugins_main_arr as $myplugin ) {
        ?>
        <tr>
            <td class="leftrowdetails"><?php echo esc_html($myplugin['name']); ?></td>
            <td class="rightrowdetails">
        <?php
        if ($myplugin['is_active'] == 1 ) {
            echo 'ACTIVE';
        } else {
            echo 'INACTIVE';
        }
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(' . esc_html($myplugin['version']) . ')'; 
        if($myplugin['name'] == 'BookingPress Pro - Appointment Booking plugin') {
            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$activated_modules;
        }
        ?>
        </td>
        </tr>
        <?php
    }
    ?>
        
        
        
    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    </table>
    <?php
    exit;
}
