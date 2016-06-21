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
    if (!is_dir($this->app_root . 'database')) {
      mkdir($this->app_root . 'database', $dir_permissions, true);
    }

    if (!file_exists($this->app_root . 'config/main.json')) {
      mkdir($this->app_root . 'config', $dir_permissions);
      $data = '{
"app_name":"RedmineOnBoarding",
"project_identifire":"induction"
}';
      file_put_contents($this->app_root . 'config/main.json', $data);
    }
  }
}
