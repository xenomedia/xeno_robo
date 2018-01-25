<?php

namespace XenoMedia\XenoRobo\Robo\Wordpress;

use XenoMedia\XenoRobo\Robo\Base;

/**
 * Base class for Wordpress sites.
 */
abstract class BaseWordpress extends Base {

  /**
   * Perform init functionality and start docker.
   *
   * You should have DockerStart.scpt file in your project.
   */
  public function start() {
    $this->setup();
    parent::start();
  }

  /**
   * Perform set up tasks.
   */
  public function setup() {
    if (!file_exists($this->getSiteRoot() . 'wp-config.php')) {
      $this->_exec('cp ' . $this->getSiteRoot() . 'default.wp-config.php ' . $this->getSiteRoot() . 'wp-config.php');
      $this->npmInstall();
      $this->dbGet();
      $this->siteInit = TRUE;
      $this->start();
    }
    else {
      $this->siteInit = TRUE;
    }
  }

  /**
   * Find and replace live with local domain..
   */
  public function wpSearch() {
    $this->_exec("docker-compose exec --user=82 php wp --path=/var/www/html/web search-replace '" . $this->getLiveDomain() . "' '" . $this->getLocalDomain() . "'  --skip-columns=guid");
    $this->_exec("docker-compose exec --user=82 php wp --path=/var/www/html/web cache flush");
  }

}
