<?php
	// Start a session and load the OAuth library.
	session_start();
	require_once __DIR__ . '/oauth-client/src/microsoft.class.php';
	
	// Delete the access token if needed.
	if(isset($_GET["del_token"]) && isset($_SESSION["m_token"])) unset($_SESSION["m_token"]);
	
	// Create a new Microsoft object.
	$microsoft = new OAuthMicrosoft("0000000000000000", "0000000000000000000000000000000000000000", Array(
		"session_prefix" => "m_" // Prefix for session data. Defaults to microsoft_
	));
	
	// Output a Login Button.
	echo $microsoft->loginButton("Login with Microsoft Account", "https://example.com/microsoft-1/code.php", Array("wl.signin", "wl.basic"));
	
	// If an access token exists (OAuth2::accessToken() does not return null), fetch the current user's data.
	if($microsoft->accessToken() != null) {
		// The user is logged in, you can do whatever you like here.
		// In this example we just print the profile data, along with the profile picture and permissions.
		$profile = $microsoft->userProfile();
		echo "<pre>" . print_r($profile, true) . "</pre><br /><br />\n\n";
	} else {
		echo "You have not granted access to Microsoft. Click the link above.\n";
	}
	