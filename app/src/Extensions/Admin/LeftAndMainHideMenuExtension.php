<?php

namespace WhittingtonsOfDmg\SiteStatDash\Extensions\Admin;

use SilverStripe\Admin\CMSMenu;
use SilverStripe\AssetAdmin\Controller\AssetAdmin;
use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\Core\Extension;
use SilverStripe\Reports\ReportAdmin;
use SilverStripe\VersionedAdmin\ArchiveAdmin;

class LeftAndMainHideMenuExtension extends Extension
{
    protected function onInit()
    {
        CMSMenu::remove_menu_class(ReportAdmin::class);
        CMSMenu::remove_menu_class(CMSMain::class);
        CMSMenu::remove_menu_class(AssetAdmin::class);
        CMSMenu::remove_menu_class(ArchiveAdmin::class);
    }
}
