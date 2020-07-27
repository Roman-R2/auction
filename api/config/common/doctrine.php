<?php

declare(strict_types=1);

use App\Auth;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\DBAL\Types\Type;
use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\Tools\Setup;
use Psr\Container\ContainerInterface;

return [
    //Контейнер, при вызове EntityManager выполнит эту анонимную функцию один раз,
    //сохранит объект внутри себя и потом уже будет возвращать этот объект
    EntityManagerInterface::class => function (ContainerInterface $container): EntityManagerInterface {
        /**
         * @psalm-suppress MixedArrayAccess
         * @psalm-var array{
         *     metadata_dirs:array,
         *     dev_mode:bool,
         *     proxy_dir:string,
         *     cache_dir:?string,
         *     types:array<string,string>,
         *     connection:array
         * } $settings
         */
        $settings = $container->get('config')['doctrine'];

        //Создаем конфигурацияю для doctrine
        //с помошью фабрики createAnnotationMetadataConfiguration, передавая туда нужные параметры
        $config = Setup::createAnnotationMetadataConfiguration(
            $settings['metadata_dirs'],
            $settings['dev_mode'],
            $settings['proxy_dir'],
            $settings['cache_dir'] ? new FilesystemCache($settings['cache_dir']) : new ArrayCache(),
            false
        );

        //Устанавливаем стратегию именования полей в БД из имен полей сущьностей
        //для их автоматической конвертации в_такой_формат
        $config->setNamingStrategy(new UnderscoreNamingStrategy());

        //Прохрдим по массиву types из конфигурации, который содержит наши кастомные типы для сушности
        //и добавляем их в доктрину, чтобы они в ней были зарегистрированны
        foreach ($settings['types'] as $name => $class) {
            if (!Type::hasType($name)) {
                Type::addType($name, $class);
            }
        }

        //Подключаем к doctrine слушателя из FixDefaultSchemaSubscriber.php
        // и добавляем его в EntityManager
        $eventManager = new EventManager();

        /**
         * @psalm-suppress InvalidArrayOffset
         * @psalm-suppress MixedAssignment
         * @psalm-suppress MixedArgument
         */
        foreach ($settings['subscribers'] as $name) {
            /** @var EventSubscriber $subscriber */
            $subscriber = $container->get($name);
            $eventManager->addEventSubscriber($subscriber);
        }

        return EntityManager::create(
            $settings['connection'],
            $config,
            $eventManager
        );
    },

    'config' => [
        'doctrine' => [
            'dev_mode' => false,
            'cache_dir' => __DIR__ . '/../../var/cache/doctrine/cache',
            'proxy_dir' => __DIR__ . '/../../var/cache/doctrine/proxy',
            'connection' => [
                'driver' => 'pdo_pgsql',
                'host' => getenv('DB_HOST'),
                'user' => getenv('DB_USER'),
                'password' => getenv('DB_PASSWORD'),
                'dbname' => getenv('DB_NAME'),
                'charset' => 'utf-8'
            ],
            'subscribers' => [],
            'metadata_dirs' => [
                __DIR__ . '/../../src/Auth/Entity',
            ],
            'types' => [
                Auth\Entity\User\IdType::NAME => Auth\Entity\User\IdType::class,
                Auth\Entity\User\EmailType::NAME => Auth\Entity\User\EmailType::class,
                Auth\Entity\User\RoleType::NAME => Auth\Entity\User\RoleType::class,
                Auth\Entity\User\StatusType::NAME => Auth\Entity\User\StatusType::class,
            ],
        ],
    ],
];
