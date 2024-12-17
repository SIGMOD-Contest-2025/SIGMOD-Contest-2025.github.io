<?php
/*
 * Copyright (C) 2013 by
 * Fuad Jamour <fjamour@gmail.com>
 * All rights reserved.
 * MIT license.
*/
include('header.php');

	$user_row = db_get_user_row($_SESSION['team_name']);

  $pass_credentials = false;
  $pass_changing = (isset($_POST['password']) && !empty($_POST['password'])) ||
                   (isset($_POST['password2']) && !empty($_POST['password2'])) ||
                   (isset($_POST['password_c']) && !empty($_POST['password_c']));
	$err_msgs = array('email'=>'', 'institution'=>'', 'country'=>'', 'language'=>'', 'password'=>'', 'password_c'=>'');
	$account_updated = false;
  $hide_country_check = !$user_row['show_country'];
  $hide_instit_check = !$user_row['show_instit'];
	if(isset($_POST['Update'])) {// user submitted information, do all the checks on the inputs
		$pass_email          = validate_email($_POST['email'], $err_msgs['email']);
		$pass_instit         = validate_instit($_POST['institution'], $err_msgs['institution']);
		$pass_country        = validate_country($_POST['country'], $err_msgs['country']);
		$pass_language       = true;
		//$pass_language       = validate_language($_POST['language'], $err_msgs['language']);
		$pass_password       = validate_password($_POST['password'], $_POST['password2'], $err_msgs['password']);
		
		$pass_password_c     = validate_password($_POST['password_c'], $_POST['password_c'], $err_msgs['password_c']);
		if($pass_password_c)
			$pass_credentials = db_check_login($user_row['team_name'], $_POST['password_c']);
			
		if(!$pass_credentials)
			$err_msgs['password_c'] = 'Wrong password, please try again.';

    $hide_country_check = isset($_POST['hide_country']);
    $hide_instit_check = isset($_POST['hide_instit']);
		if($pass_email && $pass_instit && $pass_country && $pass_language &&
       (!$pass_changing || ($pass_password && $pass_password_c && $pass_credentials))) {
			db_update_team($user_row['team_name'],
                           $_POST['email'],
                           $_POST['institution'],
                           $_POST['country'],
			   'Other',
                           //$_POST['language'],
                           $pass_changing ? $_POST['password'] : '',
                           $hide_instit_check,
                           $hide_country_check);
			$user_row = db_get_user_row($_SESSION['team_name']);
			$account_updated = true;
		}
    if (!$pass_changing) {
      $err_msgs['password'] = '';
      $err_msgs['password_c'] = '';
    }
	}
