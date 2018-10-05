Discord
---

- To convert an array of permissions to an integer:
    ```php
    $client->permissionsToInteger(['ADMINISTRATOR']); // 8
    ```
- To convert an integer to an array of permissions:
    ```php
    $client->integerToPermissions(8); // ['ADMINISTRATOR']
    ```
- To check if an integer has a permission:
    ```php
    $client->integerHasPermission(8, 'ADMINISTRATOR'); // true
    ```
- To get a URL to add the client's bot to a guild:
    ```php
    $client->inviteBot(['ADMINISTRATOR'], 'https://example.com/discord/code.php'); // OAuth2\AuthoriseUrl
    ```
- To get a URL to add a webhook to a channel:
    ```php
    $client->inviteWebhook('https://example.com/discord/code.php'); // OAuth2\AuthoriseUrl
    ```
