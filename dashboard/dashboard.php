<?php
/*
 * Copyright (C) 2013 by
 * Fuad Jamour <fjamour@gmail.com>
 * All rights reserved.
 * MIT license.
*/
 include('header.php'); 

  function parseTime($time, &$failed) {
    if ($time < 0) {
      $failed = true;
      return 'failed';
    }
    return $time;
  }
  function getRecall($t1, $t2){
    if ($t1 < 0){
      return $t2;
    }
    return $t1;
  }
	$submissions_arr = db_get_user_submissions($_SESSION['team_id']);

  $status_pass = '<i class="glyphicon glyphicon-ok text-success"></i><br>pass';
  $status_fail = '<i class="glyphicon glyphicon-remove text-danger"></i><br>fail';
  $status_pending = '<i class="glyphicon glyphicon-hourglass"></i><br>pending';
  $rows = array();
  for($i = 0; $i < count($submissions_arr); $i++) {
    $rows[$i]['submission_time'] = str_replace(';', '<br>', date('M d, Y;H:i:s e', $submissions_arr[$i]['submission_time']));
    $rows[$i]['notes'] = $submissions_arr[$i]['notes'];
    $rows[$i]['filename'] = $submissions_arr[$i]['filename'];
    
    if($submissions_arr[$i]['result_code'] < 0) {// submission has not been evaluated yet
      $rows[$i]['time_s'] = 'N/A';
      /*
      $rows[$i]['time_m'] = 'N/A';
      $rows[$i]['time_l'] = 'N/A';
      $rows[$i]['time_xl'] = 'N/A';
      */
      $rows[$i]['time_xxl'] = 'N/A';
      $rows[$i]['status'] = $status_pending;
      $rows[$i]['style'] = 'info';
      
    } else {
      $failed = false;
      $rows[$i]['time_s'] = parseTime(getRecall($submissions_arr[$i]['runtime_small'], $submissions_arr[$i]['runtime_big']), $failed);
      /*
      $rows[$i]['time_m'] = parseTime($submissions_arr[$i]['runtime_big'], $failed);
      $rows[$i]['time_l'] = parseTime($submissions_arr[$i]['runtime_large'], $failed);
      $rows[$i]['time_xl'] = parseTime($submissions_arr[$i]['runtime_vlarge'], $failed);
      */
      $rows[$i]['time_xxl'] = parseTime($submissions_arr[$i]['runtime_vvlarge'], $failed);
      $rows[$i]['status'] = $failed ? $status_fail : $status_pass;
      $rows[$i]['style'] = $failed ? 'danger' : 'success';
    }
  }
?>

<a href="logout.php" class="btn btn-default btn-xs pull-right">
  <i class="glyphicon glyphicon-log-out"></i> Log Out
</a>
You are logged in as: 
<a href="update_account.php" class="btn btn-default btn-xs">
  <i class="glyphicon glyphicon-user"></i> <?php echo $_SESSION['team_name']; ?>
</a> <div>&nbsp;</div>

<center>
  <div class="well well-sm countdown">
    <i class="glyphicon glyphicon-time"></i>
    <span id="countdown"></span>
  </div>
</center>
<script src="../js/date-time.js"></script>
<script type="text/javascript">countdown(<?php echo $submission_deadline - time() ?>, 'countdown');</script>

<?php //check if has pending submissions, to block!
	$block_submission  = (db_get_num_pending_submissions($_SESSION['team_id']) > 0 and $BLOCK_IF_PENDING);
?>

<?php // check if the number of submissions today reaches maximum allowed number
  $time_today_min = strtotime('today');
  $time_today_max = strtotime('tomorrow');
  $num_submssion_today = db_get_num_today_submissions($_SESSION['team_id'], $time_today_min, $time_today_max);
  $over_submission = ($num_submssion_today >= $max_submission_num);
  $time_now = time();
  $wait_hours = intdiv($time_today_max - $time_now, 60*60);
  $wait_minutes = intdiv($time_today_max - $time_now, 60) - $wait_hours * 60;
?>


<?php if(!$enable_uploads) echo $uploads_disabled_message; ?>

