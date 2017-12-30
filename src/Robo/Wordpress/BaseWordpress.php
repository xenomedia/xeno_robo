<?php

namespace XenoMedia\XenoRobo\Robo\Wordpress;

use XenoMedia\XenoRobo\Robo\Base;

/**
 * Base class for Wordpress sites.
 */
abstract class BaseWordpress extends Base {

  /**
   * Perform init functionality and start docker.
   *
   * You should have DockerStart.scpt file in your project.
   */
  public function start() {
    $this->setup();
    parent::start();
  }

  /**
   * Perform set up tasks.
   */
  public function setup() {
    if (!file_exists($this->getSiteRoot() . 'wp-config.php') {
      $this->_exec('cp ' . $this->getSiteRoot() . 'default.wp-config.php ' . $this->getSiteRoot() . 'wp-config.php');
      $this->npmInstall();
      $this->siteInit = TRUE;
      $this->start();
    }
    else {
      $this->siteInit = TRUE;
    }
  }

}
