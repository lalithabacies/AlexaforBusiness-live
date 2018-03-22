<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates a CSV file from passed arrays
 *
 * @since 1.3.0
 */
class MLW_Export_CSV {

  /**
	 * File name to be used for CSV file
	 *
	 * @var string
	 * @since 1.3.0
	 */
	public $filename = 'export.csv';

  /**
	 * Opened file
	 *
	 * @var fopen
	 * @since 1.3.0
	 */
	public $file = false;

  /**
   * Stores the filename and opens the file for writing to
   *
   * @param string $filename The name of the file
   * @since 1.3.0
   */
  function __construct( $filename ) {
    $this->filename = $filename;
    $filename_path = plugin_dir_path( __FILE__ ).$filename;
    $this->file = fopen( $filename_path, 'w' );
  }

  /**
   * Adds a new row in the csv using the supplied array
   *
   * @param array $row A single dimensional array that contains the contents of each column in one row
   * @since 1.3.0
   */
  function add_row( $row ) {
    fputcsv( $this->file, $row );
  }

  /**
   * Closes the file
   *
   * @return bool If the file successfully closed or not
   * @since 1.3.0
   */
  function finalize() {
    return fclose( $this->file );
  }

	/**
	 * Returns the URL to the file
	 *
	 * @return string The URL to the csv file
	 * @since 1.3.0
	 */
	function get_url() {
		return plugin_dir_url( __FILE__ ) . $this->filename;
	}

	/**
	 * Returns the path to the file
	 *
	 * @return string The path to the csv file
	 * @since 1.3.0
	 */
	function get_path() {
		return plugin_dir_path( __FILE__ ) . $this->filename;
	}
}

?>
