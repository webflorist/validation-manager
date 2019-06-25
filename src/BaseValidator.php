<?php

namespace Webflorist\ValidationManager;

use Illuminate\Validation\Validator;

abstract class BaseValidator
{

    /**
     * @var ValidationManager
     */
    protected $ValidationManager;

    /**
     * @var Validator
     */
    protected $illuminateValidator;

    /**
     * BaseValidator constructor.
     * @param ValidationManager $ValidationManager
     */
    public function __construct(ValidationManager $ValidationManager)
    {
        $this->ValidationManager = $ValidationManager;
    }

    /**
     * The generated error-message.
     * Can be set via the setErrorMessage-method inside the validate-method.
     *
     * @var null|string
     */
    protected $errorMessage = null;

    /**
     * Main validation method.
     * Overwrite this method and perform validation here.
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function validate($attribute, $value, $parameters)
    {
        return true;
    }

    /**
     * Needed for Laravel to extract the error message via Validator::replacer(),
     * which is called within ValidationServiceProvider.
     *
     * @param $message
     * @param $attribute
     * @param $rule
     * @param $parameters
     * @return null
     */
    public function replace($message, $attribute, $rule, $parameters)
    {

        if ($this->errorMessage !== null) {
            $message = $this->errorMessage;
        }

        return $message;
    }

    /**
     * @param Validator $illuminateValidator
     */
    public function setIlluminateValidator(Validator $illuminateValidator)
    {
        $this->illuminateValidator = $illuminateValidator;
    }

    /**
     * Sets the error Message, that should be sent to the client.
     * Can/Should be used inside the validate-method.
     *
     * @param string $message
     */
    protected function setErrorMessage($message = '')
    {
        $this->errorMessage = $message;
    }

    /**
     * With this method a sub-validator can be used from within the main validator.
     * If the sub-validator generates an error-message, it is set as the error message
     * within the main validator.
     *
     * Example (to validate the PDO of a domain within the DomainNameValidator using the DomainPdoValidator):
     * $isValid = $this->performSubValidation('domainpdo', Fields::$domainPdo, $domainPdo);
     *
     * @param string $validatorRule
     * @param string $attribute
     * @param string $value
     * @param array $parameters
     * @return bool
     */
    protected function performSubValidation($validatorRule = '', $attribute = 'key', $value = '', $parameters = [])
    {

        $isValid = true;

        // The parameters are passed as an array, but for creating the laravel-validator,
        // we must append it to the rule as a comma-separated string preceded by a colon.
        if (count($parameters) > 0) {
            $validatorRule .= ':' . implode(',', $parameters);
        }

        // We make an instance of the wished validator-rule.
        $validator = $this->ValidationManager->createValidator(
            [$attribute => $value],
            [
                $attribute => $validatorRule,
            ]
        );

        // If it fails, ...
        if ($validator->fails()) {

            // ... we "steal" the error message from it ...
            $this->setErrorMessage($validator->getMessageBag()->first());

            // ... and also return a false in this case.
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Sets an error for a specific field within the calling illuminate-validator.
     * If $field is part of an array, you must use the dot-notation (e.g. "domainList.1.domainName).
     *
     * @param string $field
     * @param string $error
     */
    protected function setError4Field($field, $error)
    {
        $this->illuminateValidator->getMessageBag()->add($field, $error);
    }

    /**
     * Sets multiple errors for a specific field within the calling illuminate-validator.
     * If $field is part of an array, you must use the dot-notation (e.g. "domainList.1.domainName).
     *
     * @param string $field
     * @param array $errors
     */
    protected function setErrors4Field($field, $errors)
    {
        foreach ($errors as $error) {
            $this->setError4Field($field, $error);
        }
    }

}