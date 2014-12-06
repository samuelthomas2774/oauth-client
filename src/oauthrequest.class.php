<?php
	/* class OAuthRequest
	 * /src/oauthrequest.class.php
	 */
	class OAuthRequest {
		// OAuth $oauth. The OAuth object used to make this request.
		private $oauth = null;
		
		// Array $request. An array of information about the request.
		private $request = Array("method" => null, "url" => null, "params" => Array(), "headers" => Array(), "auth" => false);
		
		// Array $response. An array of information about the response. This is filled when the request is executed.
		private $response = Array();
		
		// Object $error. An object of information about an error that was returned from the API.
		private $error;
		
		// CURL $curl. A curl handler for the request.
		private $curl = null;
		
		// function __construct(). Creates a new OAuthRequest object.
		public function __construct($oauth, $method, $url, $params = Array(), $headers = Array(), $auth = false) {
			// Store oauth object in OAuthRequest::oauth.
			if(!$oauth instanceof OAuth) throw new Exception(__CLASS__ . "::" . __METHOD__ . "(): \$oauth must be a OAuth instance.");
			else $this->oauth = $oauth;
			
			// Store method in OAuthRequest::request["method"].
			if(($method != "GET") && ($method != "POST") && ($method != "DELETE") && ($method != "DELETE")) throw new Exception(__CLASS__ . "::" . __METHOD__ . "(): \$method must be either GET, POST or DELETE.");
			else $this->request["method"] = $method;
			
			// Store url in OAuthRequest::request["url"].
			if(!is_string($url)) throw new Exception(__CLASS__ . "::" . __METHOD__ . "(): \$url must be a string.");
			else $this->request["url"] = $url;
			
			// Store params in OAuthRequest::request["params"].
			if(($method == "PUT") && !is_string($params)) $this->request["params"] = "";
			elseif(($method != "PUT") && !is_array($params)) $this->request["params"] = Array();
			else $this->request["params"] = $params; // Do not throw an exception here. This variable is not required and has a default value, so just use that if the input cannot be used.
			
			// Store headers in OAuthRequest::request["headers"].
			if(!is_array($headers)) $this->request["headers"] = Array();
			else $this->request["headers"] = $headers;
			
			// Store authentication in OAuthRequest::request["auth"].
			if($auth == true) $this->request["auth"] = true;
			else $this->request["auth"] = false;
			
			// Create the OAuthRequest::error object.
			$this->error = new stdClass();
		}
		
		// function parameter(). Returns / sets a parameter.
		public function parameter($name, $value = null) {
			if($value != null) $this->request["params"][$name] = $value;
			else return isset($this->request["params"][$name]) ? $this->request["params"][$name] : null;
		}
		
		// function execute(). Executes the request.
		public function execute() {
			if($this->curl == null) $this->curl = curl_init();
			
			if(!isset($this->request["params"]["access_token"]) && ($this->oauth->accessToken() != null) && (($this->oauth->options("api")["token_auth"] != false) && ($this->oauth->options("api")["token_auth"] != 2))) {
				$this->request["params"]["access_token"] = $this->oauth->accessToken();
			}
			
			if(($this->request["method"] == "GET") || ($this->request["method"] == "DELETE")) {
				if(strpos($this->request["url"], "?") !== false) $url = $this->request["url"] . "&" . http_build_query($this->request["params"]);
				else $url = $this->request["url"] . "?" . http_build_query($this->request["params"]);
			} else {
				$url = $this->request["url"];
			}
			
			curl_setopt($this->curl, CURLOPT_URL, (strpos($url, "http") !== 0 ? $this->oauth->options("api")["base_url"] : "") . $url);
			curl_setopt($this->curl, CURLOPT_HEADER, false);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
			$headers = Array();
			//$headers[] = "Content-Type: application/x-www-form-urlencoded";
			if($this->request["auth"] == true) $headers[] = "Authorization: Basic " . base64_encode($this->oauth->app()["id"] . ":" . $this->oauth->app()["secret"]);
			elseif(($this->oauth->accessToken() != null) && ($this->oauth->options("api")["token_auth"] == 2) && !isset($this->request["params"]["access_token"])) $headers[] = "Authorization: Bearer {$this->oauth->accessToken()}";
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, array_merge($headers, $this->request["headers"]));
			
			if($this->request["method"] == "GET") {
				
			} elseif($this->request["method"] == "POST") {
				curl_setopt($this->curl, CURLOPT_POST, true);
				curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($this->request["params"]));
			} elseif($this->request["method"] == "DELETE") {
				curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
			} elseif($this->request["method"] == "PUT") {
				curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->request["params"]);
			}
			
			$curl_response = curl_exec($this->curl);
			$this->response["headers"] = Array();
			$this->response["body"] = $curl_response;
			$this->response["curl"] = curl_getinfo($this->curl);
			
			// Check for errors
			$response = $this->responseObject();
			if(isset($response->error)) {
				$this->error = $response->error;
				$this->oauth->error = $response->error;
				//exit(var_dump($this, true));
				if(is_object($response->error)) $this->oauth->triggerError($response->error->type . ": " . $response->error->status . " (" . $response->error->code . ")", $response->error);
				else $this->oauth->triggerError($response->error . ": " . $response->error_description . " (" . curl_getinfo($this->curl, CURLINFO_HTTP_CODE) . ")", $response);
			}
		}
		
		// function request(). Returns information about the request.
		public function request() {
			return $this->request;
		}
		
		// function response(). Returns the response as a string.
		public function response() {
			return $this->response["body"];
		}
		
		// function responseHeaders(). Returns the response headers as an array.
		public function responseHeaders() {
			return $this->response["headers"];
		}
		
		// function responseObject(). Returns the response as an object.
		public function responseObject() {
			$json = json_decode($this->response["body"], false);
			if($json == false) return new stdClass();
			else return $json;
		}
		
		// function responseArray(). Returns the response as an object.
		public function responseArray() {
			$json = json_decode($this->response["body"], true);
			if($json == false) return Array();
			else return $json;
		}
		
		// function errorInfo(). Returns an object of information about the last error returned from the API.
		public function errorInfo() {
			return $this->error;
		}
		
		// function close().
		public function close() {
			if($this->curl != null) {
				curl_close($this->curl);
				$this->curl = null;
			}
		}
		
		// function __destruct().
		public function __destruct() {
			$this->close();
		}
	}
	
