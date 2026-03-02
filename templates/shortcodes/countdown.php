<?php
/**
 * Template: Countdown shortcode frontend
 *
 * Available variables:
 *
 * @var array  $atts           Shortcode attributes
 * @var string $uniqueId       Unique DOM ID for this countdown instance
 * @var string $targetDateUtc  Target date/time stored as UTC (format: Y-m-d H:i:s)
 * @var array  $units          Associative array: unit => bool (whether to show it)
 * @var string $wrapStyle      Inline CSS for the outer wrapper (typography)
 * @var string $cssVars        CSS custom properties string for countdown styling
 */

if (!defined('ABSPATH')) {
    exit;
}

// Массив склонений для единиц времени: [1, 2-4, 5-0]
// Например: 1 день, 2 дня, 5 дней
$unitLabels = [
    'years'   => [__('Год', 'wp-countdown'), __('Года', 'wp-countdown'), __('Лет', 'wp-countdown')],
    'months'  => [__('Месяц', 'wp-countdown'), __('Месяца', 'wp-countdown'), __('Месяцев', 'wp-countdown')],
    'weeks'   => [__('Неделя', 'wp-countdown'), __('Недели', 'wp-countdown'), __('Недель', 'wp-countdown')],
    'days'    => [__('День', 'wp-countdown'), __('Дня', 'wp-countdown'), __('Дней', 'wp-countdown')],
    'hours'   => [__('Час', 'wp-countdown'), __('Часа', 'wp-countdown'), __('Часов', 'wp-countdown')],
    'minutes' => [__('Минута', 'wp-countdown'), __('Минуты', 'wp-countdown'), __('Минут', 'wp-countdown')],
    'seconds' => [__('Секунда', 'wp-countdown'), __('Секунды', 'wp-countdown'), __('Секунд', 'wp-countdown')],
];

/**
 * Функция склонения русских числительных
 * @param int $number Число
 * @param array $forms Массив форм [1, 2-4, 5-0]
 * @return string
 */
function pluralize($number, $forms) {
    $number = abs($number) % 100;
    $n1 = $number % 10;
    
    if ($number > 10 && $number < 20) {
        return $forms[2];
    }
    if ($n1 > 1 && $n1 < 5) {
        return $forms[1];
    }
    if ($n1 == 1) {
        return $forms[0];
    }
    return $forms[2];
}

/** @var string[] $activeUnits */
$activeUnits = array_keys(array_filter($units));
$lastUnit    = !empty($activeUnits) ? end($activeUnits) : '';

$elClass   = !empty($atts['el_class']) ? ' ' . esc_attr($atts['el_class']) : '';
$idAttr    = !empty($atts['el_id']) ? ' id="' . esc_attr($atts['el_id']) . '"' : '';
$vcCss     = function_exists('vc_shortcode_custom_css_class') ? vc_shortcode_custom_css_class($atts['css'] ?? '', ' ') : '';
?>
<div<?php echo $idAttr; ?>
     class="cd-countdown-wrap<?php echo $elClass . $vcCss; ?>"
     style="<?php echo esc_attr($cssVars . $wrapStyle); ?>">

    <div id="<?php echo esc_attr($uniqueId); ?>"
         class="cd-countdown"
         data-target="<?php echo esc_attr($targetDateUtc); ?>"
         data-units="<?php echo esc_attr(implode(',', $activeUnits)); ?>"
         data-labels="<?php echo esc_attr(json_encode($unitLabels)); ?>"
         role="timer"
         aria-label="<?php esc_attr_e('Обратный отсчёт', 'wp-countdown'); ?>">

        <?php foreach ($activeUnits as $unit): ?>
            <div class="cd-block" data-unit="<?php echo esc_attr($unit); ?>">

                <div class="cd-flip-container">
                    <div class="cd-flip-card">
                        <span class="cd-number"
                              data-key="<?php echo esc_attr($unit); ?>">
                            00
                        </span>
                    </div>
                </div>

                <div class="cd-label" data-label-key="<?php echo esc_attr($unit); ?>">
                    <?php echo esc_html(isset($unitLabels[$unit]) ? $unitLabels[$unit][2] : $unit); ?>
                </div>

            </div>

            <?php if ($unit !== $lastUnit): ?>
                <div class="cd-separator" aria-hidden="true"><span>:</span></div>
            <?php endif; ?>

        <?php endforeach; ?>

    </div>

    <div class="cd-expired"
         id="<?php echo esc_attr($uniqueId); ?>-expired"
         style="display:none;">
        <?php esc_html_e('Сейчас!', 'wp-countdown'); ?>
    </div>

</div>
