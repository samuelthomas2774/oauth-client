OAuth Client
============

An OAuth 2.0 Client library with built-in support for Facebook, Google, Microsoft, Yahoo, GitHub &amp; LinkedIn.

Facebook
------------
1. Include src/facebook.class.php in all pages that need to access Facebook.
    ```php
    require_once __DIR__ . '/oauth-client/src/facebook.class.php';
    
    ```
2. Create a new OAuthFacebook object with the parameters $client_id, $client_secret.
    These can be created at https://developers.facebook.com/apps/
    ```php
    $client_id = "appid";
    $client_secret = "appsecret";
    $oauth = new OAuthFacebook($client_id, $client_secret);
    
    ```

- To get a link to the Login Dialog:
    ```php
    $redirect_url = "http://example.com/facebook/code.php";
    $permissions = Array("email", "user_friends"); // Optional scope array.
    $login_url = $oauth->loginURL($redirect_url, $permissions);
    
    ```
- To get an access token from the code that was returned:
    ```php
    $redirect_url = "http://example.com/facebook/code.php"; // Must match the $redirect_url given to OAuth2::loginURL() exactly. The user will not be redirected anywhere.
    $oauth->getAccessTokenFromCode($redirect_url);
    
    ```
- To make API Requests: (The access token will be included automatically).
    ```php
    $method = "GET"; // Must be GET, POST, PUT or DELETE (uppercase).
    $url = "/me";
    //$params = Array("fields" => "id,name"); // You can also add an optional array of parameters.
    $request = $oauth->api($method, $url /* , $params */);
    try { $request->execute(); } catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }
    $response_plaintext = $request->response();
    $response_array = $response->responseArray();
    $response_object = $response->responseObject();
    
    ```
- To get the current user:
    ```php
    try { $user = $oauth->userProfile(); }
    catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }
    
    ```
- To get the current user's profile picture:
    ```php
    $width = 50; // Width in pixels, optional.
    $height = 50; // Height in pixels, optional.
    try { $user_picture = $oauth->profilePicture($width, $height); }
    catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }
    $user_picture_url = $user_picture->url;
    $user_picture_htmltag = $user_picture->tag;
    
    ```
- To get / set the current access token:
    You do not need to do this at the start of the script to get the access token from the session, this is done automatically. Also, this function updates the access token in the session.
    ```php
    $oauth->accessToken(); // Get
    $oauth->accessToken($new_access_token); // Set
    $oauth->accessToken($new_access_token, false); // Set without updating the access token in the session.
    
    ```

Google+
------------
1. Include src/google.class.php in all pages that need to access Google+.
    ```php
    require_once \__DIR__ . '/oauth-client/src/google.class.php';
    
    ```
2. Create a new OAuthGoogle object with the parameters $client_id, $client_secret.
    These can be created at https://console.developers.google.com/
    ```php
    $client_id = "appid";
    $client_secret = "appsecret";
    $oauth = new OAuthGoogle($client_id, $client_secret);
    
    ```

- To get a link to the Login Dialog:
    ```php
    $redirect_url = "http://example.com/google/code.php";
    $permissions = Array("https://www.googleapis.com/auth/plus.login"); // Optional scope array.
    $login_url = $oauth->loginURL($redirect_url, $permissions);
    
    ```
- To get an access token from the code that was returned:
    ```php
    $redirect_url = "http://example.com/google/code.php"; // Must match the $redirect_url given to OAuth1::loginURL() exactly. The user will not be redirected anywhere.
    $oauth->getAccessTokenFromCode($redirect_url);
    
    ```
- To make API Requests: (The access token will be included automatically).
    ```php
    $method = "GET"; // Must be GET, POST, PUT or DELETE (uppercase).
    $url = "/userinfo";
    //$params = Array("fields" => "id,name"); // You can also add an optional array of parameters.
    $request = $oauth->api($method, $url /* , $params */);
    try { $request->execute(); } catch(Exception $error) { exit("Google returned an error: " . print_r($error, true)); }
    $response_plaintext = $request->response();
    $response_array = $response->responseArray();
    $response_object = $response->responseObject();
    
    ```
- To get the current user:
    ```php
    try { $user = $oauth->userProfile(); }
    catch(Exception $error) { exit("Google returned an error: " . print_r($error, true)); }
    
    ```
- To get / set the current access token:
    You do not need to do this at the start of the script to get the access token from the session, this is done automatically. Also, this function updates the access token in the session.
    ```php
    $oauth->accessToken(); // Get
    $oauth->accessToken($new_access_token); // Set
    $oauth->accessToken($new_access_token, false); // Set without updating the access token in the session.
    
    ```

