<?php

declare(strict_types=1);

namespace App\Auth\Command\JoinByEmail\Comfirm;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\User;
use App\Auth\Entity\User\UserRepository;
use App\Flusher;
use DateTimeImmutable;
use DomainException;

class Handler
{
    private UserRepository $users;
    private Flusher $flusher;

    public function __construct(
        UserRepository $users,
        Flusher $flusher
    ) {
        $this->users = $users;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        if (!$user = $this->users->findByConfirmToken($command->token)) {
            throw new DomainException('Incorrect token.');
        }

        $user->confirmJoin($command->token, new DateTimeImmutable());

        $this->flusher->flush();
    }
}
