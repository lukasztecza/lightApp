### How to use
- include using composer
- create `/public/app.php` (where domain should point to) with the following content:
```
<?php
define('APP_ROOT_DIR', str_replace('/public', '', __DIR__));
include(APP_ROOT_DIR . '/vendor/autoload.php');
(new TinyAppBase\Model\System\Project())->run();
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
        "path": "/",
        "methods": ["GET"],
        "controller": "myController",
        "action": "home"
    }
]
```
- create `/src/Config/dependencies.json` with the following content:
```
{
    "fileController": {
        "class": "MyApp\\Controller\\MyController"
    }
}
```
- create `/src/Controller/MyController.php` with the following content:
```
<?php
namespace MyApp\Controller;

use TinyAppBase\Model\System\Request;
use TinyAppBase\Model\System\Response;

class MyController
{
    public function home(Request $request) : Response
    {
        return new Response(['message' => 'Hello world!'])
    }
}

```
