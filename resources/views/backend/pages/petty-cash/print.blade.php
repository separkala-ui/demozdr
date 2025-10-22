<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارش تنخواه گردان - {{ $ledger->branch_name }}</title>
    <style>
        :root {
            color-scheme: light;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 32px 24px 64px;
            background: #f4f6fb;
            font-family: 'Tahoma', sans-serif;
            color: #1a202c;
            direction: rtl;
            line-height: 1.6;
        }

        .sheet {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 40px 48px;
            box-shadow: 0 15px 40px rgba(15, 23, 42, 0.12);
        }

        .report-header {
            border-bottom: 3px solid #1f2937;
            padding-bottom: 18px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
        }

        .report-header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            color: #111827;
        }

        .report-header p {
            margin: 4px 0 0;
            color: #4b5563;
            font-size: 13px;
        }

        .header-meta {
            text-align: left;
            font-size: 12px;
            color: #6b7280;
            border-right: 2px solid #d1d5db;
            padding-right: 16px;
        }

        .meta-table-section {
            margin-bottom: 28px;
        }

        .meta-table-section h2 {
            font-size: 16px;
            margin: 0 0 12px;
            color: #1f2937;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .classic-table thead th {
            background: #f3f4f6;
            color: #111827;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid #1f2937;
            padding: 10px 12px;
            text-align: right;
        }

        .classic-table tbody td {
            border: 1px solid #1f2937;
            padding: 10px 12px;
            font-size: 13px;
            color: #1f2937;
            vertical-align: top;
        }

        .summary-box {
            border: 1px solid #1f2937;
            border-radius: 8px;
            padding: 18px 20px;
            margin-bottom: 28px;
            background: #fafafa;
        }

        .summary-box h3 {
            margin: 0 0 12px;
            font-size: 15px;
            font-weight: 600;
            color: #111827;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .summary-item {
            font-size: 13px;
            color: #1f2937;
        }

        .summary-item strong {
            display: block;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .transactions-section h2 {
            margin: 0 0 12px;
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
        }

        .transactions-section .description-cell {
            min-width: 160px;
        }

        .report-footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }

        .no-print {
            max-width: 960px;
            margin: 0 auto 24px;
        }

        .control-panel {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 24px 28px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        .control-panel h2 {
            margin: 0 0 16px;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .control-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px 18px;
            margin-bottom: 18px;
        }

        .control-grid label {
            display: flex;
            flex-direction: column;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
        }

        .control-grid input,
        .control-grid textarea {
            margin-top: 6px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 13px;
            color: #0f172a;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .control-grid input:focus,
        .control-grid textarea:focus {
            outline: none;
            border-color: #1f2937;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.18);
        }

        .meta-form {
            border-top: 1px dashed #d1d5db;
            padding-top: 18px;
        }

        .meta-form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .meta-form-header h3 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #111827;
        }

        .meta-row {
            display: grid;
            grid-template-columns: repeat(7, minmax(140px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
            align-items: flex-start;
        }

        .meta-row input {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 13px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .meta-row input[readonly] {
            background: #f8fafc;
            color: #475569;
            cursor: not-allowed;
        }

        .meta-row input:focus {
            outline: none;
            border-color: #1f2937;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.18);
        }

        .control-hint {
            margin: 8px 0 0;
            font-size: 12px;
            color: #6b7280;
        }

        .control-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        .print-button {
            background: linear-gradient(135deg, #1f2937, #111827);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 10px 20px rgba(17, 24, 39, 0.25);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(17, 24, 39, 0.28);
        }

        .js-hidden {
            display: none;
        }

        .print-icon {
            display: inline-block;
            width: 18px;
            height: 14px;
            border: 2px solid #ffffff;
            border-radius: 3px;
            position: relative;
            box-sizing: border-box;
        }

        .print-icon::before {
            content: '';
            position: absolute;
            top: -6px;
            left: 1px;
            right: 1px;
            height: 6px;
            border: 2px solid #ffffff;
            border-bottom: none;
            border-radius: 2px 2px 0 0;
        }

        .print-icon::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 2px;
            right: 2px;
            height: 6px;
            border: 2px solid #ffffff;
            border-radius: 0 0 2px 2px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 12px 22px;
            border-radius: 10px;
            border: 1px solid #0f172a;
            color: #0f172a;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .back-link:hover {
            background: #0f172a;
            color: #ffffff;
        }

        .empty-state {
            text-align: center;
            padding: 36px;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
            color: #475569;
            font-size: 13px;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .sheet {
                border: none;
                border-radius: 0;
                box-shadow: none;
                padding: 32px 40px;
            }

            .no-print {
                display: none !important;
            }

            .report-header {
                margin-top: 0;
            }

            .classic-table thead th,
            .classic-table tbody td {
                font-size: 12px;
                padding: 8px 10px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 16px;
            }

            .sheet {
                padding: 24px;
            }

            .report-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .meta-row {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body>
@php
    $today = verta();
    $defaultNote = match ($period) {
        'today' => 'دوره گزارش: امروز (' . $today->format('Y/m/d') . ')',
        'yesterday' => 'دوره گزارش: دیروز (' . (clone $today)->subDay()->format('Y/m/d') . ')',
        '3days' => 'دوره گزارش: سه روز گذشته',
        '7days' => 'دوره گزارش: هفت روز گذشته',
        'month' => 'دوره گزارش: ماه جاری (' . (clone $today)->format('Y/m') . ')',
        default => 'دوره گزارش: از ' . ($dateFrom ?? 'شروع') . ' تا ' . ($dateTo ?? 'اکنون'),
    };
    $defaultReportDate = verta()->format('Y/m/d');
    $defaultManager = optional(auth()->user())->full_name ?? '';
    $periodIncoming = (float) ($totals['incoming'] ?? 0);
    $periodOutgoing = (float) ($totals['outgoing'] ?? 0);
    $periodAdjustmentPositive = (float) ($totals['adjustment_positive'] ?? 0);
    $periodAdjustmentNegative = (float) ($totals['adjustment_negative'] ?? 0);
    $overallIncoming = (float) ($overallTotals['total_incoming'] ?? 0);
    $overallOutgoing = (float) ($overallTotals['total_outgoing'] ?? 0);

    $defaultMeta = [
        'branch' => $ledger->branch_name,
        'manager' => $defaultManager,
        'periodIncoming' => $periodIncoming,
        'totalIncoming' => $overallIncoming,
        'periodOutgoing' => $periodOutgoing,
        'totalOutgoing' => $overallOutgoing,
        'reportDate' => $defaultReportDate,
    ];
@endphp

<div
    class="no-print control-panel"
    id="reportControls"
    data-defaults='@json($defaultMeta)'
>
    <h2>تنظیمات گزارش</h2>

    <div class="control-grid">
        <label>
            عنوان گزارش
            <input type="text" id="headerTitleInput" value="گزارش تنخواه گردان">
        </label>
        <label>
            سرتیتر فرعی
            <input type="text" id="headerSubtitleInput" value="شعبه {{ $ledger->branch_name }}">
        </label>
        <label>
            توضیح هدر
            <textarea id="headerNoteInput" rows="2">{{ $defaultNote }}</textarea>
        </label>
    </div>

    <div class="meta-form">
        <div class="meta-form-header">
            <h3>اطلاعات معرفی گزارش</h3>
            <span class="control-hint">با تکمیل ردیف جاری، ردیف جدید به صورت خودکار ظاهر می‌شود.</span>
        </div>
        <div id="metaRows">
            <div class="meta-row" data-static="true">
                <input type="text" data-field="branch" placeholder="تنخواه شعبه" value="{{ $ledger->branch_name }}" data-required="true" readonly>
                <input type="text" data-field="manager" placeholder="تنخواه گردان" value="{{ $defaultManager }}">
                <input type="number" data-field="periodIncoming" placeholder="ورودی دوره (ریال)" value="{{ $periodIncoming }}" min="0" step="1" readonly>
                <input type="number" data-field="totalIncoming" placeholder="ورودی تجمعی (ریال)" value="{{ $overallIncoming }}" min="0" step="1" readonly>
                <input type="number" data-field="periodOutgoing" placeholder="خروجی دوره (ریال)" value="{{ $periodOutgoing }}" min="0" step="1" readonly>
                <input type="number" data-field="totalOutgoing" placeholder="خروجی تجمعی (ریال)" value="{{ $overallOutgoing }}" min="0" step="1" readonly>
                <input type="text" data-field="reportDate" placeholder="تاریخ گزارش" value="{{ $defaultReportDate }}">
            </div>
        </div>
    </div>
</div>

<div class="sheet" id="reportSheet">
    <header class="report-header">
        <div>
            <h1 id="reportTitle">گزارش تنخواه گردان</h1>
            <p id="reportSubtitle">شعبه {{ $ledger->branch_name }}</p>
            <p id="reportNote">{{ $defaultNote }}</p>
        </div>
        <div class="header-meta">
            <div>تاریخ چاپ: {{ verta()->format('Y/m/d H:i') }}</div>
            <div>تهیه شده توسط سیستم تنخواه گردان</div>
        </div>
    </header>

    <section class="meta-table-section">
        <h2>ثبت اطلاعات تنخواه</h2>
        <table class="classic-table">
            <thead>
            <tr>
                <th>تنخواه شعبه</th>
                <th>تنخواه گردان</th>
                <th>ورودی دوره (ریال)</th>
                <th>ورودی تجمعی شعبه (ریال)</th>
                <th>خروجی دوره (ریال)</th>
                <th>خروجی تجمعی (ریال)</th>
                <th>تاریخ گزارش</th>
            </tr>
            </thead>
            <tbody id="metaDisplayBody">
            <tr class="js-hidden">
                <td>{{ $ledger->branch_name }}</td>
                <td>{{ $defaultManager ?: '---' }}</td>
                <td>{{ number_format($periodIncoming) }}</td>
                <td>{{ number_format($overallIncoming) }}</td>
                <td>{{ number_format($periodOutgoing) }}</td>
                <td>{{ number_format($overallOutgoing) }}</td>
                <td>{{ $defaultReportDate }}</td>
            </tr>
            </tbody>
        </table>
    </section>

    <section class="summary-box">
        <h3>خلاصه وضعیت تنخواه</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <strong>مانده اولیه تأیید شده</strong>
                {{ number_format($overallTotals['opening_balance'] ?? 0) }} ریال
            </div>
            <div class="summary-item">
                <strong>سقف مجاز</strong>
                {{ number_format($overallTotals['limit_amount'] ?? 0) }} ریال
            </div>
            <div class="summary-item">
                <strong>مانده فعلی شعبه</strong>
                {{ number_format($overallTotals['current_balance'] ?? 0) }} ریال
            </div>
            <div class="summary-item">
                <strong>ورودی تأیید شده این دوره</strong>
                {{ number_format($periodIncoming) }} ریال
            </div>
            <div class="summary-item">
                <strong>خروجی تأیید شده این دوره</strong>
                {{ number_format($periodOutgoing) }} ریال
            </div>
            <div class="summary-item">
                <strong>تعدیلات مثبت این دوره</strong>
                {{ number_format($periodAdjustmentPositive) }} ریال
            </div>
            <div class="summary-item">
                <strong>تعدیلات منفی این دوره</strong>
                {{ number_format(abs($periodAdjustmentNegative)) }} ریال
            </div>
            <div class="summary-item">
                <strong>ورودی تجمعی تأیید شده (شامل مانده اولیه)</strong>
                {{ number_format($overallIncoming) }} ریال
            </div>
            <div class="summary-item">
                <strong>خروجی تجمعی تأیید شده</strong>
                {{ number_format($overallOutgoing) }} ریال
            </div>
            <div class="summary-item">
                <strong>شارژهای در انتظار تأیید</strong>
                {{ number_format($overallTotals['pending_charges_total'] ?? 0) }} ریال
            </div>
            <div class="summary-item">
                <strong>هزینه‌های در انتظار تأیید</strong>
                {{ number_format($overallTotals['pending_expenses_total'] ?? 0) }} ریال
            </div>
        </div>
    </section>

    <section class="transactions-section">
        <h2>ریز تراکنش‌ها</h2>
        @if($transactions->isEmpty())
            <div class="empty-state">
                هیچ تراکنشی برای دوره انتخابی ثبت نشده است.
            </div>
        @else
            <table class="classic-table">
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>تاریخ شمسی</th>
                    <th>نوع</th>
                    <th>وضعیت</th>
                    <th>مبلغ (ریال)</th>
                    <th class="description-cell">شرح</th>
                    <th>شماره مرجع</th>
                </tr>
                </thead>
                <tbody>
                @foreach($transactions as $index => $transaction)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ verta($transaction->transaction_date)->format('Y/m/d H:i') }}</td>
                        <td>
                            @if($transaction->type === \App\Models\PettyCashTransaction::TYPE_CHARGE)
                                شارژ
                            @elseif($transaction->type === \App\Models\PettyCashTransaction::TYPE_EXPENSE)
                                هزینه
                            @else
                                تعدیل
                            @endif
                        </td>
                        <td>
                            @switch($transaction->status)
                                @case(\App\Models\PettyCashTransaction::STATUS_APPROVED)
                                    تایید شده
                                    @break
                                @case(\App\Models\PettyCashTransaction::STATUS_SUBMITTED)
                                    ارسال شده
                                    @break
                                @case(\App\Models\PettyCashTransaction::STATUS_REJECTED)
                                    رد شده
                                    @break
                                @case(\App\Models\PettyCashTransaction::STATUS_NEEDS_CHANGES)
                                    نیاز به اصلاح
                                    @break
                                @case(\App\Models\PettyCashTransaction::STATUS_UNDER_REVIEW)
                                    در حال بررسی مدیریت
                                    @break
                                @default
                                    پیش‌نویس
                            @endswitch
                        </td>
                        <td>{{ number_format($transaction->amount) }}</td>
                        <td>{{ $transaction->description ?? '---' }}</td>
                        <td>{{ $transaction->reference_number ?? '---' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <footer class="report-footer">
        این گزارش با هدف مستندسازی گردش تنخواه و تایید مالی تهیه شده است.
    </footer>
</div>

<div class="no-print control-actions">
    <button type="button" class="print-button" onclick="window.print()">
        <span aria-hidden="true" class="print-icon"></span>
        چاپ رسمی گزارش
    </button>
    <a href="{{ route('admin.petty-cash.index', ['ledger' => $ledger->id]) }}" class="back-link">
        بازگشت به فهرست شعبه
    </a>
</div>

<template id="metaRowTemplate">
    <div class="meta-row">
        <input type="text" data-field="branch" placeholder="تنخواه شعبه" data-required="true">
        <input type="text" data-field="manager" placeholder="تنخواه گردان">
        <input type="number" data-field="periodIncoming" placeholder="ورودی دوره (ریال)" min="0" step="1">
        <input type="number" data-field="totalIncoming" placeholder="ورودی تجمعی (ریال)" min="0" step="1">
        <input type="number" data-field="periodOutgoing" placeholder="خروجی دوره (ریال)" min="0" step="1">
        <input type="number" data-field="totalOutgoing" placeholder="خروجی تجمعی (ریال)" min="0" step="1">
        <input type="text" data-field="reportDate" placeholder="تاریخ گزارش">
    </div>
</template>

<script>
    (() => {
        const numberFormatter = new Intl.NumberFormat('fa-IR');
        const controls = document.getElementById('reportControls');
        const defaults = controls ? JSON.parse(controls.dataset.defaults || '{}') : {};

        const titleInput = document.getElementById('headerTitleInput');
        const subtitleInput = document.getElementById('headerSubtitleInput');
        const noteInput = document.getElementById('headerNoteInput');

        const titleOutput = document.getElementById('reportTitle');
        const subtitleOutput = document.getElementById('reportSubtitle');
        const noteOutput = document.getElementById('reportNote');

        const metaRows = document.getElementById('metaRows');
        const metaDisplayBody = document.getElementById('metaDisplayBody');
        const template = document.getElementById('metaRowTemplate');

        const syncHeader = () => {
            if (titleOutput && titleInput) {
                titleOutput.textContent = titleInput.value.trim() || 'گزارش تنخواه گردان';
            }
            if (subtitleOutput && subtitleInput) {
                subtitleOutput.textContent = subtitleInput.value.trim() || '';
            }
            if (noteOutput && noteInput) {
                noteOutput.textContent = noteInput.value.trim();
            }
        };

        [titleInput, subtitleInput, noteInput].forEach(element => {
            if (element) {
                element.addEventListener('input', syncHeader);
            }
        });

        const numericFields = ['periodIncoming', 'totalIncoming', 'periodOutgoing', 'totalOutgoing'];

        const harvestRows = () => {
            const rows = [];
            metaRows.querySelectorAll('.meta-row').forEach(row => {
                const record = {};
                let hasValue = false;

                row.querySelectorAll('input[data-field]').forEach(input => {
                    const key = input.dataset.field;
                    let value = input.value.trim();

                    if (numericFields.includes(key) && value !== '') {
                        const numeric = Number(value);
                        if (!Number.isNaN(numeric)) {
                            record[key] = numeric;
                            hasValue = hasValue || numeric !== 0;
                        }
                    } else if (value !== '') {
                        record[key] = value;
                        hasValue = true;
                    }
                });

                if (hasValue) {
                    rows.push(record);
                }
            });
            return rows;
        };

        const renderDisplayTable = () => {
            if (!metaDisplayBody) {
                return;
            }
            const rows = harvestRows();
            metaDisplayBody.innerHTML = '';

            if (!rows.length) {
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = '<td colspan="7" style="text-align:center; color:#6b7280;">هیچ اطلاعات مقدمه‌ای ثبت نشده است.</td>';
                metaDisplayBody.appendChild(emptyRow);
                return;
            }

            rows.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${row.branch ?? '---'}</td>
                    <td>${row.manager ?? '---'}</td>
                    <td>${row.periodIncoming != null ? numberFormatter.format(row.periodIncoming) : '---'}</td>
                    <td>${row.totalIncoming != null ? numberFormatter.format(row.totalIncoming) : '---'}</td>
                    <td>${row.periodOutgoing != null ? numberFormatter.format(row.periodOutgoing) : '---'}</td>
                    <td>${row.totalOutgoing != null ? numberFormatter.format(row.totalOutgoing) : '---'}</td>
                    <td>${row.reportDate ?? '---'}</td>
                `;
                metaDisplayBody.appendChild(tr);
            });
        };

        const allRequiredFilled = row => {
            const requiredInputs = row.querySelectorAll('input[data-required="true"]');
            if (!requiredInputs.length) {
                return false;
            }

            return Array.from(requiredInputs).every(input => input.value.trim() !== '');
        };

        const ensureTrailingRow = () => {
            const rows = metaRows.querySelectorAll('.meta-row');
            const lastRow = rows[rows.length - 1];

            if (!lastRow || lastRow.dataset.static === 'true') {
                return;
            }

            if (allRequiredFilled(lastRow)) {
                appendRow();
            }
        };

        const bindRow = row => {
            row.querySelectorAll('input[data-field]').forEach(input => {
                input.addEventListener('input', () => {
                    renderDisplayTable();
                    ensureTrailingRow();
                });
            });
        };

        const appendRow = (initialValues = {}) => {
            if (!template) {
                return;
            }
            const clone = template.content.cloneNode(true);
            const row = clone.querySelector('.meta-row');
            metaRows.appendChild(clone);

            const appendedRow = metaRows.lastElementChild;
            appendedRow.querySelectorAll('input[data-field]').forEach(input => {
                const key = input.dataset.field;
                if (initialValues[key] !== undefined) {
                    input.value = initialValues[key];
                } else {
                    input.value = '';
                }
            });

            bindRow(appendedRow);
        };

        // Initialize default display table state.
        renderDisplayTable();
        ensureTrailingRow();

        // If initial row is empty, populate with defaults.
        const firstRowInputs = metaRows.querySelectorAll('.meta-row:first-child input[data-field]');
        if (firstRowInputs.length) {
            firstRowInputs.forEach(input => {
                const key = input.dataset.field;
                if (input.value.trim() === '' && defaults[key] !== undefined && defaults[key] !== null && defaults[key] !== '') {
                    input.value = defaults[key];
                }
            });
            renderDisplayTable();
            ensureTrailingRow();
        }

        // Bind existing rows.
        metaRows.querySelectorAll('.meta-row').forEach(bindRow);
        syncHeader();
    })();
</script>
</body>
</html>