<?php if($block_submission) { 
  ?>
    <div class="alert alert-info col-xs-12 text-center">
      <i class="glyphicon glyphicon-info-sign"></i>
      You have a pending submission.
      <br>
	<?php 
			$pos = db_get_pos_evaluation_queue($_SESSION['team_id']);
      $pos_queue = intdiv($pos, $EVAL_QUEUE_SIZE);

			if ($pos_queue == 0) {
				echo "The submission is in the first position of the evaluation queue.";
			} else {
				echo "The submission is in position ";
				echo $pos_queue + 1;
				echo " of the evaluation queue";
			}
		?>
  </div><div>&nbsp;</div>

<?php } ?>


<?php if($over_submission and !$block_submission) { 
?>
<div class="alert alert-info col-xs-12 text-center">
      <i class="glyphicon glyphicon-info-sign"></i>
      You have reached the maximum number of submissions today. Please wait for
      <?php 
        $minute_text = ($wait_minutes == 1) ? " minute " : " minutes ";
        $hour_text = ($wait_hours == 1) ? " hour " : " hours ";

        if($wait_hours == 0 and $wait_minutes == 0){
          echo "1 minute to try again.";
        }
        else{
          if($wait_hours == 0) {
            echo $wait_minutes . $minute_text . "to try again.";
          }
          else{
            echo $wait_hours . $hour_text . $wait_minutes . $minute_text . "to try again.";
          }
        }
      ?>
  </div><div>&nbsp;</div>
<?php } ?>


<div class="text-center">
  <strong>Submissions Today:
  <?php 
    echo $num_submssion_today
  ?>
  </span>
  &nbsp;&nbsp;
  Allowed Submissions Per Day:
  <?php 
    echo $max_submission_num
  ?>
  </strong>
  <br>
</div>


<div class="panel panel-default">
  <!-- Default panel contents -->
  <div class="panel-heading">
    <strong>Your Submissions:</strong>
    <?php if(!$block_submission && $enable_uploads && !$over_submission) { ?>
      <a href="upload.php" class="btn btn-default btn-xs pull-right">
        <i class="glyphicon glyphicon-plus"></i> New Submission
      </a>
    <?php } ?>
  </div>
  <?php if(count($rows) == 0) { ?>
    <div class="panel-body text-center">
		  You don't have any submissions yet.
    </div>
  <?php } else { ?>
    <div class="table-responsive">
      <table class="table table-hover submissions-table">
        <thead>
          <tr>
            <th>Submitted</th>
            <th class="text-center">Recall</th>
            <!--
            <th class="text-center">Recall D1</th>
            <th class="text-center">Recall D2</th>
            -->
            <th class="text-center">Runtime (s)</th>
            <th class="text-center">Status</th>
            
            <th class="text-center">Log</th>
  
            <th class="text-center">Notes</th>

          </tr>
        </thead>
        <tbody>
          <?php for($i = 0; $i < count($rows); $i++) { ?>
            <tr class="<?php echo $rows[$i]['style'] ?>">
              <td>
                <?php echo $rows[$i]['submission_time'] ?>
              </td>
              <td class="text-center">
                <?php echo $rows[$i]['time_s'] ?>
              </td>
              <!--
              <td class="text-center">
                <#?php echo $rows[$i]['time_m'] ?>
              </td>
              <td class="text-center">
                <#?php echo $rows[$i]['time_l'] ?>
              </td>
          -->
              <td class="text-center">
                <?php echo $rows[$i]['time_xxl'] ?>
              </td>
              <td class="text-center">
                <?php echo $rows[$i]['status'] ?>
              </td>
              
              <td class="text-center"><?php if($rows[$i]['status'] != $status_pending) { ?><a <?php
                echo sprintf('href="%s"', '../2023data/processed_results/' . str_replace(".rpz", "_stderr.log", $rows[$i]['filename']));
              ?> download>log</a><?php } ?></td>

              <td class="text-center"<?php if (!empty($rows[$i]['notes'])) echo 'class="notes"' ?>>
                <?php echo strip_tags($rows[$i]['notes']) ?>
              </td>

            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  <?php } ?>
</div>
	    
<?php include('footer.php'); ?>
