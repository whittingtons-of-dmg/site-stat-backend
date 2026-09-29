<?php

namespace WhittingtonsOfDmg\SiteStatDash\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use SilverStripe\ORM\DataList;
use SilverStripe\SiteConfig\SiteConfig;
use WhittingtonsOfDmg\SiteStatDash\Models\Site;

class HeartBeatService
{
    private ?DataList $queue;

    /**
     * @throws Exception
     */
    public function __construct($filterVar)
    {
        $sitesMap = Site::get()->filter('Priority', $filterVar);

        if ($sitesMap->count() <= 0)
            throw new Exception("Queue is empty. The current priority filter '" . $filterVar . "' has returned an empty list. Skipping execution.");

        $this->queue = $sitesMap;
    }

    public function monitor(): void
    {
        $pool = $this->getPool();

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();

        $end = true;
    }

    private function getPool(): Pool
    {
        $concurrency = SiteConfig::current_site_config()->Concurrency ?? 5;
        $client = new Client();

        $requests = function () {
            foreach ($this->queue as $site) {
                yield new Request('HEAD', $site->getDefaultUrl());
            }
        };

        return new Pool($client, $requests(), [
            'concurrency' => $concurrency,
            'fulfilled' => function (Response $response, $index) {
                $site = Site::get()->byID($this->queue[$index]->ID);
                $newResponse = \WhittingtonsOfDmg\SiteStatDash\Models\Response::fromGuzzleResponse($response);
                $site->Responses()->Add($newResponse);
            },
            'rejected' => function (RequestException $reason, $index) {
                $site = Site::get()->byID($this->queue[$index]->ID);
                $newResponse = \WhittingtonsOfDmg\SiteStatDash\Models\Response::fromGuzzleReason($reason);
                $site->Responses()->Add($newResponse);
            },
        ]);
    }
}
