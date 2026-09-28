<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models;

use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Forms\CheckboxField_Readonly;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\UrlField;
use SilverStripe\ORM\DataObject;

class Site extends DataObject
{
    private static string $table_name = 'SiteStatDash_Site';
    private static string $singular_name = 'Website';
    private static string $plural_name = 'Websites';
    private static string $description = '';
    private static string $default_sort = 'Priority';
    private static string $primary_protocol = 'https://';
    private static string $fallback_protocol = 'http://';

    private static array $db = [
        'Priority'          => 'Int',
        'HomePageUrl'       => 'Varchar(255)',
        'AccountCode'       => 'Varchar(255)',
        'RepoUri'           => 'Varchar(500)',
        'ServerRepoPath'    => 'Varchar(500)',
        'AvgResponseTime'   => 'Int',
        'Https'             => 'Boolean',
    ];

    private static array $has_one = [
        'Parent'            => Server::class,
        'AccountOwner'      => AccountExecutive::class,
        'ServerVersion'     => VersionNumber::class,
        'CMSVersion'        => VersionNumber::class,
        'FrameworkVersion'  => VersionNumber::class,
    ];

    private static array $has_many = [
        'Responses'         => Response::class,
        'Notes'             => Note::class,
    ];

    private static array $indexes = [
        'HomePageUrl' => true,
    ];

    private static array $summary_fields = [
        'getTitle' => 'Title',
    ];

    private static array $many_many = [
        'Packages'      => SiteSoftware::class,
    ];

    public function canCreate($member = null, $context = []): bool
    {
        return (bool)Server::get()->first();
    }

    public function getTitle(): string
    {
        return $this->HomePageUrl;
    }

    public function getAvgResponseTime()
    {

    }

    public function getLastResponse()
    {

    }

    public function getCMSFields()
    {
        $fields = FieldList::create(TabSet::create('Root'));

        $versionsList = VersionNumber::get()->Map('ID', 'getTitle') ?? [];

        $fields->addFieldsToTab('Root.Main', [
            TextField::create('HomePageUrl', 'Home Page URL')->setAttribute('placeholder', 'www.yoursite.com'),
            TextField::create('Type', 'Software')->setAttribute('placeholder', 'PHP'),
            DropDownField::create('ServerVersionID', 'Server Version', $versionsList)->setEmptyString('Select Server Ver.'),
            DropDownField::create('CMSVersionID', 'CMS Version', $versionsList)->setEmptyString('Select CMS'),
            DropDownField::create('FrameworkVersionID', 'Framework Version',$versionsList)->setEmptyString('Select Framework'),
            TextField::create('AccountCode', 'Account Code')->setAttribute('placeholder', 'KOKI-KOKI'),
            UrlField::create('RepoUri', 'Repo URI')->setAttribute('placeholder', 'https://bitbucket.com/your/repository'),
            TextField::create('ServerRepoPath', 'Server Repo Path')->setAttribute('placeholder', '~/repos/your-repository'),
            CheckboxField_Readonly::create('Https', 'HTTPS Enabled'),
            ReadonlyField::create('AvgResponseTime', 'Avg. Response Time')->setDescription('In seconds'),
            DropdownField::create('ParentID', 'Parent Server', Server::get()->map())->setEmptyString('Pick a server'),
            DropdownField::create('AccountOwnerID', 'Account Owner', AccountExecutive::get()->map())->setEmptyString('Pick an account owner'),
        ]);

        if ($this->isInDB()) {
            $fields->addFieldsToTab('Root.Notes', [
                GridField::create('Notes', 'Notes', Note::get(), GridFieldConfig_RecordEditor::create()),
            ]);

            $fields->addFieldsToTab('Root.Responses', [
                GridField::create('Responses', 'Responses', Response::get(), GridFieldConfig_RecordEditor::create()),
            ]);
        }

        return $fields;
    }

    public function validate(): ValidationResult
    {
        $result = parent::validate();

        if (!$this->HomePageUrl)
            $result->addFieldError('HomePageUrl', 'Home Page Url is required');

        if (!$this->isInDB() && Site::get()->filter('HomePageUrl', $this->HomePageUrl)->first())
            $result->addFieldError('HomePageUrl', 'Homepage URL already exists');

        if (!$this->ParentID)
            $result->addFieldError('ParentID', 'Parent Server is required');

        return $result;
    }

    public function getPrimaryProtocol(): string
    {
        return self::$primary_protocol;
    }

    public function getFallbackProtocol(): string
    {
        return self::$fallback_protocol;
    }
}
