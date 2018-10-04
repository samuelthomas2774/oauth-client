<?php

namespace OAuth2;

trait ManagesState
{
    /**
     * Generate a state.
     *
     * @param boolean $update_session
     * @return string
     */
    public function generateState(array $state = [], bool $update_session = true): State
    {
        if (!$state instanceof State) {
            $state = new State($state);
        }

        $this->pushState($state, $update_session);

        return $state;
    }

    protected function generateOrPushState($state, bool $update_session = true): State
    {
        if ($state instanceof State) {
            $this->pushState($state, $update_session);
            return $state;
        } else {
            return $this->generateState($state);
        }
    }

    public function getStates(): array
    {
        if (!array_key_exists($this->session_prefix, self::$state)) return [];

        return self::$state[$this->session_prefix];
    }

    public function setStates(array $states, bool $update_session = true)
    {
        self::$state[$this->session_prefix] = $states;

        if ($update_session) {
            $this->session('state', self::$state[$this->session_prefix]);
        }
    }

    public function pushState(State $state, bool $update_session = true)
    {
        $states = $this->getStates();

        foreach ($states as $s) {
            if ($state->getId() === $s->getId()) return;
        }

        array_push($states, $state);

        $this->setStates($states, $update_session);
    }

    public function getStateById(string $id): ?State
    {
        $states = $this->getStates();

        foreach ($states as $state) {
            if ($state->getId() === $id) return $state;
        }

        return null;
    }

    public function getLastStates(): array
    {
        if (!array_key_exists($this->session_prefix, self::$last_state)) return [];

        return self::$last_state[$this->session_prefix];
    }

    /**
     * Returns all valid states from the last request.
     *
     * @param boolean $update_session
     * @return string
     */
    public function getLastStateById(string $id): ?State
    {
        $states = $this->getLastStates();

        foreach ($states as $state) {
            if ($state->getId() === $id) return $state;
        }

        return null;
    }

    protected function ageState(bool $update_session = true)
    {
        if (!array_key_exists($this->session_prefix, self::$last_state)) {
            $last_state = $this->session('state');

            if (is_string($last_state)) $last_state = new State([], $last_state);

            self::$last_state[$this->session_prefix] = $last_state;

            if ($update_session) {
                $this->session('state', self::$state = []);
            }
        }
    }
}
