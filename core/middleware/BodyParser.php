<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2018-09-24
 * Time: 08:15
 */

namespace DHP_Karna\core\middleware;


use DHP_Karna\core\kernel\ContainerInterface;
use DHP_Karna\core\kernel\MiddlewareInterface;
use DHP_Karna\core\kernel\RequestInterface;
use DHP_Karna\core\kernel\ResponseInterface;

class BodyParser implements MiddlewareInterface
{

    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        callable $next
    ): ResponseInterface {
        $body = $request->getBody();
        try {
            // TODO: Implement better json handling
            $json = \json_decode($body, \true);
            if (\json_last_error() == \JSON_ERROR_NONE) {
                $request = $request->setBody($json);
            }
        } catch (\Exception $e) {
        }

        return $next($request, $response);
    }
}