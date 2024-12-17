<?php
/*
 * Copyright (C) 2013 by
 * Fuad Jamour <fjamour@gmail.com>
 * All rights reserved.
 * MIT license.
*/
 ob_start();
 include('header.php');
  if(!$enable_uploads) {
    echo $uploads_disabled_message;
    include('footer.php');
		exit;
  }
	$err_msgs = array('file'=>'', 'notes'=>'');
	$upload_str = "Your notes are kept private and are there to help you organize your submissions. Limit your notes to 256 characters.";
  
  //check if has pending submissions, to block!
	$block_submission  = (db_get_num_pending_submissions($_SESSION['team_id']) > 0 and $BLOCK_IF_PENDING);

  // check if the number of submissions today reaches maximum allowed number
  $time_today_min = strtotime('today');
  $time_today_max = strtotime('tomorrow');
  $num_submssion_today = db_get_num_today_submissions($_SESSION['team_id'], $time_today_min, $time_today_max);
  $over_submission = ($num_submssion_today >= $max_submission_num);
  $time_now = time();
  $wait_hours = intdiv($time_today_max - $time_now, 60*60);
  $wait_minutes = intdiv($time_today_max - $time_now, 60) - $wait_hours * 60;

if($over_submission) {?>
  <div class="alert alert-danger col-xs-12 text-center">
    <i class="glyphicon glyphicon-exclamation-sign"></i>
    You have reached the maximum number of submissions today. You can not upload a new submission!
    <br>
    Do you want to go to your <a href='dashboard.php'>dashboard</a>?
  </div>
<?php include('footer.php'); exit; 
}?>

<?php
if($block_submission) {
?>
  <div class="alert alert-danger col-xs-12 text-center">
    <i class="glyphicon glyphicon-exclamation-sign"></i>
    You have a pending submission, you can not upload a new submission!
		<br>
		Do you want to go to your <a href='dashboard.php'>dashboard</a>?
  </div>
<?php include('footer.php'); exit; }
	$upload_success = false;
	if(isset($_POST['Submit'])) {
		$pass_filename   = validate_lib_filename($_FILES['file'], $err_msgs['file']);
		$pass_notes      = validate_submission_notes($_POST['notes'], $err_msgs['notes']);
		if($pass_filename && $pass_notes) {
			if(!is_dir($SUBMISSIONS_PATH)) {
				die("Uploads are not properly set up!");
			}
			$submission_time = time();
			$storage_filename    = $submission_time . '_' . $_SESSION['team_id'] . '_' . rand(10000,30000) . rand(10000,30000) . '_' . $submission_file;
			$storage_path = $SUBMISSIONS_PATH . '/' . $storage_filename;
			
			if($_POST['notes'] != $upload_str) $notes = $_POST['notes']; else $notes = '--';
			if(db_insert_submission($_SESSION['team_id'], $submission_time, $notes, $storage_filename)) {
				if(!chmod($_FILES["file"]["tmp_name"] , 0664)) { die("Fatal error (a) during upload: Please contact the admin"); }
//				echo $_FILES["file"]["tmp_name"] ;
				if(!chgrp($_FILES["file"]["tmp_name"] , $SUBMISSIONS_USERS)) { die("Fatal error (b) during upload: Please contact the admin"); }
		  		if(!move_uploaded_file($_FILES["file"]["tmp_name"], $storage_path)) { die("Fatal error (c) during upload: Please contact the admin"); }
		  		if(!chmod($storage_path , 0664)) { die("Fatal error (d) during upload: Please contact the admin"); }
		  		$upload_success = true;
				//insert an entry in the submissions ip log
				db_insert_submission_ip($_SESSION['team_id'], $submission_time, get_ip_address());
				header('Location: dashboard.php');
				exit(0);
			}
		}
	}
if(!$upload_success) {?>
  <div class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">                    
    <div class="panel panel-default" >
      <div class="panel-heading">
        <div class="panel-title">Upload a Submission</div>
      </div>     
      <div class="panel-body">
        <form action="upload.php" enctype="multipart/form-data" method="post" class="form-horizontal" role="form">
          <?php if (empty($err_msgs['file'])) { ?>
            <div class="input-group">
          <?php } else { ?>
            <label class="control-label text-danger" for="file"><?php echo $err_msgs['file']; ?></label>
            <div class="input-group has-error">
          <?php } ?>
            <span class="input-group-addon" title="Library File"><i class="glyphicon glyphicon-none"></i><i class="glyphicon glyphicon-file"></i><i class="glyphicon glyphicon-none"></i></span>
            <input id="file" type="file" class="form-control" name="file">                                        
          </div>
          <?php if (empty($err_msgs['notes'])) { ?>
            <div class="input-group">
          <?php } else { ?>
            <label class="control-label text-danger" for="notes"><?php echo $err_msgs['notes']; ?></label>
            <div class="input-group has-error">
          <?php } ?>
            <span class="input-group-addon" title="Notes"><i class="glyphicon glyphicon-none"></i><i class="glyphicon glyphicon-edit"></i><i class="glyphicon glyphicon-none"></i></span>
            <textarea id="notes" class="form-control" name="notes" rows="7" maxlength="256" placeholder="<?php echo $upload_str; ?>"><?php echo isset($_POST['notes']) ? $_POST['notes'] : '' ?></textarea>
          </div>
          <div class="form-group">
            <div class="col-xs-12 controls">
              <div class="pull-right text-nowrap">
                <a href="dashboard.php" class="btn btn-danger">Cancel</a>
                <input type="submit" name="Submit" value="Upload File" class="btn btn-success">
              </div>
            </div>
          </div>
        </form>     
      </div>                     
    </div>  
  </div>
<?php } else {?>
  <div class="alert alert-success col-xs-12 text-center">
    <i class="glyphicon glyphicon-ok"></i>
    Your library was submitted successfully! Evaluating the submission might take a few minutes.
		<br>
		Go to your <a href="dashboard.php">dashboard</a> to check the result.
  </div>
<?php }
include('footer.php'); ?>
