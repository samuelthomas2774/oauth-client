<?php
	// Start a session (files are loaded later).
	session_start();
	
	// Get the current server from PATH_INFO.
	if(!isset($_SERVER["PATH_INFO"])) {
		echo "Server was not found.<br />\n\n";
		goto session;
	} $server_url = ltrim($_SERVER["PATH_INFO"], "/");
	
	// Get all the servers from clients.json.
	$servers = json_decode(file_get_contents(__DIR__ . "/clients.json"));
	if(!is_object($servers)) {
		echo "Invalid clients.json file.<br />\n\n";
		goto session;
	}
	
	if(!isset($servers->{$server_url})) {
		echo "Server was not found.<br />\n\n";
		goto session;
	}
	
	$server = (object)$servers->{$server_url};
	$server->url = $server_url;
	if(!isset($server->name)) $server->name = ucfirst(strtolower($server->url));
	if(!isset($server->scope)) $server->scope = Array();
	require_once __DIR__ . '/oauth-client/src/' . ltrim($server->file, "/");
	$oauth = new $server->class($server->id, $server->secret, Array("errors" => Array("throw" => false)));
	
	// Delete the access token if needed.
	if(isset($_GET["del_token"])) $oauth->accessToken(null);
	
	// Errors should be thrown here: just an example of using OAuth2::options().
	$oauth->options([ "errors", "throw" ], true);
	
	// Try to fetch an access token with the code in $_GET["code"]. Also check the state in $_GET["state"].
	try {
		$oauth->getAccessTokenFromCode("https://example.com/code.php/{$server->url}");
		
		// --------
		// If we got here, no error was thrown and an access token was successfully retrieved from the code.
		// Output the code and access token.
		echo "Success! Click the link at the bottom of the page to return home and fetch data using the access token.<br />\n";
		echo "Code: " . htmlspecialchars($_GET["code"]) . "<br />\n";
		echo "Access Token: " . htmlspecialchars($oauth->accessToken()) . "\n<br /><br />\n\n";
	} catch(Exception $error) {
		echo "Error - Click the link at the bottom of the page to return home and try again: " . $error->getMessage() . "\n<br /><br />\n\n";
	}
	
	// Output a link to the homepage to fetch data using the access token.
	echo "<a href=\"../default.php/{$server->url}\">Home</a>\n";
	
	session:
	echo "<pre>Session: " . print_r($_SESSION, true) . "</pre>\n";
	