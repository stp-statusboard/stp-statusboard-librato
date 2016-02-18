<?php

namespace StpBoard\Librato;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use StpBoard\Base\BoardProviderInterface;
use StpBoard\Base\TwigTrait;
use StpBoard\Librato\Exception\LibratoException;
use StpBoard\Librato\Service\Client;
use StpBoard\Librato\Service\LibratoService;
use Symfony\Component\HttpFoundation\Request;

class LibratoControllerProvider implements ControllerProviderInterface, BoardProviderInterface
{
    use TwigTrait;

    /**
     * @var LibratoService
     */
    protected $libratoService;

    /**
     * @var array
     */
    protected $methodsMap = [
        'rpm' => [
            'method' => 'fetchRpmForGraphWidget',
            'template' => 'chart.html.twig',
        ],
        'average_response_time' => [
            'method' => 'fetchAverageResponseTimeForGraphWidget',
            'template' => 'chart.html.twig',
        ],
    ];

    /**
     * Returns route prefix, starting with "/"
     *
     * @return string
     */
    public static function getRoutePrefix()
    {
        return '/librato';
    }

    /**
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        $this->libratoService = new LibratoService(new Client());

        $this->initTwig(__DIR__ . '/views');
        $controllers = $app['controllers_factory'];

        $controllers->get(
            '/',
            function (Application $app) {
                /** @var Request $request */
                $request = $app['request'];

                try {
                    $config = $this->getConfig($request);

                    $result = $this->libratoService->$config['method']($config);

                    return $this->twig->render(
                        $config['template'],
                        [
                            'name' => $config['name'],
                            'data' => $result,
                        ]
                    );
                } catch (LibratoException $e) {
                    return $this->twig->render('error.html.twig', [
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        );

        return $controllers;
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws LibratoException
     */
    protected function getConfig(Request $request)
    {
        $name = $request->get('name');
        if (empty($name)) {
            throw new LibratoException('Empty chart name');
        }

        $apiUser = $request->get('apiUser');
        if (empty($apiUser)) {
            throw new LibratoException('Empty apiUser');
        }

        $apiToken = $request->get('apiToken');
        if (empty($apiToken)) {
            throw new LibratoException('Empty apiToken');
        }

        $action = $request->get('action');
        if (empty($action)) {
            throw new LibratoException('Empty action');
        }

        $begin = $request->get('begin', '-30minutes');

        if (!isset($this->methodsMap[$action])) {
            throw new LibratoException('Unrecognized action');
        }

        $method = $this->methodsMap[$action]['method'];
        $template = $this->methodsMap[$action]['template'];

        if (!method_exists($this->libratoService, $method)) {
            throw new LibratoException('Unrecognized method');
        }

        return [
            'name' => $name,
            'apiUser' => $apiUser,
            'apiToken' => $apiToken,
            'method' => $method,
            'template' => $template,
            'begin' => $begin,
        ];
    }
}
