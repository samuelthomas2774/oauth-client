<?php
	/* class OAuthReddit
	 * /src/reddit.class.php
	 */
	if(!class_exists("OAuth2")) require_once __DIR__ . '/oauth.class.php';
	
	class OAuthAmazon extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		protected $options = Array(
			"session_prefix"		=> "reddit_",
			"button_colour"			=> "#ff5700",
			"dialog"				=> Array("base_url" => "https://www.reddit.com/login"),
			"api"					=> Array("base_url" => "https://www.reddit.com/api/v1/me", "token_auth" => 2),
			"requests"				=> Array("/oauth/token" => "/auth/o2/token", "/oauth/token/debug" => "/auth/o2/tokeninfo")
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api(OAuth2::GET, "/user/profile");
			
			$request->execute();
			$response = $request->responseObject();
			$response->id = $response->user_id;
			return $response;
		}
	}
