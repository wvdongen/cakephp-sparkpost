# cakephp-sparkpost

### Installation

You can clone the plugin into your project:

```
cd path/to/app/Plugin
git clone https://github.com/wvdongen/cakephp-sparkpost.git SparkPost
```

Also you can use composer for install this plugin. Just add new requirement to your composer.json

```
"require": {
    ...,
    "wvdongen/cakephp-sparkpost": "*"
},
```

Bootstrap the plugin in app/Config/bootstrap.php:

```php
CakePlugin::load('SparkPost');
```

## Configuration

Create the file app/Config/email.php with the class EmailConfig.

```php
<?php
class EmailConfig {
  public $sparkPost = array(
    'transport' => 'SparkPost.SparkPost',
    'api_key' => 'YOUR_API_KEY',
    'emailFormat' => 'both',
  );
}
```
An optional key 'timeout' can be provided too.

## Requirements

CakePHP 2.0+
