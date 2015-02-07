<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2015-02-07 11:54
 */

namespace DHP\components;

use DHP\components\response\Response as Response;
use DHP\components\request\Request as Request;

/**
 * Class app
 *
 * This class is the basis for the app
 * @package DHP\components
 */
class app
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Response
     */
    private $response;

    /**
     * @param Request  $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response){
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Loads and setups the routing for this app
     * @param Array $routes
     */
    public function loadRoutes(Array $routes){

    }

    /**
     * Loads the config for the app and initialize it
     *
     * @param array $config
     */
    public function loadAppConfig(Array $config){

    }

    /**
     * With the method, we apply modules, middleware or other
     * functionality to the application
     * @param mixed $applyThis the object, function, closure to apply
     */
    public function apply($applyThis){

    }

    /**
     * This will start the app
     */
    public function __invoke(){

    }

    /**
     * This will invoke all the middlewares that has been applied
     */
    private function runMiddleware(){

    }
}
