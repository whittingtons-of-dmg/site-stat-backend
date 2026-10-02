<?php

namespace WhittingtonsOfDmg\SiteStatDash\Tasks;

use Exception;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use WhittingtonsOfDmg\SiteStatDash\Service\HeartBeatService;

class SiteSurveillanceTask extends BuildTask
{
    protected string $title = 'Site Surveillance Task';
    protected static string $description = 'Ping all websites to determine their availability and uptime status';
    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $email = Email::create('noreply@innismagiore.com', 'web-dev@innismaggiore.com', 'Site Health Check Task: Error');
        $priorityVar = Controller::curr()->getRequest()->getVar('priority');

        if (!empty($priorityVar)) {
            try {
                new HeartBeatService($priorityVar)->monitorByPriority();
            } catch (Exception $e) {
                Injector::inst()->get(LoggerInterface::class)->error($e->getMessage());
                $email->setBody('Error retriving HEAD requests for sites: ' . $e->getMessage())->sendPlain();
                return Command::FAILURE;
            }
        } else {
            $msg = 'Required url query parameter "priority" either missing or empty';
            Injector::inst()->get(LoggerInterface::class)->error($msg);
            $email->setBody($msg);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
