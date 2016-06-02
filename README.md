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
    'emailFormat' => 'both',
    'sparkpost' => array(
        'api_key' => 'YOUR_API_KEY',
        'timeout' => '120', // optional, set non-default timeout
        'log' => array( // optional, write to CakeLog
            'level' => 'debug', // optional, see Psr\Log\LogLevel, but cannot use class constants here.
            'format' => '{response}', // optional, string with token substitution, see https://github.com/guzzle/guzzle/blob/master/src/MessageFormatter.php#L12'.
        ),
    ),
  );
}
```

## Requirements

CakePHP 2.0+
