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
        var now = new Date();
        var target = new Date(targetMs);
        
        if (target <= now) return null;

        var result = {};
        var current = new Date(now);

        // Ensure units are processed from largest to smallest
        var orderedUnits = ['years', 'months', 'weeks', 'days', 'hours', 'minutes', 'seconds'];
        
        orderedUnits.forEach(function (unit) {
            if (units.indexOf(unit) === -1) return;
            
            if (unit === 'years') {
                var years = 0;
                while (true) {
                    var nextYear = new Date(current);
                    nextYear.setFullYear(current.getFullYear() + 1);
                    if (nextYear > target) break;
                    years++;
                    current = nextYear;
                }
                result.years = years;
            } else if (unit === 'months') {
                var months = 0;
                while (true) {
                    var nextMonth = new Date(current);
                    nextMonth.setMonth(current.getMonth() + 1);
                    if (nextMonth > target) break;
                    months++;
                    current = nextMonth;
                }
                result.months = months;
            } else if (unit === 'weeks') {
                var weeks = 0;
                while (true) {
                    var nextWeek = new Date(current);
                    nextWeek.setDate(current.getDate() + 7);
                    if (nextWeek > target) break;
                    weeks++;
                    current = nextWeek;
                }
                result.weeks = weeks;
            } else if (unit === 'days') {
                var days = 0;
                while (true) {
                    var nextDay = new Date(current);
                    nextDay.setDate(current.getDate() + 1);
                    if (nextDay > target) break;
                    days++;
                    current = nextDay;
                }
                result.days = days;
            } else if (unit === 'hours') {
                var diffMs = target - current;
                var hours = Math.floor(diffMs / (1000 * 60 * 60));
                result.hours = hours;
                current = new Date(current.getTime() + hours * 60 * 60 * 1000);
            } else if (unit === 'minutes') {
                var diffMs = target - current;
                var minutes = Math.floor(diffMs / (1000 * 60));
                result.minutes = minutes;
                current = new Date(current.getTime() + minutes * 60 * 1000);
            } else if (unit === 'seconds') {
                var diffMs = target - current;
                var seconds = Math.floor(diffMs / 1000);
                result.seconds = seconds;
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
     * Склонение русских числительных
     * @param {number} number - Число
     * @param {Array} forms - Массив форм [1, 2-4, 5-0]
     * @returns {string}
     */
    function pluralize(number, forms) {
        number = Math.abs(number) % 100;
        var n1 = number % 10;
        
        if (number > 10 && number < 20) {
            return forms[2];
        }
        if (n1 > 1 && n1 < 5) {
            return forms[1];
        }
        if (n1 === 1) {
            return forms[0];
        }
        return forms[2];
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
        var labelsStr = el.getAttribute('data-labels');

        if (!targetUtc || !unitsStr) return;

        var units = unitsStr.split(',').map(function (u) { return u.trim(); }).filter(Boolean);
        var targetMs = parseUtcDate(targetUtc);
        var labels = labelsStr ? JSON.parse(labelsStr) : {};

        if (!targetMs) {
            console.warn('[cd-countdown] Не удалось распарсить дату: ' + targetUtc);
            return;
        }

        // Cache number and label elements
        var numberEls = {};
        var labelEls = {};
        units.forEach(function (unit) {
            var numEl = el.querySelector('[data-key="' + unit + '"]');
            if (numEl) numberEls[unit] = numEl;
            
            var lblEl = el.querySelector('[data-label-key="' + unit + '"]');
            if (lblEl) labelEls[unit] = lblEl;
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

                var value = diff[unit];

                if (initial) {
                    // Render without animation on first paint
                    numEl.textContent = pad(value);
                } else {
                    flipNumber(numEl, value);
                }

                // Update label with correct declension
                var lblEl = labelEls[unit];
                if (lblEl && labels[unit]) {
                    lblEl.textContent = pluralize(value, labels[unit]);
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
