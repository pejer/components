<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2015-02-07 12:17
 */

namespace DHP\components\middleware;

use DHP\components\abstractClasses\Middleware;
use DHP\components\request\Request;

/**
 * Class BodyParser
 *
 * Parses body of request, expecting JSON-data, and turns it into a php-object via json_decode
 *
 * @package DHP\components\middleware
 */
class BodyParser extends Middleware
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Parses the body contents and return as object // array
     */
    public function __invoke()
    {
        $this->request->body = json_decode($this->request->body, true, 512, \JSON_BIGINT_AS_STRING);
    }
}
