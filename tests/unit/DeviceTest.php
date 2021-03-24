<?php

namespace App\Tests\unit;

use App\Entity\Device;
use App\Entity\Pass;
use App\Tests\UnitTester;
use Codeception\Specify;
use Codeception\Test\Unit;

class DeviceTest extends Unit
{
    use Specify;

    /** @var UnitTester */
    protected $tester;

    /** @var \Faker\Generator */
    private $faker;

    /** @var \Symfony\Component\Validator\Validator\TraceableValidator */
    private $validator;

    /**
     * @var Device
     * @specify
     */
    private $device;

    protected function _before()
    {
        $this->faker = \Faker\Factory::create('nl_NL');
        //$this->validator = $this->tester->grabService('validator');
    }

    protected function _after()
    {

    }

    /**
     * unit test
     * @group manager
     */
    public function testCanMake()
    {
        $this->device = new Device();

        $this->specify("Device is ok", function() {

            /** @var Pass $pass */
            $pass = $this->tester->have(Pass::class);

            $name = $this->faker->word;

            $this->device->setPass($pass);

            //$errors = $this->validator->validate($this->category);
            //$this->assertEquals(0, sizeof($errors));

            verify($this->device->getPass())->equals($pass);

        });
    }

    /**
     * integration test
     * @group manager
     */
    public function testCanSave()
    {
        /** @var Device $device */
        $device = $this->tester->have(Device::class);

        $this->tester->seeInRepository(Device::class, [
            'pass' => $device->getPass(),
        ]);
    }
}
