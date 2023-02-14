<?php

declare(strict_types=1);

namespace Zxin\Think\Validate;

use think\Request;

interface AskValidateInterface
{
    /**
     * @param Request     $request
     * @param string|null $scene
     * @return string|array
     */
    public static function askValidate(Request $request, ?string $scene);
}
