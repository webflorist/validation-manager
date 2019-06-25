<?php

namespace ValidationManagerTests\Feature\Validators;

use Webflorist\ValidationManager\BaseValidator;

class StartsAndEndsWithDotValidator extends BaseValidator
{

    public function validate($attribute, $value, $parameters) {

        $isValid = true;
        
        if ($isValid) {
            $isValid = $this->checkStartsWithDot($value);
        }


        if ($isValid) {
            $isValid = $this->checkSEndsWithDot($value);
        }

        return $isValid;

    }
    
    function checkStartsWithDot($value) {
        if (substr($value,0,1) !== '.') {
            $this->setErrorMessage('Value must start with dot.');
            return false;
        }
        return true;
    }

    function checkSEndsWithDot($value) {
        if (substr($value,-1) !== '.') {
            $this->setErrorMessage('Value must end with dot.');
            return false;
        }
        return true;
    }

}