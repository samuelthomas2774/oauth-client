<?php

namespace OAuth2;

class State
{
    protected $id;
    protected $data = [];

    public function __construct(array $data = [], string $id = null)
    {
        if (!$id) {
            $id = self::generateId();
        }

        $this->id = $id;
        $this->data = $data;
    }

    static public function generateId(): string
    {
        return hash('sha256', time() . uniqid(mt_rand(), true));
    }

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
