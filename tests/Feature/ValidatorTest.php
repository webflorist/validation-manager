<?php

namespace ValidationManagerTests\Feature;

use ValidationManagerTests\Feature\Validators\AlwaysFailsValidator;
use ValidationManagerTests\Feature\Validators\StartsAndEndsWithDotValidator;
use ValidationManagerTests\Feature\Validators\UsesSubvalidationValidator;
use ValidationManagerTests\TestCase;
use Webflorist\ValidationManager\ValidationManager;

class ValidatorTest extends TestCase
{

    public function testValidator()
    {
        app()[ValidationManager::class]->registerValidator(AlwaysFailsValidator::class);

        $validator = app()[ValidationManager::class]->createValidator4Field(
            'iAlwaysFail',
            "fieldValue",
            'alwaysfails'
        );

        $this->assertEquals(
            'validation.alwaysfails',
            $validator->errors()->get('iAlwaysFail')[0]
        );
    }

    public function testValidatorRegisteredFromFolder()
    {
        app()[ValidationManager::class]->registerValidatorsFromFolder('ValidationManagerTests\Feature\Validators', __DIR__ . '/Validators');

        $validator = app()[ValidationManager::class]->createValidator4Field(
            'iAlwaysFail',
            "fieldValue",
            'alwaysfails'
        );

        $this->assertEquals(
            'validation.alwaysfails',
            $validator->errors()->get('iAlwaysFail')[0]
        );
    }


    public function testDefaultErrorMessage()
    {
        app()[ValidationManager::class]->registerValidator(AlwaysFailsValidator::class);
        app()[ValidationManager::class]->registerMessage('alwaysfails', 'Thou hast failed again!');

        $validator = app()[ValidationManager::class]->createValidator4Field(
            'iAlwaysFail',
            "fieldValue",
            'alwaysfails'
        );

        $this->assertEquals(
            'Thou hast failed again!',
            $validator->errors()->get('iAlwaysFail')[0]
        );
    }

    public function testDefaultErrorMessageRegisteredViaLanguageFile()
    {
        app()[ValidationManager::class]->registerValidator(AlwaysFailsValidator::class);
        app()[ValidationManager::class]->registerMessagesFromLanguageFile('messages', "ValidationManagerTests");

        $validator = app()[ValidationManager::class]->createValidator4Field(
            'iAlwaysFail',
            "fieldValue",
            'alwaysfails'
        );

        $this->assertEquals(
            'The field "i always fail" is doomed to always fail.',
            $validator->errors()->get('iAlwaysFail')[0]
        );
    }

    public function testDefaultErrorMessageWithAttribute()
    {
        app()[ValidationManager::class]->registerValidator(AlwaysFailsValidator::class);
        app()[ValidationManager::class]->registerMessage('alwaysfails', 'The field ":attribute" has failed again!');
        app()[ValidationManager::class]->registerAttribute('iAlwaysFail', 'Always Failing');

        $validator = app()[ValidationManager::class]->createValidator4Field(
            'iAlwaysFail',
            "fieldValue",
            'alwaysfails'
        );

        $this->assertEquals(
            'The field "always failing" has failed again!',
            $validator->errors()->get('iAlwaysFail')[0]
        );
    }

    public function testDefaultErrorMessageWithAttributeRegisteredViaLanguageFile()
    {
        app()[ValidationManager::class]->registerValidator(AlwaysFailsValidator::class);
        app()[ValidationManager::class]->registerMessagesFromLanguageFile('messages', "ValidationManagerTests");
        app()[ValidationManager::class]->registerAttributesTranslationId('ValidationManagerTests::attributes');

        $validator = app()[ValidationManager::class]->createValidator4Field(
            'iAlwaysFail',
            "fieldValue",
            'alwaysfails'
        );

        $this->assertEquals(
            'The field "always failing" is doomed to always fail.',
            $validator->errors()->get('iAlwaysFail')[0]
        );
    }

    public function testCustomErrorMessage1()
    {
        app()[ValidationManager::class]->registerValidator(StartsAndEndsWithDotValidator::class);

        $validator = app()[ValidationManager::class]->createValidator4Field(
            'iAlwaysFail',
            "I Have no dot!",
            'startsandendswithdot'
        );

        $this->assertEquals(
            'Value must start with dot.',
            $validator->errors()->get('iAlwaysFail')[0]
        );
    }

    public function testCustomErrorMessage2()
    {
        app()[ValidationManager::class]->registerValidator(StartsAndEndsWithDotValidator::class);

        $validator = app()[ValidationManager::class]->createValidator4Field(
            'iAlwaysFail',
            ". I do not end with a dot!",
            'startsandendswithdot'
        );

        $this->assertEquals(
            'Value must end with dot.',
            $validator->errors()->get('iAlwaysFail')[0]
        );
    }

    public function testSubValidation()
    {
        app()[ValidationManager::class]->registerValidator(StartsAndEndsWithDotValidator::class);
        app()[ValidationManager::class]->registerValidator(UsesSubvalidationValidator::class);

        $validator = app()[ValidationManager::class]->createValidator4Field(
            'iAlwaysFail',
            'I Have no dot!',
            'usessubvalidation'
        );

        $this->assertEquals(
            'Value must start with dot.',
            $validator->errors()->get('iAlwaysFail')[0]
        );
    }

}
