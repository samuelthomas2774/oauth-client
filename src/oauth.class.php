<?php
	/* class OAuth2
	 * /src/oauth.class.php
	 */
	if(!class_exists("OAuthRequest")) require_once __DIR__ . '/oauthrequest.class.php';
	
	class OAuth2 {
		// Array $client. An array of information about the client.
		private $client = Array("id" => null, "secret" => null);
		
		// String $token. The current access token.
		private $token = null;
		
		// Array / Object $options. Default options are returned by the OAuth2::defaultoptions() method.
		// These will overwrite the default options, can be used to set default options for extended classes (subclasses).
		protected $options = null;
		
		// Constants.
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
		
		// function __construct(). Creates a new OAuth2 object.
		// $client_id: String or int, the client_id.
		// $client_secret: String, the client_secret.
		// $options: Array or object of options to set. This can also have an access_token property that will replace the access token. Using this to set the access token will not change the access token in the session (if set).
		public function __construct($client_id, $client_secret, $options = Array()) {
			// Store Client ID in OAuth2::client["id"].
			if(!is_int($client_id) && !is_string($client_id)) throw new Exception(__METHOD__ . "(): \$client_id must be a string or an integer.");
			else $this->client["id"] = (string)$client_id;
			
			// Store Client Secret in OAuth2::client["secret"].
			if(!is_string($client_secret)) throw new Exception(__METHOD__ . "(): \$client_secret must be a string.");
			else $this->client["secret"] = $client_secret;
			
			// Save options.
			if(!is_array($options) && !is_object($options)) throw new Exception(__METHOD__ . "(): \$options must be an array or an object.");
			else {
				$default_options = $this->defaultoptions();
				if(is_object($this->options) || is_array($this->options)) $extended_options = (array)$this->options;
				else $extended_options = Array();
				$user_options = (array)$options;
				
				$this->options = $default_options;
				foreach($extended_options as $key => $value) $this->options($key, $value);
				foreach($options as $key => $value) $this->options($key, $value);
			}
			
			// Try to restore the access token from options or the session.
			if($this->options("access_token") != null) {
				$this->token = $this->options("access_token"); unset($this->options->access_token);
			} elseif($this->session("token") != null) $this->token = $this->session("token");
		}
		
		// function api(). Makes a new request to the server's API.
		// $method: GET, POST, PUT or DELETE, the http method to use in this request.
		// $url: The url to send this request to. If this is not a full valid url, it will be appended to the option api->base_url.
		// $params: If this is a GET request, this will be url-encoded and added to the url, if this is a POST/PUT request this will become the request body.
		// $headers: Additional headers to send. Will overwrite headers set in the api->headers option.
		// $auth: If true will send the client id and secret in the Authorization header.
		public function api($method, $url, $params = Array(), $headers = Array(), $auth = false) {
			// Everything here is done by the OAuthRequest class.
			return new OAuthRequest($this, $method, $url, $params, $headers, $auth);
		}
		
		// function getAccessTokenFromCode(). Exchanges the code for an access token.
		public function getAccessTokenFromCode($redirect_url, $code = null, $state = true) {
			// Check if redirect_url is a url. The redirect_url should be exactly the same as the redirect_url used in the login dialog. (So really, this should just be the same as the current url.)
			if(!filter_var($redirect_url, FILTER_VALIDATE_URL)) throw new Exception(__METHOD__ . "(): \$redirect_url must be a valid url.");
			
			// Check if code is a string or null.
			if(is_string($code)) $code = trim($code);
			elseif(($code == null) && isset($_GET["code"])) $code = trim($_GET["code"]);
			else throw new Exception(__METHOD__ . "(): \$code must be a string.");
			
			// Check state if required.
			if($state == true) $state = isset($_GET["state"]) ? $_GET["state"] : null;
			if(($this->options("session_prefix") != null) && ($state != false) && ( // Check state? Ignore if sessions are disabled (as state won't exist) or if $state is set to false.
				($this->session("state") == null) || // State is not set: trigger error.
				($this->session("state") != $state) // State does not match $state: trigger error.
			)) {
				// Invalid state parameter.
				$this->sessionDelete("state");
				$this->triggerError("Invalid state parameter.");
				return;
			}
			
			// Unset the state session parameter.
			$this->sessionDelete("state");
			
			// Unset the access token.
			$this->accessToken(null);
			
			// Example request: POST /oauth/token?client_id={client_id}&client_secret={client_secret}&redirect_uri={redirect_uri}&code={code}
			$request = $this->api(OAuth2::POST, $this->options([ "requests", "/oauth/token" ]), Array(
				"grant_type" => "authorization_code",
				"client_id" => $this->client()->id,
				"client_secret" => $this->client()->secret,
				"redirect_uri" => $redirect_url,
				"code" => $code,
				"state" => $state
			), null, true);
			
			$request->execute();
			
			// Get the response.
			if($this->options([ "requests", "/oauth/token:response" ]) == "query") $response = $request->responseQueryString();
			elseif($this->options([ "requests", "/oauth/token:response" ]) == "xml") $response = $request->responseXMLObject();
			else $response = $request->responseObject();
			
			$this->accessToken($response->access_token);
			return $response;
		}
		
		// function getAccessTokenFromRefreshToken(). Exchanges a refresh token for an access token.
		public function getAccessTokenFromRefreshToken($refresh_token) {
			// Check if refresh token is a string.
			if(!is_string($refresh_token)) throw new Exception(__METHOD__ . "(): \$refresh_token must be a string.");
			
			// Unset the access token.
			$this->accessToken(null);
			
			// Example request: POST /oauth/token?client_id={client_id}&client_secret={client_secret}&redirect_uri={redirect_uri}&code={code}
			$request = $this->api(OAuth2::POST, $this->options([ "requests", "/oauth/token" ]), Array(
				"grant_type" => "refresh_token",
				"client_id" => $this->client()->id,
				"client_secret" => $this->client()->secret,
				"refresh_token" => $refresh_token
			), null, true);
			
			$request->execute();
			
			// Get the response.
			if($this->options([ "requests", "/oauth/token:response" ]) == "query") $response = $request->responseQueryString();
			elseif($this->options([ "requests", "/oauth/token:response" ]) == "xml") $response = $request->responseXMLObject();
			else $response = $request->responseObject();
			
			$this->accessToken($response->access_token);
			return $response;
		}
		
		// function validateAccessToken(). Checks if an access token is valid. Most OAuth 2 providers do not have this endpoint.
		public function validateAccessToken($access_token = null) {
			// Check if access_token is string.
			if(!is_string($access_token)) $access_token = $this->accessToken();
			
			// Example request: GET /oauth/token/debug?access_token={access_token}
			$request = $this->api("GET", $this->options([ "requests", "/oauth/token/debug" ]), Array(
				"access_token" => $access_token
			));
			
			try {
				$request->execute();
				
				// Get the response.
				if($this->options([ "requests", "/oauth/token:response" ]) == "query") $response = $request->responseQueryString();
				elseif($this->options([ "requests", "/oauth/token:response" ]) == "xml") $response = $request->responseXMLObject();
				else $response = $request->responseObject();
			} catch(Exception $e) { return false; }
			if(isset($response->error)) return false;
			
			if($response->expires_in <= 0) return false;
			return true;
		}
		
		// function loginURL(). Returns the URL for the login dialog.
		public function loginURL($redirect_url, $permissions = Array(), $params = Array()) {
			// Check if redirect_url is a url. The redirect_url should go to a PHP script on the same domain that runs OAuth::getAccessTokenFromCode().
			if(!filter_var($redirect_url, FILTER_VALIDATE_URL)) throw new Exception(__METHOD__ . "(): \$redirect_url must be a valid url.");
			
			// Check if permissions is an array.
			if(!is_array($permissions)) $permissions = Array();
			
			// Example Login Dialog URL to request a user's email address:
			// https://mydatastore.com/oauth/authorize?client_id={client_id}&redirect_uri={redirect_url}&response_type=code&scope=email
			
			// Generate a unique state parameter and store it in the session.
			$state = hash("sha1", time() . uniqid(mt_rand(), true));
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
		
		// function loginButton(). Returns the URL for the login dialog.
		public function loginButton($button_text, $redirect_url, $permissions = Array(), $params = Array(), $colour = "rgb(47,71,122)") {
			// Check if button_text is a string.
			if(!is_string($button_text)) throw new Exception(__METHOD__ . "(): \$button_text must be a string.");
			
			// Check if colour is a valid hex, rgb(a) or hsl(a) colour.
			if(!preg_match("/(\#([0-9ABCDEF][0-9ABCDEF][0-9ABCDEF]([0-9ABCDEF][0-9ABCDEF][0-9ABCDEF])?)|rgb\(([0-9]*), ?([0-9]*), ?([0-9]*)\)|rgba\(([0-9]*), ?([0-9]*), ?([0-9]*), ?([0-9]*)\)|rgb\(([0-9]*), ?([0-9]*), ?([0-9]*)\)|rgba\(([0-9]*), ?([0-9]*), ?([0-9]*), ?([0-9]*)\)|black|gray|white|red|green|blue|transparent)/i", $colour)) throw new Exception(__METHOD__ . "(): \$colour must be a string.");
			
			// Get a Login Dialog URL using the OAuth::loginURL() function.
			$url = $this->loginURL($redirect_url, $permissions, $params);
			
			// Build the html tag.
			$button = "<a href=\"";
			$button .= htmlentities($url);
			$button .= "\" style=\"background-color:{$colour};display:block;min-width:80px;width:calc(100%-20px);padding:10px;text-align:center;color:white;font-family:arial;text-decoration:none;\">";
			$button .= htmlentities($button_text);
			$button .= "</a>";
			
			return $button;
		}
		
		// function loginRedirect(). Redirects to the login dialog.
		public function loginRedirect($redirect_url, $permissions = Array(), $params = Array()) {
			// Get a Login Dialog URL using the OAuth::loginURL() function.
			$url = $this->loginURL($redirect_url, $permissions, $params);
			
			// Redirect to the Login Dialog.
			header("Location: {$url}", true, 303);
			exit();
		}
		
		// function accessToken(). Returns / sets the current access token.
		public function accessToken($token = false, $session = true) {
			if($token === null) {
				$this->token = null;
				if($session == true) $this->sessionDelete("token");
			} elseif(is_string($token)) {
				$this->token = $token;
				if($session == true) $this->session("token", $token);
			} else {
				return $this->token;
			}
		}
		
		// function options(). Returns / sets an option.
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
			if(isset($params[1])) $value = $params[1];
			
			$option = $this->options;
			$nk = array_keys($name);
			$lo = end($nk);
			foreach($name as $i => $key) {
				if(is_object($option) && isset($option->{$key}) && ($lo == $i)) $option = &$option->{$key};
				elseif(is_array($option) && isset($option[$key]) && ($lo == $i)) $option = &$option[$key];
				elseif(is_object($option) && isset($option->{$key})) $option = $option->{$key};
				elseif(is_array($option) && isset($option[$key])) $option = $option[$key];
				else return null;
			}
			
			if(isset($value)) {
				if(is_object($option) && (is_object($value) || is_array($value))) {
					foreach($value as $k => $v) {
						if(is_object($v)) $option->{$k} = (object)array_merge((array)$option->{$k}, (array)$v);
						if(is_array($v)) $option->{$k} = (array)array_merge((array)$option->{$k}, (array)$v);
						else $option->{$k} = $v;
					}
				} elseif(is_array($option) && (is_object($value) || is_array($value))) {
					foreach($value as $k => $v) {
						if(is_object($v)) $option->{$k} = (object)array_merge((array)$option->{$k}, (array)$v);
						if(is_array($v)) $option->{$k} = (array)array_merge((array)$option->{$k}, (array)$v);
						else $option->{$k} = $v;
					}
				} else $option = $value;
			} else {
				return $option;
			}
		}
		
		// function defaultoptions(). Returns the default options.
		public function defaultoptions() {
			$options = new stdClass();
			$options->session_prefix = "oauth_";
			
			// Login Dialog. Set a few important variables for using the Login Dialog.
			$options->dialog = new stdClass();
			$options->dialog->base_url = "https://mydatastore.com/oauth/authorize";
			$options->dialog->scope_separator = " ";
			
			// API. Set a few important variables for using the API.
			// token_auth: 1 = access_token parameter (default), 2 = Authorization header, false = Do not automatically send an access token.
			$options->api = new stdClass();
			$options->api->base_url = "https://api.mydatastore.com";
			$options->api->token_auth = true;
			$options->api->headers = Array(
				"User-Agent" => "OAuth 2.0 Client https://github.com/samuelthomas2774/oauth-client"
			);
			$options->api->callback = null;
			
			// Default requests. Sets a few important variables for the requests this class makes.
			$options->requests = new stdClass();
			$options->requests->{"/oauth/token"} = "/oauth/token";
			$options->requests->{"/oauth/token:response"} = "json";
			$options->requests->{"/oauth/token/debug"} = "/oauth/token/debug";
			
			// Errors. Sets how and when this class triggers errors.
			// Invalid parameter exceptions are thrown even if throw is set to false here.
			$options->errors = new stdClass();
			$options->errors->throw = true;
			
			return $options;
		}
		
		// function client(). Returns the client id and secret.
		public function client() {
			return (object)$this->client;
		}
		
		// -- FOR OAuth2 AND OAuthRequest USE ONLY -- //
		// function triggerError(). Triggers an error. This should only be used by the OAuth2 and OAuthRequest classes.
		public function triggerError($message, $error = null) {
			$this->error = $error != null ? $error : $message;
			if($this->options([ "errors", "throw" ]) == true) throw new Exception($message);
		}
		
		// function session(). Returns / Sets session data. This should only be used by the OAuth2 and OAuthRequest classes.
		// Fails silently if sessions are disabled.
		public function session($name) {
			$params = func_get_args();
			if(isset($params[1])) $value = $params[1];
			
			$session_prefix = $this->options("session_prefix");
			if(!is_string($session_prefix) && ($session_prefix != false))
				$this->options("session_prefix", $session_prefix = $this->defaultoptions()->session_prefix);
			
			if($session_prefix == false) return null; // Sessions are diabled.
			elseif(isset($value)) // Set
				$_SESSION[$session_prefix . $name] = $value;
			elseif(isset($_SESSION[$session_prefix . $name])) // Get
				return $_SESSION[$session_prefix . $name];
			else return null;
		}
		
		// function sessionDelete(). Deletes session data. This should only be used by the OAuth2 and OAuthRequest classes.
		// Fails silently if sessions are disabled.
		public function sessionDelete($name) {
			$session_prefix = $this->options("session_prefix");
			if(!is_string($session_prefix) && ($session_prefix != false))
				$this->options("session_prefix", $session_prefix = $this->defaultoptions()->session_prefix);
			
			if($session_prefix == false) return null; // Sessions are diabled.
			elseif(isset($_SESSION[$session_prefix . $name])) unset($_SESSION[$session_prefix . $name]); // Delete
		}
	}
	