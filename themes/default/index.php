<?php

  // Main execution flow of the base theme

  // Load the header template
  $this->get_header(); 

  // Load the main content of the site
  // This content comes from our primary function
  $this->get_content();

  // Load the sidebar template
  $this->get_sidebar();

  // Load the footer template
  $this->get_footer();

?>
