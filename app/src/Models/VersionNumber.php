<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models;

use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;

class VersionNumber extends DataObject
{
    private static string $table_name = 'SiteStatDash_VersionNumber';
    private static string $singular_name = 'Version';
    private static string $plural_name = 'Versions';
    private static string $description = '';

    private static array $db = [
        'SortOrder'             => 'Int',
        'VersionNumber'         => 'Varchar(50)',
        'EOL'                   => 'Boolean',
        'EOLDate'               => 'Date',
    ];

    private static array $has_one = [
        'ServerSoftware'        => ServerSoftware::class,
        'SiteSoftware'          => SiteSoftware::class,
    ];

    private static array $summary_fields = [
        'getSoftwareTitle'      => 'Software',
        'VersionNumber'         => 'Version Number',
        'EOLDate.Nice'          => 'EOL Date',
    ];

    public function getSoftwareTitle(): string
    {
        return $this->ServerSoftwareID ? $this->ServerSoftware->getTitle() : $this->SiteSoftware->getTitle();
    }

    public function getTitle(): string
    {
        return $this->ServerSoftwareID ? $this->ServerSoftware->getTitle() . " " . $this->VersionNumber : $this->SiteSoftware->getTitle() . " " . $this->VersionNumber;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
             'SortOrder',
             'ServerSoftwareID',
             'SiteSoftwareID',
        ]);

        if (!$this->ServerSoftwareID && !$this->SiteSoftwareID) {
            $fields->addFieldsToTab('Root.Main', [
                DropDownField::create('ServerSoftwareID', 'Server Software', ServerSoftware::get()->Map('ID', 'getTitle')),
                DropDownField::create('SiteSoftwareID', 'Site Software', SiteSoftware::get()->Map('ID', 'getTitle')),
            ]);
        }


        return $fields;
    }

    public function validate(): ValidationResult
    {
        $result = parent::validate();

        if ($this->isInDB() && !$this->ServerSoftwareID && !$this->SiteSoftwareID)
            $result->addError('No software to apply this version to.');

        if ($this->ServerSoftwareID && $this->SiteSoftwareID)
            $result->addError('Can\'t have both Server and Site software selected.');

        return $result;
    }
}
