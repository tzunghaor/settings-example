<?php

namespace App\Controller;

use App\Entity\User;
use App\Settings\One\ContentSettings;
use App\Settings\One\DisplaySettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tzunghaor\SettingsBundle\Service\SettingsService;

class AppController extends AbstractController
{
    #[Route(path: '/', name: 'index')]
    public function index(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();
        $loginLinks = [];
        foreach ($users as $user) {
            $loginLinks[$user->getUserIdentifier()] = '/login/' . $user->getUserIdentifier();
        }

        return $this->render('index.html.twig', [
            'loginLinks' => $loginLinks,
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


    #[Route(path: '/example', name: 'example')]
    public function example(
        SettingsService $defaultSettingsService,
        SettingsService $projectSettingsService,
    ): Response {
        $boxes = [
            'default' => [
                'display' => $defaultSettingsService->getSection(DisplaySettings::class),
                'content' => $defaultSettingsService->getSection(ContentSettings::class),
            ],
        ];

        // there is no sensible default project scope without authenticated user
        if ($this->getUser()) {
            $boxes['project'] = [
                'display' => $projectSettingsService->getSection(DisplaySettings::class),
                'content' => $projectSettingsService->getSection(ContentSettings::class),
            ];
        }

        return $this->render('example.html.twig', [
            'boxes' => $boxes,
        ]);
    }
}