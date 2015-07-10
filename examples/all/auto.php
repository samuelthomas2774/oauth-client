<?php
	// Start a session (files are loaded later).
	session_start();
	
	// Require and register the autoloader.
	require_once __DIR__ . '/../../src/autoload.php';
	
	// Get the current server from PATH_INFO.
	if(!isset($_SERVER["PATH_INFO"])) {
		echo "Server was not found.<br />\n\n";
		goto session;
	} $server_url = ltrim($_SERVER["PATH_INFO"], "/");
	
	// Get all the servers from clients.json.
	if(file_exists(__DIR__ . "/../../../clients.json")) $servers = json_decode(file_get_contents(__DIR__ . "/../../../clients.json"));
	else $servers = json_decode(file_get_contents(__DIR__ . "/clients.json"));
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
	if(isset($server->file)) require_once __DIR__ . '/' . ltrim($server->file, "/");
	$oauth = new $server->class($server->id, $server->secret, Array("errors" => Array("throw" => false)));
	
	// Errors should be thrown here: just an example of using OAuth2::options().
	$oauth->options([ "errors", "throw" ], true);
	
	// Do stuff.
	try {
		$action = $oauth->autorun();
		
		// --------
		// Check what happened.
		echo "Action {$action} completed.<br /><br />\n\n";
	} catch(Exception $error) {
		echo "Error - Click the link at the bottom of the page to return home and try again: " . $error->getMessage() . "\n<br /><br />\n\n";
	}
	
	// Output a link to the homepage to fetch data using the access token.
	echo "<a href=\"../default.php/{$server->url}\">Home</a>\n";
	
	session:
	echo "<pre>Session: " . print_r($_SESSION, true) . "</pre>\n";
	