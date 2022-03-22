<?php

namespace KagaDorapeko\Laravel\Aliyun\Sms;

use Illuminate\Support\ServiceProvider;

class AliyunSmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/aliyun-sms.php', 'aliyun-sms');

        $this->app->singleton(AliyunSmsService::class, function () {
            return new AliyunSmsService;
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/aliyun-sms.php' => config_path('aliyun-sms.php'),
            ], 'laravel-aliyun-sms-config');
        }
    }
}