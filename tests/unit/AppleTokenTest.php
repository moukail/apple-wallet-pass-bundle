<?php

namespace App\Tests\unit;

use App\Entity\Pass;
use App\Tests\UnitTester;
use Codeception\Specify;
use Codeception\Test\Unit;
use Moukail\AppleWalletNotificationBundle\Entity\Token;
use Ramsey\Uuid\Uuid;

class AppleTokenTest extends Unit
{
    use Specify;

    /** @var UnitTester */
    protected $tester;

    /** @var \Faker\Generator */
    private $faker;

    /** @var \Symfony\Component\Validator\Validator\TraceableValidator */
    private $validator;

    /**
     * @var Token
     * @specify
     */
    private $appleToken;

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
        $this->appleToken = new Token();

        $this->specify("Category is ok", function() {

            /** @var Pass $pass */
            $pass = $this->tester->have(Pass::class);

            $token = $this->faker->uuid;

            $this->appleToken
                ->setPass($pass)
                ->setToken($token)
                ->setValidUntil(new \DateTime('last day of December this year'));

            //$errors = $this->validator->validate($this->category);
            //$this->assertEquals(0, sizeof($errors));

            verify($this->appleToken->getToken())->equals($token);

        });
    }

    /**
     * integration test
     * @group manager
     */
    public function testCanSave()
    {
        /** @var Token $appleToken */
        $appleToken = $this->tester->have(Token::class);

        $this->tester->seeInRepository(Token::class, [
            'pass' => $appleToken->getPass(),
            'token' => $appleToken->getToken(),
        ]);
    }
}
