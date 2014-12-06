<?php
	/* class OAuthMicrosoft
	 * /src/microsoft.class.php
	 */
	require_once 'oauth.class.php';
	
	class OAuthMicrosoft extends OAuth {
		// Options. These shouldn't be modified here, but using the OAuth::options() function.
		public $options = Array(
			"session_prefix"		=> "microsoft_",
			"dialog"				=> Array("base_url" => "https://login.live.com/oauth20_authorize.srf", "scope_separator" => " "),
			"api"					=> Array("base_url" => "https://apis.live.net/v5.0", "token_auth" => true),
			"requests"				=> Array("/oauth/token" => "https://login.live.com/oauth20_token.srf", "/oauth/token/debug" => "https://login.live.com/oauth20_token.srf"),
			"errors"				=> Array("throw" => true)
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api("GET", "/me"));
			
			$request->execute();
			return $request->responseObject();
		}
	}
	
