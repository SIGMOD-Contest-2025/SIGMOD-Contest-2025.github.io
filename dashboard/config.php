<?php session_start();
   date_default_timezone_set('America/New_York');
   $page_title = 'ACM SIGMOD 2023 Programming Contest Dashboard';
   $submission_file = 'submission.rpz';
   $max_file_size_kb = 307200;
   $enable_uploads = TRUE;
   $submission_deadline = mktime(23, 59, 59, 4 /* April */, 15, 2023);
   $max_submission_num = 10;

   $SUBMISSIONS_PATH = '/data/site/submissions/';
   $SUBMISSIONS_USERS = 'www-data';
   $BLOCK_IF_PENDING = TRUE;
   $INFINITY = 1000000;
   $EVAL_QUEUE_SIZE = 10;

   $uploads_disabled_message =
     '<div class="alert alert-danger col-xs-12 text-center">'.
       '<i class="glyphicon glyphicon-exclamation-sign"></i>'.
       'Submission uploads are temporarily disabled.'.
       '<br>'.
       'Sorry for the inconvenience.'.
     '</div><div>&nbsp;</div>';
   
   if (time() > $submission_deadline) {
     $enable_uploads = FALSE;
     $uploads_disabled_message =
       '<div class="alert alert-info col-xs-12 text-center">'.
         '<i class="glyphicon glyphicon-info-sign"></i>'.
         'The submissions deadline has now passed.'.
         '<br>'.
         'No more submissions will be accepted from this point forward.'.
       '</div><div>&nbsp;</div>';
   }
   
   $programming_languages = array('C', 'C++', 'Go', 'Java', 'Rust', 'Other');

   function get_programming_languages($select="")
   {
      global $programming_languages;
      $programming_language = "";
      if($select == "")
         $programming_language = '<option value disabled selected hidden>Programming Language</option>';
      else
         $programming_language = '<option value disabled hidden>Programming Language</option>';
      
      
      foreach($programming_languages as $language) {
         if($language == $select)
            $programming_language .= ('<option selected>' . $language . '</option>');
         else
            $programming_language .= ('<option>' . $language . '</option>');
      }
      return $programming_language;
   }
   $current_file = basename($_SERVER['PHP_SELF'], ".php");

   function is_logged_in()
   {
      return !(!array_key_exists('auth', $_SESSION) || $_SESSION['auth'] != 1);
   }

   if(!is_logged_in() && ($current_file != 'login' && $current_file != 'create_account' && $current_file != 'leaderboard')) {
      header('Location: login.php');
      exit(0);
   }
include('util.php');
include('mysql.php');
include('countries.php'); ?>
