<?php
	/* class OAuth2
	 * /src/oauth.class.php
	 */
	if(!class_exists("OAuthRequest")) require_once __DIR__ . '/oauthrequest.class.php';
	
	class OAuth2 {
		// Array $client: An array of information about the client
		private $client = Array("id" => null, "secret" => null);
		
		// String $token: The current access token
		private $token = null;
		
		// Array / Object $options: Default options are returned by the OAuth2::defaultoptions() method
		// These will overwrite the default options, can be used to set default options for extended classes (subclasses)
		protected $options = null;
		
		// Error: Will be set / read / unset when an error is returned from the api
		public $error = null;
		
		// Constants
		const GET = "GET";
		const POST = "POST";
		const PUT = "PUT";
		const DELETE = "DELETE";
		
		const responseText = 10;
		const responseJSONArray = 21;
		const responseJSONObject = 22;
		const responseQueryStringArray = 31;
		const responseQueryStringObject = 32;
		const responseXMLArray = 41;
		const responseXMLObject = 42;
		const responseSimpleXMLObject = 43;
		
		// function __construct(): Creates a new OAuth2 object
		// $client_id: String or int, the client_id
		// $client_secret: String, the client_secret
		// $options: Array or object of options to set - this can also have an access_token property that will replace the access token - using this to set the access token will not change the access token in the session (if set)
		public function __construct($client_id, $client_secret, $options = Array()) {
			// Store Client ID in OAuth2::client["id"]
			if(!is_int($client_id) && !is_string($client_id)) throw new Exception(__METHOD__ . "(): \$client_id must be a string or an integer.");
			else $this->client["id"] = (string)$client_id;
			
			// Store Client Secret in OAuth2::client["secret"]
			if(!is_string($client_secret)) throw new Exception(__METHOD__ . "(): \$client_secret must be a string.");
			else $this->client["secret"] = $client_secret;
			
			// Save options
			if(!is_array($options) && !is_object($options)) throw new Exception(__METHOD__ . "(): \$options must be an array or an object.");
			else {
				$default_options = $this->defaultoptions();
				if(is_object($this->options) || is_array($this->options)) $extended_options = $this->options;
				else $extended_options = Array();
				
				$this->options = $default_options;
				foreach($extended_options as $key => $value) $this->options($key, $value);
				foreach($options as $key => $value) $this->options($key, $value);
			}
			
			// Try to restore the access token from options or the session
			if(is_string($this->options("access_token"))) {
				$this->token = $this->options("access_token"); unset($this->options->access_token);
			} elseif(is_string($this->session("token"))) $this->token = $this->session("token");
		}
		
		// function autorun(): Completes most OAuth-related tasks itself
		// $allowedactions: An integer/array containing the actions this function should do
		// Returns when the access token was replaced with the access token in $_GET["access_token"] or $_POST["access_token"]
		const AutoSetToken = 10;
		// Returns when the code in $_GET["code"] was used to get an access token
		const AutoGetFromCode = 20;
		// Returns when the username and password in $_POST["username"] and $_POST["password"] was used to get an access token
		const AutoGetFromPassword = 30;
		// Returns when the refresh token in was used to get an access token
		const AutoGetFromRefreshToken = 40;
		// Returns when nothing happened, $_GET/_POST["access_token"], $_GET["code"] and $_POST["username"&"password"] was not set
		const AutoFail = 50;
		public function autorun($allowedactions = null) {
			if($allowedactions === null)
				$allowedactions = Array(OAuth2::AutoSetToken, OAuth2::AutoGetFromCode, OAuth2::AutoGetFromPassword, OAuth2::AutoGetFromRefreshToken);
			
			// Check if redirect_url is a url - the redirect_url should be exactly the same as the redirect_url used in the login dialog (so really, this should just be the same as the current url)
			if(is_array($allowedactions)) {}
			if(is_int($allowedactions)) $allowedactions = Array($allowedactions);
			if(is_array($allowedactions)) throw new Exception(__METHOD__ . "(): \$allowedactions must be an array.");
			
			if(in_array(OAuth2::AutoSetToken, $allowedactions) && isset($_GET["access_token"]) && is_string($_GET["access_token"])) {
				$this->accessToken($_GET["access_token"]);
				return OAuth2::AutoSetToken;
			} elseif(in_array(OAuth2::AutoSetToken, $allowedactions) && isset($_POST["access_token"]) && is_string($_POST["access_token"])) {
				$this->accessToken($_GET["access_token"]);
				return OAuth2::AutoSetToken;
			} elseif(in_array(OAuth2::AutoGetFromCode, $allowedactions) && isset($_GET["code"]) && is_string($_GET["code"])) {
				$url = new stdClass();
				$url->protocol = "http" . (isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == "on") ? "s" : "");
				$url->host = $_SERVER["HTTP_HOST"];
				$p = strpos($_SERVER["REQUEST_URI"], "?");
				$url->path = ltrim(substr($_SERVER["REQUEST_URI"], 0, $p !== false ? $p : strlen($_SERVER["REQUEST_URI"])), "/");
				$this->getAccessTokenFromCode("{$url->protocol}://{$url->host}/{$url->path}");
				return OAuth2::AutoGetFromCode;
			} elseif(in_array(OAuth2::AutoGetFromPassword, $allowedactions) && isset($_POST["username"]) && is_string($_POST["username"]) && isset($_POST["password"]) && is_string($_POST["password"])) {
				$this->getAccessTokenFromUserCredentials($_POST["username"], $_POST["password"]);
				return OAuth2::AutoGetFromPassword;
			} elseif(in_array(OAuth2::AutoGetFromRefreshToken, $allowedactions) && isset($_GET["refresh_token"]) && is_string($_GET["refresh_token"])) {
				$this->getAccessTokenFromRefreshToken($_GET["refresh_token"]);
				return OAuth2::AutoGetFromRefreshToken;
			} elseif(in_array(OAuth2::AutoGetFromRefreshToken, $allowedactions) && isset($_POST["refresh_token"]) && is_string($_POST["refresh_token"])) {
				$this->getAccessTokenFromRefreshToken($_POST["refresh_token"]);
				return OAuth2::AutoGetFromRefreshToken;
			} else {
				return OAuth2::AutoFail;
			}
		}
		
		// function api(): Makes a new request to the server's API
		// $method: GET, POST, PUT or DELETE, the http method to use in this request
		// $url: The url to send this request to - if this is not a full valid url, it will be appended to the option api->base_url
		// $params: If this is a GET request, this will be url-encoded and added to the url, if this is a POST/PUT request this will become the request body
		// $headers: Additional headers to send - will overwrite headers set in the api->headers option
		// $auth: If true will send the client id and secret in the Authorization header
		public function api($method, $url, $params = Array(), $headers = Array(), $auth = false) {
			// Everything here is done by the OAuthRequest class
			return new OAuthRequest($this, $method, $url, $params, $headers, $auth);
		}
		
		// --- Authorization Code Grant --- //
		// function getAccessTokenFromCode(): Exchanges the code for an access token
		public function getAccessTokenFromCode($redirect_url, $code = null, $state = true) {
			// Check if redirect_url is a url - the redirect_url should be exactly the same as the redirect_url used in the login dialog (so really, this should just be the same as the current url)
			if(!is_string($redirect_url) || !filter_var($redirect_url, FILTER_VALIDATE_URL)) throw new Exception(__METHOD__ . "(): \$redirect_url must be a valid url.");
			
			// Check if code is a string or null
			if(is_string($code)) $code = trim($code);
			elseif(($code === null) && isset($_GET["code"])) $code = trim($_GET["code"]);
			else throw new Exception(__METHOD__ . "(): \$code must be a string.");
			
			// Check state if required
			if($state === true) $state = isset($_GET["state"]) ? $_GET["state"] : null;
			if($this->sessions() && ($state !== false) && ( // Check state? Ignore if sessions are disabled (as state won't exist) or if $state is set to false
				!is_string($this->session("state")) || // State is not set (or is not string): trigger error
				($this->session("state") != $state) // State does not match $state: trigger error
			)) {
				// Invalid state parameter
				$this->session("state", null);
				$this->triggerError("Invalid state parameter.");
				return;
			}
			
			// Delete the state session parameter
			$this->session("state", null);
			
			// Delete the access token
			$this->accessToken(null);
			
			// Example request: POST /oauth/token?client_id={client_id}&client_secret={client_secret}&redirect_uri={redirect_uri}&code={code}
			$request = $this->api(OAuth2::POST, $this->options([ "requests", "/oauth/token" ]), Array(
				"grant_type" => "authorization_code",
				"client_id" => $this->client()->id,
				"client_secret" => $this->client()->secret,
				"redirect_uri" => $redirect_url,
				"code" => $code
			), null, true);
			
			$request->execute();
			
			// Get the response
			if($this->options([ "requests", "/oauth/token:response" ]) == "query") $response = $request->responseQueryString();
			elseif($this->options([ "requests", "/oauth/token:response" ]) == "xml") $response = $request->responseXMLObject();
			else $response = $request->responseObject();
			if(isset($response->error)) return false;
			
			$this->accessToken($response->access_token);
			return $response;
		}
		
		// function getAccessTokenFromRefreshToken(): Exchanges a refresh token for an access token
		public function getAccessTokenFromRefreshToken($refresh_token) {
			// Check if refresh token is a string
			if(!is_string($refresh_token)) throw new Exception(__METHOD__ . "(): \$refresh_token must be a string.");
			
			// Unset the access token
			$this->accessToken(null);
			
			// Example request: POST /oauth/token?client_id={client_id}&client_secret={client_secret}&redirect_uri={redirect_uri}&code={code}
			$request = $this->api(OAuth2::POST, $this->options([ "requests", "/oauth/token" ]), Array(
				"grant_type" => "refresh_token",
				"client_id" => $this->client()->id,
				"client_secret" => $this->client()->secret,
				"refresh_token" => $refresh_token
			), null, true);
			
			$request->execute();
			
			// Get the response
			if($this->options([ "requests", "/oauth/token:response" ]) == "query") $response = $request->responseQueryString();
			elseif($this->options([ "requests", "/oauth/token:response" ]) == "xml") $response = $request->responseXMLObject();
			else $response = $request->responseObject();
			if(isset($response->error)) return false;
			
			$this->accessToken($response->access_token);
			return $response;
		}
		
		// --- Implicit Grant --- //
		// function iloginURL(): Returns the URL for the login dialog
		public function iloginURL($redirect_url, $permissions = Array(), $params = Array()) {
			if(is_array($params) && !isset($params["response_type"])) $params["response_type"] = "token";
			return $this->loginURL($redirect_url, $permissions, $params);
		}
		
		// function iloginButton(): Returns the URL for the login dialog
		public function iloginButton($button_text, $redirect_url, $permissions = Array(), $params = Array(), $colour = null) {
			if(is_array($params) && !isset($params["response_type"])) $params["response_type"] = "token";
			return $this->loginButton($button_text, $redirect_url, $permissions, $params, $colour);
		}
		
		// function iloginRedirect(): Redirects to the login dialog
		public function iloginRedirect($redirect_url, $permissions = Array(), $params = Array(), $message = "") {
			if(is_array($params) && !isset($params["response_type"])) $params["response_type"] = "token";
			return $this->loginRedirect($redirect_url, $permissions, $params, $message);
		}
		
		// --- Resource Owner Credentials Grant --- //
		// function getAccessTokenFromUserCredentials(): Uses a username and password to get an access token
		public function getAccessTokenFromUserCredentials($username, $password) {
			// Check if username and password is a string.
			if(!is_string($username)) throw new Exception(__METHOD__ . "(): \$username must be a string.");
			if(!is_string($password)) throw new Exception(__METHOD__ . "(): \$password must be a string.");
			
			// Unset the access token
			$this->accessToken(null);
			
			// Example request: POST /oauth/token?client_id={client_id}&client_secret={client_secret}&username={username}&password={password}
			$request = $this->api(OAuth2::POST, $this->options([ "requests", "/oauth/token" ]), Array(
				"grant_type" => "password",
				"client_id" => $this->client()->id,
				"client_secret" => $this->client()->secret,
				"username" => $username,
				"password" => $password
			), null, true);
			
			$request->execute();
			
			// Get the response
			if($this->options([ "requests", "/oauth/token:response" ]) == "query") $response = $request->responseQueryString();
			elseif($this->options([ "requests", "/oauth/token:response" ]) == "xml") $response = $request->responseXMLObject();
			else $response = $request->responseObject();
			if(isset($response->error)) return false;
			
			$this->accessToken($response->access_token);
			return $response;
		}
		
		// --- Client Credentials Grant --- //
		// function getAccessTokenFromClientCredentials(): Uses the client id and secret to get an access token
		public function getAccessTokenFromClientCredentials() {
			// Unset the access token.
			$this->accessToken(null);
			
			// Example request: POST /oauth/token?client_id={client_id}&client_secret={client_secret}
			$request = $this->api(OAuth2::POST, $this->options([ "requests", "/oauth/token" ]), Array(
				"grant_type" => "client_credentials",
				"client_id" => $this->client()->id,
				"client_secret" => $this->client()->secret
			), null, true);
			
			$request->execute();
			
			// Get the response
			if($this->options([ "requests", "/oauth/token:response" ]) == "query") $response = $request->responseQueryString();
			elseif($this->options([ "requests", "/oauth/token:response" ]) == "xml") $response = $request->responseXMLObject();
			else $response = $request->responseObject();
			if(isset($response->error)) return false;
			
			$this->accessToken($response->access_token);
			return $response;
		}
		
		// function validateAccessToken(): Checks if an access token is valid
		// Most OAuth 2 providers do not have any endpoint like this
		public function validateAccessToken($access_token = null) {
			// Check if access_token is string.
			if(!is_string($access_token)) $access_token = $this->accessToken();
			
			// Example request: GET /oauth/token/debug?access_token={access_token}
			$request = $this->api("GET", $this->options([ "requests", "/oauth/token/debug" ]), Array(
				"access_token" => $access_token
			));
			
			try {
				$request->execute();
				
				// Get the response
				if($this->options([ "requests", "/oauth/token:response" ]) == "query") $response = $request->responseQueryString();
				elseif($this->options([ "requests", "/oauth/token:response" ]) == "xml") $response = $request->responseXMLObject();
				else $response = $request->responseObject();
			} catch(Exception $e) { return false; }
			if(isset($response->error)) { $oauth->error = null; return false; }
			
			if($response->expires_in <= 0) return false;
			return true;
		}
		
		// function loginURL(): Returns the URL for the login dialog
		public function loginURL($redirect_url, $permissions = Array(), $params = Array()) {
			// Check if redirect_url is a url - the redirect_url should go to a PHP script on the same domain that runs OAuth2::getAccessTokenFromCode()
			if(!filter_var($redirect_url, FILTER_VALIDATE_URL)) throw new Exception(__METHOD__ . "(): \$redirect_url must be a valid url.");
			
			// Check if permissions is an array
			if(!is_array($permissions)) $permissions = Array();
			
			// Example Login Dialog URL to request a user's email address:
			// https://mydatastore.com/oauth/authorize?client_id={client_id}&redirect_uri={redirect_url}&response_type=code&scope=email
			
			// Generate a unique state parameter and store it in the session
			$state = hash("sha256", time() . uniqid(mt_rand(), true));
			$this->session("state", $state);
			
			$url_params = Array(
				"response_type" => "code",
				"client_id" => $this->client()->id,
				"redirect_uri" => $redirect_url,
				"scope" => implode($this->options([ "dialog", "scope_separator" ]), $permissions),
				"state" => $state
			);
			
			$url = $this->options([ "dialog", "base_url" ]) . "?" . http_build_query(array_merge($params, $url_params));
			return $url;
		}
		
		// function loginButton(): Returns the URL for the login dialog
		public function loginButton($button_text, $redirect_url, $permissions = Array(), $params = Array(), $colour = null) {
			// Check if button_text is a string
			if(!is_string($button_text)) throw new Exception(__METHOD__ . "(): \$button_text must be a string.");
			
			// Check if colour is a valid hex, rgb(a) or hsl(a) colour
			$expression = "/(\#([0-9ABCDEF][0-9ABCDEF][0-9ABCDEF]([0-9ABCDEF][0-9ABCDEF][0-9ABCDEF])?)|";
			$expression .= "rgb\(([0-9]*), *([0-9]*), *([0-9]*)\)|";
			$expression .= "rgba\(([0-9]*), *([0-9]*), *([0-9]*), *(0|0\.[0-9]*|1)\)|";
			$expression .= "hsl\(([0-9]*), *([0-9]*)%, *([0-9]*)%\)|";
			$expression .= "hsla\(([0-9]*), *([0-9]*)%, *([0-9]*)%, *([0-9]*)\)|";
			$expression .= "black|gray|white|red|green|blue|transparent)/i";
			if($colour === null) $colour = $this->options([ "button_colour" ]);
			elseif(!is_string($colour) || !preg_match($expression, $colour)) throw new Exception(__METHOD__ . "(): \$colour must be a string containing a valid colour.");
			
			// Get a Login Dialog URL using the OAuth2::loginURL() function
			$url = $this->loginURL($redirect_url, $permissions, $params);
			
			// Build the html tag
			$button = "<a class=\"oauth-login-button\" href=\"";
			$button .= htmlentities($url);
			$button .= "\" style=\"background-color:{$colour};display:inline-block;min-width:80px;width:calc(100% - 20px);padding:10px;text-align:center;color:white;font-family:arial;text-decoration:none;\">";
			$button .= htmlentities($button_text);
			$button .= "</a>";
			
			return $button;
		}
		
		// function loginRedirect(): Redirects to the login dialog
		public function loginRedirect($redirect_url, $permissions = Array(), $params = Array(), $message = "") {
			// Get a Login Dialog URL using the OAuth2::loginURL() function
			$url = $this->loginURL($redirect_url, $permissions, $params);
			
			// Make sure $message is a string
			if(!is_string($message)) $message = "";
			
			// Make sure headers have not been sent
			if(headers_sent()) throw new Exception(__METHOD__ . "(): Headers have already been sent.");
			
			// Redirect to the Login Dialog
			header("Location: {$url}", true, 303);
			exit($message);
		}
		
		// function accessToken(): Returns / sets the current access token
		public function accessToken($token = false, $session = true) {
			if($token === null) {
				$this->token = null;
				if($session === true) $this->session("token", null);
			} elseif(is_string($token)) {
				$this->token = $token;
				if($session === true) $this->session("token", $token);
			} else {
				return $this->token;
			}
		}
		
		// function options(): Returns / sets an option
		// Get: $oauth->options("session_prefix");
		// Get: $oauth->options(Array("dialog", "base_url"));
		// Get: $oauth->options([ "dialog", "base_url" ]);
		// Set: $oauth->options("session_prefix", "oauth_");
		// Set: $oauth->options(Array("dialog", "base_url"), "https://www.facebook.com/dialog/oauth");
		// Set: $oauth->options([ "dialog", "base_url" ], "https://www.facebook.com/dialog/oauth");
		// Set: $oauth->options(Array("api", "headers"), Array("X-Header" => "X-Value")); // Array will be merged.
		public function options($name) {
			$params = func_get_args();
			if(is_string($name) || is_int($name)) $name = Array($name);
			if(!is_array($name)) return null;
			$aset = array_key_exists(1, $params) ? true : false;
			
			$options = Array(&$this->options);
			$ek = 0;
			foreach($name as $i => $key) {
				if(is_object($options[$ek])) {
					if(!isset($options[$ek]->{$key}) && $aset) {
						$options[$ek]->{$key} = new stdClass();
						$options[$ek + 1] = &$options[$ek]->{$key};
					} elseif(!isset($options[$ek]->{$key}) && !$aset) $options[$ek + 1] = null;
					else $options[$ek + 1] = &$options[$ek]->{$key};
				} elseif(is_array($options[$ek])) {
					if(!isset($options[$ek][$key]) && $aset) {
						$options[$ek][$key] = Array();
						$options[$ek + 1] = &$options[$ek][$key];
					} elseif(!isset($options[$ek][$key]) && !$aset) $options[$ek + 1] = null;
					else $options[$ek + 1] = &$options[$ek][$key];
				} else {
					$options[$ek + 1] = &$options[$ek];
				}
				$ek++;
			}
			$option = &$options[$ek];
			
			if($aset && !array_key_exists(2, $params)) {
				$value = $params[1];
				
				/*if(is_object($option) && (is_object($value) || is_array($value))) {
					foreach($value as $k => $v) {
						if(is_object($v)) $option->{$k} = (object)array_merge((array)$option->{$k}, (array)$v);
						if(is_array($v)) $option->{$k} = (array)array_merge((array)$option->{$k}, (array)$v);
						else $option->{$k} = $v;
					}
				} elseif(is_array($option) && (is_object($value) || is_array($value))) {
					foreach($value as $k => $v) {
						if(is_object($v)) $option[$k] = (object)array_merge((array)$option[$k], (array)$v);
						if(is_array($v)) $option[$k] = (array)array_merge((array)$option[$k], (array)$v);
						else $option[$k] = $v;
					}
				} else*/ $option = $value;
			} else {
				return $option;
			}
		}
		
		// function defaultoptions(): Returns the default options
		public function defaultoptions() {
			$options = new stdClass();
			$options->session_prefix = "oauth_";
			$options->button_colour = "rgb(47,71,122)";
			
			$options->session_handler = new stdClass();
			$options->session_handler->check = "OAuth2::_session_check";
			$options->session_handler->get = "OAuth2::_session_get";
			$options->session_handler->set = "OAuth2::_session_set";
			$options->session_handler->delete = "OAuth2::_session_delete";
			
			// Login Dialog: Set a few important variables for using the Login Dialog
			$options->dialog = new stdClass();
			$options->dialog->base_url = "https://mydatastore.com/oauth/authorize";
			$options->dialog->scope_separator = " ";
			
			// API: Set a few important variables for using the API
			// token_auth: 1 = access_token parameter (default), 2 = Authorization header, false = Do not automatically send an access token
			$options->api = new stdClass();
			$options->api->base_url = "https://api.mydatastore.com";
			$options->api->token_auth = true;
			$options->api->headers = Array(
				"User-Agent" => "OAuth 2.0 Client https://github.com/samuelthomas2774/oauth-client"
			);
			$options->api->callback = null;
			
			// Default requests: Sets a few important variables for the requests this class makes
			$options->requests = new stdClass();
			$options->requests->{"/oauth/token"} = "/oauth/token";
			$options->requests->{"/oauth/token:response"} = "json";
			$options->requests->{"/oauth/token/debug"} = "/oauth/token/debug";
			
			// Errors: Sets how and when this class triggers errors
			// Invalid parameter exceptions are thrown even if throw is set to false here
			$options->errors = new stdClass();
			$options->errors->throw = true;
			
			return $options;
		}
		
		// function client(): Returns the client id and secret
		public function client() {
			return (object)$this->client;
		}
		
		// -- FOR OAuth2 AND OAuthRequest USE ONLY -- //
		// function triggerError(): Triggers an error - this should only be used by the OAuth2 and OAuthRequest classes
		public function triggerError($message, $error = null) {
			$this->error = $error !== null ? $error : $message;
			if($this->options([ "errors", "throw" ]) === true) throw new Exception($message);
		}
		
		// function sessions(): Checks if sessions are enabled
		public function sessions(&$prefix = null) {
			// Get session_prefix - if not a string or false reset to default
			if(!is_string($prefix = $this->options([ "session_prefix" ])) && ($prefix !== false))
				$this->options("session_prefix", $prefix = $this->defaultoptions()->session_prefix);
			
			if(!call_user_func($this->options([ "session_handler", "check" ]), $this))
				// Doesn't matter if sessions are disabled: one hasn't been started
				return false;
			elseif($prefix === false)
				// Sessions are diabled
				return false;
			else
				// Sessions are enabled and one is active
				return true;
		}
		
		// function session(): Returns / sets session data - this should only be used by the OAuth2 and OAuthRequest classes
		// Fails silently if sessions are disabled
		public function session($name, $value = null) {
			// Check if sessions are enabled
			if(!$this->sessions($session_prefix)) return null;
			
			if((func_num_args() >= 2) && ($value === null))
				// Delete
				call_user_func($this->options([ "session_handler", "delete" ]), $session_prefix . $name, $this);
			elseif(func_num_args() >= 2)
				// Set
				call_user_func($this->options([ "session_handler", "set" ]), $session_prefix . $name, $value, $this);
			else
				// Get
				return call_user_func($this->options([ "session_handler", "get" ]), $session_prefix . $name, $this);
		}
		
		// function sessionDelete(): Deletes session data - this should only be used by the OAuth2 and OAuthRequest classes
		// Fails silently if sessions are disabled
		// This can now be done with OAuth2::session() by setting $value to null
		public function sessionDelete($name) {
			// Check if sessions are enabled.
			if(!$this->sessions()) return null;
			$session_prefix = $this->options([ "session_prefix" ]);
			
			if(isset($_SESSION[$session_prefix . $name]))
				// Delete
				unset($_SESSION[$session_prefix . $name]);
		}
		
		// function _session_check(): Checks if sessions are enabled
		protected static function _session_check($oauth) {
			if(session_status() === PHP_SESSION_ACTIVE)
				// A session is active
				return true;
			else return false;
		}
		
		// function _session_get(): Reads session data
		protected static function _session_get($key, $oauth) {
			if(!self::_session_check($oauth))
				return null;
			elseif(isset($_SESSION[$key]))
				// Is set
				return $_SESSION[$key];
			else
				// Is not set
				return null;
		}
		
		// function _session_set(): Writes session data
		protected static function _session_set($key, $value, $oauth) {
			if(!self::_session_check($oauth))
				return null;
			
			// Set
			$_SESSION[$key] = $value;
		}
		
		// function _session_delete(): Deletes session data
		protected static function _session_delete($key, $oauth) {
			if(!self::_session_check($oauth))
				return null;
			
			// Delete
			$_SESSION[$key] = null;
			unset($_SESSION[$key]);
		}
	}
	