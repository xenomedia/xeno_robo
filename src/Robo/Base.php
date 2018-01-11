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
   * Pull live database from last nights backup.
   *
   * @command get-db
   */
  public function getDb() {
    // If mariadb-init directory doesn't exist create it.
    $this->_exec('mkdir -p mariadb-init');

    // Remove the dump file if it exists.
    $this->taskFilesystemStack()
      ->remove('mariadb-init/dump.sql');

    // If it has Pantheon info get Pantheon dump.
    if ($pantheon = $this->getPantheonInfo()) {
      // Get database from Pantheon.
      $this->_exec('terminus backup:create ' . $pantheon['site_name'] . '.' . $pantheon['env'] . ' --element=db');
      $this->_exec('terminus backup:get ' . $pantheon['site_name'] . '.' . $pantheon['env'] . ' --element=db --to=mariadb-init/dump.sql.gz');
      $this->_exec('gunzip mariadb-init/dump.sql.gz');
    }
  }

  /**
   * Halt containers and cleanup network.
   */
  public function halt() {
    $this->_exec('docker-compose stop');
    $this->_exec('docker-sync stop');
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
   * Get Patheon info.
   */
  public function getPantheonInfo() {
    return $this->config('pantheon');
  }

}
