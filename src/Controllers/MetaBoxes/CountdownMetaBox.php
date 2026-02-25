<?php

namespace CountdownPlugin\Controllers\MetaBoxes;

use WpToolKit\Controller\MetaBoxController;
use WpToolKit\Entity\MetaBoxContext;
use WpToolKit\Entity\MetaBoxPriority;

class CountdownMetaBox extends MetaBoxController
{
    private const NONCE = 'cd_countdown_settings_nonce';

    public function __construct()
    {
        parent::__construct(
            'cd_countdown_settings',
            'Настройки обратного отсчёта',
            'countdown',
            MetaBoxContext::NORMAL,
            MetaBoxPriority::HIGH
        );
    }

    public function render($post): void
    {
        wp_nonce_field(self::NONCE, self::NONCE);

        $targetDateUtc = get_post_meta($post->ID, 'cd_target_date_utc', true);

        // Convert stored UTC to local time for datetime-local input display
        $displayDate = '';
        if ($targetDateUtc) {
            $utcDt = new \DateTime($targetDateUtc, new \DateTimeZone('UTC'));
            $utcDt->setTimezone(wp_timezone());
            $displayDate = $utcDt->format('Y-m-d\TH:i');
        }

        $showYears   = get_post_meta($post->ID, 'cd_show_years',   true);
        $showMonths  = get_post_meta($post->ID, 'cd_show_months',  true);
        $showWeeks   = get_post_meta($post->ID, 'cd_show_weeks',   true);
        $showDays    = get_post_meta($post->ID, 'cd_show_days',    true);
        $showHours   = get_post_meta($post->ID, 'cd_show_hours',   true);
        $showMinutes = get_post_meta($post->ID, 'cd_show_minutes', true);
        $showSeconds = get_post_meta($post->ID, 'cd_show_seconds', true);

        // Defaults for new posts (target date not yet set)
        if ($targetDateUtc === '') {
            $showMonths = '1';
            $showDays   = '1';
            $showHours  = '1';
        }

        $units = [
            ['key' => 'cd_show_years',   'label' => 'Годы',    'val' => $showYears],
            ['key' => 'cd_show_months',  'label' => 'Месяцы',  'val' => $showMonths],
            ['key' => 'cd_show_weeks',   'label' => 'Недели',  'val' => $showWeeks],
            ['key' => 'cd_show_days',    'label' => 'Дни',     'val' => $showDays],
            ['key' => 'cd_show_hours',   'label' => 'Часы',    'val' => $showHours],
            ['key' => 'cd_show_minutes', 'label' => 'Минуты',  'val' => $showMinutes],
            ['key' => 'cd_show_seconds', 'label' => 'Секунды', 'val' => $showSeconds],
        ];
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="cd_target_date">Целевая дата и время</label>
                </th>
                <td>
                    <input
                        type="datetime-local"
                        name="cd_target_date"
                        id="cd_target_date"
                        value="<?php echo esc_attr($displayDate); ?>"
                        class="regular-text"
                    />
                    <p class="description">
                        Введите дату в вашем локальном часовом поясе (<?php echo esc_html(wp_timezone_string()); ?>).
                        Сохраняется автоматически в UTC 0.
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">Единицы отображения</th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text">Выберите единицы для отображения</legend>
                        <?php foreach ($units as $unit): ?>
                            <label style="display:inline-block; margin-right: 16px; margin-bottom: 6px;">
                                <input
                                    type="checkbox"
                                    name="<?php echo esc_attr($unit['key']); ?>"
                                    value="1"
                                    <?php checked($unit['val'], '1'); ?>
                                />
                                <?php echo esc_html($unit['label']); ?>
                            </label>
                        <?php endforeach; ?>
                        <p class="description">
                            По умолчанию выбраны: месяцы, дни, часы.
                        </p>
                    </fieldset>
                </td>
            </tr>
        </table>
        <?php
    }

    public function callback($postId): void
    {
        if (!isset($_POST[self::NONCE]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE])), self::NONCE)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        // Save target date as UTC 0
        if (!empty($_POST['cd_target_date'])) {
            $localDateStr = sanitize_text_field(wp_unslash($_POST['cd_target_date']));

            try {
                $localDt = new \DateTime($localDateStr, wp_timezone());
                $localDt->setTimezone(new \DateTimeZone('UTC'));
                update_post_meta($postId, 'cd_target_date_utc', $localDt->format('Y-m-d H:i:s'));
            } catch (\Exception $e) {
                // Invalid date — skip saving
            }
        }

        // Save checkbox fields
        $checkboxFields = [
            'cd_show_years',
            'cd_show_months',
            'cd_show_weeks',
            'cd_show_days',
            'cd_show_hours',
            'cd_show_minutes',
            'cd_show_seconds',
        ];

        foreach ($checkboxFields as $field) {
            update_post_meta($postId, $field, isset($_POST[$field]) ? '1' : '0');
        }
    }
}
