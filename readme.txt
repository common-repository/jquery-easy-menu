=== Easy menus ===
Contributors: Extendyourweb.com
Donate link: http://www.extendyourweb.com
Tags: css, dropdown, menu, image menu, shortcodes, widget, pages, post, categories, multi, jquery, navigation, category list, themes, custom-styles, options-page, animations, effects
Requires at least: 2.8  
Tested up to: 3.9
Stable tag: 3.1

Plugin to load different types of menus with pictures.

== Description ==

[Plugin Demo&Manual](http://www.extendyourweb.com/easy-menus/) | [Plugin author](http://www.extendyourweb.com) | [Donate](http://www.extendyourweb.com)

Plugin to create different types of menus with pictures. 

The plugin allows you to add an image to each button of the menu. 

Button shortocodes in pages or post. Easy menu widget.

List of shortcodes: 
<ul>
<li>[EasyMenu menu = xxx style = 1 /] Horizontal menu / images / submenu</li> 
<li>[EasyMenu menu = xxx style = 2 /] Vertical menu with icons</li> 
<li>[EasyMenu menu = xxx style = 3 /] Vertical menu with icons 2</li> 
<li>[EasyMenu menu = xxx style = 5 /] Horizontal menu with icons</li> 
<li>[EasyMenu menu = xxx style = 9 /] Circles menu with images</li> 
<li>[xxx EasyMenu menu = style = 11 /] Horizontal menu icons / submenus</li> 
<li>[xxx EasyMenu menu = style = 12 /] Buttons icons menu / submenu</li>
</ul>
xxx *** is the menu id; 

You can include shortocodes within templates. For them, insert the following php code on a piece of a template: '<php echo apply_filters ('the_content', '[EasyMenu menu = 5 style = 1 /] ");? >' This example would load the menu with id 5 and style of menu 1.


== Installation ==

Either install the plugin via the WordPress admin panel, or ...

1. Upload `jquery-easy-menu` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

There are no configuration options in this plugin.

== Screenshots ==

1. Menus samples
2. Page/Post Shortcodes
3. Widget


== Changelog ==

= 3.1 =
* Released on 1/07/2014
* New plugin version, more menus.


= 2.1 =
* Released on 2012
* Initial release