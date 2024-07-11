# think-scheduler cron

#### 介绍
thinkphp5 计划任务调度 scheduler cron.   
简单实现精简版计划任务调度  
解决多个计划任务时需要频繁修改服务器cron问题  
在程序内实现管理任务调度

#### 软件架构
php >=7.1  
依赖于thinkphp5.0框架  
依赖于thinkphp 命令行


#### 安装教程
`composer require cronthink/scheduler `

#### 使用说明

1.  安装完成后,首先执行命令初始化:

```
php think cron init
```
执行命令后会在项目根目录下生成样例文件  `根目录/scheduler/CronConfig.php`

```


<?php

namespace scheduler;

use think\Console;
use think\console\Input;
use think\console\Output;
use think\Exception;
use think\ThinkScheduler\ExceptionScheduler;
use think\ThinkScheduler\Scheduler;
use think\ThinkScheduler\SchedulerInterface;
use Throwable;

/**
 * Class CronConfig
 */
class CronConfig implements SchedulerInterface
{

    /**
     * @var Scheduler
     */
    protected $scheduler;

    /**
     * @var Input
     */
    protected $input;

    /**
     * @var Output
     */
    protected $output;


    /**
     * 执行错误日志
     * 可自定义重写
     * @var string
     */
    public $errorLogPath = LOG_PATH . 'cron/cron-error.log';


    /**
     * @param Scheduler $scheduler
     * @param Input $input
     * @param Output $output
     */
    public function __construct(Scheduler $scheduler, Input $input, Output $output)
    {
        $this->scheduler = $scheduler;
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * Fixme: 配置cron任务
     * @return void
     * @throws Exception
     */
    public function cronConfig(): void
    {
        $this->scheduler->expression('*/2 * * * *')
            ->call(function () {
                $this->output->info(PHP_EOL . '===== callback --- 复杂逻辑调用类方法 Class::action() =====');
            })
            ->desc('任务描述')
            ->save();

        $this->scheduler->expression('*/2 * * * *')
            ->call(function () {
                $res = Console::call('list', ['-h'])->fetch();
                $this->output->info(PHP_EOL . '===== callback --- 调用命令行 list, [-h] =====' . $res);
            })
            ->desc('任务描述2')
            ->save();

        $this->scheduler->expression('*/2 * * * *')
            ->call(function () {
                $this->output->info(PHP_EOL . '===== callback 单纯闭包编写简单逻辑 =====');
            })
            ->desc('任务描述3')
            ->save();

        $this->scheduler->expression('*/2 * * * *')
            ->call(function () {
                /**
                 * Fixme: 1.多个任务执行时 某一任务出现错误或异常会写入日志 默认不会终止程序后续任务继续执行
                 *        2. 如果想打断程序后续任务终止运行 可自己捕获异常 用 ExceptionScheduler 类抛出
                 */
                try {

                    //处理逻辑

                } catch (Throwable $exception) {
                    throw new ExceptionScheduler('错误信息, 将会终止执行');
                }

            })
            ->desc('任务描述4')
            ->save();
    }

}
```

2.  调用 $this->scheduler 下的方法 expression() cron表达式
3.  调用 $this->scheduler 下的方法 call() 内写逻辑
4.  调用 $this->scheduler 下的方法 desc() 任务描述用于区分任务
5.  调用 $this->scheduler 下的方法 save() 必须最后调用 保存验证配置
6.  可修改类中属性 $errorLogPath 修改执行错误日志路径 
7.  `php think cron start` 启动运行  
8.  `php think cron list`  查看已配置任务列表  
7.  服务器设置计划任务 `php think cron start` 一分钟执行一次(crontab默认最短一分钟)  

#### 参与贡献

1.  依赖包 `dragonmantank/cron-expression": "^3.3` 用于分析cron表达式

