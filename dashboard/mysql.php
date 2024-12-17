<?php
/*
 * Copyright (C) 2013 by
 * Fuad Jamour <fjamour@gmail.com>
 * All rights reserved.
 * MIT license.
*/
include('mysql.config.php'); 
    //db related functions and globals
	$db_header_included = true;
	$db_connected = false;
	$db_con;

	function db_connect()
	{
		global $db_con, $db_connected, $db_salt, $db_host, $db_username, $db_password, $db_name;
		if($db_connected) {
			return $db_con;
		} else {
			//TODO implement an exception handler to show a page if db connection goes wrong
			$db_con = mysqli_connect("$db_host", "$db_username", "$db_password") or die("Can't connect to DB!");
			mysqli_select_db($db_con,"$db_name") or die("Can't select DB");
			$db_connected = true;
			return $db_con;		
		}
	}

	function db_disconnect()
	{
		global $db_con, $db_connected;
		if($db_connected) {
			$db_connected = false;
			mysqli_close($db_con);
		}
	}

	//IMP: table names and attribute names hard coded here
	function db_check_team($team_name, $email, &$name_exists, &$email_exists)
	{
		$name_exists  = false;
		$email_exists = false;
        $db_con = db_connect();
		$sql = sprintf("SELECT * FROM users WHERE team_name='%s';",
                       mysqli_real_escape_string( $db_con,$team_name));
		$sql_res = mysqli_query( $db_con,$sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_check_team)");
		}
		$num_rows = mysqli_num_rows($sql_res);
		if($num_rows > 0)
			$name_exists = true;

		$sql = sprintf("SELECT * FROM users WHERE team_email='%s';",
                       mysqli_real_escape_string( $db_con,$email));
		$sql_res = mysqli_query( $db_con,$sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_check_team)");
		}
		$num_rows = mysqli_num_rows($sql_res);
		if($num_rows > 0)
			$email_exists = true;
	}

	//IMP: table names and attribute names hard coded here
	function db_register_team($team_name,
                              $team_email,
                              $team_instit,
                              $team_country,
                              $team_language,
                              $team_pass,
                              $hide_instit,
                              $hide_country)
	{
		global $db_salt;
		$db_con = db_connect();
		$password_hash = hash('sha256',$team_pass.$db_salt);
		$sql = sprintf("INSERT INTO users (team_name, team_email, team_institute, team_country, team_language, pass_hash, show_instit, show_country) VALUES('%s', '%s', '%s', '%s', '%s', '%s', %s, %s);",
                       mysqli_real_escape_string($db_con,$team_name),
                       mysqli_real_escape_string($db_con,$team_email),
                       mysqli_real_escape_string($db_con,$team_instit),
                       mysqli_real_escape_string($db_con,$team_country),
                       mysqli_real_escape_string($db_con,$team_language),
                       mysqli_real_escape_string($db_con,$password_hash),
                       ($hide_instit ? 'FALSE' : 'TRUE'),
                       ($hide_country ? 'FALSE' : 'TRUE'));

		if(!mysqli_query($db_con,$sql)) {
			die("Can't register team! (db_register_team)");
		}
	}

	//IMP: table names and attribute names hard coded here
	function db_update_team($team_name,
                            $team_email,
                            $team_instit,
                            $team_country,
                            $team_language,
                            $team_pass,
                            $hide_instit,
                            $hide_country)
	{
		global $db_salt;
        $db_con = db_connect();
		//1. update users_log
		//2. update users
		$sql = sprintf("INSERT INTO users_log (SELECT *, UNIX_TIMESTAMP() as change_time FROM users WHERE team_name='%s');",
                       mysqli_real_escape_string( $db_con,$team_name));
                       
		if(!mysqli_query( $db_con,$sql)) {
			die("Can't update team! (db_update_team_a)");
		}
    
		$sql = sprintf("UPDATE users SET team_email='%s', team_institute='%s', team_country='%s', team_language='%s'%s, show_instit=%s, show_country=%s WHERE team_name='%s';",
                       mysqli_real_escape_string( $db_con,$team_email),
                       mysqli_real_escape_string( $db_con,$team_instit),
                       mysqli_real_escape_string( $db_con,$team_country),
                       mysqli_real_escape_string( $db_con,$team_language),
                       empty($team_pass) ? '' : sprintf(", pass_hash='%s'", mysqli_real_escape_string( $db_con,hash('sha256',$team_pass.$db_salt))),
                       ($hide_instit ? 'FALSE' : 'TRUE'),
                       ($hide_country ? 'FALSE' : 'TRUE'),
                       mysqli_real_escape_string( $db_con,$team_name));
		if(!mysqli_query( $db_con,$sql)) {
			die("Can't update team! (db_update_team_b)");
		}                       
	}

	//IMP: table names and attribute names hard coded here
	function db_check_login($team_name, $team_pass)
	{
		global $db_salt;
		$succeed = false;
        $db_con = db_connect();
		$password_hash = hash('sha256',$team_pass.$db_salt);
		$sql = sprintf("SELECT * FROM users WHERE team_name='%s' and pass_hash='%s';",
                       mysqli_real_escape_string( $db_con,$team_name),
                       mysqli_real_escape_string( $db_con,$password_hash));
		$sql_res = mysqli_query( $db_con,$sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_check_login)");
		}
		$num_rows = mysqli_num_rows($sql_res);
		if($num_rows > 0)
			$succeed = true;
		else
			$succeed = false;

		return $succeed;
	}

	//IMP: table names and attribute names hard coded here
	function db_get_user_row($team_name)
	{
        $db_con = db_connect();
		$sql = sprintf("SELECT * FROM users WHERE team_name='%s';",
                       mysqli_real_escape_string( $db_con,$team_name));
		$sql_res = mysqli_query( $db_con,$sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_get_row)");
		}
		$num_rows = mysqli_num_rows($sql_res);
		if($num_rows != 1) {
			die("Fatal error in db_get_row");
		}
		return mysqli_fetch_assoc($sql_res);
	}

	//IMP: table names and attribute names hard coded here
	//NOTE: submission_time in submissions table is unique,
	//      this function will return True if the record was inserted,
	//      and False if not
	function db_insert_submission($team_id,
                                  $submission_time,
                                  $notes,
                                  $filename)
	{
		$db_con = db_connect();
		$sql = sprintf("INSERT INTO submissions (team_id, submission_time, notes, filename) VALUES(%s, %s, '%s', '%s');",
                       mysqli_real_escape_string($db_con, $team_id),
                       mysqli_real_escape_string($db_con, $submission_time),
                       mysqli_real_escape_string($db_con, $notes),
                       mysqli_real_escape_string($db_con, $filename));
		$sql_res = mysqli_query($db_con,$sql);
		if(!$sql_res) {
			return False;
		}
		
		return True;
		//if(!$sql_res) {
		//	die("Can't execute db query! (db_insert_submission)");
		//}		
	}

	//IMP: table names and attribute names hard coded here
	function db_get_user_submissions($team_id)
	{
		$db_con = db_connect();
		$sql = sprintf("SELECT * FROM submissions WHERE team_id=%s and show_dashboard=TRUE ORDER BY submission_time DESC;",
                       mysqli_real_escape_string($db_con, $team_id));
		$sql_res = mysqli_query($db_con,$sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_get_user_submissions)");
		}
		$num_rows = mysqli_num_rows($sql_res);
		$submissions_arr = array();
		for($i = 0; $i < $num_rows; $i++) {
			$submissions_arr[$i] = mysqli_fetch_assoc($sql_res);
		}
		
		return $submissions_arr;
	}

	//IMP: table names and attribute names hard coded here
	function db_get_leaderboard_entries()
	{
        $db_con = db_connect();
		$sql_res = mysqli_query($db_con,'CALL GetLeaderboard()');
		if(!$sql_res) {
			die("Can't execute db query! (db_get_leaderboard_entries)");
		}
		$num_rows = mysqli_num_rows($sql_res);
		$submissions_arr = array();
		for($i = 0; $i < $num_rows; $i++) {
			$submissions_arr[$i] = mysqli_fetch_assoc($sql_res);
		}
		return $submissions_arr;		
	}
	
	// Doesn't really remove the submission from the database, but changes
	// the show_leaderboard and show_dashboard flags
	function db_del_submission($team_id, $timestamp)
	{
		$db_con = db_connect();
		$sql = sprintf("UPDATE submissions SET show_dashboard=FALSE, show_leaderboard=FALSE WHERE team_id=%s and submission_time=%s;",
                       mysqli_real_escape_string($db_con, $team_id),
                       mysqli_real_escape_string($db_con, $timestamp));
		$sql_res = mysqli_query($db_con,$sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_del_submission)");
		}				
	}
	
	//IMP: table names and attribute names hard coded here
	function db_get_num_pending_submissions($team_id)
	{
		$db_con = db_connect();
		$sql = sprintf("SELECT * FROM submissions WHERE team_id='%s' and result_code=-1;",
                       mysqli_real_escape_string($db_con, $team_id));
		$sql_res = mysqli_query($db_con,$sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_get_num_pending_submissions)");
		}
		$num_rows = mysqli_num_rows($sql_res);
		return $num_rows;
	}

	function db_get_num_today_submissions($team_id, $time_today_min, $time_today_max)
	{
		$db_con = db_connect();
		$sql = sprintf("SELECT * FROM submissions WHERE team_id=%s and submission_time>=%d and submission_time<=%d",
                       mysqli_real_escape_string($db_con, $team_id), $time_today_min, $time_today_max);
		$sql_res = mysqli_query($db_con,$sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_get_num_pending_submissions)");
		}
		$num_rows = mysqli_num_rows($sql_res);
		return $num_rows;
	}

   function db_get_pos_evaluation_queue($team_id)
   {
      $db_con = db_connect();
      $sql = sprintf("select count(*) as POS_T from submissions o, (select min(submission_time) as own_time from submissions where team_id='%s' and result_code=-1) t where o.result_code=-1.0 and o.submission_time < t.own_time",
                       mysqli_real_escape_string($db_con, $team_id));
      $sql_res = mysqli_query($db_con,$sql);
      if(!$sql_res) {
         die("Can't execute db query! (db_get_num_pending_submissions)");
      }

      $row = mysqli_fetch_assoc($sql_res);
      $pos   = intval($row['POS_T']);
      return $pos;
   }
	
	//IMP: table names and attribute names hard coded here
	function db_insert_submission_ip($team_id, $timestamp, $ip_addr)
	{
		$db_con = db_connect();
		$sql = sprintf("INSERT INTO ip_log VALUES(%s, %s, '%s');", $team_id, $timestamp, $ip_addr);
		$sql_res = mysqli_query($db_con,$sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_insert_submission_p)");
		}
	}

	//delete a submission from database
	function db_del_team_submissions($team_id) {
		$db_con = db_connect();
		$sql = sprintf("DELETE FROM submissions WHERE team_id='%s'", mysqli_real_escape_string($db_con, $team_id));

		$sql_res = mysqli_query($db_con, $sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_del_team_submissions)");
		}
	}
	
	//mark all submissions as evaluated
	function db_pass_team_submissions($team_id) {
		$db_con = db_connect();
		$sql = sprintf("UPDATE submissions SET result_code=1, runtime_small=0.5, runtime_big=0.5, runtime_large=0.5, runtime_vlarge=0.5,runtime_vvlarge=100 WHERE team_id='%s'", mysqli_real_escape_string($db_con, $team_id));

		$sql_res = mysqli_query($db_con, $sql);
		if(!$sql_res) {
			die("Can't execute db query! (db_pass_team_submissions)");
		}
	}
?>
