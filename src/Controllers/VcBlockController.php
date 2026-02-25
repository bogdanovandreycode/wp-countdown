<?php

namespace CountdownPlugin\Controllers;

abstract class VcBlockController
{
    /**
     * @param string $shortcodeName
     * @param array<string, mixed> $atts
     * @param array<string, mixed> $vcConfig
     */
    public function __construct(
        public string $shortcodeName,
        public array $atts,
        public array $vcConfig
    ) {
        add_shortcode($shortcodeName, [$this, 'render']);

        add_action('vc_before_init', function () {
            vc_map(array_merge([
                'base' => $this->shortcodeName,
                'params' => $this->buildParams(),
                'name' => ucfirst(str_replace('_', ' ', $this->shortcodeName)),
                'category' => 'Доп. блоки',
            ], $this->vcConfig));
        });
    }

    abstract public function render($atts, $content): string;

    /**
     * Возвращает атрибуты с дефолтами
     * @param array $atts
     * @return array
     */
    public function getAtts($atts): array
    {
        return shortcode_atts($this->atts, $atts);
    }

    /**
     * Генерирует vc params на основе $atts и базовых визуальных настроек
     * @return array<int, array<string, mixed>>
     */
    protected function buildParams(): array
    {
        $defined = [];

        // 👇 Базовые UI-настройки (не будут дублироваться)
        $uiParams = [
            'text_color' => [
                'type' => 'colorpicker',
                'heading' => 'Цвет текста',
            ],
            'text_align' => [
                'type' => 'dropdown',
                'heading' => 'Выравнивание текста',
                'value' => [
                    'По умолчанию' => '',
                    'Слева' => 'left',
                    'По центру' => 'center',
                    'Справа' => 'right',
                    'По ширине' => 'justify',
                ],
            ],
            'font_size' => [
                'type' => 'textfield',
                'heading' => 'Размер шрифта',
                'description' => 'Примеры: 16px, 1.2rem, 120%',
            ],
            'line_height' => [
                'type' => 'textfield',
                'heading' => 'Высота строки',
            ],
            'letter_spacing' => [
                'type' => 'textfield',
                'heading' => 'Межбуквенный интервал',
            ],
            'font_weight' => [
                'type' => 'dropdown',
                'heading' => 'Толщина шрифта',
                'value' => [
                    'По умолчанию' => '',
                    '100',
                    '200',
                    '300',
                    '400',
                    '500',
                    '600',
                    '700',
                    '800',
                    '900'
                ],
            ],
            'font_family' => [
                'type' => 'dropdown',
                'heading' => 'Семейство шрифтов',
                'value' => [
                    'По умолчанию' => '',
                    'Arial' => 'Arial, Helvetica, sans-serif',
                    'Times New Roman' => '"Times New Roman", Times, serif',
                    'Georgia' => 'Georgia, serif',
                    'Verdana' => 'Verdana, Geneva, sans-serif',
                    'Courier New' => '"Courier New", Courier, monospace',
                ],
            ],
            'text_transform' => [
                'type' => 'dropdown',
                'heading' => 'Преобразование текста',
                'value' => [
                    'По умолчанию' => '',
                    'Прописные' => 'uppercase',
                    'Строчные' => 'lowercase',
                    'Каждое слово с заглавной буквы' => 'capitalize',
                    'Нет' => 'none',
                ],
            ],
            'font_style' => [
                'type' => 'dropdown',
                'heading' => 'Стиль шрифта',
                'value' => [
                    'По умолчанию' => '',
                    'Обычный' => 'normal',
                    'Курсив' => 'italic',
                ],
            ],
        ];

        // 👇 Параметры, явно заданные в $atts (если не заданы выше)
        foreach ($this->atts as $name => $default) {
            if (!isset($uiParams[$name])) {
                $defined[$name] = [
                    'type' => 'textfield',
                    'heading' => ucfirst(str_replace('_', ' ', $name)),
                    'param_name' => $name,
                    'value' => $default,
                    'group' => 'Общее',
                ];
            }
        }

        // 👇 ID и class
        $defined['el_id'] = [
            'type' => 'textfield',
            'heading' => 'ID элемента',
            'param_name' => 'el_id',
            'description' => 'Необязательный ID для этого элемента.',
            'group' => 'Общее',
        ];
        $defined['el_class'] = [
            'type' => 'textfield',
            'heading' => 'Дополнительное имя класса',
            'param_name' => 'el_class',
            'description' => 'Добавьте имя класса для пользовательских стилей.',
            'group' => 'Общее',
        ];

        // 👇 Добавляем UI-настройки
        foreach ($uiParams as $name => $config) {
            $defined[$name] = array_merge($config, [
                'param_name' => $name,
                'group' => 'Общее',
            ]);
        }

        // 👇 Вкладка "Дизайн" (CSS редактор)
        $defined['css'] = [
            'type' => 'css_editor',
            'heading' => 'Настройки дизайна',
            'param_name' => 'css',
            'group' => 'Дизайн',
        ];

        return array_values($defined);
    }
}
