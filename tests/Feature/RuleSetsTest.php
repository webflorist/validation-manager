<?php

namespace ValidationManagerTests\Feature;

use ValidationManagerTests\TestCase;
use Webflorist\ValidationManager\Exceptions\RuleSetAlreadyDefined;
use Webflorist\ValidationManager\Exceptions\RuleSetNotDefined;

class RuleSetsTest extends TestCase
{

    public function testSetAndGetRuleSet()
    {
        \RuleSets::set([
            'myRuleSetKey'=>'my|rules'
        ]);

        $this->assertEquals(
            'my|rules',
            \RuleSets::get('myRuleSetKey')
        );
    }

    public function testSetAlreadyDefinedRule()
    {
        $this->expectException(RuleSetAlreadyDefined::class);
        $this->expectExceptionMessage('Ruleset with key "myRuleSetKey" is already defined with these rules: "my|rules"');
        \RuleSets::set([
            'myRuleSetKey'=>'my|rules'
        ]);

        \RuleSets::set([
            'myRuleSetKey'=>'my|rules'
        ]);
    }

    public function testGetUndefinedRule()
    {
        $this->expectException(RuleSetNotDefined::class);
        $this->expectExceptionMessage('Ruleset with key "iAmNotDefined" is not defined.');
        \RuleSets::get('iAmNotDefined');
    }

}
