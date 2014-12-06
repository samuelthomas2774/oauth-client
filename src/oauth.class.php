<?php
	/* class OAuth
	 * /src/oauth.class.php
	 */
	require_once 'oauthrequest.class.php';
	
	class OAuth {
		// Array $app. An array of information about the application.
		private $app = Array("id" => null, "secret" => null);
		
		// String $token. The current access token.
		private $token = null;
		
		// Options. These shouldn't be modified here, but using the OAuth::options() function.
		public $options = Array(
			"session_prefix"		=> "oauth_",
			// Login Dialog. Set a few important variables for using the Login Dialog.
			"dialog"				=> Array("base_url" => "https://www.facebook.com/dialog/oauth", "scope_separator" => ","),
			// API. Set a few important variables for using the API. // token_auth: true|1 = access_token parameter |default, 2 = Authorization header, false = Do not automatically send an access token.
			"api"					=> Array("base_url" => "https://graph.facebook.com/v2.1", "token_auth" => true),
			// Default requests. Sets a few important variables for the requests this class makes.
			"requests"				=> Array("/oauth/token" => "/oauth/token", "/oauth/token/debug" => "/oauth/token/debug"),
			// Errors. Sets how and when this class triggers errors. Note that invalid parameter exceptions are thrown even if throw is set to false here.
			"errors"				=> Array("throw" => true)
		);
		
		// function __construct(). Creates a new OAuth object.
		public function __construct($app_id, $app_secret, $options = Array()) {
			// Store App ID in OAuth::app["id"].
			if(!is_int($app_id) && !is_string($app_id)) throw new Exception(__CLASS__ . "::" . __METHOD__ . "(): \$app_id must be a string or an integer.");
			else $this->app["id"] = (string)$app_id;
			
			// Store App Secret in OAuth::app["secret"].
			if(!is_string($app_secret)) throw new Exception(__CLASS__ . "::" . __METHOD__ . "(): \$app_secret must be a string.");
			else $this->app["secret"] = $app_secret;
			
			// Save options.
			if(!is_array($options)) throw new Exception(__CLASS__ . "::" . __METHOD__ . "(): \$options must be an array.");
			else foreach($options as $key => $value) {
				$this->options($key, $value);
			}
			
			// Try to restore the access token from the session.
			if($this->session("token") != null) $this->token = $this->session("token");
		}
		
		// function api(). Makes a new request to the server's API.
		public function api($method, $url, $params = Array(), $headers = Array(), $auth = false) {
			// Everything here is done by the OAuthRequest class.
			return new OAuthRequest($this, $method, $url, $params, $headers, $auth);
		}
		
		// function getAccessTokenFromCode(). Exchanges the code for an access token.
		public function getAccessTokenFromCode($redirect_url, $code = null, $state = true) {
			// Check if redirect_url is a url. The redirect_url should be exactly the same as the redirect_url used in the login dialog. (So really, this should just be the same as the current url.)
			if(!filter_var($redirect_url, FILTER_VALIDATE_URL)) throw new Exception(__CLASS__ . "::" . __METHOD__ . "(): \$redirect_url must be a valid url.");
			
			// Check if code is a string or null.
			if(is_string($code)) $code = trim($code);
			elseif(($code == null) && isset($_GET["code"])) $code = trim($_GET["code"]);
			else throw new Exception(__CLASS__ . "::" . __METHOD__ . "(): \$code must be a string.");
			
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
			
			// Example request: GET /oauth/token?client_id={app_id}&client_secret={app_secret}&redirect_uri={redirect_url}&code={code}
			$request = $this->api("POST", $this->options("requests")["/oauth/token"], Array(
				"client_id"				=> $this->app["id"],
				"client_secret"			=> $this->app["secret"],
				"redirect_uri"			=> $redirect_url,
				"code"					=> $code,
				"grant_type"			=> "authorization_code"
			), null, true);
			
			$request->execute();
			//exit(print_r($request, true));
			$response = $request->responseObject();
			$this->accessToken($response->access_token);
		}
		
		// function validateAccessToken(). Validates an access token.
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
		public function loginURL($redirect_url, $permissions = Array(), $rerequest = false) {
			// Check if redirect_url is a url. The redirect_url should go to a PHP script on the same domain that runs OAuth::getAccessTokenFromCode().
			if(!filter_var($redirect_url, FILTER_VALIDATE_URL)) throw new Exception(__CLASS__ . "::" . __METHOD__ . "(): \$redirect_url must be a valid url.");
			
			// Check if permissions is an array.
			if(!is_array($permissions)) $permissions = Array();
			
			// Example Login Dialog URL to request a user's email address and friends who also use the application: https://oauth.mydatastore.com/dialog/oauth?client_id={app_id}&redirect_uri={redirect_url}&response_type=code&scope=email,user_friends
			
			// Generate a unique state parameter and store it in the session.
			$state = hash("sha1", time() . uniqid(mt_rand(), true));
			$this->session("state", $state);
			
			$url_params = Array(
				"client_id"				=> $this->app["id"],
				"redirect_uri"			=> $redirect_url,
				"response_type"			=> "code",
				"scope"					=> implode(",", $permissions),
				"state"					=> $state
			);
			
			if($rerequest == true) $url_params["auth_type"] = "rerequest";
			
			$url = $this->options("dialog")["base_url"] . "?" . http_build_query($url_params);
			return $url;
		}
		
		// function loginButton(). Returns the URL for the login dialog.
		public function loginButton($button_text, $redirect_url, $permissions = Array(), $rerequest = false, $colour = "rgb(47,71,122)") {
			// Check if button_text is a string.
			if(!is_string($button_text)) throw new Exception(__CLASS__ . "::" . __METHOD__ . "(): \$button_text must be a string.");
			
			// Check if colour is a valid hex, rgb(a) or hsl(a) colour.
			if(!preg_match("/(\#([0-9ABCDEF][0-9ABCDEF][0-9ABCDEF]([0-9ABCDEF][0-9ABCDEF][0-9ABCDEF])?)|rgb\(([0-9]*), ?([0-9]*), ?([0-9]*)\)|rgba\(([0-9]*), ?([0-9]*), ?([0-9]*), ?([0-9]*)\)|rgb\(([0-9]*), ?([0-9]*), ?([0-9]*)\)|rgba\(([0-9]*), ?([0-9]*), ?([0-9]*), ?([0-9]*)\)|black|gray|white|red|green|blue|transparent)/i", $colour)) throw new Exception(__CLASS__ . "::" . __METHOD__ . "(): \$colour must be a string.");
			
			// Get a Login Dialog URL using the OAuth::loginURL() function.
			$url = $this->loginURL($redirect_url, $permissions, $rerequest);
			
			// Build the html tag.
			$button = "<a href=\"";
			$button .= $url;
			$button .= "\" style=\"background-color:{$colour};display:block;min-width:80px;width:calc(100%-20px);padding:10px;text-align:center;color:white;font-family:arial;text-decoration:none;\">";
			$button .= htmlentities($button_text);
			$button .= "</a>";
			
			return $button;
		}
		
		// function loginRedirect(). Redirects to the login dialog.
		public function loginRedirect($redirect_url, $permissions = Array(), $rerequest = false) {
			// Get a Login Dialog URL using the OAuth::loginURL() function.
			$url = $this->loginURL($redirect_url, $permissions, $rerequest);
			
			// Redirect to the Login Dialog.
			header("Location: {$url}", true, 303);
			exit();
		}
		
		// function accessToken(). Returns / sets the current access token.
		public function accessToken($token = null) {
			if(is_string($token)) {
				$this->token = $token;
				$this->session("token", $token);
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
			} else return $this->options[$name];
		}
		
		// function app(). Returns the app id and secret.
		public function app() {
			return $this->app;
		}
		
		// -- FOR OAuth AND OAuthRequest USE ONLY -- //
		// function triggerError(). Triggers an error. This should only be used by the OAuth and OAuthRequest classes.
		public function triggerError($message, $error = null) {
			$this->error = $error != null ? $error : $message;
			if($this->options("errors")["throw"] == true) throw new Exception($message);
		}
		
		// function session(). Returns / Sets session data. This should only be used by the OAuth and OAuthRequest classes. Fails silently if sessions are disabled.
		public function session($name) {
			$params = func_get_args();
			if($this->options("session_prefix") == false) return null; // Sessions are diabled.
			elseif(isset($params[1])) {
				$value = $params[1];
				$_SESSION[$this->options("session_prefix") . $name] = $value; // Set
			} elseif(isset($_SESSION[$this->options("session_prefix") . $name])) return $_SESSION[$this->options("session_prefix") . $name];
			else return null; // Get
		}
		
		// function sessionDelete(). Returns / Sets session data. This should only be used by the OAuth and OAuthRequest classes. Fails silently if sessions are disabled.
		public function sessionDelete($name) {
			if($this->options("session_prefix") == false) return null; // Sessions are diabled.
			elseif(isset($_SESSION[$this->options("session_prefix") . $name])) unset($_SESSION[$this->options("session_prefix") . $name]); // Delete
		}
	}
	
