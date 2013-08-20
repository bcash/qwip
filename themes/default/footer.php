<?php

    $mytheme = 'Default';
    if ( isset( $_SESSION['preferences']['theme']  ) ) { 
        $mytheme = $_SESSION['preferences']['theme'];
    }
    
    $amiloggedin = false;
    if ( !empty( $_SESSION['account_id'] ) )  {
        $amiloggedin = true;
    }


?>
</div>

    <footer id="footer" class="footer">
        <div class="container-fluid">

            <div class="row-fluid">
                <div class='span2'> <!-- login / logout link -->
                </div>
                <div class='span6'><?php
                    
                    if ( $amiloggedin ) {

                        ?><div class="btn-group themes-group"><a class="btn dropdown-toggle" 
                                data-toggle="dropdown" href="#">Theme: <?php 
                                
                                    print $mytheme;  

                                ?> <span class="caret"></span></a>
                                    <ul class="dropdown-menu themes"><?php
                                    
                                        foreach ( $_SESSION['themelist'] as $tname => $tpath  )  {
                                            print "<li><a href='?theme=" . $tname . "'>" . $tname . "</a></li>";
                                        }
                                    
                                    ?></ul>
                        </div><?php
                    
                    }   
                                                
                    ?></div>
                    <div class='span4 muted'>
                        <a href='http://alpineinternet.com/'>
                            <img src='//s3.amazonaws.com/centerstage/3.5.0/meta_images/alpine/poweredby.gif' 
                                        alt='Provide Feedback to Development Team' 
                                        title='Provide Feedback to Development Team' 
                                        id='AdminPowered'
                                        class='pull-right'></a>
                    </div>
                </div>
                <div class="row-fluid">
                    <div class='span12 muted'>
                        <p class='muted'><br><br>
                        <a href="http://www.briancash.com/">Copyright</a> (c) 2000-<?php 
                                    print date( 'Y' )  ?>, 
                        <a href="http://www.briancash.com" 
                                target="alpdev" >Brian Cash</a>.  All Rights Reserved.
                        <br><?php print "App: " . $this->app['name'];  ?>
                        </p>
                    </div>
                </div>
            </div>
        </footer>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>

        <script src="//s3.amazonaws.com/alpine/services/bootstrap-2.3.1/js/bootstrap.min.js" 
                type="text/javascript" charset="utf-8"></script>

        <?php // Default Google Analytics tracking for each app... ?>       
        <script type="text/javascript">
        
          var _gaq = _gaq || [];
          _gaq.push(['_setAccount', '<?php echo $this->macros['ga_tracking_id']; ?>']);
          _gaq.push(['_trackPageview']);
        
          (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
          })();
        
        </script>

<?php

   /* Always have $this->print_footer() just before the closing </body>
    * tag of your theme, or you will break many apps, which
    * generally use this hook to add elements to <body>.
    */
   $this->print_footer();

?></body>
</html>
