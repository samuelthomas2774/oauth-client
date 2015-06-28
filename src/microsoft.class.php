<?php
	/* class OAuthMicrosoft
	 * /src/microsoft.class.php
	 */
	if(!class_exists("OAuth2")) require_once __DIR__ . '/oauth.class.php';
	
	class OAuthMicrosoft extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		protected $options = Array(
			"session_prefix"		=> "microsoft_",
			"button_colour"			=> "#f65314",
			"dialog"				=> Array("base_url" => "https://login.live.com/oauth20_authorize.srf"),
			"api"					=> Array("base_url" => "https://apis.live.net/v5.0"),
			"requests"				=> Array("/oauth/token" => "https://login.live.com/oauth20_token.srf", "/oauth/token/debug" => "https://login.live.com/oauth20_token.srf")
		);
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile() {
			$request = $this->api(OAuth2::GET, "/me");
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			$user = new stdClass();
			$user->id = isset($response->id) ? $response->id : null;
			$user->username = is_string($user->id) || is_numeric($user->id) ? (string)$user->id : null;
			$user->name = isset($response->name) ? $response->name : $user->username;
			$user->email = isset($user->response->emails->account) ? $user->response->emails->account : null;
			$user->response = $response;
			return $user;
		}
		
		// function profilePicture(). Fetches the current user's profile.
		public function profilePicture() {
			$request = $this->api(OAuth2::GET, "/me/picture");
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			if(!isset($response->location)) return false;
			$picture = new stdClass();
			$picture->url = $request->location;
			
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
	