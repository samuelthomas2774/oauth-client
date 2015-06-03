<?php
	/* class OAuth2
	 * /src/oauth.class.php
	 */
	require_once 'oauthrequest.class.php';
	
	class OAuth2 {
		// Array $client. An array of information about the client.
		private $client = Array("id" => null, "secret" => null);
		
		// String $token. The current access token.
		private $token = null;
		
		// Options. These shouldn't be modified here, but using the OAuth::options() function.
		public $options = Array(
			"session_prefix"		=> "oauth_",
			// Login Dialog. Set a few important variables for using the Login Dialog.
			"dialog"				=> Array("base_url" => "https://www.facebook.com/dialog/oauth", "scope_separator" => ","),
			// API. Set a few important variables for using the API.
			// token_auth: 1 = access_token parameter (default), 2 = Authorization header, false = Do not automatically send an access token.
			"api"					=> Array("base_url" => "https://graph.facebook.com/v2.1", "token_auth" => true, "headers" => Array(
				"User-Agent"			=> "OAuth 2.0 Client https://github.com/samuelthomas2774/oauth-client"
			), "callback" => null),
			// Default requests. Sets a few important variables for the requests this class makes.
			"requests"				=> Array("/oauth/token" => "/oauth/token", "/oauth/token:response" => "json", "/oauth/token/debug" => "/oauth/token/debug"),
			// Errors. Sets how and when this class triggers errors. Note that invalid parameter exceptions are thrown even if throw is set to false here.
			"errors"				=> Array("throw" => true)
		);
		
		// Constants.
		const responseText = 10;
		const responseJSONArray = 21;
		const responseJSONObject = 22;
		const responseQueryStringArray = 31;
		const responseQueryStringObject = 32;
		const responseXMLArray = 41;
		const responseXMLObject = 42;
		const responseSimpleXMLObject = 43;
		
		// function __construct(). Creates a new OAuth2 object.
		public function __construct($client_id, $client_secret, $options = Array()) {
			// Store App ID in OAuth2::client["id"].
			if(!is_int($client_id) && !is_string($client_id)) throw new Exception(__METHOD__ . "(): \$client_id must be a string or an integer.");
			else $this->client["id"] = (string)$client_id;
			
			// Store App Secret in OAuth2::client["secret"].
			if(!is_string($client_secret)) throw new Exception(__METHOD__ . "(): \$client_secret must be a string.");
			else $this->client["secret"] = $app_secret;
			
			// Save options.
			if(!is_array($options)) throw new Exception(__METHOD__ . "(): \$options must be an array.");
			else foreach($options as $key => $value) {
				$this->options($key, $value);
			}
			
			// Try to restore the access token from the session.
			if($this->options("access_token") != null) { $this->token = $this->options("access_token"); unset($this->options["access_token"]); }
			elseif($this->session("token") != null) $this->token = $this->session("token");
		}
		
		// function api(). Makes a new request to the server's API.
		public function api($method, $url, $params = Array(), $headers = Array(), $auth = false) {
			// Everything here is done by the OAuthRequest class.
			// $method: GET, POST, PUT or DELETE, the http method to use in this request.
			// $url: The url to send this request to. If this is not a full valid url (http://api.example.com/v1.0/user), it will be appended to the option api->base_url ($base_url = "http://api.example.com/v1.0/"; $url = "/user"; $request_url = "http://api.example.com/v1.0/user";)
			// $params: If this is a GET request, this will be url-encoded and added to the url, if this is a POST/PUT request this will become the request body.
			// $headers: Additional headers to send. Will overwrite headers set in the api->headers option.
			// $auth: If true will send the client id and secret in the Authorization header.
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
			if( ($state != false) && // Check state?
				(
					($this->session("state") == null) || // State is not set: trigger error.
					($this->session("state") != $state) // State does not match $state: trigger error.
				)
			) {
				// Invalid state parameter.
				$this->sessionDelete("state");
				$this->triggerError("Invalid state parameter.");
				return;
			}
			
			// Unset the state session parameter.
			$this->sessionDelete("state");
			
			// Unset the access token.
			$this->token = null;
			
			// Example request: POST /oauth/token?client_id={client_id}&client_secret={client_secret}&redirect_uri={redirect_uri}&code={code}
			$request = $this->api("POST", $this->options("requests")["/oauth/token"], Array(
				"grant_type"			=> "authorization_code",
				"client_id"				=> $this->client["id"],
				"client_secret"			=> $this->client["secret"],
				"redirect_uri"			=> $redirect_url,
				"code"					=> $code,
				"state"					=> $state
			), null, true);
			
			$request->execute();
			
			// OAuth 2 spec states that this url should responsed with valid JSON, query string is for Facebook API >= v2.2.
			if($this->options("requests")["/oauth/token:response"] == "query") $response = $request->responseQueryString();
			else $response = $request->responseObject();
			
			$this->accessToken($response->access_token);
		}
		
		// function validateAccessToken(). Checks if an access token is valid. Most OAuth 2 provider do not have this endpoint.
		public function validateAccessToken($access_token = null) {
			// Check if access_token is string.
			if(!is_string($access_token)) $access_token = $this->token;
			
			// Example request: GET /oauth/token/debug?access_token={access_token}
			$request = $this->api("GET", $this->options("requests")["/oauth/token/debug"], Array(
				"access_token"			=> $access_token
			));
			
			try { $request->execute(); $response = $request->responseObject(); }
			catch(Exception $e) { return false; }
			if(isset($response->error)) return false;
			
			if($response->expires_in <= 0) return false;
			return true;
		}
		
		// function loginURL(). Returns the URL for the login dialog.
		public function loginURL($redirect_url, $permissions = Array(), $params = Array()) {
			// Check if redirect_url is a url. The redirect_url should go to a PHP script on the same domain that runs OAuth::getAccessTokenFromCode().
			if(!filter_var($redirect_url, FILTER_VALIDATE_URL)) throw new Exception(__CLASS__ . "::" . __METHOD__ . "(): \$redirect_url must be a valid url.");
			
			// Check if permissions is an array.
			if(!is_array($permissions)) $permissions = Array();
			
			// Example Login Dialog URL to request a user's email address and friends who also use the application: https://oauth.mydatastore.com/dialog/oauth?client_id={app_id}&redirect_uri={redirect_url}&response_type=code&scope=email,user_friends
			
			// Generate a unique state parameter and store it in the session.
			$state = hash("sha1", time() . uniqid(mt_rand(), true));
			$this->session("state", $state);
			
			$url_params = Array(
				"response_type"			=> "code",
				"client_id"				=> $this->client()->id,
				"redirect_uri"			=> $redirect_url,
				"scope"					=> implode($this->options("dialog")["scope_separator"], $permissions),
				"state"					=> $state
			);
			
			$url = $this->options("dialog")["base_url"] . "?" . http_build_query(array_merge($params, $url_params));
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
		public function accessToken($token = null, $session = true) {
			if(is_string($token)) {
				$this->token = $token;
				if($session == true) $this->session("token", $token);
			} elseif($token == false) {
				$this->token = null;
			} else return $this->token;
		}
		
		// function options(). Returns / sets an option.
		public function options($name) {
			$params = func_get_args();
			if(isset($params[1])) {
				$value = $params[1];
				if(isset($this->options[$name]) && is_array($this->options[$name]) && is_array($value)) $this->options[$name] = array_merge($this->options[$name], $value);
				elseif(isset($this->options[$name]) && is_object($this->options[$name]) && is_object($value)) $this->options[$name] = (object)array_merge((array)$this->options[$name], (array)$value);
				else $this->options[$name] = $value;
			} else return isset($this->options[$name]) ? $this->options[$name] : null;
		}
		
		// function client(). Returns the client id and secret.
		public function client() {
			return (object)$this->client;
		}
		
		// -- FOR OAuth2 AND OAuthRequest USE ONLY -- //
		// function triggerError(). Triggers an error. This should only be used by the OAuth2 and OAuthRequest classes.
		public function triggerError($message, $error = null) {
			$this->error = $error != null ? $error : $message;
			if($this->options("errors")["throw"] == true) throw new Exception($message);
		}
		
		// function session(). Returns / Sets session data. This should only be used by the OAuth2 and OAuthRequest classes. Fails silently if sessions are disabled.
		public function session($name) {
			$params = func_get_args();
			if($this->options("session_prefix") == false) return null; // Sessions are diabled.
			elseif(isset($params[1])) {
				$value = $params[1];
				$_SESSION[$this->options("session_prefix") . $name] = $value; // Set
			} elseif(isset($_SESSION[$this->options("session_prefix") . $name])) return $_SESSION[$this->options("session_prefix") . $name];
			else return null; // Get
		}
		
		// function sessionDelete(). Deletes session data. This should only be used by the OAuth2 and OAuthRequest classes. Fails silently if sessions are disabled.
		public function sessionDelete($name) {
			if($this->options("session_prefix") == false) return null; // Sessions are diabled.
			elseif(isset($_SESSION[$this->options("session_prefix") . $name])) unset($_SESSION[$this->options("session_prefix") . $name]); // Delete
		}
	}
	
