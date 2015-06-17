<?php
	/* class OAuthGitHub
	 * /src/github.class.php
	 */
	require_once 'oauth.class.php';
	
	class OAuthGitHub extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		public $options = Array(
			"session_prefix"		=> "github_",
			"dialog"				=> Array("base_url" => "https://github.com/login/oauth/authorize", "scope_separator" => ","),
			"api"					=> Array("base_url" => "https://api.github.com", "token_auth" => false),
			"requests"				=> Array("/oauth/token" => "https://github.com/login/oauth/access_token", "/oauth/token:response" => "query", "/oauth/token/debug" => "https://github.com/login/oauth/access_token")
		);
		
		// function api(). Modify the API request before the user gets it.
		public function api($method, $url, $params = Array(), $headers = Array(), $auth = false) {
			if(!isset($params["access_token"]) && !isset($headers["Authorization"]) && ($this->accessToken() != null))
				$headers["Authorization"] = "token " . $this->accessToken();
			
			// Everything here is done by the OAuthRequest class.
			return parent::api($method, $url, $params, $headers, $auth);
		}
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api("GET", "/user");
			
			$request->execute();
			return $request->responseObject();
		}
	}
	