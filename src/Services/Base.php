<?php

namespace Neoassist\ProtocolExport\Services;

use Neoassist\ProtocolExport\Config;
use Neoassist\ProtocolExport\Exception;

/**
 * Class Config
 * @package Neoassist\ProtocolExport\Services
 */
class Base
{
    /**
     * @var string
     */
    protected $baseApi;

    /**
     * @var string
     */
    protected $authorization;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->baseApi = "https://{$config->subdomain}.neoassist.com";
        $this->authorization = "Authorization: apphub {$config->appkey}:{$config->appsecret}";
    }

    /**
     * @param string $method
     * @param string|null $path
     * @param string|null $bodyParams
     * @param string|null $uri
     * @return object|null
     * @throws Exception
     */
    protected function get(string $method, $path, $bodyParams = null, $uri = null)
    {
        try {
            $curl = curl_init();

            $headers = [$this->authorization];
            $options = [
                CURLOPT_URL => $uri ?? $this->baseApi . $path,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ];

            if (strtolower($method) === 'post') {
                $headers[] = 'Content-Type: application/json';
                if ($bodyParams) {
                    $options[CURLOPT_POSTFIELDS] = $bodyParams;
                }
            }

            $options[CURLOPT_HTTPHEADER] = $headers;

            curl_setopt_array($curl, $options);

            $response = curl_exec($curl);

            curl_close($curl);

            $result = json_decode($response);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("service get decode error: {$path} - " . json_last_error_msg());
            }

            return $result;
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * @param array $nodes
     * @return array
     */
    protected function multiple_threads_request($nodes)
    {
        $mh = curl_multi_init();
        $curl_array = [];

        $headers = [$this->authorization];

        foreach ($nodes as $i => $url) {
            $curl_array[$i] = curl_init($this->baseApi . $url);
            curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_array[$i], CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl_array[$i], CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl_array[$i], CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl_array[$i], CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_multi_add_handle($mh, $curl_array[$i]);
        }

        $running = NULL;
        do {
            usleep(10000 / 2);
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        $res = [];
        foreach ($nodes as $i => $url) {
            $res[$url] = json_decode(curl_multi_getcontent($curl_array[$i]));
            if (json_last_error() !== JSON_ERROR_NONE) {
                $res[$url] = null;
            }
        }

        foreach ($nodes as $i => $url) {
            curl_multi_remove_handle($mh, $curl_array[$i]);
        }

        curl_multi_close($mh);

        return $res;
    }
}
