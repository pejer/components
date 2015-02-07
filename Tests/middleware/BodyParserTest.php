<?php

/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2015-02-07 12:41
 */
class BodyParserTest extends \PHPUnit_Framework_TestCase
{


    public function testReadData()
    {
        $request = new DHP\components\request\Request(null, null, '{"henrik":"Pejer"}');
        $object  = new \DHP\components\middleware\BodyParser($request);

        $object();
        $this->assertEquals($request->body, Array("henrik" => "Pejer"));
    }
}
