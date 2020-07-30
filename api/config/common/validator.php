<?php

declare(strict_types=1);

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

//Достаем из билдера валидатор, который будет использовать аннотации,
// и возвращаем его в наш код
return [
    ValidatorInterface::class => function (): ValidatorInterface {
        //Вызываем этот метод, так как у нас doctrine 2, и она не может просто так
        // подкгрузить аннотации из наших комманд, так что мы делаем это здесь
        /** @psalm-suppress DeprecatedMethod */
        AnnotationRegistry::registerLoader('class_exists');

        return Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
    },
];
