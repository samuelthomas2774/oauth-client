<?php
	/* class OAuthYahoo
	 * /src/yahoo.class.php
	 */
	if(!class_exists("OAuth2")) require_once __DIR__ . '/oauth.class.php';
	
	class OAuthYahoo extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		protected $options = Array(
			"session_prefix"		=> "yahoo_",
			"button_colour"			=> "#400191",
			"dialog"				=> Array("base_url" => "https://api.login.yahoo.com/oauth2/request_auth"),
			"api"					=> Array("base_url" => "https://social.yahooapis.com/v1", "headers" => Array("Accept" => "application/json")),
			"requests"				=> Array("/oauth/token" => "https://api.login.yahoo.com/oauth2/get_token", "/oauth/token/debug" => "https://api.login.yahoo.com/oauth2/get_token")
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api(OAuth2::GET, "/user/me/profile");
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			$user = new stdClass();
			$user->id = isset($response->profile->guid) ? $response->profile->guid : null;
			$user->username = is_string($user->id) || is_numeric($user->id) ? $user->id : null;
			$user->name = isset($response->profile->nickname) ? $response->profile->nickname : $user->username;
			$user->email = isset($user->response->emails->account) ? $user->response->emails->account : null;
			$user->response = $request->responseObject();
			return $user;
		}
		
		// function profilePicture(). Fetches the current user's profile.
		public function profilePicture($size = 50) {
			// Check if width and height are integers.
			if(!is_numeric($size)) $size = 50;
			
			$request = $this->api(OAuth2::GET, "/user/me/profile/image/{$size}x{$size}");
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			if(!isset($response->image->url) || !isset($response->image->width) || !isset($response->image->height)) return false;
			$picture = new stdClass();
			$picture->url = $response->image->url;
			$picture->width = $response->image->width;
			$picture->height = $response->image->height;
			
			// Build an <img /> tag.
			$picture->tag = "<img src=\"";
			$picture->tag .= htmlentities($picture->url);
			$picture->tag .= "\" style=\"width:";
			$picture->tag .= htmlentities($picture->width);
			$picture->tag .= "px;height:";
			$picture->tag .= htmlentities($picture->height);
			$picture->tag .= "px;\" />";
			
			return $picture;
		}
	}
	