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

        if( ! $response = file_get_contents($url, false, stream_context_create($contextOptions))){
            return null;
        }

        return json_decode($response, true);
    }
}