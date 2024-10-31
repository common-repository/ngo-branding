<?php
/*
* Plugin Name: NGO Branding
* Plugin URI: https://ngo-portal.org
* Description: Cleans up WordPress admin, SEO adapts, set permalinks, disables some updates, adds feature guest author and makes the NGO-Portal to feel like home. This plugin should be active on the entire portal. Many settings is dependent on the logged in users capabilities, so it does not do much more then cosmetic, SEO and security changes for network-admin, but more for site-admins and editors that will get a cleaner dashboard with the settings they need.
* Version: 1.3.4
* Author: George Bredberg
* Author URI: https://datagaraget.se
* Text Domain: ngo-branding
* Domain Path: /languages
* License GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	echo 'This file should not be accessed directly!';
	exit; // Exit if accessed directly
}

// Load translation
add_action( 'plugins_loaded', 'ngob_load_plugin_textdomain' );
 function ngob_load_plugin_textdomain() {
   load_plugin_textdomain( 'ngo-branding', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

/////////////////////////////////////////////////////
// Set permalinks. Runs only on plugin activation. //
/////////////////////////////////////////////////////
function ngob_reset_permalinks() {
	global $wp_rewrite;
	$wp_rewrite->set_permalink_structure( '/%postname%/' );
	flush_rewrite_rules();
}
add_action( 'register_activation_hook( __FILE__)', 'ngob_reset_permalinks' );
//add_action( 'init', 'ngob_reset_permalinks' ); // Debug

////////////////////////////////////////////
// Set permalink structure for new blogs. //
////////////////////////////////////////////
// This is not absolutely needed, but if ngo-branding is activated on a network site,
// the permalinks will be correct from start on the newly created ngo-sites.
function ngob_set_default_permalink_for_new_blogs($blog_id) {
	update_blog_option( $blog_id, 'permalink_structure', '/%postname%/' );
	flush_rewrite_rules(); //FIX: Necessary? Trying this to resolve down problem.
	//Permalinks does not work for new custom post types without resaving permalinks for that site. Maybe flush_rewrite_rules after activating ngo-concert /ngo-production?
}
add_action('init', 'ngob_set_default_permalink_for_new_blogs');
add_action('wpmu_activate_blog', 'ngob_set_default_permalink_for_new_blogs');

/////////////////////////////////////////////////////////////////////
// Disable admin bar and redirect from back-office to front-office //
/////////////////////////////////////////////////////////////////////
// Run for everyone that is not at least an editor (I.E is a subscriber or less.)

function ngob_redirect_user() {
    if ( ! current_user_can('edit_posts') ) {
        add_action( 'after_setup_theme', 'ngob_disable_admin_bar' ); // Disable front end adminbar
        add_action( 'admin_init', 'ngob_redirect_to_front' ); // If logged in, redirect from back office to front office
    }
}
add_action( 'wp_loaded', 'ngob_redirect_user' );

// Disable frontend admin bar
function ngob_disable_admin_bar() {
	add_filter('show_admin_bar', '__return_false');
}

// Redirect back to homepage and do not allow access to WP admin.
function ngob_redirect_to_front(){
	if ( ! defined('DOING_AJAX') && ! current_user_can('edit_posts') ) {
		wp_redirect( site_url() );
		exit;
	}
}

/* Hide WP version strings from scripts and styles - No need to help any hackers...
 * @return {string} $src
 * @filter script_loader_src
 * @filter style_loader_src
 */
function ngob_remove_wp_version_strings( $src ) {
	global $wp_version;
	parse_str(parse_url($src, PHP_URL_QUERY), $query);
	if ( !empty($query['ver']) && $query['ver'] === $wp_version ) {
		$src = remove_query_arg('ver', $src);
	}
	return $src;
}
add_filter( 'script_loader_src', 'ngob_remove_wp_version_strings' );
add_filter( 'style_loader_src', 'ngob_remove_wp_version_strings' );

