<?php

    // QWIP - Quick Webapp Integration Platform.  A lightweight webpp host providing authentication, 
    // themes, routing, and code compartmentalization.

    // Basic example - no authentication

    // Get our path to qwip
    include( "vendor/bcash/qwip/qwip.php" );

    // Define our homedir
    define('APP_HOME', dirname(dirname(dirname(__FILE__))) );

    // Instanstiate app handler
    $q = new qwip(  array( 
                        'app' => APP_HOME . '/public_html/apps',
                        'lib' => APP_HOME . '/lib',
                        'sys' => '/usr/common/lib',
                        'themes' => APP_HOME . '/public_html/themes'
                         )
                    );

    // Start the session
    $q->session_start();

    // Run the requested app
    $q->run();

?>