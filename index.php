<?php
/**
 * @package Wordpress_Clone
 * @version 0.1
 *
 * Plugin Name: Wordpress Clone
 * Plugin URI: http://wordpress.org/plugins/wordpress_clone/
 * Description: This plugin can help you to clone your current wordpress install with database to a new directory ready to be accessed by the browser.
 * Author: Andreas Christodoulou
 * Version: 0.1
 * Author URI: http://www.digitzone.net/
*/

add_action( 'admin_menu', 'register_admin_menu_page' );

function register_admin_menu_page() {

    add_menu_page( 'WP Clone', 'WP Clone', 'manage_options', 'wp-clone/views/admin.php' );

}