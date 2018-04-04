<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers your tab in the addon  settings page
 *
 * @since 1.3.0
 * @return void
 */
function a4b_addon_analysis_register_stats_tabs() {
  global $mlwQuizMasterNext;
  if ( ! is_null( $mlwQuizMasterNext ) && ! is_null( $mlwQuizMasterNext->pluginHelper ) && method_exists( $mlwQuizMasterNext->pluginHelper, 'register_quiz_settings_tabs' ) ) {
    $mlwQuizMasterNext->pluginHelper->register_stats_settings_tab( "Reporting And Analysis", 'qsm_addon_analysis_stats_tabs_content' );
  }
}

/**
 * Generates the content for your addon settings tab
 *
 * @since 1.3.0
 * @return void
 */
function a4b_addon_analysis_stats_tabs_content() {

  //Enqueue your scripts and styles
  wp_enqueue_script('plotlyjs', plugins_url( '../js/plotly.min.js' , __FILE__ ), array( 'jquery' ) );
  wp_enqueue_script( 'qsm_analysis_admin', plugins_url( '../js/qsm-analysis-admin.js' , __FILE__ ), array( 'plotlyjs', 'jquery' ) );
  wp_enqueue_style( 'qsm_analysis_admin_style', plugins_url( '../css/qsm-analysis-admin.css' , __FILE__ ) );
  wp_enqueue_script( 'jquery-ui-core' );
  wp_enqueue_script( 'jquery-ui-button' );
  wp_enqueue_script( 'jquery-ui-datepicker' );
  global $wpdb;

  ?>

  <link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.css" rel="stylesheet" />

    <?php


    $quiz_id = intval( $_POST["qmn_selected_quiz"] );
    $table_name = $wpdb->prefix . "mlw_results";
    if ( isset( $_POST['analyze_quiz_results_filter_nonce'] ) ) {
    $filter_sql = '';
    $filter_user = intval( $_POST["filter_user"] );
    $filter_name = sanitize_text_field( $_POST["filter_name"] );
    $filter_business = sanitize_text_field( $_POST["filter_business"] );
    if ( !empty( $_POST["date_start"] ) && !empty( $_POST["date_end"] ) ) {
      $start_date = date( "Y-m-d 00:00:00", strtotime( sanitize_text_field( $_POST["date_start"] ) ) );
      $end_date = date( "Y-m-d 23:59:59", strtotime( sanitize_text_field( $_POST["date_end"] ) ) );
      $filter_sql .= " AND (time_taken_real BETWEEN '$start_date' AND '$end_date')";
    }
    if ( !empty( $filter_user ) ) {
      $filter_sql .= " AND (user = $filter_user)";
    }
    if ( !empty( $filter_name ) ) {
      $filter_sql .= " AND (name LIKE '%$filter_name%')";
    }
    if ( !empty( $filter_business ) ) {
      $filter_sql .= " AND (business LIKE '%$filter_business%')";
    }
    $results_data = $wpdb->get_results( "SELECT quiz_system, point_score, correct_score, correct, total, name, business, email, phone, user,
        time_taken, time_taken_real, quiz_results FROM $table_name WHERE (quiz_id = $quiz_id) AND (deleted=0)$filter_sql ORDER BY time_taken_real ASC" );
    } else {
      $results_data = $wpdb->get_results( "SELECT quiz_system, point_score, correct_score, correct, total, name, business, email, phone, user,
          time_taken, time_taken_real, quiz_results FROM $table_name WHERE quiz_id = $quiz_id AND deleted=0 ORDER BY time_taken_real ASC" );
    }
    
    $request_total=0;
    $response_total=0;
    $device_total=0;
    
    //get_username and get_userid functions are defined in alexa-for-business plugin
    
    $request_data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/requests_read',json_encode(array('username'=>get_username()))));
    $request_total=count($request_data);
    
    $response_data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/scan_response',json_encode(array())));
    //$response_total=$response_data->Count;
    
    $device_data=json_decode(doCurl_POST('https://nexter-alexa-for-business.herokuapp.com/a4b/api/v1.0/get_devices',json_encode(array())));
    $device_total=count($device_data);
    
    //compile results
    $results_total = 0;
    $results_average_points = 0;
    $results_average_score = 0;
    $results_average_correct_answers = 0;
    $results_total_questions = 0;
    $results_average_time = 0;
    $answer_data = array();
    $quiz_system = 0;

    $array_for_json = array();
    $question_counter = 0;
    if($response_data){
        $array_for_json[$question_counter]["questionText"]="Response Last Six Months";
        $array_for_json[$question_counter]["totalAnswers"]=$response_total;
        $array_for_json[$question_counter]["averagePoints"]= 7;
        $array_for_json[$question_counter]["correct"] = 55;
        $answer_counter = 0;
        $total_res      = 0;
        $answer_array = array();
        
        $new = array();
        $last_six_month = array();
        for ($j = 0; $j <= 5; $j++) {
            $last_six_month[date("m/Y", strtotime(" -$j month"))]=0;
        }
        foreach($response_data->Items as $arr){
            list($userid_res,$room_res)=explode('_@_',$arr->RequestName);
            if($userid_res == get_userid()){
                $dateformat = explode(",",$arr->Date)[0];
                $date_split = explode("/",$dateformat);
                $date       = $date_split[1]."/".$date_split[2];
                if(array_key_exists($date,$new)){
                    $new[$date]+=1;
                }else{
                    $new[$date]=1;
                }
                $req_name = explode("_@_",$arr->RequestName);
                if(get_userid() == $req_name[0]){
                    $total_res++;
                }
                $response_total=$total_res;
            }
        }
        $new = array_merge($last_six_month,$new);
        foreach ($new as $key=>$value) {
            $answer_array["answerText"] = $key;
            $answer_array["totalSelected"] = $value;
            $array_for_json[$question_counter]["answers"][] = $answer_array;
            $answer_counter++;
        }
        $question_counter++;
    }
    
    if($response_data){
        $array_for_json[$question_counter]["questionText"]="Response By Category";
        $array_for_json[$question_counter]["totalAnswers"]=$response_total;
        $array_for_json[$question_counter]["averagePoints"]= 7;
        $array_for_json[$question_counter]["correct"] = 55;
        $answer_counter = 0;
        $answer_array = array();
        
        $new = array();
        foreach($response_data->Items as $arr){
            list($userid_res,$room_res)=explode('_@_',$arr->RequestName);
            if($userid_res == get_userid()){
                if(array_key_exists($arr->RequestType,$new)){
                    $new[$arr->RequestType]+=1;
                }else{
                    $new[$arr->RequestType]=1;
                }
            }
        }
    
        foreach ($new as $key=>$value) {
            $answer_array["answerText"] = $key;
            $answer_array["totalSelected"] = $value;
            $array_for_json[$question_counter]["answers"][] = $answer_array;
            $answer_counter++;
        }
        $question_counter++;
    }
    
    if($response_data){
        $array_for_json[$question_counter]["questionText"]="Response By Room Type";
        $array_for_json[$question_counter]["totalAnswers"]=$response_total;
        $array_for_json[$question_counter]["averagePoints"]= 7;
        $array_for_json[$question_counter]["correct"] = 55;
        $answer_counter = 0;
        $answer_array = array();
        
        $new = array();
        foreach($response_data->Items as $arr){
            if(array_key_exists($arr->RequestName,$new)){
                $new[$arr->RequestName]+=1;
            }else{
                $new[$arr->RequestName]=1;
            }
        }
    
        foreach ($new as $key=>$value){
            list($userid_res,$room_res)=explode('_@_',$key);
            if($userid_res == get_userid()){
            $answer_array["answerText"] = $room_res;
            $answer_array["totalSelected"] = $value;
            $array_for_json[$question_counter]["answers"][] = $answer_array;
            $answer_counter++;
            }
        }
        $question_counter++;
    }

    ?>
    <script>
      var qsmAnswerData = {
        question: <?php echo json_encode( $array_for_json ); ?>,
        system: <?php echo $quiz_system; ?>
      };
    </script>

    <?php
    if ( $results_total > 0 ) {
      $results_average_points = $results_average_points/$results_total;
      $results_average_score = $results_average_score/$results_total;
      $results_average_correct_answers = $results_average_correct_answers/$results_total;
      $results_average_time = $results_average_time/$results_total;
    } else {
      $results_average_points = 0;
      $results_average_score = 0;
      $results_average_correct_answers = 0;
      $results_average_time = 0;
    }

    ?>
    <h3>Results Overview</h3>
    <section class="qsm_results_overview_section">
      <div class="qsm_stats_container">
        <div class="qsm_stats_container_title">Requests</div>
        <div class="qsm_stats_container_content"><?php echo $request_total; ?></div>
      </div>
      <div class="qsm_stats_container">
        <div class="qsm_stats_container_title">Responses</div>
        <div class="qsm_stats_container_content"><?php echo $response_total; ?></div>
      </div>
      <div class="qsm_stats_container">
        <div class="qsm_stats_container_title">Devices</div>
        <div class="qsm_stats_container_content"><?php echo $device_total; ?></div>
      </div>
    </section>
    <h3>Results</h3>
    <section class="qsm_answer_data_section">

    </section>
    
    <section class="qsm_answer_data_section1">

    </section>
    <section class="qsm_answer_data_section2">

    </section>
    <?php

  //}
}

add_shortcode('a4b_dashboard','a4b_addon_analysis_stats_tabs_content');
?>
