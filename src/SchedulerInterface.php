<?php

namespace think\ThinkScheduler;

/**
 * 配置任务契约接口
 * @date: 2024/7/9
 */
interface SchedulerInterface
{

    /**
     * 任务配置
     * @date: 2024/7/9
     */
    public function cronConfig(): void;


}