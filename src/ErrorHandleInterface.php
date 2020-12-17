<?php

namespace Zxin\Think\Validate;

use think\Request;
use think\Response;

interface ErrorHandleInterface
{
    public function handle(Request $request, ValidateContext $context): Response;
}
