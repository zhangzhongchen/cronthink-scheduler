<?php
namespace think\ThinkScheduler;

use Closure;
use Cron\CronExpression;
use think\Exception;

/**
 * 任务调度参数类
 * @date: 2024/7/9
 * Class Scheduler
 * @package app\admin\command\Cron
 */
class Scheduler
{

    /**
     * cron 表达式
     * @var string
     */
    public $expression;

    /**
     * 闭包
     * @var Closure
     */
    public $callback;

    /**
     * 描述
     * @var string
     */
    public $desc;

    /**
     * 所有任务列表
     * @var array
     */
    protected static $list;


    /**
     * 设置表达式
     * @param $expression
     * @return Scheduler
     * @date: 2024/7/9
     */
    public function expression($expression)
    {
        $this->expression = $expression;
        return $this;
    }


    /**
     * 设置执行闭包方法
     * @param $callback
     * @return Scheduler
     * @date: 2024/7/9
     */
    public function call($callback)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * 描述信息
     * @param $desc
     * @return $this
     * @date: 2024/7/9
     */
    public function desc($desc)
    {
        $this->desc = $desc;
        return $this;
    }

    /**
     * 保存配置准备执行
     * @return $this
     * @throws Exception
     * @date: 2024/7/9
     */
    public function save()
    {
        new CronExpression($this->expression);

        if (!$this->callback instanceof Closure) {
            throw new Exception('Must implement closure method call()');
        }

        if (!$this->desc) {
            throw new Exception('Missing desc value');
        }

        $oldThis = clone $this;
        self::$list[] = $oldThis;
        $this->reset();
        return $oldThis;
    }

    /**
     * 获取所有任务数组调度类
     * @return array
     * @date: 2024/7/9
     */
    public static function getList()
    {
        $list = self::$list;
        self::$list = [];
        return $list;
    }

    /**
     * 重置属性值
     * @date: 2024/7/9
     */
    public function reset()
    {
        $this->expression = null;
        $this->callback = null;
        $this->desc = null;
        return $this;
    }

    /**
     * 获取所有任务数组
     * @return array
     * @date: 2024/7/9
     */
    public static function getListAll()
    {
        $data = [];
        foreach (self::$list as $list) {
            $data[] = self::_getObjectVars($list);
        }
        return $data;
    }

    /**
     * 获取当前配置数组
     * @return array
     * @date: 2024/7/9
     */
    public function getCurrent()
    {
        return self::_getObjectVars($this);
    }

    /**
     * 获取类中的public属性
     * @return array
     * @date: 2024/7/9
     */
    protected static function _getObjectVars($object)
    {
        $vars = get_object_vars($object);
        $vars['callback'] = $vars['callback'] instanceof Closure ? Closure::class . ' function () {}' : '';
        return $vars;
    }

}