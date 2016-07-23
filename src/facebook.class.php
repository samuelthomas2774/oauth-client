<?php
	/* class OAuthFacebook
	 * /src/facebook.class.php
	 */
	if(!class_exists("OAuth2")) require_once __DIR__ . '/oauth.class.php';
	
	class OAuthFacebook extends OAuth2 {
		// Options. These shouldn't be modified here, but using the OAuth2::options() function.
		protected $options = Array(
			"session_prefix"		=> "facebook_",
			"button_colour"			=> "rgb(47,71,122)",
			"dialog"				=> Array("base_url" => "https://www.facebook.com/v2.3/dialog/oauth", "scope_separator" => ","),
			"api"					=> Array("base_url" => "https://graph.facebook.com/v2.3", "callback" => "OAuthFacebook::apiCallback"),
			"requests"				=> Array("/oauth/token" => "/oauth/access_token", "/oauth/token/debug" => "/debug_token")
		);
		
		// function __construct(). Creates a new OAuth2 object.
		public function __construct($client_id, $client_secret, $options = Array()) {
			parent::__construct($client_id, $client_secret, $options);
			if(is_numeric($this->options([ "api", "version" ]))) {
				$this->setVersion((float)$this->options([ "api", "version" ]));
				unset($this->options->api->version);
			}
		}
		
		// function apiCallback(). Callback for api requests.
		static function apiCallback($oauth, $request, $curl) {
			// Check for errors.
			$response = $request->responseObject();
			if(is_object($response) && isset($response->error->message)) {
				$oauth->triggerError($response->error->message, $response);
			}
		}
		
		// function setVersion(). Sets the Graph API Version.
		public function setVersion($version = 2.3) {
			// Check if version is numeric.
			if(!is_numeric($version)) throw new Exception(__METHOD__ . "(): \$version must be a string.");
			else $version = (float)$version;
			
			$this->options([ "dialog", "base_url" ], "https://www.facebook.com/dialog/oauth");
			$this->options([ "dialog", "scope_separator" ], ",");
			$this->options([ "api", "base_url" ], "https://graph.facebook.com");
			$this->options([ "requests", "/oauth/token" ], "/oauth/access_token");
			$this->options([ "requests", "/oauth/token:response" ], "json");
			$this->options([ "requests", "/oauth/token/debug" ], "/debug_token");
			
			switch($version) {
				case 2.7:
					$this->options([ "dialog", "base_url" ], "https://www.facebook.com/v2.7/dialog/oauth");
					$this->options([ "api", "base_url" ], "https://graph.facebook.com/v2.7");
					break;
				default: case 2.6:
					$this->options([ "dialog", "base_url" ], "https://www.facebook.com/v2.6/dialog/oauth");
					$this->options([ "api", "base_url" ], "https://graph.facebook.com/v2.6");
					break;
				case 2.5:
					$this->options([ "dialog", "base_url" ], "https://www.facebook.com/v2.5/dialog/oauth");
					$this->options([ "api", "base_url" ], "https://graph.facebook.com/v2.5");
					break;
				case 2.4:
					$this->options([ "dialog", "base_url" ], "https://www.facebook.com/v2.4/dialog/oauth");
					$this->options([ "api", "base_url" ], "https://graph.facebook.com/v2.4");
					break;
				case 2.3:
					$this->options([ "dialog", "base_url" ], "https://www.facebook.com/v2.3/dialog/oauth");
					$this->options([ "api", "base_url" ], "https://graph.facebook.com/v2.3");
					break;
				case 2.2:
					$this->options([ "dialog", "base_url" ], "https://www.facebook.com/v2.2/dialog/oauth");
					$this->options([ "api", "base_url" ], "https://graph.facebook.com/v2.2");
					$this->options([ "requests", "/oauth/token:response" ], "query");
					break;
				case 2.1:
					$this->options([ "dialog", "base_url" ], "https://www.facebook.com/v2.1/dialog/oauth");
					$this->options([ "api", "base_url" ], "https://graph.facebook.com/v2.1");
					$this->options([ "requests", "/oauth/token:response" ], "query");
					break;
				case 2:
					$this->options([ "dialog", "base_url" ], "https://www.facebook.com/v2.0/dialog/oauth");
					$this->options([ "api", "base_url" ], "https://graph.facebook.com/v2.0");
					$this->options([ "requests", "/oauth/token:response" ], "query");
					break;
				case 1:
					$this->options([ "dialog", "base_url" ], "https://www.facebook.com/v1.0/dialog/oauth");
					$this->options([ "api", "base_url" ], "https://graph.facebook.com/v1.0");
					$this->options([ "requests", "/oauth/token:response" ], "query");
					break;
			}
		}
		
		// function parseSignedRequest(). Parses a signed request.
		public function parseSignedRequest($signed_request = null) {
			// Check if code is a string or null.
			if(is_string($signed_request)) $signed_request = trim($signed_request);
			elseif(($signed_request === null) && isset($_POST["signed_request"])) $signed_request = trim($_POST["signed_request"]);
			else throw new Exception(__METHOD__ . "(): \$signed_request must be a string.");
			
			list($encoded_sig, $payload) = explode(".", $signed_request, 2);
			
			// Decode the data.
			$sig = base64_decode(strtr($encoded_sig, "-_", "+/"));
			$data = json_decode(base64_decode(strtr($payload, "-_", "+/")), false);
			
			// Confirm the signature.
			$expected_sig = hash_hmac("sha256", $payload, $this->client()->secret, true);
			if($sig !== $expected_sig)
				throw new Exception(__METHOD__ . "(): Invalid signature. Make sure you have entered the client_secret correctly.");
			
			return $data;
		}
		
		// function validateAccessToken(). Checks an access token is valid.
		public function validateAccessToken($access_token = null) {
			// Check if access_token is string.
			if(!is_string($access_token)) $access_token = $this->token;
			
			// Example request: GET /oauth/token/debug?access_token={access_token}
			$request = $this->api(OAuth2::GET, $this->options([ "requests", "/oauth/token/debug" ], Array(
				"access_token" => $this->client()->id . "|" . $this->client()->secret,
				"input_token" => $access_token
			)));
			
			try {
				$request->execute();
				if($this->options([ "requests", "/oauth/token:response" ]) == "query") $response = $request->responseQueryString();
				elseif($this->options([ "requests", "/oauth/token:response" ]) == "xml") $response = $request->responseXMLObject();
				else $response = $request->responseObject();
			} catch(Exception $e) { return false; }
			if(isset($response->error)) return false;
			
			if($response->expires_in <= 0) return false;
			return true;
		}
		
		// function userProfile(). Fetches the current user's profile.
		public function userProfile($fields = Array()) {
			// Check if fields is an array.
			if(!is_array($fields)) $fields = Array();
			$fields = array_merge($fields, Array("id", "name", "email"));
			
			$request = $this->api(OAuth2::GET, "/me", Array("fields" => implode(",", $fields)));
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			$user = new stdClass();
			$user->id = isset($response->id) ? $response->id : null;
			$user->username = isset($response->username) ? $response->username : $user->id;
			$user->name = isset($response->name) ? $response->name : null;
			$user->email = isset($response->email) ? $response->email : null;
			$user->response = $response;
			return $user;
		}
		
		// function profilePicture(). Fetches the current user's profile.
		public function profilePicture($size = 50) {
			// Check if width and height are integers.
			if(!is_numeric($size)) $size = 50;
			
			$request = $this->api(OAuth2::GET, "/me", Array("fields" => "id,picture.width({$size}).height({$size})"));
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			if(!isset($response->picture->data) || !isset($response->picture->data->url)) return false;
			$picture = $response->picture->data;
			
			// Build an <img /> tag.
			$picture->tag = "<img src=\"";
			$picture->tag .= htmlentities($picture->url);
			$picture->tag .= "\" style=\"width:{$size}px;height:{$size}px;\" />";
			
			return $picture;
		}
		
		// function permissions(). Fetches the permissions and returns them in an array.
		public function permissions($rearrange = true) {
			$request = $this->api(OAuth2::GET, "/me/permissions");
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			if($rearrange === false) {
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
			
			if(isset($permissions->{$permission}) && ($permissions->{$permission}->granted === true)) return true;
			else return false;
		}
		
		// function ids(). Fetches the user ids for other apps the user has authorised and are linked to the same business.
		public function ids($rearrange = true) {
			$request = $this->api(OAuth2::GET, "/me/ids_for_business");
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			if($rearrange === false) {
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
			$request = $this->api(OAuth2::DELETE, "/me/permissions" . (is_string($permission) ? "/" . urlencode($permission) : ""));
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			if(isset($response->success) && ($response->success === true)) return true;
			else return false;
		}
		
		// function pages(). Fetches a list of all the pages the user manages. Requires the manage_pages permission.
		public function pages($rearrange = true) {
			if(!$this->permission("manage_pages"))
				throw new Exception(__METHOD__ . "(): User has declined the manage_pages permission.");
			
			$request = $this->api(OAuth2::GET, "/me/accounts");
			$request->execute();
			$response = $request->responseObject();
			if(isset($request->error)) return false;
			
			if($rearrange === false) {
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
					$pages->{$page->id}->category_list = isset($page->category_list) ? $page->category_list : null;
					$pages->{$page->id}->oauth = new self($this->client()->id, $this->client()->secret, Array(
						"access_token" => $page->access_token
					));
				}
				
				return $pages;
			}
		}
		
		// function post(). Posts something to the user's timeline. Requires the publish_actions permission.
		public function post($message, $post = Array(), $returnid = false) {
			if(!$this->permission("publish_actions"))
				throw new Exception(__METHOD__ . "(): User has declined the publish_actions permission.");
			
			// Check $post is an array / object.
			if(is_array($post) || is_object($post)) $post = (object)$post;
			else throw new Exception(__METHOD__ . "(): \$post must be an array/object.");
			
			// Check $message is a string.
			if(is_string($message)) $post->message = $message;
			else throw new Exception(__METHOD__ . "(): \$message must be a string.");
			
			$request = $this->api(OAuth2::POST, "/me/feed", $post);
			$request->execute();
			$response = $request->responseObject();
			
			if(isset($response->id)) {
				if($returnid === true) return $response->id;
				else return true;
			} else return false;
		}
	}
	