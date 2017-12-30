# Xeno Robo
Robo commands for developing websites at Xeno Media.

## Getting Started

### Installation

Install with Composer by running:

```
composer global require xenomedia/xeno_robo
```

### Create Robo File

In the root of your project create `RoboFile.php` file. Extend the class based on
the project you are working on.

Example Drupal 7 RoboFile.php:

```
<?php

use XenoMedia\XenoRobo\Robo\Drupal\BaseDrupalD7;

class RoboFile extends BaseDrupalD7 {

}
```

Example Drupal 8 RoboFile.php:

```
<?php

use XenoMedia\XenoRobo\Robo\Drupal\BaseDrupalD8;

class RoboFile extends BaseDrupalD8 {

}
```

Example Wordpress Robo File:

```
<?php

use XenoMedia\XenoRobo\Robo\Wordpress\BaseWordpress;

class RoboFile extends BaseWordpress {

}
```

### Create robo.yml.dist file

Create a yml file that looks like this in the root of your project:

```
site:
  grunt_path: # Leave blank if no grunt.
  root_path: # Leave blank if same as project path.
```
