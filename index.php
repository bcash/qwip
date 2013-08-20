<?php

	/*

	QWhat is QWIP?

	Qwip stands for a quick webapps integration platform. A toolkit for people that build and manage sites using PHP.

	It's goal is to facilitate agile adoption of disparate code sources quickly. 
	
	By providing centralized theming, authentication, and other common services, qwip should 
	help you integrate code from multiple locations into your site as quickly as possible, but 
	without compromising security, performance, and design integrity.

	Design principles:
	
		Modular core.  Run as light or as heavy as needed for your infrastructure.  Avoid WordPress-style bloat or 
						CodeIgniter-style complexity.  Assumptions may be required.  Includes only.  No eval statements.

		Themes - Provide a theme system designed to be very similar to WordPress.  WordPress is the most popular design system
  				 with thousands of design-developers in the market.  QWIP is designed for maximum third party designer 
  				 participation.  Provide great WordPress theme conversion tutorials and a theme repository.  Parent themes
  				 are supported.

		Users - Allow override of user authentication, session management, session and user data for any application.
				Integrate with other open ID providers for single sign-on over mutiple sites.

		DB independent - Don't assume we're always going to use mysql, or anything else.  Show child class examples in nosql.
		
		Simplest Use Cases - Demonstrate our platform with the simplest use cases.
		
		Flexible Paths - Codebase and components should be allowed anywhere in the file system.  
							Organize your project the way you want.
		
		Obvious Errors - Show errors loud and proud.  Provide maximum help to resolve during configuration.  Make performance 
							monitoring and debugging easy.
		
		Agility - You don't always need a "framework".  Most small and medium sized projects don't require giant frameworks
					and for most projects they get in the way.  QWIP is designed to just do it's job and get out of the way.

		Quickest installation - Provide quickest start for common use cases.

		Quickest App Development - Optimize speed to app.

		Quickest App Integration - Optimize speed to integration.  Allow for very quick open source and third party integration.
									The QWIP mantra during integration is to "trim the fat".  Because it's always easier to 
									remove unneeded code rather than write new code.
		
		Standards - Take maximum advantage of packaged utilities via composer.  Integrate perfectly itself.
		
		Integrated debugging - Utilize standard debugging methods by integrating via composer.

		Static Class Object - Recommend the static class approach because we should only have one master controller and it is 
							  annoying to pass around app handles.  Still, QWIP will work in standard object-oriented mode.

		Automated testing - Should pass all automated tests that are continually developed.

	QWIP HQ - https://github.com/bcash/qwip

	qwipit.org - examples of qwip'd applications and their seamless integration.  wordpress, simple scripts, cms apps

	*/


	// Base class should retain lightest possible framework using only native php.  
	// 
	// Review example child classes for mysql, SimpleDB, OpenID implementations.
	// 

class QWIP {

	// Self
	public static $instance;

	// Paths to QWIP resources
	public static $paths;

	// Timer tracking performance
	public static $timer;

	// App instance and meta info
	public static $app;
	
	// Route information to delegate to our app
	public static $routes;

	// Macros that are populated by our app and rendered within our theme.
	public static $macros;
	
	// Theme name and path info
	public static $theme;


	// Should probably set paths and default variables in constructor.  Allow overrides.
	//
	public static function __construct( $upaths ) {

		// The instance handle / housekeeping
		self::$instance =& $this;

		$this->timer['start'] = microtime( true );
	
		// Set paths.

		// Define some constants that are not overridable.
		define('DS', DIRECTORY_SEPARATOR );
		define('QWIP_SELF', basename(__FILE__) );
		define('QWIP_BASE_PATH',realpath('.'));
		define('QWIP_BASE_URL', dirname($_SERVER["SCRIPT_NAME"]));	

		// Config defaults	
		$this->path['url'] = QWIP_BASE_URL;
		// $this->path['cdn'] = '';
		$this->path['app'] = QWIP_BASE_PATH . DS . 'app';
		$this->path['sys'] = dirname( QWIP_BASE_PATH ) . DS . 'lib';
		$this->path['themes'] = QWIP_BASE_PATH . DS . 'themes';

		// Set overrides here
		if( isset( $upaths ) && is_array( $upaths ) ) {
			$this->path = array_merge( $this->path, $upaths );
		}

		// Check the path to the "application" folder if overridden in the config file
		if ( is_dir($this->path['app']) ) {
			define('QWIP_APP_PATH', $this->path['app'] );
		} else {
			exit( "Your application folder path does not appear to be set correctly. " ); 
		}
	
		// Check the path to the "lib" folder
		if ( is_dir($this->path['lib']) ) {
			define('QWIP_LIB_PATH', $this->path['lib'] );
		} else {
			exit( "Your library folder path does not appear to be set correctly. " );
		}
	
		// Check the path to the "sys" folder
		if ( is_dir( $this->path['sys'] ) ) {
			define('QWIP_SYS_PATH', $this->path['sys'] );
		} else {
			exit( "Your system folder path does not appear to be set correctly. " );
		}
	
		// Check the path to the "themes" folder
		if ( is_dir($this->path['themes']) ) {
			define('QWIP_THEMES_PATH', $this->path['themes'] );
		} else {
			exit( "Your theme folder path does not appear to be set correctly. " );
		}

		return;
	}


