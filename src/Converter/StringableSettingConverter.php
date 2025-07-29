<?php


namespace App\Converter;


use App\Model\StringableInterface;
use Symfony\Component\PropertyInfo\Type;
use Tzunghaor\SettingsBundle\Service\SettingConverterInterface;

/**
 * This converter is tagged with 'tzunghaor_settings.setting_converter', so
 * tzunghaor/settings-bundle will try this to convert values between DB and PHP.
 * This one can convert any class implementing App\Model\StringableInterface
 */
class StringableSettingConverter implements SettingConverterInterface
{

    /**
     * @param Type $type
     *
     * @return bool true if this converter can convert to-from this type
     */
    public function supports(Type $type): bool
    {
        if (($className = $type->getClassName()) === null) {
            return false;
        }

        return class_implements($type->getClassName())[StringableInterface::class] ?? false;
    }

    /**
     * @param Type $type
     * @param mixed $value value used in setting section object
     *
     * @return string value persisted in DB
     */
    public function convertToString(Type $type, $value): string
    {
        if (!$type->isCollection()) {
            return $value->toString();
        }

        $strings = [];
        foreach ($value as $item) {
            $strings[] = $item->toString();
        }

        return json_encode($strings);
    }

    /**
     * @param Type $type
     * @param string $value value persisted in DB
     *
     * @return mixed value used in setting section object
     */
    public function convertFromString(Type $type, string $value)
    {
        $class = $type->getClassName();
        if (!$type->isCollection()) {
            return $class::fromString($value);
        }

        $objects = [];

        foreach (json_decode($value, true) as $string) {
            $objects[] = $class::fromString($string);
        }

        return $objects;
    }
}