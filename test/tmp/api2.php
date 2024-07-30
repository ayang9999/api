<?php

namespace Ayang\ApiManager\Test\tmp;
use GuzzleHttp\Client;
class api2 extends apiClient
{
    public function handel(string $m, string $url, array $p) : string
    {
        return 1;
        return $this->client->request($m, $url, $p);
    }
}