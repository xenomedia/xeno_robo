<?php

namespace XenoMedia\XenoRobo\Robo;

/**
 * Base class for Xeno robo commands.
 */
abstract class Base extends \Robo\Tasks {
  // Used to read robo.yml.dist file.
  use \NuvoleWeb\Robo\Task\Config\loadTasks;

  public $siteInit = FALSE;

  /**
   * Perform init functionality and start docker.
   *
   * You should have DockerStart.scpt file in your project.
   */
  public function start() {
    if ($this->siteInit) {
      $this->_exec('/usr/bin/osascript DockerStart.scpt ' . $this->config('site.grunt_path'));
    }
  }
}
