import './bootstrap';
import 'iconify-icon/dist/iconify-icon.min.js';
import "jsvectormap/dist/jsvectormap.min.css";
import "flatpickr/dist/flatpickr.min.css";
import "dropzone/dist/dropzone.css";

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

import focus from '@alpinejs/focus'
import flatpickr from "flatpickr";
import Dropzone from "dropzone";
import * as jalaaliModule from "../../modules/jalaali-js-master/index.js";

const {
    toJalaali,
    toGregorian,
    isValidJalaaliDate,
    isLeapJalaaliYear,
    jalaaliMonthLength,
} = jalaaliModule.default ?? jalaaliModule ?? {};

const persianMonths = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
const persianWeekdays = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
const persianDigits = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
const arabicDigits = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
const englishDigits = ['0','1','2','3','4','5','6','7','8','9'];
const persianDigitMap = persianDigits.reduce((acc, char, index) => ({ ...acc, [char]: englishDigits[index] }), {});
const arabicDigitMap = arabicDigits.reduce((acc, char, index) => ({ ...acc, [char]: englishDigits[index] }), {});

function toPersianDigits(value) {
    return String(value).replace(/[0-9]/g, (d) => persianDigits[d] ?? d);
}

function toEnglishDigits(value) {
    return String(value)
        .replace(/[۰-۹]/g, (char) => persianDigitMap[char] ?? char)
        .replace(/[٠-٩]/g, (char) => arabicDigitMap[char] ?? char);
}

function pad(number) {
    return number.toString().padStart(2, '0');
}

