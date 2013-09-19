<?php
/**
 *  The MIT License (MIT)
 *  
 *  Copyright (c) 2013 Brian Cash
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of
 *  this software and associated documentation files (the "Software"), to deal in
 *  the Software without restriction, including without limitation the rights to
 *  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 *  the Software, and to permit persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 *  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */ 

    // Define some constants that are not overridable.
    if ( !defined( 'DS' ) ) define('DS', DIRECTORY_SEPARATOR );
    if ( !defined( 'QWIP_SELF' ) ) define('QWIP_SELF', basename(__FILE__) );
    if ( !defined( 'QWIP_BASE_PATH' ) ) define('QWIP_BASE_PATH',realpath('.'));
    if ( !defined( 'QWIP_BASE_URL' ) ) define('QWIP_BASE_URL', dirname($_SERVER["SCRIPT_NAME"]));   
    if ( !defined( 'QWIP_SESSION_SECONDS' ) ) define('QWIP_SESSION_SECONDS', 60*60*24*1 ); 
    
    
    // Base class should retain lightest possible framework using only native php.  
    // 
    // Review example child classes for mysql, SimpleDB, OpenID implementations.
    // 

class QWIP {

    // Self
    public static $instance;

    // Paths to QWIP resources
    public $paths;

    // Timer tracking performance
    public $timer;

    // App instance and meta info
    public $app;
    
    // Route information to delegate to our app
    public $routes;

    // Macros are populated by our app and rendered within our theme.
    public $macros;
    
    // Theme name and path info
    public $theme;
    
    // Registered hooks execute user code
    public $hooks;

    // Set paths and default variables in constructor.
    //
    public function __construct( $upaths ) {

        // The instance handle / housekeeping
        self::$instance =& $this;

        $this->timer['start'] = microtime( true );
    
        // Set paths.
        $this->path['url'] = QWIP_BASE_URL;
        $this->path['cdn'] = '';
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
            exit( sprintf( "\n<div class='alert'>Your application folder path (%s) does not appear to be set correctly.</div>", 
                $this->path['app'] ) ); 
        }
    
        // Check the path to the "lib" folder
        if ( is_dir($this->path['lib']) ) {
            define('QWIP_LIB_PATH', $this->path['lib'] );
        } else {
            exit( sprintf( "\n<div class='alert'>Your library folder path (%s) does not appear to be set correctly.</div>", 
                $this->path['lib'] ) );
        }
    
        // Check the path to the "sys" folder
        if ( is_dir( $this->path['sys'] ) ) {
            define('QWIP_SYS_PATH', $this->path['sys'] );
        } else {
            exit( sprintf( "\n<div class='alert'>Your system folder path (%s) does not appear to be set correctly.</div>", 
                $this->path['lib'] )  );
        }
    
        // Check the path to the "themes" folder
        if ( is_dir($this->path['themes']) ) {
            define('QWIP_THEMES_PATH', $this->path['themes'] );
        } else {
            exit( sprintf( "\n<div class='alert'>Your theme folder path (%s) does not appear to be set correctly.</div>", 
                $this->path['lib'] ) );
        }

        // Set file search path 
        ini_set( 'include_path',  sprintf( "%s:%s:%s:%s:%s", get_include_path(), 
                QWIP_BASE_PATH, QWIP_APP_PATH, QWIP_SYS_PATH, QWIP_THEMES_PATH ) );

