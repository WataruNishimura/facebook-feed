<?php
require_once("vendor/autoload.php");

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

const PLUGIN_PREFIX = "ffd_";

const PLUGIN_SETTING_GROUP = PLUGIN_PREFIX . "settings_options";

class FB_LOGIN
{

  private $fb_app_id = "";
  private $fb_app_secret = "";

  public function __construct($FB_APP_ID, $FB_APP_SECRET)
  {
    $this->fb_app_id = $FB_APP_ID;
    $this->fb_app_secret = $FB_APP_SECRET;
  }

  public function init()
  {
    if ($this->fb_app_id && $this->fb_app_secret) :
      $fb = new Facebook\Facebook([
        'app_id' => $this->fb_app_id,
        'app_secret' => $this->fb_app_secret,
      ]);

      return $fb;
    endif;
  }
}

// Register plugin settings.

