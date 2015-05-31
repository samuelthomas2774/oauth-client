<?php
	/* class OAuthST
	 * /src/st.class.php
	 */
	require_once 'oauth.class.php';
	
	class OAuthST extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		public $options = Array(
			"session_prefix"		=> "st_",
			"dialog"				=> Array("base_url" => "http://apis.samuelthomas.ml/auth", "scope_separator" => " "),
			"api"					=> Array("base_url" => "http://apis.samuelthomas.ml", "token_auth" => true),
			"requests"				=> Array("/oauth/token" => "/auth", "/oauth/token/debug" => "/auth/token"),
			"errors"				=> Array("throw" => true)
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			// Check if fields is an array.
			if(!is_array($fields)) $fields = Array();
			
			$request = $this->api("GET", "/user");
			
			$request->execute();
			return $request->responseObject();
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
	
