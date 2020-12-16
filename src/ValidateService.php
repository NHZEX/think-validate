<?php

namespace Zxin\Think\Validate;

use Doctrine\Common\Annotations\AnnotationRegistry;
use think\Service;

class ValidateService extends Service
{
    public function register()
    {
        $this->app->middleware->add(ValidateMiddleware::class, 'controller');

        AnnotationRegistry::registerLoader('class_exists');
    }

    public function boot()
    {
    }
}
