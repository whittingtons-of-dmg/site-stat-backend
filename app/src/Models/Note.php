<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

class Note extends DataObject
{
    private static string $table_name = 'SiteStatDash_Note';
    private static string $singular_name = 'Note';
    private static string $plural_name = 'Notes';
    private static string $description = '';

    private static array $db = [
        'Title' => 'Varchar(255)',
        'Content' => 'Varchar(500)',
    ];

    private static array $has_one = [
        'Owner' => Site::class,
    ];

    private static array $summary_fields = [
        'Title',
        'Content',
        'Created',
    ];

    public function getCMSFields(): ?FieldList
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'Content',
            'OwnerID',
        ]);

        $fields->addFieldToTab('Root.Main', new TextField('Content', 'Content')->setDescription('1,000 characters max'));

        return $fields;
    }
}
