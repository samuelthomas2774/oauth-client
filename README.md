OAuth Client
============

An OAuth 2.0 Client library with built-in support for Facebook, Google, Microsoft, Yahoo, GitHub, LinkedIn &amp; more.

**Built-in providers**

- Facebook https://facebook.com
- Google https://google.co.uk
- Microsoft https://microsoft.com/en-gb/
- Yahoo https://yahoo.co.uk
- GitHub https://github.com
- LinkedIn https://linkedin.com
- Spotify https://spotify.com
- Amazon https://amazon.co.uk
- Disqus https://disqus.com
- Instagram https://instagram.com
- samuelthomas.ml http://samuelthomas.ml (my website)
- TeamViewer https://teamviewer.com
- WordPress.com https://wordpress.com
- Other, just create a new OAuth2 object and include the dialog->base_url, api->base_url and requests->{"/oauth/token"} options.

Default (OAuth2)
------------
1. Include src/oauth.class.php in all page that need access to your other provider.
    ```php
    require_once __DIR__ . '/oauth-client/src/oauth.class.php';
    
    ```
2. Create a new OAuth2 object with the parameters $client_id, $client_secret and $options.
    The $options array must have at least dialog->base_url, api->base_url and requests->{"/oauth/token"}.
    ```php
    $client_id = "appid";
    $client_secret = "appsecret";
    $oauth = new OAuth2(
        $client_id, $client_secret,
        $options = Array(
            "dialog" => Array("base_url" => "https://facebook.com/dialog/oauth"),
            "api" => Array("base_url" => "https://graph.facebook.com/v2.2"),
            "requests" => Array("/oauth/token" => "/oauth/access_token")
        )
    );
    
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
    $request = $oauth->api($method, $url /* , $params = Array() /* , $headers = Array() /* , $auth = false */ */ */ );
    try { $request->execute(); } catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }
    $response_plaintext = $request->response();
    $response_array = $response->responseArray();
    $response_object = $response->responseObject();
    
    ```
- To get / set the current access token:
    You do not need to do this at the start of the script to get the access token from the session, this is done automatically. Also, this function updates the access token in the session.
    ```php
    $oauth->accessToken(); // Get
    $oauth->accessToken($new_access_token); // Set
    $oauth->accessToken($new_access_token, false); // Set without updating the access token in the session.
    
    ```

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

