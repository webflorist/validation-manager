<?php

use Webflorist\ValidationManager\RuleSets\RuleSets;
use Webflorist\ValidationManager\ValidationManager;

if (!function_exists('validation_manager')) {
    /**
     * Get the available ValidationManager instance.
     *
     * @return ValidationManager
     */
    function validation_manager()
    {
        return app(ValidationManager::class);
    }
}

if (!function_exists('rule_sets')) {
    /**
     * Get the available RuleSets instance.
     *
     * @return RuleSets
     */
    function rule_sets()
    {
        return app(RuleSets::class);
    }
}