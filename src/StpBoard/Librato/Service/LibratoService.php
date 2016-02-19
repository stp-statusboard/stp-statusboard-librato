<?php

namespace StpBoard\Librato\Service;

use StpBoard\Librato\Exception\LibratoException;
use DateTime;

class LibratoService
{
    const BASE_URL_V1 = 'https://metrics-api.librato.com/v1';

    const RPM_FOR_GRAPH_WIDGET_URL = self::BASE_URL_V1 . '/metrics/router.connect?resolution=60&start_time=%s&end_time=%s';
    const AVERAGE_RESPONSE_TIME_URL = self::BASE_URL_V1 . '/metrics/router.service?resolution=60&start_time=%s&end_time=%s';
    const ERROR_RATE_URL = self::BASE_URL_V1 . '/metrics/app-error-percentage?resolution=3600&start_time=%s&end_time=%s';

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
     *
     * @return string
     * @throws LibratoException
     */
    public function fetchErrorRate($config)
    {
        return $this->fetchMetric($config, self::ERROR_RATE_URL, '%');
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

    /**
     * @param array $config
     * @param string $url
     * @param string $unit
     *
     * @return string
     */
    protected function fetchMetric($config, $url, $unit = '')
    {
        $url = sprintf($url, strtotime($config['begin']), strtotime('now'));

        $data = $this->client->getJSON($url, $config);

        $sum = 0;
        $count = 0;

        foreach ($data['measurements'] as $measurement) {
            foreach ($measurement['series'] as $singleStat) {
                $sum += $singleStat['value'];
                $count++;
            }
        }

        $average = $count ? $sum / $count : 0;

        return round($average, 2) . $unit;
    }
}
