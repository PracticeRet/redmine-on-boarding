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
use League\Csv\Reader;



class ImportIssues extends Command
{
  private $config = null;
  private $limit = 200;

  public function __construct(&$config) {
    parent::__construct();
    $this->config = $config;
  }

  protected function configure() {
    $this->setName('import')
    ->setDescription("Import induction stories in issue Redmin application.")
        ->addOption(
            'login',
            null,
            InputOption::VALUE_REQUIRED,
            'Provide username of redmine to assign issues.'
        );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    if (!ini_get("auto_detect_line_endings")) {
      ini_set("auto_detect_line_endings", '1');
    }


    $helper = $this->getHelper('question');
    $defaultEngineer = 0;
    foreach($this->config->engineer_types as $issueindex => $issue) {
      $engineers[$issueindex]=$issue['type'];
      if($issue['default']) {
        $defaultEngineer = $issueindex;
      }
    }
    $question = new ChoiceQuestion(
        "Choose engineer type (defaults to {$this->config->engineer_types[$defaultEngineer]['type']})",
        $engineers,
        $defaultEngineer
    );
    $question->setErrorMessage('Engineer type "%s" is invalid.');
    $engineerIndex = array_search($helper->ask($input, $output, $question),$engineers);

    $login = $input->getOption('login');
    $rclient = new Client($this->config->redmine_url, $this->config->redmine_api);
    $login_user_id = $rclient->user->getIdByUsername($login,['limit' => $this->limit]);
    $redmine_user_array = $rclient->user->show($login_user_id);

    if(!$redmine_user_array['user']['id']){
      $output->writeln("<error>Given username is not available.</error>");
    }else {

      $output->writeln("<comment>Importing induction stories....</comment>");

      $project_id = $rclient->project->getIdByName($this->config->redmine_project_name);
      $tracker_id = $rclient->tracker->getIdByName($this->config->redmine_tracker);
      $status_id = $rclient->issue_status->getIdByName($this->config->redmine_status);

      $fname = $redmine_user_array['user']['firstname'];
      $lname = $redmine_user_array['user']['lastname'];

      // user specific stories import through looping around all possible places.
      $ereader = Reader::createFromPath($this->config->app_root."assets/{$this->config->engineer_types[$engineerIndex]['file']}");
      foreach ($ereader as $index => $erow) {
        $assigned_to_username = (!empty($erow[1]))?$erow[1]:$login;
        $issue = [
            'project_id'  => $project_id,
            'subject'     => sprintf($erow[0],"{$fname} {$lname}"),
            'assigned_to_id' => $rclient->user->getIdByUsername($assigned_to_username,['limit'=>$this->limit]),
            'tracker_id' => $tracker_id,
            'description' => $erow[3],
            'status' => $status_id,
            'is_private' => ($erow[2]=='yes'?true:false)
        ];
        $role = isset($this->config->engineer_role[$assigned_to_username])?
            $this->config->engineer_role[$assigned_to_username]:
            "Developer";


          $rclient->membership->create($project_id, array(
              'user_id' => $issue['assigned_to_id'],
              'role_ids' => [$rclient->role->listing()[$role]]
          ));
          $eissue = $rclient->issue->create($issue);

        if($eissue->id) {
          $output->writeln("<info>Success: {$issue['subject']}</info>");
        }else {
          $output->writeln("<error>Failed: {$issue['subject']}</error>");
        }
      }


      //Common stories import
      $creader= Reader::createFromPath($this->config->app_root."assets/common.csv");
      foreach ($creader as $cindex => $crow) {
        $assigned_to_username = (!empty($crow[1]))?$crow[1]:$login;
        $issueArr=[
            'project_id'  => $project_id,
            'subject'     => sprintf($crow[0],"{$fname} {$lname}"),
            'description' => $crow[3],
            'assigned_to_id' => $rclient->user->getIdByUsername($assigned_to_username,['limit'=>$this->limit]),
            'tracker_id' => $tracker_id,
            'status' => $status_id,
            'is_private' => ($crow[2]=='yes'?true:false)
        ];
        $role = isset($this->config->engineer_role[$assigned_to_username])?
            $this->config->engineer_role[$assigned_to_username]:
            "Developer";


        $rclient->membership->create($project_id,array(
            'user_id' => $issueArr['assigned_to_id'],
            'role_ids' => [$rclient->role->listing()[$role]]
        ));
        $cissue = $rclient->issue->create($issueArr);


        if($cissue->id) {
          $output->writeln("<info>Success: {$issueArr['subject']}</info>");
        }else {
          $output->writeln("<error>Failed: {$issueArr['subject']}</error>");
        }
      }

    }

  }
}
