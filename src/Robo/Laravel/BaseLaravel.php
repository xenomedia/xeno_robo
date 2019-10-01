<?php

namespace XenoMedia\XenoRobo\Robo\Laravel;

use XenoMedia\XenoRobo\Robo\Base;

/**
 * Base class for Laravel sites.
 */
abstract class BaseLaravel extends Base {

  /**
   * Perform set up tasks.
   */
  public function setup() {
    if (!file_exists('.env')) {
      $this->_exec('cp .env.example .env');
      $this->npmInstall();
      $this->composerInstall();
      $this->dbRefresh();
      $this->siteInit = TRUE;
      $this->start();
    }
    else {
      $this->dbRefresh();
      $this->siteInit = TRUE;
      $this->start();
    }
  }

  /**
   * Refresh database.
   */
  public function dbRefresh() {
    $this->dbGet();
    $databaseInfo = $this->getDatabaseInfo();
    $this->_exec('docker-compose exec php sh -c "mysql -u ' . $databaseInfo['user'] . ' --password=' . $databaseInfo['password'] .' -h ' . $databaseInfo['host'] .' ' . $databaseInfo['database'] .' < mariadb-init/dump.sql"');
  }

}
