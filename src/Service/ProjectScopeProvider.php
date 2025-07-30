<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function getScopeDisplayHierarchy(?string $searchString = null): array
    {
        $authenticatedUser = $this->security->getUser();
        if (!$authenticatedUser || !in_array('ROLE_USER', $authenticatedUser->getRoles())) {
            return [];
        }

        $likeExpr = empty($searchString) ? null : '%' . addcslashes($searchString, '%_') . '%';
        $isAdmin = in_array('ROLE_ADMIN', $authenticatedUser->getRoles());

        $query = $this->em->createQueryBuilder()
            ->select(['p', 'u'])
            ->from(User::class, 'u')
            ->leftJoin('u.projects', 'p')
            ->orderBy('u.id')
            ->addOrderBy('p.name')
        ;

        if ($likeExpr) {
            $query
                ->andWhere('p.name LIKE :likeExpr OR u.id LIKE :likeExpr')
                ->setParameter('likeExpr', $likeExpr)
            ;
        }
        if (!$isAdmin) {
            $query
                ->andWhere('u.id = :userId')
                ->setParameter('userId', $authenticatedUser->getUserIdentifier())
            ;
        }

        $users = $query->getQuery()->getResult();
        $displayHierarchy = [];
        /** @var User $user */
        foreach ($users as $user) {
            $children = [];
            foreach ($user->getProjects() as $project) {
                $children[] = new Item(self::PREFIX_PROJECT . '-' . $project->getName(), $project->getName(), extra: [Item::EXTRA_EDITABLE => true]);
            }

            $displayHierarchy[] = new Item(self::PREFIX_USER . '-' . $user->getId(), $user->getId(), $children, [Item::EXTRA_EDITABLE => true]);

        }

        return $displayHierarchy;
    }

    /**
     * Method for SettingsVoter to convert scope to an entity.
     *
     * We could instead implement IsGrantedSupportingScopeProviderInterface in this class, and make a Voter
     * that votes directly on User|Project as $subject - but then we lose the possibility to vote based on
     * settings section name (e.g. ContentSettings / DisplaySettings).
     */
    public function getObject(string $scope): User|Project|null
    {
        [$prefix, $identifier] = explode('-', $scope, 2);

        return match ($prefix) {
            self::PREFIX_USER => $this->em->find(User::class, $identifier),
            self::PREFIX_PROJECT => $this->em->find(Project::class, $identifier),
            default => null,
        };
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