<?php
/**
 * @package Wordpress_Clone
 * @version 1.0.0
 *
 * Plugin Name: WP Clone
 * Plugin URI: https://github.com/achristodoulou/wp-clone-plugin
 * Description: This plugin can help you to clone your current WP install with database to a new directory ready to be accessed by the browser.
 * Author: Andreas Christodoulou
 * Author URI: http://www.digitzone.net/
*/

add_action( 'admin_menu', 'register_admin_menu_page' );

function register_admin_menu_page() {

    add_menu_page( 'WP Clone', 'WP Clone', 'manage_options', 'wp-clone/views/admin.php' );

}