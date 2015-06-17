<?php
	// Start a session and load the OAuth library.
	session_start();
	require_once __DIR__ . '/oauth-client/src/yahoo.class.php';
	
	// Delete the access token if needed.
	if(isset($_GET["del_token"]) && isset($_SESSION["y_token"])) unset($_SESSION["y_token"]);
	
	// Create a new Yahoo object.
	$yahoo = new OAuthYahoo("0000000000000000", "0000000000000000000000000000000000000000", Array(
		"session_prefix" => "y_" // Prefix for session data. Defaults to yahoo_
	));
	
	// Output a Login Button.
	echo $yahoo->loginButton("Login with Yahoo!", "https://example.com/yahoo-1/code.php");
	
	// If an access token exists (OAuth2::accessToken() does not return null), fetch the current user's data.
	if($yahoo->accessToken() != null) {
		// The user is logged in, you can do whatever you like here.
		// In this example we just print the profile data, along with the profile picture and permissions.
		$profile = $yahoo->userProfile();
		echo "<pre>" . print_r($profile, true) . "</pre><br /><br />\n\n";
	} else {
		echo "You have not granted access to Yahoo. Click the link above.\n";
	}
	