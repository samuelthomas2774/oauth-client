<?php
	/* class OAuthGoogle
	 * /src/google.class.php
	 */
	if(!class_exists("OAuth2")) require_once __DIR__ . '/oauth.class.php';
	
	class OAuthGoogle extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		protected $options = Array(
			"session_prefix"		=> "google_",
			"button_colour"			=> "#dd4b39",
			"dialog"				=> Array("base_url" => "https://accounts.google.com/o/oauth2/auth"),
			"api"					=> Array("base_url" => "https://www.googleapis.com/plus/v1"),
			"requests"				=> Array("/oauth/token" => "https://accounts.google.com/o/oauth2/token", "/oauth/token/debug" => "https://accounts.google.com/o/oauth2/token")
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api(OAuth2::GET, "https://www.googleapis.com/oauth2/v2/userinfo");
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			$user = new stdClass();
			$user->id = isset($response->id) ? $response->id : null;
			$user->username = is_string($user->id) || is_numeric($user->id) ? (string)$user->id : null;
			$user->name = isset($response->name) ? $response->name : $user->username;
			$user->email = isset($response->email) ? $response->email : null;
			$user->response = $response;
			return $user;
		}
		
		// function profilePicture(). Fetches the current user's profile.
		public function profilePicture($size = 50) {
			// Check if width and height are integers.
			if(!is_numeric($size)) $size = 50;
			
			$request = $this->api(OAuth2::GET, "https://www.googleapis.com/oauth2/v2/userinfo");
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			if(!isset($response->picture)) return false;
			$picture = new stdClass();
			$picture->url = $response->picture . "?sz={$size}";
			
			// Build an <img /> tag.
			$picture->tag = "<img src=\"";
			$picture->tag .= htmlentities($picture->url);
			$picture->tag .= "\" style=\"width:{$size}px;height:{$size}px;\" />";
			
			return $picture;
		}
	}
	