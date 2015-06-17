<?php
	/* class OAuthDisqus
	 * /src/disqus.class.php
	 */
	if(!class_exists("OAuth2")) require_once __DIR__ . '/oauth.class.php';
	
	class OAuthDisqus extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		protected $options = Array(
			"session_prefix"		=> "disqus_",
			"dialog"				=> Array("base_url" => "https://disqus.com/api/oauth/2.0/authorize/", "scope_separator" => ","),
			"api"					=> Array("base_url" => "https://disqus.com/api/3.0", "token_auth" => 2),
			"requests"				=> Array("/oauth/token" => "https://disqus.com/api/oauth/2.0/access_token/", "/oauth/token/debug" => "https://disqus.com/api/oauth/2.0/access_token/")
		);
		
		// function api(). Makes a new request to the server's API.
		public function api($method, $url, $params = Array(), $headers = Array(), $auth = false) {
			if(!isset($params["api_key"])) $params["api_key"] = $this->client()->id;
			if(!isset($params["api_secret"])) $params["api_secret"] = $this->client()->secret;
			return parent::api($method, $url, $params, $headers, $auth);
		}
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api("GET", "/users/details.json");
			
			$request->execute();
			return $request->responseObject()->response;
		}
	}
	