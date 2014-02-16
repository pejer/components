<?php
/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2014-02-13 16:28
 *
 */

namespace event;


use DHP\components\event\Event;

class EventTest extends \PHPUnit_Framework_TestCase{
    /** @var  \DHP\components\event\Event */
    private $object;

    public function setUp()
    {
        $this->object = new Event();
    }

    public function testRegisterFunction()
    {
        $global = false;
        $anonMethod = function ($val = null) use (&$global) {
            $global = $val;
            return true;
        };
        $this->object->register('test', $anonMethod);

        $anonMethod2 = function ($val = null) use (&$global) {
            $global = $val .'hej';
            return false;
        };
        $this->object->register('*',$anonMethod2);
        $val1 = 'new value';
        $val2 = 'new value2';
        $val3 = 'new value3';
        $val4 = 'new value4';
        $val5 = 'new value5';
        $val6 = 'new value6';
        $val7 = 'new value7';
        $val8 = 'new value8';
        $this->object->trigger('test');
        $this->assertFalse($this->object->trigger('doesNotExist'));
        $this->assertEquals('hej', $global);
        $this->object->trigger('test', $val1);
        $this->object->trigger('test', $val1, $val2);
        $this->object->trigger('test', $val1, $val2, $val3);
        $this->object->trigger('test', $val1, $val2, $val3, $val4);
        $this->object->trigger('test', $val1, $val2, $val3, $val4, $val5);
        $this->object->trigger('test', $val1, $val2, $val3, $val4, $val5, $val6);
        $this->object->trigger('test', $val1, $val2, $val3, $val4, $val5, $val6, $val7);
        $this->object->trigger('test', $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8);

        $this->assertEquals('new valuehej', $global);
    }

    public function testNamespacedEvents(){
        $this->object->register('test.test',function($val = null){
              return 'works';
          }
        );

        $this->assertEquals($this->object->trigger('test.test'),'works');

        $this->object->register('test.*',function($val = null){
              return 'Overridden';
          }
        );
        $this->assertEquals($this->object->trigger('test.test'),'Overridden');
    }

    public function testRegisterMethod()
    {
        eval("class ttt{
    public \$global;
    public function erun(\$val = null){
        \$this->global = \$val;
       }
}");
        $o = new \ttt();
        $this->object->register('test', array($o, 'erun'));
        $val1 = 'new value';
        $val2 = 'new value2';
        $val3 = 'new value3';
        $val4 = 'new value4';
        $val5 = 'new value5';
        $val6 = 'new value6';
        $val7 = 'new value7';
        $val8 = 'new value8';
        $this->object->trigger('test');
        $this->assertEquals(null, $o->global);
        $this->object->trigger('test', $val2);
        $this->assertEquals($val2, $o->global);

        $this->object->trigger('test', $val3, $val2);
        $this->assertEquals($val3, $o->global);


        $this->object->trigger('test', $val4, $val2, $val3);
        $this->assertEquals($val4, $o->global);

        $this->object->trigger('test', $val5, $val2, $val3, $val4);
        $this->assertEquals($val5, $o->global);

        $this->object->trigger('test', $val6, $val2, $val3, $val4, $val5);
        $this->assertEquals($val6, $o->global);

        $this->object->trigger('test', $val7, $val2, $val3, $val4, $val5, $val6);
        $this->assertEquals($val7, $o->global);

        $this->object->trigger('test', $val8, $val2, $val3, $val4, $val5, $val6, $val7);
        $this->assertEquals($val8, $o->global);
    }

    public function testSubscriber()
    {
        eval('class observer{
        public $value,$event;
        public function __construct($event){$this->event = $event;}
        public function run(){
            $v = "new value";
            $this->value = $this->event->triggerSubscribe($this,"observerCalls",$v);
        }
        }');

        $obs = new \observer($this->object);
        $this->assertEquals(null,$obs->value);
        $obs->run();
        $this->assertEquals(null,$obs->value);
        eval('class sub{
            public function observerCalls($val){
                return false;
            }
        }');
        $sub = new \sub;
        $this->object->subscribe($obs,$sub);
        $obs->run();
        $this->assertFalse($obs->value);
    }

}
