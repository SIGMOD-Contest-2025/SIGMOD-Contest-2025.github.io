<?php
/*
 * Copyright (C) 2013 by
 * Fuad Jamour <fjamour@gmail.com>
 * All rights reserved.
 * MIT license.
*/
  ob_start();
  include('header.php');
  //Don't allow to log in again!
  if(is_logged_in()) {
    header('Location: dashboard.php');
    exit(0);
  }
  $err_msgs = array('login'=>'');
  $login_success = false;
  if(isset($_POST['Submit'])) {
    $dum_var;
    $pass_credentials = false;

    $pass_name     = validate_name($_POST['name'], $dum_var);
    $pass_password = validate_password($_POST['password'], $_POST['password'], $dum_var);
    if($pass_name && $pass_password)
      $pass_credentials = db_check_login($_POST['name'], $_POST['password']);
if
    ($pass_credentials) {
      //setcookie("name", $name, time()+(84600*30));
      $_SESSION['auth'] = 1;
      $user_row = db_get_user_row($_POST['name']);
      $_SESSION['team_name'] = $user_row['team_name'];
      $_SESSION['team_id']   = $user_row['team_id'];
      $login_success = true;
    } else {
      $err_msgs['login'] = "Wrong username or password! Try again.";
    }
  }
if($login_success) {
    header('Location: dashboard.php');
    exit(0);
} else { ?>
  <div class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
    <div class="panel panel-default" >
      <div class="panel-heading">
        <div class="panel-title">Sign In</div>
      </div>
      <div class="panel-body">
        <?php if (!empty($err_msgs['login'])) { ?>
          <div class="alert alert-danger col-xs-12 text-center">
            <i class="glyphicon glyphicon-exclamation-sign"></i>
            <?php echo $err_msgs['login']; ?>
          </div>
        <?php } ?>
        <form name="login_form" method="post" action="login.php" class="form-horizontal" role="form">
          <div class="input-group">
            <span class="input-group-addon" title="Team Name"><i class="glyphicon glyphicon-user"></i><i class="glyphicon glyphicon-user"></i><i class="glyphicon glyphicon-user"></i></span>
            <input id="name" type="text" class="form-control" name="name" value="" placeholder="Team Name">
          </div>
          <div class="input-group">
            <span class="input-group-addon" title="Password"><i class="glyphicon glyphicon-none"></i><i class="glyphicon glyphicon-lock"></i><i class="glyphicon glyphicon-none"></i></span>
            <input id="password" type="password" class="form-control" name="password" placeholder="Password">
          </div>
          <div class="form-group">
            <div class="col-xs-12 controls">
              <input type="submit" name="Submit" value="Login" class="btn btn-success pull-right">
            </div>
          </div>
          <hr>
          <div class="col-xs-12 control">
            <div>
              Don't have an account?
              <a href="create_account.php">Sign Up Here</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php }
 include('footer.php'); ?>
