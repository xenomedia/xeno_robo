<?php

namespace XenoMedia\XenoRobo\Robo\Drupal;

/**
 * Base class for Drupal 7 sites.
 */
class BaseDrupalD7 extends BaseDrupal {

  /**
   * Perform set up tasks.
   */
  public function setup() {
    // If .htaccess file or local settings file does not exit lets create them.
    if (!file_exists($this->getSiteRoot() . '.htaccess') || !file_exists($this->getSiteRoot() . 'sites/default/local.settings.php')) {
      $this->say("Missing .htaccess or local.settings.php");
      $name = $this->confirm("Missing .htaccess or local.setting.php Copy the default?");
      if ($name) {
        $this->_exec('cp ' . $this->getSiteRoot() . '.htaccess.default ' . $this->getSiteRoot() . '.htaccess');
        $this->_exec('cp ' . $this->getSiteRoot() . 'sites/default/default.local.settings.php ' . $this->getSiteRoot() . 'sites/default/local.settings.php');
        $this->npmInstall();
        $this->dbGet();
        $this->siteInit = TRUE;
        $this->start();
      }
    }
    else {
      $this->dbGet();
      $this->siteInit = TRUE;
      $this->start();
    }
  }

}
