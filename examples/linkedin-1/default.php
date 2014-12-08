<?php
	// Start a session and load the OAuth library.
	session_start();
	require_once 'src/linkedin.class.php';
	
	// Delete the access token if needed.
	if(isset($_GET["del_token"]) && isset($_SESSION["l_token"])) unset($_SESSION["l_token"]);
	
	// Create a new LinkedIn object.
	$linkedin = new OAuthLinkedin("0000000000000000", "0000000000000000000000000000000000000000", Array(
		"session_prefix" => "l_" // Prefix for session data. Defaults to linkedin_
	));
	
	// Output a Login Button.
	echo $linkedin->loginButton("Login with LinkedIn", "https://example.com/linkedin-1/code.php", Array("r_basicprofile"));
	
	// If an access token exists (OAuth::accessToken() does not return null), fetch the current user's data.
	if($linkedin->accessToken() != null) {
		// The user is logged in, you can do whatever you like here.
		// In this example we just print the profile data, along with the profile picture and permissions.
		$profile = $linkedin->userProfile();
		echo "<pre>" . print_r($profile, true) . "</pre><br /><br />\n\n";
	} else {
		echo "You have not granted access to LinkedIn. Click the link above.\n";
	}
	
