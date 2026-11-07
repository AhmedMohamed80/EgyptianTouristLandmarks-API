<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request; 

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Request::macro('convertPutToPost', function () {
            if ($this->isMethod('PUT')) {
                $this->request->add($_POST);
                foreach ($_FILES as $key => $file) {
                    $this->files->set($key, $file);
                }
            }
        });

        app('request')->convertPutToPost();
    }
}
