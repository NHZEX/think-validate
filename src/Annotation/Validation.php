<?php

namespace Zxin\Think\Validate\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Validation
 * @package Zxin\Think\Validate\Annotation
 * @Annotation
 * @Annotation\Target({"CLASS", "METHOD"})
 */
class Validation extends Annotation
{
    /**
     * @var string
     */
    public $value = '';

    /**
     * @var string
     */
    public $scene = '';
}
