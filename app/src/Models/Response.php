<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models;

use SilverStripe\ORM\DataObject;

class Response extends DataObject
{
    private static string $table_name = 'SiteStatDash_Response';
    private static string $singular_name = 'Response';
    private static string $plural_name = 'Responses';
    private static string $description = '';

    private static array $db = [
        'StatCode'          => 'Int',
        'Timestamp'         => 'Datetime',
        'Message'           => 'Varchar(255)',
        'LocationHeader'    => 'Varchar(255)',
        'ServerHeader'      => 'Varchar(255)',
        'XHeaders'          => 'Varchar(500)',
        'SecondsTillReply'  => 'Int',
        'HTTPS'             => 'Boolean',
        'WWW'               => 'Boolean',
        'TimedOut'          => 'Boolean',
        'PingFailed'        => 'Boolean',
        'Redirected'        => 'Boolean',

        // DNS fields
        'Registrar'         => 'Varchar(255)',
        'DnsTypeA'          => 'Varchar(255)',
        'DnsFailed'         => 'Boolean',
    ];

    private static array $summary_fields = [
        'StatCode' => 'Status Code',
        'TimedOut.Nice' => 'Timed Out',
        'Failed.Nice' => 'Failed',
    ];

    private static array $has_one = [
        'Site' => Site::class,
    ];

    public static function fromGuzzle(array $responseObj): self
    {
        $response = new self();

        return $response;
    }
}
