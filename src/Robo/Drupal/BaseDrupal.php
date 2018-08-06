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
   * Pull DB Backup and refresh.
   */
  public function dbRefresh() {
    $this->dbGet();
    if ($this->getXenoVersion() == '') {
      $this->_exec('docker-compose exec --user=82 php sh -c "drush sql-drop --root=/var/www/html/web -y"');
      $this->_exec('docker-compose exec --user=82 php sh -c "drush sql-cli < mariadb-init/dump.sql --root=/var/www/html/web"');
    } else {
      $this->_exec('docker-compose exec php drush sql-drop --root=/var/www/html/web -y');
      $this->_exec('docker-compose exec php sh -c "drush sql-cli < mariadb-init/dump.sql --root=/var/www/html/web"');
    }

  }

}
