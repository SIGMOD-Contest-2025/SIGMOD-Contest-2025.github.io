<?php
/*
 * Copyright (C) 2013 by
 * Fuad Jamour <fjamour@gmail.com>
 * All rights reserved.
 * MIT license.
*/
    include('header.php');
    // require_once "lib/recaptchalib.php";; 

	//If already logged in don't allow to register
	if(is_logged_in()) {
		header('Location: dashboard.php');
		exit(0);
	}

	function check_account_exists($name, $email, &$err_msg_name, &$err_msg_email)
	{
		$name_exists = true;
		$email_exists = true;
		db_check_team($name, $email, $name_exists, $email_exists);
		if($name_exists)
			$err_msg_name = 'Team name already exists, please choose another name!';
		if($email_exists)
			$err_msg_email = 'Another team registered with this email, please enter another one!';

		if($name_exists || $email_exists)
			return false;
		else
			return true;
	}

	//TODO May be should do something if somebody tried to access this page while logged in?
	$err_msgs = array('captcha'=>'', 'team_name'=>'', 'email'=>'', 'institution'=>'', 'country'=>'', 'language'=>'', 'password'=>'');
	$account_created = false;
	if(isset($_POST['Submit'])) {// user submitted information, do all the checks on the inputs
//		if(!isset($_POST['g-recaptcha-response'])) {
//			$err_msgs['team_name'] = 'Error: did not receive captcha response, do you have javascript enabled?';
//			return;
//		} else {
			// Check captcha
//			$secret = "6LcglUMUAAAAALkJbiskgtJS4HJw45lufbyvfS1w";
//			$reCaptcha = new ReCaptcha($secret);
//			$response = $reCaptcha->verifyResponse(
//		      $_SERVER["REMOTE_ADDR"],
//		      $_POST["g-recaptcha-response"]
//		   );

//		   $pass_captcha = true;
//		   if ($response == null || !$response->success) {
//		   	$pass_captcha = false;
//		   	$err_msgs['captcha'] = "Captcha could not be verified";
//		   }

			$pass_name           = validate_name($_POST['name'], $err_msgs['team_name']);
			$pass_email          = validate_email($_POST['email'], $err_msgs['email']);
			$pass_instit         = validate_instit($_POST['institution'], $err_msgs['institution']);
			$pass_country        = validate_country($_POST['country'], $err_msgs['country']);
			$pass_language       = true;
			// $pass_language       = validate_language($_POST['language'], $err_msgs['language']);
			$pass_password       = validate_password($_POST['password'], $_POST['password2'], $err_msgs['password']);

			if($pass_name && $pass_email)
				$pass_account_exists = check_account_exists($_POST['name'], $_POST['email'], $err_msgs['team_name'], $err_msgs['email']);
			else
				$pass_account_exists = false;

			// if($pass_captcha && $pass_name && $pass_email && $pass_instit && $pass_country && $pass_language && $pass_password && $pass_account_exists) {
			if($pass_name && $pass_email && $pass_instit && $pass_country && $pass_language && $pass_password && $pass_account_exists) {
				db_register_team($_POST['name'],
	                             $_POST['email'],
	                             $_POST['institution'],
	                             $_POST['country'],
				     'Other',
	                             // $_POST['language'],
	                             $_POST['password'],
	                             isset($_POST['hide_instit']),
	                             isset($_POST['hide_country']));
				$account_created = true;
				// TODO may be account confirmation by email or something? LATER
			}
//		}
	}

