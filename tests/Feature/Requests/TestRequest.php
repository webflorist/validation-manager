<?php

namespace ValidationManagerTests\Feature\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Webflorist\ValidationManager\Traits\ValidationManagerTrait;
use Illuminate\Http\JsonResponse;

class TestRequest extends FormRequest
{
    
    use ValidationManagerTrait;
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function getRules()
    {
        $rules = [
            'iAlwaysFail' => 'alwaysfails',
            'iShouldStartAndEndWithADot' => 'startsandendswithdot',
            'iAmBeingSubvalidated' => 'usessubvalidation'
        ];

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            new JsonResponse($validator->errors()->messages())
        );
    }
}
