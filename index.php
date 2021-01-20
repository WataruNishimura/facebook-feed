<?php

/**
 * Plugin Name: facebook-feed
 */

function register_scripts()
{
  wp_enqueue_script('facebook-sdk', 'https://connect.facebook.net/en_US/sdk.js', '', '', true);
  wp_enqueue_style('admin-menu', plugin_dir_url(__FILE__) . "/assets/css/admin.css" );
}

function inline_scripts()
{
?>
  <script>
    function statusChangeCallback(response) { // Called with the results from FB.getLoginStatus().
      console.log('statusChangeCallback');
      console.log(response); // The current login status of the person.
      if (response.status === 'connected') { // Logged into your webpage and Facebook.
        console.log(response);
      } else { // Not logged into your webpage or we are unable to tell.
        document.getElementById('status').innerHTML = 'Please log ' +
          'into this webpage.';
      }
    }


    function checkLoginState() { // Called when a person is finished with the Login Button.
      FB.getLoginStatus(function(response) { // See the onlogin handler
        statusChangeCallback(response);
      });
    }

    window.fbAsyncInit = function() {
      FB.init({
        appId: 3605800966162639,
        cookie: true, // Enable cookies to allow the server to access the session.
        xfbml: true, // Parse social plugins on this webpage.
        version: 'v9.0' // Use this Graph API version for this call.
      });


      FB.getLoginStatus(function(response) { // Called after the JS SDK has been initialized.
        statusChangeCallback(response); // Returns the login status.
      });
    };
  </script>
<?php
}

require_once("admin_menu.php");

add_action('admin_print_scripts', 'inline_scripts');
add_action('admin_enqueue_scripts', 'register_scripts');
add_action('init', 'FacebookFeedBanner::init');
