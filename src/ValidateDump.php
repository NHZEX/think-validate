<?php

namespace Zxin\Think\Validate;

use think\App;
use Zxin\Think\Annotation\DumpValue;
use Zxin\Think\Annotation\Scanning;

class ValidateDump
{
    use InteractsWithAnnotation;

    /**
     * @var App
     */
    protected $app;

    /**
     * @var string
     */
    protected $namespace;

    public static function dump()
    {
        (new self())->scanAnnotation();
    }

    public function __construct()
    {
        $this->app = \app();

        $this->namespace = $this->app->config->get('validate.namespace', 'app\\Validate');
        if (!str_ends_with($this->namespace, '\\')) {
            $this->namespace .= '\\';
        }
    }

    public function scanAnnotation()
    {
        $scanning = new Scanning($this->app);
        $result = [];

        foreach ($scanning->scanningClass() as $class) {
            foreach (get_class_methods($class) as $method) {
                $validation = $this->parseAnnotation($class, $method);
                if ($validation === null) {
                    continue;
                }
                $result[$class][$method] = [
                    'validate' => $validation->value,
                    'scene' => empty($validation->scene) ? null : $validation->scene,
                ];
            }
        }

        $dump = new DumpValue(app_path() . 'validate_storage.php');
        $dump->load();
        $dump->save($result);
    }
}
