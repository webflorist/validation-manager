<?php

namespace ValidationManagerTests\Feature;

use ValidationManagerTests\TestCase;
use ValidationManagerTests\Feature\Validators\AlwaysFailsValidator;
use Webflorist\ValidationManager\Exceptions\AttributeAlreadyRegistered;
use Webflorist\ValidationManager\Exceptions\MessageAlreadyRegistered;
use Webflorist\ValidationManager\Exceptions\RuleAlreadyInUseException;
use Webflorist\ValidationManager\ValidationManager;

class ExceptionsTest extends TestCase
{

    public function testRuleAlreadyRegistered()
    {
        $this->expectException(RuleAlreadyInUseException::class);
        $this->expectExceptionMessage('Rule "alwaysfails" is already in use as: ValidationManagerTests\Feature\Validators\AlwaysFailsValidator');
        app()[ValidationManager::class]->registerValidator(AlwaysFailsValidator::class);
        app()[ValidationManager::class]->registerValidator(AlwaysFailsValidator::class);

    }

    public function testRuleAlreadyRegisteredFromFolder()
    {
        $this->expectException(RuleAlreadyInUseException::class);
        $this->expectExceptionMessage('Rule "alwaysfails" is already in use as: ValidationManagerTests\Feature\Validators\AlwaysFailsValidator');
        app()[ValidationManager::class]->registerValidator(AlwaysFailsValidator::class);
        app()[ValidationManager::class]->registerValidatorsFromFolder('ValidationManagerTests\Feature\Validators', __DIR__ . '/Validators');
    }

    public function testMessageAlreadyRegistered()
    {
        $this->expectException(MessageAlreadyRegistered::class);
        $this->expectExceptionMessage('Message with key "alwaysfails" is already registered for locale "en" with this text: "Thou hast failed again!"');
        app()[ValidationManager::class]->registerMessage('alwaysfails','Thou hast failed again!');
        app()[ValidationManager::class]->registerMessage('alwaysfails','Thou hast failed again!');
    }

    public function testMessageAlreadyRegisteredFromLanguageFile()
    {
        $this->expectException(MessageAlreadyRegistered::class);
        $this->expectExceptionMessage('Message with key "alwaysfails" is already registered for locale "en" with this text: "Thou hast failed again!"');
        app()[ValidationManager::class]->registerMessage('alwaysfails','Thou hast failed again!');
        app()[ValidationManager::class]->registerMessagesFromLanguageFile('messages',"ValidationManagerTests");

    }

    public function testAttributeAlreadyRegistered()
    {
        $this->expectException(AttributeAlreadyRegistered::class);
        $this->expectExceptionMessage('Attribute "iAlwaysFail" is already registered for locale "en" with value "Always Failing"');
        app()[ValidationManager::class]->registerAttribute('iAlwaysFail','Always Failing');
        app()[ValidationManager::class]->registerAttribute('iAlwaysFail','Always Failing');
    }


}
