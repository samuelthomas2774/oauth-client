<?php
	/* class OAuthEventbrite
	 * /src/eventbrite.class.php
	 */
	if(!class_exists("OAuth2")) require_once __DIR__ . '/oauth.class.php';
	
	class OAuthEventbrite extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		protected $options = Array(
			"session_prefix"		=> "eventbrite_",
			"dialog"				=> Array("base_url" => "https://www.eventbrite.com/oauth/authorize"),
			"api"					=> Array("base_url" => "https://www.eventbriteapi.com/v3", "token_auth" => 2),
			"requests"				=> Array("/oauth/token" => "https://www.eventbrite.com/oauth/token", "/oauth/token/debug" => "https://www.eventbrite.com/oauth/token")
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api("GET", "/users/me/");
			
			$request->execute();
			return $request->responseObject();
		}
	}
	