<?php if ( ! defined('QWIP_BASE_PATH')) exit('No direct script access allowed');

include_once "qwipapp/index.php";

class menu extends qwipapp {

	/**
	 * Constructor
	 */
	public function __construct()
	{
	
		// Call parent constructor
		parent::__construct();
		
		// Override auth required
		$this->authreq = false;
		
		return;		
	}


	/**
	 */
	public function index()
	{
		global $_SYS;
			
		// print "<BR>Menu Loaded and running index.";
		$this->export['title'] = "Not Found";
		$this->export['body'] = "Sorry, this page not found.";


		return;			
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */