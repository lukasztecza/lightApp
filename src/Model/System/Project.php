<?php declare(strict_types=1);
namespace LightApp\Model\System;

use LightApp\Model\System\ErrorHandler;
use LightApp\Model\System\Router;
use LightApp\Model\System\Request;
use LightApp\Model\System\Response;
use LightApp\Model\Middleware\MiddlewareInterface;
use LightApp\Model\Command\CommandInterface;

class Project
{
    protected const PARAMETER_ENVIRONMENT = 'environment';
    protected const PARAMETER_DEFAULT_CONTENT_TYPE = 'defaultContentType';
    protected const PARAMETER_APPLICATION_STARTING_POINT = 'applicationStartingPoint';

    protected const ROUTED_CONTROLLER_PLACEHOLDER = '%routedController%';
    protected const ROUTED_ACTION_PLACEHOLDER = '%routedAction%';

    protected const CONFIG_PATH = APP_ROOT_DIR . '/src/Config/';

    public function run() : void
    {
        $parameters = $this->getParameters();
        $this->checkRequiredParameters($parameters);

        $this->setErrorHandler($parameters);

        $request = $this->getRequest($parameters);
        $dependencies = $this->getDependencies($parameters, $request);
        $toCreate = [];
        $counter = 0;
        $this->analyseInjections($counter, $dependencies, $toCreate, $parameters[static::PARAMETER_APPLICATION_STARTING_POINT]);
        $toCreate = array_values($toCreate);

        $this->inject($dependencies, $toCreate);
        if (!($dependencies[$parameters[static::PARAMETER_APPLICATION_STARTING_POINT]]['object'] instanceof MiddlewareInterface)) {
            throw new \Exception('Application middleware has to implement ' . MiddlewareInterface::class);
        }
        $dependencies[$parameters[static::PARAMETER_APPLICATION_STARTING_POINT]]['object']->process($request);
    }

    public function runCommand(string $objectName) : string
    {
        $parameters = $this->getParameters();
        $this->checkRequiredParameters($parameters);

        $this->setErrorHandler($parameters);

        $dependencies = $this->getDependencies($parameters);
        if (!isset($dependencies[$objectName]) || !class_exists($dependencies[$objectName]['class'])) {
            throw new \Exception(
                'Class for object name ' . var_export($objectName, 1) . ' does not exist,' .
                ' check dependecies.json and parameters passed with command'
            );
        }
        $toCreate = [];
        $counter = 0;
        $this->analyseInjections($counter, $dependencies, $toCreate, $objectName);
        $toCreate = array_values($toCreate);

        $this->inject($dependencies, $toCreate);
        if (!($dependencies[$objectName]['object'] instanceof CommandInterface)) {
            throw new \Exception('Command has to implement ' . CommandInterface::class);
        }
        $commandResult = $dependencies[$objectName]['object']->execute();
        trigger_error('Command ended with the result:' . var_export($commandResult, true), E_USER_NOTICE);

        return ($commandResult->getStatus() ? 'Command succeded' : 'Command failed') .
            ' with message ' . $commandResult->getMessage() . PHP_EOL
        ;
    }

    protected function setErrorHandler(array $parameters) : void
    {
        new ErrorHandler($parameters[static::PARAMETER_ENVIRONMENT], $parameters[static::PARAMETER_DEFAULT_CONTENT_TYPE]);
    }

    protected function getRequest(array $parameters) : Request
    {
        $routes = json_decode(file_get_contents(static::CONFIG_PATH . 'routes.json'), true);
        return (new Router($routes, $parameters[static::PARAMETER_DEFAULT_CONTENT_TYPE]))->buildRequest();
    }

    private function getParameters() : array
    {
        if (!file_exists(static::CONFIG_PATH . 'parameters.json')) {
            throw new \Exception('Could not find parameters.json file in ' . static::CONFIG_PATH);
        }

        $parameters = json_decode(file_get_contents(static::CONFIG_PATH . 'parameters.json'), true);
        $settings = file_get_contents(static::CONFIG_PATH . 'settings.json');

        foreach ($parameters as $key => $parameter) {
            if (!is_int($parameter) && !is_string($parameter) && !is_float($parameter) && !is_bool($parameter)) {
                throw new \Exception(
                    'Parameter has to be string, int, float or bool got ' . var_export($parameter, true) . ' for key ' . var_export($key, true)
                );
            }

            $placeholder = '%' . $key . '%';
            $settings = str_replace($placeholder, $parameter, $settings);
        }
        $parameters += json_decode($settings, true);

        return $parameters;
    }

