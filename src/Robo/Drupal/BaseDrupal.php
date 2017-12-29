<?php

namespace XenoMedia\XenoRobo\Robo\Drupal;

use XenoMedia\XenoRobo\Robo\Base;

/**
 * Base class for Drupal sites.
 */
abstract class BaseDrupal extends Base {

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
  abstract public function setup();
}