Microsoft Account
------------
1. Include src/microsoft.class.php in all pages that need to access Microsoft.
    ```php
    require_once \__DIR__ . '/oauth-client/src/microsoft.class.php';
    
    ```
2. Create a new OAuthMicrosoft object with the parameters $client_id, $client_secret.
    These can be created at https://account.live.com/developers/applications/create
    ```php
    $client_id = "appid";
    $client_secret = "appsecret";
    $oauth = new OAuthMicrosoft($client_id, $client_secret);
    
    ```

- To get a link to the Login Dialog:
    ```php
    $redirect_url = "http://example.com/microsoft/code.php";
    $permissions = Array("wl.signin", "wl.basic"); // Optional scope array.
    $login_url = $oauth->loginURL($redirect_url, $permissions);
    
    ```
- To get an access token from the code that was returned:
    ```php
    $redirect_url = "http://example.com/microsoft/code.php"; // Must match the $redirect_url given to OAuth2::loginURL() exactly. The user will not be redirected anywhere.
    $oauth->getAccessTokenFromCode($redirect_url);
    
    ```
- To make API Requests: (The access token will be included automatically).
    ```php
    $method = "GET"; // Must be GET, POST, PUT or DELETE (uppercase).
    $url = "/me";
    //$params = Array("fields" => "id,name"); // You can also add an optional array of parameters.
    $request = $oauth->api($method, $url /* , $params */);
    try { $request->execute(); } catch(Exception $error) { exit("Microsoft returned an error: " . print_r($error, true)); }
    $response_plaintext = $request->response();
    $response_array = $response->responseArray();
    $response_object = $response->responseObject();
    
    ```
- To get the current user:
    ```php
    try { $user = $oauth->userProfile(); }
    catch(Exception $error) { exit("Microsoft returned an error: " . print_r($error, true)); }
    
    ```
- To get / set the current access token:
    You do not need to do this at the start of the script to get the access token from the session, this is done automatically. Also, this function updates the access token in the session.
    ```php
    $oauth->accessToken(); // Get
    $oauth->accessToken($new_access_token); // Set
    $oauth->accessToken($new_access_token, false); // Set without updating the access token in the session.
    
    ```

Yahoo!
------------
1. Include src/yahoo.class.php in all pages that need to access Yahoo.
    ```php
    require_once \__DIR__ . '/oauth-client/src/yahoo.class.php';
    
    ```
2. Create a new OAuthYahoo object with the parameters $client_id, $client_secret.
    These can be created at https://developer.apps.yahoo.com/dashboard/createKey.html
    ```php
    $client_id = "appid";
    $client_secret = "appsecret";
    $oauth = new OAuthYahoo($client_id, $client_secret);
    
    ```

- To get a link to the Login Dialog:
    ```php
    $redirect_url = "http://example.com/yahoo/code.php";
    $login_url = $oauth->loginURL($redirect_url);
    
    ```
- To get an access token from the code that was returned:
    ```php
    $redirect_url = "http://example.com/yahoo/code.php"; // Must match the $redirect_url given to OAuth2::loginURL() exactly. The user will not be redirected anywhere.
    $oauth->getAccessTokenFromCode($redirect_url);
    
    ```
- To make API Requests: (The access token will be included automatically).
    ```php
    $method = "GET"; // Must be GET, POST, PUT or DELETE (uppercase).
    $url = "/me";
    //$params = Array("fields" => "id,name"); // You can also add an optional array of parameters.
    $request = $oauth->api($method, $url /* , $params */);
    try { $request->execute(); } catch(Exception $error) { exit("Yahoo returned an error: " . print_r($error, true)); }
    $response_plaintext = $request->response();
    $response_array = $response->responseArray();
    $response_object = $response->responseObject();
    
    ```
- To get the current user:
    ```php
    try { $user = $oauth->userProfile(); }
    catch(Exception $error) { exit("Yahoo returned an error: " . print_r($error, true)); }
    
    ```
- To get / set the current access token:
    You do not need to do this at the start of the script to get the access token from the session, this is done automatically. Also, this function updates the access token in the session.
    ```php
    $oauth->accessToken(); // Get
    $oauth->accessToken($new_access_token); // Set
    $oauth->accessToken($new_access_token, false); // Set without updating the access token in the session.
    
    ```

GitHub
------------
1. Include src/github.class.php in all pages that need to access GitHub.
    ```php
    require_once \__DIR__ . '/oauth-client/src/github.class.php';
    
    ```
