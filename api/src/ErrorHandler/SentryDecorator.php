<?php

declare(strict_types=1);

namespace App\ErrorHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\ErrorHandlerInterface;
use Throwable;

use function Sentry\captureException;

class SentryDecorator implements ErrorHandlerInterface
{
    private ErrorHandlerInterface $next;

    public function __construct(ErrorHandlerInterface $next)
    {
        $this->next = $next;
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        //Ради этой строчки мы и переопределили метод __invoke класса ErrorHandlerInterface
        //чтобы в sentry передавались все логи
        captureException($exception);

        return ($this->next)(
            $request,
            $exception,
            $displayErrorDetails,
            $logErrors,
            $logErrorDetails
        );
    }
}
