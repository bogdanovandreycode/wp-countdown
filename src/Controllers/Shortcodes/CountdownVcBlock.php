<?php

namespace CountdownPlugin\Controllers\Shortcodes;

use CountdownPlugin\Controllers\VcBlockController;

class CountdownVcBlock extends VcBlockController
{
    public function __construct(
        private string $pluginFilePath,
        private string $pluginDirPath
    ) {
        parent::__construct(
            'cd_countdown',
            [
                'countdown_id'        => '',
                'number_color'        => '#ffffff',
                'label_color'         => '#a0a0b0',
                'block_bg_color'      => '#1a1a2e',
                'separator_color'     => '#e94560',
                'number_font_size'    => '48px',
                'label_font_size'     => '13px',
                'block_border_radius' => '8px',
                'block_padding'       => '20px 24px',
                'gap'                 => '16px',
            ],
            [
                'name'        => 'Обратный отсчёт',
                'description' => 'Динамический обратный отсчёт с анимацией',
                'icon'        => 'dashicons-clock',
            ]
        );
    }

    /**
     * Переопределяем buildParams(): добавляем выбор записи countdown
     * и специфичные стилевые параметры для блока.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildParams(): array
    {
        // These keys exist in $this->atts so getAtts() works, but we'll
        // replace the parent's auto-generated textfield entries with
        // proper types (dropdown, colorpicker, etc.).
        $overridden = [
            'countdown_id', 'number_color', 'label_color', 'block_bg_color',
            'separator_color', 'number_font_size', 'label_font_size',
            'block_border_radius', 'block_padding', 'gap',
        ];

        // Get the base params and remove the auto-generated duplicates
        $params = array_values(array_filter(
            parent::buildParams(),
            fn(array $p) => !in_array($p['param_name'] ?? '', $overridden, true)
        ));

        // Prepend countdown selector as the first param
        array_unshift($params, [
            'type'        => 'dropdown',
            'heading'     => 'Выбрать отсчёт',
            'param_name'  => 'countdown_id',
            'value'       => $this->getCountdownOptions(),
            'description' => 'Выберите запись типа "Обратный отсчёт"',
            'group'       => 'Общее',
        ]);

        // Countdown-specific style params (group "Отсчёт")
        $extra = [
            [
                'type'       => 'colorpicker',
                'heading'    => 'Цвет цифр',
                'param_name' => 'number_color',
                'value'      => '#ffffff',
                'group'      => 'Отсчёт',
            ],
            [
                'type'       => 'colorpicker',
                'heading'    => 'Цвет подписей',
                'param_name' => 'label_color',
                'value'      => '#a0a0b0',
                'group'      => 'Отсчёт',
            ],
            [
                'type'       => 'colorpicker',
                'heading'    => 'Фон блока',
                'param_name' => 'block_bg_color',
                'value'      => '#1a1a2e',
                'group'      => 'Отсчёт',
            ],
            [
                'type'       => 'colorpicker',
                'heading'    => 'Цвет разделителя ":"',
                'param_name' => 'separator_color',
                'value'      => '#e94560',
                'group'      => 'Отсчёт',
            ],
            [
                'type'        => 'textfield',
                'heading'     => 'Размер шрифта цифр',
                'param_name'  => 'number_font_size',
                'value'       => '48px',
                'description' => 'Например: 48px, 3rem',
                'group'       => 'Отсчёт',
            ],
            [
                'type'        => 'textfield',
                'heading'     => 'Размер шрифта подписей',
                'param_name'  => 'label_font_size',
                'value'       => '13px',
                'group'       => 'Отсчёт',
            ],
            [
                'type'       => 'textfield',
                'heading'    => 'Скругление блоков',
                'param_name' => 'block_border_radius',
                'value'      => '8px',
                'group'      => 'Отсчёт',
            ],
            [
                'type'        => 'textfield',
                'heading'     => 'Внутренние отступы блока',
                'param_name'  => 'block_padding',
                'value'       => '20px 24px',
                'description' => 'CSS shorthand: например 20px 24px',
                'group'       => 'Отсчёт',
            ],
            [
                'type'        => 'textfield',
                'heading'     => 'Расстояние между блоками',
                'param_name'  => 'gap',
                'value'       => '16px',
                'description' => 'Например: 16px',
                'group'       => 'Отсчёт',
            ],
        ];

        array_push($params, ...$extra);

        return $params;
    }

    /**
     * Рендерит шорткод на фронтенде.
     *
     * @param array|string $atts
     * @param string|null  $content
     * @return string
     */
    public function render($atts, $content): string
    {
        $atts        = $this->getAtts($atts);
        $countdownId = absint($atts['countdown_id']);

        if (!$countdownId) {
            return '<div class="cd-countdown-placeholder">Обратный отсчёт не выбран.</div>';
        }

        $post = get_post($countdownId);
        if (!$post || $post->post_type !== 'countdown' || $post->post_status !== 'publish') {
            return '<div class="cd-countdown-placeholder">Обратный отсчёт не найден или не опубликован.</div>';
        }

        $targetDateUtc = get_post_meta($countdownId, 'cd_target_date_utc', true);
        if (!$targetDateUtc) {
            return '<div class="cd-countdown-placeholder">Целевая дата не задана.</div>';
        }

        $units = [
            'years'   => get_post_meta($countdownId, 'cd_show_years',   true) === '1',
            'months'  => get_post_meta($countdownId, 'cd_show_months',  true) === '1',
            'weeks'   => get_post_meta($countdownId, 'cd_show_weeks',   true) === '1',
            'days'    => get_post_meta($countdownId, 'cd_show_days',    true) === '1',
            'hours'   => get_post_meta($countdownId, 'cd_show_hours',   true) === '1',
            'minutes' => get_post_meta($countdownId, 'cd_show_minutes', true) === '1',
            'seconds' => get_post_meta($countdownId, 'cd_show_seconds', true) === '1',
        ];

        // Enqueue assets
        $pluginUrl = plugin_dir_url($this->pluginFilePath);
        wp_enqueue_style('cd-countdown', $pluginUrl . 'assets/css/countdown.css', [], '1.0');
        wp_enqueue_script('cd-countdown', $pluginUrl . 'assets/js/countdown.js', [], '1.0', true);

        $wrapStyle = $this->buildWrapStyle($atts);
        $cssVars   = $this->buildCssVars($atts);
        $uniqueId  = 'cd-' . uniqid();

        ob_start();
        include $this->pluginDirPath . '/templates/shortcodes/countdown.php';
        return ob_get_clean();
    }

