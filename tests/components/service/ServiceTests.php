<?php

declare(strict_types=1);

use DHP\components\service\Service;
use PHPUnit\Framework\TestCase;


class ServiceTests extends TestCase
{

  public function setUp(): void
  {
    if (!class_exists('app\services\testing')) {

      $eval = <<<PELLE
    namespace app\services;
    class testing{
      public function __construct(string \$mess="default message", string \$plus = 'this is plus'){
        echo \$mess . \$plus;
      }
    }
    class flaska{
      public function __construct(testing \$testing){
      }
    }

    class complicated{
      public function __construct(flaska \$flaska, string \$mess="Don't want to see this"){
        echo \$mess;
      }

      public function check(\$one){
        echo \$one;
      } 
    }
PELLE;
      eval($eval);
    }
  }

  public function testServiceController()
  {
    $service = new Service();
    $this->expectOutputString("herre - its working new message herre - its working new message");
    $service->prepare('app\services\testing')
      ->withArgs(plus: 'new message ', mess: "herre - its working ")
      ->store();
    $obj = $service->load('app\services\testing');
    $this->assertEquals('app\services\testing', get_class($obj));

    $serviceTwo = new Service();
    $serviceTwo->prepare('app\services\testing')
      ->withArgs("herre - its working ", "new message")
      ->store();
    $obj = $serviceTwo->load('app\services\testing');
  }

  public function testService()
  {
    $service = new Service();
    $this->expectOutputString("default messagethis is plus this is what we want to see");
    $service->prepare('app\services\complicated')
      ->withArgs(mess: 'should not see this')
      ->store();
    $objone = $service->load('app\services\complicated', [null, ' this is what we want to see']);
    $objtwo = $service->load('app\services\complicated', [null, ' this will never show']);
    $this->assertEquals('app\services\complicated', get_class($objone));
    $this->assertEquals(spl_object_hash($objone), spl_object_hash($objtwo));
  }

  public function testServiceSomeMoah()
  {
    $service = new Service();
    $this->expectOutputString('default messagethis is plus and this is the new mess value');
    $service->prepare('app\services\complicated')
      ->withArgs(mess: ' and this is the new mess value')
      ->load();
  }
}
