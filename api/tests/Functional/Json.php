<?php

declare(strict_types=1);

namespace Test\Functional;

class Json
{
    /**
     * @param string $string
     * @return array
     */
    public static function decode(string $string): array
    {
        /** @var array */
        return json_decode($string, true, 512, JSON_THROW_ON_ERROR);
    }
}
