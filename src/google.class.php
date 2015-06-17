<?php
	/* class OAuthGoogle
	 * /src/google.class.php
	 */
	require_once 'oauth.class.php';
	
	class OAuthGoogle extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		public $options = Array(
			"session_prefix"		=> "google_",
			"dialog"				=> Array("base_url" => "https://accounts.google.com/o/oauth2/auth"),
			"api"					=> Array("base_url" => "https://www.googleapis.com/oauth2/v2"),
			"requests"				=> Array("/oauth/token" => "https://accounts.google.com/o/oauth2/token", "/oauth/token/debug" => "https://accounts.google.com/o/oauth2/token")
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api("GET", "/userinfo");
			
			$request->execute();
			$user = new stdClass();
			$user->response = $request->responseObject();
			$user->id = $user->response->id;
			$user->username = (string)$user->response->id;
			$user->name = $user->response->name;
			$user->email = isset($user->response->email) ? $user->response->email : null;
			return $user;
		}
		
		// function profilePicture(). Fetches the current user's profile.
		public function profilePicture($size = 50) {
			// Check if width and height are integers.
			if(!is_int($size) && !is_numeric($size)) $size = 50;
			
			$request = $this->api("GET", "/me", Array("sz" => $size));
			
			$request->execute();
			$response = $request->responseObject();
			$picture = $response->image;
			
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
	