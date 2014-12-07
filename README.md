OAuth Client
============

An OAuth 2.0 Client library with built-in support for Facebook, Google, Microsoft, Yahoo, GitHub &amp; LinkedIn.

Facebook
------------
1. Include src/facebook.class.php in all pages that need to access Facebook.
<pre>require_once \__DIR__ . '/oauth-client/src/facebook.class.php';</pre>
2. Create a new OAuthFacebook object with the parameters $app_id, $app_secret.
<pre>$app_id = "appid";
$app_secret = "appsecret";
$oauth = new OAuthFacebook($app_id, $app_secret);</pre>

- To get a link to the Login Dialog:
<pre>$redirect_url = "http://example.com/facebook/code.php";
$permissions = Array("email", "user_friends"); // Optional scope array.
$login_url = $oauth->loginURL($redirect_url, $permissions);</pre>
- To get an access token from the code that was returned:
<pre>$redirect_url = "http://example.com/facebook/code.php"; // Must match the $redirect_url given to OAuth::loginURL() exactly. The user will not be redirected anywhere.
$oauth->getAccessTokenFromCode($redirect_url);</pre>
- To make Graph API Calls: (The access token will be included automatically).
<pre>$method = "GET"; // Must be GET, POST, PUT or DELETE (uppercase).
$url = "/me";
//$params = Array("fields" => "id,name"); // You can also add an optional array of parameters.
$request = $oauth->api($method, $url /* , $params */);
try { $request->execute(); } catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }
$response_plaintext = $request->response();
$response_array = $response->responseArray();
$response_object = $response->responseObject();</pre>
- To get the current user:
<pre>try { $user = $oauth->userProfile(); }
catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }</pre>
- To get the current user's profile picture:
<pre>$width = 50; // Width in pixels, optional.
$height = 50; // Height in pixels, optional.
try { $user_picture = $oauth->profilePicture($width, $height); }
catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }
$user_picture_url = $user_picture->url;
$user_picture_htmltag = $user_picture->tag;</pre>
- To get / set the current access token:
You do not need to do this at the start of the script to get the access token from the session, this is done automatically. Also, this function updates the access token in the session.
<pre>$oauth->accessToken(); // Get
$oauth->accessToken($new_access_token); // Set
$oauth->accessToken($new_access_token, false); // Set without updating the access token in the session.</pre>

Google+
------------
1. Include src/google.class.php in all pages that need to access Google+.
<pre>require_once \__DIR__ . '/oauth-client/src/google.class.php';</pre>
2. Create a new OAuthFacebook object with the parameters $client_id, $client_secret.
<pre>$client_id = "appid";
$client_secret = "appsecret";
$oauth = new OAuthGoogle($client_id, $client_secret);</pre>

- To get a link to the Login Dialog:
<pre>$redirect_url = "http://example.com/google/code.php";
$permissions = Array("https://www.googleapis.com/auth/plus.login"); // Optional scope array.
$login_url = $oauth->loginURL($redirect_url, $permissions);</pre>
- To get an access token from the code that was returned:
<pre>$redirect_url = "http://example.com/google/code.php"; // Must match the $redirect_url given to OAuth::loginURL() exactly. The user will not be redirected anywhere.
$oauth->getAccessTokenFromCode($redirect_url);</pre>
- To make API Calls: (The access token will be included automatically).
<pre>$method = "GET"; // Must be GET, POST, PUT or DELETE (uppercase).
$url = "/userinfo";
//$params = Array("fields" => "id,name"); // You can also add an optional array of parameters.
$request = $oauth->api($method, $url /* , $params */);
try { $request->execute(); } catch(Exception $error) { exit("Google returned an error: " . print_r($error, true)); }
$response_plaintext = $request->response();
$response_array = $response->responseArray();
$response_object = $response->responseObject();</pre>
- To get the current user:
<pre>try { $user = $oauth->userProfile(); }
catch(Exception $error) { exit("Google returned an error: " . print_r($error, true)); }</pre>
- To get / set the current access token:
You do not need to do this at the start of the script to get the access token from the session, this is done automatically. Also, this function updates the access token in the session.
<pre>$oauth->accessToken(); // Get
$oauth->accessToken($new_access_token); // Set
$oauth->accessToken($new_access_token, false); // Set without updating the access token in the session.</pre>

