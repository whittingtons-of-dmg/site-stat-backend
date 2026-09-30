<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class Server extends DataObject
{
    private static string $table_name = 'SiteStatDash_Server';
    private static string $singular_name = 'Server';
    private static string $plural_name = 'Servers';
    private static string $description = '';

    private static array $db = [
        'Title'             => 'Varchar(255)',
        'Category'          => 'Varchar(255)',
        'Provider'          => 'Varchar(255)',
    ];

    private static array $has_one = [
        'ServerSoftware'    => ServerSoftware::class,
        'ServerOS'          => ServerSoftware::class,
    ];

    private static array $has_many = [
        'Addresses'         => Address::class,
        'Sites'             => Site::class,
    ];

    private static array $many_many = [
        'Packages'          => ServerSoftware::class,
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'Sites',
            'Addresses',
        ]);

        $inlineConfig = GridFieldConfig_RecordEditor::create();
        $inlineConfig->removeComponentsByType([GridFieldDataColumns::class, GridFieldAddNewButton::class]);

        // 3. Instantiate and configure editable columns
        $editableColumns = new GridFieldEditableColumns();

        $editableColumns->setDisplayFields([
            'IPv4Address' => [
                'title' => 'IPv4 Address',
                'field' => TextField::class
            ],
            'Primary' => [
                'title' => 'Primary',
                'field' => CheckboxField::class
            ],
        ]);

        $inlineConfig->addComponent($editableColumns);

        // 5. Optional: Add a button to insert rows directly inline
        $inlineConfig->addComponent(new GridFieldAddNewInlineButton());

        $versionsList = VersionNumber::get()->Map('ID', 'getTitle') ?? [];

        $fields->addFieldsToTab('Root.Main', [
            DropdownField::create('ServerSoftwareID', 'Server Software', $versionsList)->setEmptyString('Software Version'),
            DropdownField::create('ServerOSID', 'Server OS', $versionsList)->setEmptyString('OS Version'),
            GridField::create('Sites',
                'Sites',
                $this->Sites(),
                GridFieldConfig_RecordEditor::create()
                    ->addComponent(new GridFieldSortableRows('SortOrder'))
            ),
        ]);
        $fields->addFieldsToTab('Root.Addresses', [
            GridField::create('Addresses',
                'Addresses',
                $this->Addresses(),
                $inlineConfig
            ),
        ]);

        return $fields;
    }

    public function getSitesUrlList(): false|HasManyList
    {
        return $this->Sites() ?? false;
    }
}
