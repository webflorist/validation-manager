<?php

namespace Webflorist\ValidationManager;

use Illuminate\Support\ServiceProvider;
use Request;
use Validator;
use Webflorist\ValidationManager\RuleSets\RuleSets;

class ValidationManagerServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     * @param ValidationManager $ValidationManager
     * @throws Exceptions\MessageAlreadyRegistered
     * @throws Exceptions\AttributeAlreadyRegistered
     */
    public function boot(ValidationManager $ValidationManager)
    {

        // Publish the config.
        $this->publishes([
            __DIR__ . '/config/validation-manager.php' => config_path('validation-manager.php'),
        ]);

        // Load default translations.
        $this->loadTranslationsFrom(__DIR__ . "/resources/lang", "Webflorist-ValidationManager");

        // Register default error messages.
        $ValidationManager->registerMessagesTranslationId('Webflorist-ValidationManager::messages');

        // Register default attributes.
        $ValidationManager->registerAttributesTranslationId('Webflorist-ValidationManager::attributes');

        // Replacer for error-messages of regex-validator to include the invalid characters.
        Validator::replacer('regex', function ($message, $attribute, $rule, $parameters) {

            // Get the actual submitted value from the request.
            $value = Request::get($attribute);

            // Get string of invalid characters.
            $invalidChars = mb_ereg_replace("[" . $parameters[0] . "]", '', $value);

            // Split them into an array.
            $invalidChars = preg_split('//u', $invalidChars, -1, PREG_SPLIT_NO_EMPTY);

            // Eliminate multiple occurrences.
            $invalidChars = array_unique($invalidChars);

            // Retrieve default-template.
            $message = validation_manager()->getMessage('regex');

            // If invalid chars were found, we use different template.
            if (count($invalidChars) > 0) {
                $message = validation_manager()->getMessage('regex_invalid_chars');
            }

            // Perform replacements.
            $message = str_replace(':attribute', validation_manager()->getAttribute($attribute), $message);
            $message = str_replace(':invalid_chars', implode(',', $invalidChars), $message);

            return $message;
        });

        // Replacer for error-messages of required_if-validator to properly translate the :value.
        Validator::replacer('required_if', function ($message, $attribute, $rule, $parameters) {

            // Perform replacements.
            $message = str_replace(':other', validation_manager()->getAttribute($parameters[0]), $message);
            $message = str_replace(':value', validation_manager()->getAttribute($parameters[0] . '_' . $parameters[1]), $message);

            return $message;
        });

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        // Merge the config.
        $this->mergeConfigFrom(__DIR__ . '/config/validation-manager.php', 'validation-manager');

        // Register the ValidationManager singleton.
        $this->app->singleton(ValidationManager::class, function () {
            return new ValidationManager();
        });

        // Register the RuleSets singleton.
        $this->app->singleton(RuleSets::class, function () {
            return new RuleSets();
        });

    }

}