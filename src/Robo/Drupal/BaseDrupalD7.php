<?php

namespace XenoMedia\XenoRobo\Robo\Drupal;

/**
 * Base class for Drupal D7 sites.
 */
class BaseDrupalD7 extends BaseDrupal {

  /**
   * {@inheritdoc}
   */
  public function setup() {
    if (!file_exists($this->getSiteRoot() . '.htaccess') || !file_exists($this->getSiteRoot() . 'sites/default/local.settings.php')) {
      $this->say("Missing .htaccess or local.setting.php");
      $name = $this->confirm("Missing .htaccess or local.setting.php Copy the default?");
      if ($name) {
        $this->_exec('cp ' . $this->getSiteRoot() . '.htaccess.default ' . $this->getSiteRoot() . '.htaccess');
        $this->_exec('cp ' . $this->getSiteRoot() . 'sites/default/default.local.settings.php ' . $this->$this->getSiteRoot() . 'sites/default/local.settings.php');
        $this->siteInit = TRUE;
        $this->start();
      }
    }
    else {
      $this->siteInit = TRUE;
    }
  }

}