function formatJalaliDate(date, includeTime = false) {
    if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
        return '';
    }

    if (typeof toJalaali !== 'function') {
        let fallback = `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
        if (includeTime) {
            fallback += ` ${pad(date.getHours())}:${pad(date.getMinutes())}`;
        }
        return fallback;
    }

    const { jy, jm, jd } = toJalaali(date.getFullYear(), date.getMonth() + 1, date.getDate());
    let result = `${jy}-${pad(jm)}-${pad(jd)}`;

    if (includeTime) {
        result += ` ${pad(date.getHours())}:${pad(date.getMinutes())}`;
    }

    return toPersianDigits(result);
}

function parseJalaliDate(value, includeTime = false) {
    if (!value) {
        return null;
    }

    const normalized = toEnglishDigits(value)
        .replace(/\//g, '-')
        .replace(/Z$/i, '')
        .trim();

    const isoMatch = normalized.match(/^(\d{4})-(\d{2})-(\d{2})(?:[T\s](\d{2}):(\d{2}))?/);
    if (isoMatch && Number(isoMatch[1]) > 1700) {
        const [, gy, gm, gd, h = '0', m = '0'] = isoMatch;
        return new Date(
            Number(gy),
            Number(gm) - 1,
            Number(gd),
            includeTime ? Number(h) : 0,
            includeTime ? Number(m) : 0,
        );
    }

    if (typeof toGregorian !== 'function') {
        return null;
    }

    const [datePartRaw, timePartRaw] = normalized.split(/[T\s]/);
    const datePart = datePartRaw || '';
    const timePartClean = (timePartRaw || '').split(/[.]/)[0];

    const [jy, jm, jd] = datePart.split('-').map(Number);

    if (!jy || !jm || !jd || Number.isNaN(jy) || Number.isNaN(jm) || Number.isNaN(jd)) {
        return null;
    }

    const { gy, gm, gd } = toGregorian(jy, jm, jd);

    let hours = 0;
    let minutes = 0;

    if (includeTime && timePartClean) {
        const [h, m] = timePartClean.split(':').map(Number);
        if (!Number.isNaN(h)) hours = h;
        if (!Number.isNaN(m)) minutes = m;
    }

    const parsedDate = new Date(gy, gm - 1, gd, hours, minutes);
    if (Number.isNaN(parsedDate.getTime())) {
        const fallback = new Date(normalized);
        return Number.isNaN(fallback.getTime()) ? null : fallback;
    }

    return parsedDate;
}

function decorateCalendar(instance) {
    if (!instance || !toJalaali) {
        return;
    }

    try {
        const { jy, jm } = toJalaali(instance.currentYear, instance.currentMonth + 1, 1);

        if (Array.isArray(instance.monthElements)) {
            instance.monthElements.forEach((el) => {
                el.textContent = persianMonths[jm - 1] ?? persianMonths[0];
            });
        }

        if (Array.isArray(instance.yearElements)) {
            instance.yearElements.forEach((el) => {
                el.value = toPersianDigits(jy);
                el.setAttribute('readonly', 'readonly');
            });
        }

        const weekdayNodes = instance.weekdayContainer?.querySelectorAll('.flatpickr-weekday');
        if (weekdayNodes) {
            weekdayNodes.forEach((el, index) => {
                el.textContent = persianWeekdays[index % persianWeekdays.length];
            });
        }

        instance.days?.childNodes?.forEach((dayElem) => {
            if (!dayElem.classList || !dayElem.classList.contains('flatpickr-day')) {
                return;
            }

            const dateObj = dayElem.dateObj;
            if (!dateObj) {
                return;
            }

            const { jy: dayJy, jm: dayJm, jd: dayJd } = toJalaali(dateObj.getFullYear(), dateObj.getMonth() + 1, dateObj.getDate());
            dayElem.textContent = toPersianDigits(dayJd);
            dayElem.setAttribute('data-jalali', `${dayJy}-${pad(dayJm)}-${pad(dayJd)}`);
            dayElem.setAttribute('aria-label', `${dayJy}-${pad(dayJm)}-${pad(dayJd)}`);
        });
    } catch (error) {
        console.error('Failed to decorate Jalali calendar:', error);
    }
}

(function initializeJalali() {
    try {
        window.initJalaliDatepicker = (element, { enableTime = false } = {}) => {
            if (!element) {
                return;
            }

            const initialDate = parseJalaliDate(element.value, enableTime) || new Date();

            const config = {
                enableTime,
                allowInput: true,
                time_24hr: true,
                locale: {
                    firstDayOfWeek: 6,
                },
                defaultDate: initialDate,
                parseDate: (value) => parseJalaliDate(value, enableTime) || new Date(value),
                formatDate: (date) => formatJalaliDate(date, enableTime) || date.toISOString(),
            };

            const hook = (_, __, instance) => decorateCalendar(instance);
            ['onReady', 'onOpen', 'onMonthChange', 'onYearChange', 'onValueUpdate'].forEach((event) => {
                config[event] = [hook];
            });

            let picker;
            try {
                picker = flatpickr(element, config);
                decorateCalendar(picker);
            } catch (error) {
                console.error('Flatpickr Jalali failed, falling back to default mode.', error);
                picker = flatpickr(element, {
                    enableTime,
                    allowInput: true,
                    time_24hr: true,
                    defaultDate: initialDate,
                });
            }

            return picker;
        };
    } catch (error) {
        console.error('Failed to initialize Jalali datepicker:', error);
    }
})();

import chart01 from "./components/charts/chart-01";
import chart02 from "./components/charts/chart-02";
import chart03 from "./components/charts/chart-03";
import userGrowthChart from "./components/charts/user-growth-chart.js";
import map01 from "./components/map-01";
import "./components/calendar-init.js";
import "./components/image-resize";
import SlugGenerator from "./components/slug-generator";
import * as Popper from '@popperjs/core';

// Make Popper available globally with the correct structure
window.Popper = Popper;

// Register a slug generator component with Alpine.
Alpine.data('slugGenerator', (initialTitle = '', initialSlug = '') => {
    return SlugGenerator.alpineComponent(initialTitle, initialSlug);
});

// Initialize Livewire.
Livewire.start();

// Register an advanced fields component with Alpine.
Alpine.data('advancedFields', (initialMeta = {}) => {
    return {
        fields: [],
        initialized: false,

        init() {
            // Convert initial meta object to array format.
            if (initialMeta && Object.keys(initialMeta).length > 0) {
                this.fields = Object.entries(initialMeta).map(([key, data]) => {
                    if (typeof data === 'object' && data !== null && data.value !== undefined) {
                        return {
                            key: key,
                            value: data.value || '',
                            type: data.type || 'input',
                            default_value: data.default_value || ''
                        };
                    } else {
                        // Handle legacy format where data is just the value
                        return {
                            key: key,
                            value: typeof data === 'string' ? data : '',
                            type: 'input',
                            default_value: ''
                        };
                    }
                });
            }

            // If no fields exist, add one empty field.
            if (this.fields.length === 0) {
                this.addField();
            }

            this.initialized = true;
        },

        addField() {
            this.fields.push({
                key: '',
                value: '',
                type: 'input',
                default_value: ''
            });
        },

        removeField(index) {
            if (this.fields.length > 1) {
                this.fields.splice(index, 1);
            }
        },

        get fieldsJson() {
            return this.initialized ? JSON.stringify(this.fields) : '[]';
        }
    };
});

// Alpine plugins.
Alpine.plugin(focus);
window.Alpine = Alpine;

// Global drawers.
window.openDrawer = function (drawerId) {
    // Try using the LaraDrawers registry if available.
    if (window.LaraDrawers && window.LaraDrawers[drawerId]) {
        window.LaraDrawers[drawerId].open = true;
        return;
    }

    // Try using Alpine.js directly.
    const drawerEl = document.querySelector(`[data-drawer-id="${drawerId}"]`);
    if (drawerEl && window.Alpine) {
        try {
            const alpineInstance = Alpine.getComponent(drawerEl);
            if (alpineInstance) {
                alpineInstance.open = true;
                return;
            }
        } catch (e) {
            console.error('Alpine error:', e);
        }
    }

    window.dispatchEvent(new CustomEvent('open-drawer-' + drawerId));
};

// Dark mode toggle.
document.addEventListener('DOMContentLoaded', function () {
    const html = document.documentElement;
    const darkModeToggle = document.getElementById('darkModeToggle');
    const header = document.getElementById('appHeader');

    // Update header background based on current mode
    function updateHeaderBg() {
        if (!header) return;
        const isDark = html.classList.contains('dark');
    }

    // Initialize dark mode
    const savedDarkMode = localStorage.getItem('darkMode');
    if (savedDarkMode === 'true') {
        html.classList.add('dark');
    } else if (savedDarkMode === 'false') {
        html.classList.remove('dark');
    }

    updateHeaderBg();

    const observer = new MutationObserver(updateHeaderBg);
    observer.observe(html, {
        attributes: true,
        attributeFilter: ['class']
    });

    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function (e) {
            e.preventDefault();
            const isDark = html.classList.toggle('dark');
            localStorage.setItem('darkMode', isDark);
            updateHeaderBg();
        });
    }

    // Initialize sidebar state from localStorage if it exists
    if (window.Alpine) {
        const sidebarState = localStorage.getItem('sidebarToggle');
        if (sidebarState !== null) {
            document.addEventListener('alpine:initialized', () => {
                // Ensure the Alpine.js instance is ready
                setTimeout(() => {
                    const alpineData = document.querySelector('body').__x;
                    if (alpineData && typeof alpineData.$data !== 'undefined') {
                        alpineData.$data.sidebarToggle = JSON.parse(sidebarState);
                    }
                }, 0);
            });
        }
    }
});

// Initialize all drawer triggers on page load
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-drawer-trigger]').forEach(function (element) {
        element.addEventListener('click', function (e) {
            const drawerId = this.getAttribute('data-drawer-trigger');
            if (drawerId) {
                e.preventDefault();
                window.openDrawer(drawerId);
                return false;
            }
        });
    });
});


// Init Dropzone
const dropzoneArea = document.querySelectorAll("#demo-upload");

if (dropzoneArea.length) {
    let myDropzone = new Dropzone("#demo-upload", { url: "/file/post" });
}

// Document Loaded
document.addEventListener("DOMContentLoaded", () => {
    chart01();
    chart02();
    chart03();
    userGrowthChart();
    map01();

    const copyInput = document.getElementById("copy-input");
    if (copyInput) {
        // Select the copy button and input field
        const copyButton = document.getElementById("copy-button");
        const copyText = document.getElementById("copy-text");
        const websiteInput = document.getElementById("website-input");

        // Event listener for the copy button
        copyButton.addEventListener("click", () => {
            // Copy the input value to the clipboard
            navigator.clipboard.writeText(websiteInput.value).then(() => {
                // Change the text to "Copied"
                copyText.textContent = "Copied";

                // Reset the text back to "Copy" after 2 seconds
                setTimeout(() => {
                    copyText.textContent = "Copy";
                }, 2000);
            });
        });
    }
});

// Get the current year
const year = document.getElementById("year");
if (year) {
    year.textContent = new Date().getFullYear();
}

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search-input");
    const searchButton = document.getElementById("search-button");

    // Function to focus the search input
    function focusSearchInput() {
        searchInput?.focus();
    }

    if (searchInput) {
        // Add click event listener to the search button
        searchButton.addEventListener("click", focusSearchInput);
    }

    // Add keyboard event listener for Cmd+K (Mac) or Ctrl+K (Windows/Linux)
    document.addEventListener("keydown", function (event) {
        if ((event.metaKey || event.ctrlKey) && event.key === "k") {
            event.preventDefault(); // Prevent the default browser behavior
            focusSearchInput();
        }
    });

    // Add keyboard event listener for "/" key
    document.addEventListener("keydown", function (event) {
        // Check if the active element is an input, textarea, or contenteditable element
        const activeElement = document.activeElement;
        const isInputField = activeElement.tagName === 'INPUT' ||
            activeElement.tagName === 'TEXTAREA' ||
            activeElement.contentEditable === 'true';

        if (event.key === "/" && !isInputField) {
            event.preventDefault(); // Prevent the "/" character from being typed
            focusSearchInput();
        }
    });
});

// Toast notification helper function
window.showToast = function (variant, title, message) {
    // Dispatch the notify event that the toast component listens for
    window.dispatchEvent(new CustomEvent('notify', {
        detail: {
            variant, // 'success', 'error', 'warning', 'info'
            title,
            message
        }
    }));
};

// Import term drawer functionality
import './term-drawer.js';

// Prevent navigation if form is dirty (unsaved changes).
import './prevent-dirty-form-changes.js';

// Import menu handling functionality
import './components/menu-handler.js';
