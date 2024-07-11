<?php

namespace think\ThinkScheduler\command;


use Cron\CronExpression;
use think\Console;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\Exception;
use think\ThinkScheduler\ExceptionScheduler;
use think\ThinkScheduler\Scheduler;
use think\ThinkScheduler\SchedulerInterface;
use Throwable;


/**
 * cron 计划任务调度类
 * @date: 2024/7/9
 * Class Cron
 * @package think\ThinkScheduler\command
 */
class Cron extends Command
{


    /**
     * @date: 2024/7/9
     */
    protected function configure()
    {
        $this->setName('cron')
            ->addArgument('status', Argument::REQUIRED, 'init:初始化 start:启动 list:查看所有任务列表')
            ->setDescription('cron 计划任务调度器');
    }


    /**
     * @param Input $input
     * @param Output $output
     * @throws Exception
     * @throws Throwable
     * @date: 2024/7/9
     */
    protected function execute(Input $input, Output $output)
    {
        $status = $input->getArgument('status');
        $path = ROOT_PATH . 'scheduler';
        $filename = 'CronConfig.php';
        $filePath = $path . DS . $filename;
        if ($status == 'init') {
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            $contents = file_get_contents($this->_getTemplate());
            if (false === file_put_contents($filePath, $contents)) {
                throw new ExceptionScheduler(sprintf('The file "%s" could not be written to', $path));
            }
            $output->info('scheduler init success');
            return true;
        }

        include $filePath;
        $cronConfig = new \scheduler\CronConfig(new Scheduler(), $input, $output);
        if (!$cronConfig instanceof SchedulerInterface) {
            throw new ExceptionScheduler('\scheduler\CronConfig  must implement an interface class ' . SchedulerInterface::class);
        }
        $cronConfig->cronConfig();
        if ($status == 'start') {
            /**
             * @var $list Scheduler
             */
            foreach (Scheduler::getList() as $list) {
                try {
                    $CronExpression = new CronExpression($list->expression);
                    if ($CronExpression->isDue()) {
                        $callback = $list->callback;
                        $callback();
                    }
                } catch (ExceptionScheduler $exception) {
                    throw $exception;
                } catch (Throwable $exception) {
                    $this->_log(['taskInfo' => $list->getCurrent(), 'exception' => $exception], $cronConfig->errorLogPath);
                }

            }
        } else {
            $output->info(print_r(Scheduler::getListAll(), true));
        }
    }


    /**
     * 写入错误日志
     * @param $log
     * @return false|int
     * @date: 2024/7/9
     */
    protected function _log($log, $path)
    {
        return $this->customLog(print_r($log, true), $path, true);
    }

    /**
     * @param $log
     * @param $filename
     * @param bool $isAppend
     * @return false|int
     * @date: 2024/7/9
     */
    public function customLog($log, $filename, bool $isAppend = true)
    {
        $dirname = pathinfo($filename, PATHINFO_DIRNAME);
        if (!file_exists($dirname)) {
            mkdir($dirname, 0755, true);
        }
        $log = date('Y-m-d H:i:s') . ': ' . $log . PHP_EOL;
        return file_put_contents($filename, $log, $isAppend ? FILE_APPEND : 0);
    }

    /**
     * @return string
     * @date: 2024/7/9
     */
    protected function _getTemplate()
    {
        return __DIR__ . '/stubs/cronConfig.stub';
    }

}
