<?php

namespace StpBoard\Librato\Service;

use StpBoard\Librato\Exception\LibratoException;
use DateTime;

class LibratoService
{
    const BASE_URL_V1 = 'https://metrics-api.librato.com/v1';

    const RPM_FOR_GRAPH_WIDGET_URL = self::BASE_URL_V1 . '/metrics/router.connect?resolution=1&start_time=%s&end_time=%s';
    const AVERAGE_RESPONSE_TIME_URL = self::BASE_URL_V1 . '/metrics/router.service?resolution=1&start_time=%s&end_time=%s';

    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param array $config
     *
     * @return array
     * @throws LibratoException
     */
    public function fetchRpmForGraphWidget($config)
    {
        return $this->fetchMetricForGraph($config, self::RPM_FOR_GRAPH_WIDGET_URL);
    }

    /**
     * @param array $config
     *
     * @return array
     * @throws LibratoException
     */
    public function fetchAverageResponseTimeForGraphWidget($config)
    {
        return $this->fetchMetricForGraph($config, self::AVERAGE_RESPONSE_TIME_URL);
    }

    /**
     * @param array $config
     * @param string $url
     *
     * @return array
     */
    protected function fetchMetricForGraph($config, $url)
    {
        $url = sprintf($url, strtotime($config['begin']), strtotime('now'));

        $data = $this->client->getJSON($url, $config);

        $result = [];
        foreach ($data['measurements']['unassigned'] as $singleStat) {
            $result[] = [
                'x' => 1000 * (int)$singleStat['measure_time'],
                'y' => $singleStat['value'],
            ];
        }

        return $result;
    }
}
