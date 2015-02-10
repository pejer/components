<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2015-02-10 18:38
 */

namespace DHP\components\utils;

use DHP\components\request\Request;
use DHP\components\response\Response;

/**
 * Class propelApi
 *
 * This class will act as a API-server to interact with propel
 *
 * @package DHP\components\utils
 */
class propelApi
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
     * @var String
     */
    private $propelNamespace;

    /**
     * Initializes the object
     * @param String   $propelNamespace
     * @param Request  $request
     * @param Response $response
     */
    public function __construct($propelNamespace, Request $request, Response $response)
    {
        $this->request         = $request;
        $this->response        = $response;
        $this->propelNamespace = $propelNamespace;
    }

    /**
     * Handles get-requests with an id
     *
     * @method GET
     * @uri :table/:id
     * @routeAlias propelApi.get
     * @param      $table
     * @param null $id
     * @param null $edit
     * @param      $next
     * @param      $dependencyInjection
     */
    public function getObject($table, $id = null, $next)
    {
        $obj = $this->propelNamespace . '\\' . $table;
        $next();
    }

    /**
     * Handels post-requests
     *
     * @method POST
     * @uri :table/:id
     * @routeAlias propelApi.post
     */
    public function post()
    {

    }
}
