<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: "string")]
    private string $id;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getRoles(): array
    {
        return $this->id === 'Alice' ? ['ROLE_ADMIN', 'ROLE_USER'] : ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // nothing to do
    }

    public function getUserIdentifier(): string
    {
        return $this->id;
    }
}