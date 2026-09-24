<?php

namespace {

    use SilverStripe\CMS\Model\SiteTree;

    class Page extends SiteTree
    {
        private static $db = [];

        private static $has_one = [];

        public function canCreate($member = null, $context = []): bool
        {
            return false;
        }

        public function canEdit($member = null, $context = []): bool
        {
            return false;
        }
    }
}
