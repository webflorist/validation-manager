<?php

namespace Webflorist\ValidationManager\RuleSets;

use Webflorist\ValidationManager\Exceptions\RuleSetAlreadyDefined;
use Webflorist\ValidationManager\Exceptions\RuleSetNotDefined;

class RuleSets
{

    protected $ruleSets = [];

    /**
     * Sets a singular RuleSet
     *
     * @param string $key
     * @param string $rules
     * @throws RuleSetAlreadyDefined
     * @throws RuleSetNotDefined
     */
    protected function setRule($key = '', $rules = '')
    {
        if (isset($this->ruleSets[$key])) {
            throw new RuleSetAlreadyDefined('Ruleset with key "' . $key . '" is already defined with these rules: "' . $this->get($key) . '"');
        } else {
            $this->ruleSets[$key] = $rules;
        }
    }

    /**
     * Define RuleSets via an array.
     * e.g. ['key1' => 'rules1', 'key2' => 'rules2']
     *
     * @param array $definitions
     * @throws RuleSetAlreadyDefined
     * @throws RuleSetNotDefined
     */
    public function set($definitions = [])
    {
        foreach ($definitions as $ruleKey => $rules) {
            $this->setRule($ruleKey, $rules);
        }
    }

    /**
     * Get RuleSet defined for a key
     *
     * @param string $key
     * @return mixed
     * @throws RuleSetNotDefined
     */
    public function get($key = '')
    {
        if (!isset($this->ruleSets[$key])) {
            throw new RuleSetNotDefined('Ruleset with key "' . $key . '" is not defined.');
        } else {
            return $this->ruleSets[$key];
        }
    }

}