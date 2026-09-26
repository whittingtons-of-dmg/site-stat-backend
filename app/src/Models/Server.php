<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\ORM\DataObject;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

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
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'Sites'
        ]);

        $fields->addFieldsToTab('Root.Main', [
            GridField::create('Sites',
                'Sites',
                $this->Sites(),
                GridFieldConfig_RecordEditor::create()
                    ->addComponent(new GridFieldSortableRows('Priority'))
            ),
        ]);

        return $fields;
    }

    public function getSitesUrlMap(): false|array
    {
        return $this->Sites()?->map('ID', 'HomePageUrl') ?? false;
    }
}