	public static function session_start() {

		// Session is not loaded yet
		$this->session_preload();

		# Start the normal php session
		session_start();
		
		// If user wants to be logged out, fully reset our session
		if( isset($_GET['logout']) ) {
			$this->logout();
		}
		
		// Start user authentication
		//
		$_SESSION['action'] = 'logout';	  
	
		if (!isset($_SESSION['account_id'])) {

			// header( "x-validate: running config_on_validate" );
			$this->rememberme();

			if (!isset($_SESSION['account_id'])) {
		        $_SESSION['identity']['token'] = 'invalid/expired'; 
			} else {
		        $_SESSION['identity']['token'] = 'remembered'; 
			}
		}
	
		// If not logged in, authenticate against open ID.
		// 
		if (!isset($_SESSION['account_id'])) {
	
			// Clear all session identity info
			unset( $_SESSION['identity'] );
	
			// Resetting the session?
			$_SESSION['action'] = 'login';
			if ( !isset( $_SESSION['clicks'] ) ) {
			  $_SESSION['clicks'] = 0;  
			}
	
			$this->authenticate();
	
		} else {
	
			$current_cookie = null;
			if ( isset( $_COOKIE['PHPSESSID'] ) ) {
				$current_cookie = $_COOKIE['PHPSESSID'];
			}
			// Extend the session by session length with each click.		
			setcookie( 'PHPSESSID', $current_cookie, time() + SESSION_SECONDS, '/' );
			
			// This will load session based on a valid auth token.
			$this->on_authenticated();
			
		}

		// Session is now loaded
		$this->session_postload();
		
		return;		
	}
	
	
	// Loads user, session, runs app
	public static function run() {

		// Read routes
		$this->getroute();
	
		// Load object info (reads zero value in route request)
		$this->loadapp();
	
		// Run our app
		if ( $this->app['instance']->authreq && !isset($_SESSION['account_id']) ) {
	
			// Auth is required, yet user is not authenticated.
			$this->auth_redirect();
			
		} else {
			
			// If action exists, run app and requested action, otherwise, pass through to app 
			// and let it handle interpretation of this action.
			$action = 'index';
			if ( isset( $this->app['instance']->action ) ) {
				$action = $this->app['instance']->action;
			}
			if ( !method_exists( $this->app['instance'], $action ) ) {		
				$action = 'index';
				if ( !method_exists( $this->app['instance'], $action ) ) {		
					exit("Cannot find index method file for app. " 
									. "Missing method in: " . $this->app['instance'] );
				}
			}
			$this->app['instance']->$action();	
	
			// Consolidate on_ready into footer		
			if ( isset( $this->app['instance']->on_ready ) ) {
			
				$allscripts = '';
				foreach( $this->app['instance']->on_ready as $idx => $script ) {
					$allscripts .= "\n" . $script;
				}
	
				$newhead = "
	
					<!-- Set by adding to this->obj->app->on_ready[] from the editor -->
				    <script>
				    $(document).ready(function() {
				    	{{ALLSCRIPTS}}
				    });
				    </script>			
				    ";
	
				$this->app['instance']->export['footer'] .= str_replace( '{{ALLSCRIPTS}}', $allscripts, $newhead );
	
			}
			
			// Exported data is now added to our sys array
			if ( isset( $this->app['instance']->export ) && !empty( $this->app['instance']->export ) ) {
				foreach( $this->app['instance']->export as $var => $val ) {
					// Populate our theme with app exported macros.
					$this->macros[$var] = $val;
				}
			}
	
			// If this is not an ajax call, eval the theme.
			if ( !$this->is_ajax() ) {
	
				// Set default theme (app may decide theme)
				$this->theme['name'] = $this->macros['theme'];
	
				// Load theme
				if ( isset( $this->theme['name'] ) && !empty( $this->theme['name'] ) ) {
					$this->load_themes();
				}
				
				if ( isset( $this->theme['name'] ) && !empty( $this->theme['name'] )  ) {
				
					// Include theme functions
					if ( !empty( $this->theme['includes'] ) ) {
						foreach ( $this->theme['includes'] as $include_file ) { 
							if ( file_exists( $include_file ) ) {
								include_once( $include_file );
							} else {
									exit("Cannot find functions file for theme. " 
													. "Missing file: " . $include_file );
							}
						}
					}  
				
					// Now, finally, include our index.php from our theme to start the chain reaction.
					if ( !isset( $this->theme['files']['index.php'] ) || empty( $this->theme['files']['index.php'] ) ) {
							exit("Cannot find theme file for theme. " 
											. "Missing index.php file in " .  $this->theme['name'] );
					}
	
					// Start Theme processing.
					include_once( $this->theme['files']['index.php'] );
	
				}
				
			}
			
			// If request was ajax, we've already run.  Ajax methods should simply print output.
			
			// This may save user persistence data.
			$this->saveuser();
			
		}
	
		
		return;		
	}




