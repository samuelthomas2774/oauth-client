<?php

namespace OAuth2;

class UserProfile
{
    /**
     * A unique identifier for this account.
     *
     * @var string
     */
    public $id;

    /**
     * A unique identifier for this account to be displayed to the user.
     *
     * @var string
     */
    public $username;

    /**
     * An array of additional IDs for this account.
     *
     * @var string[]
     */
    public $ids = [];

    /**
     * This account's name.
     *
     * @var string
     */
    public $name;

    /**
     * An array of email addresses for this account.
     *
     * @var string[]
     */
    public $email_addresses = [];

    /**
     * An array of phone numbers for this account.
     *
     * @var string[]
     */
    public $phone_numbers = [];

    /**
     * The preferred URL of this account.
     *
     * @var string
     */
    public $url;

    /**
     * Creates a user profile object.
     *
     * @param string $id
     * @param string $username
     * @param string $name
     * @param array $email_addresses
     * @param string $url
     */
    public function __construct(string $id, string $username = null, string $name = null, $email_addresses = [], string $url = null)
    {
        $this->id = $id;
        $this->username = $username;
        $this->name = $name;
        $this->email_addresses = is_array($email_addresses) ? $email_addresses : [$email_addresses];
        $this->url = $url;
    }
}
