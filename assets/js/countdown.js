/**
 * Countdown Plugin — frontend JS
 * Динамический обратный отсчёт с flip-анимацией.
 * Не зависит от сторонних библиотек.
 */
(function () {
    'use strict';

    /**
     * Парсит строку UTC-даты формата "Y-m-d H:i:s" в Unix-миллисекунды.
     * @param {string} utcString
     * @returns {number|null}
     */
    function parseUtcDate(utcString) {
        var m = utcString.match(/^(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2}):(\d{2})$/);
        if (!m) return null;
        return Date.UTC(
            parseInt(m[1], 10),
            parseInt(m[2], 10) - 1,
            parseInt(m[3], 10),
            parseInt(m[4], 10),
            parseInt(m[5], 10),
            parseInt(m[6], 10)
        );
    }

    /**
     * Вычисляет разницу между targetMs и текущим временем.
     * Возвращает null, если target уже прошёл.
     *
     * @param {number}   targetMs
     * @param {string[]} units    – массив выбранных единиц в нужном порядке
     * @returns {Object|null}
     */
    function calcDiff(targetMs, units) {
        var diffSec = Math.floor((targetMs - Date.now()) / 1000);
        if (diffSec <= 0) return null;

        var result = {};
        var remaining = diffSec;

        var secondsIn = {
            years: Math.floor(365.25 * 24 * 3600),
            months: Math.floor(30.4375 * 24 * 3600),
            weeks: 7 * 24 * 3600,
            days: 24 * 3600,
            hours: 3600,
            minutes: 60,
            seconds: 1,
        };

        // Ensure units are processed from largest to smallest
        var orderedUnits = ['years', 'months', 'weeks', 'days', 'hours', 'minutes', 'seconds'];
        orderedUnits.forEach(function (unit) {
            if (units.indexOf(unit) !== -1 && secondsIn[unit] !== undefined) {
                result[unit] = Math.floor(remaining / secondsIn[unit]);
                remaining -= result[unit] * secondsIn[unit];
            }
        });

        return result;
    }

    /**
     * Дополняет число ведущим нулём (возвращает строку длиной 2).
     * @param {number} n
     * @returns {string}
     */
    function pad(n) {
        return String(Math.max(0, n)).padStart(2, '0');
    }

    /**
     * Обновляет значение одного элемента с flip-анимацией.
     * @param {HTMLElement} el   – элемент .cd-number
     * @param {number}      val  – новое значение
     */
    function flipNumber(el, val) {
        var formatted = pad(val);
        if (el.textContent.trim() === formatted) return; // no change — skip

        // Phase 1: flip out
        el.classList.remove('cd-flip-in');
        el.classList.add('cd-flip-out');

        setTimeout(function () {
            el.textContent = formatted;
            el.classList.remove('cd-flip-out');
            // Phase 2: flip in
            el.classList.add('cd-flip-in');

            setTimeout(function () {
                el.classList.remove('cd-flip-in');
            }, 230);
        }, 220);
    }

    /**
     * Инициализирует один экземпляр отсчёта.
     * @param {HTMLElement} el – элемент .cd-countdown[data-target]
     */
    function initCountdown(el) {
        var targetUtc = el.getAttribute('data-target');
        var unitsStr = el.getAttribute('data-units');

        if (!targetUtc || !unitsStr) return;

        var units = unitsStr.split(',').map(function (u) { return u.trim(); }).filter(Boolean);
        var targetMs = parseUtcDate(targetUtc);

        if (!targetMs) {
            console.warn('[cd-countdown] Не удалось распарсить дату: ' + targetUtc);
            return;
        }

        // Cache number elements
        var numberEls = {};
        units.forEach(function (unit) {
            var numEl = el.querySelector('[data-key="' + unit + '"]');
            if (numEl) numberEls[unit] = numEl;
        });

        var expiredEl = document.getElementById(el.id + '-expired');

        /** @param {boolean} initial */
        function tick(initial) {
            var diff = calcDiff(targetMs, units);

            if (!diff) {
                // Time is up
                el.style.display = 'none';
                if (expiredEl) expiredEl.style.display = '';
                return;
            }

            units.forEach(function (unit) {
                var numEl = numberEls[unit];
                if (!numEl) return;

                if (initial) {
                    // Render without animation on first paint
                    numEl.textContent = pad(diff[unit]);
                } else {
                    flipNumber(numEl, diff[unit]);
                }
            });
        }

        // First render — no animation
        tick(true);

        // Subsequent ticks every second
        setInterval(function () { tick(false); }, 1000);
    }

    // ── Bootstrap ────────────────────────────────────────────────────────────────

    function bootstrap() {
        var elements = document.querySelectorAll('.cd-countdown[data-target]');
        elements.forEach(initCountdown);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrap);
    } else {
        // DOMContentLoaded has already fired (e.g., deferred script)
        bootstrap();
    }
})();
