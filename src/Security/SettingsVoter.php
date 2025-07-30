<?php

namespace App\Security;

use App\Entity\Project;
use App\Entity\User;
use App\Service\ProjectScopeProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Tzunghaor\SettingsBundle\Model\SettingSectionAddress;

class SettingsVoter extends Voter
{
    public function __construct(
        private readonly ProjectScopeProvider $projectScopeProvider,
    ) {

    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof SettingSectionAddress && $attribute === 'edit';
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var SettingSectionAddress $subject - supports() already determined the type of $subject */

        // admins are allowed to edit all settings
        if (in_array('ROLE_ADMIN', $token->getRoleNames())) {
            return true;
        }

        // users are allowed to edit their own settings in 'project' collection
        if (in_array('ROLE_USER', $token->getRoleNames())) {
            if ($subject->getCollectionName() === null) {
                // null passed as collection name means "Is there any setting that the user can edit?"
                return true;
            }

            if ($subject->getCollectionName() !== 'project') {
                return false;
            }

            if ($subject->getScope() === null) {
                // null passed as scope means "Is there any setting that the user can edit in 'project' collection?"
                return true;
            }

            $object = $this->projectScopeProvider->getObject($subject->getScope());

            if ($object instanceof User) {
                $subjectUser = $object;
            } elseif ($object instanceof Project) {
                $subjectUser = $object->getOwner();
            }


            // We could make more granular decision based on $subject->getSectionName()

            return isset($subjectUser) && $subjectUser->getId() === $token->getUser()->getUserIdentifier();
        }

        return false;
    }
}