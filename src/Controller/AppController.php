<?php

namespace App\Controller;

use App\Settings\ContentSettings;
use App\Settings\DisplaySettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tzunghaor\SettingsBundle\Service\SettingsService;

class AppController extends AbstractController
{
    #[Route(path: '/')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route(path: '/example', name: 'example')]
    public function example(SettingsService $defaultSettingService): Response
    {
        $displaySettings = $defaultSettingService->getSection(DisplaySettings::class);
        $contentSettings = $defaultSettingService->getSection(ContentSettings::class);

        return $this->render('example.html.twig', [
            'displaySettings' => $displaySettings,
            'contentSettings' => $contentSettings,
        ]);
    }
}