Microsoft Account
------------
1. Include src/microsoft.class.php in all pages that need to access Microsoft.
<pre>require_once \__DIR__ . '/oauth-client/src/microsoft.class.php';</pre>
2. Create a new OAuthMicrosoft object with the parameters $client_id, $client_secret.
<pre>$client_id = "appid";
$client_secret = "appsecret";
$oauth = new OAuthMicrosoft($client_id, $client_secret);</pre>

- To get a link to the Login Dialog:
<pre>$redirect_url = "http://example.com/microsoft/code.php";
$permissions = Array("wl.signin", "wl.basic"); // Optional scope array.
$login_url = $oauth->loginURL($redirect_url, $permissions);</pre>
- To get an access token from the code that was returned:
<pre>$redirect_url = "http://example.com/microsoft/code.php"; // Must match the $redirect_url given to OAuth::loginURL() exactly. The user will not be redirected anywhere.
$oauth->getAccessTokenFromCode($redirect_url);</pre>
- To make API Calls: (The access token will be included automatically).
<pre>$method = "GET"; // Must be GET, POST, PUT or DELETE (uppercase).
$url = "/me";
//$params = Array("fields" => "id,name"); // You can also add an optional array of parameters.
$request = $oauth->api($method, $url /* , $params */);
try { $request->execute(); } catch(Exception $error) { exit("Microsoft returned an error: " . print_r($error, true)); }
$response_plaintext = $request->response();
$response_array = $response->responseArray();
$response_object = $response->responseObject();</pre>
- To get the current user:
<pre>try { $user = $oauth->userProfile(); }
catch(Exception $error) { exit("Microsoft returned an error: " . print_r($error, true)); }</pre>
- To get / set the current access token:
You do not need to do this at the start of the script to get the access token from the session, this is done automatically. Also, this function updates the access token in the session.
<pre>$oauth->accessToken(); // Get
$oauth->accessToken($new_access_token); // Set
$oauth->accessToken($new_access_token, false); // Set without updating the access token in the session.</pre>

Yahoo!
------------
1. Include src/yahoo.class.php in all pages that need to access Yahoo.
<pre>require_once \__DIR__ . '/oauth-client/src/yahoo.class.php';</pre>
2. Create a new OAuthYahoo object with the parameters $client_id, $client_secret.
<pre>$client_id = "appid";
$client_secret = "appsecret";
$oauth = new OAuthYahoo($client_id, $client_secret);</pre>

- To get a link to the Login Dialog:
<pre>$redirect_url = "http://example.com/yahoo/code.php";
$login_url = $oauth->loginURL($redirect_url);</pre>
- To get an access token from the code that was returned:
<pre>$redirect_url = "http://example.com/yahoo/code.php"; // Must match the $redirect_url given to OAuth::loginURL() exactly. The user will not be redirected anywhere.
$oauth->getAccessTokenFromCode($redirect_url);</pre>
- To make API Calls: (The access token will be included automatically).
<pre>$method = "GET"; // Must be GET, POST, PUT or DELETE (uppercase).
$url = "/me";
//$params = Array("fields" => "id,name"); // You can also add an optional array of parameters.
$request = $oauth->api($method, $url /* , $params */);
try { $request->execute(); } catch(Exception $error) { exit("Yahoo returned an error: " . print_r($error, true)); }
$response_plaintext = $request->response();
$response_array = $response->responseArray();
$response_object = $response->responseObject();</pre>
- To get the current user:
<pre>try { $user = $oauth->userProfile(); }
catch(Exception $error) { exit("Yahoo returned an error: " . print_r($error, true)); }</pre>
- To get / set the current access token:
You do not need to do this at the start of the script to get the access token from the session, this is done automatically. Also, this function updates the access token in the session.
<pre>$oauth->accessToken(); // Get
$oauth->accessToken($new_access_token); // Set
$oauth->accessToken($new_access_token, false); // Set without updating the access token in the session.</pre>

GitHub
------------
1. Include src/github.class.php in all pages that need to access GitHub.
<pre>require_once \__DIR__ . '/oauth-client/src/github.class.php';</pre>
2. Create a new OAuthGitHub object with the parameters $client_id, $client_secret.
<pre>$client_id = "appid";
$client_secret = "appsecret";
$oauth = new OAuthGitHub($client_id, $client_secret);</pre>

