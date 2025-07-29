<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Project;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Tzunghaor\SettingsBundle\Model\Item;
use Tzunghaor\SettingsBundle\Service\ScopeProviderInterface;

class ProjectScopeProvider implements ScopeProviderInterface
{
    private const PREFIX_USER = 'user';
    private const PREFIX_PROJECT = 'proj';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ) {
    }

    public function getScope(mixed $subject = null): Item
    {
        // return default scope name if $subject is null
        if ($subject === null) {
            return $this->getDefaultScope();
        }

        // if $subject is string, then it is already a scope name
        if (is_string($subject)) {
            return new Item($subject);
        }

        // we can provide the scope of User and Project
        if ($subject instanceof User) {
            return new Item(self::PREFIX_USER . '-' . $subject->getId());
        } elseif ($subject instanceof Project) {
            return new Item(self::PREFIX_PROJECT . '-' . $subject->getName());
        }

        throw new \InvalidArgumentException('Cannot determine scope');
    }

    public function getScopePath(mixed $subject = null): array
    {
        // get default scope name if $subject is null
        $subject = $subject ?? $this->getDefaultScope()->getName();

        // if $subject is string, then it is already a scope name
        if (is_string($subject)) {
            [$prefix, $identifier] = explode('-', $subject, 2);

            // 'user' scopes don't have parent scope
            if ($prefix === self::PREFIX_USER) {
                return [];
            }

            // if it is not a user, then it is a project: its parent is its owner
            $subject = $this->em->find(Project::class, $identifier);
        }

        // we can provide the scope path of Project instances
        if ($subject instanceof Project) {
            return [self::PREFIX_USER . '-' . $subject->getOwner()->getId()];
        }

        throw new \InvalidArgumentException('Cannot determine scope path');
    }

    public function getScopeDisplayHierarchy(?string $searchString = null): array
    {
        // todo: actually search, use roles
        $users = $this->em->getRepository(User::class)->findAll();
        $displayHierarchy = [];
        foreach ($users as $user) {
            $displayHierarchy[] = new Item(self::PREFIX_USER . '-' . $user->getId(), $user->getId());
        }

        return $displayHierarchy;
    }

    /**
     * @throws RuntimeException if there is no "authenticated" user
     */
    private function getDefaultScope(): Item
    {
        $user = $this->security->getUser();
        $userId = $user instanceof UserInterface ? $user->getUserIdentifier() : null;

        if ($userId === null) {
            throw new RuntimeException('There is no default project scope without authenticated user.');
        }

        return new Item(self::PREFIX_USER . '-' . $userId);
    }

}