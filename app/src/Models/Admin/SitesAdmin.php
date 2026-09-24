<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models\Admin;

use SilverStripe\Admin\ModelAdmin;
use WhittingtonsOfDmg\SiteStatDash\Models\AccountExecutive;
use WhittingtonsOfDmg\SiteStatDash\Models\Server;
use WhittingtonsOfDmg\SiteStatDash\Models\Site;

class SitesAdmin extends ModelAdmin
{
    private static array $managed_models = [Server::class, Site::class, AccountExecutive::class];
    private static string $url_segment = 'web-properties';
    private static string $menu_title = 'Web Properties';
    private static string $menu_icon_class = 'font-icon-block-globe-2';
}
