<?php
	/* class OAuthSpotify
	 * /src/spotify.class.php
	 */
	if(!class_exists("OAuth2")) require_once __DIR__ . '/oauth.class.php';
	
	class OAuthSpotify extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		protected $options = Array(
			"session_prefix"		=> "spotify_",
			"dialog"				=> Array("base_url" => "https://accounts.spotify.com/authorize"),
			"api"					=> Array("base_url" => "https://api.spotify.com/v1", "token_auth" => 2, "callback" => "OAuthSpotify::apiCallback"),
			"requests"				=> Array("/oauth/token" => "https://accounts.spotify.com/api/token", "/oauth/token/debug" => "https://accounts.spotify.com/api/token")
		);
		
		// function apiCallback(). Callback for api requests.
		static function apiCallback($oauth, $request, $curl) {
			// Check for errors.
			$response = $request->responseObject();
			if(isset($response->error)) $oauth->triggerError($response->error->message . " (" . $response->error->status . ")", $response->error);
		}
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api("GET", "/me");
			
			$request->execute();
			return $request->responseObject();
		}
	}
	