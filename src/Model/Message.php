<?php

namespace App\Model;

/**
 * This class is used in a form collection as entry data.
 * Since this class is not known by tzunghaor/settings-bundle, we have to implement a converter.
 * We will use App/Converter/StringableSettingConverter - for this we implement the App\Model\StringableInterface
 * on this class.
 */
class Message implements StringableInterface
{
    public const TYPE_ERROR = 'error';
    public const TYPE_INFO = 'info';
    public const TYPE_SUCCESS = 'success';

    public const AVAILABLE_TYPES = [
        self::TYPE_ERROR,
        self::TYPE_INFO,
        self::TYPE_SUCCESS
    ];

    private string $type;

    private string $text;


    public function setType(string $type): void
    {
        if (!in_array($type, self::AVAILABLE_TYPES)) {
            throw new \DomainException('Invalid Message $type');
        }

        $this->type = $type;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function toString(): string
    {
        return $this->type . '|' . $this->text;
    }

    public static function fromString(string $string): StringableInterface
    {
        $message = new Message();

        [$message->type, $message->text] = explode('|', $string, 2);

        return $message;
    }
}