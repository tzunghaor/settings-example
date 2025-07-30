<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\User;
use App\Settings\One\ContentSettings;
use App\Settings\One\DisplaySettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tzunghaor\SettingsBundle\Service\SettingsService;

class AppController extends AbstractController
{
    private const SESSION_KEY_TIME = 'time';

    #[Route(path: '/', name: 'index')]
    public function index(
        EntityManagerInterface $em
    ): Response {
        $users = $em->getRepository(User::class)->findAll();
        $loginLinks = [];
        foreach ($users as $user) {
            $loginLinks[$user->getUserIdentifier()] = '/login/' . $user->getUserIdentifier();
        }

        // determine initial collection for edit settings iframe
        $roles = $this->getUser()?->getRoles();
        if (empty($roles)) {
            // no logged in user => no collection, results in empty editor
            $collection = null;
        } elseif (in_array('ROLE_ADMIN', $roles)) {
            // admin => no collection, results in 'default' collection selected
            $collection = null;
        } else {
            // non admin user has permission only ro 'project' collection
            $collection = 'project';
        }

        return $this->render('index.html.twig', [
            'loginLinks' => $loginLinks,
            'collection' => $collection,
        ]);
    }

    #[Route('/login/{userId}', name: 'login', defaults: ['userId' => ''])]
    public function login(string $userId, Security $security, EntityManagerInterface $em): Response
    {
        if ($userId) {
            $user = $em->find(User::class, $userId);
            $security->login($user);
        } else {
            $security->logout(false);
        }

        return $this->redirectToRoute('index');
    }

    /**
     * Authentication is actually done with the login action above, but Symfony needs an authenticator in
     * security.yaml, and login_check seemed to be the easiest to set up
     */
    #[Route('/login_check', name: 'login_check')]
    public function loginCheck(): Response
    {
        throw new \Exception('This should not be reached');
    }


    #[Route(path: '/example/{project}', name: 'example', defaults: ['project' => null])]
    public function example(
        ?Project $project,
        Request $request,
        SettingsService $defaultSettingsService,
        SettingsService $projectSettingsService,
        EntityManagerInterface $em,
    ): Response {
        // The 'default' collection's label is 'Time of Day' - let's determine which scope of it should we use
        $session = $request->getSession();
        $timeString = $session->has(self::SESSION_KEY_TIME) ? $session->get(self::SESSION_KEY_TIME) : 'now';
        $time = new \DateTime($timeString, new \DateTimeZone('UTC'));
        $timeOfDayScope = 'night';
        $timeString = $time->format('H:i');
        $session->set(self::SESSION_KEY_TIME, $timeString);
        if ($timeString > '06:00') {
            if ($timeString < '12:00') {
                $timeOfDayScope = 'morning';
            } elseif ($timeString < '18:00') {
                $timeOfDayScope = 'afternoon';
            }
        }

        $defaultCollectionAddress = $defaultSettingsService->getSectionAddress(DisplaySettings::class);
        $boxes = [
             'default' => [
                'display' => $defaultSettingsService->getSection(DisplaySettings::class, $timeOfDayScope),
                'content' => $defaultSettingsService->getSection(ContentSettings::class, $timeOfDayScope),
            ],
        ];

        if ($project && $project->getOwner()->getId() !== $this->getUser()->getUserIdentifier()) {
            return $this->redirectToRoute('example');
        }

        if ($project) {
            $boxes['project'] = [
                'display' => $projectSettingsService->getSection(DisplaySettings::class, $project),
                'content' => $projectSettingsService->getSection(ContentSettings::class, $project),
            ];
        }

        if ($this->getUser()) {
            $projects = $em
                ->createQuery('SELECT p FROM App\Entity\Project p JOIN p.owner u WHERE u.id = :userId ORDER BY p.name')
                ->setParameter('userId', $this->getUser()->getUserIdentifier())
                ->getResult()
            ;
        } else {
            $projects = null;
        }

        return $this->render('example.html.twig', [
            'boxes' => $boxes,
            'currentProject' => $project,
            'projects' => $projects,
            'timeString' => $timeString,
        ]);
    }

    #[Route(path: '/set-time/{timeString}/{project}', name: 'set_time', defaults: ['project' => null])]
    public function setTime(string $timeString, ?string $project, Request $request): Response
    {
        $request->getSession()->set(self::SESSION_KEY_TIME, $timeString);

        return $this->redirectToRoute('example', ['project' => $project]);
    }
}