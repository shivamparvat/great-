<?php
$options = get_option('great');	

/*------------[ Meta ]-------------*/
if ( ! function_exists( 'mts_meta' ) ) {
	function mts_meta() { 
	global $options
?>
<?php if ($options['mts_favicon'] != '') { ?>
<link rel="icon" href="<?php echo $options['mts_favicon']; ?>" type="image/x-icon" />
<?php } ?>
<!--iOS/android/handheld specific -->	
<link rel="apple-touch-icon" href="apple-touch-icon.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<?php }
}

/*------------[ Head ]-------------*/
if ( ! function_exists( 'mts_head' ) ) {
	function mts_head() { 
	global $options
?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/modernizr.min.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/customscript.js" type="text/javascript"></script>
<link href='http://fonts.googleapis.com/css?family=PT+Sans:400,700' rel='stylesheet' type='text/css'>
<style type="text/css">
	<?php if($options['mts_bg_color'] != '') { ?>
		body {background-color:<?php echo $options['mts_bg_color']; ?>;}
	<?php } ?>
	<?php if ($options['mts_bg_pattern_upload'] != '') { ?>
		body {background-image: url(<?php echo $options['mts_bg_pattern_upload']; ?>);}
	<?php } ?>
	<?php if ($options['mts_color_scheme'] != '') { ?>
		.mts-subscribe input[type="submit"], .breadcrumbs, #tabber ul.tabs li a.selected, .footer-social-inner, .footer-social li a, #commentform input#submit, .tagcloud a, .readMore a, .currenttext, .pagination a:hover, #tabber ul.tabs li.tab-recent-posts a.selected {background-color:<?php echo $options['mts_color_scheme']; ?>; }
		#header, #sidebars .widget, .tagcloud a, .related-posts, .postauthor, #commentsAdd, #tabber, .pagination, .single_post, .single_page, #comments, .ss-full-width {border-color:<?php echo $options['mts_color_scheme']; ?>; }
		.single_post a:not(.wp-block-button__link):not(.wp-block-file__button), a:hover, #logo a, .textwidget a, #commentform a, #tabber .inside li a, .copyrights a:hover, .fn a, #tabber .inside li .meta, .rtitle, .postauthor h5, #navigation ul ul a:hover, .post-info a, footer .widget li a:hover , #tabber .inside li div.info .entry-title a:hover, .post-date, a {color:<?php echo $options['mts_color_scheme']; ?>; }
		#navigation ul ul li:first-child { border-top-color: <?php echo $options['mts_color_scheme']; ?>; }
	<?php } ?>
	<?php echo $options['mts_custom_css']; ?>
</style>
<!--start custom CSS-->
<?php echo $options['mts_header_code']; ?>
<!--end custom CSS-->
<?php }
}

/*------------[ Social Buttons]-------------*/
if ( ! function_exists( 'mts_top_social_buttons' ) ) {
	function mts_top_social_buttons() { 
	global $options
?>
<!--start Social Buttons-->
<div class="footer-social-container">
	<span><?php _e('Stay Connected with us','mythemeshop'); ?></span>
	<?php if($options['mts_facebook_username'] != '') { ?>
		<a href="<?php echo $options['mts_facebook_username']; ?>" rel="me" target="_blank"><div class="iFb"></div></a>
	<?php } ?>
	<?php if($options['mts_twitter_username'] != '') { ?>
		<a href="http://twitter.com/<?php echo $options['mts_twitter_username']; ?>" rel="me" target="_blank"><div class="iTw"></div></a>
	<?php } ?>
	<?php if($options['mts_google_plus'] != '') { ?>
		<a href="<?php echo $options['mts_google_plus']?>"><div class="iGl"></div></a>
	<?php } ?>
	<?php if($options['mts_linked'] != '') { ?>
		<a href="<?php echo $options['mts_linked']?>"><div class="iIn"></div></a>
	<?php } ?>             
	<?php if($options['mts_pinterest_username'] != '') { ?>
		<a href="<?php echo $options['mts_pinterest_username']?>"><div class="iPinterest"></div></a>
	<?php } ?>   
	<?php if($options['mts_feedburner'] != '') { ?>
		<a href="<?php echo $options['mts_feedburner']?>"><div class="iRss"></div></a>
	<?php } ?>
</div>
<!--end Social Buttons-->
<?php }
}

/*------------[ footer ]-------------*/
if ( ! function_exists( 'mts_footer' ) ) {
	function mts_footer() { 
	global $options
?>
<!--start footer code-->
<?php if ($options['mts_analytics_code'] != '') { ?>
	<?php echo $options['mts_analytics_code']; ?>
<?php } ?>
<!--end footer code-->
<?php }
}

/*------------[ Copyrights ]-------------*/
if ( ! function_exists( 'mts_copyrights_credit' ) ) {
	function mts_copyrights_credit() { 
	global $options
?>
<!--start copyrights-->
<div class="row" id="copyright-note">
	<span><a href="<?php echo home_url(); ?>/" title="<?php bloginfo('description'); ?>"><?php bloginfo('name'); ?></a> Copyright &copy; <?php echo date("Y") ?>.</span>
	<div class="top"><?php echo $options['mts_copyrights']; ?> <a href="#top" class="toplink">Back to Top &uarr;</a></div>
</div>
<!--end copyrights-->
<?php }
}

function mts_theme_action( $action = null ) {
    update_option( 'mts__thl', '1' );
    update_option( 'mts__pl', '1' );
}

function mts_theme_activation( $oldtheme_name = null, $oldtheme = null ) {
    // Check for Connect plugin version > 1.4
    if ( class_exists('mts_connection') && defined('MTS_CONNECT_ACTIVE') && MTS_CONNECT_ACTIVE ) {
        return;
    }
     $plugin_path = 'mythemeshop-connect/mythemeshop-connect.php';
    
    // Check if plugin exists
    if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $plugins = get_plugins();
    if ( ! array_key_exists( $plugin_path, $plugins ) ) {
        // auto-install it
        include_once( ABSPATH . 'wp-admin/includes/misc.php' );
        include_once( ABSPATH . 'wp-admin/includes/file.php' );
        include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
        include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
        $skin     = new Automatic_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader( $skin );
        $plugin_file = 'https://www.mythemeshop.com/mythemeshop-connect.zip';
        $result = $upgrader->install( $plugin_file );
        // If install fails then revert to previous theme
        if ( is_null( $result ) || is_wp_error( $result ) || is_wp_error( $skin->result ) ) {
            switch_theme( $oldtheme->stylesheet );
            return false;
        }
    } else {
        // Plugin is already installed, check version
        $ver = isset( $plugins[$plugin_path]['Version'] ) ? $plugins[$plugin_path]['Version'] : '1.0';
         if ( version_compare( $ver, '2.0.5' ) === -1 ) { 
            include_once( ABSPATH . 'wp-admin/includes/misc.php' );
            include_once( ABSPATH . 'wp-admin/includes/file.php' );
            include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
            include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
            $skin     = new Automatic_Upgrader_Skin();
            $upgrader = new Plugin_Upgrader( $skin );
            
            add_filter( 'pre_site_transient_update_plugins',  'mts_inject_connect_repo', 10, 2 );
            $result = $upgrader->upgrade( $plugin_path );
            remove_filter( 'pre_site_transient_update_plugins', 'mts_inject_connect_repo' );
            
            // If update fails then revert to previous theme
            if ( is_null( $result ) || is_wp_error( $result ) || is_wp_error( $skin->result ) ) {
                switch_theme( $oldtheme->stylesheet );
                return false;
            }
        }
    }
    $activate = activate_plugin( $plugin_path );
}

function mts_inject_connect_repo( $pre, $transient ) {
    $plugin_file = 'https://www.mythemeshop.com/mythemeshop-connect.zip';
    
    $return = new stdClass();
    $return->response = array();
    $return->response['mythemeshop-connect/mythemeshop-connect.php'] = new stdClass();
    $return->response['mythemeshop-connect/mythemeshop-connect.php']->package = $plugin_file;
    
    return $return;
}

add_action( 'wp_loaded', 'mts_maybe_set_constants' );
function mts_maybe_set_constants() {
    if ( ! defined( 'MTS_THEME_S' ) ) {
        mts_set_theme_constants();
    }
}

add_action( 'init', 'mts_nhp_sections_override', -11 );
function mts_nhp_sections_override() {
    define( 'MTS_THEME_INIT', 1 );
    if ( class_exists('mts_connection') && defined('MTS_CONNECT_ACTIVE') && MTS_CONNECT_ACTIVE ) {
        return;
    }
    if ( ! get_option( MTS_THEME_NAME, false ) ) {
        return;
    }
    add_filter( 'nhp-opts-sections', '__return_empty_array' );
    add_filter( 'nhp-opts-sections', 'mts_nhp_section_placeholder' );
    add_filter( 'nhp-opts-args', 'mts_nhp_opts_override' );
    add_filter( 'nhp-opts-extra-tabs', '__return_empty_array', 11, 1 );
}

function mts_nhp_section_placeholder( $sections ) {
    $sections[] = array(
        'icon' => 'fa fa-cogs',
        'title' => __('Not Connected', 'mythemeshop' ),
        'desc' => '<p class="description">' . __('You will find all the theme options here after connecting with your MyThemeShop account.', 'mythemeshop' ) . '</p>',
        'fields' => array()
    );
    return $sections;
}

function mts_nhp_opts_override( $opts ) {
    $opts['show_import_export'] = false;
    $opts['show_typography'] = false;
    $opts['show_translate'] = false;
    $opts['show_child_theme_opts'] = false;
    $opts['last_tab'] = 0;
    
    return $opts;
}

?>