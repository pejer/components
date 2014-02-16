<?php
/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2014-02-13 15:47
 *
 */

namespace utils;

use DHP\components\utils\Constants;

class ConstantsTest extends \PHPUnit_Framework_TestCase {

    /** @var \DHP\components\utils\Constants  */
    private $object;
    public function setUp(){
        $this->object = new Constants(array('henrik' => 'Default Environment'));
    }
    /**
     * @expectedException \RuntimeException
     */
    public function testGet(){
        $this->assertEquals('Default Environment',$this->object->henrik);
        $this->object->henrik = 'newValueRendersException';
    }
    /**
     * @expectedException \RuntimeException
     */
    public function testGetDifferentEnvironments(){
        $this->object->henrik('newEnvironment','This is the new Environment');
        $this->assertEquals('Default Environment',$this->object->henrik);
        $this->object->setDefaultEnvironment('newEnvironment');
        $this->assertEquals('This is the new Environment',$this->object->henrik);

        $this->object->setDefaultEnvironment('env3');
        $this->object->henrik = "works";
        $this->assertEquals('works',$this->object->henrik);
        $this->object->henrik = 'newValueRendersException';
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testEnvironments(){
        $this->object->setDefaultEnvironment('dev');
        $this->object->db = '127.0.0.1';
        $this->assertEquals($this->object->db, '127.0.0.1');
        # test that eventhough we are in another environment, the default global is still fetched
        $this->assertEquals('Default Environment',$this->object->henrik);
        $this->object->db('dev','newValueShouldRaiseException');
    }

    public function testDefaultEnvironment(){
        $this->object = new Constants(array('henrik' => 'Default Environment'),'prod');
        $this->assertEquals($this->object->henrik,'Default Environment');
        $this->assertNull($this->object->doesNotExist);
    }
}
