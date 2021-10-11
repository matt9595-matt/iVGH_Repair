<?php
//WP Plugin Credentials
/**
 * Plugin Name: iVGHRepair
 * Plugin URI: http://www.ivgh.com
 * Description: GSX Repair Tracking Application
 * Version: 1.0
 * Author: Matt
 * Author URI: http://www.ivgh.com
 **/
//Shortcode Function to Call
function GSXResults()
{
   //call repair status function
   require_once('iVGHRepair_Stat.php');
   //create class instance
   $GSXRepair = new RepairTracker();
   //call form function and submission checker
   $GSXRepair -> Repair_Status_Form();
   $GSXRepair -> GSX_ID();

}
 add_shortcode('iVGHRepair','GSXResults');


?>