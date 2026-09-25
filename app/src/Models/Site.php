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

    private array $cms_list = [
        'WordPress' => 'wordpress',
        'SilverStripe CMS' => 'silverstripe-cms',
        'Drupal' => 'drupal',
        'Joomla' => 'joomla',
        'Magento' => 'magento',
        'Sitecore' => 'sitecore',
        'BigCommerce' => 'bigcommerce',
        'Webflow' => 'webflow',
        'Shopify' => 'shopify',
        'Wix' => 'wix',
        'Squarespace' => 'squarespace',
        'Contentful' => 'contentful',
        'Sanity' => 'sanity',
        'Strapi' => 'strapi',
        'Payload CMS' => 'payload',
        'Directus' => 'directus',
        'Storyblok' => 'storyblok',
        'Ghost' =>  'ghost',
        'Prismic' => 'prismic',
        'Hygraph' => 'hygraph',
        'DatoCMS' => 'datocms',
        'Builder.io' => 'builderio',
        'TinaCMS' => 'tinacms',
        'Decap CMS' => 'decap-cms',
        'Craft CMS' => 'craft-cms',
        'Statamic' => 'statamic',
        'Umbraco' => 'umbraco',
        'TYPO3' => 'typo3',
        'PrestaShop' => 'prestashop',
    ];

    private array $framework_list = [
        'SilverStripe' => 'silverstripe',
        'Next.js' => 'nextjs',
        'React' => 'react',
        'Vue.js' => 'vue',
        'Nuxt' => 'nuxt',
        'Angular' => 'angular',
        'Svelte' => 'svelte',
        'SvelteKit' => 'sveltekit',
        'Astro' => 'astro',
        'React Router' => 'react-router',
        'Remix' => 'remix',
        'SolidJS' => 'solidjs',
        'SolidStart' => 'solidstart',
        'Qwik' => 'qwik',
        'Gatsby' => 'gatsby',
        'Eleventy' => 'eleventy',
        'Hugo' => 'hugo',
        'Jekyll' => 'jekyll',
        'TanStack Start' => 'tanstack-start',
        'Laravel' => 'laravel',
        'Ruby on Rails' => 'rails',
        'Django' => 'django',
        'Flask' => 'flask',
        'ASP.NET Core' => 'aspnet-core',
        'Spring Boot' => 'spring-boot',
        'Express' => 'express',
        'Fastify' => 'fastify',
        'NestJS' => 'nestjs',
        'Hono' => 'hono',
    ];

    private static array $db = [
        'Domain'            => 'Varchar(255)',
        'Type'              => 'Varchar(255)',
        'SoftwareVersion'   => 'Varchar(255)',
        'CMS'               => 'Varchar(255)',
        'Framework'         => 'Varchar(255)',
        'AccountCode'       => 'Varchar(255)',
        'RepoUri'           => 'Varchar(500)',
        'ServerRepoPath'    => 'Varchar(500)',
        'AvgResponseTime'   => 'Int',
        'Https'             => 'Boolean',
    ];

    private static array $has_one = [
        'Bucket'            => QueueBucket::class,
        'Parent'            => Server::class,
        'AccountOwner'      => AccountExecutive::class,
    ];

    private static array $has_many = [
        'Responses'         => Response::class,
        'Notes'             => Note::class,
    ];

    public function canCreate($member = null, $context = []): bool
    {
        return (bool)Server::get()->first();
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

        $buckets =  $this->Parent()->Buckets()->map() ?? QueueBucket::get()->map();

        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Domain')->setAttribute('placeholder', 'www.yoursite.com'),
            TextField::create('Type', 'Software')->setAttribute('placeholder', 'PHP'),
            TextField::create('SoftwareVersion', 'Version')->setAttribute('placeholder', '8.4'),
            DropDownField::create('CMS', 'CMS', $this->cms_list)->setEmptyString('Select CMS'),
            DropDownField::create('Framework', 'Framework', $this->framework_list)->setEmptyString('Select Framework'),
            TextField::create('AccountCode', 'Account Code')->setAttribute('placeholder', 'KOKI-KOKI'),
            UrlField::create('RepoUri', 'Repo URI')->setAttribute('placeholder', 'https://bitbucket.com/your/repository'),
            TextField::create('ServerRepoPath', 'Server Repo Path')->setAttribute('placeholder', '~/repos/your-repository'),
            CheckboxField_Readonly::create('Https', 'HTTPS Enabled'),
            ReadonlyField::create('AvgResponseTime', 'Avg. Response Time')->setDescription('In seconds'),
            DropdownField::create('ParentID', 'Parent Server', Server::get()->map())->setEmptyString('Pick a server'),
            DropdownField::create('AccountOwnerID', 'Account Owner', AccountExecutive::get()->map())->setEmptyString('Pick an account owner'),
        ]);

        if ($this->ParentID) {
            $fields->addFieldsToTab('Root.Main', [
                DropdownField::create('BucketID', 'Bucket', $buckets)->setEmptyString('Pick a bucket'),
            ], 'ParentID');
        }

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
            $result->addFieldError('Domain', 'Domain is required');

        if (!$this->ParentID)
            $result->addFieldError('ParentID', 'Parent Server is required');

        if (preg_match('/^(?!:\/\/)([a-zA-Z0-9-_]+\.)+[a-zA-Z]{2,}$/', $this->Domain))
            $result->addFieldError('Domain', 'Please exclude https://, http://, paths, or trailing slashes.');

        return $result;
    }
}
