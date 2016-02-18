<?php

namespace StpBoard\Librato\Service;

use StpBoard\Librato\Exception\LibratoException;

class Client
{
    /**
     * @param string $url
     * @param array $config
     *
     * @return array
     * @throws LibratoException
     */
    public function getJSON($url, $config)
    {
        return $this->parseJSON($this->request($url, $config));
    }

    /**
     * @param string $url
     * @param array $config
     *
     * @return string
     */
    protected function request($url, $config)
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_USERPWD, sprintf('%s:%s', $config['apiUser'], $config['apiToken']));
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curlHandle);

        curl_close($curlHandle);

        return $data;
    }

    /**
     * @param string $data
     *
     * @return array
     * @throws LibratoException
     */
    protected function parseJSON($data)
    {
        if ($data === false) {
            throw new LibratoException('Can not get data from Librato');
        }

        $data = json_decode($data, true);
        if ($data === null) {
            throw new LibratoException('Can not parse response from Librato');
        }

        return $data;
    }
}
