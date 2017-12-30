<?php

namespace XenoMedia\XenoRobo\Robo;

use Robo\Tasks as Tasks;

/**
 * Base class for Xeno robo commands.
 */
abstract class Base extends Tasks {
  // Used to read robo.yml.dist file.
  use \NuvoleWeb\Robo\Task\Config\loadTasks;

  /**
   * Set to TRUE if site has been inited.
   *
   * @var bool
   */
  public $siteInit = FALSE;

  /**
   * Perform init functionality and start docker.
   *
   * You should have DockerStart.scpt file in your project.
   */
  public function start() {
    if ($this->siteInit) {
      $this->_exec('/usr/bin/osascript DockerStart.scpt ' . $this->getGruntPath());
    }
  }

  /**
   * Get grunt path set in config file.
   */
  public function getGruntPath() {
    return $this->config('site.grunt_path');
  }

  /**
   * Get site root path.
   */
  public function getSiteRoot() {
    $path = $this->config('site.root_path');

    // If path make sure it has trailing slash.
    if ($path) {
      $path = $this->endWithTrailingSlash($path);
    }

    return $path;
  }

  /**
   * End string with trailing slash.
   *
   * @param string $string
   *   String to update.
   *
   * @return string
   *   Updated string ending with trailing slash.
   */
  private function endWithTrailingSlash($string) {
    // Remove and add a slash at the end.
    $string = rtrim($string, '/') . '/';

    return $string;
  }

  /**
   * Run npm install on your grunt path.
   *
   * @return $this
   */
  public function npmInstall() {
    if ($this->getGruntPath()) {
      return $this->taskNpmInstall()
        ->dir($this->getGruntPath())
        ->run();
    }
  }

}
