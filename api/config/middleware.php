<?php

declare(strict_types=1);

use Slim\App;
use App\Http;

return static function (App $app, \Psr\Container\ContainerInterface $container): void {
    /**
     * @psalm-var array{debug:bool}
     */
    $config = $container->get('config');
    $app->addErrorMiddleware($config['debug'], true, true);
};
