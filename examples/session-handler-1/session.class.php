<?php
	class Session {
		public function __construct() {
			if(session_status() !== PHP_SESSION_ACTIVE)
				session_start();
		}
		
		public function check() {
			return true;
		}
		
		// function get(): Reads session data
		public function get($key) {
			if(isset($_SESSION[$key]))
				return $_SESSION[$key];
			else return null;
		}
		
		// function set(): Writes session data
		public function set($key, $value) {
			$_SESSION[$key] = $value;
		}
		
		// function delete(): Deletes session data
		public function delete($key) {
			$_SESSION[$key] = null;
			unset($_SESSION[$key]);
		}
	}
	