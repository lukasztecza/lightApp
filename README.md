# tinyAppBase
Minimal application skeleton based on middleware, dependancy injection and model-view-controller patterns.

### Application flow
- application expects `APP_ROOT_DIR` to be defined as application root directory `/`
- note that web root directory should be in `/public`
- `TinyAppBase\Model\System\Project` should be the first class hit by app
- it will pull configurations from `src/Config/*` json files
- application sets error handler `TinyAppBase\Model\System\ErrorHandler`
- and builds `TinyAppBase\Model\System\Request` using `TinyAppBase\Model\System\Router`
- router determines `%routedController%` and `%routedAction%` parameters
- then first application middleware is executed named in `src/Config/settings.json` as `applicationStartingPoint`
- this class should be specified in `src/Config/dependencies.json`

### Basic usege
- assuming that your app is `myRepo/myApp` then include using composer:
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
- create `/public/app.php` (where your domain should point to) with the following content:
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
- start php server in `/public` directory
```
cd /public
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
### Running with apache
- if you have set apache you may find this `/public/.htaccess` content useful:
```
# Do not allow to index directory
Options -Indexes

# Set browser caching
<IfModule mod_headers.c>
    <FilesMatch "\.(jpg|jpeg|png|gif)$">
        Header set Cache-Control "max-age=86400, public"
    </FilesMatch>
    <FilesMatch "\.(css|js)$">
        Header set Cache-Control "max-age=86400, private"
    </FilesMatch>
    <FilesMatch "\.(ttf|woff|woff2|eot)$">
        Header set Cache-Control "max-age=86400, private"
    </FilesMatch>
</IfModule>

# Redirect to front controller
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Redirect to URI without front controller
    RewriteCond %{ENV:REDIRECT_STATUS} ^$
    RewriteRule ^app\.php(?:/(.*)|$) /$1 [R=301,L]

    # If the requested filename exists, simply serve it.
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule ^ - [L]

    # Rewrite all other queries to the front controller.
    RewriteRule ^ /app.php [L]
</IfModule>

<IfModule !mod_rewrite.c>
    <IfModule mod_alias.c>
        # When mod_rewrite is not available redirect to the front controller
        RedirectMatch 302 ^/$ /app.php/
    </IfModule>
</IfModule>
```
