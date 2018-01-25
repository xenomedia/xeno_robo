<?php

namespace XenoMedia\XenoRobo\Robo\Drupal;

/**
 * Base class for Drupal 8 sites.
 */
class BaseDrupalD8 extends BaseDrupal {

  /**
   * Perform set up tasks.
   */
  public function setup() {
    // If .htaccess file or local settings file does not exit lets create them.
    if ((file_exists($this->getSiteRoot() . '.htaccess.default') && !file_exists($this->getSiteRoot() . '.htaccess')) || !file_exists($this->getSiteRoot() . 'sites/default/settings.local.php')) {
      $this->say("Missing .htaccess or settings.local.php");
      $name = $this->confirm("Missing .htaccess or settings.local.php Copy the default?");
      if ($name) {
        // Only copy the .htaccess.default if it default exists.
        if (file_exists($this->getSiteRoot() . '.htaccess.default')) {
          $this->_exec('cp ' . $this->getSiteRoot() . '.htaccess.default ' . $this->getSiteRoot() . '.htaccess');
        }

        $this->_exec('cp ' . $this->getSiteRoot() . 'sites/example.settings.local.php ' . $this->getSiteRoot() . 'sites/default/settings.local.php');
        $this->npmInstall();
        $this->dbGet();
        $this->siteInit = TRUE;
        $this->start();
      }
    }
    else {
      $this->siteInit = TRUE;
    }
  }

}
