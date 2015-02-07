<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2014-02-09 07:35
 */

namespace routing;


use DHP\components\routing\Routing;

class RoutingTest extends \PHPUnit_Framework_TestCase
{

    /** @var  \DHP\components\routing\Routing */
    private $object;

    public function setUp()
    {
        $this->object = new Routing();
    }

    public function testAlias()
    {
        $this->object->add(array('GET'), 'blog/:title', function () {
        }, 'blog.page');
        $this->assertEquals('blog/this-is-the-title', $this->object->uriForRoute('blog.page', array('title' => 'this-is-the-title')));
        $assert = array(
            'POST'   => array(),
            'PUT'    => array(),
            'DELETE' => array(),
            'HEADER' => array(),
            'ANY'    => array()
        );
        $routes = $this->object->get();
        $get = $routes['GET'];
        unset($routes['GET']);
        $this->assertEquals($assert, $routes);
        $this->assertNotNull($get['blog/:title']);

        $this->assertNotNull($this->object->match('GET', 'blog/title-of-blog-post'));
        $this->assertNotNull($this->object->match('GET', 'routes/for/uri/does/not/exist'));
    }

    public function testMatchingRoutes()
    {
        $this->object->add(array('GET'), 'blog/:title', function () {
        }, 'blog.page');
        $assert = array(
            'POST'   => array(),
            'PUT'    => array(),
            'DELETE' => array(),
            'HEADER' => array(),
            'ANY'    => array()
        );
        $routes = $this->object->get();
        $get = $routes['GET'];
        unset($routes['GET']);
        $this->assertEquals($assert, $routes);
        $this->assertNotNull($get['blog/:title']);

        $this->assertNotNull($this->object->match('GET', 'blog/title-of-blog-post'));
        $this->assertEquals(array(), $this->object->match('GET', 'routes/for/uri/does/not/exist'));
    }

    public function testCustomParamTypes()
    {
        $this->object->addCustomParameter('user', function () {
            return "userID";
        });

        $this->object->add('GET', 'user/:user', function ($user) {
            return "The id of the user is {$user}";
        });

        $matchingRoute = $this->object->match('GET', 'user/2');
        $this->assertNotNull($matchingRoute);

        $this->assertEquals("The id of the user is userID", $matchingRoute[0]['closure']($matchingRoute[0]['route'][0]));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAddDuplicateRoute()
    {
        $this->object->add('GET', '/blog/:title', function () {
        });
        $this->object->add('GET', '/blog/:title', function () {
        });
    }

    public function testMatching()
    {
        $this->object->add('GET', 'about', function () {
        });
        $this->assertNotNull($this->object->match('GET', 'about'));

        $this->object->add('GET', 'about/:title', function () {
        });
        $this->assertEquals(array(), $this->object->match('GET', 'aboutt/titlte'));
    }

    public function testGeneratingRoutes()
    {
        eval('class Controller{
/**
* @method GET
* @uri blog/:title
* @routeAlias blog.title
*/
function title($title){
    return $title;
}
}');
        $this->object->makeRoutesForClass('Controller', 'personal');
        $matchingRoute = $this->object->match('GET', 'personal/blog/here-is-the-title');
        $this->assertNotNull($matchingRoute);
        /** @noinspection PhpUndefinedClassInspection */
        $o = new \Controller();
        $this->assertEquals("here is the title", call_user_func_array(array($o, $matchingRoute[0]['closure']['method']), $matchingRoute[0]['route']));
    }
}
