<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Entity\User\User\ChangeEmail;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Token;
use App\Auth\Test\Builder\UserBuilder;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @covers User
 * @psalm-suppress PropertyNotSetInConstructor
 */
class RequestEmailChangingTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = (new UserBuilder())->active()->withEmail($old = new Email('old@email.test'))->build();

        $now = new DateTimeImmutable();

        $token = $this->createToken($now->modify('+1 day'));

        $user->requestEmailChanging($token, $now, $new = new Email('new@email.test'));

        self::assertEquals($token, $user->getNewEmailToken());
        self::assertEquals($old, $user->getEmail());
        self::assertEquals($new, $user->getNewEmail());
    }

    public function testSame(): void
    {
        $user = (new UserBuilder())->active()->withEmail($old = new Email('old@email.test'))->build();

        $now = new DateTimeImmutable();
        $token = $this->createToken($now->modify('+1 day'));

        $this->expectExceptionMessage('Email is already same.');
        $user->requestEmailChanging($token, $now, $old);
    }

    public function testAlready(): void
    {
        $user = (new UserBuilder())->active()->build();

        $now = new DateTimeImmutable();
        $token = $this->createToken($now->modify('+1 day'));

        $user->requestEmailChanging($token, $now, $email = new Email('new@email.test'));
        $this->expectExceptionMessage('Changing is already requested.');
        $user->requestEmailChanging($token, $now, $email);
    }

    public function testExpired(): void
    {
        $user = (new UserBuilder())->active()->build();

        $now = new DateTimeImmutable();
        $token = $this->createToken($now->modify('+1 hour'));
        $user->requestEmailChanging($token, $now, new Email('temp@email.test'));

        $newDate = $now->modify('+2 hours');
        $newToken = $this->createToken($newDate->modify('+1 hour'));
        $user->requestEmailChanging($newToken, $newDate, $newEmail = new Email('last@email.test'));

        self::assertEquals($newEmail, $user->getNewEmail());
        self::assertEquals($newToken, $user->getNewEmailToken());
    }

    public function testNotActive(): void
    {
        $now = new DateTimeImmutable();
        $token = $this->createToken($now->modify('+1 hour'));

        $user = (new UserBuilder())->build();

        $this->expectExceptionMessage('User is not active.');
        $user->requestEmailChanging($token, $now, new Email('new@email.test'));
    }

    public function createToken(DateTimeImmutable $modify): Token
    {
        return new Token(
            Uuid::uuid4()->toString(),
            $modify
        );
    }
}
