<?php

namespace App\Exceptions;

use Exception;

class SmartInvoiceException extends Exception
{
    public static function serviceDisabled(): self
    {
        return new self(__('smart_invoice.service_disabled'));
    }

    public static function attachmentsMissing(): self
    {
        return new self(__('smart_invoice.attachments_required'));
    }

    public static function requestFailed(string $reason): self
    {
        return new self($reason);
    }
}