// Hide WP version strings from generator meta tag used in RSS-feeds and in head elements in WP
function ngob_remove_version() {
	return '';
}
add_filter('the_generator', 'ngob_remove_version');

// Hide WP version string in Admin Footer
function ngob_footer_shh() {
	remove_filter( 'update_footer', 'core_update_footer' );
}
add_action( 'admin_menu', 'ngob_footer_shh' );

/////////////////////////////////////////////////////////////////
// Remove WP-footer and replace it with some nice branding. ;) //
/////////////////////////////////////////////////////////////////
// Does not affect Wally footer (if wally theme is activated). Wally footer needs to be disabled in child themes functions.php
function ngob_remove_footer_admin () {
	_e('Powered by', 'ngo-branding');?> <a href="http://ngo-portal.org" target="_blank"><?php _e('NGO-Portal', 'ngo-branding');?></a> | <?php _e('A platform for local cooperation in the social economy.', 'ngo-branding');?></a></p><?php
}
add_filter('admin_footer_text', 'ngob_remove_footer_admin', 99);

//////////////////////////
// Customize login page //
//////////////////////////

// Custom login - load css
function ngob_custom_login() {
	echo '<link rel="stylesheet" type="text/css" href="'.plugin_dir_url(__FILE__).'/custom_login/custom_login.css" />';
}
add_action('login_head', 'ngob_custom_login');

// Hide login errors
function ngob_login_error_override(){
	$error_message = __('You have entered wrong login information. <br/> To protect your account, we will not tell if it\'s the password or the user name that is wrong.', 'ngo-branding');
	return $error_message;
}
add_filter( 'login_errors', 'ngob_login_error_override' );

// Changing the logo link from wordpress.org to our site
function ngob_modify_login_url() {
	return get_bloginfo( 'url' );
}
add_filter('login_headerurl', 'ngob_modify_login_url');

// Changing the alt text of the logo
function ngob_login_logo_url_title() {
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertitle', 'ngob_login_logo_url_title' );

// No shaking if login failed
function ngob_no_shake_login_head() {
	remove_action('login_head', 'wp_shake_js', 12);
}
add_action('login_head', 'ngob_no_shake_login_head');

// Precheck remember me
function ngob_login_checked_remember_me() {
	add_filter( 'login_footer', 'ngob_rememberme_checked' );
}

function ngob_rememberme_checked() {
	echo "<script>document.getElementById('rememberme').checked = true;</script>";
}
//add_action( 'init', 'ngob_login_checked_remember_me' );

/////////////////////////////
// Change WP-logo in admin //
/////////////////////////////
function ngob_custom_logo() {
	echo '
	<style type="text/css">
		#wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
			background-image: url(' . plugins_url('images/fp_logo.gif', __FILE__) . ') !important;
			background-position: 0 0;
			color:rgba(0, 0, 0, 0);
		}
		#wpadminbar #wp-admin-bar-wp-logo.hover > .ab-item .ab-icon {
			background-position: 0 0;
		}
	</style>
	';
}
add_action('wp_before_admin_bar_render', 'ngob_custom_logo');

/////////////////////////
// Brand wp-logo menu. //
/////////////////////////
// These menues are turned off in ngo_menu_deactivate -> Clean up the admin bar
// So for them to show up, you have to modify down links, and then turn the relevant links on in ngo_menu_deactivate.

