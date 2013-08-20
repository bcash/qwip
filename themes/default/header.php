<?php

    // Available themes
    $_SESSION['themelist'] = array( 
        'Default' => '//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.no-icons.min.css',
        'Amelia' => '//netdna.bootstrapcdn.com/bootswatch/2.3.1/amelia/bootstrap.min.css',
        'Cerulean' => '//netdna.bootstrapcdn.com/bootswatch/2.3.1/cerulean/bootstrap.min.css',
        'Cosmo' => '//netdna.bootstrapcdn.com/bootswatch/2.3.1/cosmo/bootstrap.min.css',
        'Cyborg' => '//netdna.bootstrapcdn.com/bootswatch/2.3.1/cyborg/bootstrap.min.css',
        'Journal' => '//netdna.bootstrapcdn.com/bootswatch/2.3.1/journal/bootstrap.min.css',
        'Readable' => '//netdna.bootstrapcdn.com/bootswatch/2.3.1/readable/bootstrap.min.css',
        'Simplex' => '//netdna.bootstrapcdn.com/bootswatch/2.3.1/simplex/bootstrap.min.css',
        'Slate' => '//netdna.bootstrapcdn.com/bootswatch/2.3.1/slate/bootstrap.min.css',
        'Spacelab' => '//netdna.bootstrapcdn.com/bootswatch/2.3.1/spacelab/bootstrap.min.css',
        'Spruce' => '//netdna.bootstrapcdn.com/bootswatch/2.3.1/spruce/bootstrap.min.css',
        'Superhero' => '//netdna.bootstrapcdn.com/bootswatch/2.3.1/superhero/bootstrap.min.css',
        'United' => '//netdna.bootstrapcdn.com/bootswatch/2.3.1/united/bootstrap.min.css',
        'Geo' => '//alpine.s3.amazonaws.com/services/bootstrap-themes/geo/geo-bootstrap.min.css'    
    );

    // Other header goes here
    if ( isset( $_GET['theme'] ) && isset( $_SESSION['themelist'][$_GET['theme']] ) ) {
        $_SESSION['preferences']['theme'] = $_GET['theme'];
    }
    
    $theme = $_SESSION['themelist']['Default'];
    if ( isset( $_SESSION['preferences']['theme'] ) && isset( $_SESSION['themelist'][$_SESSION['preferences']['theme']] ) ) {
        $theme = $_SESSION['themelist'][$_SESSION['preferences']['theme']];
    }

?>
<!doctype html>
<html lang="en" class="no-js">
    <head>
        <meta charset="utf-8">
        <title><?php $this->print_title(); ?></title>
    
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="description" content="<?php $this->print_description(); ?>">
        <meta name="author" content="Brian Cash - http://www.briancash.com/">
        <meta name="generator" content="QWIP, the WebApps platform">
    
        <!-- Le styles -->
        <link href="<?php echo $theme; ?>" rel="stylesheet">
        <link href="//netdna.bootstrapcdn.com/font-awesome/3.0.2/css/font-awesome.css" rel="stylesheet">

        <!-- Vendor Styles -->
        <script src="//s3.amazonaws.com/alpine/services/modernizr-2.6.2/modernizr.custom.14811.js"></script>
        
        
    <?php
       /* Always have print_head() just before the closing </head>
        * tag of your theme, or you will break many apps, which
        * generally use this hook to add elements to <head> such
        * as styles, scripts, and meta tags.
        */
       $this->print_head();
    ?>
    </head>
    <body onload="<?php $this->print_onload(); ?>">
        <header>
            <nav class="NOTcontainer">
            <div class="navbar navbar-fixed-top noprint"  data-dropdown="dropdown">
                <div class="navbar-inner">
                    <div class="container-fluid">
                        <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </a>
                        <a class="brand-logo" href="/"><img src="//s3.amazonaws.com/centerstage/4.0.0/images/alpine_logo_26.png"></a>
                        <a class="brand" href="/"> Example</a>
                        <div class="nav-collapse collapse">
    <?php
    
?>
                        <!-- secondary Nav -->            
                        <ul class="nav secondary-nav pull-right">
                        
<?php
    
            if ( !empty( $_SESSION['profile']['email'] ) )  {
            
                print '<li class="dropdown">';
                print ' <a href="#" class="dropdown-toggle" data-toggle="dropdown">';
            
                $size = 20;
                
                $grav_url = "//www.gravatar.com/avatar/" 
                            . md5( strtolower( trim( $_SESSION['profile']['email'] ) ) ) 
                            . "?s=" . $size;
            
                print stripslashes($_SESSION['profile']['name']) .  
                      ' <img class="profileimage" src="' . $grav_url .  '" alt=""> ' . 
                          '<b class="caret"></b></a>';
            
                print ' <ul class="dropdown-menu">';

                print '   <li><a href="?logout=1">Logout</a></li>';
                print ' </ul>';
                print '</li>';
            
            } else {
                print '   <li><a href="?login">Login</a></li>';
            }
                
?>
                
    
                            </ul>
                             </div><!--/.nav-collapse -->
                        </div>
                    </div>
                </div>
            </nav>

<?php
    
?>
        </header>
        <div id="content" class="container-fluid">