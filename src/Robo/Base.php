<?php

namespace XenoMedia\XenoRobo\Robo;

use Robo\Tasks as Tasks;

/**
 * Base class for Xeno robo commands.
 */
abstract class Base extends Tasks {
  // Used to read robo.yml.dist file.
  use \NuvoleWeb\Robo\Task\Config\loadTasks;

  const DUMP_FILE = 'dump.sql';

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
   * Backup Database and stop containers.
   */
  public function stop() {
    $this->backupDb();
    $this->halt();
  }

  /**
   * Backup database from docker site.
   */
  public function backupDb() {
    $this->_exec('docker-compose exec --user=82 mariadb /usr/bin/mysqldump -u drupal --password=drupal drupal > mariadb-init/' . self::DUMP_FILE);
  }

  /**
   * Halt containers and cleanup network.
   */
  public function halt() {
    $this->_exec('docker-compose stop');
    $this->_exec('docker-sync stop');
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
    if (file_exists('mariadb-init/' . self::DUMP_FILE) || file_exists('mariadb-init/' . self::DUMP_FILE . '.gz')) {
      $this->_exec('rm mariadb-init/*');
    }

    // If it has Pantheon info get Pantheon dump.
    if ($pantheon = $this->getPantheonInfo()) {
      // Get database from Pantheon.
      $this->_exec('terminus backup:create ' . $pantheon['site_name'] . '.' . $pantheon['env'] . ' --element=db');
      $this->_exec('terminus backup:get ' . $pantheon['site_name'] . '.' . $pantheon['env'] . ' --element=db --to=mariadb-init/' . self::DUMP_FILE . '.gz');
      $this->_exec('gunzip mariadb-init/' . self::DUMP_FILE . '.gz');
    }
    // If it has Stage info get database dump from staging.
    elseif ($stage = $this->getStageInfo()) {
      $this->_exec("scp {$stage['user']}@{$stage['host']}:{$stage['backup_location']}/{$stage['site_name']}.sql.gz mariadb-init/" . self::DUMP_FILE . ".gz");
      $this->_exec('gunzip mariadb-init/' . self::DUMP_FILE . '.gz');
    }
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
   * Runs composer install.
   *
   * @return $this
   */
  public function composerInstall() {
    return $this->taskComposerInstall()->run();
  }

  /**
   * Remove docker containers and volumes.
   */
  public function dockerClean() {
    $name = $this->confirm("This will remove all containers and volumes. Are you sure?");
    if ($name) {
      $this->_exec('docker-sync-stack clean');
    }
  }

  /**
   * Merge Master with current branch.
   *
   * @return mixed
   *   Value of the collection.
   */
  public function gitMaster() {
    $current_branch = exec('git rev-parse --abbrev-ref HEAD');

    $collection = $this->collectionBuilder();
    $collection->taskGitStack()
      ->checkout('master')
      ->pull('origin', 'master')
      ->checkout($current_branch)
      ->merge('master')
      ->completion($this->taskGitStack()->push('origin', $current_branch));

    return $collection;
  }

  /**
   * Publish current branch to master.
   *
   * @return mixed
   *   Value of the collection.
   */
  public function gitPublish() {
    $current_branch = exec('git rev-parse --abbrev-ref HEAD');

    $collection = $this->collectionBuilder();
    $collection->taskGitStack()
      ->checkout('master')
      ->merge($current_branch)
      ->completion($this->taskGitStack()->push('origin', 'master'))
      ->completion($this->taskGitStack()->checkout($current_branch));

    return $collection;
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

  /**
   * Get Stage info.
   */
  public function getStageInfo() {
    return $this->config('stage');
  }

}
