<?php

	// Footer goes here
	global $_SYS;
	
	global $User;
	global $Site;
	
	global $sactions;


	$mytheme = 'Default';
	if ( isset( $User['preferences']['theme']  ) ) { 
	$mytheme = $User['preferences']['theme'];
	}
	
	$myloggedin = false;
	if ( !empty( $_SESSION['account_id'] ) )  {
		$myloggedin = true;
	}


?>
</div>

		<footer id="footer" class="footer">
		    <div class="container-fluid">

		        <div class="row-fluid">
		            <div class='span2'>
<?php 
			// Login / logout button
			print '<a href="' . $_SYS['authopts'][$_SESSION['action']][0] . '" class="btn btn-link">' 
						. $_SYS['authopts'][$_SESSION['action']][1] . '</a>';  
			
?>		            </div>
		            <div class='span6'><?php
		            
		            if ( $myloggedin ) {

						?><div class="btn-group themes-group"><a class="btn dropdown-toggle" 
								data-toggle="dropdown" href="#">Theme: <?php 
								
									print $mytheme;  

								?> <span class="caret"></span></a>
									<ul class="dropdown-menu themes"><?php
									
    									foreach ( $Site['themelist'] as $tname => $tpath  )  {
        									print "<li><a href='?theme=" . $tname . "'>" . $tname . "</a></li>";
    									}
									
									?></ul>
						</div><?php
					
					}	
		                						
		            ?></div>
		            <div class='span4 muted'>
            			<a href='http://alpineinternet.com/'>
            				<img src='//centerstage.s3.amazonaws.com/3.5.0/meta_images/alpine/poweredby.gif' 
            							alt='Provide Feedback to Development Team' 
            							title='Provide Feedback to Development Team' 
            							id='AdminPowered'
            							class='pull-right'></a>
		            </div>
		        </div>
		        <div class="row-fluid">
		            <div class='span12 muted'>
	        			<p class='muted'><br><br>
	        			<a href="http://www.alpineinternet.com/">Copyright</a> (c) 2000-<?php 
	        						print date( 'Y' )  ?>, 
	    				<a href="http://www.alpineinternet.com" 
	    						target="alpdev" >Alpine Internet Solutions</a>.  All Rights Reserved.
	        			<br><?php print "App: " . $this->app['name'];  ?>
	        			</p>
		            </div>
		        </div>
		    </div>
		</footer>

		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
		<script src="//alpine.s3.amazonaws.com/services/jquery-cookie/jquery.cookie.js"></script>
		<script src="//alpine.s3.amazonaws.com/services/jstree/jstree.min.js" 
					type="text/javascript"></script>

		<script src="//alpine.s3.amazonaws.com/services/bootstrap-2.3.1/js/bootstrap.min.js" 
				type="text/javascript" charset="utf-8"></script>

		<?php // Default Google Analytics tracking for each wave app... ?>		
		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		
		  ga('create', 'UA-39925726-1', 'alpineclients.com');
		  ga('send', 'pageview');
		
		</script>
		
		<!-- Enhanced typeahead from github /tcrosen/twitter-bootstrap-typeahead -->
		<script 
		src="//alpine.s3.amazonaws.com/services/twitter-bootstrap-typeahead/js/bootstrap-typeahead.js" 
			type="text/javascript" charset="utf-8"></script>			

<?php

   /* Always have $this->print_footer() just before the closing </body>
    * tag of your theme, or you will break many apps, which
    * generally use this hook to add elements to <body> such
    * as google analytics.
    */
   $this->print_footer();

?></body>
</html>
