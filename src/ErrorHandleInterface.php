<?php

namespace Zxin\Think\Validate;

use think\Request;
use think\Response;
use think\Validate;

interface ErrorHandleInterface
{
    public function handle(Request $request, Validate $validate): Response;
}
