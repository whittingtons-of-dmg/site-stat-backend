<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models;

use SilverStripe\ORM\DataObject;

class MigratedSite extends DataObject
{
    private static string $table_name = 'SiteStatDash_MigratedSite';
    private static string $singular_name = 'Migrated Site';
    private static string $plural_name = 'Migrated Sites';
    private static string $description = '';

    public static function fromSite(Site $site): self
    {
        $migratedSite = new self();

        return $migratedSite;
    }
}
