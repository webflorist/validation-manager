<?php

namespace Webflorist\ValidationManager;

use App;
use Illuminate\Support\Str;
use ReflectionClass;
use Validator;
use Webflorist\ValidationManager\Exceptions\AttributeAlreadyRegistered;
use Webflorist\ValidationManager\Exceptions\MessageAlreadyRegistered;
use Webflorist\ValidationManager\Exceptions\RuleAlreadyInUseException;

class ValidationManager
{

    /**
     * Multidimensional array with locales as the first and attributes as the second dimension.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Multidimensional array with locales as the first and messages as the second dimension.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Registered rulesets.
     *
     * @var array
     */
    protected $registeredRules = [];

    /**
     * ValidationManager constructor.
     */
    public function __construct()
    {
        foreach ($this->getLaravelRules() as $key => $rule) {
            $this->registeredRules[$rule] = 'Built-in Laravel rule';
        }

        // Create empty arrays for attributes an messages for all registered locales.
        foreach (config('validation-manager.locales') as $locale) {
            $this->attributes[$locale] = [];
            $this->messages[$locale] = [];
        }
    }

    /**
     * Register a Validator.
     * The full class name (incl. namespace) must be stated.
     * Validator classes must end with "Validator" (e.g. DomainNameValidator).
     * The corresponding rule is the short class-name of the validator, excl "Validator" and strtolower() (e.g. domainname).
     *
     * @param string $fullClassName
     * @throws RuleAlreadyInUseException
     */
    public function registerValidator($fullClassName)
    {

        // Bind the validator as a singleton.
        app()->singleton($fullClassName, function () use ($fullClassName) {
            return new $fullClassName(app()[ValidationManager::class]);
        });

        // The rule-name is the name-of the validator-class excl. the namespace and excl "Validator" and strtolower().
        $ruleName = strtolower(substr($fullClassName, strrpos($fullClassName, '\\') + 1, -9));

        // Check, if the rule is already in use and throw exception, if it is.
        $registeredRules = $this->getRegisteredRules();
        if (isset($registeredRules[$ruleName])) {
            throw new RuleAlreadyInUseException('Rule "' . $ruleName . '" is already in use as: ' . $registeredRules[$ruleName]);
        }

        // Register the validation-rule.
        Validator::extend($ruleName, function ($attribute, $value, $parameters, $validator) use ($fullClassName) {

            // Set the calling Illuminate\Validation\Validator instance, so the validator can manipulate it.
            app($fullClassName)->setIlluminateValidator($validator);

            return app($fullClassName)->validate($attribute, $value, $parameters);

        });

        // Register the replacer.
        Validator::replacer($ruleName, function ($message, $attribute, $rule, $parameters) use ($fullClassName) {
            return app($fullClassName)->replace($message, $attribute, $rule, $parameters);
        });

        // Add rule to $this->registeredRules
        $this->registeredRules[$ruleName] = $fullClassName;
    }

    /**
     * Register validators from the files of a directory.
     * Files inside $dir must resemble the class-name.
     * Namespace of these validator-classes must be stated via $namespace.
     *
     * @param string $namespace
     * @param string $dir
     * @throws RuleAlreadyInUseException
     */
    public function registerValidatorsFromFolder($namespace, $dir)
    {

        $validatorFiles = scandir($dir);

        foreach ($validatorFiles as $key => $validatorFileName) {
            if (strpos($validatorFileName, "php") !== false) {
                $this->registerValidator($namespace . "\\" . substr($validatorFileName, 0, -4));
            }
        }

    }

    /**
     * Registers an attributes and their human readable translations for a locale (uses current locale, if omitted)
     * to be used in error messages or other functionality.
     *
     * @param string $key
     * @param string $value
     * @param null $locale
     * @throws AttributeAlreadyRegistered
     */
    public function registerAttribute($key, $value, $locale = null)
    {

        // Fill $locale with currently set locale, if it is null.
        $this->establishLocale($locale);

        // Check, if the attribute is already registered and throw exception, if it is.
        if (isset($this->attributes[$locale][$key])) {
            throw new AttributeAlreadyRegistered('Attribute "' . $key . '" is already registered for locale "' . $locale . '" with value "' . $this->attributes[$locale][$key] . '"');
        }

        $this->attributes[$locale][$key] = $value;
    }

