<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models;

use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

class Address extends DataObject
{
    private static string $table_name = 'SiteStatDash_Address';
    private static string $singular_name = 'IPv4 Address';
    private static string $plural_name = 'IPv4 Addresses';
    private static string $description = '';

    private static array $db = [
        'IPv4Address'   => 'Varchar(255)',
        'Primary'       => 'Boolean',
    ];

    private static array $has_one = [
        'Server' => Server::class,
    ];

    private static array $summary_fields = [
        'getTitle' => 'Title',
        'Primary.Nice' => 'Primary',
    ];

    public function getTitle(): string
    {
        return $this->IPv4Address;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'ServerID',
            'IPv4Address',
        ]);

        $fields->addFieldsToTab('Root.Main', [
            TextField::create('IPv4Address', 'IPv4 Address')
        ], 'Primary');

        return $fields;
    }

    public function validate(): ValidationResult
    {
        $result = parent::validate();

        $primary = $this->Server()->Addresses()->filter('Primary', true)->first()->ID;

        if (!$this->IPv4Address)
            $result->addFieldError('IPv4Address', 'IPv4 Address is required.');

        if (!preg_match('/^(?:25[0-5]|2[0-4]\d|1\d{2}|[1-9]\d|\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d{2}|[1-9]\d|\d)){3}$/', $this->IPv4Address))
            $result->addFieldError('IPv4Address', 'Invalid IPv4 Address');

        if ($primary && $primary !== $this->ID)
            $result->addFieldError('Addresses', 'Primary address already set');

        return $result;
    }
}
