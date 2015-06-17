<?php
	/* class OAuthWordPress
	 * /src/wordpress.class.php
	 */
	require_once 'oauth.class.php';
	
	class OAuthWordPress extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		public $options = Array(
			"session_prefix"		=> "wordpress_",
			"dialog"				=> Array("base_url" => "https://public-api.wordpress.com/oauth2/authorize"),
			"api"					=> Array("base_url" => "https://public-api.wordpress.com/rest/v1", "token_auth" => 2),
			"requests"				=> Array("/oauth/token" => "https://public-api.wordpress.com/oauth2/token", "/oauth/token/debug" => "https://public-api.wordpress.com/oauth2/token")
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api("GET", "/me");
			
			$request->execute();
			$response = $request->responseObject();
			$response->id = $response->ID;
			return $response;
		}
	}
	