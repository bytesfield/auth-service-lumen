<?php

use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Faker\Factory;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    protected $faker;

    public function setUp() :void {
        parent::setUp();
        $this->faker = Factory::create();
    }
}
