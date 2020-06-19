<?php

declare(strict_types=1);

namespace App\Auth\Service;

use DateTimeImmutable;

interface Tokenizer
{
    public function generate(DateTimeImmutable $date): Token;
}
