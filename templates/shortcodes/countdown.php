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

$unitLabels = [
    'years'   => __('Лет',     'wp-countdown'),
    'months'  => __('Месяцев', 'wp-countdown'),
    'weeks'   => __('Недель',  'wp-countdown'),
    'days'    => __('Дней',    'wp-countdown'),
    'hours'   => __('Часов',   'wp-countdown'),
    'minutes' => __('Минут',   'wp-countdown'),
    'seconds' => __('Секунд',  'wp-countdown'),
];

/** @var string[] $activeUnits */
$activeUnits = array_keys(array_filter($units));
$lastUnit    = !empty($activeUnits) ? end($activeUnits) : '';

$elClass   = !empty($atts['el_class']) ? ' ' . esc_attr($atts['el_class']) : '';
$idAttr    = !empty($atts['el_id'])    ? ' id="' . esc_attr($atts['el_id']) . '"' : '';
$vcCss     = function_exists('vc_shortcode_custom_css_class') ? vc_shortcode_custom_css_class($atts['css'] ?? '', ' ') : '';
?>
<div<?php echo $idAttr; ?>
     class="cd-countdown-wrap<?php echo $elClass . $vcCss; ?>"
     style="<?php echo esc_attr($cssVars . $wrapStyle); ?>">

    <div id="<?php echo esc_attr($uniqueId); ?>"
         class="cd-countdown"
         data-target="<?php echo esc_attr($targetDateUtc); ?>"
         data-units="<?php echo esc_attr(implode(',', $activeUnits)); ?>"
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

                <div class="cd-label">
                    <?php echo esc_html($unitLabels[$unit] ?? $unit); ?>
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
        <?php esc_html_e('Время вышло!', 'wp-countdown'); ?>
    </div>

</div>
