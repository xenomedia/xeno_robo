<?php

namespace XenoMedia\XenoRobo\Robo\Drupal;

use XenoMedia\XenoRobo\Robo\Drupal\BaseDrupal;

/**
 * Base class for Drupal D7 sites.
 */
class BaseDrupalD7 extends BaseDrupal {

  public function setup() {
    if (!file_exists('www/.htaccess') || !file_exists('www/sites/default/local.settings.php')) {
      $this->say("Missing .htaccess or local.setting.php");
      $name = $this->confirm("Missing .htaccess or local.setting.php Copy the default?");
      if ($name) {
        $this->_exec('cp www/.htaccess.default www/.htaccess');
        $this->_exec('cp www/sites/default/default.local.settings.php www/sites/default/local.settings.php');
        $this->siteInit = TRUE;
        $this->start();
      }
    }
    else {
      $this->siteInit = TRUE;
    }
  }
}
