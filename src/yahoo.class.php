<?php
	/* class OAuthYahoo
	 * /src/yahoo.class.php
	 */
	require_once 'oauth.class.php';
	
	class OAuthYahoo extends OAuth {
		// Options. These shouldn't be modified here, but using the OAuth::options() function.
		public $options = Array(
			"session_prefix"		=> "yahoo_",
			"dialog"				=> Array("base_url" => "https://api.login.yahoo.com/oauth2/request_auth"),
			"api"					=> Array("base_url" => "https://social.yahooapis.com/v1", "token_auth" => true),
			"requests"				=> Array("/oauth/token" => "https://api.login.yahoo.com/oauth2/get_token", "/oauth/token/debug" => "https://api.login.yahoo.com/oauth2/get_token"),
			"errors"				=> Array("throw" => true)
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api("GET", "/user/me/profile"));
			
			$request->execute();
			return $request->responseObject();
		}
	}
	
