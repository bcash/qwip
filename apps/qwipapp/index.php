<?php  if ( ! defined('QWIP_BASE_PATH')) exit('No direct script access allowed');
/**
 * QWIP
 *
 */
class qwipapp {

	private static $instance;

	public $q;	

	// Data I will make available to classes using me.
	public $export;

	// Is authentication required to see this module?
	public $authreq = true;

	// What is our default resource path?
	public $respath = '';
	
	public $action = 'index';

	/**
	 * Constructor
	 */
	public function __construct( )
	{
		self::$instance =& $this;

		$export = array();

		$this->export['title'] = 'WARNING: Empty title. Override with App.';
		$this->export['description'] = 'WARNING: Empty description. Override with App.';
		$this->export['body'] = "<div class='alert'>WARNING: Empty body. Override with App.</div>";
		
		// Default Theme
		$this->export['theme'] = 'default';
		
		// Default to auth required
//		$this->authreq = true;
		
		header("x-app-startup: done." );
		
		return;		
	}

	public static function &get_instance()
	{
		return self::$instance;
	}
	
	public function index(  )
	{
		
		$this->export['title'] = "Default " . $this->q->app['name'] . " handler";
		$this->export['description'] = "Default " . $this->q->app['name'] . " handler";
		$this->export['body'] = "<div class='alert'>TODO: Implement " . $this->q->app['name'] . " handling.</div>";

		return;			
	}


	public function get_respath()
	{
		if ( empty( $this->respath ) ) {
			$this->respath = '/app/' . $this->q->app['name'] . '/';
		}

		return $this->respath;
	}


}
// END qwipapp base class

