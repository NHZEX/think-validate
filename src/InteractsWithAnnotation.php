<?php

namespace Zxin\Think\Validate;

use Zxin\Think\Validate\Annotation\Validation;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionException;
use think\Validate;

/**
 * Trait InteractsWithAnnotation
 * @package Zxin\Think\Validate
 * @property string $namespace
 */
trait InteractsWithAnnotation
{
    public function parseAnnotation(string $class, string $method): ?Validation
    {
        try {
            $refClass = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            return null;
        }
        if ($refClass->isAbstract() || $refClass->isTrait()) {
            return null;
        }
        $reader = new AnnotationReader();
        try {
            $refMethod = $refClass->getMethod($method);
        } catch (ReflectionException $e) {
            return null;
        }
        if (!$refMethod->isPublic() || $refMethod->isStatic()) {
            return null;
        }
        $methodName = $refMethod->getName();
        if (str_starts_with($methodName, '_')) {
            return null;
        }
        $annotations = $reader->getMethodAnnotations($refMethod);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Validation) {
                $result = $this->parseValidation($annotation, $method);
                if ($result !== null) {
                    return $result;
                }
            }
        }
        return null;
    }

    public function parseValidation(Validation $validation, string $method): ?Validation
    {
        if (empty($validation->value)) {
            return null;
        }
        if (str_starts_with($validation->value, '@')) {
            $class = $this->namespace . str_replace('.', '\\', substr($validation->value, 1));
        } elseif (str_starts_with($validation->value, '\\')) {
            $class = $validation->value;
        } else {
            return null;
        }
        if (!class_exists($class) || !is_subclass_of($class, Validate::class)) {
            return null;
        }
        $validation = clone $validation;
        $validation->value = $class;
        if ($validation->scene === '_') {
            $validation->scene = $method;
        }
        return $validation;
    }
}
