<?php

namespace XenoMedia\XenoRobo\Robo\Wordpress;

use XenoMedia\XenoRobo\Robo\Base;

/**
 * Base class for Wordpress sites.
 */
abstract class BaseWordpress extends Base {

  /**
   * Perform set up tasks.
   */
  public function setup() {
    if (!file_exists($this->getSiteRoot() . 'wp-config.php')) {
      $this->_exec('cp ' . $this->getSiteRoot() . 'default.wp-config.php ' . $this->getSiteRoot() . 'wp-config.php');
      $this->_exec('cp ' . $this->getSiteRoot() . '.htaccess.default ' . $this->getSiteRoot() . '.htaccess');
      $this->npmInstall();
      $this->dbGet();
      $this->siteInit = TRUE;
      $this->start();
    }
    else {
      $this->dbGet();
      $this->siteInit = TRUE;
      $this->start();
    }
  }

  /**
   * Find and replace live with local domain..
   */
  public function wpSearch() {
    if ($this->getXenoVersion() == '') {
      $this->_exec("docker-compose exec --user=82 php wp --path=/var/www/html/" . $this->getSiteRoot() . " search-replace '" . $this->getLiveDomain() . "' '" . $this->getLocalDomain() . "'  --skip-columns=guid");
      $this->_exec("docker-compose exec --user=82 php wp --path=/var/www/html/" . $this->getSiteRoot() . " cache flush");
    } else {
      $this->_exec("docker-compose exec php wp --path=/var/www/html/" . $this->getSiteRoot() . " search-replace '" . $this->getLiveDomain() . "' '" . $this->getLocalDomain() . "'  --skip-columns=guid");
      $this->_exec("docker-compose exec php wp --path=/var/www/html/" . $this->getSiteRoot() . " cache flush");
    }
  }

    /**
   * Pull DB Backup and refresh.
   */
  public function dbRefresh() {
    $this->dbGet();
    if ($this->getXenoVersion() == '') {
      $this->_exec('docker-compose exec --user=82 php sh -c "wp db import mariadb-init/dump.sql --path=/var/www/html/web"');
    }
    else {
      $this->_exec('docker-compose exec php sh -c "wp db import mariadb-init/dump.sql --path=/var/www/html/web"');
    }

    $this->wpSearch();
  }

}
