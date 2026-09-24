<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models;

use SilverStripe\ORM\DataObject;

class Server extends DataObject
{
    private static string $table_name = 'SiteStatDash_Server';
    private static string $singular_name = 'Server';
    private static string $plural_name = 'Servers';
    private static string $description = '';

    private static array $db = [
        'Title'         => 'Varchar(255)',
        'Category'      => 'Varchar(255)',
        'Provider'      => 'Varchar(255)',
    ];

    private static array $has_many = [
        'Addresses'     => Address::class,
        'Sites'         => Site::class,
        'Buckets'       => QueueBucket::class,
    ];
}
