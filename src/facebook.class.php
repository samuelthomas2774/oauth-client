<?php
	/* class OAuthFacebook
	 * /src/facebook.class.php
	 */
	if(!class_exists("OAuth2")) require_once __DIR__ . '/oauth.class.php';
	
	class OAuthFacebook extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		protected $options = Array(
			"session_prefix"		=> "facebook_",
			"dialog"				=> Array("base_url" => "https://www.facebook.com/v2.3/dialog/oauth", "scope_separator" => ","),
			"api"					=> Array("base_url" => "https://graph.facebook.com/v2.3"),
			"requests"				=> Array("/oauth/token" => "/oauth/access_token", "/oauth/token/debug" => "/debug_token")
		);
		
		// function __construct(). Creates a new OAuth2 object.
		public function __construct($client_id, $client_secret, $options = Array()) {
			parent::__construct($client_id, $client_secret, $options);
			if($this->options([ "api", "version" ]) != null) {
				$this->setVersion($this->options([ "api", "version" ]));
				unset($this->options->api->version);
			}
		}
		
		// function setVersion(). Sets the Graph API Version.
		public function setVersion($version = 2.3) {
			// Check if version is a numeric.
			if(!is_numeric($version)) throw new Exception(__METHOD__ . "(): \$signed_request must be a string.");
			else $version = (float)$version;
			
			switch($version) {
				default: case 2.3:
					$this->options([ "dialog", "base_url" ], "https://www.facebook.com/v2.3/dialog/oauth");
					$this->options([ "dialog", "scope_separator" ], ",");
					$this->options([ "api", "base_url" ], "https://graph.facebook.com/v2.3");
					$this->options([ "requests", "/oauth/token" ], "/oauth/access_token");
					$this->options([ "requests", "/oauth/token:response" ], "json");
					$this->options([ "requests", "/oauth/token/debug" ], "/debug_token");
					break;
				default: case 2.2:
					$this->options([ "dialog", "base_url" ], "https://www.facebook.com/v2.2/dialog/oauth");
					$this->options([ "dialog", "scope_separator" ], ",");
					$this->options([ "api", "base_url" ], "https://graph.facebook.com/v2.2");
					$this->options([ "requests", "/oauth/token" ], "/oauth/access_token");
					$this->options([ "requests", "/oauth/token:response" ], "query");
					$this->options([ "requests", "/oauth/token/debug" ], "/debug_token");
					break;
				default: case 2.1:
					$this->options([ "dialog", "base_url" ], "https://www.facebook.com/v2.1/dialog/oauth");
					$this->options([ "dialog", "scope_separator" ], ",");
					$this->options([ "api", "base_url" ], "https://graph.facebook.com/v2.1");
					$this->options([ "requests", "/oauth/token" ], "/oauth/access_token");
					$this->options([ "requests", "/oauth/token:response" ], "query");
					$this->options([ "requests", "/oauth/token/debug" ], "/debug_token");
					break;
				default: case 2:
					$this->options([ "dialog", "base_url" ], "https://www.facebook.com/v2.0/dialog/oauth");
					$this->options([ "dialog", "scope_separator" ], ",");
					$this->options([ "api", "base_url" ], "https://graph.facebook.com/v2.0");
					$this->options([ "requests", "/oauth/token" ], "/oauth/access_token");
					$this->options([ "requests", "/oauth/token:response" ], "query");
					$this->options([ "requests", "/oauth/token/debug" ], "/debug_token");
					break;
				default: case 1:
					$this->options([ "dialog", "base_url" ], "https://www.facebook.com/v1.0/dialog/oauth");
					$this->options([ "dialog", "scope_separator" ], ",");
					$this->options([ "api", "base_url" ], "https://graph.facebook.com/v1.0");
					$this->options([ "requests", "/oauth/token" ], "/oauth/access_token");
					$this->options([ "requests", "/oauth/token:response" ], "query");
					$this->options([ "requests", "/oauth/token/debug" ], "/debug_token");
					break;
			}
		}
		
		// function parseSignedRequest(). Parses a signed request.
		public function parseSignedRequest($signed_request = null) {
			// Check if code is a string or null.
			if(is_string($signed_request)) $signed_request = trim($signed_request);
			elseif(($signed_request == null) && isset($_POST["signed_request"])) $signed_request = trim($_POST["signed_request"]);
			else throw new Exception(__METHOD__ . "(): \$signed_request must be a string.");
			
			list($encoded_sig, $payload) = explode(".", $signed_request, 2);
			
			// Decode the data.
			$sig = base64_decode(strtr($encoded_sig, "-_", "+/"));
			$data = json_decode(base64_decode(strtr($payload, "-_", "+/")), false);
			
			// Confirm the signature.
			$expected_sig = hash_hmac("sha256", $payload, $this->client()->secret, $raw = true);
			if($sig !== $expected_sig)
				throw new Exception(__METHOD__ . "(): Invalid signature. Make sure you have entered the client_secret correctly.");
			
			return $data;
		}
		
		// function validateAccessToken(). Checks an access token is valid.
		public function validateAccessToken($access_token = null) {
			// Check if access_token is string.
			if(!is_string($access_token)) $access_token = $this->token;
			
			// Example request: GET /oauth/token/debug?access_token={access_token}
			$request = $this->api("GET", $this->options("requests")["/oauth/token/debug"], Array(
				"access_token" => $this->client()->id . "|" . $this->client()->secret,
				"input_token" => $access_token
			));
			
			try { $request->execute(); parse_str($request->response(), $response); }
			catch(Exception $e) { return false; }
			if(isset($response->error)) return false;
			
			if($response["expires_in"] <= 0) return false;
			return true;
		}
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile($fields = Array()) {
			// Check if fields is an array.
			if(!is_array($fields)) $fields = Array();
			
			$request = $this->api("GET", "/me", Array("fields" => implode(",", $fields)));
			
			$request->execute();
			$user = new stdClass();
			$user->response = $request->responseObject();
			$user->id = isset($user->response->id) ? $user->response->id : null;
			$user->username = isset($user->response->username) ? $user->response->username : $user->response->id;
			$user->name = isset($user->response->name) ? $user->response->name : null;
			$user->email = isset($user->response->email) ? $user->response->email : null;
			return $user;
		}
		
		// function profilePicture(). Fetches the current user's profile.
		public function profilePicture($width = 50, $height = 50) {
			// Check if width and height are integers.
			if(!is_int($width) && !is_numeric($width)) $width = 50;
			if(!is_int($height) && !is_numeric($height)) $height = 50;
			
			$request = $this->api("GET", "/me", Array("fields" => "id,picture.width({$width}).height({$height})"));
			
			$request->execute();
			$response = $request->responseObject();
			$picture = $response->picture->data;
			
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
		
		// function permissions(). Fetches the permissions and returns them in an array.
		public function permissions($rearrange = true) {
			$request = $this->api("GET", "/me/permissions");
			
			$request->execute();
			$response = $request->responseObject();
			
			if($rearrange == false) {
				return $response;
			} else {
				$permissions = new stdClass();
				foreach($response->data as $p) {
					$status = $p->status;
					if($status == "granted") $granted = true; else $granted = false;
					$permissions->{$p->permission} = new stdClass(); // Array("granted" => $granted, "status" => $p->status);
					$permissions->{$p->permission}->granted = $granted;
					$permissions->{$p->permission}->status = $p->status;
				}
				
				return $permissions;
			}
		}
		
		// function permission(). Checks if the permission has been granted. Returns true if true, false if false.
		public function permission($permission) {
			$permissions = $this->permissions();
			
			if(isset($permissions->{$permission}) && ($permissions->{$permission}->granted == true)) {
				return true;
			} else {
				return false;
			}
		}
		
		// function ids(). Fetches the user ids for other apps the user has authorised and are linked to the same business.
		public function ids($rearrange = true) {
			$request = $this->api("GET", "/me/ids_for_business");
			
			$request->execute();
			$response = $request->responseObject();
			
			if($rearrange == false) {
				return $response;
			} else {
				$ids = new stdClass();
				foreach($response->data as $id) {
					$ids->{$id->app->id} = new stdClass(); // Array("app_name" => $id->app->name, "app_namespace" => $id->app->namespace, "app_id" => $id->app->id, "user_id" => $id->id);
					$ids->{$id->app->id}->app_name = $id->app->name;
					$ids->{$id->app->id}->app_namespace = $id->app->namespace;
					$ids->{$id->app->id}->app_id = $id->app->id;
					$ids->{$id->app->id}->user_id = $id->id;
				}
				
				return $ids;
			}
		}
		
		// function deauth(). De-authorises the application, or removes one permission. Once this is called, the user will have to authorise the application again using the Facebook Login Dialog.
		public function deauth($permission = null) {
			$request = $this->api("DELETE", "/me/permissions" . (is_string($permission) ? "/" . $permission : ""));
			
			$request->execute();
			$response = $request->responseObject();
			
			if($response->success == true) return true;
			else return false;
		}
		
		// function pages(). Fetches a list of all the pages the user manages. Requires the manage_pages permission.
		public function pages($rearrange = true) {
			$permissions = $this->permissions(); if(!isset($permissions->manage_pages) || ($permissions->manage_pages->status == "declined"))
				throw new Exception(__METHOD__ . "(): User has declined the manage_pages permission.");
			
			$request = $this->api("GET", "/me/accounts");
			
			$request->execute();
			$response = $request->responseObject();
			
			if($rearrange == false) {
				return $response;
			} else {
				$pages = new stdClass();
				foreach($response->data as $page) {
					$pages->{$page->id} = new stdClass();
					$pages->{$page->id}->id = $page->id;
					$pages->{$page->id}->name = $page->name;
					$pages->{$page->id}->access_token = $page->access_token;
					$pages->{$page->id}->permissions = $page->perms;
					$pages->{$page->id}->category = $page->category;
					$pages->{$page->id}->category_list = $page->category_list;
				}
				
				return $pages;
			}
		}
		
		// function post(). Posts something to the user's timeline. Requires the publish_actions permission.
		public function post($post2 = Array(), $returnid = false) {
			$permissions = $this->permissions(); if(!isset($permissions->publish_actions) || ($permissions->publish_actions->status == "declined"))
				throw new Exception(__METHOD__ . "(): User has declined the publish_actions permission.");
			
			$post = Array();
			if(isset($post2["message"])) $post["message"] = $post2["message"];
			if(isset($post2["link"])) $post["link"] = $post2["link"];
			if(isset($post2["place"])) $post["place"] = $post2["place"];
			if(isset($post2["place"]) && isset($post2["tags"])) $post["tags"] = $post2["tags"];
			
			$request = $this->api("POST", "/me/feed", $post);
			
			$request->execute();
			$response = $request->responseObject();
			
			if(isset($response->id)) {
				if($returnid == true) return $response->id;
				else return true;
			} else {
				return false;
			}
		}
	}
	
