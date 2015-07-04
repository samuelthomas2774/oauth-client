OAuth Client
============

An OAuth 2.0 Client library with built-in support for Facebook, Google, Microsoft, Yahoo, GitHub, LinkedIn & more.

<pre>
<b>Live Demo</b>: You can see the current source on Heroku, running from examples/all, here:<br />
<a href="http://oauth-client-test.herokuapp.com">http://oauth-client-test.herokuapp.com/</a>
</pre>

**[Built-in providers](#built-in-providers)**

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

- To automatically set / get an access token:
    ```php
    // Will a execute task in the order:
    // Set the access token to $_GET["access_token"].
    // Set the access token to $_POST["access_token"].
    // Get an access token from the code $_GET["code"] (and check $_GET["state"]). This will try to guess the redirect_uri from the current url, make sure the server does not redirect anywhere, like from http to https.
    // Get an access token from the username and password $_POST["username"] and $_POST["password"].
    // Get an access token from the refresh token $_GET["refresh_token"].
    // Get an access token from the refresh token $_POST["refresh_token"].
    switch($oauth->autorun()) {
        case OAuth2::AutoSetToken:
            echo "The access token was set to {$oauth->accessToken()}.\n"; // Note: this value came from the user, if outputting html make sure to filter this.
            break;
        case OAuth2::AutoGetFromCode:
            echo "The code {$_GET["code"]} was used to get the access token {$oauth->accessToken()}.\n"; // Note: this value came from the user, if outputting html make sure to filter this.
            break;
        case OAuth2::AutoGetFromPassword:
            echo "The username {$_POST["username"]} and password ******** was used to get the access token {$oauth->accessToken()}.\n"; // Note: this value came from the user, if outputting html make sure to filter this.
            break;
        case OAuth2::AutoGetFromRefreshToken:
            echo "The refresh token " . (isset($_GET["refresh_token"]) ? $_GET["refresh_token"] : $_POST["refresh_token"]) . " was used to get the access token {$oauth->accessToken()}.\n"; // Note: this value came from the user, if outputting html make sure to filter this.
            break;
        default: case OAuth2::AutoFail:
            echo "Nothing happened" . ($oauth->accessToken() != null ? ", but an access token was already set" : ", an access token is still not set") . ".\n";
            break;
    }
    
    ```
- To get a link to the Login Dialog:
    ```php
    $redirect_url = "http://example.com/facebook/code.php";
    $permissions = Array("email", "user_friends"); // Optional scope array.
    $login_url = $oauth->loginURL($redirect_url, $permissions);
    
    ```
- To get an access token from the code that was returned:
    ```php
    // Must match the $redirect_url given to OAuth2::loginURL() exactly. The user will not be redirected anywhere.
    $redirect_url = "http://example.com/facebook/code.php";
    
    // Returns an object of data returned from the server. This may include a refresh_token.
    $token_data = $oauth->getAccessTokenFromCode($redirect_url);
    
    ```
- To get an access token a refresh token:
    ```php
    // Returns an object of data returned from the server. This may include a new refresh_token.
    $token_data = $oauth->getAccessTokenFromRefreshToken($refresh_token);
    
    ```
- To make API Requests: (The access token will be included automatically).
    ```php
    $method = OAuth2::GET; // Must be OAuth2::GET, OAuth2::POST, OAuth2::PUT or OAuth2::DELETE.
    $url = "/me";
    //$params = Array("fields" => "id,name"); // You can also add an optional array of parameters.
    $request = $oauth->api($method, $url /* , $params = Array() /* , $headers = Array() /* , $auth = false */ );
    try { $request->execute(); } catch(Exception $error) { exit("OAuth Provider returned an error: " . print_r($error, true)); }
    $response_plaintext = $request->response();
    $response_array = $response->responseArray();
    $response_object = $response->responseObject();
    
    ```
- To get / set the current access token:
    You do not need to do this at the start of the script to get the access token from the session, this is done automatically. Also, this function updates the access token in the session.
    ```php
    // Get
    $oauth->accessToken();
    
    // Set
    $oauth->accessToken($new_access_token);
    
    // Set without updating the access token in the session.
    $oauth->accessToken($new_access_token, false);
    
    ```

You can also use these methods in extended classes (subclasses).

Built-in providers
------------
Any other providers please contact me at https://samuelthomas.ml/about/contact and I'll add it as soon as possible.

**Provider**        | **Class**             | **File in /src**          | **Sign-up url**
--------------------|-----------------------|---------------------------|-------------------------
Facebook `U` `P`    | OAuthFacebook         | facebook.class.php        | https://developers.facebook.com/apps/
Google `U` `P`      | OAuthGoogle           | google.class.php          | https://console.developers.google.com/
Microsoft `U` `P`   | OAuthMicrosoft        | microsoft.class.php       | https://account.live.com/developers/applications/create
Yahoo `U` `P`       | OAuthYahoo            | yahoo.class.php           | https://developer.apps.yahoo.com/dashboard/createKey.html
GitHub `U` `P`      | OAuthGitHub           | github.class.php          | https://github.com/settings/applications/new
LinkedIn `U` `P`    | OAuthLinkedin         | linkedin.class.php        | https://www.linkedin.com/developer/apps/new
Amazon `U`          | OAuthAmazon           | amazon.class.php          | https://developer.amazon.com/lwa/sp/overview.html
Disqus `U`          | OAuthDisqus           | disqus.class.php          | https://disqus.com/api/applications/register/
Instagram `U`       | OAuthInstagram        | instagram.class.php       | https://instagram.com/developer/clients/register/
Spotify `U`         | OAuthSpotify          | spotify.class.php         | https://developer.spotify.com/my-applications/#!/applications/create
samuelthomas.ml `U` `P` | OAuthST           | st.class.php              | https://samuelthomas.ml/developer/clients
TeamViewer `U`      | OAuthTeamViewer       | teamviewer.class.php      | https://login.teamviewer.com/nav/api
WordPress.com `U`   | OAuthWordPress        | wordpress.class.php       | https://developer.wordpress.com/apps/new/

All the built-in providers above have an extra method, userProfile `U`, that returns the user's data in an object:
```php
try { $user = $oauth->userProfile(); }
catch(Exception $error) { exit("OAuth Provider returned an error: " . print_r($error, true)); }

// $user == stdClass::__set_state(Array("id" => 1, "username" => "samuelthomas2774", "name" => "Samuel Elliott", "email" => null, "response" => $response_from_server));

```

Some also have a profilePicture method `P`, that returns the user's profile picture and an &lt;img /&gt; tag:
```php
try { $picture = $oauth->userProfile(); }
catch(Exception $error) { exit("OAuth Provider returned an error: " . print_r($error, true)); }

// $picture == stdClass::__set_state(Array("url" => "https://gravatar.com/avatar/?s=50&d=mm", "tag" => "<img src=\"https://gravatar.com/avatar/?s=50&d=mm\" style=\"width:50px;height:50px;\" />"));

```

#### Facebook
The OAuthFacebook class has some additional methods:

- To parse a signed request sent from Facebook when the page is loaded in a page tab or Facebook Canvas:
    ```php
    $signed_request = $oauth->parseSignedRequest(/* $_POST["signed_request"] */);
    
    ```
- To get an object of permissions the user granted:
    ```php
	try { $permissions = $oauth->permissions(); }
    catch(Exception $error) { exit("Facebook returned an error: {$error->getMessage()}\n"); }
    
    if(!isset($permissions->email) || ($permissions->email->granted != true))
        echo "You have &lt;b&gt;not&lt;/b&gt; allowed access to your email address.\n";
    if(!isset($permissions->publish_actions) || ($permissions->publish_actions->granted != true))
        echo "You have &lt;b&gt;not&lt;/b&gt; allowed posting to your timeline.\n";
    
    // To get the response as it was sent:
    $permissions_response = $oauth->permissions(false);
    
    ```
- To check if a permission has been granted:
    ```php
    try {
        if($oauth->permission("email")) echo "You allowed access to your email address.\n";
		else echo "You have &lt;b&gt;not&lt;/b&gt; allowed access to your email address.\n";
    } catch(Exception $error) { exit("Facebook returned an error: {$error->getMessage()}\n"); }
    
    ```
- To get an object of user ids for other apps that are linked to the same business and that the user has ever authorized.
    ```php
    try { $ids = $oauth->ids(); }
    catch(Exception $error) { exit("Facebook returned an error: {$error->getMessage()}\n"); }
    
    if(isset($ids->{$other_app_id})) echo "Your user id for the app {$ids->{$other_app_id}->app_name} is: {$ids->{$other_app_id}->user_id}\n";
    else echo "You have never authorized the app {$other_app_id}.\n";
    
    ```
- To deauthorize the application or removes one permission:
    ```php
    // Deauthorize one permission:
    if(isset($_GET["deauth"]) && ($_GET["deauth"] == "email")) {
        try {
            if($oauth->deauth("email")) echo "This app no longer has access to your email.\n";
            else echo "Something went wrong... this app still has access to your email.\n";
        } catch(Exception $error) { exit("Facebook returned an error: {$error->getMessage()}\n"); }
    }
    
    // Deauthorize all permissions:
    else $oauth->deauth();
    ```
- To get an object of all the pages the user manages:
    ```php
    try { $pages = $oauth->pages(); }
    catch(Exception $error) { exit("Facebook returned an error: {$error->getMessage()}\n"); }
    echo "You have " . count($pages) . " Facebook Pages.\n\n";
    foreach($pages as $page_id => $page) {
        echo "Page ID: {$page->id}\n";
        echo "Page Name: {$page->id}\n";
        echo "Page Access Token: {$page->access_token}\n";
        echo "Page Access Token Permissions: {$page->permissions}\n";
        echo "Page Category: {$page->id}\n";
        
        $oauth_page = new OAuthFacebook($oauth->client()->id, $oauth->client()->secret, Array("access_token" => $page->access_token));
        // You can use the above object to make Graph API requests as the page. When you use a page access token, only the OAuth::api() and OAuth::options() functions will work.
        try { $request = $oauth_page->api("GET", "/{$page->id}"); $request->execute(); }
        catch(Exception $error) { exit("Facebook returned an error: {$error->getMessage()}\n"); }
        echo "API response: {$request->response()}\n\n";
    }
    ```
- To post to the user's timeline:
    ```php
    // Note that the text is from $_POST: Facebook requires that the text here is entered by the user, not prefilled.
    // Apps are not even allowed to suggest text and let the user edit it.
    $post = new stdClass();
    $post->message = $_POST["facebook_post_text"];
    
    try {
        if(($post_id = $oauth->post((array)$post)) !== false) echo "The text you entered was posted to the user's timeline. The post id was {$post_id}.\n";
        else echo "Something went wrong... the text you entered was not posted to the user's timeline.\n";
    } catch(Exception $error) { exit("Facebook returned an error: {$error->getMessage()}\n"); }
    ```

#### samuelthomas.ml
- To get all storage objects:
    ```php
    try { $objects = $oauth->objects(); }
    catch(Exception $error) { exit("samuelthomas.ml returned an error: {$error->getMessage()}\n"); }
    print_r($objects);
    
    ```
- To get a storage object:
    ```php
    try { $object = $oauth->object("name"); }
    catch(Exception $error) { exit("samuelthomas.ml returned an error: {$error->getMessage()}\n"); }
    print_r($object);
    
    ```
- To set a storage object:
    ```php
    try {
        if($oauth->object("name", "value")) echo "Updated storage object.\n";
        else echo "Error updating storage object.\n";
    } catch(Exception $error) { exit("samuelthomas.ml returned an error: {$error->getMessage()}\n"); }
    
    ```
- To delete a storage object:
    ```php
    try {
        if($oauth->object("name", null)) echo "Storage object is deleted.\n";
        else echo "Error deleting storage object.\n";
    } catch(Exception $error) { exit("samuelthomas.ml returned an error: {$error->getMessage()}\n"); }
    
    ```
- To delete all storage objects:
    ```php
    try {
        if($oauth->deleteObjects()) echo "Storage objects are deleted.\n";
        else echo "Error deleting storage objects.\n";
    } catch(Exception $error) { exit("samuelthomas.ml returned an error: {$error->getMessage()}\n"); }
    
    ```

Extending the OAuth2 class.
------------
You can extend the OAuth2 and other classes to add new functions and make existing functions work differently:

```php
require_once __DIR__ . '/oauth-client/src/facebook.class.php';
class My_Extended_Facebook_Class extends OAuthFacebook {
    // Options. Customize default options (optional).
    protected $options = Array(
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
