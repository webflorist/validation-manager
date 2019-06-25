<?php

namespace ValidationManagerTests\Feature;

use ValidationManagerTests\Feature\Validators\AlwaysFailsValidator;
use ValidationManagerTests\Feature\Validators\StartsAndEndsWithDotValidator;
use ValidationManagerTests\TestCase;
use Webflorist\ValidationManager\ValidationManager;

class GlobalRulesTest extends TestCase
{

    protected $testConfig = [

        // Global Rules
        'global_rules' => ['iAmAGlobalRuleField' => 'alwaysfails'],

        'locales' => [
            'de',
            'en'
        ]
    ];

    public function testGlobalRule()
    {
        app()[ValidationManager::class]->registerValidator(AlwaysFailsValidator::class);
        app()[ValidationManager::class]->registerValidator(StartsAndEndsWithDotValidator::class);

        $this->assertEquals(
            'validation.alwaysfails',
            json_decode($this->get($this->testRoute . '?iShouldStartAndEndWithADot=.value.&iAmAGlobalRuleField=value')->baseResponse->getContent())->iAmAGlobalRuleField[0]
        );
    }


}
