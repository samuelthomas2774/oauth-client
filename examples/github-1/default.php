<?php
	// Start a session and load the OAuth library.
	session_start();
	require_once 'src/github.class.php';
	
	// Delete the access token if needed.
	if(isset($_GET["del_token"]) && isset($_SESSION["gh_token"])) unset($_SESSION["gh_token"]);
	
	// Create a new GitHub object.
	$github = new OAuthGitHub("0000000000000000", "0000000000000000000000000000000000000000", Array(
		"session_prefix" => "gh_" // Prefix for session data. Defaults to github_
	));
	
	// Output a Login Button.
	echo $github->loginButton("Login with GitHub", "https://example.com/github-1/code.php", Array("user"));
	
	// If an access token exists (OAuth::accessToken() does not return null), fetch the current user's data.
	if($github->accessToken() != null) {
		// The user is logged in, you can do whatever you like here.
		// In this example we just print the profile data, along with the profile picture and permissions.
		$profile = $github->userProfile();
		echo "<pre>" . print_r($profile, true) . "</pre><br /><br />\n\n";
	} else {
		echo "You have not granted access to GitHub. Click the link above.\n";
	}
	
