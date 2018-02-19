<?php

namespace XenoMedia\XenoRobo\Robo;

use Robo\Tasks as Tasks;
use XenoMedia\XenoRobo\Docker\Traefik as Traefik;

/**
 * Base class for Xeno robo commands.
 */
abstract class Base extends Tasks {

  // Used to read robo.yml.dist file.
  use \NuvoleWeb\Robo\Task\Config\loadTasks;

  const DUMP_FILE = 'dump.sql';

  /**
   * Perform init functionality and start docker.
   *
   * You should have DockerStart.scpt file in your project.
   */
  public function start() {
    $this->traefikUpdate();

    $pathToDockerStart = $this->getDirectory() . '/../../files/Docker/';
    // If there is a DockerStart file locally use the one that is local.
    if (file_exists('DockerStart.scpt')) {
      $pathToDockerStart = '';
    }

    $this->_exec('/usr/bin/osascript ' . $pathToDockerStart . 'DockerStart.scpt ' . $this->getProjectdir() . ' ' . $this->getGruntPath());
  }

  /**
   * Backup Database and stop containers.
   */
  public function stop() {
    $this->dbBackup();
    $this->halt();
  }

  /**
   * Halt containers and cleanup network.
   */
  public function halt() {
    $this->traefikRemove();
    if (file_exists('docker-compose-dev.yml')) {
      $this->_exec('docker-compose -f docker-compose.yml -f docker-compose-dev.yml stop');
    }
    else {
      $this->_exec('docker-compose stop');
    }
    $this->traefikRemoveNetwork();
  }

  /**
   * Backup database from docker site.
   */
  public function dbBackup() {
    $db = $this->getDatabaseInfo();
    $this->_exec("docker-compose exec --user=82 mariadb /usr/bin/mysqldump -u {$db['user']} --password={$db['password']} {$db['database']} > mariadb-init/" . self::DUMP_FILE);
  }

  /**
   * Pull live database from last nights backup.
   */
  public function dbGet() {
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
   */
  public function composerInstall() {
    $this->taskComposerInstall()->run();
  }

  /**
   * Runs docker compose command.
   */
  public function dockerCompose() {
    if (file_exists('docker-compose-dev.yml')) {
      $this->_exec('docker-compose -f docker-compose.yml -f docker-compose-dev.yml up');
    }
    else {
      $this->_exec('docker-compose up');
    }
  }

  /**
   * Remove docker containers and volumes.
   */
  public function dockerClean() {
    $name = $this->confirm("This will remove all containers and volumes. Are you sure?");
    if ($name) {
      $this->_exec('docker-compose rm -f');
    }
  }

  /**
   * Access php on docker.
   */
  public function shellPhp() {
    $this->_exec('docker-compose exec --user=82 php sh');
  }

  /**
   * Access php on docker.
   */
  public function shellPwd() {
    return $this->getDirectory() . '\n' . $this->getProjectdir();
  }

  /**
   * Run Behat tests.
   */
  public function test() {
    $this->taskExec('docker-compose')
      ->args(['exec', '--user=82', 'php', './vendor/bin/behat'])
      ->option('colors')
      ->option('format', 'progress')
      ->run();
  }

  /**
   * Cherry pick current branch to master.
   *
   * @return mixed
   *   Value of the collection.
   */
  public function gitCp() {
    $current_branch = exec('git rev-parse --abbrev-ref HEAD');

    $collection = $this->collectionBuilder();
    $collection->taskGitStack()
      ->checkout('master')
      ->exec('git cherry-pick ' . $current_branch)
      ->completion($this->taskGitStack()->push('origin', 'master'))
      ->completion($this->taskGitStack()->checkout($current_branch));

    return $collection;
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
      ->completion($this->taskGitStack()
        ->push('origin', $current_branch));

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
   * Updates Traefik container with current project info.
   */
  public function traefikUpdate() {
    $traefik = new Traefik($this->getCurrentDirectory());
    $traefik->update();
  }

  /**
   * Restarts Traefik container.
   */
  public function traefikStop() {
    $traefik = new Traefik($this->getCurrentDirectory());
    $traefik->stop();
  }

  /**
   * Restarts Traefik container.
   */
  public function traefikRestart() {
    $traefik = new Traefik($this->getCurrentDirectory());
    $traefik->restart();
  }

  /**
   * Remove project from Traefik container.
   */
  public function traefikRemove() {
    $traefik = new Traefik($this->getCurrentDirectory());
    $traefik->remove();
  }

  /**
   * Remove project from Traefik container.
   */
  public function traefikRemoveNetwork() {
    $traefik = new Traefik($this->getCurrentDirectory());
    $traefik->removeNetwork();
  }

  /**
   * Get the current directory of project.
   *
   * @return string
   *   Directory name.
   */
  public function getCurrentDirectory() {
    return exec('pwd | xargs basename');
  }

  /**
   * Get grunt path set in config file.
   */
  public function getGruntPath() {
    return $this->config('site.grunt_path');
  }

  /**
   * Get Live Domain set in config file.
   */
  public function getLiveDomain() {
    return $this->config('site.live_domain');
  }

  /**
   * Get Local Domain set in config file.
   */
  public function getLocalDomain() {
    return $this->config('site.local_domain');
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
   * Get Database info.
   */
  public function getDatabaseInfo() {
    return $this->config('database');
  }

  /**
   * Get Current Directory.
   */
  public function getDirectory() {
    return __DIR__;
  }

  /**
   * Get Project Directory.
   */
  public function getProjectdir() {
    return getcwd();
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
