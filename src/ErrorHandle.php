<?php

namespace Zxin\Think\Validate;

use think\Response;

interface ErrorHandle
{
    public function handle(): Response;
}
