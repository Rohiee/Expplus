<?php
    $requested_module = ( ! empty($_REQUEST['page']) && ( $_REQUEST['page'] != 'bookingpress' ) ) ? sanitize_text_field(str_replace('bookingpress_', '', $_REQUEST['page'])) : 'dashboard'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['page'] sanitized properly

    $bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/bookingpress_header.php';
    $bookingpress_load_file_name = apply_filters('bookingpress_modify_header_content', $bookingpress_load_file_name);
    require $bookingpress_load_file_name;

    do_action( 'bookingpress_page_admin_notices' );

    do_action('bookingpress_' . $requested_module . '_dynamic_view_load');
?>

<el-drawer custom-class="bpa-help-drawer" :visible.sync="needHelpDrawer" :direction="needHelpDrawerDirection">
    <el-container>
        <div class="bpa-back-loader-container" v-if="is_display_drawer_loader == '1'">
            <div class="bpa-back-loader"></div>
        </div>
        <div class="bpa-hd-header">
            <h1 class="bpa-page-heading">{{ requested_module }}</h1>
            <el-link :href="read_more_link" :underline="false" target="_blank" class="bpa-btn bpa-btn__small"><?php esc_html_e('Read more', 'bookingpress-appointment-booking'); ?></el-link>
        </div>
        <div class="bpa-hd-body bp_new_single_content" v-html="helpDrawerData"></div>
    </el-container>    
</el-drawer>
<el-drawer custom-class="bpa-help-drawer" :visible.sync="needHelpDrawer_add" :direction="add_needHelpDrawerDirection">
    <el-container>
        <div class="bpa-back-loader-container" v-if="is_display_drawer_loader == '1'">
            <div class="bpa-back-loader"></div>
        </div>
        <div class="bpa-hd-header">
            <h1 class="bpa-page-heading">{{ requested_module }}</h1>
            <el-link :href="read_more_link" :underline="false" target="_blank" class="bpa-btn bpa-btn__small"><?php esc_html_e('Read more', 'bookingpress-appointment-booking'); ?></el-link>
        </div>
        <div class="bpa-hd-body bp_new_single_content" v-html="helpDrawerData"></div>
    </el-container>    
</el-drawer>