- To get a link to the Login Dialog:
<pre>$redirect_url = "http://example.com/github/code.php";
$permissions = Array("user"); // Optional scope array.
$login_url = $oauth->loginURL($redirect_url, $permissions);</pre>
- To get an access token from the code that was returned:
<pre>$redirect_url = "http://example.com/github/code.php"; // Must match the $redirect_url given to OAuth::loginURL() exactly. The user will not be redirected anywhere.
$oauth->getAccessTokenFromCode($redirect_url);</pre>
- To make API Calls: (The access token will be included automatically).
<pre>$method = "GET"; // Must be GET, POST, PUT or DELETE (uppercase).
$url = "/me";
//$params = Array("fields" => "id,name"); // You can also add an optional array of parameters.
$request = $oauth->api($method, $url /* , $params */);
try { $request->execute(); } catch(Exception $error) { exit("GitHub returned an error: " . print_r($error, true)); }
$response_plaintext = $request->response();
$response_array = $response->responseArray();
$response_object = $response->responseObject();</pre>
- To get the current user:
<pre>try { $user = $oauth->userProfile(); }
catch(Exception $error) { exit("GitHub returned an error: " . print_r($error, true)); }</pre>
- To get / set the current access token:
You do not need to do this at the start of the script to get the access token from the session, this is done automatically. Also, this function updates the access token in the session.
<pre>$oauth->accessToken(); // Get
$oauth->accessToken($new_access_token); // Set
$oauth->accessToken($new_access_token, false); // Set without updating the access token in the session.</pre>

LinkedIn
------------
1. Include src/linkedin.class.php in all pages that need to access LinkedIn.
<pre>require_once \__DIR__ . '/oauth-client/src/linkedin.class.php';</pre>
2. Create a new OAuthLinkedin object with the parameters $client_id, $client_secret.
<pre>$client_id = "appid";
$client_secret = "appsecret";
$oauth = new OAuthLinkedin($client_id, $client_secret);</pre>

- To get a link to the Login Dialog:
<pre>$redirect_url = "http://example.com/linkedin/code.php";
$permissions = Array("r_basicprofile"); // Optional scope array.
$login_url = $oauth->loginURL($redirect_url, $permissions);</pre>
- To get an access token from the code that was returned:
<pre>$redirect_url = "http://example.com/linkedin/code.php"; // Must match the $redirect_url given to OAuth::loginURL() exactly. The user will not be redirected anywhere.
$oauth->getAccessTokenFromCode($redirect_url);</pre>
- To make API Calls: (The access token will be included automatically).
<pre>$method = "GET"; // Must be GET, POST, PUT or DELETE (uppercase).
$url = "/me";
//$params = Array("fields" => "id,name"); // You can also add an optional array of parameters.
$request = $oauth->api($method, $url /* , $params */);
try { $request->execute(); } catch(Exception $error) { exit("LinkedIn returned an error: " . print_r($error, true)); }
$response_plaintext = $request->response();
$response_array = $response->responseArray();
$response_object = $response->responseObject();</pre>
- To get the current user:
<pre>try { $user = $oauth->userProfile(); }
catch(Exception $error) { exit("LinkedIn returned an error: " . print_r($error, true)); }</pre>
- To get / set the current access token:
You do not need to do this at the start of the script to get the access token from the session, this is done automatically. Also, this function updates the access token in the session.
<pre>$oauth->accessToken(); // Get
$oauth->accessToken($new_access_token); // Set
$oauth->accessToken($new_access_token, false); // Set without updating the access token in the session.</pre>

Extending the OAuth class.
------------
You can extend the OAuth, OAuthFacebook, OAuthGoogle, OAuthMicrosoft, OAuthYahoo, OAuthGitHub & OAuthLinkedin classes to add new functions and make existing functions work differently:
<pre>require_once __DIR__ . '/oauth-client/src/facebook.class.php';
class My_Extended_Facebook_Class extends OAuthFacebook {
    // Options. Customize default options (optional).
    $options = Array(
        // Change the api->base_url option to use a different api version.
        "api" => Array("base_url" => "https://graph.facebook.com/v1.0")
    );
    
    // Add a new function for getting the current user's id.
    public function userid() {
        $user = $this->userProfile(Array("id"));
        return $user->id;
    }
}</pre>
You can then use the newly created class:
<pre>$oauth = new My_Extended_Facebook_Class("appid", "appsecret");
try { echo "Your Facebook User ID is: " . $oauth->userid(); }
catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }</pre>
