<?php

	// QWIP - Quick Webapp Integration Platform.  A lightweight webpp host providing authentication, 
	// themes, routing, and code compartmentalization.

	// Google Apps Open ID integration example - google apps openid authentication

	// Get our path to qwip
	include( "qwip-0.7/qwip-0.7.class.php" );

	define('APP_HOME', dirname(dirname(dirname(__FILE__))));

	// Instanstiate app handler
	$q = new qwip(	array( 
							'app' => APP_HOME . DS . 'public_html' . DS . 'qapps',
							'lib' => APP_HOME . DS . 'lib',
							'sys' => APP_HOME . DS . 'lib',
							'themes' => APP_HOME . DS . 'public_html' . DS . 'themes'
					 )
				);  // Pass in new base path, etc.

	// Set hooks
	$q->hooks['qwip:authenticate'] = 'qwip_authenticate';
	$q->hooks['qwip:on_validate'] = 'qwip_on_validate';

	// Start the session
	$q->session_start();

	// Run the requested app
	$q->run();



	// On validate
	function qwip_on_validate( $q ) {
		
		// If valid user, allow in and set up user record. 
		if ( !empty( $_SESSION['profile']['email'] ) ) {
		
		} else {

			print "<div class='alert'>Sorry, this account is not authorized to use this service.  "
				." Try logging out of any personal accounts and into your authorized account.</div>";
		
			unset( $_SESSION['profile'] );	
			unset( $_SESSION['identity'] );	
			unset( $_SESSION['identifier'] );	
			unset( $_SESSION['account_id'] );	
				
		}
		
		return;		
	}

	// Hook called on authenticate w/google
	function qwip_authenticate( $q ) { 

		// Not logged in, so load openid
		require 'lightopenid-0.6/openid.php';
		
		$is_valid = false;

		try {

		    $openid = new LightOpenID( $_SERVER['HTTP_HOST'] );

		    $openid->realm     = 'http://' . $_SERVER['HTTP_HOST'];
	        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
	            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
	            && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
	        ) {
			    $openid->realm     = 'https://' . $_SERVER['HTTP_HOST'];
 			}

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
		    
		        echo "<div class='alert'>User has canceled authentication!</div>";
		        
		        $_SESSION['identity']['status'] = 'canceled'; 

		    } else {
		    
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
					$q->on_validate();
	
			        // Decrement the clicks to make up for redirect
			        $_SESSION['clicks'] = $_SESSION['clicks'] - 1;
		        
	    		} else {
		    	    echo "<div class='alert'>User " . $openid->identity . " has not logged in.</div>";
	    		}
		    }
	
		} catch(ErrorException $e) {
			echo $e->getMessage();
		}
		
		return $is_valid;		
	}



?>