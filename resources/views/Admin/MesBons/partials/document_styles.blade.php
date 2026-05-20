<style>
    .bon-document {
        background: #eef1f5;
        padding: 18px 0;
    }

    .bon-sheet {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        padding: 14mm;
        background: #fff;
        color: #111827;
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 12px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
    }

    .doc-header,
    .info-grid,
    .items-table,
    .totals-table,
    .validation-table {
        width: 100%;
        border-collapse: collapse;
    }

    .doc-header td {
        border: 1px solid #1f2937;
        vertical-align: middle;
        padding: 8px;
    }

    .logo-cell {
        width: 24%;
        text-align: center;
    }

    .logo-cell img {
        max-width: 105px;
        max-height: 72px;
    }

    .logo-fallback {
        display: inline-block;
        width: 62px;
        height: 62px;
        line-height: 62px;
        border: 2px solid #111827;
        font-weight: 700;
        font-size: 22px;
    }

    .entity-cell {
        width: 46%;
        text-align: center;
        line-height: 1.45;
    }

    .entity-cell h2 {
        margin: 0 0 4px;
        font-size: 18px;
        text-transform: uppercase;
    }

    .ref-cell {
        width: 30%;
        text-align: center;
    }

    .ref-cell strong,
    .ref-cell span,
    .ref-cell small {
        display: block;
        margin: 3px 0;
    }

    .ref-cell strong {
        font-size: 15px;
    }

    .bon-sheet h1 {
        margin: 14px 0 12px;
        padding: 8px 0;
        border: 2px solid #111827;
        background: #f3f4f6;
        text-align: center;
        font-size: 20px;
        text-transform: uppercase;
        letter-spacing: 0;
    }

    .info-grid td {
        width: 50%;
        border: 1px solid #374151;
        padding: 8px;
        vertical-align: top;
    }

    .info-grid span,
    .description-block span {
        display: block;
        margin-bottom: 3px;
        color: #4b5563;
        font-size: 10px;
        text-transform: uppercase;
    }

    .info-grid strong {
        font-size: 12px;
    }

    .description-block {
        margin: 10px 0;
        border: 1px solid #374151;
        padding: 8px;
        min-height: 46px;
    }

    .description-block p {
        margin: 0;
        line-height: 1.45;
    }

    .items-table th,
    .items-table td {
        border: 1px solid #111827;
        padding: 6px;
        vertical-align: top;
    }

    .items-table th {
        background: #1f2937;
        color: #fff;
        font-size: 11px;
        text-transform: uppercase;
    }

    .center {
        text-align: center;
    }

    .right {
        text-align: right;
    }

    .muted {
        color: #6b7280;
    }

    .totals-table {
        margin: 12px 0 16px auto;
        width: 54%;
    }

    .totals-table td {
        border: 1px solid #111827;
        padding: 7px;
    }

    .totals-table td:last-child {
        text-align: right;
        font-weight: 700;
    }

    .totals-table .grand-total td:last-child {
        text-align: left;
    }

    .grand-total td {
        background: #f3f4f6;
        font-weight: 700;
    }

    .validation-table td {
        width: 25%;
        height: 78px;
        border: 1px solid #111827;
        padding: 8px;
        vertical-align: top;
    }

    .validation-table strong,
    .validation-table span {
        display: block;
    }

    .state {
        margin-top: 4px;
        font-size: 11px;
    }

    .state-valide {
        color: #047857;
    }

    .state-refuse {
        color: #b91c1c;
    }

    .state-attente {
        color: #92400e;
    }

    .signature-line {
        margin-top: 28px;
        border-top: 1px solid #6b7280;
    }

    .refusal-box {
        margin-top: 12px;
        border: 1px solid #b91c1c;
        padding: 8px;
        color: #7f1d1d;
        background: #fef2f2;
    }

    .doc-actions {
        width: 210mm;
        margin: 0 auto 12px;
        text-align: right;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        .bon-document {
            background: #fff;
            padding: 0;
        }

        .bon-sheet {
            width: auto;
            min-height: auto;
            margin: 0;
            padding: 0;
            box-shadow: none;
        }

        .doc-actions {
            display: none !important;
        }
    }
</style>