2. Create a new OAuthGitHub object with the parameters $client_id, $client_secret.
    These can be created at https://github.com/settings/applications/new
    ```php
    $client_id = "appid";
    $client_secret = "appsecret";
    $oauth = new OAuthGitHub($client_id, $client_secret);
    
    ```

- To get a link to the Login Dialog:
    ```php
    $redirect_url = "http://example.com/github/code.php";
    $permissions = Array("user"); // Optional scope array.
    $login_url = $oauth->loginURL($redirect_url, $permissions);
    
    ```
- To get an access token from the code that was returned:
    ```php
    $redirect_url = "http://example.com/github/code.php"; // Must match the $redirect_url given to OAuth2::loginURL() exactly. The user will not be redirected anywhere.
    $oauth->getAccessTokenFromCode($redirect_url);
    
    ```
- To make API Requests: (The access token will be included automatically).
    ```php
    $method = "GET"; // Must be GET, POST, PUT or DELETE (uppercase).
    $url = "/me";
    //$params = Array("fields" => "id,name"); // You can also add an optional array of parameters.
    $request = $oauth->api($method, $url /* , $params */);
    try { $request->execute(); } catch(Exception $error) { exit("GitHub returned an error: " . print_r($error, true)); }
    $response_plaintext = $request->response();
    $response_array = $response->responseArray();
    $response_object = $response->responseObject();
    
    ```
- To get the current user:
    ```php
    try { $user = $oauth->userProfile(); }
    catch(Exception $error) { exit("GitHub returned an error: " . print_r($error, true)); }
    
    ```
- To get / set the current access token:
    You do not need to do this at the start of the script to get the access token from the session, this is done automatically. Also, this function updates the access token in the session.
    ```php
    $oauth->accessToken(); // Get
    $oauth->accessToken($new_access_token); // Set
    $oauth->accessToken($new_access_token, false); // Set without updating the access token in the session.
    
    ```

LinkedIn
------------
1. Include src/linkedin.class.php in all pages that need to access LinkedIn.
    ```php
    require_once \__DIR__ . '/oauth-client/src/linkedin.class.php';
    
    ```
2. Create a new OAuthLinkedin object with the parameters $client_id, $client_secret.
    These can be created at https://www.linkedin.com/developer/apps/new
    ```php
    $client_id = "appid";
    $client_secret = "appsecret";
    $oauth = new OAuthLinkedin($client_id, $client_secret);
    
    ```

- To get a link to the Login Dialog:
    ```php
    $redirect_url = "http://example.com/linkedin/code.php";
    $permissions = Array("r_basicprofile"); // Optional scope array.
    $login_url = $oauth->loginURL($redirect_url, $permissions);
    
    ```
- To get an access token from the code that was returned:
    ```php
    $redirect_url = "http://example.com/linkedin/code.php"; // Must match the $redirect_url given to OAuth2::loginURL() exactly. The user will not be redirected anywhere.
    $oauth->getAccessTokenFromCode($redirect_url);
    
    ```
- To make API Requests: (The access token will be included automatically).
    ```php
    $method = "GET"; // Must be GET, POST, PUT or DELETE (uppercase).
    $url = "/me";
    //$params = Array("fields" => "id,name"); // You can also add an optional array of parameters.
    $request = $oauth->api($method, $url /* , $params */);
    try { $request->execute(); } catch(Exception $error) { exit("LinkedIn returned an error: " . print_r($error, true)); }
    $response_plaintext = $request->response();
    $response_array = $response->responseArray();
    $response_object = $response->responseObject();
    
    ```
- To get the current user:
    ```php
    try { $user = $oauth->userProfile(); }
    catch(Exception $error) { exit("LinkedIn returned an error: " . print_r($error, true)); }
    
    ```
- To get / set the current access token:
    You do not need to do this at the start of the script to get the access token from the session, this is done automatically. Also, this function updates the access token in the session.
    ```php
    $oauth->accessToken(); // Get
    $oauth->accessToken($new_access_token); // Set
    $oauth->accessToken($new_access_token, false); // Set without updating the access token in the session.
    
    ```

Extending the OAuth2 class.
------------
You can extend the OAuth2, OAuthFacebook, OAuthGoogle, OAuthMicrosoft, OAuthYahoo, OAuthGitHub & OAuthLinkedin classes to add new functions and make existing functions work differently:

```php
require_once __DIR__ . '/oauth-client/src/facebook.class.php';
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
}

```

You can then use the newly created class:

```php
$oauth = new My_Extended_Facebook_Class("appid", "appsecret");
try { echo "Your Facebook User ID is: " . $oauth->userid(); }
catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }

```
