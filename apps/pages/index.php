<?php if ( ! defined('QWIP_BASE_PATH')) exit('No direct script access allowed');

include_once "qwipapp/index.php";

class pages extends qwipapp {

	/**
	 * Constructor
	 */
	public function __construct()
	{
	
		// Call parent constructor
		parent::__construct();

		// No auth required
		$this->authreq = false;
		
		return;		
	}

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{
	
		// global $_SYS;

		$this->export['title'] = 'Default Page';
		
		$this->export['description'] = 'Default page description';

		// Start output buffer
		ob_start();

		$page = 'index.html';
		$params = '';
		
		$last_req = end ( $this->q->routes['req'] );
		if ( !empty( $last_req ) ) {
			$page = $last_req;
			$pos = strpos($page, '?');
			if ( $pos !== false  ) {
				$_SYS['debug'][] = 'split';
				list( $page, $params ) = explode( '?', $page, 2 );
				$_SYS['debug'][] = '1st Page defined: ' . $page;
				$_SYS['debug'][] = '1st Params defined: ' . $params;
				if ( empty( $page ) ) {
					$page = 'index.html';
				}
			}
		}

		// Activate scroll spy
		$this->q->app['instance']->on_ready[] = "$('body').scrollspy({ target: '#topnav' })";


		// Remove last item from routes, add back, combine to page path
		array_pop( $this->q->routes['req'] );
		array_push( $this->q->routes['req'], $page );
		
		// combine to path
		$page = implode( DS, $this->q->routes['req'] );			

		$proot = $this->q->path['app'] . DS . $this->q->app['name'] . DS . 'root';

		// Evaluate our page
		if ( file_exists( $proot . DS . $page ) ) {
			include_once( $proot . DS . $page );
		} else {
			$this->q->showxhead( __FUNCTION__, "qwip out.  Showing 404. " );
			// Show default 404 message as a last resort.
			print $this->q->get_404();
		}

		// Close output buffer, save to body.
		$this->export['body'] = ob_get_contents();
		ob_end_clean();

		return;
	}

}

?>