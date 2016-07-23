<?php
	// Load the OAuth library
	require_once __DIR__ . '/oauth-client/src/facebook.class.php';
	
	// Delete the access token if needed
	if(isset($_GET["del_token"]) && isset($_SESSION["fb_token"])) unset($_SESSION["fb_token"]);
	
	// session.class.php is some session library
	require_once __DIR__ . '/session.class.php';
	$session = new Session();
	
	// Create a new Facebook object
	$facebook = new OAuthFacebook("0000000000000000", "0000000000000000000000000000000000000000", Array(
		"session_handler" => Array(
			"check" => Array($session, "check"),
			"get" => Array($session, "get"),
			"set" => Array($session, "set"),
			"delete" => Array($session, "delete")
		)
	));
	
	// Output a Login Button
	echo $facebook->loginButton("Login with Facebook", "https://example.com/code.php", Array("email", "user_friends"));
	
	// Try fetching the user's data - if an error is thrown, show a link to the login dialog
	if($facebook->accessToken() != null) {
		// The user is logged in, you can do whatever you like here
		// In this example we just print the profile data, along with the profile picture and permissions
		$profile = $facebook->userProfile();
		echo "<pre>" . print_r($profile, true) . "</pre><br /><br />\n\n";
		
		// Profile picture
		$profilepicture = $facebook->profilePicture();
		echo "<pre>" . print_r($profilepicture, true) . "</pre><br /><br />\n\n";
		
		// Permissions
		$permissions = $facebook->permissions();
		echo "<pre>" . print_r($permissions, true) . "</pre><br /><br />\n\n";
		
		// Friends
		if($facebook->permission("user_friends")) {
			$request = $facebook->api(OAuth2::GET, "/me/friends");
			$request->execute();
			$friends = $request->responseObject();
		} else $friends = "You have not granted access to Friends that also use this app.";
		echo "<pre>" . print_r($friends, true) . "</pre><br /><br />\n\n";
	} else {
		echo "You have not granted access to Facebook. Click the link above.\n";
	}
	
	echo "<pre>" . print_r($_SESSION, true) . "</pre><br /><br />\n\n";
	