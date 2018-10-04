<?php

namespace OAuth2;

class State
{
    /**
     * The state ID. This is used in the state query string parameter.
     *
     * @var string
     */
    protected $id;

    /**
     * Data to store with this state.
     * Property accesses on this object will be proxied to this.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Creates a state object.
     *
     * @param array $data
     * @param string $id
     */
    public function __construct(array $data = [], string $id = null)
    {
        if (!$id) {
            $id = self::generateId();
        }

        $this->id = $id;
        $this->data = $data;
    }

    /**
     * Generates an ID.
     *
     * @return string
     */
    static public function generateId(): string
    {
        return hash('sha256', time() . uniqid(mt_rand(), true));
    }

    /**
     * Returns the state ID.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->id;
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __set($name, $value)
    {
        return $this->data[$name] = $value;
    }
}