- To make API Requests: (The access token will be included automatically).
    ```php
    $request = $oauth->api("GET", "/me", Array("fields" => "id,name"));
    try { $request->execute(); } catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }
    $response = $response->responseObject();
    
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

Google+
------------
1. Include src/google.class.php in all pages that need to access Google+.
    ```php
    require_once __DIR__ . '/oauth-client/src/google.class.php';
    
    ```
2. Create a new OAuthGoogle object with the parameters $client_id, $client_secret.
    These can be created at https://console.developers.google.com/
    ```php
    $client_id = "appid";
    $client_secret = "appsecret";
    $oauth = new OAuthGoogle($client_id, $client_secret);
    
    ```

- To make API Requests: (The access token will be included automatically).
    ```php
    $request = $oauth->api("GET", "/me", Array("fields" => "id,name"));
    try { $request->execute(); } catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }
    $response = $response->responseObject();
    
    ```
- To get the current user:
    ```php
    try { $user = $oauth->userProfile(); }
    catch(Exception $error) { exit("Google returned an error: " . print_r($error, true)); }
    
    ```

Microsoft Account
------------
1. Include src/microsoft.class.php in all pages that need to access Microsoft.
    ```php
    require_once __DIR__ . '/oauth-client/src/microsoft.class.php';
    
    ```
2. Create a new OAuthMicrosoft object with the parameters $client_id, $client_secret.
    These can be created at https://account.live.com/developers/applications/create
    ```php
    $client_id = "appid";
    $client_secret = "appsecret";
    $oauth = new OAuthMicrosoft($client_id, $client_secret);
    
    ```

- To make API Requests: (The access token will be included automatically).
    ```php
    $request = $oauth->api("GET", "/me", Array("fields" => "id,name"));
    try { $request->execute(); } catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }
    $response = $response->responseObject();
    
    ```
- To get the current user:
    ```php
    try { $user = $oauth->userProfile(); }
    catch(Exception $error) { exit("Microsoft returned an error: " . print_r($error, true)); }
    
    ```

Yahoo!
------------
1. Include src/yahoo.class.php in all pages that need to access Yahoo.
    ```php
    require_once __DIR__ . '/oauth-client/src/yahoo.class.php';
    
    ```
2. Create a new OAuthYahoo object with the parameters $client_id, $client_secret.
    These can be created at https://developer.apps.yahoo.com/dashboard/createKey.html
    ```php
    $client_id = "appid";
    $client_secret = "appsecret";
    $oauth = new OAuthYahoo($client_id, $client_secret);
    
    ```

- To make API Requests: (The access token will be included automatically).
    ```php
    $request = $oauth->api("GET", "/me", Array("fields" => "id,name"));
    try { $request->execute(); } catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }
    $response = $response->responseObject();
    
    ```
- To get the current user:
    ```php
    try { $user = $oauth->userProfile(); }
    catch(Exception $error) { exit("Yahoo returned an error: " . print_r($error, true)); }
    
    ```

GitHub
------------
1. Include src/github.class.php in all pages that need to access GitHub.
    ```php
    require_once __DIR__ . '/oauth-client/src/github.class.php';
    
    ```
2. Create a new OAuthGitHub object with the parameters $client_id, $client_secret.
    These can be created at https://github.com/settings/applications/new
    ```php
    $client_id = "appid";
    $client_secret = "appsecret";
    $oauth = new OAuthGitHub($client_id, $client_secret);
    
    ```

- To make API Requests: (The access token will be included automatically).
    ```php
    $request = $oauth->api("GET", "/me", Array("fields" => "id,name"));
    try { $request->execute(); } catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }
    $response = $response->responseObject();
    
    ```
- To get the current user:
    ```php
    try { $user = $oauth->userProfile(); }
    catch(Exception $error) { exit("GitHub returned an error: " . print_r($error, true)); }
    
    ```

LinkedIn
------------
1. Include src/linkedin.class.php in all pages that need to access LinkedIn.
    ```php
    require_once __DIR__ . '/oauth-client/src/linkedin.class.php';
    
    ```
2. Create a new OAuthLinkedin object with the parameters $client_id, $client_secret.
    These can be created at https://www.linkedin.com/developer/apps/new
    ```php
    $client_id = "appid";
    $client_secret = "appsecret";
    $oauth = new OAuthLinkedin($client_id, $client_secret);
    
    ```

- To make API Requests: (The access token will be included automatically).
    ```php
    $request = $oauth->api("GET", "/me", Array("fields" => "id,name"));
    try { $request->execute(); } catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }
    $response = $response->responseObject();
    
    ```
- To get the current user:
    ```php
    try { $user = $oauth->userProfile(); }
    catch(Exception $error) { exit("LinkedIn returned an error: " . print_r($error, true)); }
    
    ```

Other providers
------------
Empty sign-up urls will be added soon!

Any other providers please contact me at http://samuelthomas.ml/about/contact and I'll add it as soon as possible.

**Provider**        | **Class**             | **File in /src**          | **Sign-up url**
--------------------|-----------------------|---------------------------|-------------------------
Facebook            | OAuthFacebook         | facebook.class.php        | https://developers.facebook.com/apps/
Google              | OAuthGoogle           | google.class.php          | https://console.developers.google.com/
Microsoft           | OAuthMicrosoft        | microsoft.class.php       | https://account.live.com/developers/applications/create
Yahoo               | OAuthYahoo            | yahoo.class.php           | https://developer.apps.yahoo.com/dashboard/createKey.html
GitHub              | OAuthGitHub           | github.class.php          | https://github.com/settings/applications/new
LinkedIn            | OAuthLinkedin         | linkedin.class.php        | https://www.linkedin.com/developer/apps/new
Amazon              | OAuthAmazon           | amazon.class.php          |
Disqus              | OAuthDisqus           | disqus.class.php          |
Instagram           | OAuthInstagram        | instagram.class.php       |
Spotify             | OAuthSpotify          | spotify.class.php         |
samuelthomas.ml     | OAuthST               | st.class.php              | http://samuelthomas.ml/developer/clients
TeamViewer          | OAuthTeamViewer       | teamviewer.class.php      |
WordPress.com       | OAuthWordPress        | wordpress.class.php       |

Extending the OAuth2 class.
------------
You can extend the OAuth2 and other classes to add new functions and make existing functions work differently:

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
try { echo "Your Facebook User ID (for this app) is: " . $oauth->userid(); }
catch(Exception $error) { exit("Facebook returned an error: " . print_r($error, true)); }

```
