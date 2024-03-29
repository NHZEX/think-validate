<?php

namespace Zxin\Think\Validate\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * Class Validation
 * @package Zxin\Think\Validate\Annotation
 * @Annotation
 * @Annotation\Target({"CLASS", "METHOD"})
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Validation
{
    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $scene;

    public function __construct(
        string $name,
        string $scene = ''
    ) {
        $this->name = $name;
        $this->scene = $scene;
    }
}
