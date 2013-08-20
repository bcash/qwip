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
		$this->authreq = true;

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