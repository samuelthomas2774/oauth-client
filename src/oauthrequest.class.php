<?php
	/* class OAuthRequest
	 * /src/oauthrequest.class.php
	 */
	class OAuthRequest {
		// OAuth $oauth. The OAuth2 object used to make this request.
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
			if(!$oauth instanceof OAuth2) throw new Exception(__METHOD__ . "(): \$oauth must be a OAuth2 instance.");
			else $this->oauth = $oauth;
			
			// Store method in OAuthRequest::request["method"].
			if(!in_array($method, Array(OAuth2::GET, OAuth2::POST, OAuth2::PUT, OAuth2::DELETE))) throw new Exception(__METHOD__ . "(): \$method must be either GET, POST, PUT or DELETE.");
			else $this->request["method"] = $method;
			
			// Store url in OAuthRequest::request["url"].
			if(!is_string($url)) throw new Exception(__METHOD__ . "(): \$url must be a string.");
			else $this->request["url"] = $url;
			
			// Store params in OAuthRequest::request["params"].
			if(($method == OAuth2::PUT) && !is_string($params)) $this->request["params"] = "";
			elseif(($method != OAuth2::PUT) && !is_array($params)) $this->request["params"] = Array();
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
		
		// function execute(). Executes the request.
		public function execute() {
			if($this->curl == null) $this->curl = curl_init();
			
			if(!isset($this->request["params"]["access_token"]) && ($this->oauth->accessToken() != null) && (($this->oauth->options([ "api", "token_auth" ]) != false) && ($this->oauth->options([ "api", "token_auth" ]) != 2))) {
				$this->request["params"]["access_token"] = $this->oauth->accessToken();
			}
			
			if(in_array($this->request["method"], Array(OAuth2::GET, OAuth2::DELETE))) {
				if(strpos($this->request["url"], "?") !== false) $url = $this->request["url"] . "&" . http_build_query($this->request["params"]);
				else $url = $this->request["url"] . "?" . http_build_query($this->request["params"]);
			} else {
				$url = $this->request["url"];
			}
			
			if(filter_var($url, FILTER_VALIDATE_URL)) curl_setopt($this->curl, CURLOPT_URL, $url);
			else curl_setopt($this->curl, CURLOPT_URL, rtrim($this->oauth->options([ "api", "base_url" ]), "/") . "/" . ltrim($url, "/"));
			curl_setopt($this->curl, CURLOPT_HEADER, false);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
			$headers = Array(); // Headers
			if($this->request["auth"] == true) $headers["Authorization"] = "Basic " . base64_encode($this->oauth->client()->id . ":" . $this->oauth->client()->secret);
			elseif(($this->oauth->accessToken() != null) && ($this->oauth->options([ "api", "token_auth" ]) == 2) && !isset($this->request["params"]["access_token"])) $headers["Authorization"] = "Bearer {$this->oauth->accessToken()}";
			$dheaders = $this->oauth->options([ "api", "headers" ]);
			$headers = array_merge($headers, is_array($dheaders) ? $dheaders : Array(), $this->request["headers"]);
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, call_user_func(function($headers) {
				$return = Array();
				foreach($headers as $key => $value) $return[] = "{$key}: {$value}";
				return $return;
			}, $headers));
			
			$i = 0; $headers = Array();
			curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, function($ch, $header) use(&$i, &$headers) {
				if(strlen(trim($header)) == 0) return strlen($header);
				
				if(strpos($header, ": ") !== false) {
					list($key, $value) = explode(": ", trim($header), 2);
					$key = trim($key); $value = trim($value);
					
					$key = explode("-", $key);
					foreach($key as $_1 => $_2) $key[$_1] = ucfirst($_2);
					$key = implode("-", $key);
					
					$headers[$i] = trim($header);
					$headers[$key] = $value;
				} else {
					$headers[$i] = trim($header);
				}
				
				$i++;
				return strlen($header);
			});
			
			switch($this->request["method"]) {
				case OAuth2::GET: // GET
					// Requests are GET method by default.
					break;
				case OAuth2::POST: // POST
					curl_setopt($this->curl, CURLOPT_POST, true);
					curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($this->request["params"]));
					break;
				case OAuth2::PUT: // PUT
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
					curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->request["params"]);
					break;
				case OAuth2::DELETE: // DELETE
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
					break;
			}
			
			$curl_response = curl_exec($this->curl);
			$this->response["headers"] = $headers;
			$this->response["body"] = $curl_response;
			$this->response["curl"] = curl_getinfo($this->curl);
			
			// Success function.
			$callback = is_callable($this->oauth->options([ "api", "callback" ])) ? $this->oauth->options([ "api", "callback" ]) : function($oauth, $request, $curl) {
				// Check for errors.
				$response = $request->responseObject();
				if(is_object($response) && isset($response->error)) {
					$request->error = $response->error;
					$oauth->triggerError($response->error . (isset($response->error_description) ? ": " . $response->error_description : "") . " (" . curl_getinfo($curl, CURLINFO_HTTP_CODE) . ")", $response);
				}
			};
			call_user_func_array($callback, Array($this->oauth, $this, $this->curl));
		}
		
		// function request(). Returns information about the request.
		public function request() {
			return (object)$this->request;
		}
		
		// function parameter(). Returns / sets a parameter.
		public function parameter($name, $value = null) {
			if($value !== null) $this->request["params"][$name] = $value;
			else return isset($this->request["params"][$name]) ? $this->request["params"][$name] : null;
		}
		
		// function header(). Returns / sets a header.
		public function header($name, $value = null) {
			if($value !== null) $this->request["headers"][$name] = $value;
			else return isset($this->request["headers"][$name]) ? $this->request["headers"][$name] : null;
		}
		
		// function auth(). Returns / sets client autentication.
		public function auth($auth = null) {
			if($auth === true) $this->request["auth"] = true;
			elseif($auth === false) $this->request["auth"] = false;
			else return $this->request["auth"] == true ? true : false;
		}
		
		// function response(). Returns the response as a string.
		public function response($response_type = OAuth2::responseText) {
			switch($response_type) {
				default: case OAuth2::responseText: return $this->response["body"]; break;
				case OAuth2::responseJSONArray: $json = json_decode($this->response["body"], true); return $json == false ? Array() : $json; break;
				case OAuth2::responseJSONObject: $json = json_decode($this->response["body"], false); return $json == false ? new stdClass() : $json; break;
				case OAuth2::responseQueryStringArray: parse_str($this->response["body"], $query); return $query == false ? Array() : $query; break;
				case OAuth2::responseQueryStringObject: parse_str($this->response["body"], $query); return $query == false ? new stdClass() : (object)$query; break;
				case OAuth2::responseXMLArray: $xml = simplexml_load_string($this->response["body"]); return (array)$xml; break;
				case OAuth2::responseXMLObject: $xml = (array)simplexml_load_string($this->response["body"]); return (object)$xml; break;
				case OAuth2::responseSimpleXMLObject: $xml = simplexml_load_string($this->response["body"]); return $xml; break;
			}
		}
		
		// function responseHeaders(). Returns the response headers as an array.
		public function responseHeaders() {
			return $this->response["headers"];
		}
		
		// function responseObject(). Returns the response as an object.
		public function responseObject() {
			return $this->response(OAuth2::responseJSONObject);
		}
		
		// function responseArray(). Returns the response as an object.
		public function responseArray() {
			return $this->response(OAuth2::responseJSONArray);
		}
		
		// function responseQueryString(). Returns the response as an object.
		public function responseQueryString() {
			return $this->response(OAuth2::responseQueryStringObject);
		}
		
		// function responseXMLObject(). Returns the response as an object.
		public function responseXMLObject() {
			return $this->response(OAuth2::responseXMLObject);
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
	