	// Saves session data
	
	public static function __destruct() {
		// print "Destroying " . $this->name . "\n";
		
		return;
	}	
	
	
	public static function logout() {
	
		// Unset all of the session variables.
		$_SESSION = array();
	    // Clear cookie
		if (ini_get("session.use_cookies")) {
		    $params = session_get_cookie_params();
		    setcookie(session_name(), '', time() - SESSION_SECONDS,
		        $params["path"], $params["domain"],
		        $params["secure"], $params["httponly"]
		    );
		}	  

		// Set the cookie with information
		setcookie('authentication', "", time() - 3600, '/' );
		
		// Start a new, empty session and cookie	  
		session_regenerate_id();
		session_destroy();		
		
		// Redirect to the home page
		header( "Location: " . $this->path['url'] );
		exit;
		
		return;		
	}

	
	public static function getroute() {

		// Read the route from the URL
		$req = explode('/', $_SERVER['REQUEST_URI']);
		$scr = explode('/', $_SERVER['SCRIPT_NAME']);
		
		for($i= 0;$i < sizeof($scr);$i++) {
			if ( $req[$i] == $scr[$i] ) {
				unset($req[$i]);
			}
		}
	
		// Save Requests
		$this->routes['req'] = array_values( $req );	  
	
		return;
	}


	// Runs before session is loaded.	
	public static function session_preload() {
		
		return;
	}

	
	public static function session_postload() {

		// Count clicks in this session
		if ( isset( $_SESSION['clicks'] ) ) {
			$_SESSION['clicks'] = $_SESSION['clicks'] + 1;
		} else {
			$_SESSION['clicks'] = 1;  
		}

		return;		
	}

	
	public static function authenticate() {
	
		// Not logged in, so load openid
		require 'lib/openid.php';

		try {

		    $openid = new LightOpenID( $_SERVER['HTTP_HOST'] );
		    if(!$openid->mode) {
	
			    	// Allow a login if they are not already logged in
		        if(isset($_GET['login'])) {

		            $openid->identity = 'https://www.google.com/accounts/o8/id';
		            $openid->required = array(
		            					'namePerson/first', 
		            					'namePerson/last', 
		            					'contact/country/home', 
		            					'pref/language', 
		            					'contact/email'
		            			); 
		            header('Location: ' . $openid->authUrl());
		            exit;

		        }
		        
		    } else if ($openid->mode == 'cancel') {
		        echo 'User has canceled authentication!';
		        $_SESSION['identity']['status'] = 'canceled'; 
		    } else {
		    
	    		// echo 'User login validated.';
	    		if ( $openid->validate() ) {
	    		
			        // Update our session with this info
			        $_SESSION['identity']['status'] = 'logged in'; 
			        $_SESSION['identity'] = $openid->getAttributes();
			        $_SESSION['identifier'] = $openid->identity;
			        $_SESSION['action'] = 'logout';
			        
			        // First update our profile, with what we know
			        $_SESSION['account_id'] = $_SESSION['identifier'];
			        
			        $_SESSION['profile']['email'] = $_SESSION['identity']['contact/email'];
	        		$_SESSION['profile']['name']  = $_SESSION['identity']['namePerson/first'] . ' ' 
	        										. $_SESSION['identity']['namePerson/last'];
	        		$_SESSION['profile']['fname'] = $_SESSION['identity']['namePerson/first'];
	        		$_SESSION['profile']['lname'] = $_SESSION['identity']['namePerson/last'];
	
					// Next, run specific installation validation 	
					$this->on_validate();
	
			        // Decrement the clicks to make up for redirect
			        $_SESSION['clicks'] = $_SESSION['clicks'] - 1;
		        
	    		} else {
		    	    echo 'User ' . $openid->identity . ' has not logged in.';
	    		}
		    }
	
		} catch(ErrorException $e) {
			echo $e->getMessage();
		}

		return;
	
	}
	


