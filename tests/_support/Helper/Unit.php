<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Unit extends \Codeception\Module
{
    public function callNonPublic($object, string $method, array $params)
    {
        return (function () use ($object, $method, $params) {
            return call_user_func_array([$object, $method], $params);
        })->bindTo($object, $object)();
    }

    public function removeDirRecursively($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir($dir . '/' . $file)) ? $this->removeDirRecursively($dir . '/' . $file) : unlink($dir . '/' . $file);
        }

        return rmdir($dir);
    }
}
