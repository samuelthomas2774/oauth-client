Facebook
---

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