function ngob_admin_bar_wp_menu( $wp_admin_bar ) {
	$wp_admin_bar->add_menu( array(
		'id'    => 'wp-logo',
		'title' => '<span class="ab-icon"></span>',
		'href'  => 'https://ngo-portal.org',
		'meta'  => array(
			'title' => __('About NGO-portal', 'ngo-branding'),
		),
	) );

	// Add "About" link
	$wp_admin_bar->add_menu( array(
		'parent' => 'wp-logo',
		'id'     => 'about',
		'title'  => __('About NGO-portal', 'ngo-branding'),
		'href' => 'https://ngo-portal.org/om/',
		) );

	// Add a replace for WordPress.org link
	$wp_admin_bar->add_menu( array(
			'parent'    => 'wp-logo-external',
			'id'        => 'wporg',
			'title'     => __('NGO portal at Github', 'ngo-branding'),
			'href'      => 'https://github.com/ngo-portal',
	) );

	// Add codex link
	$wp_admin_bar->add_menu( array(
		'parent'    => 'wp-logo-external',
		'id'        => 'documentation',
		'title'     => __('Documentation', 'ngo-branding'),
		'href'      => 'https://ngo-portal.org/',
	) );

	// Add forums link
	// Not active in ngo_menu_deactivate, but here to override WP:s own link to forum in case ngo_menu_deactivate is deactivated. To avoid confusion.
	$wp_admin_bar->add_menu( array(
		'parent'    => 'wp-logo-external',
		'id'        => 'support-forums',
		'title'     => __('Donate', 'ngo-branding'),
		'href'      => 'https://ngo-portal.org/donera/',
	) );

	// Add feedback link
	$wp_admin_bar->add_menu( array(
		'parent'    => 'wp-logo-external',
		'id'        => 'feedback',
		'title'     => __('Error reports and requests', 'ngo-branding'),
		'href'      => 'https://ngo-portal.org/kontakt/',
	) );
}
remove_action( 'admin_bar_menu', 'wp_admin_bar_wp_menu', 99 );
add_action( 'admin_bar_menu', 'ngob_admin_bar_wp_menu', 99 );

/////////////////////////////
// Change greeting message //
/////////////////////////////
// FIX: Does not play well with translations since the greeting message 'Hej/Howdy' vill be different...
function ngob_replace_howdy( $wp_admin_bar ) {
	$my_account=$wp_admin_bar->get_node('my-account');
	$ngo_greeting= __('Welcome to NGO Portal', 'ngo-branding');
	$wplang = get_locale();
	if( $wplang == 'sv_SE' ) {
		$newtitle = str_replace( 'Hej,', $ngo_greeting, $my_account->title );
	} else if($wplang == 'en_US') {
		$newtitle = str_replace( 'Howdy,', $ngo_greeting, $my_account->title );
	}
	$wp_admin_bar->add_node( array(
		'id' => 'my-account',
		'title' => $newtitle,
	) );
}
add_filter( 'admin_bar_menu', 'ngob_replace_howdy',25 );

///////////////////////////////////////////////////////////////////////////////////
// Function to show random motivational /informational messages in WP-admin area //
///////////////////////////////////////////////////////////////////////////////////
function ngob_rnd_msg_get_text() {
	$motivation = array(
		__('Welcome to <a target=\"_blank\" href=\"https://ngo-portal.org\">NGO-portal</a>', 'ngo-branding'),
		__('NGO portal is a network for local and regional cooperation.', 'ngo-branding'),
		__('NGO portal is supported by <a target=\"_blank\" href=\"https://datagaraget.se\">https://datagaraget.se</a>', 'ngo-branding'),
		__('Show your events in the portal calendar to show it for everybody.', 'ngo-branding'),
		__('Events concerning only members should only be shown in your own calendar.', 'ngo-branding'),
		__('Not so many events? View a list of events rather than a calendar.', 'ngo-branding'),
		__('In the portal calendar you show public events.', 'ngo-branding'),
		__('Amateur Theatre Association? Show your productions in the portal.', 'ngo-branding'),
		__('Music Association? Show your concerts / gigs here.', 'ngo-branding'),
		__('Alone is not strong. We are happy that you are using the NGO portal.', 'ngo-branding'),
		__('Did you know you can show ads for your event on the portal?', 'ngo-branding'),
		__('Have an opinion or a suggestion? <a target=\"_blank\" href=\"https://ngo-portal.org/kontakt/\">Let us know.</a>', 'ngo-branding'),
	);
	shuffle( $motivation );
	return $motivation[0];
}

