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
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			$user = new stdClass();
			$user->id = isset($response->id) ? $response->id : null;
			$user->username = isset($response->username) ? $response->username : $response->id;
			$user->name = isset($response->displayname) ? $response->displayname : $user->username;
			$user->email = isset($response->email) ? $response->email : null;
			$user->response = $response;
			return $user;
		}
		
		// function profilePicture(). Fetches the current user's profile picture.
		public function profilePicture($size = 50) {
			// Check if size is an integer.
			if(!is_int($size) && !is_numeric($size)) $size = 50;
			
			$request = $this->api(OAuth2::GET, "/user", Array("picture_size" => $size));
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			$picture = new stdClass();
			$picture->url = $response->picture;
			
			// Build an <img /> tag.
			$picture->tag = "<img src=\"";
			$picture->tag .= htmlentities($picture->url);
			$picture->tag .= "\" style=\"width:{$size}px;height:{$size}px;\" />";
			
			return $picture;
		}
		
		// --- Storage Objects --- //
		// function objects(). Fetches all storage objects.
		public function objects() {
			$request = $this->api(OAuth2::GET, "/user/me/storage");
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			return $response;
		}
		
		// function object(). Fetches a storage object.
		public function object($name) {
			// Check if name is a string, array or object.
			if(is_array($name) || is_object($name)) $name = implode("->", (array)$name);
			if(!is_string($name)) throw new Exception(__METHOD__ . "(): \$name must be a string.");
			
			$params = func_get_args();
			if(array_keys_exists(1, $params) && ($params[1] === null)) {
				// Delete
				$request = $this->api(OAuth2::DELETE, "/user/me/storage", Array("name" => $name));
				$request->execute();
				$response = $request->responseObject();
				if(isset($response->success) && ($response->success == true)) return true;
				else return false;
			} elseif(array_keys_exists(1, $params)) {
				// Set
				$request = $this->api(OAuth2::POST, "/user/me/storage", Array("name" => $name, "value" => json_encode($value)));
				$request->execute();
				$response = $request->responseObject();
				if(isset($response->success) && ($response->success == true)) return true;
				else return false;
			} else {
				// Get
				$request = $this->api(OAuth2::GET, "/user/me/storage", Array("name" => $name));
				$request->execute();
				$response = $request->responseObject();
				if(isset($request->error)) return false;
				
				$object = new stdClass();
				$object->name = isset($response->name) ? $response->name : $name;
				$object->value = isset($response->value) ? (is_object($value_decoded = json_decode($response->value)) ? $value_decoded : $response->value) : null;
				$object->date = isset($response->lastupdated) ? $response->lastupdated : null;
				$object->time = isset($response->lastupdated) ? strtotime($response->lastupdated) : null;
				$object->response = $response;
				return $object;
			}
		}
		
		// function deleteObjects(). DELETES ALL storage objects.
		public function deleteObjects() {
			$request = $this->api(OAuth2::DELETE, "/user/me/storage");
			$request->execute();
			$response = $request->responseObject();
			if(isset($response->success) && ($response->success == true)) return true;
			else return false;
		}
	}
	