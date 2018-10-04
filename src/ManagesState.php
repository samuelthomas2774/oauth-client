<?php

namespace OAuth2;

trait ManagesState
{
    /**
     * Returns all states for this session prefix.
     *
     * @return array
     */
    public function getStates(): array
    {
        if (!array_key_exists($this->session_prefix, self::$state)) {
            if (!is_array($states = $this->session('state'))) return [];

            self::$state[$this->session_prefix] = $states;
        }

        return self::$state[$this->session_prefix];
    }

    /**
     * Sets all states for this session prefix.
     *
     * @param array $states
     * @param boolean $update_session
     */
    public function setStates(array $states, bool $update_session = true)
    {
        self::$state[$this->session_prefix] = $states;

        if ($update_session) {
            $this->session('state', self::$state[$this->session_prefix]);
        }
    }

    /**
     * Adds a state for this session prefix.
     *
     * @param \OAuth2\State $state
     * @param boolean $update_session
     */
    public function pushState(State $state, bool $update_session = true)
    {
        $states = $this->getStates();

        foreach ($states as $s) {
            if ($state->getId() === $s->getId()) return;
        }

        array_push($states, $state);

        $this->setStates($states, $update_session);
    }

    /**
     * Finds a state by it's ID.
     *
     * @param string $id
     * @return \OAuth2\State
     */
    public function getStateById(string $id): ?State
    {
        $states = $this->getStates();

        foreach ($states as $state) {
            if ($state->getId() === $id) return $state;
        }

        return null;
    }

    /**
     * Returns all the last request's states for this session prefix.
     *
     * @param boolean $age_states
     * @return array
     */
    public function getLastStates($age_states = true): array
    {
        if ($age_states) $this->ageState();

        if (!array_key_exists($this->session_prefix, self::$last_state)) return [];

        return self::$last_state[$this->session_prefix];
    }

    /**
     * Finds a state from the last request with it's ID.
     *
     * @param string $id
     * @return \OAuth2\State
     */
    public function getLastStateById(string $id): ?State
    {
        $states = $this->getLastStates();

        foreach ($states as $state) {
            if ($state->getId() === $id) return $state;
        }

        return null;
    }

    /**
     * Moves all current states to the last request's states.
     *
     * @param boolean $update_session
     */
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
