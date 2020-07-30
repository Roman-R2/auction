<?php

declare(strict_types=1);

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

//Достаем из билдера валидатор, который будет использовать аннотации,
// и возвращаем его в наш код
return [
    ValidatorInterface::class => function (): ValidatorInterface {
        /** @psalm-suppress DeprecatedMethod */
        AnnotationRegistry::registerLoader('class_exists');

        return Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
    },
];
