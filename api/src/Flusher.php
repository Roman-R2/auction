<?php

declare(strict_types=1);

namespace App;

use Doctrine\ORM\EntityManager;

class Flusher
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {

        $this->em = $em;
    }

    public function flush(): void
    {
        $this->em->flush();
    }
}
