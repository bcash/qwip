# What is QWIP?

Qwip stands for a quick webapps integration platform.  A toolkit for people that build and manage sites using PHP.  

It's goal is to facilitate agile adoption of disparate code quickly.  By providing centralized theming, authentication, 
and other common services,qwip should help you bring code from multiple locations into your site as quickly as 
possible, but without compromising security, performance, and design integrity.

## Design principles / Features:
	
### Modular core
Run as light or as heavy as needed for your infrastructure.  Avoid WordPress-style bloat or CodeIgniter-style complexity.  Assumptions may be required.  Includes only, no eval statements.

### Agility
You don't always need a "framework".  Most small and medium sized projects don't require giant frameworks and for most projects they get in the way.  QWIP is designed to just do it's job and get out of the way.

### Theme Support
QWIP provides a theme system designed to be very similar to WordPress.  WordPress is the most popular design system with thousands of design-developers in the market.  QWIP is designed for maximum third party designer participation.  

Parent / child themes are supported.

Goals: Provide great WordPress theme conversion tutorials and a theme repository.  

### Users / Authentication
Unified user authentication, session management, session and user data for any application. Integrate with other Open ID providers for single sign-on over multiple sites.

### DB independent 
Don't assume we're always going to use mysql, or anything else.  
		
### Flexible Paths 
Codebase and components should be allowed anywhere in the file system.    Organize your project the way you want.
		
### Simplest Use Cases
Demonstrate the platform with the simplest use cases.
		
### Keep Errors Obvious 
Display errors.  TODO: Provide maximum help to resolve during configuration.  Make performance monitoring and debugging easy.
		
### Quickest installation
Provide quickstart for common use cases via composer.  Take maximum advantage of packaged utilities via composer.  Integrate perfectly itself.

### Rapid App Development
Optimize speed to app with tutorials.

### Rapid App Integration 
Optimize speed to integration.  Allow for very quick open source and third party integration.  The QWIP mantra during integration is to "trim the fat".  Because it's always easier to remove unneeded code rather than write new code.
		
### Coming Soon
qwipit.org - examples of qwip'd apps and major integration.  wordpress, vbulletin, simple scripts, cms apps


## Release Information

QUIP v0.7 is being packaged for release under the MIT open source license.

## Changelog

v0.7 - Initial release

## Server Requirements

-  PHP version 5.2.4 or newer.

## Installation

### 1. Install qwip via composer

First, install composer: http://getcomposer.org/doc/01-basic-usage.md

```
{
    "require": {
        "bcash/qwip": "dev-master"
    }
}
```


### 2. Copy / create themes, apps to your home directory.
Tell qwip where to find them by passing these paths to the constructor.
```
    // Instanstiate app handler
    $q = new qwip(  array( 
                        'app' => HOME . '/public_html/apps',
                        'lib' => HOME . '/lib',
                        'sys' => '/usr/common/lib',
                        'themes' => HOME . '/public_html/themes'
                         )
                    );
```
### 3. Include qwip in your script.
```
	// qwip installed via composer
	require 'vendor/autoload.php';
```

### 4. Set up .htaccess.
See .htaccess examples below.

### 5. Run 
```
    // Run the requested app
    $q->run();
```

## Sample .htaccess

Sample .htaccess for apache which rewrites raw urls to a routable format.
```

<IfModule mod_rewrite.c>

  RewriteEngine On
  RewriteBase /
  RewriteRule ^index\.php$ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /index.php [L]

</IfModule>
```

To place qwip in a subdirectory, simply modify these two lines:
```
  RewriteBase /qwip/
  
  
  RewriteRule . /qwip/index.php [L]

```

## Usage


A quick example:

```php
<?php
	// qwip installed via composer
	require 'vendor/autoload.php';

    // Instanstiate app handler
    $q = new qwip(  array( 
                        'app' => HOME . '/public_html/apps',
                        'lib' => HOME . '/lib',
                        'sys' => '/usr/common/lib',
                        'themes' => HOME . '/public_html/themes'
                         )
                    );

    // Start the session
    $q->session_start();

    // Run the requested app
    $q->run();

?>
```



## Sample App

An app is a simple extension of the base app class.  All routing is delegated to the class.  This class would be called with /template/.

```php
<?php if ( ! defined('QWIP_BASE_PATH')) exit('No direct script access allowed');

include_once "qwipapp/index.php";

class template extends qwipapp {

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Call parent constructor
		parent::__construct();

		// Authentication required?
		$this->authreq = false;

		$this->export['title'] = 'QWIP Template App';
		$this->export['head'] = '';

		return;		
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{

		ob_start();

	    // Do interesting stuff here
	    print <<<__EOT__

	    <h1>Template App</h1>
	    <p>Motto: No need to fear.  Template app is here.</p>

__EOT__;

		$this->export['body'] = ob_get_contents();

		return;
	}

}
?>
```

## Serving raw / json output

Note: To perform theme-free ajax operations, simply print your output and exit the system from your class -- without letting qwip wrap your output in the theme.
