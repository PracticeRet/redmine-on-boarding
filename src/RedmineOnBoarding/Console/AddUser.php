<?php
namespace RedmineOnBoarding\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Redmine\Client;
use Symfony\Component\Console\Question\ChoiceQuestion;



class AddUser extends Command
{
  private $config = null;

  public function __construct(&$config) {
    parent::__construct();
    $this->config = $config;
  }

  protected function configure() {
    $this->setName('adduser')
    ->setDescription("Add new user in your redmine app.");
  }

  protected function execute(InputInterface $input, OutputInterface $output) {


    $helper = $this->getHelper('question');

    $passQuestion = new Question('Enter password: ');
    $passQuestion->setHidden(true);
    $passQuestion->setHiddenFallback(false);


    $login = $helper->ask($input, $output, new Question('Enter Engineer login: '));
    $password = $helper->ask($input, $output, $passQuestion);
    $fname = $helper->ask($input, $output, new Question('Enter first name: '));
    $lname = $helper->ask($input, $output, new Question('Enter last name: '));
    $email = $helper->ask($input, $output, new Question('Enter email: '));


    $output->writeln("<comment>Creating user....</comment>");

    $rclient = new Client($this->config->redmine_url, $this->config->redmine_api);

    $userarray = [
        "login" => $login,
        "mail" => $email,
        "firstname" => $fname,
        "lastname" => $lname,
        "password" => $password
    ];
    $redmine_user = $rclient->user->create($userarray);

    if($redmine_user->error){
      $output->writeln("<error>{$redmine_user->error}</error>");
    }elseif($redmine_user->id){
      $output->writeln('<info>User Created:</info>');
      $output->writeln("<info>Login Name = {$redmine_user->login}</info>");
      $output->writeln("<info>Login Password = {$password}</info>");
    }
  }
}