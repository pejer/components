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
        public function test(int \$num){
            return ++\$num;
        }
    }
    class flaska{
      public function __construct(testing \$testing){
        var_dump(\$testing);
      }
    }
PELLE;
      eval($eval);
    }
  }

  public function testServiceController()
  {
    $service = new Service();
    $obj = $service->load('app\services\testing');
    $this->assertEquals('app\services\testing', get_class($obj));
  }

  public function testService()
  {
    $service = new Service();
    $obj = $service->load('app\services\flaska');
    $this->assertEquals('app\services\flaska', get_class($obj));
  }
  /* Example stuff
    public function testInit()
    {
        $service = new Services(["pejer" => '\app\services\testing']);
        $eval = <<<PELLE
        namespace app\services;
        class testing{
            public function test(int \$num){
                return ++\$num;
            }
        }
PELLE;
        eval($eval);
        $obj = $service->service("testing");
        $this->assertEquals(is_a($obj, '\app\services\testing'), true);
        $this->assertEquals($obj, $service->get('\app\services\testing'));
        $this->assertEquals($obj, $service->get('pejer'));
    }
*/
}
