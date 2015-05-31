<?php
	/* class OAuthGoogle
	 * /src/google.class.php
	 */
	require_once 'oauth.class.php';
	
	class OAuthGoogle extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		public $options = Array(
			"session_prefix"		=> "google_",
			"dialog"				=> Array("base_url" => "https://accounts.google.com/o/oauth2/auth", "scope_separator" => " "),
			"api"					=> Array("base_url" => "https://www.googleapis.com/oauth2/v2", "token_auth" => true),
			"requests"				=> Array("/oauth/token" => "https://accounts.google.com/o/oauth2/token", "/oauth/token/debug" => "https://accounts.google.com/o/oauth2/token"),
			"errors"				=> Array("throw" => true)
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api("GET", "/userinfo");
			
			$request->execute();
			return $request->responseObject();
		}
	}
	
