# tinyAppBase
Minimal application skeleton based on middleware, dependancy injection and model-view-controller patterns.

### Application flow
- application expects `APP_ROOT_DIR` to be defined as application root directory `/`
- note that web root directory should be in `/public`
- `src/Model/System/Project.php` should be the first class hit by app, it pulls configs from `src/Config/*` json files
- application sets error handler and builds `src/Model/System/Request.php` using `src/Model/System/Router.php`
- router also determines `%routedController%` and `%routedAction%` parameters
- then first application middleware is executed specified in `src/Config/settings.json` as `applicationStartingPoint`
- it should be specified in `src/Config/dependencies.json`

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
- Wrap in `@` to inject other class to your class constructor
- Wrap in `%` to inject parameter specified in `src/Config/parameters.json` or `src/Config/settings.json`
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
- it will store logs in `/tmp/logs/php-{date}.log` file instead of throwing errors to output and display error page

### Html output
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
### Running commands
- if you want to run command line jobs create `/scripts/command.php` with the following content:
```
<?php
define('APP_ROOT_DIR', str_replace('/scripts', '', __DIR__));
include(APP_ROOT_DIR . '/vendor/autoload.php');
if (empty($argv[1])) {
    echo 'Please specify command object name from dependencies as parameter' . PHP_EOL;
    exit;
}
echo (new TinyAppBase\Model\System\Project())->runCommand($argv[1]);
```
- include in `/src/Config/dependencies.json` an entry for it:
```
"myCommand": {
    "class": "MyApp\\Model\\Command\\MyCommand",
    "inject": [
        "some variable"
    ]
}
```
- create `/src/Model/Command/MyCommand` with the following content:
```
<?php
namespace MyApp\Model\Command;

use TinyAppBase\Model\Command\CommandInterface;
use TinyAppBase\Model\Command\CommandResult;

class MyCommand implements CommandInterface
{
    private $someVariable;

    public function __construct(string $someVariable)
    {
        $this->someVariable = $someVariable;
    }

    public function execute() : CommandResult
    {
        echo 'Passed variable is: ' . $this->someVariable . PHP_EOL;
        return new CommandResult('success', 'everything went well');
    }
}
```
- go to `/scripts` and run:
```
php command.php myCommand
```
