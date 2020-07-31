<?php

declare(strict_types=1);

use Doctrine\Common\Annotations\AnnotationRegistry;
use Psr\Container\ContainerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

//Достаем из билдера валидатор, который будет использовать аннотации,
// и возвращаем его в наш код
return [
    ValidatorInterface::class => function (ContainerInterface $container): ValidatorInterface {
        //Вызываем этот метод, так как у нас doctrine 2, и она не может просто так
        // подкгрузить аннотации из наших комманд, так что мы делаем это здесь
        /** @psalm-suppress DeprecatedMethod */
        AnnotationRegistry::registerLoader('class_exists');

        //Достаем наш переводчик, чтобы добавить его в валидатор
        /** @var TranslatorInterface $translator */
        $translator = $container->get(TranslatorInterface::class);


        return Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->setTranslator($translator)
            ->setTranslationDomain('validators')
            ->getValidator();
    },
];
