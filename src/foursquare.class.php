<?php
	/* class OAuthFoursquare
	 * /src/foursquare.class.php
	 */
	if(!class_exists("OAuth2")) require_once __DIR__ . '/oauth.class.php';
	
	class OAuthFoursquare extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		protected $options = Array(
			"session_prefix"		=> "foursquare_",
			"dialog"				=> Array("base_url" => "https://foursquare.com/oauth2/authorize"),
			"api"					=> Array("base_url" => "https://api.foursquare.com/v2", "token_auth" => false),
			"requests"				=> Array("/oauth/token" => "https://foursquare.com/oauth2/access_token", "/oauth/token/debug" => "https://foursquare.com/oauth2/access_token")
		);
		
		// function api(). Makes a new request to the server's API.
		public function api($method, $url, $params = Array(), $headers = Array(), $auth = false) {
			if(!isset($params["oauth_token"])) $params["oauth_token"] = $this->accessToken();
			if(!isset($params["v"])) $params["v"] = "20140806";
			if(!isset($params["m"])) $params["m"] = "foursquare";
			return parent::api($method, $url, $params, $headers, $auth);
		}
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api("GET", "/users/self");
			
			$request->execute();
			return $request->responseObject()->response->user;
		}
	}
	