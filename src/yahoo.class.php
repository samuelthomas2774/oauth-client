<?php
	/* class OAuthYahoo
	 * /src/yahoo.class.php
	 */
	if(!class_exists("OAuth2")) require_once __DIR__ . '/oauth.class.php';
	
	class OAuthYahoo extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		protected $options = Array(
			"session_prefix"		=> "yahoo_",
			"dialog"				=> Array("base_url" => "https://api.login.yahoo.com/oauth2/request_auth"),
			"api"					=> Array("base_url" => "https://social.yahooapis.com/v1"),
			"requests"				=> Array("/oauth/token" => "https://api.login.yahoo.com/oauth2/get_token", "/oauth/token/debug" => "https://api.login.yahoo.com/oauth2/get_token")
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api("GET", "/user/me/profile");
			
			$request->execute();
			$user = new stdClass();
			$user->response = $request->responseObject();
			$user->id = $user->response->profile->guid;
			$user->username = (string)$user->response->guid;
			$user->name = $user->response->displayname;
			$user->email = isset($user->response->emails->account) ? $user->response->emails->account : null;
			return $user;
		}
		
		// function profilePicture(). Fetches the current user's profile.
		public function profilePicture($width = 50, $height = 50) {
			// Check if width and height are integers.
			if(!is_int($width) && !is_numeric($width)) $width = 50;
			if(!is_int($height) && !is_numeric($height)) $height = 50;
			
			$request = $this->api("GET", "/user/me/profile/image/{$width}x{$height}");
			
			$request->execute();
			$response = $request->responseObject();
			$picture = new stdClass();
			$picture->url = $response->image->url;
			$picture->width = $response->image->width;
			$picture->height = $response->image->height;
			
			// Build an <img> tag.
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
	