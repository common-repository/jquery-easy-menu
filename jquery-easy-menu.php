<?php

/**
 * Easy menus
 *
 * Plugin to load different types of menus with pictures.
 *
 */

/**
 * Plugin Name: Easy menu
 * Plugin URI:  http://www.extendyourweb.com/easy-menus/
 * Description: Display image as a menu content.
 * Author:      Extendyourweb.com
 * Author URI:  http://www.extendyourweb.com
 * Version:     3.1
 * Text Domain: jqem
 * License:     GPL
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;

class jquery_easy_menu {

	protected $plugin_basename;

	/**
	 * Is last menu item of current page.
	 *
	 * @var $is_current_item
	 * @since 3.0 
	 * @access public
	 */
	public $is_current_item;

	/**
	 * Sets class properties.
	 * 
	 * @since 1.0
	 * @access public
	 *
	 * @uses add_action() To hook function.
	 * @uses plugin_basename() To get plugin's file name.
	 */
	public function __construct() {
		// Register init
		add_action( 'init', array( $this, 'init' ) );

		// Get a basename
		$this->plugin_basename = plugin_basename( __FILE__ );
	}

	/**
	 * Register actions & filters on init.
	 * 
	 * @since 1.0
	 * @access public
	 *
	 * @uses add_post_type_support() To enable thumbs for nav menu.
	 * @uses is_admin() To see if it's admin area.
	 * @uses jquery_easy_menu_Admin() To call admin functions.
	 * @uses add_action() To hook function.
	 * @uses apply_filters() Calls 'jqem_filter_menu_item_content' to
	 *                        overwrite menu item filter.
	 * @uses add_filter() To hook filters.
	 * @uses do_action() Calls 'jqem_init'.
	 */
	public function init() {
		// Add thumbnail support to menus
		add_post_type_support( 'nav_menu_item', 'thumbnail' );

		// Load admin file
		if ( is_admin() ) {
			require_once dirname( __FILE__ ) . '/inc/admin.php';
			new jquery_easy_menu_Admin();
		}

		// Register AJAX handler
		add_action( 'wp_ajax_jqem_added_thumbnail', array( $this, 'ajax_added_thumbnail' ) );

		// Register menu item content filter if needed
		if ( apply_filters( 'jqem_filter_menu_item_content', true ) ) {
			add_filter( 'nav_menu_css_class',       array( $this, 'register_menu_item_filter'   ), 15, 3 );
			add_filter( 'walker_nav_menu_start_el', array( $this, 'deregister_menu_item_filter' ), 15, 2 );
		}

		// Register plugins action links filter
		add_filter( 'plugin_action_links_' .               $this->plugin_basename, array( $this, 'action_links' ) );
		add_filter( 'network_admin_plugin_action_links_' . $this->plugin_basename, array( $this, 'action_links' ) );

		do_action( 'jqem_init' );
	}

	/**
	 * Load textdomain for internationalization.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @uses is_textdomain_loaded() To check if translation is loaded.
	 * @uses load_plugin_textdomain() To load translation file.
	 * @uses plugin_basename() To get plugin's file name.
	 */
	public function load_textdomain() {
		/* If translation isn't loaded, load it */
		if ( ! is_textdomain_loaded( 'jqem' ) )
			load_plugin_textdomain( 'jqem', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Return thumbnail's HTML after addition.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @uses absint() To get positive integer.
	 * @uses has_post_thumbnail() To check if item has thumb.
	 * @uses admin_url() To get URL of uploader.
	 * @uses esc_url() To escape URL.
	 * @uses add_query_arg() To append variables to URL.
	 * @uses get_the_post_thumbnail() To get item's thumb.
	 */
	public function ajax_added_thumbnail() {
		// Get submitted values
		$post_id      = isset( $_POST[ 'post_id' ]      ) ? absint( $_POST[ 'post_id' ]      ) : 0;
		$thumbnail_id = isset( $_POST[ 'thumbnail_id' ] ) ? absint( $_POST[ 'thumbnail_id' ] ) : 0;

		// If there aren't values, exit
		if ( 0 == $post_id || 0 == $thumbnail_id )
			die( '0' );

		// If there isn't featured image, exit
		if ( ! has_post_thumbnail( $post_id ) )
			die( '1' );

		// Form upload link
		$upload_url = admin_url( 'media-upload.php' );
		$query_args = array(
			'post_id'   => $post_id,
			'tab'       => 'gallery',
			'TB_iframe' => '1',
			'width'     => '640',
			'height'    => '425'
		);
		$upload_url = esc_url( add_query_arg( $query_args, $upload_url ) );

		// Item's featured image
		$post_thumbnail = get_the_post_thumbnail( $post_id, 'thumb' );

		// Full HTML
		$return_html = '<a href="' . $upload_url . '" data-id="' . $post_id . '" class="thickbox add_media">' . $post_thumbnail . '</a>';

		die( $return_html );		
	}

	/**
	 * Display an image as menu item content.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @uses has_post_thumbnail() To check if item has thumb.
	 * @uses apply_filters() Calls 'jqem_menu_item_content' to
	 *                        filter outputted content.
	 * @uses get_the_post_thumbnail() To get item's thumb.
	 *
	 * @param string $content Item's content
	 * @param int $item_id Item's ID
	 * @return string $content Item's content
	 */
	public function menu_item_content( $content, $item_id ) {
		
		return $content;
	}

	/**
	 * Display a hover image for menu item image.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * Thanks {@link http://www.webmasterworld.com/forum21/6615.htm}
	 *
	 * @uses get_post_meta() To get item's hover & active images IDs.
	 * @uses wp_get_attachment_image_src() To get hover image's data.
	 * @uses apply_filters() Calls 'jqem_menu_item_hover' to
	 *                        filter returned attributes.
	 *
	 * @param array $attr Image's attributes.
	 * @param object $attachment Image's post object data.
	 * @return array $attr New image's attributes.
	 */
	public function menu_item_hover( $attr, $attachment ) {
		if ( ( $hover_id = get_post_meta( $attachment->post_parent, '_jqem_hover', true ) ) && ! ( $this->is_current_item && get_post_meta( $attachment->post_parent, '_jqem_active', true ) ) ) {
			$image = wp_get_attachment_image_src( $hover_id, 'full', false );
			$url = $image[0];
			$src = $attr['src'];
			$attr['onmouseover'] = 'this.src=\'' . $url . '\'';
			$attr['onmouseout'] = 'this.src=\'' . $src . '\'';

			$attr = apply_filters( 'jqem_menu_item_hover', $attr, $attachment );
		}

		return $attr;
	}

	/**
	 * Display an active image for menu item.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @uses get_post_meta() To get item's active image ID.
	 * @uses wp_get_attachment_image_src() To get active image's data.
	 * @uses apply_filters() Calls 'jqem_menu_item_active' to
	 *                        filter returned attributes.
	 *
	 * @param array $attr Image's attributes.
	 * @param object $attachment Image's post object data.
	 * @return array $attr New image's attributes.
	 */
	public function menu_item_active( $attr, $attachment ) {
		if ( $this->is_current_item && ( $active_id = get_post_meta( $attachment->post_parent, '_jqem_active', true ) ) ) {
			$image = wp_get_attachment_image_src( $active_id, 'full', false );
			$url = $image[0];
			$attr['src'] = $url;

			$attr = apply_filters( 'jqem_menu_item_active', $attr, $attachment );
		}

		return $attr;
	}

	/**
	 * Register menu item content filter.
	 *
	 * Also check if menu item is of
	 * currently displayed page.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @uses has_post_thumbnail() To check if item has thumb.
	 * @uses add_filter() To hook filter.
	 *
	 * @param array $item_classes Item's classes.
	 * @param object $item Menu item data object.
	 * @param object $args Item's arguments.
	 * @return array $item_classes Item's classes.
	 */
	public function register_menu_item_filter( $item_classes, $item, $args = array() ) {
		if ( has_post_thumbnail( $item->ID ) ) {
			// Register filters
			add_filter( 'the_title',                          array( $this, 'menu_item_content' ), 15, 2 );
			add_filter( 'wp_get_attachment_image_attributes', array( $this, 'menu_item_hover'   ), 15, 2 );
			add_filter( 'wp_get_attachment_image_attributes', array( $this, 'menu_item_active'  ), 15, 2 );

			// Mark current item status
			if ( in_array( 'current-menu-item', $item_classes ) )
				$this->is_current_item = true;
			else
				$this->is_current_item = false;

			// Add 'has-image' class to the menu item
			$item_classes[] = 'has-image';
		}

		return $item_classes;
	}

	/**
	 * Deregister menu item content filter.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @uses remove_filter() To unhook filter.
	 *
	 * @param string $item_output Item's content
	 * @param object $item Menu item data object.
	 * @return string $item_output Item's content
	 */
	public function deregister_menu_item_filter( $item_output, $item ) {
		remove_filter( 'the_title',                          array( $this, 'menu_item_content' ), 15, 2 );
		remove_filter( 'wp_get_attachment_image_attributes', array( $this, 'menu_item_hover'   ), 15, 2 );
		remove_filter( 'wp_get_attachment_image_attributes', array( $this, 'menu_item_active'  ), 15, 2 );

		return $item_output;
	}

	/**
	 * Add action links to plugins page.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @uses jquery_easy_menu::load_textdomain() To load translations.
	 *
	 * @param array $link Plugin's action links.
	 * @return array $link Plugin's action links.
	 */
	public function action_links( $links ) {
		// Load translations
		$this->load_textdomain();

		$links['donate'] = '<a href="http://www.extendyourweb.com">' . __( 'Donate', 'jqem' ) . '</a>';
		return $links;
	}
}

/**
 * Initialize a plugin.
 *
 * Load class when all plugins are loaded
 * so that other plugins can overwrite it.
 *
 * @since 1.0
 *
 * @uses jquery_easy_menu To initialize plugin.
 */


function add_header_easymenu() {

 wp_enqueue_script('jquery');
 
 //wp_enqueue_style( 'jquery-easy-menu-css', plugin_dir_url( __FILE__ ).'css/style.css');
 //wp_enqueue_script( 'jquery-easy-menu-js', plugin_dir_url( __FILE__ ).'js/jquery-easy-menu.js');

}

// shortcodes

	function easymenu_add_generator_button_shortcodes( $page = null, $target = null ) {
		
		$site_url = get_option( 'siteurl' );
		
		echo '<a href="#TB_inline?width=640&height=600&inlineId=easymenu-generator-wrap" class="button thickbox" title="' . __( 'Insert menu', 'jqem' ) . '" data-page="' . $page . '" data-target="' . $target . '"><img src="' . plugins_url( 'images/minilogo.png' , __FILE__ ) . '"  alt="easymenu" /></a>';
		
				
	}
	
add_action( 'media_buttons', 'easymenu_add_generator_button_shortcodes', 100 );

		function easymenu_generator_popup() {
		?>
		<div id="easymenu-generator-wrap" style="display:none">
			<div id="easymenu-generator" style="text-align:center">
				<H2>Select Menu and Style:</H2>
                
                <?php
				
				$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

		// If no menus exists, direct the user to go and create some.

		if ( !$menus ) {

			echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.'), admin_url('nav-menus.php') ) .'</p>';


		}
		
		else {
			
					echo '<select id="easymenuid" name="easymenuid">';

	
			foreach ( $menus as $menu ) {


				echo '<option value="'. $menu->term_id .'">'. $menu->name .'</option>';

			}

		echo '</select>';
		}
				
				
				?>
                <br/>
                           
                <input type="radio" name="piicon" id="piicon" value="1" checked="checked"> Horizontal menu/images/submenu<br/><br/>
                <input type="radio" name="piicon" id="piicon" value="2"> Vertical menu with icons<br/><br/>
<input type="radio" name="piicon" id="piicon" value="3"> Vertical menu with icons 2<br/><br/>
<input type="radio" name="piicon" id="piicon" value="5"> Horizontal menu with icons<br/><br/>
<input type="radio" name="piicon" id="piicon" value="9"> Circles menu with images<br/><br/>
<input type="radio" name="piicon" id="piicon" value="11"> Horizontal menu icons/submenus<br/><br/>
<input type="radio" name="piicon" id="piicon" value="12"> Buttons icons menu/submenu<br/><br/>

<br/><br/>

   
               

                
<?php        

echo '<br/><a href="javascript: appendText(\'[easymenu menu=\'+jQuery(\'#easymenuid :selected\').val()+\' style=\'+jQuery(\'input[name=piicon]:checked\').val()+\' /]\n\')" class="button-primary" style="color:#fff" >INSERT MENU</a>';
	
?>
			</div>
		</div>
		<?php
	}
	

add_action( 'admin_footer', 'easymenu_generator_popup' );

	
	add_shortcode( 'easymenu', 'easymenuprint' );
function easymenuprint( $atts ) {
	extract( shortcode_atts( array(
		'menu' => '',
		'style' => '1'
	), $atts ) );
	
$html='';
	
	$menu_items = wp_get_nav_menu_items($menu);
	$menu_list = '';
	$namemenu="easymenu".$menu;
	if(!isset($style) || $style==1) {
		$namemenu="sdt_menu";
		$html=create_easy_menu_jquery1($menu_items, 0, $namemenu);
		Shortcodes_easymenu::add( 'css', 'sdt_menu' );
		Shortcodes_easymenu::add( 'js', 'sdt_menu' );
	}
	
	if(isset($style) && $style==2) {
		$html=create_easy_menu_jquery2($menu_items, 0, 'ca-menu');
		Shortcodes_easymenu::add( 'css', 'ca-menu' );
	}
	if(isset($style) && $style==3) {
		$html=create_easy_menu_jquery3($menu_items, 0, 'ca3-menu');
		Shortcodes_easymenu::add( 'css', 'ca3-menu' );
	}
	if(isset($style) && $style==5) {
		$html=create_easy_menu_jquery4($menu_items, 0, 'ca4-menu');
		Shortcodes_easymenu::add( 'css', 'ca4-menu' );
	}
	if(isset($style) && $style==9) {
		$html=create_easy_menu_jquery9($menu_items, 0, 'ca9-menu');
		Shortcodes_easymenu::add( 'css', 'ca9-menu' );
	}
	if(isset($style) && $style==11) {
		$html=create_easy_menu_jquery_normal($menu_items, 0, 'cbp-hrmenu', 0);
		
		$html='<nav id="cbp-hrmenu" class="cbp-hrmenu">'.$html.'</nav>';
		Shortcodes_easymenu::add( 'css', 'cbp-hrmenu' );
		Shortcodes_easymenu::add( 'js', 'cbp-hrmenu' );
	}
	
	if(isset($style) && $style==12) {
		$html=create_easy_menu_jquery_normal2($menu_items, 0, 'menu-icon', 0);
		
		$html='<div class="menu-icon">'.$html.'</div>';
		Shortcodes_easymenu::add( 'css', 'menu-icon' );
		Shortcodes_easymenu::add( 'js', 'menu-icon' );
	}
	return $html;
}

function create_easy_menu_jquery1($menu_items, $id_menu, $namemenu) {
	
	
	$encon=0;
	
$menu_list = '';
	foreach ( (array) $menu_items as $key => $menu_item ) {


		
		if($menu_item->menu_item_parent==$id_menu) {
			
			if($encon==0) {
				if($id_menu==0) $menu_list = '<ul id="'.$namemenu.'" class="'.$namemenu.'">';
				else $menu_list = '<div class="sdt_box">'; 
				$encon=1;
			}
			
			$image=get_the_post_thumbnail( $menu_item->ID, 'full');
			$title = $menu_item->title;
			$description = $menu_item->attr_title;
			$url = $menu_item->url;
			
			if($id_menu==0) {
				
				if($image!="") $menu_list .= '<li><a href="' . $url . '">'.$image.'<span class="sdt_active"></span><span class="sdt_wrap"><span class="sdt_link">' . $title . '</span><span class="sdt_descr">'.$description.'</span></span></a>';
							
				else $menu_list .= '<li><a href="' . $url . '"><span class="sdt_active"></span><span class="sdt_wrap"><span class="sdt_link">' . $title . '</span><span class="sdt_descr">'.$description.'</span></span></a>';
				
			}
			else {
				if($image!="")  $menu_list .= '<a href="' . $url . '">'.$image.'' . $title . '</a>';
				else $menu_list .= '<a href="' . $url . '">' . $title . '</a>';
			}
			$menu_list .=create_easy_menu_jquery1($menu_items, $menu_item->ID, $namemenu);
			
			if($id_menu==0) $menu_list .= '</li>';

		}
	}
	if($encon==1) {
		if($id_menu==0) $menu_list .= '</ul>';
		else  $menu_list .= '</div>';
	}
	
	return $menu_list;
}

function create_easy_menu_jquery2($menu_items, $id_menu, $namemenu) {
	
	
	$encon=0;
	
$menu_list = '';
	foreach ( (array) $menu_items as $key => $menu_item ) {


		
		if($menu_item->menu_item_parent==$id_menu) {
			
			if($encon==0) {
				if($id_menu==0) $menu_list = '<ul id="'.$namemenu.'" class="'.$namemenu.'">';
				else $menu_list = '<div class="sdt_box">'; 
				$encon=1;
			}
			
			$image=get_the_post_thumbnail( $menu_item->ID, 'full');
			$title = $menu_item->title;
			$description = $menu_item->attr_title;
			$url = $menu_item->url;
			
			if($id_menu==0) {
				if($image!="")  $menu_list .= '<li><a href="' . $url . '"><span class="ca-icon">' . $image . '</span><div class="ca-content"><h2 class="ca-main">' . $title . '</h2><h3 class="ca-sub">'.$description.'</h3></div></a>';
						
						
				else $menu_list .= '<li><a href="' . $url . '"><div class="ca-content"><h2 class="ca-main">' . $title . '</h2><h3 class="ca-sub">'.$description.'</h3></div></a>';
			}
			else {
				if($image!="")  $menu_list .= '<a href="' . $url . '">'.$image.'' . $title . '</a>';
				else $menu_list .= '<a href="' . $url . '">' . $title . '</a>';
			}
			//$menu_list .=create_easy_menu_jquery2($menu_items, $menu_item->ID, $namemenu);
			
			if($id_menu==0) $menu_list .= '</li>';

		}
	}
	if($encon==1) {
		if($id_menu==0) $menu_list .= '</ul>';
		else  $menu_list .= '</div>';
	}
	
	return $menu_list;
}

function create_easy_menu_jquery3($menu_items, $id_menu, $namemenu) {
	
	
	$encon=0;
	
$menu_list = '';
	foreach ( (array) $menu_items as $key => $menu_item ) {


		
		if($menu_item->menu_item_parent==$id_menu) {
			
			if($encon==0) {
				if($id_menu==0) $menu_list = '<ul id="'.$namemenu.'" class="'.$namemenu.'">';
				else $menu_list = '<div class="sdt_box">'; 
				$encon=1;
			}
			
			$image=get_the_post_thumbnail( $menu_item->ID, 'full');
			$title = $menu_item->title;
			$description = $menu_item->attr_title;
			$url = $menu_item->url;
			
			if($id_menu==0) {
				if($image!="")  $menu_list .= '<li><a href="' . $url . '"><span class="ca3-icon">' . $image . '</span><div class="ca3-content"><h2 class="ca3-main">' . $title . '</h2><h3 class="ca3-sub">'.$description.'</h3></div></a>';
						
						
				else $menu_list .= '<li><a href="' . $url . '"><div class="ca3-content"><h2 class="ca3-main">' . $title . '</h2><h3 class="ca3-sub">'.$description.'</h3></div></a>';
			}
			else {
				if($image!="")  $menu_list .= '<a href="' . $url . '">'.$image.'' . $title . '</a>';
				else $menu_list .= '<a href="' . $url . '">' . $title . '</a>';
			}
			//$menu_list .=create_easy_menu_jquery2($menu_items, $menu_item->ID, $namemenu);
			
			if($id_menu==0) $menu_list .= '</li>';

		}
	}
	if($encon==1) {
		if($id_menu==0) $menu_list .= '</ul>';
		else  $menu_list .= '</div>';
	}
	
	return $menu_list;
}


function create_easy_menu_jquery4($menu_items, $id_menu, $namemenu) {
	
	
	$encon=0;
	
$menu_list = '';
	foreach ( (array) $menu_items as $key => $menu_item ) {


		
		if($menu_item->menu_item_parent==$id_menu) {
			
			if($encon==0) {
				if($id_menu==0) $menu_list = '<ul id="'.$namemenu.'" class="'.$namemenu.'">';
				else $menu_list = '<div class="sdt_box">'; 
				$encon=1;
			}
			
			$image=get_the_post_thumbnail( $menu_item->ID, 'full');
			$title = $menu_item->title;
			$description = $menu_item->attr_title;
			$url = $menu_item->url;
			
			if($id_menu==0) {
				if($image!="")  $menu_list .= '<li><a href="' . $url . '"><span class="ca4-icon">' . $image . '</span><div class="ca4-content"><h2 class="ca4-main">' . $title . '</h2><h3 class="ca4-sub">'.$description.'</h3></div></a>';
						
						
				else $menu_list .= '<li><a href="' . $url . '"><div class="ca4-content"><h2 class="ca4-main">' . $title . '</h2><h3 class="ca4-sub">'.$description.'</h3></div></a>';
			}
			else {
				if($image!="")  $menu_list .= '<a href="' . $url . '">'.$image.'' . $title . '</a>';
				else $menu_list .= '<a href="' . $url . '">' . $title . '</a>';
			}
			//$menu_list .=create_easy_menu_jquery2($menu_items, $menu_item->ID, $namemenu);
			
			if($id_menu==0) $menu_list .= '</li>';

		}
	}
	if($encon==1) {
		if($id_menu==0) $menu_list .= '</ul>';
		else  $menu_list .= '</div>';
	}
	
	return $menu_list;
}

function create_easy_menu_jquery9($menu_items, $id_menu, $namemenu) {
	
	
	$encon=0;
	
$menu_list = '';
	foreach ( (array) $menu_items as $key => $menu_item ) {


		
		if($menu_item->menu_item_parent==$id_menu) {
			
			if($encon==0) {
				if($id_menu==0) $menu_list = '<ul id="'.$namemenu.'" class="'.$namemenu.'">';
				else $menu_list = '<div class="sdt_box">'; 
				$encon=1;
			}
			
			$image=get_the_post_thumbnail( $menu_item->ID, 'full');
			$title = $menu_item->title;
			$description = $menu_item->attr_title;
			$url = $menu_item->url;
			
			if($id_menu==0) {
				if($image!="")  $menu_list .= '<li><a href="' . $url . '"><span class="ca9-icon">' . $image . '</span><div class="ca9-content"><h2 class="ca9-main">' . $title . '</h2><h3 class="ca9-sub">'.$description.'</h3></div></a>';
						
						
				else $menu_list .= '<li><a href="' . $url . '"><div class="ca9-content"><h2 class="ca9-main">' . $title . '</h2><h3 class="ca9-sub">'.$description.'</h3></div></a>';
			}
			else {
				if($image!="")  $menu_list .= '<a href="' . $url . '">'.$image.'' . $title . '</a>';
				else $menu_list .= '<a href="' . $url . '">' . $title . '</a>';
			}
			//$menu_list .=create_easy_menu_jquery2($menu_items, $menu_item->ID, $namemenu);
			
			if($id_menu==0) $menu_list .= '</li>';

		}
	}
	if($encon==1) {
		if($id_menu==0) $menu_list .= '</ul>';
		else  $menu_list .= '</div>';
	}
	
	return $menu_list;
}


function create_easy_menu_jquery_normal($menu_items, $id_menu, $namemenu, $level) {
	
	
	if(!isset($level)) $level=$id_menu;
	$encon=0;
	
$menu_list = '';
	foreach ( (array) $menu_items as $key => $menu_item ) {
		
		if($menu_item->menu_item_parent==$id_menu) {
			
			if($encon==0) {
				
				if($level!=1) $menu_list = '<ul>'; 
				$encon=1;
			}
			
			$image=get_the_post_thumbnail( $menu_item->ID, 'full');
			$title = $menu_item->title;
			$url = $menu_item->url;
			if($id_menu==0) {
				$menu_list .= '<li><a href="' . $url . '">'.$image.$title.'</a><div class="cbp-hrsub"><div class="cbp-hrsub-inner">';
			}
			else {
				if($level!=1) $menu_list .= '<li><a href="' . $url . '">'.$image.$title . '</a>';
				else $menu_list .= '<div><h4><a href="' . $url . '">'.$image.$title.'</a></h4>';
			}
			
			$menu_list.=create_easy_menu_jquery_normal($menu_items, $menu_item->ID, $namemenu, $level+1);
			
			if($level==1) $menu_list.= '</div>';
			if($id_menu==0)  $menu_list.= '</div></div>';
			
			if($level!=1) $menu_list.= '</li>';
		}
	}
	if($encon==1) {
		
		if($level!=1) $menu_list.= '</ul>';
		//else $menu_list.= '</div>';
	}
	
	return $menu_list;
}

function create_easy_menu_jquery_normal2($menu_items, $id_menu, $namemenu, $level) {
	
	
	if(!isset($level)) $level=$id_menu;
	$encon=0;
	
$menu_list = '';
	foreach ( (array) $menu_items as $key => $menu_item ) {
		
		if($menu_item->menu_item_parent==$id_menu) {
			$image=get_the_post_thumbnail( $menu_item->ID, 'full');
			$title = $menu_item->title;
			$url = $menu_item->url;
			if($encon==0) {
				
				
				if($id_menu!=0) $menu_list='<p>';
				$encon=1;
			}
			
			
			if($id_menu==0) {
				$menu_list .= '<div class="item"><a class="link">'.$image.'</a><div class="item_content"><h2><a href="' . $url . '">'.$title.'</a></h2>';
			}
			else {
				$menu_list .= '<a href="' . $url . '">'.$image.$title . '</a>';

			}
			
			if($level<1) $menu_list.=create_easy_menu_jquery_normal2($menu_items, $menu_item->ID, $namemenu, $level+1);
			if($id_menu==0) $menu_list.= '</div></div>';
		}
	}
	if($encon==1) {
		
		if($id_menu!=0) $menu_list.='</p>';

	}
	
	return $menu_list;
}


function create_easy_menu_jquery_base($menu_items, $id_menu, $namemenu) {
	
	
	$encon=0;
	
$menu_list = '';
	foreach ( (array) $menu_items as $key => $menu_item ) {
		
		if($menu_item->menu_item_parent==$id_menu) {
			
			if($encon==0) {
				if($id_menu==0) $menu_list = '<ul class="'.$namemenu.'">';
				else $menu_list = '<ul>'; 
				$encon=1;
			}
			
			$image=get_the_post_thumbnail( $menu_item->ID, 'full');
			$title = $menu_item->title;
			$url = $menu_item->url;
			$menu_list .= '<li><a href="' . $url . '">' . $title . '</a>';
			
			$menu_list .=create_easy_menu_jquery3($menu_items, $menu_item->ID, $namemenu);
			
			$menu_list .= '</li>';
		}
	}
	if($encon==1) $menu_list .= '</ul>';
	
	return $menu_list;
}



function jquery_easy_menu_head_admin() {

echo '
<script>
function appendText(text) {
//Insert content
var str=text;
str=str.replace(/4s0/g, \'"\');
window.send_to_editor(str);

}
</script>
';

}

add_action( 'admin_head', 'jquery_easy_menu_head_admin' );
add_action('wp_enqueue_scripts', 'add_header_easymenu');

function jqem_instantiate() {
	new jquery_easy_menu();
}
add_action( 'plugins_loaded', 'jqem_instantiate', 15 );

/// add css and jquery

/**
 * Class for managing plugin assets
 */
class Shortcodes_easymenu {

	/**
	 * Set of queried assets
	 *
	 * @var array
	 */
	 
	 
	 
	static $assets = array( 'css' => array(), 'js' => array() );

	/**
	 * Constructor
	 */
	function __construct() {
		// Register
		add_action( 'wp_head',                     array( __CLASS__, 'register' ) );
		add_action( 'admin_head',                  array( __CLASS__, 'register' ) );
		// Enqueue
		add_action( 'wp_footer',                   array( __CLASS__, 'enqueue' ) );
		add_action( 'admin_footer',                array( __CLASS__, 'enqueue' ) );
		// Custom CSS
		add_action( 'wp_footer',                   array( __CLASS__, 'custom_css' ), 99 );

	}

	/**
	 * Register assets
	 */

	/**
	 * Add asset to the query
	 */
	public static function add( $type, $handle ) {
		
		if($type=='js') {
			
			if($handle=="sdt_menu") wp_enqueue_script( 'sdt_menu', plugin_dir_url( __FILE__ ).'js/jquery.easing.1.3.js');
			if($handle=="cbp-hrmenu") wp_enqueue_script( 'cbp-hrmenu', plugin_dir_url( __FILE__ ).'js/cbp-hrmenu.js');
			if($handle=="menu-icon") wp_enqueue_script( 'menu-icon', plugin_dir_url( __FILE__ ).'js/menu-icon.js');
		}
		
		if($type=='css') {
			if($handle=="sdt_menu")wp_enqueue_style( 'sdt_menu', plugin_dir_url( __FILE__ ).'css/sdt_menu.css');
			if($handle=="ca-menu")wp_enqueue_style( 'ca-menu', plugin_dir_url( __FILE__ ).'css/ca-menu.css');
			if($handle=="ca4-menu")wp_enqueue_style( 'ca4-menu', plugin_dir_url( __FILE__ ).'css/ca-menu4.css');
			if($handle=="ca3-menu")wp_enqueue_style( 'ca3-menu', plugin_dir_url( __FILE__ ).'css/ca-menu3.css');
			if($handle=="ca2-menu")wp_enqueue_style( 'ca2-menu', plugin_dir_url( __FILE__ ).'css/ca-menu2.css');
			if($handle=="ca5-menu")wp_enqueue_style( 'ca5-menu', plugin_dir_url( __FILE__ ).'css/ca-menu5.css');
			if($handle=="ca6-menu")wp_enqueue_style( 'ca6-menu', plugin_dir_url( __FILE__ ).'css/ca-menu6.css');
			if($handle=="ca9-menu")wp_enqueue_style( 'ca9-menu', plugin_dir_url( __FILE__ ).'css/ca-menu9.css');
			if($handle=="cbp-hrmenu")wp_enqueue_style( 'cbp-hrmenu', plugin_dir_url( __FILE__ ).'css/cbp-hrmenu.css');
			if($handle=="menu-icon")wp_enqueue_style( 'menu-icon', plugin_dir_url( __FILE__ ).'css/menu-icon.css');
			
		}
		
	}

}
// widget

class wp_easymenu extends WP_Widget {



	function wp_easymenu() {



		$widget_ops = array('classname' => 'wp_easymenu', 'description' => 'Easy menus. Choose the menu and design.' );



		$this->WP_Widget('wp_easymenu', 'Easy Menus', $widget_ops);



	}



	function widget($args, $instance) {
		extract($args, EXTR_SKIP);

		$site_url = get_option( 'siteurl' );

		//$instance['hide_is_admin']

			echo $before_widget;

			//echo easymenu_render("", $instance);
			$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
			$menu = empty($instance['menu']) ? '&nbsp;' : apply_filters('widget_menu', $instance['menu']);
			$style = empty($instance['style']) ? '&nbsp;' : apply_filters('widget_style', $instance['style']);
			if($title!="") echo '<h3>'.$title.'</h3>';
			echo apply_filters( 'the_content', "[easymenu menu=".$menu." style=".$style." /]");


			echo $after_widget;

	}
	
	
	
	

	function update($new_instance, $old_instance) {
		

		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);

		$instance['style'] = strip_tags($new_instance['style']);
	

		$instance['menu']=strip_tags($new_instance['menu']);
		
		

		return $instance;



	}



	function form($instance) {



		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'menu' => '', 'style' => '') );



		$title = strip_tags($instance['title']);



		$id=rand(0, 99999);



		$values = strip_tags($instance['menu']);



		$style = strip_tags($instance['style']);


		$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );







		// If no menus exists, direct the user to go and create some.



		if ( !$menus ) {

			echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.'), admin_url('nav-menus.php') ) .'</p>';

			return;

		}
		
		$auxi=rand()*10000;
		
		
		

			?>

            

        <p>

		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:') ?></label>

		<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />

	</p>

        <p>

		<label for="<?php echo $this->get_field_id('menu'); ?>"><?php _e('Select Menu:'); ?></label>

		<select id="<?php echo $this->get_field_id('menu'); ?>" name="<?php echo $this->get_field_name('menu'); ?>">

		<?php

			foreach ( $menus as $menu ) {

				$selected = $values == $menu->term_id ? ' selected="selected"' : '';

				echo '<option'. $selected .' value="'. $menu->term_id .'">'. $menu->name .'</option>';

			}

		?>

		</select>
	</p>
    <hr/>
 <h3>Design</h3>
 <p>

		<label for="<?php echo $this->get_field_id('style'); ?>"><?php _e('Select style:'); ?></label>



		<select id="<?php echo $this->get_field_id('style'); ?>" name="<?php echo $this->get_field_name('style'); ?>" >

	<option value="1" <?php if($style=="1") echo 'selected="selected"'; ?>>Horizontal menu/images/submenu</option>
    <option value="2" <?php if($style=="2") echo 'selected="selected"'; ?>>Vertical menu with icons</option>
    <option value="3" <?php if($style=="3") echo 'selected="selected"'; ?>>Vertical menu with icons 2</option>
    <option value="5" <?php if($style=="5") echo 'selected="selected"'; ?>>Horizontal menu with icons</option>
    <option value="9" <?php if($style=="9") echo 'selected="selected"'; ?>>Circles menu with images</option>
    <option value="11" <?php if($style=="11") echo 'selected="selected"'; ?>>Horizontal menu icons/submenus</option>
    <option value="12" <?php if($style=="12") echo 'selected="selected"'; ?>>Buttons icons menu/submenu</option>
</select>
	</p>

<p>
<a href="http://www.extendyourweb.com/" target="_blank">Manual & support</a>
</p>
<p>
Download more plugins/widgets/themes:
</p>

<p>
<a href="http://www.extendyourweb.com/" target="_blank" title="Download more plugins and widgets">www.extendyourweb.com</a>
</p>
<?php 



	}



}

add_action( 'widgets_init', create_function('', 'return register_widget("wp_easymenu");') );

?>