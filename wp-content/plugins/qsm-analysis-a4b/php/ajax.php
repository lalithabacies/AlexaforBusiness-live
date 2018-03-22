<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function qsm_addon_analysis_ajax_export_results() {
  global $wpdb;
  $quiz_id = intval( $_POST["quizID"] );
  $filter_user = intval( $_POST["user"] );
  $filter_name = sanitize_text_field( $_POST["name"] );
  $filter_business = sanitize_text_field( $_POST["business"] );
  $start_date = sanitize_text_field( $_POST["start_date"] );
  $end_date = sanitize_text_field( $_POST["end_date"] );
  $args = array(
    'quiz_id' => $quiz_id
  );
  if ( ! empty ( $filter_user ) && 0 !== $filter_user ) {
    $args["by_user"] = $filter_user;
  }
  if ( ! empty ( $filter_business ) ) {
    $args["by_business"] = $filter_business;
  }
  if ( ! empty ( $filter_name ) ) {
    $args["by_name"] = $filter_name;
  }
  if ( ! empty ( $start_date ) && ! empty ( $end_date ) ) {
    $args["start_date"] = $start_date;
    $args["end_date"] = $end_date;
  }
  $file = qsm_addon_analysis_results_create_csv( $args );

  echo json_encode( array( 'filename' => $file ) );

  die();
}

?>
