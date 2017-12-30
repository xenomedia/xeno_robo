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
    $this->_exec('cp www/default.wp-config.php www/wp-config.php');
  }

}
