<?php

namespace Zxin\Think\Validate;

use Generator;
use SplFileInfo;
use SplFileObject;
use Symfony\Component\Finder\Finder;
use Symfony\Component\VarExporter\Exception\ExceptionInterface;
use Symfony\Component\VarExporter\VarExporter;
use think\App;

class ValidateDump
{
    use InteractsWithAnnotation;

    /**
     * @var App
     */
    protected $app;

    protected $baseDir;
    protected $controllerLayer;
    protected $apps = [];

    protected $controllerNamespaces = 'app\\';

    /**
     * @var string
     */
    protected $namespace;

    public static function dump()
    {
        (new self())->scanAuthAnnotation();
    }

    public function __construct()
    {
        $this->app = \app();

        $this->namespace = $this->app->config->get('validate.namespace', 'app\\Validate');
        if (!str_ends_with($this->namespace, '\\')) {
            $this->namespace .= '\\';
        }
    }

    /**
     * @return int
     */
    public function scanAuthAnnotation(): int
    {
        $this->baseDir         = $this->app->getBasePath();
        $this->controllerLayer = $this->app->config->get('route.controller_layer');
        $this->apps = [];

        $dirs = array_map(function ($app) {
            return $this->baseDir . $app . DIRECTORY_SEPARATOR . $this->controllerLayer;
        }, $this->apps);
        $dirs[] = $this->baseDir . $this->controllerLayer . DIRECTORY_SEPARATOR;

        return $this->scanAnnotation($dirs);
    }

    /**
     * @param $dirs
     * @return int
     */
    protected function scanAnnotation($dirs): int
    {
        $result = [];
        foreach ($this->scanning($dirs) as $file) {
            $class = $this->parseClassName($file);
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

        $this->export($result);
        return count($result);
    }

    public function export(array $data): bool
    {
        $filename = app_path() . 'validate_storage.php';

        if (is_file($filename) && is_readable($filename)) {
            $sf = new SplFileObject($filename, 'r');
            $sf->seek(2);
            [, $lastHash] = explode(':', $sf->current() ?: ':');
            $lastHash = trim($lastHash);
            $contents = $sf->fread($sf->getSize() - $sf->ftell());
            if ($lastHash !== md5($contents)) {
                unset($lastHash);
            }
        }

        try {
            $nodes_data = VarExporter::export($data);
        } catch (ExceptionInterface $e) {
            $nodes_data = '[]';
        }

        $contents = "return {$nodes_data};\n";
        $hash = md5($contents);

        if (isset($lastHash) && $lastHash === $hash) {
            return true;
        }

        $date = date('c');
        $info = "// update date: {$date}\n// hash: {$hash}";

        $tempname = stream_get_meta_data($tf = tmpfile())['uri'];
        fwrite($tf, "<?php\n{$info}\n{$contents}");
        copy($tempname, $filename);

        return true;
    }

    /**
     * @param $dirs
     * @return Generator
     */
    protected function scanning($dirs): Generator
    {
        $finder = new Finder();
        $finder->files()->in($dirs)->name('*.php');
        if (!$finder->hasResults()) {
            return;
        }
        yield from $finder;
    }

    /**
     * 解析类命名（仅支持Psr4）
     * @param SplFileInfo $file
     * @return string
     */
    protected function parseClassName(SplFileInfo $file): string
    {
        $controllerPath = substr($file->getPath(), strlen($this->baseDir));

        $controllerPath = str_replace('/', '\\', $controllerPath);
        if (!empty($controllerPath)) {
            $controllerPath .= '\\';
        }

        $baseName = $file->getBasename(".{$file->getExtension()}");
        return $this->controllerNamespaces . $controllerPath . $baseName;
    }
}
