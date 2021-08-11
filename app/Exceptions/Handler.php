<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Handler extends ExceptionHandler {

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register() {


        $this->reportable(function (InvalidOrderException $e) {
            return $this->render($request, $exception);
        });
    }
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function render($request,$exception)
    {
        if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
            $token = \Tymon\JWTAuth\Facades\JWTAuth::getToken();
            $newToken = \Tymon\JWTAuth\Facades\JWTAuth::refresh($token);
            

            $response_array = array(
                'status' => false,                
                'data' => ((object)[]),
                'message' => $exception->getMessage()                
            );
            return response($response_array, Response::HTTP_UNAUTHORIZED);
        } else if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {

            $response_array = array(
                'status' => false,                
                'data' => ((object)[]),
                'message' => $exception->getMessage()                
            );
            return response($response_array, Response::HTTP_BAD_REQUEST);
        } else if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenBlacklistedException) {
            
            $response_array = array(
                'status' => false,                
                'data' => ((object)[]),
                'message' => $exception->getMessage()                
            );
            return response($response_array, $exception->getStatusCode());

        } else if ($exception instanceof \ErrorException) {
           // return parent::render($request, $exception);
            $response_array = array(
                'status' => false,                
                'data' => ((object)[]),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line_number' => $exception->getLine()
            );

            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        } else if ($exception instanceof \Exception) {

            $message = $exception->getMessage();
            /*$pos = strpos($exception->getMessage(), "STATE[");
            if($pos){
                $message = trans('translate.SOMETHING_WENT_WRONG_TRY_AGAIN');
            }*/
            if($message == 'Unauthenticated.' || $message != ''){
                $response_array = array(
                    'status' => false,                
                    'data' => array(),
                    'message' => $message                
                );
                return response()->json($response_array, Response::HTTP_UNAUTHORIZED);
            }else{
                $response_array = array(
                    'status' => false,                
                    'data' => array(),
                    'message' => "Permission Denied."                
                );
                return response()->json($response_array, Response::HTTP_OK);
            }
            /*$message = $exception->getMessage();
            $response_array = array(
                'status' => false,                
                'data' => ((object)[]),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line_number' => $exception->getLine()           
            );

            return response()->json($response_array, Response::HTTP_BAD_REQUEST);*/
        }

        return parent::render($request, $exception);
    }
    
}
