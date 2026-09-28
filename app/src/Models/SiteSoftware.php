<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models;

use SilverStripe\Forms\DateField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class SiteSoftware extends DataObject
{
    private static string $table_name = 'SiteStatDash_SiteSoftware';
    private static string $singular_name = 'Site Package';
    private static string $plural_name = 'Site Software';
    private static string $description = '';

    private static array $db = [
        'Name'              => 'Varchar(255)',
        'Description'       => 'Varchar(500)',
    ];

    private static array $has_many = [
        'Versions'          => VersionNumber::class,
    ];

    private static array $belongs_many_many = [
        'Sites'             => Site::class,
    ];

    public function getTitle(): string
    {
        return $this->Name;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $inlineConfig = GridFieldConfig_RecordEditor::create();
        $inlineConfig->addComponent(new GridFieldSortableRows('SortOrder'));
        $inlineConfig->removeComponentsByType([GridFieldDataColumns::class, GridFieldAddNewButton::class]);

        // 3. Instantiate and configure editable columns
        $editableColumns = new GridFieldEditableColumns();

        $editableColumns->setDisplayFields([
            'VersionNumber' => [
                'title' => 'Version Number',
                'field' => TextField::class
            ],
            'EOLDate' => [
                'title' => 'EOL Date',
                'field' => DateField::class
            ],
        ]);

        $inlineConfig->addComponent($editableColumns);

        // 5. Optional: Add a button to insert rows directly inline
        $inlineConfig->addComponent(new GridFieldAddNewInlineButton());

        $fields->removeByName([
            'Description',
            'Sites',
            'Versions',
        ]);

        $fields->addFieldsToTab('Root.Main', [
            TextareaField::create('Description')->setDescription('Some notes about the software'),
        ]);

        if ($this->isInDB()) {
            $fields->addFieldsToTab('Root.Main', [
                GridField::create(
                    'Versions',
                    'Versions',
                    $this->getOwner()->Versions(),
                    $inlineConfig
                ),
            ]);
        }

        return $fields;
    }
}
