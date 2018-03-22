<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Prepares and creates the csv file
 *
 * @since 1.3.0
 * @param array $args An array of which columns to include in csv
 * @param bool $path True to return path, false to return url
 * @return string Either path or url of created csv based on $path parameter
 */
function qsm_addon_analysis_results_create_csv( $args, $path = false ) {

  // Prepares defaults and parses $args
  $defaults = array(
    'quiz_id' => 1,
    'timestamp' => 1,
    'contact' => 1,
    'user_id' => 1,
    'quiz_name' => 1,
    'questions' => 1,
    'user_answers' => 1,
    'user_comments' => 1,
    'right_wrong' => 1,
    'question_points' => 1,
    'category' => 1,
    'total_correct' => 1,
    'total_questions' => 1,
    'comments' => 1,
    'score' => 1,
    'timer' => 1,
    'by_name' => false,
    'by_business' => false,
    'by_user' => false,
    'start_date' => false,
    'end_date' => false
  );
  $csv_data = wp_parse_args( $args, $defaults );

  // Prepares SQL for filtering
  $filter_sql = "";

  // If dates are not false, add filter for time taken
  if ( $csv_data["start_date"] && $csv_data["end_date"] ) {
    $start_date = date( "Y-m-d 00:00:00", strtotime( $csv_data["start_date"] ) );
    $end_date = date( "Y-m-d 23:59:59", strtotime( $csv_data["end_date"] ) );
    $filter_sql .= " AND (time_taken_real BETWEEN '$start_date' AND '$end_date')";
  }

  // If user is not false, add filter for user
  if ( $csv_data["by_user"] ) {
    $filter_sql .= " AND (user = {$csv_data["by_user"]})";
  }

  // If name is not false, add filter for name
  if ( $csv_data["by_name"] ) {
    $filter_sql .= " AND (name LIKE '%{$csv_data["by_name"]}%')";
  }

  // If business is not false, add filter for business
  if ( $csv_data["by_business"] ) {
    $filter_sql .= " AND (business LIKE '%{$csv_data["by_business"]}%')";
  }

  // Opens csv file for writing
  if ( ! class_exists( 'MLW_Export_CSV' ) ) {
    include( "class-mlw-export-csv.php" );
  }
  $time = time();
  $csv_file = new MLW_Export_CSV( "../exports/exported_results_$time.csv" );

  // Loads all results
  global $wpdb;
  $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE (quiz_id = {$csv_data["quiz_id"]}) AND (deleted = 0)$filter_sql ORDER BY time_taken_real ASC" );

  // Turn headers into array
  $headers = array();

  // Timestamp column
  if ( $csv_data["timestamp"] ) {
    $headers[] = 'Time Submitted';
  }

  // User ID column
  if ( $csv_data["user_id"] ) {
    $headers[] = 'User ID';
  }

  // Contact info columns
  if ( $csv_data["contact"] ) {
    $headers[] = 'Name';
    $headers[] = 'Phone Number';
    $headers[] = 'Business';
    $headers[] = 'Email';
  }

  // Quiz name column
  if ( $csv_data["quiz_name"] ) {
    $headers[] = 'Quiz/Survey';
  }

  // Total correct column
  if ( $csv_data["total_correct"] ) {
    $headers[] = 'Total Correct';
  }

  // Total questions column
  if ( $csv_data["total_questions"] ) {
    $headers[] = 'Total Questions';
  }

  // Score column
  if ( $csv_data["score"] ) {
    $headers[] = 'Score';
  }

  // Check last row of results to make sure that they are serialized which determines if these results were after 2.6 QSM update
  $last_row = end( $results );
  $last_row_results = maybe_unserialize( $last_row->quiz_results );
  if ( ! is_array( $last_row_results ) ) {
    $headers[] = 'Results';
  } else {
    // Loop over all the questions to create columns for each one
    $question_array_count = count( $last_row_results[1] );
    for ( $i = 1; $i <= $question_array_count; $i++ ) {

      // Question column
      if ( $csv_data["questions"] ) {
        $headers[] = "Question $i";
      }

      // User answer column
      if ( $csv_data["user_answers"] ) {
        $headers[] = "Question $i Answer Provided";
      }

      // Question comments column
      if ( $csv_data["user_comments"] ) {
        $headers[] = "Question $i Comment Provided";
      }

      // Right or wrong column
      if ( $csv_data["right_wrong"] ) {
        $headers[] = "Question $i Right or Wrong";
      }

      // Question points column
      if ( $csv_data["question_points"] ) {
        $headers[] = "Question $i Points Earned";
      }

      // Question category column
      if ( $csv_data["category"] ) {
        $headers[] = "Question $i Category";
      }
    }

    // Comments column
    if ( $csv_data["comments"] ) {
      $headers[] = 'Comments Provided';
    }

    // Timer column
    if ( $csv_data["timer"] ) {
      $headers[] = 'Timer';
    }
  }

  // Adds header row to csv
  $csv_file->add_row( apply_filters( 'qsm_addon_export_results_headers', $headers ) );

  // Cycle through each result adding data to column
  foreach( $results as $result ) {
    $new_row = array();

    if ( $csv_data["timestamp"] ) {
      $new_row[] = $result->time_taken_real;
    }

    if ( $csv_data["user_id"] ) {
      $new_row[] = $result->user;
    }

    if ( $csv_data["contact"] ) {
      $new_row[] = $result->name;
      $new_row[] = $result->phone;
      $new_row[] = $result->business;
      $new_row[] = $result->email;
    }

    if ( $csv_data["quiz_name"] ) {
      $new_row[] = $result->quiz_name;
    }

    if ( $csv_data["total_correct"] ) {
      $new_row[] = $result->correct;
    }

    if ( $csv_data["total_questions"] ) {
      $new_row[] = $result->total;
    }

    if ( $csv_data["score"] ) {

      if ( $result->quiz_system == 0 ) {
        $new_row[] = $result->correct_score;
      }
      else if ( $result->quiz_system == 1 ) {
        $new_row[] = $result->point_score;
      }
      else {
        $new_row[] = 'Not Graded';
      }
    }

    $quiz_answers = maybe_unserialize( $result->quiz_results );

    if ( ! is_array( $quiz_answers ) ) {
      $new_row[] = 'Results';
    } else {
      $question_array_count = count( $quiz_answers[1] ) - 1;

      for ( $i = 0; $i <= $question_array_count; $i++ ) {
        if ( $csv_data["questions"] ) {
          $new_row[] = strip_tags( htmlspecialchars_decode( $quiz_answers[1][$i][0], ENT_QUOTES ) );
        }
        if ( $csv_data["user_answers"] ) {
          $new_row[] = strip_tags( htmlspecialchars_decode( $quiz_answers[1][$i][1], ENT_QUOTES ) );
        }
        if ( $csv_data["user_comments"] ) {
          $new_row[] = $quiz_answers[1][$i][3];
        }
        if ( $csv_data["right_wrong"] ) {
          if ( isset( $quiz_answers[1][$i]["correct"] ) ) {
            $new_row[] = $quiz_answers[1][$i]["correct"];
          } else {
            if ( $quiz_answers[1][$i][1] == $quiz_answers[1][$i][2] ) {
              $new_row[] = "Correct";
            } else {
              $new_row[] = "Incorrect";
            }
          }
        }
        if ( $csv_data["question_points"] ) {
          $new_row[] = $quiz_answers[1][$i]["points"];
        }
        if ( $csv_data["category"] ) {
          $new_row[] = $quiz_answers[1][$i]["category"];
        }
      }
      if ( $csv_data["comments"] ) {
        $new_row[] = str_replace( "\n", " ", $quiz_answers[2] );
      }
      if ( $csv_data["timer"] ) {
        $timer_string = '';
        $timer_hours = floor( $quiz_answers[0] / 3600 );
        if ( $timer_hours > 0 ) {
          $timer_string .= "$timer_hours hours ";
        }
        $timer_minutes = floor( ( $quiz_answers[0] % 3600 ) / 60 );
        if ( $timer_minutes > 0 ) {
          $timer_string .= "$timer_minutes minutes ";
        }
        $timer_seconds = $quiz_answers[0] % 60;
        $timer_string .=  "$timer_seconds seconds";
        $new_row[] = $timer_string;
      }
    }

    // Add result row to csv
    $csv_file->add_row( apply_filters( 'qsm_addon_export_results_rows', $new_row ) );

  }

  // Finalize and close csv file
  $csv_file->finalize();

  // Return path or url based on $path parameter of function
  if ( $path ) {
    return $csv_file->get_path();
  } else {
    return $csv_file->get_url();
  }
}

?>
