<?php
	// Start a session (files are loaded later).
	session_start();
	
	// Require and register the autoloader.
	require_once __DIR__ . '/../../src/autoload.php';
	
	// Get all the servers from clients.json.
	if(file_exists(__DIR__ . "/../../../clients.json")) $servers = json_decode(file_get_contents(__DIR__ . "/../../../clients.json"));
	else $servers = json_decode(file_get_contents(__DIR__ . "/clients.json"));
	if(!is_object($servers)) {
		echo "Invalid clients.json file.<br />\n\n";
		goto session;
	}
	
	// Get the current server from PATH_INFO.
	if(!isset($_SERVER["PATH_INFO"])) {
		echo "No server.<br />\n";
		foreach($servers as $url => $server) {
			if(!isset($server->name)) $server->name = ucfirst(strtolower($server->url));
			echo "<a href=\"/default.php/{$url}\">{$server->name}</a><br />\n";
		} echo "\n";
		goto session;
	} $server_url = ltrim($_SERVER["PATH_INFO"], "/");
	
	if(!isset($servers->{$server_url})) {
		echo "Server was not found.<br />\n\n";
		goto session;
	}
	
	$server = (object)$servers->{$server_url};
	$server->url = $server_url;
	if(!isset($server->name)) $server->name = ucfirst(strtolower($server->url));
	if(!isset($server->scope)) $server->scope = Array();
	if(isset($server->file)) require_once __DIR__ . '/' . ltrim($server->file, "/");
	$oauth = new $server->class($server->id, $server->secret, Array("errors" => Array("throw" => false)));
	
	// Delete the access token if needed.
	if(isset($_GET["del_token"])) $oauth->accessToken(null);
	
	// Output a Login Button.
	echo $oauth->loginButton("Login with {$server->name}", $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . "/code.php/{$server->url}", $server->scope);
	echo "\n<br />\n\n";
	
	// Try fetching the user's data. If an error is thrown, show a link to the login dialog.
	if($oauth->accessToken() != null) {
		// The user is logged in, you can do whatever you like here.
		// In this example we just print the profile data.
		$profile = $oauth->userProfile();
		$picture = $oauth->profilePicture();
		if(!isset($oauth->error)) {
			echo "<pre>" . print_r($profile, true) . "</pre><br /><br />\n\n";
			echo "<pre>" . print_r($picture, true) . "</pre><br /><br />\n\n";
		} else {
			// Errors are added to $oauth->error.
			echo "<pre>Error: " . print_r($oauth->error, true) . "</pre><br /><br />\n\n";
			unset($oauth->error);
		}
	} else {
		echo "You have not granted access to {$server->name}. Click the link above.<br />\n\n";
	}
	
	session:
	echo "<pre>Session: " . print_r($_SESSION, true) . "</pre>\n";
	