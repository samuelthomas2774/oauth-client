<?php
	/* class OAuthST
	 * /src/st.class.php
	 */
	if(!class_exists("OAuth2")) require_once __DIR__ . '/oauth.class.php';
	
	class OAuthST extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		protected $options = Array(
			"session_prefix"		=> "st_",
			"dialog"				=> Array("base_url" => "http://apis.samuelthomas.ml/auth"),
			"api"					=> Array("base_url" => "http://apis.samuelthomas.ml"),
			"requests"				=> Array("/oauth/token" => "/auth", "/oauth/token/debug" => "/auth/token")
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api("GET", "/user");
			$request->execute();
			
			$user = new stdClass();
			$user->response = $request->responseObject();
			$user->id = $user->response->id;
			$user->username = $user->response->username;
			$user->name = $user->response->displayname;
			$user->email = isset($user->response->email) ? $user->response->email : null;
			var_dump($user);
			return $user;
		}
		
		// function profilePicture(). Fetches the current user's profile picture.
		public function profilePicture($size = 50) {
			// Check if size is an integer.
			if(!is_int($size) && !is_numeric($size)) $size = 50;
			
			$request = $this->api("GET", "/user", Array("picture_size" => $size));
			
			$request->execute();
			$response = $request->responseObject();
			$picture = new stdClass();
			$picture->url = $response->picture;
			
			// Build an <img> tag.
			$picture->tag = "<img src=\"";
			$picture->tag .= htmlentities($picture->url);
			$picture->tag .= "\" style=\"width:";
			$picture->tag .= $size;
			$picture->tag .= "px;height:";
			$picture->tag .= $size;
			$picture->tag .= "px;\" />";
			
			return $picture;
		}
	}
	