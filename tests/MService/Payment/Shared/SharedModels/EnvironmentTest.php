<?php

namespace MService\Payment\Shared\SharedModels;

include_once "../../../../../loader.php";

use MService\Payment\Shared\Utils\MoMoException;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{

    public function testDevEnv()
    {
        $env = Environment::selectEnv('dev');

        $this->assertEquals('development', $env->getTarget(), 'Wrong Target for Development Environment');
        $this->assertEquals("https://test-payment.momo.vn", $env->getMomoEndpoint(), 'Wrong MoMo Endpoint for Development Environment');
    }

    public function testProdEnv()
    {
        $env = Environment::selectEnv('prod');

        $this->assertEquals('production', $env->getTarget(), 'Wrong Target for Production Environment');
        $this->assertEquals("https://payment.momo.vn", $env->getMomoEndpoint(), 'Wrong MoMo Endpoint for Production Environment');
    }

    public function testThrowException() {
        $this->expectExceptionMessage('MoMo doesnt provide other environment: dev and prod');
        Environment::selectEnv('random');
    }
}
