<?php

/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2014-02-05 22:03
 */
class ResponseTest extends PHPUnit_Framework_TestCase
{

    /** @var \DHP\components\response\Response */
    private $object;

    public function setUp()
    {
        $this->object = new \DHP\components\response\Response();
    }

    public function testHeader()
    {
        $this->object->setHeader('something:somethingElse');
        $res = $this->object->getHeaders();

        $assertValue = array(
            'Something: somethingElse' => null
        );
        $this->assertEquals($assertValue, $res);

        $this->object->setHeader('Content-type:image/png', true,200);
        $res = $this->object->getHeaders();

        $assertValue['Content-Type: image/png'] = 200;
        $this->assertEquals($assertValue, $res);

        # tests that if we set replace to false, we do not override an already set header
        $this->object->setHeader('Content-type:image/png', false,300);
        $res = $this->object->getHeaders();

        $assertValue['Content-Type: image/png'] = 200;
        $this->assertEquals($assertValue, $res);

        $this->object->setHeader('Content-type:image/png', true,300);
        $res = $this->object->getHeaders();

        $assertValue['Content-Type: image/png'] = 300;
        $this->assertEquals($assertValue, $res);
    }

    public function testOutputString()
    {
        $this->object->setBody("This is the data");
        $this->assertEquals('This is the data', $this->object->getBody());
    }

    public function testOutputJson()
    {
        $this->object->setBody(array('this' => 'that', 5));
        $assertValue = '{"this":"that","0":5}';
        $this->assertEquals($assertValue, $this->object->getBody());

        $this->object->setBody((object)array('this' => 'that', 5));
        $assertValue = '{"this":"that","0":5}';
        $this->assertEquals($assertValue, $this->object->getBody());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testHeadersSent(){
        $this->object->setHeader('testing: Headers');
        $this->object->sendHeaders();
        $this->object->setHeader('Status: sent');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testHeaderStatus(){
        $this->object->setStatus(200);
        $assertEquals = array(
          'HTTP/1.1 200 OK' => 200
        );
        $this->assertEquals($assertEquals,$this->object->getHeaders());

        $this->object->setStatus(666);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAppendData(){
        $this->object->setBody('start.');
        $this->object->appendBody('Hello');
        $this->assertEquals('start.Hello',$this->object->getBody());

        $this->object->appendBody(3);
        $this->assertEquals('start.Hello3',$this->object->getBody());

        $this->object->appendBody(array('something'));
    }

    public function testAppendDataWithArraysAndObjects(){
        $this->object->appendBody(array('this'=>'is a test'));
        $this->object->appendBody(array('that'=>'is another test'));
        $assertEqual = '{"this":"is a test","that":"is another test"}';
        $this->assertEquals($assertEqual,$this->object->getBody());

        // object + array now THATs gonna be painful!
        $this->object->appendBody((object)array('Finally' => 'the last test'));
        $assertEqual = '{"this":"is a test","that":"is another test","Finally":"the last test"}';
        $this->assertEquals($assertEqual,$this->object->getBody());

    }

    public function testSend(){
        $this->object->setStatus(200);
        $this->object->setBody("All is well");
        $this->assertEquals(array("HTTP/1.1 200 OK"=>200),$this->object->getHeaders());
        $this->object->send();
        $this->expectOutputString("All is well");
    }
}