    /**
     * Registers attributes from an array (typically coming from a language file) for a locale (uses current locale, if omitted).
     *
     * @param array $attributes
     * @param null $locale
     * @throws AttributeAlreadyRegistered
     */
    public function registerAttributes($attributes, $locale = null)
    {
        foreach ($attributes as $key => $value) {
            $this->registerAttribute($key, $value, $locale);
        }
    }

    /**
     * Registers the attributes from a translation ID (e.g. 'validation.attributes' or 'myNamespace::attributes).
     *
     * @param string $id
     * @throws AttributeAlreadyRegistered
     */
    public function registerAttributesTranslationId($id)
    {
        foreach (config('validation-manager.locales') as $locale) {
            $attributes = trans($id, [], $locale);
            if (is_array($attributes)) {
                $this->registerAttributes($attributes, $locale);
            }
        }
    }

    /**
     * Return all registered attributes for a locale (uses current locale, if omitted).
     *
     * @param null $locale
     * @param bool $autoLowercase : If true, all attribute-values are automatically strtolower()ed, if locale is 'en'.
     * @return array
     */
    public function getAttributes($locale = null, $autoLowercase = false)
    {

        // Fill $locale with currently set locale, if it is null.
        $this->establishLocale($locale);

        $attributes = $this->attributes[$locale];

        if ($autoLowercase) {

            //If current locale is 'en', we strtolower() the attribute-values, since they are used inside error messages.
            $lowercase = false;
            if (App::getLocale() === 'en') {
                $lowercase = true;
            }

            if ($lowercase) {
                foreach ($attributes as $attributeKey => $attributeValue) {
                    $attributes[$attributeKey] = strtolower($attributeValue);
                }
            }
        }

        return $attributes;
    }

    /**
     * Is a particular attribute defined for a particular locale (uses current locale, if omitted)?
     *
     * @param string $attribute
     * @param null $locale
     * @return bool
     */
    public function hasAttribute($attribute, $locale = null)
    {

        // Fill $locale with currently set locale, if it is null.
        $this->establishLocale($locale);

        return isset($this->attributes[$locale][$attribute]);
    }

    /**
     * Return a particular attribute for a particular locale (uses current locale, if omitted).
     *
     * @param string $attribute
     * @param null $locale
     * @param string $fallbackValue
     * @return string
     */
    public function getAttribute($attribute, $locale = null, $fallbackValue = '')
    {

        // Fill $locale with currently set locale, if it is null.
        $this->establishLocale($locale);

        $return = $fallbackValue;
        if ($this->hasAttribute($attribute, $locale)) {
            $return = $this->attributes[$locale][$attribute];
        }
        return $return;
    }

    /**
     * Registers an error message for a validator for a locale (uses current locale, if omitted).
     * The key is the rule corresponding to the validator
     * (e.g. the validator-class "DomainNameValidator" has the rule "domainname").
     * The value is the actual error message.
     *
     * @param string $key
     * @param string $value
     * @param null $locale
     * @throws MessageAlreadyRegistered
     */
    public function registerMessage($key, $value, $locale = null)
    {

        // Fill $locale with currently set locale, if it is null.
        $this->establishLocale($locale);

        // Check, if the message is already registered and throw exception, if it is.
        if (isset($this->messages[$locale][$key])) {
            throw new MessageAlreadyRegistered('Message with key "' . $key . '" is already registered for locale "' . $locale . '" with this text: "' . $this->messages[$locale][$key] . '"');
        }

        $this->messages[$locale][$key] = $value;
    }

    /**
     * Registers messages from an array (typically coming from a language file) for a locale (uses current locale, if omitted).
     *
     * @param array $messages
     * @throws MessageAlreadyRegistered
     */
    public function registerMessages($messages, $locale = null)
    {
        foreach ($messages as $key => $value) {
            $this->registerMessage($key, $value, $locale);
        }
    }

    /**
     * Registers (error-)messages from a translation ID (e.g. 'validation.errors' or 'myNamespace::errors).
     *
     * @param string $id
     * @throws MessageAlreadyRegistered
     */
    public function registerMessagesTranslationId($id)
    {
        foreach (config('validation-manager.locales') as $locale) {
            $this->registerMessages(trans($id, [], $locale), $locale);
        }
    }

