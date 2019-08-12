<?php

namespace Webflorist\ValidationManager\Traits;

use Request;
use Webflorist\ValidationManager\ValidationManager;

/**
 * Attach this trait to the base FormRequest class.
 *
 * This trait adds some additional functionality to FormRequest-validation.
 *
 */
trait ValidationManagerTrait
{

    /**
     * Set custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        // Return all messages, that were registered with the ValidationManager service.
        return app()[ValidationManager::class]->getMessages();
    }

    /**
     * Set custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {

        // Get all attributes, that were registered with the ValidationManager service.
        $attributes = app()[ValidationManager::class]->getAttributes(null, true);

        // Parse the registered rules for array-field-names (e.g. "domainList.*.domainName" or "domainName.*")
        // and try adding correct attribute translations (e.g. "domainName" in these examples) to $attributes.
        $this->processArrayAttributes($attributes);

        return $attributes;

    }

    /**
     * Override this in the FormRequest-Child and use this function (instead of rules()) to deliver the rules.
     *
     * @return array
     */
    public abstract function getRules();

    /**
     * Provides some additional functionality on top of laravel-base-features.
     *
     * Overrides the rules() function inside the FormRequest-Child,
     * which must use getRules() to deliver the rules instead.
     *
     * @return array
     */
    public function rules()
    {

        $rules = $this->getRules();

        app()[ValidationManager::class]->mergeGlobalRules($rules);

        return $rules;
    }

    /**
     * Parses the registered rules for array-field-names (e.g. "domainList.*.domainName" or "domainName.*")
     * and tries adding correct attribute translations (e.g. "domainName" in these examples) to $attributes.
     *
     * @param $attributes
     */
    private function processArrayAttributes(&$attributes)
    {
        foreach ($this->rules() as $fieldName => $rule) {

            // Only process field names with array-dividers.
            if (strpos($fieldName, '.') > 0) {

                // Explode the field-name via the array-divider character '.'.
                $explodedFieldName = explode('.', $fieldName);

                // Find out attribute to use.
                // Array fields might come in 2 possible variants:
                // - The wanted attribute is the last element (e.g. [...]domainList.*.domainName)
                // - The wanted attribute is the last but one element (e.g. [...]domainName.*)
                // (In either case of the examples, the attribute should be 'domainName'.

                // In case of [...]domainList.*.domainName, the last element of $explodedFieldName is the attribute we want.
                $attribute = array_pop($explodedFieldName);

                // In case of [...]domainName.*, the one before the last is the attribute we want.
                if (is_numeric($attribute)) {
                    $attribute = array_pop($explodedFieldName);
                }

                // Finally, if a translation for $attribute exits, we add the array-field-name with that translation.
                if (isset($attributes[$attribute])) {
                    $attributes[$fieldName] = $attributes[$attribute];
                }
            }
        }
    }

}
