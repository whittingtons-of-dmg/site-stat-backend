<?php

namespace WhittingtonsOfDmg\SiteStatDash\Models\Admin;

use SilverStripe\Admin\ModelAdmin;
use WhittingtonsOfDmg\SiteStatDash\Models\ServerSoftware;
use WhittingtonsOfDmg\SiteStatDash\Models\SiteSoftware;

class SoftwareAdmin extends ModelAdmin
{
    private static array $managed_models = [ServerSoftware::class, SiteSoftware::class];
    private static string $url_segment = 'software';
    private static string $menu_title = 'Software Packages';
    private static string $menu_icon_class = 'font-icon-p-package';
}
