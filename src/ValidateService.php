<?php

namespace Zxin\Think\Validate;

use Doctrine\Common\Annotations\AnnotationRegistry;
use think\Service;

class ValidateService extends Service
{
    /**
     * @var array
     */
    public $storage;

    public function register()
    {
        $this->app->middleware->add(ValidateMiddleware::class, 'controller');

        $this->app->bind('validateStorage', function () {
            return $this->loadStorage() ?: [];
        });

        AnnotationRegistry::registerLoader('class_exists');
    }

    public function boot()
    {
    }

    protected function loadStorage(): array
    {
        if (empty($this->storage)) {
            $filename = app_path() . 'validate_storage.php';
            /** @noinspection PhpIncludeInspection */
            $this->storage = require $filename;
        }
        return $this->storage;
    }
}
