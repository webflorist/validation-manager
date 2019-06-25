<?php

namespace Webflorist\ValidationManager\RuleSets;

use Illuminate\Support\Facades\Facade;

class RuleSetsFacade extends Facade
{

    /**
     * Static access-proxy for Registry-Functions
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return RuleSets::class;
    }

} 