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

    class loadWithoutPrepare{
        public function __construct(flaska \$flaska, private string \$val=""){
        }
        public function print(){
          echo \$this->val;
      }
      }
      class transient{
        public \$time = null;
      public function __construct(){
        \$this->time = time(); 
      }
      }
PELLE;
      eval($eval);
    }
  }

  public function testServiceController()
  {
    $service = new Service();
    $this->expectOutputString("herre - its working new message testtest herre - its working new message");
    $service->prepare('app\services\testing')
      ->withArgs(plus: 'new message ', mess: "herre - its working ")
      ->store();
    $service->prepare('app\services\testing')
      ->withArgs(plus: 'test ', mess: "test")
      ->withAliases(['fest'])
      ->store();
    $service->prepare('app\services\flaska')
      ->withArgs(plus: 'test ', mess: "test")
      ->asTransient()
      ->withAliases(['fest'])
      ->store();
    $obj  = $service->get('app\services\testing');
    $obj2 = $service->get('fest');
    $this->assertEquals('app\services\testing', get_class($obj));
    $this->assertEquals('app\services\testing', get_class($obj2));

    $serviceTwo = new Service();
    $serviceTwo->prepare('app\services\testing')
      ->withArgs("herre - its working ", "new message")
      ->store();
    $obj = $serviceTwo->get('app\services\testing');
  }

  public function testService()
  {
    $service = new Service();
    $this->expectOutputString("default messagethis is plus this is what we want to see");
    $service->prepare('app\services\complicated')
      ->withArgs(mess: 'should not see this')
      ->store();
    $objone = $service->get('app\services\complicated', [null, ' this is what we want to see']);
    $objtwo = $service->get('app\services\complicated', [null, ' this will never show']);
    $this->assertEquals('app\services\complicated', get_class($objone));
    $this->assertEquals(spl_object_hash($objone), spl_object_hash($objtwo));
  }

  public function testServiceSomeMoah()
  {
    $service = new Service();
    $this->expectOutputString('default messagethis is plus and this is the new mess value');
    $service->prepare('app\services\complicated')
      ->withArgs(mess: ' and this is the new mess value')
      ->get();
  }
  public function testServiceNoPrepare()
  {
    $service = new Service();
    $this->expectOutputString('default messagethis is plusand this');
    $obj = $service->prepare('app\services\loadWithoutPrepare')->withArgs(val: 'and this')->get();
    $obj->print();
  }

  public function testServiceTestTransient()
  {
    $service = new Service();
    $service->prepare('app\services\transient')
      ->asTransient()
      ->store();
    $objone = $service->get('app\services\transient');
    sleep(2);
    $objtwo = $service->get('app\services\transient');
    $this->assertNotEquals($objone->time, $objtwo->time);
    $this->assertNotEquals(spl_object_hash($objone), spl_object_hash($objtwo));
  }
}
