<?php

namespace XenoMedia\XenoRobo\Robo\Drupal;

use XenoMedia\XenoRobo\Robo\Base;

/**
 * Base class for Drupal sites.
 */
abstract class BaseDrupal extends Base {


  /**
   * Perform set up tasks.
   */
  abstract public function setup();

}