    private function checkRequiredParameters(array $parameters) : void
    {
        if (
            !array_key_exists(static::PARAMETER_ENVIRONMENT, $parameters) ||
            !array_key_exists(static::PARAMETER_DEFAULT_CONTENT_TYPE, $parameters) ||
            !array_key_exists(static::PARAMETER_APPLICATION_STARTING_POINT, $parameters)
        ) {
            throw new \Exception(
                'Could not find ' .
                static::PARAMETER_ENVIRONMENT . ' placeholder or ' .
                static::PARAMETER_DEFAULT_CONTENT_TYPE . ' placeholder or ' .
                static::PARAMETER_APPLICATION_STARTING_POINT . ' placeholder ' .
                ' in parameters.json or settings.json, make sure you set these values'
            );
        }
    }

    private function getDependencies(array $parameters, Request $request = null) : array
    {
        $dependencies = file_get_contents(static::CONFIG_PATH . 'dependencies.json');
        $this->checkRequiredPlaceholders($dependencies, $parameters);

        $dependencies = json_decode($dependencies, true);
        if ($request) {
            return $this->replacePlaceholders($dependencies, $parameters, $request);
        }

        return $this->replacePlaceholders($dependencies, $parameters);
    }

    private function checkRequiredPlaceholders(string $dependencies, array $parameters) : void
    {
        if (
            !strpos($dependencies, static::ROUTED_CONTROLLER_PLACEHOLDER) ||
            !strpos($dependencies, static::ROUTED_ACTION_PLACEHOLDER)
        ) {
            throw new \Exception(
                'Could not find ' .
                static::ROUTED_CONTROLLER_PLACEHOLDER . ' placeholder or ' .
                static::ROUTED_ACTION_PLACEHOLDER . ' placeholder in dependencies.json' .
                ' make sure you set these values as dependencies of object responsible for handling them'
            );
        }

        if(
            !strpos($dependencies, $parameters[static::PARAMETER_APPLICATION_STARTING_POINT])
        ) {
            throw new \Exception(
                'Could not find ' .
                static::PARAMETER_APPLICATION_STARTING_POINT . ' value in dependencies.json,' .
                ' make sure you specify it as one of the dependencies'
            );
        }
    }

    private function replacePlaceholders(array $dependencies, array $parameters, Request $request = null) : array
    {
        if ($request) {
            $replacements[static::ROUTED_CONTROLLER_PLACEHOLDER] = '@' . $request->getController() . '@';
            $replacements[static::ROUTED_ACTION_PLACEHOLDER] = $request->getAction();
        }

        foreach ($parameters as $placeholder => $value) {
            $replacements['%' . $placeholder . '%'] = $value;
        }

        foreach ($dependencies as $key => $dependency) {
            if (isset($dependency['inject'])) {
                foreach ($dependencies[$key]['inject'] as $subKey => $inject) {
                    if (isset($replacements[$inject])) {
                        $dependencies[$key]['inject'][$subKey] = $replacements[$inject];
                    }
                }
            }
        }

        return $dependencies;
    }

    private function analyseInjections(int &$counter, array $dependencies, array &$toCreate, string $name) : void
    {
        $counter++;
        if ($counter > 1000) {
            throw new \Exception('Too many dependencies or danger of infinite recurrence, reached counter ' . var_export($counter, true));
        }

        if (empty($dependencies[$name])) {
            throw new \Exception('Unrecognized dependency ' . $name);
        }

        $existing = array_search($name, $toCreate);
        if ($existing !== false) {
            unset($toCreate[$existing]);
        }
        $toCreate[] = $name;

        if (isset($dependencies[$name]['inject'])) {
            foreach ($dependencies[$name]['inject'] as $injection) {
                if (is_string($injection) && strpos($injection, '@') === 0) {
                    $subname = trim($injection, '@');
                    $this->analyseInjections($counter, $dependencies, $toCreate, $subname);
                }
            }
        }
    }

    private function inject(array &$dependencies, array $toCreate) : void
    {
        $index = count($toCreate);
        while ($index--) {
            if (empty($dependencies[$toCreate[$index]]['object'])) {
                if (isset($dependencies[$toCreate[$index]]['inject'])) {
                    foreach ($dependencies[$toCreate[$index]]['inject'] as $key => $injection) {
                        if (is_string($injection) && strpos($injection, '@') === 0) {
                            $dependencies[$toCreate[$index]]['inject'][$key] = $dependencies[trim($injection, '@')]['object'];
                        }
                    }
                }

                if (isset($dependencies[$toCreate[$index]]['inject'])) {
                    $dependencies[$toCreate[$index]]['object'] = new $dependencies[$toCreate[$index]]['class'](
                        ...$dependencies[$toCreate[$index]]['inject']
                    );
                } else {
                    $dependencies[$toCreate[$index]]['object'] = new $dependencies[$toCreate[$index]]['class']();
                }
            }
        }
    }
}
