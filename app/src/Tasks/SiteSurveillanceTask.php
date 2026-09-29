<?php

namespace WhittingtonsOfDmg\SiteStatDash\Tasks;

use Exception;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use WhittingtonsOfDmg\SiteStatDash\Service\HeartBeatService;

class SiteSurveillanceTask extends BuildTask
{
    protected string $title = 'Site Surveillance';
    protected static string $description = 'Ping all websites to determine their availability and uptime status';
    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $email = Email::create('noreply@innismagiore.com', 'web-dev@innismaggiore.com', 'Site Health Check Task: Error');
        $filterVar = Controller::curr()->getRequest()->getVar('priority');

        if (!empty($filterVar)) {
            try {
                new HeartBeatService($filterVar)->monitor();
            } catch (Exception $e) {
                $email->setBody('Error checking refresh for Instagram access token: ' . $e->getMessage())->sendPlain();
                return Command::FAILURE;
            }
        } else {
            $email->setBody('Required url query parameter "priority" either missing or empty');
            return Command::FAILURE;
        }


        return Command::SUCCESS;
    }
}
