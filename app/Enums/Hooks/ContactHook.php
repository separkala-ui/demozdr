<?php

namespace App\Enums\Hooks;

enum ContactHook: string
{
    // Actions
    case CREATED = 'contact_created';
    case UPDATED = 'contact_updated';
    case DELETED = 'contact_deleted';
    case EMAIL_SENT = 'contact_email_sent';
    case STATUS_CHANGED = 'contact_status_changed';

    // Filters
    case LIST_PAGE_TABLE_HEADER_BEFORE_ACTION = 'contact_list_page_table_header_before_action';
    case FORM_FIELDS = 'contact_form_fields';
    case VALIDATION_RULES = 'contact_validation_rules';
    case EXPORT_DATA = 'contact_export_data';
}