	public static function rememberme() {
	
		global $User;

		$this->showxhead( __FUNCTION__, "OK." );

		if ( isset($_COOKIE['authentication']) ) {

			$this->showxhead( __FUNCTION__, "Have a cookie value: " . $_COOKIE['authentication'] );

		    // cookie is set, lets see if its valid and log someone in
		    list( $identifier, $token ) = explode(':', $_COOKIE['authentication'] );

		    if ( ctype_digit( $identifier ) && ctype_alnum( $token ) ) {

				$this->showxhead( __FUNCTION__, "id:" . $identifier . "." );
				$this->showxhead( __FUNCTION__, "auth:" . $token . "." );
		    
				list( $app_user_id, $appname, $appemail ) = DB::queryFirstList(  
											"SELECT id as user_id, name, email FROM %l
		                               		 WHERE id = %i AND auth_id = %s",
		                              TABLE_USERS,
		                              $identifier,
		                              $token
		                            );

				$this->showxhead( __FUNCTION__, " id:" . $app_user_id . "." );
				$this->showxhead( __FUNCTION__, " name:" . $appname . "." );
				$this->showxhead( __FUNCTION__, " email:" . $appemail . "." );
	        
		        if ( isset( $app_user_id ) && !empty( $app_user_id ) 
		        		&& isset( $appname ) && !empty( $appname )
		        		&& isset( $appemail ) && !empty( $appemail ) ) {

					$this->showxhead( __FUNCTION__, " User info loaded ok." );

					$_SESSION['account_id'] = $app_user_id;
					$_SESSION['profile']['userid'] = $app_user_id;
					$_SESSION['profile']['user_id'] = $app_user_id;
					$_SESSION['profile']['email'] = $appemail;
					$_SESSION['profile']['name'] = $appname;
			        $_SESSION['identity']['status'] = 'logged in'; 
		        
					// Ok, now load user, should exist.  Do not auto create if user does not exist.
					$this->loaduser( false );
					
					


		        } else { 

					$this->showxhead( __FUNCTION__, " User info not loaded." );

		        }

			} else {
				$this->showxhead( __FUNCTION__, "Bad Types.  Bailing." );
			}
		} else {

			$cookievals = implode( ',', array_keys( $_COOKIE ) );
			$this->showxhead( __FUNCTION__, "Cookie keys: " . $cookievals );

		}

		return;	
	}



	//
	// On Authenticated is run when user is already authorized to run.  
	// It is not run when first authenticating.  That would be config_on_validate.
	// On authenticated may be configured to integrate with other databases and tables
	// Should generate and issue a remember me token and cookie here
	// 
	
	public static function on_authenticated() {
	
		global $User;
		global $_SESSION;

		// load user if exists.  Do not auto create if user does not exist.
		$this->loaduser( false);
		
		return;
	}



	  // Note: DO NOT Send extra headers as it mess up the google authentication.
	
	
	//
	//  ==== Supporting Theme Functions ==== 
	//
	
