<?php
declare(strict_types=1);

namespace KikCMS\Services\Util;


use KikCMS\Classes\Phalcon\Injectable;

class JsonService extends Injectable
{
    /**
     * @param string $url
     * @return mixed
     */
    public function getByUrl(string $url)
    {
        $contextOptions = ["ssl" => [
            "verify_peer"      => false,
            "verify_peer_name" => false,
        ]];

        return json_decode(file_get_contents($url, false, stream_context_create($contextOptions)), true);
    }
}