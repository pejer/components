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
     * @var Array
     */
    private $propelConfig;

    /**
     * Initializes the object
     * @param Array $propelConfig
     * @param Request $request
     * @param Response $response
     */
    public function __construct($propelConfig, Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->propelConfig = $propelConfig;
        $this->propelNamespace = $propelConfig['namespace'];
    }

    /**
     * Handles get-requests with an id
     *
     * @method GET
     * @uri :table/:id
     * @routeAlias propelApi.get
     * @param      $table
     * @param null $id
     * @param  callable $next
     * @param      $dependencyInjection
     */
    public function getObject($table, $id = null, callable $next = null)
    {
        $obj = $this->propelNamespace . '\\' . $table . 'Query';
        # query object
        $propelQuery = new $obj();
        $this->response->setBody($propelQuery->findPk($id)->toJSON());
        if (isset($next)) {
            $next();
        }
    }

    /**
     * Handels post-requests
     *
     * @method POST
     * @uri :table/:id
     * @routeAlias propelApi.post
     * @param string $table
     * @param int $id
     * @param callable|null $next
     */
    public function post($table, $id, callable $next = null)
    {
        $obj = $this->propelNamespace . '\\' . $table;
        $post = new $obj();
        if (is_string($this->request->body)) {
            $post->fromJSON($this->request->body);
        } else {
            $post->fromArray($this->request->body);
        }
        if (is_callable(array($post, 'validate')) && !$post->validate()) {
            $message = '';
            foreach ($post->getValidationFailures() as $failure) {
                $message .= "Property " . $failure->getPropertyPath() . ": " . $failure->getMessage() . "\n";
            }
            throw new \RuntimeException($message);
        }
        $post->save();
        $this->response->setBody($post->toJSON());
        if (isset($next)) {
            $next();
        }
    }
}
