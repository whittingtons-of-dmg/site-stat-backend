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
use SilverStripe\SiteConfig\SiteConfig;
use WhittingtonsOfDmg\SiteStatDash\Models\Site;

class HeartBeatService
{
    private ?array $queue;

    /**
     * @throws Exception
     */
    public function __construct($filterVar)
    {
        $sitesMap = [];

        Site::get()->filter('Priority', $filterVar)->each(function($site) use (&$sitesMap) {
            $value = ['id' => $site->ID, 'url' => $site->getUrl()];
            if (!in_array($value, $sitesMap)) {
                $sitesMap[] = $value;
            }
        });

        if (count($sitesMap) <= 0)
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
        $requests = function () {
            foreach ($this->queue as $site) {
                yield new Request('HEAD', $site['url']);
            }
        };

        return new Pool($client, $requests(), [
            'concurrency' => $concurrency,
            'fulfilled' => function (Response $response, $index) {
                $this->handleGuzzleResponse($response, $index);
            },
            'rejected' => function (RequestException|ConnectException $reason, $index)  {
                $this->handleGuzzleResponse($reason, $index);
            },
        ]);
    }

    /**
     * @throws \DateMalformedStringException
     * @throws ValidationException
     */
    private function handleGuzzleResponse(Response|RequestException|ConnectException $response, int $index): void
    {
        if ($site = Site::get()->byID($this->queue[$index]['id'])) {
            $newResponse = \WhittingtonsOfDmg\SiteStatDash\Models\Response::fromGuzzleResponse($response, $site);
            $site->Responses()->Add($newResponse);
        }
    }

    public static function headLocationStatus(string $location): RequestException|ConnectException|ResponseInterface
    {
        try {
            return new Client(['allow_redirects' => false])->request('HEAD', $location);
        } catch (RequestException|GuzzleException $e) {
            return $e;
        }
    }
}
