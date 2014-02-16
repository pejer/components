<?php
/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2014-02-13 16:09
 *
 */

namespace utils;
use DHP\components\utils\Variables;

class VariablesTest extends \PHPUnit_Framework_TestCase {

    /** @var  \DHP\components\utils\Variables */
    private $object;

    public function setUp()
    {
        $this->object = new Variables(array('henrik' => 'defaultEnv'));
    }

    public function testSettingValues(){
        $this->assertEquals($this->object->henrik, 'defaultEnv');
        $this->object->henrik = 'newDefEnv';
        $this->assertEquals($this->object->henrik, 'newDefEnv');

        $this->object->henrik(Variables::DEFAULT_ENVIRONMENT,'newNewEnv');
        $this->assertEquals($this->object->henrik, 'newNewEnv');
    }
}
