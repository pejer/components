<?php

/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2015-02-08 10:02
 */
class AppTest extends PHPUnit_Framework_TestCase
{

    private $object;
    private $depencenyInjection;

    public function setUp()
    {

        $this->depencenyInjection = new \DHP\components\dependencyinjection\DependencyInjection();
        $this->depencenyInjection->set('\DHP\components\request\Request')->setArguments(
            array(
                "GET",
                "blog/this-is-the-title",
                '{"test":true}'
            )
        );
        $response     = new \DHP\components\response\Response();
        $request      = $this->depencenyInjection->get('\DHP\components\request\Request');
        $event        = new \DHP\components\event\Event();
        $routing      = new \DHP\components\routing\Routing();
        $this->object = new \DHP\components\App($request, $response, $event, $this->depencenyInjection, $routing);
    }

    public function testLoadAppConfig()
    {
        eval('class AppController{
/**
* @method GET
* @uri :title
* @routeAlias blog.title
*/
function title($title, $next){
    if($title !== "this is the title"){
        $next();
    }
    echo "Title is: {$title}";
    return $title;
}
}
class AppControllerSecond{
/**
* @method GET
* @uri blog/:title
* @routeAlias blog.title
*/
function title($title, $next){
    $next();
    echo "Will not show since $next() isn\'t executed above;";
    return $title;
}
}');
        $config = array(
            "controllers" => array(
                array('AppController','blog'),
                array('AppControllerSecond')
            ),
            "middleware"  => array('\DHP\components\middleware\BodyParser'),
            "routes"      => array(
                array(
                    'method'  => array('GET'),
                    'uri'     => 'blog/:title',
                    'closure' => function ($title, $next) {
                        echo "echo ";
                        $next();
                    }
                )
            )
        );
        $this->object->loadAppConfig($config);
        $obj = new StdClass();
        $this->object->apply($obj);
        $d = $this->object;
        $this->expectOutputString('echo Title is: this is the title');
        $d();
        $this->assertEquals(
            array('test' => true),
            $this->depencenyInjection->get('\DHP\components\request\Request')->body
        );
    }
}
