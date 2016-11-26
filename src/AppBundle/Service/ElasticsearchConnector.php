<?php

namespace AppBundle\Service;

/**
 * @author Jozef Liška <jozoliska@gmail.com>
 */
class ElasticsearchConnector {

    /**
     * @var string
     */
    private $server;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    public function __construct($server, $username = null, $password = null)
    {
        $this->server = $server;
        $this->username = $username;
        $this->password = $password;
    }

    public function request($uri, $data = '', $method = 'GET')
    {
        $url = $this->server . '/' . $uri;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if (!is_null($this->username) && !is_null($this->password)) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}