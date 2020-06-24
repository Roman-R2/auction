<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Entity\User\User\JoinByEmail;

use App\Auth\Entity\User\Token;
use App\Auth\Test\Builder\UserBuilder;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ConfirmTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = (new UserBuilder())
            ->withJoinConfirmToken($token = $this->createToken())
            ->build();

        self::assertFalse($user->isActive());
        self::assertTrue($user->isWait());

        $user->confirmJoin(
            $token->getValue(),
            $token->getExpires()->modify('-1 day')
        );

        self::assertTrue($user->isActive());
        self::assertFalse($user->isWait());

        self::assertNull($user->getJoinConfirmToken());
    }

    public function testWrong(): void
    {
        $user = (new UserBuilder())
            ->withJoinConfirmToken($token = $this->createToken())
            ->build();

        $this->expectErrorMessage('Token is invalid.');

        $user->confirmJoin(
            Uuid::uuid4()->toString(),
            $token->getExpires()->modify('-1 day')
        );
    }

    public function testExpired(): void
    {
        $user = (new UserBuilder())
            ->withJoinConfirmToken($token = $this->createToken())
            ->build();

        $this->expectErrorMessage('Token is expired.');

        $user->confirmJoin(
            $token->getValue(),
            $token->getExpires()->modify('+1 day')
        );
    }

    public function testAlready(): void
    {
        $user = (new UserBuilder())
            ->withJoinConfirmToken($token = $this->createToken())
            ->active()
            ->build();

        $this->expectErrorMessage('Confirmation is not required.');

        $user->confirmJoin(
            $token->getValue(),
            $token->getExpires()->modify('-1 day')
        );
    }

    private function createToken(): Token
    {
        return new Token(
            Uuid::uuid4()->toString(),
            new DateTimeImmutable('+1 day')
        );
    }
}
