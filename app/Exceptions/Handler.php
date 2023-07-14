<?php
  
  namespace App\Exceptions;
  
  use Illuminate\Auth\Access\AuthorizationException;
  use Illuminate\Database\Eloquent\ModelNotFoundException;
  use Illuminate\Validation\ValidationException;
  use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
  use Symfony\Component\HttpKernel\Exception\HttpException;
  use Throwable;
  
  use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
  
  use Illuminate\Http\Response;
  use Illuminate\Http\Request;
  
  class Handler extends ExceptionHandler
  {
    /**
    * A list of the exception types that should not be reported.
    *
    * @var array
    */
    protected $dontReport = [
      AuthorizationException::class,
      HttpException::class,
      ModelNotFoundException::class,
      ValidationException::class,
      ];
    
    /**
    * Report or log an exception.
    *
    * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
    *
    * @param  \Throwable  $exception
    * @return void
    *
    * @throws \Exception
    */
    public function report(Throwable $exception)
    {
      parent::report($exception);
    }
    
    /**
    * Render an exception into an HTTP response.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Throwable  $exception
    * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
    *
    * @throws \Throwable
    */
    public function render($request, Throwable $e)
    {
      if ($e instanceof DecryptException) {
        $retu = callback_return(false, 400, 'The data could not be decrypted');
      } else if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
        // Not found object/page
        $retu = callback_return(false, 404, 'Not Found');
      } else if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
        // Method not allowed
        $retu = callback_return(false, 405, 'Method Not Allowed');
      }else if ($this->isHttpException($e)) {
        $retu = callback_return(false, $e->getStatusCode(), 'Internal server error');
      }
      
      if (isset($retu)) {
        return $retu;
      }else {
        return parent::render($request, $e);
      }
    }
  }
