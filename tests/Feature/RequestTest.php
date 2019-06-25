<?php

namespace ValidationManagerTests\Feature;

use ValidationManagerTests\TestCase;
use ValidationManagerTests\Feature\Validators\AlwaysFailsValidator;
use ValidationManagerTests\Feature\Validators\StartsAndEndsWithDotValidator;
use ValidationManagerTests\Feature\Validators\UsesSubvalidationValidator;
use Webflorist\ValidationManager\ValidationManager;

class RequestTest extends TestCase
{

    public function testValidator()
    {
        app()[ValidationManager::class]->registerValidator(AlwaysFailsValidator::class);
        $this->get($this->testRoute.'?iAlwaysFail=value')->assertSee('validation.alwaysfails');
    }


    public function testValidatorRegisteredFromFolder()
    {
        app()[ValidationManager::class]->registerValidatorsFromFolder('ValidationManagerTests\Feature\Validators', __DIR__ . '/Validators');
        $this->get($this->testRoute.'?iAlwaysFail=value')->assertSee('validation.alwaysfails');
    }


    public function testDefaultErrorMessage()
    {
        app()[ValidationManager::class]->registerValidator(AlwaysFailsValidator::class);
        app()[ValidationManager::class]->registerMessage('alwaysfails','Thou hast failed again!');
        $this->get($this->testRoute.'?iAlwaysFail=value')->assertSee('Thou hast failed again!');
    }

    public function testDefaultErrorMessageRegisteredViaLanguageFile()
    {
        app()[ValidationManager::class]->registerValidator(AlwaysFailsValidator::class);
        app()[ValidationManager::class]->registerMessagesFromLanguageFile('messages',"ValidationManagerTests");
        $this->assertEquals(
            'The field "i always fail" is doomed to always fail.',
            json_decode($this->get($this->testRoute.'?iAlwaysFail=value')->baseResponse->getContent())->iAlwaysFail[0]
        );
    }

    public function testDefaultErrorMessageWithAttribute()
    {
        app()[ValidationManager::class]->registerValidator(AlwaysFailsValidator::class);
        app()[ValidationManager::class]->registerMessage('alwaysfails','The field ":attribute" has failed again!');
        app()[ValidationManager::class]->registerAttribute('iAlwaysFail','Always failing');
        $this->assertEquals(
            'The field "always failing" has failed again!',
            json_decode($this->get($this->testRoute.'?iAlwaysFail=value')->baseResponse->getContent())->iAlwaysFail[0]
        );
    }

    public function testDefaultErrorMessageWithAttributeRegisteredViaLanguageFile()
    {
        app()[ValidationManager::class]->registerValidator(AlwaysFailsValidator::class);
        app()[ValidationManager::class]->registerMessagesFromLanguageFile('messages',"ValidationManagerTests");
        app()[ValidationManager::class]->registerAttributesTranslationId('ValidationManagerTests::attributes');
        $this->assertEquals(
            'The field "always failing" is doomed to always fail.',
            json_decode($this->get($this->testRoute.'?iAlwaysFail=value')->baseResponse->getContent())->iAlwaysFail[0]
        );
    }

    public function testCustomErrorMessage1()
    {
        app()[ValidationManager::class]->registerValidator(StartsAndEndsWithDotValidator::class);
        $this->assertEquals(
            'Value must start with dot.',
            json_decode($this->get($this->testRoute.'?iShouldStartAndEndWithADot=I Have no dot!')->baseResponse->getContent())->iShouldStartAndEndWithADot[0]
        );
    }

    public function testCustomErrorMessage2()
    {
        app()[ValidationManager::class]->registerValidator(StartsAndEndsWithDotValidator::class);
        $this->assertEquals(
            'Value must end with dot.',
            json_decode($this->get($this->testRoute.'?iShouldStartAndEndWithADot=. I do not end with a dot!')->baseResponse->getContent())->iShouldStartAndEndWithADot[0]
        );
    }

    public function testSubValidation()
    {
        app()[ValidationManager::class]->registerValidator(StartsAndEndsWithDotValidator::class);
        app()[ValidationManager::class]->registerValidator(UsesSubvalidationValidator::class);
        $this->assertEquals(
            'Value must start with dot.',
            json_decode($this->get($this->testRoute.'?iAmBeingSubvalidated=I Have no dot!')->baseResponse->getContent())->iAmBeingSubvalidated[0]
        );
    }


}
