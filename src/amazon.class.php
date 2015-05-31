<?php
	/* class OAuthAmazon
	 * /src/amazon.class.php
	 */
	require_once 'oauth.class.php';
	
	class OAuthAmazon extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		public $options = Array(
			"session_prefix"		=> "amazon_",
			"dialog"				=> Array("base_url" => "https://www.amazon.com/ap/oa", "scope_separator" => " "),
			"api"					=> Array("base_url" => "https://api.amazon.com", "token_auth" => 2, "headers" => Array("User-Agent" => "OAuth 2.0 Client https://github.com/samuelthomas2774/oauth-client"), "callback" => null),
			"requests"				=> Array("/oauth/token" => "/auth/o2/token", "/oauth/token:response" => "json", "/oauth/token/debug" => "/auth/o2/tokeninfo"),
			"errors"				=> Array("throw" => true)
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api("GET", "/user/profile");
			
			$request->execute();
			$response = $request->responseObject();
			$response->id = $response->user_id;
			return $response;
		}
	}
	