	public static function is_ajax() {
		if (isset($_REQUEST['noajax']) )  {
			return false;
		}
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=="XMLHttpRequest") {
				return true;
		} else if (isset($_GET['forceajax']) && $_GET['forceajax'] == true )  {
				return true;
		}
		return false;
	}
	
	
	
	public static function auth_redirect() {
	
		if ( file_exists( $this->theme['files']['noauth.php'] ) ) {
	
			// Show our theme's 404 template if it exists.
			header('401 Authorization Required');
	
			include_once( $this->theme['files']['noauth.php'] );
			
		} else {
		
			// Show default 404 message as a last resort.
			// header('302 Authorization Required');
			header("Location: /?auth_required=true", TRUE, 302 );
			
		}
	
		return;
	}
	
	
	
	//
	//  On Validate may be configured to integrate with other databases and tables
	//  Should generate and issue a remember me token and cookie here
	// 
	
	public static function on_validate() {
	
		global $User;
		global $_SESSION;
	
		// If alpineinternet, allow in and set up user record. 
		if ( true === strpos( $_SESSION['profile']['email'], '@alpineinternet.com' ) 
			) {

			// Load user data here, autoadd if they don't already exist.
			$this->loaduser( true );
		
		} else {

			// Ok, now load user if exists.  Do not auto create if user does not exist.
			$this->loaduser( false);
			
			if ( !isset( $User ) ) {
				
				print "<div class='alert'>Sorry, this account is not authorized to use this service.  "
					." Try logging out of any personal accounts and into your authorized account.</div>";
			
				unset( $_SESSION['profile'] );	
				unset( $_SESSION['identity'] );	
				unset( $_SESSION['identifier'] );	
				unset( $_SESSION['account_id'] );	
				
			} else {
				
				// User info is loaded.  User is now logged in.
				
			}
	
		}
		
		return;
	}

	
	
	
	public static function get_404() {
	
		if ( isset( $this->theme ) 
				&& isset( $this->theme['files'] ) 
				&& isset( $this->theme['files']['404.php'] ) 
				&& file_exists( $this->theme['files']['404.php'] ) ) {
	
			// Show our theme's 404 template if it exists.
			header('404 Not Found');
	
			// Start output buffer
			ob_start();
			include_once( $this->theme['files']['404.php'] );
			// Close output buffer, save to body.
			$out = ob_get_contents();
			ob_end_clean();				
	
		} else {
			// Show default 404 message as a last resort.
			header('404 Not Found');
			$out = "<H1>Sorry, the requested page is not available.</H1>";
			$out .= "<p>We could not find the page here.  Try something else?</p>";
			$out .= "<p>If this is a protected page, you might need to log in.</p>";
		}
	
		return $out;
	}
	
	
	
	public static function missing_app() {
	
		// Show default 404 message as a last resort.
		header('404 Not Found');
		
		$out = "<H1>Sorry, the requested app is not available.</H1>";
		$out .= "<p>We could not find the app " . $this->app['path'] . ".  Try something else?</p>";
		$out .= "<p>If this is a protected app, you might need to log in.</p>";
	
		print $out;
	
		return;
	}
	
	
	
	public static function get_header() {
	
		if ( isset( $this->theme['files']['header.php'] ) && !empty( $this->theme['files']['header.php'] ) ) {
			include_once( $this->theme['files']['header.php'] );
		}
	
		return;
	}
	
	
	public static function get_content() {
	
	
		if ( isset( $this->theme['files']['content.php'] ) && !empty( $this->theme['files']['content.php'] ) ) {
			include_once( $this->theme['files']['content.php'] );
		} else if ( isset( $this->macros['body'] ) && !empty( $this->macros['body'] ) ) {
			print $this->macros['body'];
		} else {
			print "<BR>WARNING: Missing content. App " . $this->app['name'] 
						. " should populate it's export array (especially body).";
		}
	
		return;
	}



	// 
	//  User has been authenticated, load User array from storage.
	//
	public static function loaduser( $autoadd = false ) {
		
		global $User;
		
		$this->showxhead( __FUNCTION__, "Loading user data" );

		// Store error handler, throw exception instead
		$old_handler = DB::$error_handler;

		DB::$error_handler = false; // since we're catching errors, don't need error handler
		DB::$throw_exception_on_error = true;
		
		$maxtries = 3;
		while ( $maxtries > 0 ) {
			
			try {

				$this->showxhead( __FUNCTION__, "try " . $maxtries );

				// Grab related user info here.
		        $sql = "SELECT storage, id as user_id,name,email,
								created_at FROM %l 
							WHERE email = %s";

				$this->showxhead( __FUNCTION__, "email " . $_SESSION['profile']['email'] );
							
				$myUser = DB::queryFirstRow( $sql, TABLE_USERS, $_SESSION['profile']['email'] );
				if ( isset( $myUser ) && is_array( $myUser ) ) {

					$this->showxhead( __FUNCTION__, "OK: " . strlen( $myUser['storage'] ) . " bytes loaded." );
					$User = unserialize( $myUser['storage'] );
					unset( $myUser['storage'] );	

					$User['Account'] = $myUser;	// Update remainder of user account info.
					$this->showxhead( __FUNCTION__, "OK: User ID " . $User['Account']['user_id'] . " loaded." );

				} else {

					if ( $autoadd  ) {

						// Should only be alpine users.						
						$this->showxhead( __FUNCTION__, "NOTE: user not found.  Adding user." );
	
						DB::insert( TABLE_USERS, 
							array( 
									'email' => $_SESSION['profile']['email'],
									'name' => $_SESSION['profile']['name'],
									'created_at' => DB::sqleval("NOW()")
								)
						);
	
						// Try again
						continue;
						
					} else {
					
						$this->showxhead( __FUNCTION__, "NOTE: user not found.  Not adding user." );
						unset( $User );
						
					}
					
				}

				// Don't need to try again
				break;
	
			} catch( MeekroDBException $e ) {

				$this->showxhead( __FUNCTION__, "CATCH: start" );
	
				$msg = $e->getMessage();
	
				// Create users table if table not found
				if ( preg_match( '/Table /', $msg ) && preg_match( "/doesn't exist/", $msg ) ) {
					
					// print "<br>Should add this table.";	
					$this->showxhead( __FUNCTION__, "FAIL: table notfound" );
					
					$sql =<<<__EOT__

						CREATE TABLE IF NOT EXISTS %l (
						  user_id int(11) unsigned NOT NULL AUTO_INCREMENT,
						  name varchar(50) NOT NULL DEFAULT '',
						  email varchar(85) NOT NULL DEFAULT '',
						  is_bot tinyint(1) NOT NULL DEFAULT '0',
						  fullname varchar(255) DEFAULT NULL,
						  roles text,
						  manager_id int(11) unsigned NOT NULL DEFAULT '0',
						  storage mediumtext,
						  auth_id varchar(32) DEFAULT NULL,
						  is_anonymous tinyint(1) NOT NULL DEFAULT '1',
						  is_confirmed tinyint(1) NOT NULL DEFAULT '0',
						  is_remember tinyint(1) NOT NULL DEFAULT '0',
						  is_email_confirmed tinyint(1) NOT NULL DEFAULT '0',
						  language varchar(10) DEFAULT NULL,
						  browser varchar(25) DEFAULT NULL,
						  version varchar(5) DEFAULT NULL,
						  platform varchar(25) DEFAULT NULL,
						  domain varchar(48) DEFAULT NULL,
						  host varchar(48) DEFAULT NULL,
						  prev_visits int(11) NOT NULL DEFAULT '0',
						  ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
						  os varchar(10) DEFAULT NULL,
						  menu_id int(11) unsigned NOT NULL DEFAULT '21',
						  time_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						  workflow_state_id int(11) NOT NULL DEFAULT '0',
						  PRIMARY KEY (user_id),
						  KEY name (name),
						  KEY email (email)
						) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

__EOT__;

					// Restore original handler
					DB::$error_handler = $old_handler;
					DB::$throw_exception_on_error = false;
				
					DB::query( $sql, TABLE_USERS );

					$this->showxhead( __FUNCTION__, "CATCH: table added.  Trying again." );

					// Retry try / catch					
					DB::$error_handler = false; // since we're catching errors, don't need error handler
					DB::$throw_exception_on_error = true;
					
				} else {
					
					$query = $e->getQuery();
					
					$this->showxhead( __FUNCTION__, "FAIL: sql failed: " . $msg );	// Throw SQL error to syslog.  Show dumb message to user.
					syslog ( LOG_ALERT , "SQLFAIL: " . DB::$dbName . '/' . basename( __FILE__ ) . ':' . $msg . ' ' . $query );
					header( 'HTTP/1.1 525 Server Error' );
					// echo "DB Error: " . $e->getMessage() . "<br>\n"; 		// Show error message to end user.
					exit;

				}
				
				// d( $e );
				
				// Create user storage record if email not found.
	
				// echo "Error: " . $e->getMessage() . "<br>\n"; // something about duplicate keys
				// echo "SQL Query: " . $e->getQuery() . "<br>\n"; // INSERT INTO accounts...
	
				// exit;
				
			}

			// Try again?
			$maxtries--;
		}

		if ( $maxtries < 1 ) {
		
			$this->showxhead( __FUNCTION__, "FAIL: Failed to Load user data." );	// Throw SQL error to syslog.  Show dumb message to user.
			syslog ( LOG_ALERT , "FAIL: Failed to Load user data." );
			header( 'HTTP/1.1 525 Server Error.  Unable to load user data.' );
			exit;
			
		}
		
		// Restore original handler
		DB::$error_handler = $old_handler;
		DB::$throw_exception_on_error = false;
		
		// Save updated cookie and save to DB
		$this->sendcookie();		
		
		$this->showxhead( __FUNCTION__, "DONE Loading user data" );
		
		return;
	}



	// Update our cookie and sync to DB.
	public static function sendcookie() {
	
		global $User;

		// Send a cookie out with auth info for next time.
		$identifier = $User['Account']['user_id'];

		// create a random key
		$key = md5( uniqid(rand()), false );
		
		// calculate the time in 7 days ahead for expiry date
		$timeout = time() + 60 * 60 * 24 * 7;

		// Set the cookie with information
		setcookie('authentication', "$identifier:$key", $timeout, '/' );

		$User['Account']['auth_id'] = $key;

		// now update the database with the new information
		DB::update( TABLE_USERS, 
			array( 'auth_id' => $key ),
			'id = %i', $identifier );
		
		return;	
	}

	public static function showxhead( $fn, $msg ) {
		
		$theTime = sprintf( '%f', microtime(true) );
		
		$header = 'x-' . $theTime . '-' . $fn . ': ' . $msg;
		
		if ( !headers_sent() ) {
			header( $header );
		} else {
			print "<H4>" . $header . "</H4>";
		}
		
		return;
	}


	//
	// Save user values to storage before finishing page
	//
	public static function saveuser() {

/*
		global $User;

		// Clean some previous and cached values before saving.
		unset( $User['System'] );
		unset( $User['AdminNav']['Cache'] );

		$data = serialize( $User );

		// $this->showxhead( __FUNCTION__, "Saving user data" . strlen( $data ) . " bytes." );
		// d( $User );

		DB::update( TABLE_USERS, 
			array( 
				'storage' => $data, 
			), 
			'email = %s', 
			$_SESSION['profile']['email']
		);
*/

		return;
	}

	
	
	public static function get_sidebar() {
	
		if ( isset( $this->theme['files']['sidebar.php'] ) && !empty( $this->theme['files']['sidebar.php'] ) ) {
			include_once( $this->theme['files']['sidebar.php'] );
		}
			
		return;
	}
	
	
	public static function get_footer() {
	
		$this->timer['end'] = microtime( true );
		$this->timer['elapsed'] = $this->timer['end'] - $this->timer['start'];
	
		if ( isset( $this->theme['files']['footer.php'] ) && !empty( $this->theme['files']['footer.php'] ) ) {
			include_once( $this->theme['files']['footer.php'] );
		}
	
		return;
	}
	
	
	public static function load_themes() {
	
		$this->theme['includes'] = array();
		$this->theme['meta'] = array();
		
		// Temp variable
		$finfo[0] = array();
	  
		// The path to the "themes" folder
		if ( is_dir(QWIP_THEMES_PATH . DS . $this->theme['name']) ) {
	
			$this->theme['path'] = QWIP_THEMES_PATH . DS . $this->theme['name'];
			// print "<BR>Theme Path: " . $thm_path;
		
			if ( is_dir($this->theme['path']) ) {
				
				// Load the designated theme files
			    $d = dir( $this->theme['path'] );
			    while( $entry = $d->read() ) {   
			        if ( substr( $entry, 0, 1 ) != "." ) {
			        		if ( $entry == 'info.json' ) {
				            $finfo[1][$entry] = file_get_contents( $this->theme['path'] . DS . $entry );
				          } else {
				            $finfo[1][$entry] = $this->theme['path'] . DS . $entry;
				          }
			        }
			    }
			    $d->close();
		
			    $finfo[1]['_'] = (array) json_decode( $finfo[1]['info.json'] );
			    unset( $finfo[1]['info.json'] );
			    if ( isset( $finfo[1]['functions.php'] ) ) {
				    unset( $finfo[1]['functions.php'] );
			    	$finfo[1]['*'] = $this->theme['path'] . DS . 'functions.php';
			    }
	
			} else {
				exit("Your theme does not appear to be set correctly. " 
					. "Please open the following file and correct this: " . QWIP_SELF );
			}
			
		    // Determine the parent theme
		    $this->theme['parent']['name'] = $finfo[1]['_']['Based On'];
			$this->theme['parent']['path'] = QWIP_THEMES_PATH . DS . $this->theme['parent']['name'];
	
			// print "<BR>Parent Theme Path: " . $this->theme['parent']['path'];
	
			if ( !empty($this->theme['parent']['path']) ) {
				if ( is_dir($this->theme['parent']['path']) ) {
					// Load the designated theme files
			    $d = dir( $this->theme['parent']['path'] );
			    while( $entry = $d->read() ) {   
			        if ( substr( $entry, 0, 1 ) != "." ) {
			        		if ( $entry == 'info.json' ) {
				            $finfo[2][$entry] = file_get_contents( $this->theme['parent']['path'] . DS . $entry );
				          } else {
				            $finfo[2][$entry] = $this->theme['parent']['path'] . DS . $entry;
				          }
			        }
			    }
			    $d->close();
	
			    if ( isset( $finfo[2]['info.json'] ) ) {
			    	$finfo[2]['_'] = (array) json_decode( $finfo[2]['info.json'] );
			    }
			    unset( $finfo[2]['info.json'] );
			    if ( isset( $finfo[2]['functions.php'] ) ) {
				    unset( $finfo[2]['functions.php'] );
			    	$finfo[2]['*'] = $this->theme['parent']['path'] . DS . 'functions.php';
			    }
				} else {
						exit("Your parent theme does not appear to be set correctly. " 
										. "Please open the following file and correct this: " . $this->theme['path'] 
										. DS . 'info.json' );
				}
			}
	
			// Merge the files according to the parent theme rules
			if ( isset( $finfo[2] ) ) {
	
			    // Load parent files first
				foreach ( $finfo[2] as $file => $content ) {	    
					if ( $file != '*' && $file != '_' ) {
						$finfo[0][$file] = $content;
					}
				}
				if ( isset( $finfo[2]['*'] ) ) {
					$this->theme['includes'][] = $finfo[2]['*'];
				}
				if ( isset( $finfo[2]['_'] ) ) {
					foreach ( $finfo[2]['_'] as $variable => $value ) {	    
						$this->theme['meta'][$variable] = $value;
					}
				}						
	
			    // Load child files, overriding if necessary
				foreach ( $finfo[1] as $file => $content ) {	    
					if ( $file != '*' && $file != '_' ) {
						$finfo[0][$file] = $content;
					}
				}
				if ( isset( $finfo[1]['*'] ) ) {
					$this->theme['includes'][] = $finfo[1]['*'];
				}
				if ( isset( $finfo[1]['_'] ) ) {
					foreach ( $finfo[1]['_'] as $variable => $value ) {	    
						$this->theme['meta'][$variable] = $value;
					}
				}
		  }
	
		  // Take only the remaining files
		  $this->theme['files'] = $finfo[0];
	
		}
	
		return;
	}
	
	
	//
	//  ==== Supporting App Functions ==== 
	//
	
	public static function print_title() {

		if ( isset( $this->macros['title'] ) && !empty( $this->macros['title'] ) ) {
			print $this->macros['title'];
		}
		return;
	}
	
	public static function print_description() {

		if ( isset( $this->macros['description'] ) && !empty( $this->macros['description'] ) ) {
			print $this->macros['description'];
		}
		return;
	}
	
	public static function print_cdn() {

		if ( isset( $this->path['cdn'] ) && !empty( $this->path['cdn'] ) ) {
			print $this->path['cdn'];
		}
		return;
	}
	
	
	public static function print_head() {

		if ( isset( $this->macros['head'] ) && !empty( $this->macros['head'] ) ) {
			print $this->macros['head'];
		}
		return;
	}
	
	// For body onload="" hook.
	public static function print_onload() {

		if ( isset( $this->macros['onload'] ) && !empty( $this->macros['onload'] ) ) {
			print $this->macros['onload'];
		}
		return;
	}
	
	
	public static function print_footer() {

		if ( isset( $this->macros['footer'] ) && !empty( $this->macros['footer'] ) ) {
			print $this->macros['footer'];
		}
		return;
	}
	
	
	
	public static function loadapp() {
	
		// Default values
		$this->app['name'] = 'page';
	
		// Load up the possible apps we could run
		$d = dir( $this->path['app'] );
		while( $entry = $d->read() ) {
			$fp = $this->path['app'] . DS . $entry;   
			if ( substr( $entry, 0, 1 ) != "." && $entry != 'wave' && is_dir( $fp ) ) {
				$appinfo[$entry] = $fp;
			}
		}
		$d->close();
	
		// Decide which app to run
		
		// If we have a perfect match with an app, load it first
		if ( isset( $this->routes['req'][0] ) && isset( $appinfo[$this->routes['req'][0]] )  ) {
			$this->app['name'] = $this->routes['req'][0];
		} else if ( count( $this->routes['req'] ) > 1 ) {
			// IF we have more than one request in the stack, pass to menus
			$this->app['name'] = 'menu';
		} else {
			// Pass to page or config.php define'd
			$this->app['name'] = 'page';
			if ( defined( 'APP_DEFAULT' ) ) {
				$this->app['name'] = APP_DEFAULT;
			}
		}
	
		// Load the app, but don't execute anything yet.
		$this->app['path'] = $this->path['app'] . DS . $this->app['name'] . DS . 'index.php';
	
		if ( !file_exists( $this->app['path'] ) )	{
	
			$this->missing_app();
	
		} else {
	
			include_once( $this->app['path'] );
		
			// Instantiate our new app
			$className = $this->app['name'];
	  
			$this->app['instance'] = new $className( $this->instance );
	  
			return;
	
		}
	
	}
		 
}	// End QWIP class
	

?>