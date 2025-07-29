<?php

namespace App\Settings\One;

use App\Form\MessageType;
use App\Model\Message;
use Tzunghaor\SettingsBundle\Attribute\Setting;

/**
 * Content Settings
 */
class ContentSettings
{
    public string $title = '';

    #[Setting(
        dataType: Message::class . '[]',
        formEntryType: MessageType::class
    )]
    public array $messages = [];
}