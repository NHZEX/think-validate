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
        echo '====== ValidateDump ======' . PHP_EOL;
        (new self())->scanAnnotation();
        echo '========== DONE ==========' . PHP_EOL;
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
                $validate = [
                    'validate' => $validation->value,
                    'scene' => empty($validation->scene) ? null : $validation->scene,
                ];
                $result[$class][$method] = $validate;
                echo "> {$class}@{$method}\t => {$validate['validate']}" . ($validate['scene'] ? "@{$validate['scene']}" : '') . PHP_EOL;
            }
        }

        $dump = new DumpValue(ValidateService::getDumpFilePath());
        $dump->load();
        $dump->save($result);
    }
}
