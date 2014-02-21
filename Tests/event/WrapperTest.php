<?php
/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2014-02-21 12:48
 *
 */

namespace event;

use DHP\components\event\Wrapper;

class WrapperTest extends \PHPUnit_Framework_TestCase {

    public function testWrapper()
    {
        eval("class tt{public function __construct(){} public function construct(){} public function publik2Funktion(\\DHP\\components\\event\\Wrapper \$test, \$example, \$testing = 'sven'){} public function publikFunktion(array \$var1){} private function privatFunktion(){} final function finalFunktion(){}}");

        $mockClassName = '\tt_EventWrapper';
        $this->assertEquals($mockClassName,Wrapper::wrap('tt'));
        $this->assertEquals($mockClassName,Wrapper::wrap('tt'));

        $reflectionObject = new \ReflectionClass($mockClassName);

        $reflectionOriginalObject = new \ReflectionClass('tt');
        $originalMethods = array();
        foreach($reflectionOriginalObject->getMethods() as $method){
            $originalMethods[$method->name] = $method;
        }

        foreach($reflectionObject->getMethods() as $key => $method){
            switch($method->name){
                case '__construct':
                    $this->assertTrue($method->isPublic());
                    $this->assertEquals('tt_EventWrapper',$method->getDeclaringClass()->name);
                    $this->assertNotEquals($originalMethods[$method->name]->getParameters(),$method->getParameters());
                    $this->assertEquals(count($method->getParameters()),1);
                    $this->assertEquals($method->getParameters()[0]->name, '__________event');
                    break;
                case 'construct':
                    $this->assertTrue($method->isPublic());
                    $this->assertEquals('tt_EventWrapper',$method->getDeclaringClass()->name);
                    $this->assertEquals($originalMethods[$method->name]->getParameters(),$method->getParameters());
                    break;
                case 'finalFunktion':
                    $this->assertTrue($method->isPublic());
                    $this->assertEquals('tt',$method->getDeclaringClass()->name);
                    $this->assertEquals($originalMethods[$method->name]->getParameters(),$method->getParameters());
                    break;
                case 'publik2Funktion':
                    $this->assertTrue($method->isPublic());
                    $this->assertEquals('tt_EventWrapper',$method->getDeclaringClass()->name);
                    $this->assertEquals($originalMethods[$method->name]->getParameters(),$method->getParameters());
                    break;
                case 'publikFunktion':
                    $this->assertTrue($method->isPublic());
                    $this->assertEquals('tt_EventWrapper',$method->getDeclaringClass()->name);
                    $this->assertEquals($originalMethods[$method->name]->getParameters(),$method->getParameters());
                    break;
                case '__call':
                    $this->assertTrue($method->isPublic());
                    $this->assertEquals('tt_EventWrapper',$method->getDeclaringClass()->name);
                    break;
                case '__toString':
                    $this->assertTrue($method->isPublic());
                    $this->assertEquals('tt_EventWrapper',$method->getDeclaringClass()->name);
                    break;
                case 'privatFunktion':
                    $this->assertFalse($method->isPublic());
                    $this->assertEquals('tt',$method->getDeclaringClass()->name);
                    $this->assertEquals($originalMethods[$method->name]->getParameters(),$method->getParameters());
                    break;
                default:
                    $this->assertFalse(true, "{$method->name} : exists in object that we are not testing");
                    break;
            }
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNonExistingClass(){
        Wrapper::wrap('nonExistingClass');
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testFinalClass(){
        eval('final class finalKlass{}');
        Wrapper::wrap('finalKlass');
    }
}
