OAuth Client
===

An OAuth 2.0 Client library with built-in support for Facebook, Google, Microsoft, Yahoo, GitHub, LinkedIn & more.

**[Built-in providers](#built-in-providers)**

- [Facebook](https://facebook.com)
- [Google](https://google.co.uk)
- [Microsoft](https://microsoft.com/en-gb/)
- [Yahoo](https://yahoo.co.uk)
- [GitHub](https://github.com)
- [LinkedIn](https://linkedin.com)
- [Spotify](https://spotify.com)
- [Amazon](https://amazon.co.uk)
- [Disqus](https://disqus.com)
- [Instagram](https://instagram.com)
- [TeamViewer](https://teamviewer.com)
- [WordPress.com](https://wordpress.com)
- [Eventbrite](https://eventbrite.com)
- [Foursquare](https://foursquare.com)
- [GitLab](https://about.gitlab.com)
- [Mastodon](https://joinmastodon.org)
- [Discord](https://discordapp.com)
- [Pinterest](https://pinterest.co.uk)
- [Slack](https://slack.com)
- [DigitalOcean](https://digitalocean.com)
- [Gitter](https://gitter.im)
- [Deezer](https://deezer.com)
- [DeviantArt](https://deviantart.com)
- [Twitch](https://twitch.tv)
- [Vimeo](https://vimeo.com)
- [Reddit](https://reddit.com)
- Other, just create a new OAuth2\\OAuth object and include the base_api_endpoint, authorise_endpoint and token_endpoint options

Requirements and installation
---

This requires at least PHP 7.1.

Version | Supported
--------|----------
PHP 5.6 | No
PHP 7.0 | No
PHP 7.1 | Yes
PHP 7.2 | Yes

To install this, you can either download the .zip or .tar and extract it or use Composer.

### Composer

1. Add `"samuelthomas2774/oauth-client": "~3.0.0"` to your composer.json
    ```json
    {
        "require": {
            "samuelthomas2774/oauth-client": "~3.0.0"
        }
    }
    ```
2. Run Composer  
    This will automatically download the latest patch version.
    ```
    php composer.phar install
    ```

### Download .zip / .tar

1. Download the .zip or .tar file from the [releases](https://github.com/samuelthomas2774/oauth-client/releases) page
2. Extract the files from the downloaded .zip or .tar file
3. Upload the extracted files to your website, or move them to wherever they should be

Usage
---

1. Include `vendor/autoload.php` in all pages that need access to any provider  
    This will load any class in the src directory when used.
    ```php
    require_once __DIR__ . '/vendor/autoload.php';
    ```
2. Create a new `OAuth2\\OAuth` object with the parameters `$client_id`, `$client_secret` and `$options`  
    The `$options` array must have at least `base_api_endpoint`, `authorise_endpoint` and `token_endpoint`.
    ```php
    use OAuth2\OAuth;
    
    $client_id = 'client-id';
    $client_secret = 'client-secret';

    $client = new OAuth($client_id, $client_secret, null, [
        'session_prefix' => 'facebook_',
        'base_api_endpoint' => 'https://graph.facebook.com',
        'authorise_endpoint' => 'https://facebook.com/dialog/oauth',
        'token_endpoint' => 'oauth/access_token',
    ]);
    ```


- To get a link to the authorise page:
    ```php
    $redirect_url = 'http://example.com/facebook/code.php';
    $scope = ['email', 'user_friends']; // Optional scope array
    $login_url = $client->generateAuthoriseUrlAndState($redirect_url, $scope);
    ```
- To get an access token from the code that was returned:
    ```php
    // Must match the $redirect_url given to generateAuthoriseUrl exactly
    // The user will not be redirected anywhere
    $redirect_url = 'http://example.com/facebook/code.php';

    // Returns an object of data returned from the server
    // This may include a refresh_token
    $requested_scope = ['email', 'user_friends']; // Optional requested scope array
    $token = $client->getAccessTokenFromRequestCode($redirect_url, $requested_scope);
    ```
- To get an access token a refresh token:
    ```php
    // Returns an object of data returned from the server
    // This may include a new refresh_token
    // $token can be a string (a refresh token) or an AccessToken object with a refresh token returned by all getAccessToken* methods
    $new_token = $oauth->getAccessTokenFromRefreshToken($token);
    ```
- To make API requests with the access token:
    ```php
    try {
        $response = $client->api('GET', 'me' /*, $guzzle_options = [] /*, $auth = null */);
    } catch (Exception $error) {
        echo 'OAuth provider returned an error: ' . print_r($error, true);
    }
    ```
- To get/set the current access token:
    You do not need to do this at the start of the script to get the access token from the session, this is done automatically. Also, this function updates the access token in the session.
    ```php
    // Get
    $token = $client->getAccessToken();

    // Set
    $client->setAccessToken($new_token);

    // Set without updating the access token in the session
    $client->setAccessToken($new_token, false);
    ```

Built-in providers
---

Any other providers please contact me at https://samuelelliott.ml/#contact and I'll add it as soon as possible.

**Provider**        | **Class**                                     | **Sign-up url**
--------------------|-----------------------------------------------|-------------------------
Amazon              | OAuth2\\Providers\\Amazon\\Amazon             | https://developer.amazon.com/lwa/sp/overview.html
Deezer              | OAuth2\\Providers\\Deezer\\Deezer             | https://developers.deezer.com/myapps/create
DeviantArt          | OAuth2\\Providers\\DeviantArt\\DeviantArt     | https://www.deviantart.com/developers/register
DigitalOcean        | OAuth2\\Providers\\DigitalOcean\\DigitalOcean | https://cloud.digitalocean.com/account/api/applications/new
Discord `P`         | OAuth2\\Providers\\Discord\\Discord           | https://discordapp.com/developers/applications/
Disqus              | OAuth2\\Providers\\Disqus\\Disqus             | https://disqus.com/api/applications/register/
Eventbrite          | OAuth2\\Providers\\Eventbrite\\Eventbrite     | https://www.eventbrite.co.uk/myaccount/apps/new/
Facebook `P`        | OAuth2\\Providers\\Facebook\\Facebook         | https://developers.facebook.com/apps/
Foursquare          | OAuth2\\Providers\\Foursquare\\Foursquare     | https://foursquare.com/developers/apps
GitHub `P`          | OAuth2\\Providers\\GitHub\\GitHub             | https://github.com/settings/applications/new
GitLab `P` `I`      | OAuth2\\Providers\\GitLab\\GitLab             | https://gitlab.com/profile/applications *
Gitter              | OAuth2\\Providers\\Gitter\\Gitter             | https://developer.gitter.im/apps/new
Google `P`          | OAuth2\\Providers\\Google\\Google             | https://console.developers.google.com
Instagram           | OAuth2\\Providers\\Instagram\\Instagram       | https://instagram.com/developer/clients/register/
LinkedIn `P`        | OAuth2\\Providers\\Linkedin\\Linkedin         | https://www.linkedin.com/developer/apps/new
Mastodon `P` `I`    | OAuth2\\Providers\\Mastodon\\Mastodon         | https://mastodon.social/settings/applications/new *
Microsoft           | OAuth2\\Providers\\Microsoft\\Microsoft       | https://account.live.com/developers/applications/create
Pinterest           | OAuth2\\Providers\\Pinterest\\Pinterest       | https://developers.pinterest.com/apps/
Reddit              | OAuth2\\Providers\\Reddit\\Reddit             | https://www.reddit.com/prefs/apps
Slack `P`           | OAuth2\\Providers\\Slack\\Slack               | https://api.slack.com/apps
SpeechMore `P`      | OAuth2\\Providers\\SpeechMore\\SpeechMore     | https://speechmore.ml/settings/api-clients
Spotify             | OAuth2\\Providers\\Spotify\\Spotify           | https://developer.spotify.com/my-applications/#!/applications/create
TeamViewer          | OAuth2\\Providers\\TeamViewer\\TeamViewer     | https://login.teamviewer.com/nav/api
Twitch              | OAuth2\\Providers\\Twitch\\Twitch             | https://dev.twitch.tv/dashboard/apps/create
Vimeo               | OAuth2\\Providers\\Vimeo\\Vimeo               | https://developer.vimeo.com/apps/new
WordPress.com       | OAuth2\\Providers\\WordPress\\WordPress       | https://developer.wordpress.com/apps/new/
Yahoo `P`           | OAuth2\\Providers\\Yahoo\\Yahoo               | https://developer.apps.yahoo.com/dashboard/createKey.html

\* This URL is for the default instance used

All the built-in providers implement `OAuth2\UserProfilesInterface`, which adds an extra method to get a `OAuth2\UserProfile` object.

```php
try {
    $user = $client->getUserProfile();
} catch (Exception $error) {
    echo 'OAuth provider returned an error: ' . print_r($error, true);
}
```

Some also implement `OAuth2\UserPicturesInterface` (`P`), which adds a method to get the URL of the user's picture/avatar.

```php
try {
    $picture_url = $client->getUserPictureUrl();
} catch (Exception $error) {
    echo 'OAuth provider returned an error: ' . print_r($error, true);
}
```

As GitLab and Mastodon support multiple instances they implement `OAuth2\MultipleInstancesInterface`, which adds an option and method to set the instance's URL.

```php
use OAuth2\Providers\GitLab\GitLab;

$client = new GitLab($client_id, $client_secret, null, [
    'instance_url' => 'https://gitlab.fancy.org.uk',
]);

$client->setInstanceUrl('https://gitlab.com');
```

### Facebook

The OAuthFacebook class has some additional methods:

- To parse a signed request sent from Facebook when the page is loaded in a page tab or Facebook Canvas:
    ```php
    $signed_request = $client->parseSignedRequest(/* $_POST["signed_request"] */);
    ```
- To get an object of permissions the user granted:
    ```php
    try {
        $permissions = $client->permissions();

        if (!isset($permissions['email']) || $permissions['email']->granted) {
            echo 'You have <b>not</b> allowed access to your email address.<br />';
        }
        if (!isset($permissions['publish_actions']) || $permissions['publish_actions']->granted) {
            echo 'You have <b>not</b> allowed posting to your timeline.<br />';
        }
    } catch (Exception $exception) {
        echo 'Facebook returned an error: ' . $exception->getMessage();
    }
    ```
    ```php
    // To get the response as it was sent:
    $permissions_response = $client->permissions(false);
    ```
- To check if a permission has been granted:
    ```php
    try {
        if ($client->permission('email')) {
            echo 'You have allowed access to your email address.<br />';
        } else {
            echo 'You have <b>not</b> allowed access to your email address.<br />';
        }
    } catch (Exception $exception) {
        echo 'Facebook returned an error: ' . $exception->getMessage();
    }
    ```
- To get an object of user IDs for other apps that are linked to the same business and that the user has ever authorised:
    ```php
    try {
        $ids = $client->ids();

        if (isset($ids[$other_app_id])) {
            echo 'Your user ID for ' . htmlentities($ids[$other_app_id]->app_name) . ' is ' . htmlentities($ids[$other_app_id]->user_id) . '<br />';
        } else {
            echo 'You have never authorised the app ' . $other_app_id . '<br />';
        }
    } catch (Exception $exception) {
        echo 'Facebook returned an error: ' . $exception->getMessage();
    }
    ```
    ```php
    // To get the response as it was sent:
    $ids_response = $client->ids(false);
    ```
- To deauthorise the application or remove one permission:
    ```php
    // Revoke one permission:
    try {
        if ($client->deauth('email')) echo 'This app no longer has access to your email address';
        else echo 'Something went wrong... this app still has access to your email address';
    } catch (Exception $exception) {
        echo 'Facebook returned an error: ' . $exception->getMessage();
    }
    ```
    ```php
    // Revoke all permissions:
    try {
        $client->deauth();
    } catch (Exception $exception) {
        echo 'Facebook returned an error: ' . $exception->getMessage();
    }
    ```
- To get an object of all the pages the user manages:
    ```php
    use OAuth2\Providers\Facebook\Facebook;

    try {
        $pages = $client->pages();

        echo 'You have ' . count($pages) . ' Facebook Pages.<br /><br />';

        foreach ($pages as $page_id => $page) {
            echo 'Page ID: ' . htmlentities($page->id) . '<br />';
            echo 'Page Name: ' . htmlentities($page->name) . '<br />';
            echo 'Page Access Token: ' . htmlentities(print_r($page->access_token, true)) . '<br />';
            echo 'Page Access Token Permissions: ' . htmlentities(print_r($page->permissions, true)) . '<br />';
            echo 'Page Category: ' . htmlentities($page->category) . '<br /><br />';

            try {
                $page_client = new Facebook($client_id, $client_secret, $page->access_token);

                // You can use the above object to make Graph API requests as the page
                // When you use a page access token, only the OAuth::api() and OAuth::options() functions will work
                $response = $page_client->api('GET', $page->id);

                echo 'API response: ' . htmlentities(print_r($response, true)) . '<br /><br />';
            } catch (Exception $exception) {
                echo 'Facebook returned an error: ' . $exception->getMessage();
            }
        }
    } catch (Exception $exception) {
        echo 'Facebook returned an error: ' . $exception->getMessage();
    }
    ```
- To post to the user's timeline:
    ```php
    // Note that the text is from $_POST: Facebook requires that the text here is entered by the user, not prefilled
    // Apps are not even allowed to suggest text and let the user edit it
    $post = new stdClass();
    $post->message = $_POST["facebook_post_text"];
    
    try {
        if(($post_id = $oauth->post((array)$post)) !== false) echo "The text you entered was posted to the user's timeline. The post id was {$post_id}.\n";
        else echo "Something went wrong... the text you entered was not posted to the user's timeline.\n";
    } catch(Exception $error) { exit("Facebook returned an error: {$error->getMessage()}\n"); }
    try {
        $post_id = $client->post($_POST['facebook_post_text']);
        
        if ($post_id !== false) {
            echo 'The text you entered was posted to the user\'s timeline. The post ID is ' . $post_id . '.';
        } else {
            echo 'Something went wrong... the text you entered was not posted to the user\'s timeline.';
        }
    } catch (Exception $exception) {
        echo 'Facebook returned an error: ' . $exception->getMessage();
    }
    ```

Extending the OAuth2 class
---

You can extend the OAuth2 and other classes to add new functions and make existing functions work differently:

```php
use OAuth2\Providers\Facebook\Facebook;

class My_Extended_Facebook_Class extends Facebook
{
    // Change the base_api_endpoint option to use a different API version
    public $base_api_endpoint = 'https://graph.facebook.com/v1.0/';

    // Add a new function for getting the current user's ID
    public function getUserId(): string
    {
        $user = $this->getUserProfile(['id']);
        return $user->id;
    }
}
```

You can then use the newly created class:

```php
$client = new My_Extended_Facebook_Class('client-id', 'client-secret');

try {
    echo 'Your Facebook User ID (for this app) is: ' . htmlentities($client->getUserId());
} catch (Exception $exception) {
    echo 'Facebook returned an error: ' . $exception->getMessage();
}
```
