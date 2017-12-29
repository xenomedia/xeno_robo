# Xeno Robo
Robo commands for developing at Xeno Media.

## Getting Started

### Create Robo File

In the root of your project create `Robo.php` file. Extend the class based on
the project you are working on.

Example Drupal 7 Robo file:

```
<?php

use XenoMedia\XenoRobo\Robo\Drupal\BaseDrupalD7;

class RoboFile extends BaseDrupalD7 {

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