    // ─── Private helpers ────────────────────────────────────────────────────────

    /**
     * Строит inline-стиль для внешней обёртки (типографика из базового контроллера).
     */
    private function buildWrapStyle(array $atts): string
    {
        $css = '';
        if (!empty($atts['text_align']))    $css .= 'text-align:'    . esc_attr($atts['text_align'])    . ';';
        if (!empty($atts['font_family']))   $css .= 'font-family:'   . esc_attr($atts['font_family'])   . ';';
        if (!empty($atts['letter_spacing'])) $css .= 'letter-spacing:' . esc_attr($atts['letter_spacing']) . ';';
        return $css;
    }

    /**
     * Строит CSS custom properties для управления стилем блока отсчёта.
     */
    private function buildCssVars(array $atts): string
    {
        $map = [
            'number_color'        => '--cd-number-color',
            'label_color'         => '--cd-label-color',
            'block_bg_color'      => '--cd-block-bg',
            'separator_color'     => '--cd-separator-color',
            'number_font_size'    => '--cd-number-size',
            'label_font_size'     => '--cd-label-size',
            'block_border_radius' => '--cd-border-radius',
            'block_padding'       => '--cd-block-padding',
            'gap'                 => '--cd-gap',
        ];

        $vars = '';
        foreach ($map as $att => $var) {
            if (!empty($atts[$att])) {
                $vars .= $var . ':' . esc_attr($atts[$att]) . ';';
            }
        }
        return $vars;
    }

    /**
     * Возвращает список опубликованных записей типа countdown для VC dropdown.
     *
     * @return array<string, string|int>
     */
    private function getCountdownOptions(): array
    {
        $options = [' — Выберите — ' => ''];

        $posts = get_posts([
            'post_type'      => 'countdown',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);

        foreach ($posts as $post) {
            $options[$post->post_title] = $post->ID;
        }

        return $options;
    }
}
