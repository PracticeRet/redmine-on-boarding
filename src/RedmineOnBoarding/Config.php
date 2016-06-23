<?php
namespace RedmineOnBoarding;

class Config
{

  private $_attributes;

  public function __construct($runtime_config = array()) {
    $this->_attributes = $runtime_config;
    $this->process();
  }

  public function __get($property) {
    return isset($this->_attributes[$property]) ? $this->_attributes[$property] : false;
  }

  public function __set($property, $value) {
    $this->_attributes[$property] = $value;
    return true;
  }

  public function __isset($property) {
    return isset($this->_attributes[$property]) ? true : false;
  }

  public function __unset($property) {
    unset($this->_attributes[$property]);
  }

  public function process() {
    $this->_attributes['app_root'] = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
    $global_config = array();
    if (file_exists($this->app_root . 'config/main.json')) {
      $global_config = json_decode(file_get_contents($this->app_root . 'config' . DIRECTORY_SEPARATOR . 'main.json'), true);
    }
    $this->_attributes = array_merge((array)$global_config, (array)$this->_attributes);
  }

  public function setup() {
    $dir_permissions = 0700;
    if (!is_dir($this->app_root . 'assets')) {
      mkdir($this->app_root . 'assets', $dir_permissions, true);
    }
    if (!is_dir($this->app_root . 'config')) {
      mkdir($this->app_root . 'config', $dir_permissions, true);
    }

    if (!file_exists($this->app_root . 'assets/common.csv')) {
      $data = '"subject","assign_to_login","is_private","description"'."\n";
      $data .= '"As a user do this task","hr","yes","issue description will go here"';
      file_put_contents($this->app_root . 'assets/common.csv', $data);
    }

    if (!file_exists($this->app_root . 'assets/backend-engineer.csv')) {
      $data = '"subject","assign_to_login","is_private","description"'."\n";
      $data .= '"As a developer, Read all git tutorial define in wiki","","","issue description"';
      file_put_contents($this->app_root . 'assets/backend-engineer.csv', $data);
    }

    if (!file_exists($this->app_root . 'config/main.json')) {
      $data = '{
  "app_name":"RedmineOnBoarding",
  "redmine_api": "xxxxxxxxxx",
  "redmine_url": "http://your-redmine-site.com",
  "redmine_project_name": "project name",
  "redmine_tracker": "Feature",
  "redmine_status": "New",
  "engineer_types": [
    {"type":"Backend Engineer","file":"backend-engineer.csv", "default": true}
  ],
  "engineer_role":{"dinesh":"Manager","narinderk":"Manager","tarunjangra":"Manager","hr":"Manager"}
}';
      file_put_contents($this->app_root . 'config/main.json', $data);
    }
  }
}
