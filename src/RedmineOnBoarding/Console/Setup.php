<?php
namespace RedmineOnBoarding\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Setup extends Command
{
  private $config = null;

  public function __construct(&$config) {
    parent::__construct();
    $this->config = $config;
  }

  protected function configure() {
    $this->setName('setup')
    ->setDescription("Create config file in config > main.json with default values");
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->config->setup();
  }
}
