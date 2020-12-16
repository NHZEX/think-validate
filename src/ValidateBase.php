<?php

namespace Zxin\Think\Validate;

use think\Validate;
use function explode;
use function in_array;
use function strpos;

abstract class ValidateBase extends Validate
{
    /**
     * 获取当前验证器生效的字段
     * @return array
     */
    public function getRuleKeys(): array
    {
        $rules = $this->rule;

        // 如果thinkphp同意调整场景优先级，应该不用在加载场景
        if ($this->currentScene) {
            $this->getScene($this->currentScene);
        }

        foreach ($this->append as $key => $rule) {
            if (!isset($rules[$key])) {
                $rules[$key] = $rule;
            }
        }

        $result = [];

        foreach ($rules as $key => $rule) {
            if (strpos($key, '|')) {
                // 字段|描述 用于指定属性名称
                [$key] = explode('|', $key);
            }

            // 场景检测
            if (!empty($this->only) && !in_array($key, $this->only)) {
                continue;
            }

            if (isset($this->remove[$key]) && true === $this->remove[$key] && empty($this->append[$key])) {
                // 字段已经移除 无需验证
                // todo 规则已全部移除的判断
                continue;
            }

            $result[] = $key;
        }

        return $result;
    }
}
