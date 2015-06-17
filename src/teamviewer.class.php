<?php
	/* class OAuthTeamViewer
	 * /src/teamviewer.class.php
	 */
	if(!class_exists("OAuth2")) require_once __DIR__ . '/oauth.class.php';
	
	class OAuthTeamViewer extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		public $options = Array(
			"session_prefix"		=> "teamviewer_",
			"dialog"				=> Array("base_url" => "https://webapi.teamviewer.com/api/v1/oauth2/authorize", "scope_separator" => ","),
			"api"					=> Array("base_url" => "https://webapi.teamviewer.com/api/v1", "token_auth" => 2),
			"requests"				=> Array("/oauth/token" => "/oauth2/token", "/oauth/token/debug" => "/ping")
		);
		
		// function api(). Makes a new request to the server's API.
		public function api($method, $url, $params = Array(), $headers = Array(), $auth = false) {
			//if(!isset($params["api_key"])) $params["api_key"] = $this->client()->id;
			//if(!isset($params["api_secret"])) $params["api_secret"] = $this->client->secret;
			return parent::api($method, $url, $params, $headers, $auth);
		}
		
		// function validateAccessToken(). Validates an access token.
		public function validateAccessToken($access_token = null) {
			// Check if access_token is string.
			if(!is_string($access_token)) $access_token = $this->accessToken();
			
			// Example request: GET /oauth/token/debug?access_token={access_token}
			$request = $this->api("GET", $this->options([ "requests", "/oauth/token/debug" ]), Array(
				"access_token"			=> $access_token
			));
			
			try { $request->execute(); $response = $request->responseObject(); }
			catch(Exception $e) { return false; }
			if(isset($response->error)) return false;
			
			if($response->token_valid <= 0) return false;
			return true;
		}
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api("GET", "/account");
			
			$request->execute();
			$response = $request->responseObject();
			$response->id = $response->userid;
			return $response;
		}
	}
	