    /**
     * Registers messages directly from a language-file stated via it's group and an optional namespace.
     *
     * @param string $group
     * @param null $namespace
     * @throws MessageAlreadyRegistered
     */
    public function registerMessagesFromLanguageFile($group = '', $namespace = null)
    {
        $messages = app('translator')->getLoader()->load(app('translator')->getLocale(), $group, $namespace);
        $this->registerMessages($messages);

    }

    /**
     * Return all registered messages for a locale (uses current locale, if omitted).
     *
     * @param null $locale
     * @return array
     */
    public function getMessages($locale = null)
    {

        // Fill $locale with currently set locale, if it is null.
        $this->establishLocale($locale);

        return $this->messages[$locale];
    }

    /**
     * Is a particular rule-message defined for a particular locale (uses current locale, if omitted)?
     *
     * @param string $rule
     * @param null $locale
     * @return bool
     */
    public function hasMessage($rule, $locale = null)
    {

        // Fill $locale with currently set locale, if it is null.
        $this->establishLocale($locale);

        return isset($this->messages[$locale][$rule]);
    }

    /**
     * Return a particular rule-message for a particular locale (uses current locale, if omitted).
     *
     * @param string $rule
     * @param null $locale
     * @return string
     */
    public function getMessage($rule, $locale = null)
    {

        // Fill $locale with currently set locale, if it is null.
        $this->establishLocale($locale);

        if ($this->hasMessage($rule, $locale)) {
            return $this->messages[$locale][$rule];
        }
        return false;
    }

    /**
     * Creates and returns a laravel-validator-instance.
     *
     * Usage example:
     *
     * app()[ValidationManager::class]->createValidator(
     * [
     * \Fields::$domainName    => 'mydomain.at',
     * 'someOtherField'        => 'myValue'
     * ],
     * [
     * \Fields::$domainName    => 'domainname',
     * 'someOtherField'        => 'integer|min:5'
     * ],
     * );
     *
     * This would validate the domain name 'mydomain.at' against the custom DomainNameValidator,
     * and the second field 'myValue' against the laravel built-in validators 'integer' and 'min'.
     *
     * @param array $data
     * @param array $rules
     * @return \Illuminate\Validation\Validator
     */
    public function createValidator($data = [], $rules = [])
    {

        // We make an instance of the wished validator-rule,
        // using the nic.at translation files for messages and field names.
        return Validator::make(
            $data,
            $rules,
            $this->getMessages(),
            $this->getAttributes(null, true)
        );

    }

    /**
     * Creates and returns a laravel-validator-instance for a single field.
     *
     * Usage example:
     *
     * app()[ValidationManager::class]->createValidator4Field(\Fields::$domainName,'mydomain.at','domainname')
     *
     * This would validate the value "mydomain.at" against the DomainNameValidator
     *
     * @param string $attribute
     * @param string $value
     * @param string $rules
     * @return \Illuminate\Validation\Validator
     */
    public function createValidator4Field($attribute = '', $value = '', $rules = '')
    {

        return $this->createValidator(
            [$attribute => $value],
            [$attribute => $rules]
        );

    }

    /**
     * Get global rules (set via the validation-manager-config) and merge them with already defined rules.
     * If there are duplicate keys, we let the already defined ones take precedence.
     * This way global rules can be overridden with specific ones.
     *
     * @param array $rules
     */
    public function mergeGlobalRules(&$rules = [])
    {
        $rules = array_merge(config('validation-manager.global_rules'), $rules);
    }

    /**
     * Get the rules registered under $this->registeredRules.
     *
     * @return array
     */
    private function getRegisteredRules()
    {
        return $this->registeredRules;
    }

    /**
     * Get all built-in laravel-rules.
     *
     * @return array
     * @throws \ReflectionException
     */
    private function getLaravelRules()
    {
        $validator = Validator::make(array(), array());

        $r = new ReflectionClass($validator);
        $methods = $r->getMethods();


        $methods = array_filter($methods, function ($v) {
            if ($v->name == 'validate') {
                return false;
            }
            return strpos($v->name, 'validate') === 0;
        });


        $rules = array_map(function ($v) {
            $value = preg_replace('%^validate%', '', $v->name);
            $value = Str::snake($value);
            return Str::snake($value);
        }, $methods);

        return $rules;
    }

    /**
     * Fills $locale with currently set locale, if it is null.
     *
     * @param $locale
     */
    private function establishLocale(&$locale)
    {
        if (is_null($locale)) {
            $locale = App::getLocale();
        }
    }

}