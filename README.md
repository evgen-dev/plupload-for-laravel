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

# Using
## 1. Create localization file resources/lang/en/validation.php file rows if not exists and add lines:

```php
return [
    'invalid_file_extension' => 'It is forbidden to upload .:extension files',

    'max' => [
        'file' => 'The :attribute field must not be greater than :max :units.',
    ]
];
```

## 2. Add upload routes
### Basic usage without limitations:
```php
Route::post('/upload', function(){
    return Plupload::receive('file', function($file){
        $file->move(storage_path() . '/plupload/', $file->getClientOriginalName());
        return true;
    });

});
```

### Limit uploading file size:
```php
use \EvgenDev\LaravelPlupload\Filters\Filesize;

Route::post('/upload', function(){
    return Plupload::sizelimit(3, Filesize::FILE_SIZE_UNITS_MB)
    ->receive('file', function($file){
        $file->move(storage_path() . '/plupload/', $file->getClientOriginalName());
        return true;
    });
});
```

### Limit uploading file extensions:
```php
Route::post('/upload', function()
{
    return Plupload::extensions(['jpg', 'png', 'gif'])->receive('file', function($file){
        $file->move(storage_path() . '/plupload/', $file->getClientOriginalName());
        return true;
    });

});
```

### Limit uploading filesize and files extensions:
```php
use \EvgenDev\LaravelPlupload\Filters\Filesize;

Route::post('/upload', function()
{
    return Plupload::sizelimit(5, Filesize::FILE_SIZE_UNITS_MB)
    ->extensions(['jpg', 'png', 'gif'])
    ->receive('file', function($file){
        $file->move(storage_path() . '/plupload/', $file->getClientOriginalName());
        return 'ready';
    });
});
```

## 3. csrf-token validation

There are two ways.

### 1. Passing the token

Add in your blade file

```
<meta name="csrf-token" content="{{ csrf_token() }}">
```

in your Plupload inititalization JS file, add

```js
headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
```

Don't forget to refresh the token.

### 2. Disabling token validation

Add to your route rule:

```php
->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
```

```php
Route::post('/upload', function()
{
    return Plupload::receive('file', function($file){
        $file->move(storage_path() . '/plupload/', $file->getClientOriginalName());
        return true;
    });

})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
```

## 4. Preventing chunk uploading after file size or file extension error

Add in your JS file event handling:
```js
uploader.bind('ChunkUploaded', function(up, file, response) {
        response = jQuery.parseJSON(response.response);
        if(response.error && (response.error.code == 413 || response.error.code == 415)){
            alert(response.error.message);
            file.destroy();
        }
        up.refresh();
    });
```

Enjoy!
