<?php
/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2014-02-07 15:07
 *
 */
use DHP\components\dependencyinjection\DependencyInjection;
use DHP\components\dependencyinjection\Proxy;

class DependencyInjectionTest extends \PHPUnit_Framework_TestCase {

    /** @var  DHP\components\dependencyinjection\DependencyInjection */
    private $object;

    public function setUp(){
        $this->object = new DHP\components\dependencyinjection\DependencyInjection();
    }

    /**
     * @__expectedException \InvalidArgumentException
     */
    public function testSetValue(){
        $this->object->set('Henrik');
        $this->assertEquals('Henrik',$this->object->get('Henrik'));

        $this->assertNull($this->object->get('Bengt'));

        $value = array('Test'=>'We get the same array');

        $this->object->set('SameArray',$value);
        $this->assertEquals($value,$this->object->get('SameArray'));
        $this->object->set('stdClass');
        $d = new stdClass();
        $this->assertEquals($d,$this->object->get('stdClass'));
        $this->assertEquals(spl_object_hash($this->object->get('stdClass')),spl_object_hash($this->object->get('stdClass')));

        $this->object->set('SomeAlias','stdClass');
        $checkAgainst = $this->object->get('SomeAlias');
        $this->assertEquals(spl_object_hash($checkAgainst),spl_object_hash($this->object->get('SomeAlias')));


        $this->object->set('TestingSettingObject',$this->object->get('stdClass'));
        $this->assertEquals(spl_object_hash($this->object->get('TestingSettingObject')),spl_object_hash($this->object->get('stdClass')));

        $o = $this->object->set('Alias','DHP\components\dependencyinjection\Proxy')->setArguments(array('Testing'))->addMethodCall('FakeMethod',array("args","one","by","one"));
        $assertEqual = array(
          'class' => 'DHP\components\dependencyinjection\Proxy',
          'args' => array(
            'Testing'
          ),
          'methods'=>array(
            (object)array(
              'method'=>"FakeMethod",
              'args' => array("args","one","by","one")

            )
          )
        );
        $this->assertEquals($assertEqual,$o->get());

        $this->object->set('error','DHP\components\dependencyinjection\Proxy');
        # $this->assertNull($this->object->get('error'));
    }

    /**
     * @expectedException \ReflectionException
     */
    public function testClassInstatiation()
    {
        global $testValue;
        $testValue = null;
        eval('class Hepp{function __construct($Small = "Default value"){$this->p = $Small;} function autoCalled(){global $testValue;$testValue = "called";}}');
        eval('class Hepp23{function __construct(\Hepp $initvar){$this->p = $initvar;}}');
        eval('class Hepp234{function __construct(\Countable $initvar){$this->p = $initvar;}}');
        eval('class Hepp123{function __construct(\HeppDoesNotExist $initvar){$this->p = $initvar;}}');

        $this->assertNull($testValue);
        $o = $this->object->set('Hepp2','\Hepp')->setArguments(array('Small'=>"Bananer"))->addMethodCall('autoCalled');
        $this->assertEquals('Bananer',$this->object->get('Hepp2')->p);
        $this->assertEquals('called',$testValue);
        $o = $this->object->set('Hepp2','\Hepp')->setArguments(array("Bapple"));
        $this->assertEquals('Bapple',$this->object->get('Hepp2')->p);

        $o = $this->object->set('Hepp2','\Hepp');
        $this->assertEquals('Default value',$this->object->get('Hepp2')->p);

        $this->object->set('Small','DHP\components\dependencyinjection\Proxy');
        $this->assertInstanceOf('DHP\components\dependencyinjection\Proxy',$this->object->get('Hepp')->p);

        $this->assertNull($this->object->get('\HeppInt'));

        $this->assertNull($this->object->get('\ClassDoesNotExist'));
        $this->assertNull($this->object->get('\Hepp234'));

        $this->assertInstanceOf('Hepp',$this->object->get('Hepp23')->p);
        $this->assertNull($this->object->get('Hepp123'));
    }
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Trying to instantiate an interface or abstract class directly. Class was "\Hepp2345"
     */

    public function testLoadingAbstractClass(){
        eval('abstract class Hepp2345{function __construct(\Hepp $initvar){$this->p = $initvar;}}');
        $this->assertNull($this->object->get('\Hepp2345'));
    }
}
