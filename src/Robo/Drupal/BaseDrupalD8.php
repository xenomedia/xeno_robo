<?php

namespace XenoMedia\XenoRobo\Robo\Drupal;

/**
 * Base class for Drupal 8 sites.
 */
class BaseDrupalD8 extends BaseDrupal {

  /**
   * Perform set up tasks.
   */
  public function setup() {
    // If .htaccess file or local settings file does not exit lets create them.
    if ((file_exists($this->getSiteRoot() . '.htaccess.default') && !file_exists($this->getSiteRoot() . '.htaccess')) || !file_exists($this->getSiteRoot() . 'sites/default/settings.local.php')) {
      $this->say("Missing .htaccess or settings.local.php");
      $name = $this->confirm("Missing .htaccess or settings.local.php Copy the default?");
      if ($name) {
        // Only copy the .htaccess.default if it default exists.
        if (file_exists($this->getSiteRoot() . '.htaccess.default')) {
          $this->_exec('cp ' . $this->getSiteRoot() . '.htaccess.default ' . $this->getSiteRoot() . '.htaccess');
        }

        $this->drupalCreateSettings();
        $this->npmInstall();
        $this->dbGet();
        $this->siteInit = TRUE;
        $this->start();
      }
    }
    else {
      $this->dbGet();
      $this->siteInit = TRUE;
      $this->start();
    }
  }

  /**
   * Creates settings.local.php file.
   */
  public function drupalCreateSettings() {
    $create_file = TRUE;

    if (file_exists($this->getSiteRoot() . 'sites/default/settings.local.php')) {
      $create_file = $this->confirm("Are you sure you want to overwrite your current settings.local.php?");
    }

    if ($create_file) {
      // Copy the example settings file.
      $this->_exec('cp ' . $this->getSiteRoot() . 'sites/example.settings.local.php ' . $this->getSiteRoot() . 'sites/default/settings.local.php');

      $settings = file_get_contents($this->getDirectory() . '/../../files/Drupal/drupal8.settings.local.php');
      // Remove the opening php tags.
      $settings = str_replace('<?php', '', $settings);
      // Append the default settings.
      file_put_contents($this->getSiteRoot() . 'sites/default/settings.local.php', $settings, FILE_APPEND);
    }
  }

  /**
   * Run Drush sim after pull.
   */
  public function gitPull() {
    $current_branch = exec('git rev-parse --abbrev-ref HEAD');
    $config = ($this->getCim() == '' ? 'cim' : 'csim');

    $collection = $this->collectionBuilder();
    $collection->taskGitStack()
      ->pull()
      ->run();
    
    $name = $this->confirm("Run Config Import?");
    if ($name) {
      if ($this->getXenoVersion() == '') {
        $this->_exec('docker-compose exec --user=82 php /usr/local/bin/drush ' . $config . ' -y');
      } else {
        $this->_exec('docker-compose exec php /usr/local/bin/drush ' . $config . ' -y');
      }
    }
  }

}
