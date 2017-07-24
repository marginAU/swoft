<?php

namespace swoft\http;

use swoft\App;

/**
 *
 *
 * @uses      HttpClient
 * @version   2017年07月15日
 * @author    stelin <phpcrazy@126.com>
 * @copyright Copyright 2010-2016 swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class HttpClient
{
    const GET = "GET";

    const POST = "POST";

    /**
     * @param string     $url
     * @param string     $method
     * @param array|null $data
     * @param int        $timeout
     * @param array      $headers
     * <pre>
     * [
     *   'Host' => "localhost",
     *   "User-Agent" => 'Chrome/49.0.2587.3',
     *   'Accept' => 'text/html,application/xhtml+xml,application/xml',
     *   'Accept-Encoding' => 'gzip',
     * ]
     * </pre>
     */
    public static function call(string $url, string $method, array $data = array(), int $timeout = 200, array $headers = [])
    {

        list($host, $port, $uri) = self::parseUrl($url);

        $client = new \Swoole\Coroutine\Http\Client($host, $port);
        $client->setHeaders($headers);
        $client->set([ 'timeout' => $timeout]);
        $client->setMethod($method);
        $client->setData(http_build_query($data));
        $client->execute($url);
        $result =  $client->body;
        $client->close();
        return $result;
    }

    public static function deferCall(string $url, string $method, array $data = array(), int $timeout = 200, array $headers = [])
    {

        $profileKey = $method.".".$url;
        list($host, $port, $uri) = self::parseUrl($url);

        $client = new \Swoole\Coroutine\Http\Client($host, $port);
        $client->setHeaders($headers);
        $client->set([ 'timeout' => $timeout]);
        $client->setMethod($method);
        $client->setData(http_build_query($data));
        $client->setDefer();
        $result = $client->execute($url);
        return new HttpResult(null, $client, $profileKey, $result);
    }

    public static function parseUrl($url)
    {
        $defaultPorts = [
            'http'  => 80,
            'https' => 443
        ];

        $parses = parse_url($url);
        $protocol = $parses['scheme'];

        $host = $parses['host'];
        $path = $parses['path']?? "";
        $query = $parses['query']?? "";
        $uri = $path . "?" . $query;
        $port = $parses['port']?? $defaultPorts[$protocol];

        return [$host, $port, $uri];
    }
}