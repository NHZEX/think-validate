<?php

namespace Zxin\Think\Validate;

use Closure;
use think\App;
use think\exception\HttpException;
use think\Request;
use think\Response;
use think\Validate;

class ValidateMiddleware
{
    use InteractsWithAnnotation;

    /**
     * @var array 验证器映射
     */
    protected $mapping = [];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $errorHandle;

    /**
     * @var App
     */
    protected $app;

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $this->app = \app();
        if (file_exists($path = $this->app->getAppPath() . 'validate.php')) {
            /** @noinspection PhpIncludeInspection */
            $this->mapping = require $path;
        }
        $this->namespace = $this->app->config->get('validate.namespace', 'app\\Validate');
        if (!str_ends_with($this->namespace, '\\')) {
            $this->namespace .= '\\';
        }
        $this->errorHandle = $this->app->config->get('validate.error_handle');
    }

    /**
     * @param Request  $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $controllerClass = $this->getControllerClassName($request);
        $controllerAction = $request->action(true);

        // todo 转存匹配

        // todo 注解匹配
        $annotation = $this->parseAnnotation($controllerClass, $controllerAction);
        if ($annotation !== null) {
            $validateClass = $annotation->value;
            $validateScene = $annotation->scene;
            /** @var Validate $v */
            $v = new $validateClass();
            if ($validateScene) {
                // 自行决定使用何种场景
                if ('?' === $validateScene && $v instanceof AskSceneInterface) {
                    $validateScene = $v->askScene($request) ?: false;
                }
                // 选中验证场景
                $validateScene && $v->scene($validateScene);
            }
            $input = $request->param();
            if ($files = $request->file()) {
                $input += $files;
            }
            if (false === $v->check($input)) {
                // todo $this->app->make($this->errorHandle);
                $message = is_array($v->getError()) ? join(',', $v->getError()) : $v->getError();
                return Response::create($message, 'html', 400);
            }
            if ($v instanceof ValidateBase) {
                $request->withMiddleware([
                    'allow_input_fields' => $v->getRuleKeys(),
                ]);
            }
            return $next($request);
        } else {
            return $this->compatible($request, $next, $controllerClass, $controllerAction);
        }
    }

    /**
     * 兼容匹配
     * @param Request $request
     * @param Closure $next
     * @param         $controllerClass
     * @param         $controllerAction
     * @return Response
     */
    protected function compatible(Request $request, Closure $next, $controllerClass, $controllerAction): Response
    {
        if (!isset($this->mapping[$controllerClass])) {
            return $next($request);
        }
        $validateCfg = array_change_key_case($this->mapping[$controllerClass])[$controllerAction] ?? false;
        if (is_array($validateCfg)) {
            // 解析验证配置
            $validateCfg = array_pad($validateCfg, 3, null);
            if (is_string($validateCfg[0])) {
                [$validateClass, $validateScene] = $validateCfg;
            } else {
                [$validate_csrf, $validateClass, $validateScene] = $validateCfg;
            }

            // 验证输入数据
            if ($validateClass && class_exists($validateClass)) {
                /** @var Validate $v */
                $v = new $validateClass();
                if ($validateScene) {
                    // 自行决定使用何种场景
                    if ('?' === $validateScene && $v instanceof AskSceneInterface) {
                        $validateScene = $v->askScene($request) ?: false;
                    }
                    // 选中验证场景
                    $validateScene && $v->scene($validateScene);
                }
                $input = $request->param();
                if ($files = $request->file()) {
                    $input += $files;
                }
                if (false === $v->check($input)) {
                    $message = is_array($v->getError()) ? join(',', $v->getError()) : $v->getError();
                    return Response::create($message, 'html', 400);
                }
                if ($v instanceof ValidateBase) {
                    $request->withMiddleware([
                        'allow_input_fields' => $v->getRuleKeys(),
                    ]);
                }
            }
        }

        return $next($request);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    protected function getControllerClassName(Request $request): ?string
    {
        $suffix = $this->app->route->config('controller_suffix') ? 'Controller' : '';
        $controllerLayer = $this->app->route->config('controller_layer') ?: 'controller';

        $name = $request->controller();
        $class = $this->app->parseClass($controllerLayer, $name . $suffix);
        if (!class_exists($class)) {
            throw new HttpException(404, 'controller not exists:' . $class);
        }

        return $class;
    }
}
