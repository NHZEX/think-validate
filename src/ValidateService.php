<?php

namespace Zxin\Think\Validate;

use think\App;
use think\Service;
use function str_replace;

class ValidateService extends Service
{
    public array $storage;

    public function register()
    {
        $this->app->middleware->add(ValidateMiddleware::class, 'controller');

        $this->app->bind('validateStorage', fn () => $this->loadStorage() ?: []);
    }

    public function boot()
    {
    }

    protected function loadStorage(): array
    {
        if (empty($this->storage)) {
            $filename = app_path() . 'validate_storage.php';
            $this->storage = require $filename;
        }
        return $this->storage;
    }

    public static function getDumpFilePath(string $filename = 'validate_storage.php'): string
    {
        $path = App::getInstance()->config->get('validate.dump_file_path');
        if (empty($path)) {
            $path = App::getInstance()->getAppPath();
        }
        $path = \str_replace('\\', '/', $path);
        if (!str_ends_with($path, '/')) {
            $path .= '/';
        }
        return $path . $filename;
    }
}
