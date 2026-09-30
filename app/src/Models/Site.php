<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models;

use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Forms\CheckboxField;
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
use SilverStripe\Security\Member;

class Site extends DataObject
{
    private static string $table_name = 'SiteStatDash_Site';
    private static string $singular_name = 'Website';
    private static string $plural_name = 'Websites';
    private static string $description = '';
    private static string $default_sort = 'SortOrder';
    private static string $secure_protocol = 'https://';
    private static string $default_protocol = 'http://';

    private static array $db = [
        'SortOrder'         => 'Int',
        'Priority'          => 'Enum(["high", "medium", "low"], "medium")',
        'Domain'            => 'Varchar(255)',
        'AccountCode'       => 'Varchar(255)',
        'RepoUri'           => 'Varchar(500)',
        'ServerRepoPath'    => 'Varchar(500)',
        'ForceSecure'       => 'Boolean',
        // DNS fields
        'Registrar'         => 'Varchar(255)',
        'DnsARecords'       => 'Varchar(255)',
        'DnsFailed'         => 'Boolean',
    ];

    private static array $has_one = [
        'Parent'            => Server::class,
        'AccountOwner'      => Member::class,
        'ServerVersion'     => VersionNumber::class,
        'CMSVersion'        => VersionNumber::class,
        'FrameworkVersion'  => VersionNumber::class,
    ];

    private static array $has_many = [
        'Responses'         => Response::class,
        'Notes'             => Note::class,
    ];

    private static array $indexes = [
        'Domain'            => true,
    ];

    private static array $summary_fields = [
        'getTitle'          => 'Title',
    ];

    private static array $many_many = [
        'Packages'          => SiteSoftware::class,
    ];

    public function canCreate($member = null, $context = []): bool
    {
        return (bool)Server::get()->first();
    }

    public function getTitle(): string
    {
        return $this->Domain ?? "Empty";
    }

    public function getUrl(): string
    {
        return $this->ForceSecure ? $this->getSecureUrl() : $this->getDefaultUrl();
    }

    private function getDefaultUrl(): string
    {
        return self::$default_protocol . $this->Domain . "/";
    }

    private function getSecureUrl(): string
    {
        return self::$secure_protocol . $this->Domain . "/";
    }

    public function getLastResponse()
    {

    }

    public function getCMSFields()
    {
        $fields = FieldList::create(TabSet::create('Root'));

        $versionsList = VersionNumber::get()->Map('ID', 'getTitle') ?? [];

        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Domain')->setAttribute('placeholder', 'johndoe.com'),
            CheckboxField::create('ForceSecure', 'Force Secure')
                ->setDescription('Check to make sure the site check service uses the https:// protocol for this site instead of the default http://'),
            TextField::create('Type', 'Software')->setAttribute('placeholder', 'PHP'),
            DropDownField::create('Priority', 'Priority', $this->dbObject('Priority')->enumValues())->setEmptyString('Select Priority'),
            DropDownField::create('ServerVersionID', 'Server Version', $versionsList)->setEmptyString('Select Server Ver.'),
            DropDownField::create('CMSVersionID', 'CMS Version', $versionsList)->setEmptyString('Select CMS'),
            DropDownField::create('FrameworkVersionID', 'Framework Version', $versionsList)->setEmptyString('Select Framework'),
            TextField::create('AccountCode', 'Account Code')->setAttribute('placeholder', 'KOKI-KOKI'),
            UrlField::create('RepoUri', 'Repo URI')->setAttribute('placeholder', 'https://bitbucket.com/your/repository'),
            TextField::create('ServerRepoPath', 'Server Repo Path')->setAttribute('placeholder', '~/repos/your-repository'),
            DropdownField::create('ParentID', 'Parent Server', Server::get()->map())->setEmptyString('Pick a server'),
            DropdownField::create('AccountOwnerID', 'Account Owner', Member::get()->map())->setEmptyString('Pick an account owner'),
        ]);
        $fields->addFieldsToTab('Root.DNS', [
            ReadonlyField::create('Registrar'),
            ReadonlyField::create('DnsARecords', 'A Record IP List'),
            CheckboxField_Readonly::create('DnsFailed', 'DNS Failed'),
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

        if (!$this->Domain)
            $result->addFieldError('Domain', 'Domain name with tld is required');

        if (!$this->Priority)
            $result->addFieldError('PriorityID', 'Priority is required');

        if (!preg_match('/^(?!:\/\/)([a-zA-Z0-9-_]+\.)+[a-zA-Z]{2,}$/', $this->Domain))
            $result->addFieldError('Domain', 'Please omit all protocols and paths');

        if (preg_match('/\..*\./s', $this->Domain))
            $result->addFieldError('Domain', 'No subdomains, only domain names with a tld');

        if (!$this->isInDB() && Site::get()->filter('Domain', $this->Domain)->first())
            $result->addFieldError('Domain', 'Site already exists');

        if (!$this->ParentID)
            $result->addFieldError('ParentID', 'Parent Server is required');

        return $result;
    }

    public function setDNSFields(string|false $answer, string|false $belongsTo): void
    {
        $this->DnsFailed = !$answer;

        if (!$this->DnsFailed)
            $this->DnsARecords = $answer;

        $this->Registrar = $belongsTo ?: "Lookup Failed";

        $this->write();
    }
}
