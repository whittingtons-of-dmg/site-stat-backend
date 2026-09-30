<?php

namespace WhittingtonsOfDmg\SiteStatDash\Tasks;

use Exception;
use SilverStripe\Control\Email\Email;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use WhittingtonsOfDmg\SiteStatDash\Service\DomainAuthorityService;

class DomainInquiryTask extends BuildTask
{
    protected string $title = 'Domain Inquiry Task';
    protected static string $description = 'Retireve simple DNS information for all sites';

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $email = Email::create('noreply@innismagiore.com', 'web-dev@innismaggiore.com', 'Domain Inquiry Task: Error');

        try {
            new DomainAuthorityService()->inquire();
        } catch (Exception $e) {
            $email->setBody('Error checking DNS and registrar information: ' . $e->getMessage())->sendPlain();
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
