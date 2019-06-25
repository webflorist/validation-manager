<?php

namespace ValidationManagerTests;

use ValidationManagerTests\Feature\Controllers\TestController;
use Webflorist\ValidationManager\RuleSets\RuleSetsFacade;
use Webflorist\ValidationManager\ValidationManagerServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    protected $testRoute = 'test';

    protected $testConfig = [

        // Global Rules
        'global_rules' => [],

        'locales' => [
            'de',
            'en'
        ]
    ];

    protected function getPackageProviders($app)
    {
        return [ValidationManagerServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'RuleSets'  => RuleSetsFacade::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {

        // Add Translations
        $app['translator']->addNamespace('ValidationManagerTests', __DIR__ . "/Feature/lang");
	
        // Set Config
        $app['config']->set('validation-manager', $this->testConfig);

        // Set Test-Route
        $app['router']->get($this->testRoute, ['uses' => TestController::class.'@test']);

    }

}