?>
<?php if(!$account_updated) {?>
  <div class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">                    
    <div class="panel panel-default" >
      <div class="panel-heading">
        <div class="panel-title">Update Account Details</div>
      </div>     
      <div class="panel-body">
        <form name="update_account_form" method="post" action="update_account.php" class="form-horizontal" role="form">
          <div class="input-group">
            <span class="input-group-addon" title="Team Name"><i class="glyphicon glyphicon-user"></i><i class="glyphicon glyphicon-user"></i><i class="glyphicon glyphicon-user"></i></span>
            <input readonly type="text" class="form-control" value="<?php echo $user_row['team_name'] ?>">                                        
          </div>

          <?php if (empty($err_msgs['email'])) { ?>
            <div class="input-group">
          <?php } else { ?>
            <label class="control-label text-danger" for="email"><?php echo $err_msgs['email']; ?></label>
            <div class="input-group has-error">
          <?php } ?>
            <span class="input-group-addon" title="Email"><i class="glyphicon glyphicon-none"></i><i class="glyphicon glyphicon-envelope"></i><i class="glyphicon glyphicon-none"></i></span>
            <input id="email" required type="text" class="form-control" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : $user_row['team_email'] ?>" placeholder="Email">                                        
          </div>

          <?php if (empty($err_msgs['institution'])) { ?>
            <div class="input-group">
          <?php } else { ?>
            <label class="control-label text-danger" for="institution"><?php echo $err_msgs['institution']; ?></label>
            <div class="input-group has-error">
          <?php } ?>
            <span class="input-group-addon" title="Institution"><i class="glyphicon glyphicon-none"></i><i class="glyphicon glyphicon-education"></i><i class="glyphicon glyphicon-none"></i></span>
            <input id="institution" required type="text" class="form-control top-right" name="institution" value="<?php echo isset($_POST['institution']) ? $_POST['institution'] : $user_row['team_institute'] ?>" placeholder="Institution">                                        
            <div class="checkbox form-control bottom-right">
              <label>
                <input type="checkbox" name="hide_instit" <?php echo $hide_instit_check ? 'checked' : '' ?>> Hide institution name on the leaderboard.
              </label>
            </div>
          </div>
          
          <?php if (empty($err_msgs['country'])) { ?>
            <div class="input-group">
          <?php } else { ?>
            <label class="control-label text-danger" for="country"><?php echo $err_msgs['country']; ?></label>
            <div class="input-group has-error">
          <?php } ?>
            <span class="input-group-addon" title="Country"><i class="glyphicon glyphicon-none"></i><i class="glyphicon glyphicon-globe"></i><i class="glyphicon glyphicon-none"></i></span>
            <select name="country" required type="text "class="form-control top-right" id="country">
              <?php echo get_countries(isset($_POST['country']) ? $_POST['country'] : $user_row['team_country']); ?>
            </select>
            <div class="checkbox form-control bottom-right">
              <label>
                <input type="checkbox" name="hide_country" <?php echo $hide_country_check ? 'checked' : '' ?>> Hide country flag on the leaderboard.
              </label>
            </div>
          </div>
          
          <!--<?php if (empty($err_msgs['language'])) { ?>
            <div class="input-group">
          <?php } else { ?>
            <label class="control-label text-danger" for="language"><?php echo $err_msgs['language']; ?></label>
            <div class="input-group has-error">
          <?php } ?>
            <span class="input-group-addon" title="Programming Language"><i class="glyphicon glyphicon-none"></i><i class="glyphicon glyphicon-pencil"></i><i class="glyphicon glyphicon-none"></i></span>
            <select name="language" required type="text "class="form-control" id="language">
              <?php echo get_programming_languages($user_row['team_language']); ?>
            </select>
          </div>-->
          
          <?php if (empty($err_msgs['password']) && empty($err_msgs['password_c'])) { ?>
            <div class="input-group">
          <?php } else { ?>
            <?php if (!empty($err_msgs['password_c'])) { ?>
              <label class="control-label text-danger" for="password_c"><?php echo $err_msgs['password_c']; ?></label>
            <?php } else if (!empty($err_msgs['password'])) { ?>
              <label class="control-label text-danger" for="password"><?php echo $err_msgs['password']; ?></label>
            <?php } ?>
            <div class="input-group has-error">
          <?php } ?>
            <span class="input-group-addon" title="Password"><i class="glyphicon glyphicon-none"></i><i class="glyphicon glyphicon-lock"></i><i class="glyphicon glyphicon-none"></i></span>
            <input id="password_c" type="password" class="form-control top-right" name="password_c" value="" placeholder="Current Password">                                        
            <input id="password" type="password" class="form-control middle-right" name="password" value="" placeholder="New Password">                                        
            <input id="password2" type="password" class="form-control bottom-right" name="password2" value="" placeholder="Repeat New Password">                                        
          </div>

          <div class="form-group">
            <div class="col-xs-12 controls">
              <div class="pull-right text-nowrap">
                <a href="dashboard.php" class="btn btn-danger">Discard Changes</a>
                <input type="submit" name="Update" value="Update Account" class="btn btn-success">
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
    Your account details have been updated successfully!
		<br>
		Do you want to go to your <a href='dashboard.php'>dashboard</a>?
  </div>
<?php } ?>

<?php include('footer.php'); ?>
