<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2018-09-24
 * Time: 08:15
 */

namespace DHP\kaerna\interfaces;


use DHP\kaerna\ContainerInterface;
use DHP\kaerna\MiddlewareInterface;
use DHP\kaerna\RequestInterface;
use DHP\kaerna\ResponseInterface;

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