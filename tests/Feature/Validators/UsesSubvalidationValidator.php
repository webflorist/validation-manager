<?php

namespace ValidationManagerTests\Feature\Validators;

use Webflorist\ValidationManager\BaseValidator;

class UsesSubvalidationValidator extends BaseValidator
{

    public function validate($attribute, $value, $parameters) {
        return $this->performSubValidation('startsandendswithdot', $attribute, $value, $parameters);
    }

}