        return;
    }

    public function session_start() {

        # Start the normal php session
        session_start();

        // Session is now loaded
        $this->session_postload();
        
        return;     
    }
    
    // Loads user, session, runs app
    public function run() {

        if ( isset($this->hooks['qwip:'.__FUNCTION__]) ) $this->hooks['qwip:'.__FUNCTION__]( self::$instance );

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
                    exit("<div class='alert'>Cannot find index method file for app. " 
                                    . "Missing method in: " . $this->app['instance'] . "</div>" );
                }
            }
            $this->app['instance']->$action();  
    
            // Consolidate on_ready into footer     
            if ( isset( $this->app['instance']->on_ready ) ) {
            
                $allscripts = '';
                foreach( $this->app['instance']->on_ready as $idx => $script ) {
                    $allscripts .= "\n" . $script;
                }
    
                $newhead =<<<__EOT__
        <!-- Set by adding to this->q->app['instance']->on_ready[] from your qwip app -->
        <script>
        $(document).ready(function() {
            {{ALLSCRIPTS}}
        });
        </script>
__EOT__;
    			if ( !isset( $this->app['instance']->export['footer'] ) ) { $this->app['instance']->export['footer'] = ''; }
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
                                exit("\n<div class='alert'>Cannot find functions file for theme. " 
                                                . "Missing file: " . $include_file . "</div>" );
                            }
                        }
                    }  
                
                    // Now, finally, include our index.php from our theme to start the chain reaction.
                    if ( !isset( $this->theme['files']['index.php'] ) || empty( $this->theme['files']['index.php'] ) ) {
                        exit("\n<div class='alert'>Cannot find theme file for theme. " 
                                        . "Missing index.php file in " .  $this->theme['name'] . "</div>" );
                    }
    
                    // Start Theme processing.
                    include_once( $this->theme['files']['index.php'] );
    
                }
            }
            
            // If request was ajax, we've already run.  Ajax methods should simply print output.

            // Close down this app
            $this->app_complete();
        }
        
        return;     
    }

    public function app_complete() {
        
        if ( isset($this->hooks['qwip:'.__FUNCTION__]) ) $this->hooks['qwip:'.__FUNCTION__]( self::$instance );

        // This may save user persistence data.
        $this->session_complete();

        return;
    }

    public function __destruct() {

        if ( isset($this->hooks['qwip:'.__FUNCTION__]) ) $this->hooks['qwip:'.__FUNCTION__]( self::$instance );
        
        return;
    }   
    
    public function logout() {
    
        if ( isset($this->hooks['qwip:'.__FUNCTION__]) ) $this->hooks['qwip:'.__FUNCTION__]( self::$instance );

        // Unset all of the session variables.
        $_SESSION = array();
        // Clear cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - QWIP_SESSION_SECONDS,
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

    public function getroute() {

        if ( isset($this->hooks['qwip:'.__FUNCTION__]) ) $this->hooks['qwip:'.__FUNCTION__]( self::$instance );

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
    
    public function session_postload() {

        if ( isset($this->hooks['qwip:'.__FUNCTION__]) ) $this->hooks['qwip:'.__FUNCTION__]( self::$instance );

        // If user wants to be logged out, fully reset our session
        if( isset($_GET['logout']) ) {
            $this->logout();
        }
        
        // Start user authentication
        //
        $_SESSION['action'] = 'logout';   
    
        if (!isset($_SESSION['account_id'])) {

            // header( "x-validate: running config_on_validate" );
            $this->remember();

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
            setcookie( 'PHPSESSID', $current_cookie, time() + QWIP_SESSION_SECONDS, '/' );
            
            // This will load session based on a valid auth token.
            $this->on_authenticated();
            
        }

        // Count clicks in this session
        if ( isset( $_SESSION['clicks'] ) ) {
            $_SESSION['clicks'] = $_SESSION['clicks'] + 1;
        } else {
            $_SESSION['clicks'] = 1;  
        }

        return;     
    }

    // Authenticate against open ID service (google, self, other) ...   
    //
    public function authenticate() {
        if ( isset($this->hooks['qwip:'.__FUNCTION__]) ) $this->hooks['qwip:'.__FUNCTION__]( self::$instance );
        return;
    }

    public function remember() {
        if ( isset($this->hooks['qwip:'.__FUNCTION__]) ) $this->hooks['qwip:'.__FUNCTION__]( self::$instance );
        return; 
    }

    // On Authenticated is run when user is already authorized to run.  
    // It is not run when first authenticating.  That would be config_on_validate.
    // On authenticated may be configured to integrate with other databases and tables
    // Should generate and issue a remember me token and cookie here
    // 
    public function on_authenticated() {
        if ( isset($this->hooks['qwip:'.__FUNCTION__]) ) $this->hooks['qwip:'.__FUNCTION__]( self::$instance );
        return;
    }

    //  ==== Supporting Theme Functions ==== 
    //
    public function is_ajax() {
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
    
    public function auth_redirect() {
    
        if ( file_exists( $this->theme['files']['noauth.php'] ) ) {

            // Show our theme's 404 template if it exists.
            header('401 Authorization Required');
            include_once( $this->theme['files']['noauth.php'] );

        } else {
        
            // Show default 404 message as a last resort.
            // header('302 Authorization Required');
            header("Location: /auth_required.html", TRUE, 302 );
            
        }
    
        return;
    }

    //  On Validate may be configured to integrate with other databases and tables
    //  Should generate and issue a remember me token and cookie here
    // 
    public function on_validate() {
    
        if ( isset($this->hooks['qwip:'.__FUNCTION__]) ) $this->hooks['qwip:'.__FUNCTION__]( self::$instance );

        return;
    }

    public function get_404() {
    
        if ( isset($this->hooks['qwip:'.__FUNCTION__]) ) $this->hooks['qwip:'.__FUNCTION__]( self::$instance );
		
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

			// Theme is not loaded yet, so load our default for the 404
            if ( !$this->is_ajax() ) {

		        $this->theme['name'] = $this->macros['theme'];
	            $this->load_themes();
			}
			
            // Show default 404 message as a last resort.
	        header('404 Not Found');
			
            // Start output buffer
            ob_start();
            include_once( $this->theme['files']['404.php'] );
            // Close output buffer, save to body.
            $out = ob_get_contents();
            ob_end_clean();             

        }
        return $out;
    }

    
    public function missing_app() {
    
        if ( isset($this->hooks['qwip:'.__FUNCTION__]) ) $this->hooks['qwip:'.__FUNCTION__]( self::$instance );

        // Show default 404 message as a last resort.
		print $this->get_404();
        
        return;
    }
    
    public function get_header() {
    
        if ( isset( $this->theme['files']['header.php'] ) && !empty( $this->theme['files']['header.php'] ) ) {
            include_once( $this->theme['files']['header.php'] );
        }
    
        return;
    }
    
    public function get_content() {
    
        if ( isset( $this->theme['files']['content.php'] ) && !empty( $this->theme['files']['content.php'] ) ) {
            include_once( $this->theme['files']['content.php'] );
        } else if ( isset( $this->macros['body'] ) && !empty( $this->macros['body'] ) ) {
            print $this->macros['body'];
        } else {
            print "<div class='alert'>WARNING: Missing content. App " . $this->app['name'] 
                        . " should populate it's export array (especially body).</div>";
        }
    
        return;
    }

    public function showxhead( $fn, $msg ) {
        
        $theTime = sprintf( '%f', microtime(true) );
        
        $header = 'x-' . $theTime . '-' . $fn . ': ' . $msg;
        
        if ( !headers_sent() ) {
            header( $header );
        } else {
            print "\n<div class='alert'>" . $header . "</div>";
        }
        
        return;
    }

    // Save user values to storage before finishing page
    //
    public function session_complete() {
        if ( isset($this->hooks['qwip:'.__FUNCTION__]) ) $this->hooks['qwip:'.__FUNCTION__]( self::$instance );
        return;
    }

    public function get_sidebar() {
        if ( isset( $this->theme['files']['sidebar.php'] ) && !empty( $this->theme['files']['sidebar.php'] ) ) {
            include_once( $this->theme['files']['sidebar.php'] );
        }
        return;
    }
    
    public function get_footer() {
    
        $this->timer['end'] = microtime( true );
        $this->timer['elapsed'] = $this->timer['end'] - $this->timer['start'];
    
        if ( isset( $this->theme['files']['footer.php'] ) && !empty( $this->theme['files']['footer.php'] ) ) {
            include_once( $this->theme['files']['footer.php'] );
        }
    
        return;
    }
    
    public function loadapp() {
    
        // Default values
        $this->app['name'] = 'pages';
    
        // Load up the possible apps we could run
        $d = dir( $this->path['app'] );
        while( $entry = $d->read() ) {
            $fp = $this->path['app'] . DS . $entry;   
            if ( substr( $entry, 0, 1 ) != "." && $entry != 'qwipapp' && is_dir( $fp ) ) {
                $appinfo[$entry] = $fp;
            }
        }
        $d->close();
    
        // Decide which app to run
        
        // If we have a perfect match with an app, load it first
        if ( isset( $this->routes['req'][0] ) && isset( $appinfo[$this->routes['req'][0]] )  ) {
            $this->app['name'] = $this->routes['req'][0];
        } else {
            // Pass to pages or override
            $this->app['name'] = 'pages';
            if ( isset( $this->macros['app'] ) && !empty( $this->macros['app'] ) ) {
                $this->app['name'] = $this->macros['app'];
            }
        }
    
        // Load the app, but don't execute anything yet.
        $this->app['path'] = $this->path['app'] . DS . $this->app['name'] . DS . 'index.php';

        if ( !file_exists( $this->app['path'] ) )   {
            $this->missing_app();
        } else {

            include_once( $this->app['path'] );
        
            // Instantiate our new app
            $className = $this->app['name'];
      
            $this->app['instance'] = new $className();
            
            // Pass in the qwip handle
            $this->app['instance']->q = self::$instance;
      
            return;
    
        }

        if ( isset($this->hooks['qwip:'.__FUNCTION__]) ) $this->hooks['qwip:'.__FUNCTION__]( self::$instance );

        return;
    }
    
    public function load_themes() {
    
        $this->theme['includes'] = array();
        $this->theme['meta'] = array();
        
        // Temp variable
        $finfo[0] = array();
      
        // The path to the "themes" folder
        if ( is_dir(QWIP_THEMES_PATH . DS . $this->theme['name']) ) {
    
            $this->theme['path'] = QWIP_THEMES_PATH . DS . $this->theme['name'];
        
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
                exit("<div class='alert'>Your theme does not appear to be set correctly. " 
                    . "Please open the following path and correct this: " 
                    . $this->theme['path'] . "</div>" );
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
                        exit("<div class='alert'>Your parent theme does not appear to be set correctly. " 
                            . "Please open the following file and correct this: " . $this->theme['path'] 
                            . DS . 'info.json</div>' );
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

        if ( isset($this->hooks['qwip:'.__FUNCTION__]) ) $this->hooks['qwip:'.__FUNCTION__]( self::$instance );

        return;
    }
    
    //  ==== Functions that are called from theme/index.php ==== 
    //
    public function print_themepath() {

        if ( isset( $this->macros['theme'] ) && !empty( $this->macros['theme'] ) ) {
            print QWIP_BASE_URL . 'themes' . DS . $this->macros['theme'];
        }

        return;
    }

    public function print_title() {

        if ( isset( $this->macros['title'] ) && !empty( $this->macros['title'] ) ) {
            print $this->macros['title'];
        }
        return;
    }
    
    public function print_description() {

        if ( isset( $this->macros['description'] ) && !empty( $this->macros['description'] ) ) {
            print $this->macros['description'];
        }
        return;
    }

    public function print_keywords() {

        if ( isset( $this->macros['keywords'] ) && !empty( $this->macros['keywords'] ) ) {
            print $this->macros['keywords'];
        }
        return;
    }
    
    public function print_cdn() {

        if ( isset( $this->path['cdn'] ) && !empty( $this->path['cdn'] ) ) {
            print $this->path['cdn'];
        }
        return;
    }
    
    public function print_head() {

        if ( isset( $this->macros['head'] ) && !empty( $this->macros['head'] ) ) {
            print $this->macros['head'];
        }
        return;
    }
    
    public function print_onload() {

        if ( isset( $this->macros['onload'] ) && !empty( $this->macros['onload'] ) ) {
            print $this->macros['onload'];
        }
        return;
    }
    
    public function print_footer() {

        if ( isset( $this->macros['footer'] ) && !empty( $this->macros['footer'] ) ) {
            print $this->macros['footer'];
        }
        return;
    }

    // =============    
    //
    
    // Register a hook for integration purposes.  We'll pass in an instance handle.
    //
    function register_hook( $fname, $func ) {

        // Save this hook for later use.
        $this->hooks[$fname] = $func;

        return;     
    }

    // Set a macro such as UA-code
    //
    function set_macro( $name, $value ) {

        // Save this macro for theme evaluation.
        $this->macros[$name] = $value;

        return;     
    }

     
}   // End QWIP class

?>