if(!$account_created) {?>
  <div class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">                    
    <div class="panel panel-default" >
      <div class="panel-heading">
        <div class="panel-title">Create an Account</div>
      </div>     
      <div class="panel-body" style="color:red">
        Note: Please carefully read the competition rules at the bottom of this page before you create an account.
      </div>
      <div class="panel-body">
        <form name="create_account_form" method="post" action="create_account.php" class="form-horizontal" role="form">
          <?php if (empty($err_msgs['team_name'])) { ?>
            <div class="input-group">
          <?php } else { ?>
            <label class="control-label text-danger" for="name"><?php echo $err_msgs['team_name']; ?></label>
            <div class="input-group has-error">
          <?php } ?>
            <span class="input-group-addon" title="Team Name"><i class="glyphicon glyphicon-user"></i><i class="glyphicon glyphicon-user"></i><i class="glyphicon glyphicon-user"></i></span>
            <input id="name" required type="text" class="form-control" name="name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : '' ?>" placeholder="Team Name">                                        
          </div>

          <?php if (empty($err_msgs['email'])) { ?>
            <div class="input-group">
          <?php } else { ?>
            <label class="control-label text-danger" for="email"><?php echo $err_msgs['email']; ?></label>
            <div class="input-group has-error">
          <?php } ?>
            <span class="input-group-addon" title="Email"><i class="glyphicon glyphicon-none"></i><i class="glyphicon glyphicon-envelope"></i><i class="glyphicon glyphicon-none"></i></span>
            <input id="email" required type="text" class="form-control" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : '' ?>" placeholder="Email">                                        
          </div>

          <?php if (empty($err_msgs['institution'])) { ?>
            <div class="input-group">
          <?php } else { ?>
            <label class="control-label text-danger" for="institution"><?php echo $err_msgs['institution']; ?></label>
            <div class="input-group has-error">
          <?php } ?>
            <span class="input-group-addon" title="Institution"><i class="glyphicon glyphicon-none"></i><i class="glyphicon glyphicon-education"></i><i class="glyphicon glyphicon-none"></i></span>
            <input id="institution" required type="text" class="form-control top-right" name="institution" value="<?php echo isset($_POST['institution']) ? $_POST['institution'] : '' ?>" placeholder="Institution">                                        
            <div class="checkbox form-control bottom-right">
              <label>
                <input type="checkbox" name="hide_instit" <?php echo isset($_POST['hide_instit']) ? 'checked' : '' ?>> Hide institution name on the leaderboard.
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
              <?php echo get_countries(isset($_POST['country']) ? $_POST['country'] : ''); ?>
            </select>
            <div class="checkbox form-control bottom-right">
              <label>
                <input type="checkbox" name="hide_country" <?php echo isset($_POST['hide_country']) ? 'checked' : '' ?>> Hide country flag on the leaderboard.
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
              <?php echo get_programming_languages(isset($_POST['language']) ? $_POST['language'] : ''); ?>
            </select>
          </div>-->
          
          <?php if (empty($err_msgs['password'])) { ?>
            <div class="input-group">
          <?php } else { ?>
            <label class="control-label text-danger" for="password"><?php echo $err_msgs['password']; ?></label>
            <div class="input-group has-error">
          <?php } ?>
            <span class="input-group-addon" title="Password"><i class="glyphicon glyphicon-none"></i><i class="glyphicon glyphicon-lock"></i><i class="glyphicon glyphicon-none"></i></span>
            <input id="password" required type="password" class="form-control top-right" name="password" value="" placeholder="Password">                                        
            <input id="password2" required type="password" class="form-control bottom-right" name="password2" value="" placeholder="Repeat Password">                                        
          </div>
          
          <!--<?php if (!empty($err_msgs['captcha'])) { ?>
            <label class="control-label text-danger"><?php echo $err_msgs['captcha']; ?></label>
          <?php } ?>
          <div class="form-group">
            <div class="g-recaptcha" data-sitekey="6LcglUMUAAAAAIn2xwuLFvO4nO3TzVzgifNapcB4"></div>
          </div>-->

          <div class="form-group">
            <div class="col-xs-12 controls">
              <input type="submit" name="Submit" value="Create Account" class="btn btn-success pull-right">
            </div>
          </div>
          <hr>
          <div class="col-xs-12 control">
            <div>
              Already have an account? 
              <a href="login.php">Sign In Here</a>
            </div>
          </div>
        </form>     
      </div>                     
    </div>  
  </div>
  <!--js-->
  <script src='https://www.google.com/recaptcha/api.js'></script>
<?php } else {?>
  <div class="alert alert-success col-xs-12 text-center">
    <i class="glyphicon glyphicon-ok"></i>
    Account created successfully <strong><?php echo $_POST['name']; ?></strong>!
		You can <a href="login.php">log in</a> now.
  </div>
<?php } ?>

<?php include('footer.php'); ?>
