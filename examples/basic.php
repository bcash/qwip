<?php

	// QWIP - Quick Webapp Integration Platform.  A lightweight webpp host providing authentication, 
	// themes, routing, and code compartmentalization.

	// Basic example - no authentication

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

	// Start the session
	$q->session_start();

	// Run the requested app
	$q->run();

?>