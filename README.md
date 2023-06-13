plupload-for-laravel
================

Laravel plupload support.

Handeling chunked uploads.

## Installation

Install using composer 

```sh
composer require evgen-dev/plupload-for-laravel
```

Add the provider to `config/app.php`

```php
'providers' => [
    EvgenDev\LaravelPlupload\LaravelPluploadServiceProvider::class,
]
```

If you want to use te build in builder insert the facade

```php
'aliases' => array(
    'Plupload' => EvgenDev\LaravelPlupload\Facades\Plupload::class,
),
```