<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseFormat;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class BaseController extends Controller
{
    use ApiResponseFormat;

    protected $exception_statuses;

    public function __construct( array $exception_statuses = [] )
    {

        // Frequently thrown excpetions
        $this->exception_statuses = array_merge([
            ValidationException::class => 422,
            ModelNotFoundException::class => 404,
            QueryException::class => 400,
            UnauthorizedHttpException::class => 401,
        ], $exception_statuses);
    }

    public function handleException(object $exception): JsonResponse
    {
        return $this->errorResponse($this->getExceptionMessage($exception), $this->getExceptionStatus($exception));
    }

    public function getExceptionMessage(object $exception): string
    {
        switch(get_class($exception))
        {
            case ValidationException::class:
                $exception_message = json_encode($exception->errors());
                break;

            case ModelNotFoundException::class:
                $exception_message = "Not Found";
                break;

            case QueryException::class:
                $exception_message = $exception->errorInfo[1] == 1062 ? "Duplicate Entry" : $exception->getMessage();
                break;

            default:
                $exception_message = $exception->getMessage();
                break;
        }

        return $exception_message;
    }

    public function getExceptionStatus(object $exception): int
    {
        return $this->exception_statuses[get_class($exception)] ?? 500;
    }
}
