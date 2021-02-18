<?php
declare(strict_types=1);

namespace KikCMS\Services\Util;


use KikCMS\Classes\Phalcon\Injectable;

class JsonService extends Injectable
{
    /**
     * @param string $url
     * @param string|null $username
     * @param string|null $password
     * @return mixed
     */
    public function getByUrl(string $url, string $username = null, string $password = null)
    {
        $contextOptions = ["ssl" => [
            "verify_peer"      => false,
            "verify_peer_name" => false,
        ]];

        if($username && $password){
            $contextOptions['http'] = ['header' => 'Authorization: Basic ' . base64_encode("$username:$password")];
        }

        if( ! $response = file_get_contents($url, false, stream_context_create($contextOptions))){
            return null;
        }

        return json_decode($response, true);
    }
}