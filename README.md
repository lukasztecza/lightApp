### How to use
- include using composer
```
{
    "name": "myRepo/myApp",
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/lukasztecza/tinyAppBase"
       }
    ],
    "require": {
        "lukasztecza/tinyAppBase": "dev-master"
    },
    "autoload": {
        "psr-4": { "MyApp\\": "src/" }
    }
}
```
- create `/public/app.php` (where domain should point to) with the following content:
```
<?php
define('APP_ROOT_DIR', str_replace('/public', '', __DIR__));
include(APP_ROOT_DIR . '/vendor/autoload.php');
(new TinyAppBase\Model\System\Project())->run();
```
- create `/.gitignore` with the following content:
```
src/Config/parameters.json

```
- create `/src/Config/parameters.json` with the following content:
```
{
    "environment": "dev"
}
```
- create `/src/Config/settings.json` with the following content:
```
{
    "defaultContentType": "application/json",
    "applicationStartingPoint": "simpleOutputMiddleware"
}
```
- create `/src/Config/routes.json` with the following content:
```
[
    {
        "path": "/home",
        "methods": ["GET"],
        "controller": "myController",
        "action": "home"
    }
]
```
- create `/src/Config/dependencies.json` with the following content:
```
{
    "simpleOutputMiddleware": {
        "class": "TinyAppBase\\Model\\Middleware\\SimpleOutputMiddleware",
        "inject": [
            "@controllerMiddleware@",
            "%defaultContentType%"
        ]
    },
    "controllerMiddleware": {
        "class": "TinyAppBase\\Model\\Middleware\\ControllerMiddleware",
        "inject": [
            "%routedController%",
            "%routedAction%"
        ]
    },
    "myController": {
        "class": "MyApp\\Controller\\MyController"
    }
}
```
- create `/src/Controller/MyController.php` with the following content:
```
<?php
namespace MyApp\Controller;

use TinyAppBase\Controller\ControllerInterface;
use TinyAppBase\Model\System\Request;
use TinyAppBase\Model\System\Response;

class MyController implements ControllerInterface
{
    public function home(Request $request) : Response
    {
        return new Response(null, ['message' => 'Hello world!'], ['message' => 'raw']);
    }
}
```
- start php server in public directory
```
cd public
php -S localhost:8080
```
- visit in browser
```
localhost:8080/app.php/home
```
