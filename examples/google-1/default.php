<?php
	// Start a session and load the OAuth library.
	session_start();
	require_once 'src/google.class.php';
	
	// Delete the access token if needed.
	if(isset($_GET["del_token"]) && isset($_SESSION["g_token"])) unset($_SESSION["g_token"]);
	
	// Create a new Google object.
	$google = new OAuthGoogle("0000000000000000", "0000000000000000000000000000000000000000", Array(
		"session_prefix" => "g_" // Prefix for session data. Defaults to google_
	));
	
	// Output a Login Button.
	echo $google->loginButton("Login with Google+", "https://example.com/google-1/code.php", Array("https://www.googleapis.com/auth/plus.login"));
	
	// If an access token exists (OAuth::accessToken() does not return null), fetch the current user's data.
	if($google->accessToken() != null) {
		// The user is logged in, you can do whatever you like here.
		// In this example we just print the profile data, along with the profile picture and permissions.
		$profile = $google->userProfile();
		echo "<pre>" . print_r($profile, true) . "</pre><br /><br />\n\n";
	} else {
		echo "You have not granted access to Google. Click the link above.\n";
	}
	
