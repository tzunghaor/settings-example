<?php

namespace App\Settings\One;

use App\Form\CustomIntType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Validator\Constraints as Assert;
use Tzunghaor\SettingsBundle\Attribute\Setting;
use Tzunghaor\SettingsBundle\Attribute\SettingSection;

#[SettingSection(extra: ["color" => "#ddff55"])]
class DisplaySettings
{
    #[Assert\PositiveOrZero(message: "padding should not be negative")]
    #[Assert\LessThanOrEqual(30, message: "maximum accepted padding is {{ compared_value }} pt")]
    #[Setting(
        label: "Padding",
        help: "Padding in points.",
        formOptions: ["attr" => ["style" => "border: 5px solid orange;"]]
    )]
    private int $padding;

    /**
     * Margin
     *
     * Margin in points.
     * I think you already understand how this help text works.
     */
    #[Setting(
        dataType: "int",
        formType: CustomIntType::class
    )]
    #[Assert\PositiveOrZero]
    #[Assert\LessThanOrEqual(30, message: "maximum accepted margin is {{ compared_value }} pt")]
    private int $margin;

    /**
     * @var string[]
     */
    #[Setting(enum: ["bottom", "top", "left", "right"])]
    private array $borders;

    /**
     * Border Color
     */
    #[Setting(formType: ColorType::class)]
    private string $borderColor;


    public function __construct(int $padding = 0, int $margin = 0, array $borders = [], string $borderColor = 'black')
    {
        $this->padding = $padding;
        $this->margin = $margin;
        $this->borders = $borders;
        $this->borderColor = $borderColor;
    }


    public function getPadding(): int
    {
        return $this->padding;
    }

    public function getMargin(): int
    {
        return $this->margin;
    }

    /**
     * @return string[]
     */
    public function getBorders(): array
    {
        return $this->borders;
    }

    public function getBorderColor(): string
    {
        return $this->borderColor;
    }
}