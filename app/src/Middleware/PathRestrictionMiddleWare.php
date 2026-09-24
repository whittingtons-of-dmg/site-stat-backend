<?php

namespace WhittingtonsOfDmg\SiteStatDash\Middleware;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\Middleware\HTTPMiddleware;
use WhittingtonsOfDmg\SiteStatDash\Models\Admin\SitesAdmin;

class PathRestrictionMiddleWare implements HTTPMiddleware
{
    private static array $allowed_prefixes = [
        'admin',        // CMS Admin interface
        'dev',        // CMS Admin interface
        'Security',     // Login, logout, password resets
        'api/v1',       // Add custom routes
    ];

    /**
     * @throws HTTPResponse_Exception
     */
    public function process(HTTPRequest $request, callable $delegate)
    {
        $path = trim($request->getURL(), '/');

        foreach (self::$allowed_prefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                if ($path === "admin/pages")
                    return HTTPResponse::create()->redirect(SitesAdmin::config()->get('url_segment'));
                return $delegate($request);
            }
        }

        throw new HTTPResponse_Exception('Page not found', 404);
    }
}
