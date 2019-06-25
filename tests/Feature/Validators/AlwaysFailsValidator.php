<?php

namespace ValidationManagerTests\Feature\Validators;

use Webflorist\ValidationManager\BaseValidator;

class AlwaysFailsValidator extends BaseValidator
{

    public function validate($attribute, $value, $parameters)
    {
        return false;
    }

}