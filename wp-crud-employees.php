<?php 
/**
 * Plugin Name: WP Employees CRUD
 * Description: This plugin performs CRUD Operations with Employees Table. Also on Activation it will create a dynamic wordpress page and it will have a shortcode.
 * Version: 1.0
 * Author: Muhammed Abdel-Ra'ouf
 * Author URI: https://www.linkedin.com/in/muhammedraouf92
 */

 if(!defined("ABSPATH")){
    exit;
 }

 define("WCE_DIR_PATH", plugin_dir_path(__FILE__)); 
 define("WCE_DIR_URL", plugin_dir_url(__FILE__));

 include_once WCE_DIR_PATH."My_Employee.php";

 $myEmployee=new MyEmployee;

 register_activation_hook( __FILE__,[$myEmployee,'wce_on_plugin_activate'] );

 register_deactivation_hook( __FILE__,[$myEmployee,'wce_delete_table']  );

 add_shortcode( 'wce-employee-layout',[$myEmployee,'wce_add_employee_page'] );
 add_action( 'wp_enqueue_scripts',[$myEmployee,'wce_add_assets'] );

 add_action( 'wp_ajax_wce_add_employee',[$myEmployee,'wce_handle_ajax_request'] );
 
 add_action( 'wp_ajax_wce_load_employees_data',[$myEmployee,'wce_handle_ajax_get_request'] );
 add_action( 'wp_ajax_wce_delete_employees_data',[$myEmployee,'wce_handle_ajax_delete_request'] );
 add_action( 'wp_ajax_wce_get_employee_data',[$myEmployee,'wce_handle_ajax_get_single_request'] );
 add_action( 'wp_ajax_wce_edit_employee',[$myEmployee,'wce_handle_ajax_edit_request'] );


?>