// This just echoes the chosen line, we'll position it later
function ngob_rnd_msg() {
	$chosen = "&nbsp;&nbsp;" . ngob_rnd_msg_get_text();
	echo "<p id='ngo_msg'>$chosen</p>";
}

// Now we set that function up to execute when the admin_notices action is called
add_action( 'admin_notices', 'ngob_rnd_msg' );

// We need some CSS to position the paragraph
function ngob_rnd_msg_css() {
	echo '<link rel="stylesheet" type="text/css" href="'.plugin_dir_url(__FILE__).'/css/style.css" />';
	if(is_rtl()) {
		echo '<link rel="stylesheet" type="text/css" href="'.plugin_dir_url(__FILE__).'/css/style-rtl.css" />';
	}
}
add_action( 'admin_head', 'ngob_rnd_msg_css' );

//////////////////////////////////////
// Add widget to WP-admin main page //
//////////////////////////////////////
function ngob_custom_dashboard_widgets() {
	global $wp_meta_boxes;
	$headline = __('Welcome to NGO-portal', 'ngo-branding');
	wp_add_dashboard_widget('custom_help_widget', $headline, 'custom_dashboard_help');
}

function custom_dashboard_help() {
	?><img src="<?php echo plugins_url( '/images/logongoportalwidewhite-45-x80.png', __FILE__ ); ?>" alt="NGO-Portal logo">
	<p><?php _e('NGO portal is a Wordpress platform which can be used by NGO networks and others who want to create a portal with interconnected yet independent web sites to coordinate and /or show events and more in one place.', 'ngo-branding');?></p>
	<p><b><?php _e('NGO-portal Support.', 'ngo-branding');?></b><br/>
	<?php _e('For questions and information please visit our website', 'ngo-branding');?>: <a href="http://ngo-portal.org" target="_blank">NGO-portal.org</a><br />
	<?php _e('Most plugins you can find on Wordpress. For source code for them see wordpress repository or ', 'ngo-branding');?> <a href="https://github.com/joje47/NGO-portal" target="_blank"> Github</a><br />
	<?php _e('For source code and the latest updates for other plugins see ', 'ngo-branding');?>: <a href="https://github.com/NGO-portal" target="_blank"><?php _e('NGO-portal on', 'ngo-branding');?> Github</a>.<br />
	<?php _e('For documentation see', 'ngo-branding');?>: <a href="https://ngo-portal.org" target="_blank">Kontaktformulär</a>.<br />
	<?php _e('Need more help? Contact the ', 'ngo-branding');?> <a href="mailto:info@datagaraget.se"><?php _e('developers', 'ngo-branding');?></a>.</p>
	<?php
}
add_action('wp_dashboard_setup', 'ngob_custom_dashboard_widgets');

//////////////////////////////////////////////////////////
// Add field in general settings for meta name keywords //
//////////////////////////////////////////////////////////
$ngob_general_setting = new ngob_general_setting();

class ngob_general_setting {
	function ngob_general_setting( ) {
		add_filter( 'admin_init' , array( &$this , 'ngob_register_fields' ) );
	}
	function ngob_register_fields() {
		register_setting( 'general', 'meta_site_keywords', 'esc_attr' );
		add_settings_field('site_keywords', '<label for="meta_site_keywords">'.__('Meta keyword' , 'ngo-branding' ).'</label>' , array(&$this, 'ngob_fields_html') , 'general' );
	}
	function ngob_fields_html() {
		$value = get_option( 'meta_site_keywords', '' );
		echo '<input type="text" style="width: 95%;" id="meta_site_keywords" name="meta_site_keywords" value="' . $value . '" />';
	}
}

