<?php
	/* class OAuthLinkedin
	 * /src/linkedin.class.php
	 */
	if(!class_exists("OAuth2")) require_once __DIR__ . '/oauth.class.php';
	
	class OAuthLinkedin extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		protected $options = Array(
			"session_prefix"		=> "linkedin_",
			"button_colour"			=> "#0077b5",
			"dialog"				=> Array("base_url" => "https://www.linkedin.com/uas/oauth2/authorization"),
			"api"					=> Array("base_url" => "https://api.linkedin.com/v1", "token_auth" => 2, "headers" => Array("X-Li-Format" => "json")),
			"requests"				=> Array("/oauth/token" => "https://www.linkedin.com/uas/oauth2/accessToken", "/oauth/token/debug" => "https://www.linkedin.com/uas/oauth2/accessToken")
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api(OAuth2::GET, "/people/~");
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			$user = new stdClass();
			$user->id = isset($response->id) ? $response->id : null;
			$user->username = $user->id;
			$user->name = isset($response->firstName) ? $response->firstName . (isset($response->lastName) ? " " . $response->lastName : "") : $user->username;
			$user->email = isset($response->emailAddress) ? $response->emailAddress : null;
			$user->response = $response;
			return $user;
		}
		
		// function profilePicture(). Fetches the current user's profile.
		public function profilePicture($size = 50) {
			// Check if width and height are integers.
			if(!is_numeric($size)) $size = 50;
			
			$request = $this->api(OAuth2::GET, "/people/~:(id,pictureUrl)");
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			if(!isset($response->pictureUrl)) return false;
			$picture = new stdClass();
			$picture->url = $response->pictureUrl;
			
			// Build an <img /> tag.
			$picture->tag = "<img src=\"";
			$picture->tag .= htmlentities($picture->url);
			$picture->tag .= "\" style=\"width:{$size}px;height:{$size}px;\" />";
			
			return $picture;
		}
	}
	