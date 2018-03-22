<?php
/**
 * Plugin Name: A4B - Reporting And Analysis 
 * Plugin URI: http://abacies.com/
 * Description: Adds advanced reporting and analysis features to Alexa For Business
 * Author: Abacies
 * Author URI: http://abacies.com/
 * Version: 1.1
 *
 * @author Abacies
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;


/**
  * This class is the main class of the plugin
  *
  * When loaded, it loads the included plugin files and add functions to hooks or filters. The class also handles the admin menu
  *
  * @since 1.3.0
  */
class A4B_Analysis {

    /**
  	 * Version Number
  	 *
  	 * @var string
  	 * @since 1.3.0
  	 */
  	public $version = '1.3.1';

    /**
  	  * Main Construct Function
  	  *
  	  * Call functions within class
  	  *
  	  * @since 1.3.0
  	  * @uses A4B_Analysis::load_dependencies() Loads required filed
  	  * @uses A4B_Analysis::add_hooks() Adds actions to hooks and filters
  	  * @return void
  	  */
    function __construct() {
      $this->load_dependencies();
      $this->add_hooks();
      $this->check_license();
    }

    /**
  	  * Load File Dependencies
  	  *
  	  * @since 1.3.0
  	  * @return void
      * @todo If you are not setting up the addon settings tab, the quiz settings tab, or variables, simply remove the include file below
  	  */
    public function load_dependencies() {
      //include( 'php/create-csv.php' );
      //include( 'php/ajax.php' );
      include( 'php/addon-settings-tab-content.php' );
      include( 'php/results-tab-content.php' );
    }

    /**
  	  * Add Hooks
  	  *
  	  * Adds functions to relavent hooks and filters
  	  *
  	  * @since 1.3.0
  	  * @return void
      * @todo If you are not setting up the addon settings tab, the quiz settings tab, or variables, simply remove the relevant add_action below
  	  */
    public function add_hooks() {
      add_action( 'admin_init', 'a4b_addon_analysis_register_addon_settings_tabs' );
      add_action( 'admin_init', 'a4b_addon_analysis_register_stats_tabs' );
      add_action( 'wp_ajax_qsm_reporting_export', 'qsm_addon_analysis_ajax_export_results' );
      add_action( 'wp_ajax_nopriv_qsm_reporting_export', 'qsm_addon_analysis_ajax_export_results' );
    }

    /**
     * Checks license
     *
     * Checks to see if license is active and, if so, checks for updates
     *
     * @since 1.3.0
     * @return void
     */
     public function check_license() {

       if( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {

       	// load our custom updater
       	include( 'php/EDD_SL_Plugin_Updater.php' );
       }

      // retrieve our license key from the DB
      $analysis_data = get_option( 'qsm_addon_analysis_settings', '' );
      if ( isset( $logic_data["license_key"] ) ) {
        $license_key = trim( $analysis_data["license_key"] );
      } else {
        $license_key = '';
      }

     	// setup the updater
     	$edd_updater = new EDD_SL_Plugin_Updater( 'http://quizandsurveymaster.com', __FILE__, array(
     			'version' 	=> $this->version, 				// current version number
     			'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
     			'item_name' => 'Reporting & Analysis', 	// name of this plugin
     			'author' 	=> 'Frank Corso'  // author of this plugin
     		)
     	);
     }
}

/**
 * Loads the addon if QSM is installed and activated
 *
 * @since 1.3.0
 * @return void
 */
function a4b_addon_analysis_load() {
	// Make sure QSM is active
	if ( class_exists( 'MLWQuizMasterNext' ) ) {
		$qsm_analysis = new A4B_Analysis();
	} else {
		add_action( 'admin_notices', 'a4b_addon_analysis_missing_qsm' );
	}
}
add_action( 'plugins_loaded', 'a4b_addon_analysis_load' );

/**
 * Display notice if Quiz And Survey Master isn't installed
 *
 * @since       0.1.0
 * @return      string The notice to display
 */
function a4b_addon_analysis_missing_qsm() {
  echo '<div class="error"><p>QSM - Reporting & Analysis requires Quiz And Survey Master. Please install and activate the Quiz And Survey Master plugin.</p></div>';
}
?>
