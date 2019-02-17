<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-10-15 20:36
 */

namespace DHP\tests\components\container;

use DHP\components\container\Proxy;
use DHP\components\container\Unicorn;
use DHP\components\request\Request;
use PHPUnit\Framework\TestCase;

class ProxyTest extends TestCase
{
    public function testCreatingSimpleProxy()
    {
        $container = new Unicorn();
        $proxy     = new Proxy($container, '\DHP\components\request\Request');
        $this->assertEquals('DHP\components\request\Request', get_class($proxy->init()));
    }

    public function testCallingMethodOnProxy()
    {
        $container = new Unicorn();
        $proxy     = new Proxy($container, '\DHP\components\request\Request');
        $this->assertEquals('HEADER', $proxy->getMethod());
    }

    public function testProxyWithConstructorArguments()
    {
        $container = new Unicorn();
        $proxy     = new Proxy($container, '\DHP\components\request\Request');
        $proxy->addConstructorArguments('LATTJO', '/the/uri/for/this', 'this-is-thebody');
        $this->assertEquals('LATTJO', $proxy->getMethod());

        $proxy = new Proxy(
            $container,
            '\DHP\components\request\Request',
            'NEW_HEADER',
            '/the/uri/for/this',
            'this-is-thebody'
        );
        $this->assertEquals('NEW_HEADER', $proxy->getMethod());
    }

    public function testProxyWithMethodCalls()
    {
        $container = new Unicorn();
        $proxy     = new Proxy($container, '\DHP\components\container\Proxy');
        $request   = new Request('THIS-IS-METHOD');
        $proxy->addConstructorArguments($container, '\DHP\components\response\Response', $request);
        $proxy->addMethodCall('setBody', 'this is the body');
        $proxyProxy = $proxy->init();
        $this->assertEquals('DHP\components\container\Proxy', get_class($proxyProxy));
        $response = $proxyProxy->init();
        $this->assertEquals('this is the body', $response->getBody());
    }
}
