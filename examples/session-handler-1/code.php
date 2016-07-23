<?php
	// Load the OAuth library
	require_once __DIR__ . '/oauth-client/src/facebook.class.php';
	
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
	
	// Try to fetch an access token with the code in $_GET["code"] - also check the state in $_GET["state"]
	try {
		$facebook->getAccessTokenFromCode("https://example.com/code.php");
		
		// --------
		// If we got here, no error was thrown and an access token was successfully retrieved from the code
		// Output the code and access token
		echo "Success! Click the link at the bottom of the page to return home and fetch data using the access token.<br />\n";
		echo "Code: " . htmlspecialchars($_GET["code"]) . "<br />\n";
		echo "Access Token: " . htmlspecialchars($facebook->accessToken()) . "\n<br /><br />\n\n";
	} catch(Exception $error) {
		echo "Error - Click the link at the bottom of the page to return home and try again: " . $error->getMessage() . "\n<br /><br />\n\n";
	}
	
	// Output a link to the homepage to fetch data using the access token
	echo "<a href=\"./\">Home</a>\n";
	