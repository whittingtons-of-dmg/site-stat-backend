<?php

namespace WhittingtonsOfDmg\SiteStatDash\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SilverStripe\Core\Validation\ValidationException;
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

    public function monitorByPriority(): void
    {
        $pool = $this->getPool();

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();
    }

    private function getPool(): Pool
    {
        $concurrency = SiteConfig::current_site_config()->Concurrency ?? 5;

        $client = new Client(['allow_redirects' => false]);
        $idMap = [];
        $requests = function () use (&$idMap) {
            foreach ($this->queue as $site) {
                $idMap[] = $site->ID;
                yield new Request('HEAD', $site->getUrl());
            }
        };

        return new Pool($client, $requests(), [
            'concurrency' => $concurrency,
            'fulfilled' => function (Response $response, $index) use (&$idMap) {
                $this->handleGuzzleResponse($response, $index, $idMap);
            },
            'rejected' => function (RequestException|ConnectException $reason, $index) use (&$idMap)  {
                $this->handleGuzzleResponse($reason, $index, $idMap);
            },
        ]);
    }

    /**
     * @throws \DateMalformedStringException
     * @throws ValidationException
     */
    private function handleGuzzleResponse(Response|RequestException|ConnectException $response, int $index, array $idMap): void
    {
        $site = Site::get()->byID($idMap[$index]);
        $newResponse = \WhittingtonsOfDmg\SiteStatDash\Models\Response::fromGuzzleResponse($response, $site);
        $site->Responses()->Add($newResponse);
    }

    public static function getLocationStatus(string $location): false|ResponseInterface
    {
        try {
            return new Client(['allow_redirects' => false])->request('HEAD', $location);
        } catch (RequestException|GuzzleException $e) {

        }

        return false;
    }
}
