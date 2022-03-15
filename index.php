<?php
/*
Plugin Name: SlimStat Analytics - My Custom Report
Description: My super duper custom report
Version: 1.0
*/

class wp_slimstat_super_duper_report {

   public static function init() {
      if ( !class_exists( 'wp_slimstat' ) ) {
         return true;
      }

      // Register this report on the Audience screen
      add_filter( 'slimstat_reports_info', array( __CLASS__, 'add_report_info' ) );

      // Add a new option to the Add-ons Settings tab
      add_filter( 'slimstat_options_on_page', array( __CLASS__, 'add_options' ) );
   }

   // Define the report parameters (title, callback, classes, to which panel it should be added by default, etc...)
   public static function add_report_info( $_reports_info = array() ) {
      $_reports_info[ 'slim_super_duper' ] = array(
         // Report Title
         'title' => 'Super Duper',

         // Callback that renders the data retrieved from the DB
         'callback' => array( __CLASS__, 'raw_results_to_html' ),

         // Arguments for the callback
         'callback_args' => array(

            // The 'raw' param is the name of the function that retrieves the data from the DB
            // Please note: if you specify this paramenter, Slimstat will attempt to use it 
            // for the Excel generator and Email functionality
            'raw' => array( __CLASS__, 'get_raw_results' )
         ),

         // Report layout: normal, wide, full-width, tall
         // You can mix and match class names (normal tall) 
         'classes' => array( 'normal' ),

         // On what screen should this report appear by default?
         // slimview1: Real-time
         // slimview2: Overview
         // slimview3: Audience
         // slimview4: Site Analysis
         // slimview5: Traffic Sources
         // dashboard: WordPress Dashboard
         'locations' => array( 'slimview3' )
      );

      return $_reports_info;
   }

   // Use this function to append addon-specific settings to the Settings screen
   // Please make sure to use unique keys, so that they don't conflict with existing ones
   public static function add_options( $_settings = array() ) {
      $_settings[ 7 ][ 'rows' ][ 'addon_super_duper_header' ] = array(
         'title' => 'Super Duper',
         'type' => 'section_header'
      );
      $_settings[ 7 ][ 'rows' ][ 'addon_super_duper_switch' ] = array(
         'title' => 'Switch',
         'type' => 'toggle',
         'description' => 'This switch can turn on or off a special behavior in your add-on.'
      );
      $_settings[ 7 ][ 'rows' ][ 'addon_super_duper_text' ] = array(
         'title' => 'Text Field',
         'type' => 'text',
         'description' => 'A text field.'
      );
      $_settings[ 7 ][ 'rows' ][ 'addon_super_duper_textarea' ] = array(
         'title' => 'Text Area',
         'type' => 'textarea',
         'description' => 'A text area.'
      );

      return $_settings;
   }

   // Define the SQL query that will retrieve the data collected by the tracker in some unique and meaningful way
   public static function get_raw_results( $_report_id = 'p0' ) {
      $sql = "
         SELECT resource, COUNT(*) counthits
         FROM {$GLOBALS['wpdb']->prefix}slim_stats
         WHERE resource <> ''
         GROUP BY resource
         ORDER BY counthits DESC";

      return wp_slimstat_db::get_results( $sql );
   }

   // Tell Slimstat how to display this information as a report.
   public static function raw_results_to_html( $_args = array() ) {
      // Call the function that retrieves the data from the DB
      // This function should always return the ENTIRE dataset
      $all_results = call_user_func( $_args[ 'raw' ] , $_args );

      if ( empty( $all_results ) ) {
         echo '<p class="nodata">No data to display</p>';
      }
      else {
         // Slice the results to get only what we need
         $results = array_slice(
            $all_results,
            wp_slimstat_db::$filters_normalized[ 'misc' ][ 'start_from' ],
            wp_slimstat_db::$filters_normalized[ 'misc' ][ 'limit_results' ]
         );

         // Paginate results, if needed
         wp_slimstat_reports::report_pagination( count($results), count($all_results), false ); 

         // Loop through the resultset
         foreach ( $results as $a_row ) {
            echo "<p>{$a_row[ 'resource' ]} <span>{$a_row[ 'counthits' ]}</span></p>"; 
         }
      }

      // Exit if this function was called through Ajax (refresh button)
      if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
         die();
      }
   }
}
// end of class declaration

// Let's get this party started!
if ( function_exists( 'add_action' ) ) {
   add_action( 'plugins_loaded', array( 'wp_slimstat_super_duper_report', 'init' ), 10 );
}