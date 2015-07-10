<?php
	/* class OAuthAutoload
	 * /src/autoload.php
	 */
	class OAuthAutoload {
		// static function load(). Tries to load a class by it's filename.
		public static function load($class) {
			// Checks if class is OAuth2, then OAuthRequest,
			// then searches the /src directory if the class name starts with OAuth.
			$class = strtolower($class);
			if($class == "oauth2")
				require_once __DIR__ . '/oauth.class.php';
			elseif($class == "oauthrequest")
				require_once __DIR__ . '/oauthrequest.class.php';
			elseif((substr($class, 0, 5) == "oauth") && file_exists(__DIR__ . "/" . substr($class, 5) . ".class.php"))
				require_once __DIR__ . '/' . substr($class, 5) . '.class.php';
			else return false;
			return true;
		}
		
		public static function register() {
			spl_autoload_register("OAuthAutoload::load");
		}
	}
	
	// Register autoloader
	OAuthAutoload::register();
	