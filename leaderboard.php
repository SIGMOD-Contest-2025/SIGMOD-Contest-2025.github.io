<?php include('dashboard/config.php');
	$leaderboard_entries = db_get_leaderboard_entries();
  $min_times = array('s'=>PHP_INT_MAX, 'm'=>PHP_INT_MAX, 'l'=>PHP_INT_MAX);
  $max_times = array('s'=>0, 'm'=>0, 'l'=>0);
	$rows = array();
  
	if(count($leaderboard_entries) != 0) {
		for($i = 0; $i < count($leaderboard_entries); $i++) {
		    if( strcmp($leaderboard_entries[$i]['team_name'],"GuessSongs")==0){
		        continue;
        }
      $flag = sprintf('images/flags/%s.gif', $leaderboard_entries[$i]['show_country'] ? strtolower($COUNTRY_TO_CODE[$leaderboard_entries[$i]['team_country']]) : '');
      // check if the flag gif exists
			if(!file_exists($flag)) $flag='';
      $institute = $leaderboard_entries[$i]['show_instit'] ? $leaderboard_entries[$i]['team_institute'] : '';
      $submission_time = str_replace(';', '<br>', date('M d, Y;H:i:s e', $leaderboard_entries[$i]['submission_time']));
      $runtime = round($leaderboard_entries[$i]["time_xxl"]);
      $rows[$i] = <<<ROW_CONTENT
{
  name:'{$leaderboard_entries[$i]['team_name']}',
  flag:'$flag',
  institute:'$institute',
  submission_time:'$submission_time',
  //recall:'{$leaderboard_entries[$i]['recall']}',
  //time_on_top:{$leaderboard_entries[$i]['time_on_top']},
  times:[
    {$leaderboard_entries[$i]['recall']}//,
    //{$leaderboard_entries[$i]['time_m']},
    //{$leaderboard_entries[$i]['time_l']},
    //{$leaderboard_entries[$i]['time_xl']},
  ],
  runtime: '$runtime',
}
ROW_CONTENT;
		}
	}
?>
<div class="table-responsive">
  <script src="js/date-time.js"></script>
  <script src="js/leaderboard.js?v=4"></script>
  <table class="table leaders-table">
    <thead>
      <tr>
        <th class="text-right">Rank</th>
        <th>Team</th>
        <!--<th class="text-center">Time in the Lead</th> -->
	      <th class="text-center time-column" colspan="2">Best Recall</th>
        <th class="text-center time-column">Runtime(s)</th>
        <!--
        <th class="text-center time-column">Medium<br>(seconds)</th>
        <th class="text-center time-column">Large<br>(seconds)</th>
        <th class="text-center time-column">X-Large<br>(seconds)</th>
        <th class="text-center time-column">XX-Large<br>(seconds)</th>
        -->
        <th class="text-center">Submitted</th>
      </tr>
    </thead>
    <script>drawLeaderboard([<?php echo implode(',', $rows) ?>]);</script>
  </table>
</div>
<?php db_disconnect(); ?>
