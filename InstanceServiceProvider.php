<?php

namespace App\Providers;

use App\Repository\Classes\CourseRepository;
use App\Services\CourseService;
use Illuminate\Support\ServiceProvider;
use Exception;

class InstanceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     * @throws Exception
     */
    public function boot()
    {
        $this->app->instance(CourseService::class,
            new CourseService(
                new CourseRepository($this->app)
            )
        );
    }
}
