<?php
	/* class OAuthLinkedin
	 * /src/linkedin.class.php
	 */
	require_once 'oauth.class.php';
	
	class OAuthLinkedin extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth::options() function.
		public $options = Array(
			"session_prefix"		=> "linkedin_",
			"dialog"				=> Array("base_url" => "https://www.linkedin.com/uas/oauth2/authorization"),
			"api"					=> Array("base_url" => "https://api.linkedin.com/v1", "token_auth" => 2, "headers" => Array("X-Li-Format" => "json")),
			"requests"				=> Array("/oauth/token" => "https://www.linkedin.com/uas/oauth2/accessToken", "/oauth/token/debug" => "https://www.linkedin.com/uas/oauth2/accessToken")
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api("GET", "/people/~");
			
			$request->execute();
			return $request->responseObject();
		}
	}
	