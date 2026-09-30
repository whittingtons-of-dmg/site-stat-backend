<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models;

use DateTime;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\Forms\CheckboxField_Readonly;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextareaField;
use SilverStripe\ORM\DataObject;
use WhittingtonsOfDmg\SiteStatDash\Service\HeartBeatService;

class Response extends DataObject
{
    private static string $table_name = 'SiteStatDash_Response';
    private static string $singular_name = 'Response';
    private static string $plural_name = 'Responses';
    private static string $description = '';

    private static array $db = [
        'StatusCode'        => 'Int',
        'Timestamp'         => 'Datetime',
        'Message'           => 'Varchar(255)',
        'ReasonPhrase'      => 'Varchar(20)',
        'Protocol'          => 'Varchar(10)',
        'ProtocolVer'       => 'Varchar(10)',
        'RedirectLocation'  => 'Varchar(100)',
        'HTTPS'             => 'Boolean',
        'Redirected'        => 'Boolean',
        'ConnectionError'   => 'Boolean',
        'Failed'            => 'Boolean',
    ];

    private static array $summary_fields = [
        'StatusCode' => 'Status Code',
        'Timestamp.Nice' => 'Timestamp',
        'Failed.Nice' => 'Failed',
    ];

    private static array $has_one = [
        'Site' => Site::class,
    ];

    public function getCMSFields()
    {
        $fields = FieldList::create(TabSet::create('Root'));

        if ($this->StatusCode) {
            $fields->addFieldsToTab('Root.Main', [
                ReadonlyField::create('StatusCode', 'Status Code')
                    ->setDescription('Reflects the final destination\'s response status code'),
                DatetimeField::create('Timestamp')->setReadonly(true),
                TextareaField::create('Message', 'Message')->setReadonly(true),
                ReadonlyField::create('ReasonPhrase', 'Reason Phrase'),
                ReadonlyField::create('Protocol', 'Protocol')
                    ->setDescription('References the initial guzzle request\'s protocol'),
                ReadonlyField::create('RedirectLocation', 'Redirect Location'),
                CheckboxField_Readonly::create('HTTPS','HTTPS Working'),
                ReadonlyField::create('ProtocolVer', 'Protocol Version')
                    ->setDescription('Uses the final response object as reference'),
                CheckboxField_Readonly::create('Redirected', 'Initial request was redirected'),
                CheckboxField_Readonly::create('ConnectionError', 'No Internet Connection?'),
                CheckboxField_Readonly::create('Failed', 'Failed'),
            ]);
        } else {
            $fields->addFieldsToTab('Root.Main', [
                ReadonlyField::create('StatusCode', 'Status Code')
                    ->setDescription('Reflects the final destination\'s response status code'),
                DatetimeField::create('Timestamp')->setReadonly(true),
                TextareaField::create('Message', 'Message')->setReadonly(true),
                CheckboxField_Readonly::create('ConnectionError', 'No Internet Connection?'),
                CheckboxField_Readonly::create('Failed', 'Failed'),
            ]);
        }


        return $fields;
    }

    /**
     * @throws \DateMalformedStringException
     * @throws ValidationException
     */
    public static function fromGuzzleResponse(ResponseInterface|RequestException|ConnectException $response, Site $site): self
    {
        $newResponse = new self();
        $message = "";
        $failed = false;
        $dateString = property_exists($response, 'getHeader') ? $response->getHeader('Date')[0] : null;
        $timestamp = !is_null($dateString) ? new DateTime($dateString)->format(DATE_ATOM) : new DateTime()->format(DATE_ATOM);

        if ($response::class === RequestException::class) {
            $reason = $response;
            $newResponse->Message = $reason->getMessage();
            $newResponse->Failed = true;
        } else if ($response::class === ConnectException::class) {
            $newResponse->Message = $response->getMessage();
            $newResponse->ConnectionError = true;
            $newResponse->Failed = true;
        } else {
            $statusCode = $response->getStatusCode();
            $reasonPhrase = $response->getReasonPhrase();
            $redirected = $response->getStatusCode() === 301 || $response->getStatusCode() === 302;
            $protocolVersion = $response->getProtocolVersion();
            $https = $site->ForceSecure && ($response->getStatusCode() === 200);
            $location = $response->getHeader('Location')[0];

            if ($location) {
                $locationHttps = str_starts_with($location, 'https://');
                $redirectResponse = HeartBeatService::getLocationStatus($location);
                $https = $locationHttps && ($redirectResponse->getStatusCode() === 200);
                $failed = $redirectResponse->getStatusCode() !== 200;
                $statusCode = $redirectResponse->getStatusCode();
                $protocolVersion = $redirectResponse->getProtocolVersion();
                $reasonPhrase = $redirectResponse->getReasonPhrase();
            }

            $newResponse->StatusCode = $statusCode;
            $newResponse->ReasonPhrase = $reasonPhrase;
            $newResponse->Protocol = $site->ForceSecure ? 'https://' : 'http://';
            $newResponse->ProtocolVer = $protocolVersion;
            $newResponse->Message = $message;
            $newResponse->RedirectLocation = $location;
            $newResponse->HTTPS = $https;
            $newResponse->Redirected = $redirected;
            $newResponse->Failed = $failed;
        }

        $newResponse->Timestamp = $timestamp;
        $newResponse->write();

        return $newResponse;
    }
}
