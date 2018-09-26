<?php

namespace OAuth2\Exceptions;

use Throwable;
use Exception;
use stdClass;

class OAuthException extends Exception
{
    static function fromRequest(array $request, Throwable $previous = null)
    {
        $description = isset($response['error_description']) ? $response['error_description'] : $response['error'];

        if (isset($response['error_uri'])) {
            $description .= ' (' . $response['error_uri'] . ')';
        }

        return new self($description, 0, $previous);
    }

    static function fromResponse(stdClass $response, Throwable $previous = null)
    {
        return new self(isset($response->error_description) ? $response->error_description : $response->error, isset($response->error_code) ? $response->error_code : 0, $previous);
    }
}
