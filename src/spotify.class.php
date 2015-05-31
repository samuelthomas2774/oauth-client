<?php
	/* class OAuthSpotify
	 * /src/spotify.class.php
	 */
	require_once 'oauth.class.php';
	
	class OAuthSpotify extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		public $options = Array(
			"session_prefix"		=> "spotify_",
			"dialog"				=> Array("base_url" => "https://accounts.spotify.com/authorize", "scope_separator" => " "),
			"api"					=> Array("base_url" => "https://api.spotify.com/v1", "token_auth" => 2, "headers" => Array(
				"User-Agent"			=> "OAuth 2.0 Client https://github.com/samuelthomas2774/oauth-client"
			), "callback" => "OAuthSpotify::apiCallback"),
			"requests"				=> Array("/oauth/token" => "https://accounts.spotify.com/api/token", "/oauth/token:response" => "json", "/oauth/token/debug" => "https://accounts.spotify.com/api/token"),
			"errors"				=> Array("throw" => true)
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
	
