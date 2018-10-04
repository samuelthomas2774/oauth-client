<?php

namespace OAuth2;

use OAuth2\Grants\AuthorisationCodeGrant;
use OAuth2\Grants\RefreshTokenGrant;
use OAuth2\Grants\ImplicitGrant;
use OAuth2\Grants\ResourceOwnerCredentialsGrant;
use OAuth2\Grants\ClientCredentialsGrant;

use OAuth2\Grants\AuthorisationCodeGrantInterface;
use OAuth2\Grants\RefreshTokenGrantInterface;
use OAuth2\Grants\ImplicitGrantInterface;
use OAuth2\Grants\ResourceOwnerCredentialsGrantInterface;
use OAuth2\Grants\ClientCredentialsGrantInterface;

use TypeError;

use GuzzleHttp\Client as HttpClient;
use Psr\Http\Message\ResponseInterface;

class GenericOAuthProvider extends OAuth implements AuthoriseEndpointInterface, TokenEndpointInterface, AuthorisationCodeGrantInterface, RefreshTokenGrantInterface, ImplicitGrantInterface, ResourceOwnerCredentialsGrantInterface, ClientCredentialsGrantInterface
{
    use AuthoriseEndpoint;
    use TokenEndpoint;

    use AuthorisationCodeGrant;
    use RefreshTokenGrant;
    use ImplicitGrant;
    use ResourceOwnerCredentialsGrant;
    use ClientCredentialsGrant;
}
