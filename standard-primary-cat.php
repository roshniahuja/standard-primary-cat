<?php
/*
Plugin Name: Standard Primary Cat
Description: Set a primary category for your (custom) posts.
Author: Roshni Ahuja
Author URI: https://about.me/roshniahuja
Version: 1.0.0
Text Domain: standard-primary-cat
*/
if ( ! defined( 'ABSPATH' ) ) exit;

// define plugin path directory
if ( ! defined( 'SPC_PLUGIN_PATH' ) ) {
    define( 'SPC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

// including class file
include SPC_PLUGIN_PATH . 'includes/class-standard-primary-cat.php';

// initializing the class
$pc = new Standard_Primary_Category();
