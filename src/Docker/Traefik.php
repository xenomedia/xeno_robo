<?php

namespace XenoMedia\XenoRobo\Docker;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Handles Traefik functionality.
 */
class Traefik {

  /**
   * The name of the project using Traefik.
   *
   * @var string
   */
  protected $name;

  /**
   * Is the project using Solr.
   *
   * @var string
   */
  protected $solr;

  /**
   * Traefik constructor.
   *
   * @param string $name
   *   Name of the network to be created which needs to match the container
   *   prefix of your project you would like to handle with Traefik.
   */
  public function __construct($name, $solr) {
    $this->name = str_replace('-', '', $name);
    $this->solr = $solr;
  }

  /**
   * Update the Traefik container.
   */
  public function update() {
    $solr = $this->solr;
    // Update host wider traefik container.
    $traefikPath = $this->getTraefikPath();
    $traefikFile = $this->getTraefikFile();

    /* @var FileSystem $fs */
    $fs = new Filesystem();
    if (!$fs->exists($traefikFile)) {
      exec('git clone git@github.com:xenomedia/traefik.git ' . $traefikPath);
    }

    $traefik = $this->getTraefikContents();

    if (!in_array($this->name, $traefik['services']['traefik']['networks'])) {
      $traefik['services']['traefik']['networks'][] = $this->name;
      $traefik['networks'][$this->name] = [
        'external' => [
          'name' => $this->name . '_default',
        ],
      ];
      file_put_contents($traefikFile, Yaml::dump($traefik, 9, 2));
      exec('docker network create ' . $this->name . '_default');
    }

    if ($solr == 'true' && !in_array($this->name, $traefik['services']['solr']['networks'])) {
      $traefik['services']['solr']['networks'][] = $this->name;
      file_put_contents($traefikFile, Yaml::dump($traefik, 9, 2));
    }
    $this->restart();
  }

  /**
   * Restarts docker Traefik container.
   */
  public function restart() {
    exec('docker-compose -f ' . $this->getTraefikFile() . ' --project-name traefik stop');
    exec('docker-compose -f ' . $this->getTraefikFile() . ' --project-name traefik up -d');
  }

  /**
   * Stops docker Traefik container.
   */
  public function stop() {
    exec('docker-compose -f ' . $this->getTraefikFile() . ' --project-name traefik stop');
  }

  /**
   * Remove project from Traefik docker-composer.yml.
   */
  public function remove() {
    $solr = $this->solr;
    $traefik = $this->getTraefikContents();
    if (isset($traefik['services']['traefik'])) {

      foreach ($traefik['services']['traefik']['networks'] as $key => $network) {
        if ($network == $this->name) {
          unset($traefik['services']['traefik']['networks'][$key]);
        }
      }

      $traefik['services']['traefik']['networks'] = array_values($traefik['services']['traefik']['networks']);
      unset($traefik['networks'][$this->name]);

      file_put_contents($this->getTraefikFile(), Yaml::dump($traefik, 9, 2));
    }
    if ($solr == 'true' && isset($traefik['services']['solr'])) {

      foreach ($traefik['services']['solr']['networks'] as $key => $network) {
        if ($network == $this->name) {
          unset($traefik['services']['solr']['networks'][$key]);
        }
      }

      $traefik['services']['solr']['networks'] = array_values($traefik['services']['solr']['networks']);
      unset($traefik['networks'][$this->name]);

      file_put_contents($this->getTraefikFile(), Yaml::dump($traefik, 9, 2));
    }
    $this->restart();
  }

  /**
   * Remove project from Traefik docker-composer.yml.
   */
  public function removeNetwork() {
    exec('docker network rm ' . $this->name . '_default');
  }

  /**
   * Get Traefik docker-compose.yml file.
   *
   * @return array
   *   Array from yaml file.
   */
  private function getTraefikContents() {
    return Yaml::parse(file_get_contents($this->getTraefikFile()));
  }

  /**
   * Get Traefik path.
   *
   * @return string
   *   Returns traefik path.
   */
  private function getTraefikPath() {
    return $_SERVER['HOME'] . '/Sites/traefik';
  }

  /**
   * Get Traefik file.
   *
   * @return string
   *   Returns traefik file path.
   */
  private function getTraefikFile() {
    return $this->getTraefikPath() . '/docker-compose.yml';
  }

}
