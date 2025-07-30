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
        help: "Padding in points. (Orange input border defined in formOptions)",
        formOptions: ["attr" => ["style" => "border: 5px solid orange;"]],
    )]
    private int $padding;

    // Extracting help text from docblock works only if phpdocumentor/reflection-docblock is installed.
    /**
     * Margin
     *
     * Margin in points.
     * (Yellow input border is defined in CustomIntType)
     */
    #[Setting(
        dataType: "int",
        formType: CustomIntType::class,
    )]
    #[Assert\PositiveOrZero]
    #[Assert\LessThanOrEqual(30, message: "maximum accepted margin is {{ compared_value }} pt")]
    private int $margin;

    /**
     * @var string[]
     */
    #[Setting(enum: ["bottom", "top", "left", "right"])]
    private array $borders;

    #[Setting(label: 'Background Color', formType: ColorType::class)]
    private string $backgroundColor;

    #[Setting(label: 'Text Color', formType: ColorType::class)]
    private string $textColor;

    /**
     * Border Color
     */
    #[Setting(formType: ColorType::class)]
    private string $borderColor;


    public function __construct(
        int    $padding = 0,
        int    $margin = 0,
        array  $borders = [],
        string $backgroundColor = '#D2EBF5',
        string $textColor = 'black',
        string $borderColor = 'black'
    ) {
        $this->padding = $padding;
        $this->margin = $margin;
        $this->borders = $borders;
        $this->borderColor = $borderColor;
        $this->backgroundColor = $backgroundColor;
        $this->textColor = $textColor;
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

    public function getBackgroundColor(): string
    {
        return $this->backgroundColor;
    }

    public function getTextColor(): string
    {
        return $this->textColor;
    }

    public function getBorderColor(): string
    {
        return $this->borderColor;
    }
}