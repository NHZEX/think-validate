<?php
declare(strict_types=1);

namespace Zxin\Think\Validate;

use think\Request;

interface AskValidateInterface
{
    public static function askValidate(Request $request): ?string;
}
