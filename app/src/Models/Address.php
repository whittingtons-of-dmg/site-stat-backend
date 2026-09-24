<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models;

use SilverStripe\ORM\DataObject;

class Address extends DataObject
{
    private static string $table_name = 'SiteStatDash_Address';
    private static string $singular_name = 'IPv4 Address';
    private static string $plural_name = 'IPv4 Addresses';
    private static string $description = '';

    private static array $db = [
        'IPv4Address' => 'Varchar(255)',
    ];

    private static array $has_one = [
        'Server' => Server::class,
    ];

    public function getTitle(): string
    {
        return $this->Title;
    }
}
