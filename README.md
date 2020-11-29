# Bootstraps a Laravel application

## Installs
[Download the repository code](https://github.com/ilyabirman/Likely/archive/master.zip) and move `release/likely.js` and
`release/likely.css` to the desired directory (or `likely.min.js` & `likely.min.css` if you prefer them optimized).

use [https://getcomposer.org/](Composer):

```sh
$ composer require qortex/bootstrap
```

## Adds commands
```sh
$ php artisan make:service {serviceClassName}
```
Creates a service class for model. Service class contains methods that represent scenarios for creating, reading, updating and deleting models.

```sh
$ php artisan qortex:make:model {ModelClassName}
```
Extends laravel `make:model` command empowering it with `--service` option. This option makes command to create service class along with model.

## Adds Laravel Collective Forms Macros for Bootstrap

cpFilterDatetime
```blade
{{ Form::cpFilterDatetime($label, $fieldName, $defaultValue, $fieldValue, $required) }}
```

cpFilterSelectMultiple
```blade
{{ Form::cpFilterSelectMultiple($label, $fieldName, $values, $fieldValue, $attributes) }}
```

cpFilterSelect
```blade
{{ Form::cpFilterSelect($label, $fieldName, $values, $fieldValue, $attributes) }}
```

cpFormDatetime
```blade
{{ Form::cpFormDatetime($label, $fieldName, $defaultValue, $fieldValue, $required) }}
```

cpFormSelectMultiple
```blade
{{ Form::cpFormSelectMultiple($label, $fieldName, $values, $fieldValue, $attributes) }}
```

cpFormSelect
```blade
{{ Form::cpFormSelect($label, $fieldName, $values, $fieldValue, $attributes) }}
```

cpFormText
```blade
{{ Form::cpFormText($label, $fieldName, $defaultValue, $fieldValue, $attributes) }}
```

cpFormEmail
```blade
{{ Form::cpFormEmail($label, $fieldName, $defaultValue, $fieldValue, $attributes) }}
```

cpFormPhone
```blade
{{ Form::cpFormPhone($label, $fieldName, $defaultValue, $fieldValue, $attributes) }}
```

cpFormPassword
```blade
{{ Form::cpFormPassword($label, $fieldName, $fieldValue, $attributes) }}
```

cpFormTextarea
```blade
{{ Form::cpFormTextarea($label, $fieldName, $defaultValue, $fieldValue, $attributes) }}
```

cpFormCheckbox
```blade
{{ Form::cpFormCheckbox($label, $fieldName, $checked, $required, $attributes) }}
```

cpFormInfo
```blade
{{ Form::cpFormInfo($label, $fieldName, $attributes) }}
```

cpFormButton
```blade
{{ Form::cpFormButton($label, $fieldName, $value, $attributes) }}
```

cpFormDangerButton
```blade
{{ Form::cpFormDangerButton($label, $fieldName, $value, $attributes) }}
```
