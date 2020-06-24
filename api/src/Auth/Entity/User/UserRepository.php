<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

use DomainException;

interface UserRepository
{
    public function hasByEmail(Email $email): bool;
    public function hasByNetwork(Network $network): ?User;
    public function findByConfirmToken(string $token): ?User;
    /**
     * @param Id $param
     * @return User
     * @throws DomainException
     */
    public function get(Id $param): User;
    public function add(User $user): void;

}
