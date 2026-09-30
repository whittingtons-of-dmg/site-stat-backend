<?php

namespace WhittingtonsOfDmg\SiteStatDash\Service;

use Exception;
use MonoVM\WhoisPhp\WhoisHandler;
use Psr\Log\LoggerInterface;
use PurplePixie\PhpDns\DNSQuery;
use PurplePixie\PhpDns\DNSTypes;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataList;
use WhittingtonsOfDmg\SiteStatDash\Models\Site;

class DomainAuthorityService
{
    private ?DataList $queue;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $sitesMap = Site::get();

        if ($sitesMap->count() <= 0)
            throw new Exception("Empty list. Skipping execution.");

        $this->queue = $sitesMap;
    }

    public function inquire(): void
    {
        foreach ($this->queue as $site) {
            $answer = $this->getA($site->Domain);
            $belongsTo = $this->whois($site->Domain);
            $site->setDNSFields($answer, $belongsTo);
        }
    }

    private function getA(string $question)
    {
        $authorities = [
            "8.8.8.8",
            "9.9.9.9",
            "1.1.1.1",
            "208.67.222.222"
        ];

        $answer = false;
        $query = null;
        $type = DNSTypes::NAME_A;

        try
        {
            for ($i = 0; $i < count($authorities); $i++) {
                $query = new DNSQuery($authorities[$i]);

                if ($answer = $query->query($question, $type))
                    break;
            }

            // Check for an error
            if ($answer === false || $query->hasError())
            {
                Injector::inst()->get(LoggerInterface::class)->error("Error: " . $query->getLasterror()."\n");
            }
            else
            {
                $answers = [];
                foreach($answer as $result)
                {
                    if ($result->getTypename() == $type)
                    {
                        $answers[] = $result->getData();
                    }
                }
                $answer = implode(", ", $answers);
            }
        }
        catch(Exception $e)
        {
            Injector::inst()->get(LoggerInterface::class)->error("Error: " . $e->getMessage());
        }

        return $answer;
    }

    private function whois(string $domain): false|string
    {
        $whois = WhoisHandler::whois($domain);

        if ($whois->isValid()) {
            $rawText = strip_tags($whois->getWhoisMessage());

            // Regex to extract the registrar name from the raw text
            if (preg_match('/Registrar:\s*(.*)/i', $rawText, $matches)) {
                return trim($matches[1]);
            } else {
                return $rawText;
            }
        } else {
            return false;
        }
    }
}