add_action( 'wp_head', 'ngob_meta_description', 0 );
function ngob_meta_description() {
	$metadata = esc_attr( get_option( 'meta_site_keywords' ) );
	$description = esc_attr( get_bloginfo( 'description' ) );
	if( ! empty( $metadata ) ){
		echo '<meta name="keywords" content="' . $metadata . '" />';
	}
	if( ! empty( $description ) ){
		echo '<meta name="description" content="' . $description . '" />';
	}
}

///////////////////////////////////////////////////////////////////////////////////
// Add slug of page to NGO-sites. Shows up in settings - general on network site //
///////////////////////////////////////////////////////////////////////////////////
// Get site id
$blog_id = get_current_blog_id();

$ngob_sitelist_slug = new ngob_sitelist_slug();

class ngob_sitelist_slug {
	function ngob_sitelist_slug( ) {
		// Check if we are on the network site
		if( is_main_site( $blog_id ) ) {
			add_filter( 'admin_init' , array( &$this , 'ngob_register_slug' ) );
		}
	}
	function ngob_register_slug() {
		register_setting( 'general', 'sitelist_slug', 'esc_attr' );
		add_settings_field('sitelist_slug', '<label for="sitelist_slug">'.__('Slug for site-lists' , 'ngo-branding' ).'</label>' , array(&$this, 'ngob_slug_html') , 'general' );
	}
	function ngob_slug_html() {
		$value = get_option( 'sitelist_slug', '' );
		echo '<input type="text" id="sitelist_slug" name="sitelist_slug" value="' . $value . '" />';
	}
}

function ngob_get_sitelist_slug() {
	if( is_main_site( $blog_id ) ) {
		$sitelist_slug = esc_attr( get_option( 'sitelist_slug','' ) );
	} else {
		global $current_site;
		//return $current_site->blog_id;
		switch_to_blog($current_site->blog_id);
		$sitelist_slug = esc_attr( get_option( 'sitelist_slug','' ) );
		restore_current_blog();
	}
		return $sitelist_slug;
}

///////////////////////////////////////////////////////////////
// Add a custom field name "_ngob_guest_author_name" to post //
///////////////////////////////////////////////////////////////
add_filter( 'the_author', 'ngob_guest_author_name' );
//add_filter( 'get_the_author_display_name', 'ngob_guest_author_name' );

function ngob_guest_author_name( $name ) {
	global $post;
	$editor = $name;
	$author = get_post_meta( $post->ID, '_ngob_guest_author_name', true );

	if ( $author ) {
		$name = $author . " (" . __('posted by', 'ngo-branding') . "&nbsp;" . $editor . ")";
	}
	return $name;
}
// Add fields in add /edit post
add_action( 'add_meta_boxes', 'ngo_guest_author' );
	function ngo_guest_author() {
		$guest_author = __('Guest Author', 'ngo-branding');
		add_meta_box( 'ngo_guest_meta', $guest_author, 'ngo_guest_author_meta', 'post', 'side', 'high' );
	}

	function ngo_guest_author_meta( $post ) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'ngob_guest_author_nonce');
		$ngob_guest_author_name = get_post_meta( $post->ID, '_ngob_guest_author_name', true);
		_e( 'If guest author, enter name', 'ngo-branding' );
		?>
			<input type="text" style="width: 95%;" name="ngob_guest_author_name" value="<?php echo esc_attr( $ngob_guest_author_name ); ?>" />
		<?php
	}

add_action( 'save_post', 'ngo_save_guest_author_name' );
function ngo_save_guest_author_name( $post_ID ) {
	if ( ( !empty(  $_POST['ngob_guest_author_nonce'] ) ) && ( !wp_verify_nonce( $_POST['ngob_guest_author_nonce'], plugin_basename( __FILE__ ) ) ) ) {
		return;
	}

	global $post;
	if ( isset ( $post ) && ( $post->post_type == "post" ) ) {
		if (isset( $_POST ) ) {
			update_post_meta( $post_ID, '_ngob_guest_author_name', strip_tags( $_POST['ngob_guest_author_name'] ) );
		}
	}
}

?>
