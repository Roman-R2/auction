<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

use ArrayObject;
use DateTimeImmutable;
use DomainException;

class User
{

    private Id $id;
    private DateTimeImmutable $date;
    private Email $email;
    private ?string $passwordHash = null;
    private ?Token $joinConfirmToken = null;
    private ?Token $passwordResetToken = null;
    private Status $status;
    private ArrayObject $networks;

    private function __construct(
        Id $id,
        DateTimeImmutable $date,
        Email $email,
        Status $status
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->email = $email;
        $this->status = $status;
        $this->networks = new ArrayObject();
    }

    public static function requestJoinByEmail(
        Id $id,
        DateTimeImmutable $date,
        Email $email,
        string $passwordHash,
        Token $token
    ): self {
        $user = new self($id, $date, $email, Status::wait());
        $user->passwordHash = $passwordHash;
        $user->joinConfirmToken = $token;
        return $user;
    }

    public static function joinByNetwork(
        Id $id,
        DateTimeImmutable $date,
        Email $email,
        Network $network
    ): self {
        $user = new self($id, $date, $email, Status::active());
        $user->networks->append($network);
        return $user;
    }

    public function resetPassword(string $token, DateTimeImmutable $date, string $hash): void
    {
        if ($this->passwordResetToken === null) {
            throw new DomainException('Resetting is not requested.');
        }
        $this->passwordResetToken->validate($token, $date);
        $this->passwordResetToken = null;
        $this->passwordHash = $hash;
    }

    public function attachNetwork(Network $network): void
    {
        /** @var Network $existing */
        foreach ($this->networks as $existing) {
            if ($existing->isEqualTo($network)) {
                throw new DomainException('Network is already attached.');
            }
        }
        $this->networks->append($network);
    }

    public function confirmJoin(string $token, DateTimeImmutable $date): void
    {
        if ($this->joinConfirmToken === null) {
            throw new DomainException('Confirmation is not required.');
        }
        $this->joinConfirmToken->validate($token, $date);
        $this->status = Status::active();
        $this->joinConfirmToken = null;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isWait(): bool
    {
        return $this->status->isWait();
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function getJoinConfirmToken(): ?Token
    {
        return $this->joinConfirmToken;
    }

    public function getPasswordResetToken(): ?Token
    {
        return $this->passwordResetToken;
    }

    /**
     * @return Network[]
     */
    public function getNetworks(): array
    {
        /** @var Network[] */
        return $this->networks->getArrayCopy();
    }

    public function requestPasswordReset(Token $token, DateTimeImmutable $date): void
    {
        if (!$this->status->isActive()) {
            throw new DomainException('User is not active.');
        }
        if ($this->passwordResetToken !== null && !$this->passwordResetToken->isExpiredTo($date)) {
            throw new DomainException('Resetting is already request.');
        }
        $this->passwordResetToken = $token;
    }
}
