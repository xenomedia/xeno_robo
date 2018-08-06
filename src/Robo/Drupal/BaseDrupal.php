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

  /**
   * Run Drush cim after pull.
   */
  public function dbRefresh() {
    $this->dbGet();
    $this->_exec('docker-compose exec --user=82 php /usr/local/bin/drush sql-drop -y');
    $this->_exec('docker-compose exec --user=82 php /usr/local/bin/drush sql-cli < mariadb-init/dump.sql');
  }

}
