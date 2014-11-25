<?php
/*
 *Authorizations
 *implementing classes should extend RotaryAuth 
*/
abstract class RotaryAuth {
	function auth_check_login($user, $username, $password) {
	  //to be implemented by sub classes
	}
	function auth_errors() {
	  //to be implemented by sub classes
	}
	function show_password_fields() {
		return 0;
	}
	/**
	 * disable_function()
	 *
	 * The main error function to be used when a user tries to
	 * register or uses the forgotten password form
	 *
	 * @return void
	 */
	function disable_function() {
		$errors = new WP_Error();
		$errors->add(
			'registerdisabled',
			__('User registration is not available from this site, so you can\'t create an account or retrieve your password from here. See the message above.')
		);
		login_header(__('Log In'), '', $errors);
		?>
			<p id="backtoblog"><a href="<?php bloginfo('url'); ?>/" title="<?php _e('Are you lost?') ?>"><?php printf(__('&larr; Back to %s'), get_bloginfo('title', 'display' )); ?></a></p>
			<?php
		exit();
	}
	
}//end class
?>