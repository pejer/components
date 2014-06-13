<?php
/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2014-02-21 13:49
 *
 */

namespace utils;


use DHP\components\utils\String;

class StringTest extends \PHPUnit_Framework_TestCase {

    /** @var  \DHP\components\utils\String */
    private $object;

    public function setUp(){
        $this->object = new String('testValue');
    }

    public function testValue(){
        $this->assertEquals('testValue',(string)$this->object);
        $o = $this->object;
        $o('newValue');
        $this->assertEquals('newValue',(string)$this->object);

        $this->assertEquals('testValue', (string)$this->object->replace('new','test'));
        $this->assertEquals('  testValue',(string)$this->object->pad(11,' ', STR_PAD_LEFT));
        $this->assertEquals('  testtest',(string)$this->object->pregReplace('#Value#','test'));
        $matches = null;
        $this->assertEquals($this->object->pregMatch('#(estt)#',$matches),1);
        $this->assertEquals(array('estt','estt'), $matches);
    }
}
