### Basic usege
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
- if you want to make use of error handler change in `/src/Config/parameters.json`:
```
"environment": "prod"
```
* it will store logs in `/tmp/logs/php-{date}.log` file instead of throwing errors to output and display error page

#### Html output
- if you want to use `text/html` by default chenge in `/src/Config/settings.json`:
```
"defaultContentType": 'text/html'
```
- create `/src/View/home.php` with the following content:
```
<h3>
    My page
</h3>
<p><?php echo $message; ?></p>
```
- update `/src/Controller/MyController` to return response with specified filename:
```
return new Response('home.php', ['message' => 'Hello world!'], ['message' => 'html']);
```
- if you do not want to change default content type you can also just set content type in the response object:
```
return new Response('home.php', ['message' => 'Hello world!'], ['message' => 'html'], ['Content-Type' => 'text/html']);
```
