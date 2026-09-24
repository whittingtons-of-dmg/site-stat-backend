<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models;

use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

class AccountExecutive extends DataObject
{
    private static string $table_name = 'SiteStatDash_AccountExecutive';
    private static string $singular_name = 'Account Executive';
    private static string $plural_name = 'Account Executives';
    private static string $description = '';

    private static array $db = [
        'Name' => 'Varchar(255)',
        'Email' => 'Varchar(255)',
    ];

    private static array $has_many = [
        'Sites' => Site::class,
    ];

    public function getTitle(): string
    {
        return $this->Name ?? "John Doe";
    }

    public function getCMSFields()
    {
        $fields = FieldList::create(TabSet::create('Root'));

        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Name', 'Account Owner'),
            EmailField::create('Email', 'Account Owner Email'),
        ]);

        return $fields;
    }
}
