<?php
  
  require_once __DIR__.'/../vendor/autoload.php';
  
  (new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
  ))->bootstrap();
  
  date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));
  
  /*
  |--------------------------------------------------------------------------
  | Create The Application
  |--------------------------------------------------------------------------
  |
  | Here we will load the environment and create the application instance
  | that serves as the central piece of this framework. We'll use this
  | application as an "IoC" container and router for this framework.
  |
  */
  
  $app = new Laravel\Lumen\Application(
    dirname(__DIR__)
  );
  
  $app->setLocale('pl');

  //$app->register(Illuminate\Database\DatabaseServiceProvider::class);
  $app->withFacades();
  
  // $app->withEloquent();
  
  /*
  |--------------------------------------------------------------------------
  | Register Container Bindings
  |--------------------------------------------------------------------------
  |
  | Now we will register a few bindings in the service container. We will
  | register the exception handler and the console kernel. You may add
  | your own bindings here if you like or you can make another file.
  |
  */
  
  $app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
  );
  
  $app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
  );
  
  $app->singleton('cookie', function () use ($app) {
    return $app->loadComponent('session', 'Illuminate\Cookie\CookieServiceProvider', 'cookie');
  });

  use League\CommonMark\CommonMarkConverter;

  $app->singleton('markdown', function () {
      return new CommonMarkConverter();
  });

  if (!function_exists('markdown')) {
      function markdown($text)
      {
          return app('markdown')->convertToHtml($text);
      }
  }
  
  $app->bind('Illuminate\Contracts\Cookie\QueueingFactory', 'cookie');
  
  /*
  |--------------------------------------------------------------------------
  | Register Config Files
  |--------------------------------------------------------------------------
  |
  | Now we will register the "app" configuration file. If the file exists in
  | your configuration directory it will be loaded; otherwise, we'll load
  | the default version. You may register other files below as needed.
  |
  */
  $app->configure('app');
  $app->instance('path.config', app()->basePath() . DIRECTORY_SEPARATOR . 'config');
  $app->instance('path.public', app()->basePath() . DIRECTORY_SEPARATOR . 'public');
  $app->instance('path.storage', app()->basePath() . DIRECTORY_SEPARATOR . 'storage');
  $app->instance('path.resources', app()->basePath() . DIRECTORY_SEPARATOR . 'resources');
  $app->instance('path.lang', app()->basePath() . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang');

  // Utworzenie FileViewFinder i przekazanie ścieżek
  $app->bind('view.finder', function ($app) {
      return new Illuminate\View\FileViewFinder($app['files'], $app['config']['view.paths']);
  });
  
  /*
  |--------------------------------------------------------------------------
  | Register Middleware
  |--------------------------------------------------------------------------
  |
  | Next, we will register the middleware with the application. These can
  | be global middleware that run before and after each request into a
  | route or middleware that'll be assigned to some specific routes.
  |
  */
  
  $app->middleware([
       App\Http\Middleware\ExampleMiddleware::class,
       App\Http\Middleware\HtmlPurifierMiddleware::class,
  ]);
  
  $app->routeMiddleware([
      'auth' => App\Http\Middleware\Authenticate::class,
      'htmlpurifier' => App\Http\Middleware\HtmlPurifierMiddleware::class,
 ]);
 // $app->loadMigrationsFrom(__DIR__.'/../database/migrations');
  /*
  |--------------------------------------------------------------------------
  | Register Service Providers
  |--------------------------------------------------------------------------
  |
  | Here we will register all of the application's service providers which
  | are used to bind services into the container. Service providers are
  | totally optional, so you are not required to uncomment this line.
  |
  */
  
  
  
  $app->register(App\Providers\AppServiceProvider::class);
  
  $app->register(\Illuminate\Auth\AuthServiceProvider::class);

  $app->register(App\Providers\EventServiceProvider::class);
 // $app->register(\Illuminate\View\ViewServiceProvider::class);
  $app->register(Illuminate\Translation\TranslationServiceProvider::class);

  /*
  |--------------------------------------------------------------------------
  | Load The Application Routes
  |--------------------------------------------------------------------------
  |
  | Next we will include the routes file so that they can all be added to
  | the application. This will provide all of the URLs the application
  | can respond to, as well as the controllers that may handle them.
  |
  */
  $app->register(\Illuminate\Mail\MailServiceProvider::class);


  $app->alias('mail.manager', Illuminate\Mail\MailManager::class);
  $app->alias('mail.manager', Illuminate\Contracts\Mail\Factory::class);

  $app->alias('mailer', Illuminate\Mail\Mailer::class);
  $app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
  $app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);
  

  $app->router->group(['namespace' => 'App\Http\Controllers', ], function ($router) {
    require __DIR__.'/../routes/web.php';
  });
  

  return $app;

  
  $app->configure('mail');

  

 
  //
