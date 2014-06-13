<?php

/**
 *
 * Created by: Henrik Pejer, mr@henrikpejer.com
 * Date: 2014-02-07 13:16
 *
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{


    /** @var \DHP\components\request\Request */
    private $object;

    public function setUp()
    {
        $this->object = new \DHP\components\request\Request();
    }

    public function testEnvironment()
    {
        $oldServer = $_SERVER;
        $oldGet    = $_GET;
        $oldPost   = $_POST;
        $oldFiles  = $_FILES;

        $this->assertEquals('HEADER', $this->object->method);

        $_SERVER['REQUEST_METHOD']       = 'GET';
        $_SERVER['REQUEST_URI']          = '/this-is/the-uri';
        $_SERVER['HTTP_HOST']            = 'localhost:9999';
        $_SERVER['HTTP_CONNECTION']      = 'keep-alive';
        $_SERVER['HTTP_ACCEPT']          = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
        $_SERVER['HTTP_USER_AGENT']      = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.77 Safari/537.36';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate,sdch';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.8,sv;q=0.6';

        $assertHeaders                    = array();
        $assertHeaders['Host']            = 'localhost:9999';
        $assertHeaders['Connection']      = 'keep-alive';
        $assertHeaders['Accept']          = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
        $assertHeaders['User-Agent']      = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.77 Safari/537.36';
        $assertHeaders['Accept-Encoding'] = 'gzip,deflate,sdch';
        $assertHeaders['Accept-Language'] = 'en-US,en;q=0.8,sv;q=0.6';

        $_POST  = array('POSTVAR' => 'POSTVALUE');
        $_GET   = array('GETVAR' => 'GETVALUE');
        $_FILES = array('A File' => array('error' => 0));

        $this->object->setupWithEnvironment();
        $this->assertEquals('GET', $this->object->method);
        $this->assertEquals('this-is/the-uri', $this->object->uri);
        $this->assertEquals($_POST, $this->object->post);
        $this->assertEquals($_GET, $this->object->get);
        $this->assertEquals('', $this->object->body);
        $this->assertEquals($assertHeaders, $this->object->headers);
        $this->assertEquals($_FILES, $this->object->files);
        $this->assertEquals(array_merge($_GET, $_POST), $this->object->variables);

        $this->object = new \DHP\components\request\Request();
        unset($_SERVER['REQUEST_URI']);
        $_SERVER['argv'] = array(
            'index.php',
            'blogging-is-fun'
        );
        $this->object->setupWithEnvironment();

        $this->assertEquals('GET', $this->object->method);
        $this->assertEquals('blogging-is-fun', $this->object->uri);
        $this->assertEquals($_POST, $this->object->post);
        $this->assertEquals($_GET, $this->object->get);
        $this->assertEquals('', $this->object->body);
        $this->assertEquals($assertHeaders, $this->object->headers);
        $this->assertEquals($_FILES, $this->object->files);
        $this->assertEquals(array_merge($_GET, $_POST), $this->object->variables);

        unset($_SERVER['argv'][1]);

        $this->object->setupWithEnvironment();

        $this->assertEquals('GET', $this->object->method);
        $this->assertEquals('', $this->object->uri);
        $this->assertEquals($_POST, $this->object->post);
        $this->assertEquals($_GET, $this->object->get);
        $this->assertEquals('', $this->object->body);
        $this->assertEquals($assertHeaders, $this->object->headers);
        $this->assertEquals($_FILES, $this->object->files);
        $this->assertEquals(array_merge($_GET, $_POST), $this->object->variables);


        $_SERVER = $oldServer;
        $_GET    = $oldGet;
        $_POST   = $oldPost;
        $_FILES  = $oldFiles;
    }

}
