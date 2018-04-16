# Xeno Robo
Robo commands for developing websites at Xeno Media.

## Getting Started

### Installation

Install with Composer by running:

```bash
composer global require xenomedia/xeno_robo
```

or

```bash
composer global require consolidation/cgr
composer global remove consolidation/robo
cgr xenomedia/xeno_robo
```

### Create Robo File

In the root of your project create `RoboFile.php` file. Extend the class based
on the project you are working on.

Example Drupal 7 RoboFile.php:

```php
<?php

use XenoMedia\XenoRobo\Robo\Drupal\BaseDrupalD7;

/**
 * Provides Drupal 7 robo commands.
 */
class RoboFile extends BaseDrupalD7 {

}

?>
```

Example Drupal 8 RoboFile.php:

```php
<?php

use XenoMedia\XenoRobo\Robo\Drupal\BaseDrupalD8;

/**
 * Provides Drupal 8 robo commands.
 */
class RoboFile extends BaseDrupalD8 {

}

?>
```

Example Wordpress Robo File:

```php
<?php

use XenoMedia\XenoRobo\Robo\Wordpress\BaseWordpress;

/**
 * Provides Wordpress robo commands.
 */
class RoboFile extends BaseWordpress {

}

?>
```

### Create robo.yml.dist file

Create a yml file that looks like this in the root of your project:

```yaml
site:
  grunt_path: # Leave blank if no grunt.
  root_path: # Leave blank if same as project path.
  live_domain: # Used for WP Search and replace
  local_domain: # Used for WP Search and replace
database:
  database:
  user:
  password:
# `robo db:get` settings
# Pull the DB from Pantheon.
pantheon:
  site_name:
  env:
# Pull the DB from SSH.
stage:
  site_name: # The name of the *sql.gz file to get. If the file name is `example.sql.gz` then enter `example`
  user: # Staging ssh user.
  host: # Staging ssh host.
  port: # SSH port number.
  backup_location: # Path to directory where backups are stored.
```
