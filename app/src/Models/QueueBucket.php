<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models;

use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;

class QueueBucket extends DataObject
{
    private static string $table_name = 'SiteStatDash_QueueBucket';
    private static string $singular_name = 'Bucket';
    private static string $plural_name = 'Buckets';
    private static string $description = '';

    private array $priority_list = [
        300 => 'High',
        600 => 'Medium',
        900 => 'Low',
    ];

    private static array $db = [
        'Title'     => 'Varchar(255)',
        'Priority'  => 'Int',
    ];

    private static array $has_one = [
        'Owner'     => Server::class,
    ];

    public function getIntervalSeconds(): int
    {
        return $this->Priority ?? 600;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'Priority',
            'OwnerID',
        ]);

        $fields->addFieldsToTab('Root.Main', [
            DropdownField::create('Priority', 'Priority', $this->priority_list),
        ]);

        return $fields;
    }
}
