<?php
	/* class OAuthGitHub
	 * /src/github.class.php
	 */
	if(!class_exists("OAuth2")) require_once __DIR__ . '/oauth.class.php';
	
	class OAuthGitHub extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		protected $options = Array(
			"session_prefix"		=> "github_",
			"button_colour"			=> "#999999",
			"dialog"				=> Array("base_url" => "https://github.com/login/oauth/authorize", "scope_separator" => ","),
			"api"					=> Array("base_url" => "https://api.github.com", "token_auth" => false),
			"requests"				=> Array("/oauth/token" => "https://github.com/login/oauth/access_token", "/oauth/token:response" => "query", "/oauth/token/debug" => "https://github.com/login/oauth/access_token")
		);
		
		// function api(). Modify the API request before the user gets it.
		public function api($method, $url, $params = Array(), $headers = Array(), $auth = false) {
			if(is_array($headers) && !isset($headers["Authorization"]) && is_string($this->accessToken()))
				$headers["Authorization"] = "token " . $this->accessToken();
			
			// Everything here is done by the OAuthRequest class.
			return parent::api($method, $url, $params, $headers, $auth);
		}
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api(OAuth2::GET, "/user");
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			$user = new stdClass();
			$user->id = isset($response->id) ? $response->id : null;
			$user->username = isset($response->login) ? $response->login : $user->id;
			$user->name = isset($response->name) ? $response->name : $user->username;
			$user->email = isset($response->email) ? $response->email : null;
			$user->response = $response;
			return $user;
		}
		
		// function profilePicture(). Fetches the current user's profile.
		public function profilePicture($size = 50) {
			// Check if width and height are integers.
			if(!is_numeric($size)) $size = 50;
			
			$request = $this->api(OAuth2::GET, "/user");
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			if(!isset($response->avatar_url)) return false;
			$picture = new stdClass();
			$picture->url = $response->avatar_url;
			
			// Build an <img /> tag.
			$picture->tag = "<img src=\"";
			$picture->tag .= htmlentities($picture->url);
			$picture->tag .= "\" style=\"width:{$size}px;height:{$size}px;\" />";
			
			return $picture;
		}
	}
	