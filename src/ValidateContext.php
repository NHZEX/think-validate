<?php

declare(strict_types=1);

namespace Zxin\Think\Validate;

use think\Validate;
use function app;

class ValidateContext
{
    protected string $controller;

    protected string $method;

    protected Validate $validate;

    protected bool $success;

    /**
     * @var string[]
     */
    protected array $inputFields;

    /**
     * AuthContext constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @return ValidateContext|null
     */
    public static function get(): ?ValidateContext
    {
        $app = \app();
        return $app->has(ValidateContext::class) ? $app->get(ValidateContext::class) : null;
    }

    /**
     * @param string   $controller
     * @param string   $method
     * @param Validate $validate
     * @param string[] $inputFields
     * @param bool     $success
     * @return ValidateContext
     */
    public static function create(
        string $controller,
        string $method,
        Validate $validate,
        bool $success,
        array $inputFields
    ): ValidateContext {
        $ctx              = new ValidateContext();
        $ctx->controller  = $controller;
        $ctx->method      = $method;
        $ctx->validate    = $validate;
        $ctx->success     = $success;
        $ctx->inputFields = $inputFields;

        \app()->bind(ValidateContext::class, $ctx);
        return $ctx;
    }

    /**
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return Validate
     */
    public function getValidate(): Validate
    {
        return $this->validate;
    }

    /**
     * @return string[]
     */
    public function getInputFields(): array
    {
        return $this->inputFields;
    }
}
