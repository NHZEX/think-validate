<?php

namespace Zxin\Think\Validate;

use think\Validate;
use function explode;
use function in_array;
use function str_contains;

abstract class ValidateBase extends Validate
{
    /**
     * 获取当前验证器生效的字段
     * @return array
     */
    final public function getRuleKeys(): array
    {
        $rules = $this->rule;

        foreach ($this->append as $key => $rule) {
            if (!isset($rules[$key])) {
                $rules[$key] = $rule;
            }
        }

        $result = [];

        foreach ($rules as $key => $rule) {
            if (\str_contains($key, '|')) {
                // 字段|描述 用于指定属性名称
                [$key] = \explode('|', $key);
            }

            // 场景检测
            if (!empty($this->only) && !\in_array($key, $this->only)) {
                continue;
            }

            if (isset($this->remove[$key]) && true === $this->remove[$key] && empty($this->append[$key])) {
                // 字段已经移除 无需验证
                continue;
            }

            if (empty($rule)) {
                // 规则为空 无需验证
                continue;
            }

            $result[] = $key;
        }

        return $result;
    }
}
