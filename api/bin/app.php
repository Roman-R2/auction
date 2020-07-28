<?php

declare(strict_types=1);

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Tools\Console\Helper\ConfigurationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

require __DIR__ . '/../vendor/autoload.php';

if (getenv('SENTRY_DSN')) {
    Sentry\init(['dsn' => getenv('SENTRY_DSN')]);
}

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';

$cli = new Application('Console');

//Отключаем отлов ошибок у консольного приложения symfony если указана нужная переменная окружения,
// так как мы используем sentry.
//А если ошибки обрабатываются в нашем коде, то до sentry они не доходят
if (getenv('SENTRY_DSN')) {
    $cli->setCatchExceptions(false);
}

/**
 * @var string[] $commands
 * @psalm-suppress MixedArrayAccess
 */
$commands = $container->get('config')['console']['commands'];


//Достаем из контейнера внедрения зависимостей $entityManager
/** @var EntityManagerInterface $entityManager */
$entityManager = $container->get(EntityManagerInterface::class);

$connection = $entityManager->getConnection();

//Настройки для doctrine migrations
//https://www.doctrine-project.org/projects/doctrine-migrations/en/2.2/reference/custom-configuration.html#custom-configuration
$configuration = new Configuration($connection);
$configuration->setMigrationsDirectory(__DIR__ . '/../src/Data/Migration');
$configuration->setMigrationsNamespace('App\Data\Migration');
$configuration->setMigrationsTableName('migrations');
$configuration->setAllOrNothing(true);
$configuration->setCheckDatabasePlatform(false);

//Берем getHelperSet из нашего консольного прилоения и под именем 'em' передаем туда наш $entityManager.
//Мы можем это седелать, так как используем doctrine и symfony console
$cli->getHelperSet()->set(new EntityManagerHelper($entityManager), 'em');
//Прокидываем в getHelperSet сделанный нами объект configuration, для того,
// чтобы команды doctrine migrations в cli могли исспользоваться
$cli->getHelperSet()->set(new ConfigurationHelper($connection, $configuration), 'configuration');

//Doctrine\Migrations\Tools\Console\ConsoleRunner::addCommands($cli);

foreach ($commands as $name) {
    /** @var Command $command */
    $command = $container->get($name);
    $cli->add($command);
}
$cli->run();
