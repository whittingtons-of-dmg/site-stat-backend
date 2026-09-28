<?php

namespace WhittingtonsOfDmg\SiteStatDash\Service;

use WhittingtonsOfDmg\SiteStatDash\Models\Server;

class HeartBeatService
{
    public function monitor()
    {

    }

    private function buildQueue(): array
    {
        $queue = [];

        $sdl = Server::get();

        foreach ($sdl as $server) {
            $queue[$server->Title] = $server->getSitesUrlMap();
        }

        return $queue;
    }
}
