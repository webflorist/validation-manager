# webflorist/validation-manager
**Enhanced validation functionality for Laravel 5.5 and later**

## Description
This package adds extended functionality for validation with the Laravel framework. The main features are:

* Create your own custom validators with the following features:
  * Set a custom error message within the validate-method.
  * Use a different validator as a "sub-validator".
* Use translations of Laravel's built in error messages in 69 languages (using caouecs/Laravel-lang).
* Register your validator error messages and attributes (field names) and their human readable translations to be used inside error messages. Both are then used automatically.
* Use proper attributes (field names) also for array-fields (e.g. fields `domainName[0]` or `domainList[0][domainName]` will automatically use the translation of the field `domainName` inside error messages).
* Configure global rules via a config-file, that are automatically included with each Form Request Object.
* Easily create new laravel-validator instances for a set of fields or a single field, that automatically uses the registered messages, attributes and global rules. 
* Define and retrieve RuleSets via the RuleSets-facade.

## Installation
1. Require the package via composer:  
```php 
composer require webflorist/validation-manager
```
2. Add the Service-Provider to config/app.php:
```php 
Webflorist\ValidationManager\ValidationManagerServiceProvider::class
```
3. Add the RuleSets facade config/app.php:
```php 
'RuleSets'  => Webflorist\ValidationManager\RuleSets\RuleSetsFacade::class
```
4. Publish config (optional):
```
php artisan vendor:publish --provider="Webflorist\ValidationManager\ValidationManagerServiceProvider"
```
5. Use the `ValidationManagerTrait` (`Webflorist\ValidationManager\Traits\ValidationManagerTrait`) in the Base Form Request Object (`/app/Http/Requests/Request.php`)

## Usage

### Accessing the ValidationManager-service
There are several ways to interact with the ValidationManager-service:
* By using the supplied helper-function `validation_manager()`, which returns the ValidationManager-singleton from Laravel's service container.
* By injecting it into your method (e.g. `public function myControllerMethod(Webflorist\ValidationManager\ValidationManager $ValidationManager)`.
* By retrieving the ValidationManager-singleton directly from the service container using `app('Webflorist\ValidationManager\ValidationManager')` or `app()['Webflorist\ValidationManager\ValidationManager']`.

With the first two options, you can benefit from code-completion in your IDE. The following examples will use the `validation_manager()` helper-function.

### Create and register new validators
You can create your own validators anywhere in your application. The validators must conform to the following:

* They must extend `Webflorist\ValidationManager\BaseValidator`.
* Their class names must have the Suffix `Validator` (e.g. `DomainNameValidator`).
* They must overwrite the method `validate($attribute, $value, $parameters)`, with should return a boolean true/false.

Validators must be registered with the ValidationManager-service using one of the following methods:

* Register single validator:
```php
validation_manager()->registerValidator('Full\Class\Of\The\Validator');
```
* Register all validators located in a single folder:
```php
validation_manager()->registerValidatorsFromFolder('Namespace\Of\The\Validators','path/to/validators');
```

The `BaseValidator` object provides the following functionality you can use in your validator:

* A custom error message can be set via the method `setErrorMessage('myCustomErrorMessage')`.
* If you want to a different validator as a `sub-validator`, you can utilize the method `performSubValidation('rule', 'fieldName, 'value')`. Any custom error messages set within that sub-validator gets set within the main validator.
* You can add an error for a specific field via the methods `setError4Field('myField', 'My error message.')` or  `setErrors4Field('myField', ['My first error message.', 'My second error message'])`.

### Register error messages
If a validator returns `false` without setting a custom error message (via `setErrorMessage()`), laravel uses a standard error-message with a key, that is identical to the validator's rule.

This error message must be registered with the ValidationManager-service using one of the following methods:

* Register an error message for a single rule:
```php
validation_manager()->registerMessage('rule', 'message');
```
* Register error messages for multiple rules inside an array:
```php
validation_manager()->registerMessages(['rule' => 'message', ...]);
```
* Register all elements from a language-file as error messages:
```php
validation_manager()->registerMessagesFromLanguageFile('languageFileGroup','optionalLanguageFileNamespace');
```

### Register attributes

Any attributes (field-names) you use in your application might show up in error messages of (laravel-built-in or custom) validators.
If all these attributes are registered with the ValidationManager-service, each validation using a Form Request Object or createValidator/createValidator4Field call will automatically use the human readable translations of these attributes.
Registration is done using one of the following methods:

* Register a single attribute:
```php
validation_manager()->registerAttribute('fieldName', 'human readable field name');
```
* Register multiple attributes inside an array:
```php
validation_manager()->registerAttributes(['fieldName' => 'human readable field name', ...]);
```
* Register all elements from a translation-id as attributes:
```php
validation_manager()->registerAttributesTranslationId('myNamespace::myFile');
```

Note that the human readable field names are automatically set to lower-case, if the current language is english.

### Configure global rules
Global rules, that are applied automatically to all Form Request Objects, can be configured within the config-file `config/validation-manager.php` under the array-key `global_rules`, which must be a one-dimensional array of `fieldname`=> `rules` (just like the normal Laravel rules-syntax).

Note that, if rules for a single field are both defined globally as well as inside a specific Form Request Object, the non-global, specifically defined one takes precedence.

### Defining rules inside the Form Request Object
Rules in the Form Request Object must be defined inside a method called `getRules()` instead of the normal Laravel method `rules()`!

### Create validator instances
Using the `ValidationManagerTrait` inside the base Form Request Object ensures, that the registered messages and attributes are available there. But what about creating usual validator-instances without a Form Request Object?

The ValidationManager-service provides two easy ways of doing this:

* Create a validator-instance for multiple fields:
```php
validation_manager()->createValidator(['someField' => 'someValue',...],['someField' => 'some|rules',...]);
```
* Create a validator-instance for a single field:
```php
validation_manager()->createValidator4Field('someField','someValue','some|rules')
```

Attributes, messages and global rules defined with the ValidationManager-service are automatically used with these calls.

### RuleSets
If a certain field should be validated using the same rules in different locations of your application, you can define and retrieve them via the RuleSets-service/facade.

As with the ValidationManager-service, there are several ways to interact with RuleSets:
* By using the supplied helper-function `rule_sets()`, which returns the RuleSets-singleton from Laravel's service container.
* By injecting it into your method (e.g. `public function myControllerMethod(Webflorist\ValidationManager\RuleSets $ruleSets)`.
* By retrieving the RuleSets-singleton directly from the service container using `app('Webflorist\ValidationManager\RuleSets')` or `app()['Webflorist\ValidationManager\RuleSets']`.
* If you have registered the RuleSets-facade, you can of course interact with it using `\RuleSets::`.

The following examples will use the `rule_sets()` helper function.

Usage is quite straight-forward:

* Set one or more RuleSets:
```php
rule_sets()->set([
    'myRuleSetKey'=>'my|rules'
]);
```
* Retrieve a specific RuleSet:
```php
rule_sets()->get('myRuleSetKey');
```