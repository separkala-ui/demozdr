<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum CommonFilterHook: string
{
    // Dashboard.
    case DASHBOARD_AFTER_BREADCRUMBS = 'filter.dashboard_after_breadcrumbs';
    case DASHBOARD_CARDS_BEFORE_USERS = 'filter.dashboard_cards_before_users';
    case DASHBOARD_CARDS_AFTER_USERS = 'filter.dashboard_cards_after_users';
    case DASHBOARD_CARDS_AFTER_ROLES = 'filter.dashboard_cards_after_roles';
    case DASHBOARD_CARDS_AFTER_PERMISSIONS = 'filter.dashboard_cards_after_permissions';
    case DASHBOARD_CARDS_AFTER_TRANSLATIONS = 'filter.dashboard_cards_after_translations';
    case DASHBOARD_CARDS_AFTER = 'filter.dashboard_cards_after';
    case DASHBOARD_AFTER = 'filter.dashboard_after';

    // Media
    case MEDIA_AFTER_BREADCRUMBS = 'filter.media_after_breadcrumbs';

    // Translations
    case TRANSLATION_AFTER_BREADCRUMBS = 'filter.translation_after_breadcrumbs';

    // Languages
    case LANGUAGES = 'filter.languages';

    // Environment
    case AVAILABLE_KEYS = 'filter.available_keys';
    case EXCLUDED_SETTING_KEYS = 'filter.excluded_setting_keys';

    // Advanced fields
    case ADVANCED_FIELDS_TYPES = 'filter.advanced_fields_types';

    // reCAPTCHA
    case RECAPTCHA_IS_ENABLED_FOR_PAGE = 'filter.recaptcha_is_enabled_for_page';
    case RECAPTCHA_PRE_VERIFICATION = 'filter.recaptcha_pre_verification';
    case RECAPTCHA_VERIFY_URL = 'filter.recaptcha_verify_url';
    case RECAPTCHA_POST_VERIFICATION = 'filter.recaptcha_post_verification';
    case RECAPTCHA_VERIFICATION_EXCEPTION = 'filter.recaptcha_verification_exception';
    case RECAPTCHA_AVAILABLE_PAGES = 'filter.recaptcha_available_pages';

    // Generic filters
    case ADMIN_SITE_ONLY = 'filter.admin_site_only';
}
