<?php
	// Start a session and load the OAuth library.
	session_start();
	require_once __DIR__ . '/oauth-client/src/github.class.php';
	
	// Create a new GitHub object.
	$github = new OAuthGitHub("0000000000000000", "0000000000000000000000000000000000000000", Array(
		"session_prefix" => "gh_" // Prefix for session data. Defaults to github_
	));
	
	// Try to fetch an access token with the code in $_GET["code"]. Also check the state in $_GET["state"].
	try {
		$github->getAccessTokenFromCode("http://example.com/github-1/code.php");
		
		// --------
		// If we got here, no error was thrown and an access token was successfully retrieved from the code.
		// Output the code and access token.
		echo "Success! Click the link at the bottom of the page to return home and fetch data using the access token.<br />\n";
		echo "Code: " . htmlspecialchars($_GET["code"]) . "<br />\n";
		echo "Access Token: " . htmlspecialchars($github->accessToken()) . "\n<br /><br />\n\n";
	} catch(Exception $error) {
		echo "Error - Click the link at the bottom of the page to return home and try again: " . $error->getMessage() . "\n<br /><br />\n\n";
	}
	
	// Output a link to the homepage to fetch data using the access token.
	echo "<a href=\"./\">Home</a>\n";
	