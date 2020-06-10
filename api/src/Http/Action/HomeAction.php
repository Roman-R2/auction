<?php

declare(strict_types=1);

namespace App\Http\Action;

use App\Http;
use App\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

class HomeAction implements RequestHandlerInterface
{
    public function handle (ServerRequestInterface $request) : ResponseInterface
    {
        return new JsonResponse(new StdClass());
    }
}
