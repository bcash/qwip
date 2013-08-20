# Qwhat is QWIP

Qwip stands for a quick webapps integration platform.  A toolkit for people that build and manage sites using PHP.  

It's goal is to facilitate agile adoption of disparate code quickly.  By providing centralized theming, authentication, 
and other common services,qwip should help you bring code from multiple locations into your site as quickly as 
possible, but without compromising security, performance, and design integrity.

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
		
 qwipit.org - examples of qwip'd applications and their seamless integration.  wordpress, simple scripts, cms apps


## Release Information

QUIP v0.7 is being packaged for relase under the MIT open source license.

## Changelog

v0.7 - Initial release

## Server Requirements

-  PHP version 5.2.4 or newer.

## Installation

See examples.
