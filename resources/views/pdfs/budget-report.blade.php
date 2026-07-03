<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Report - {{ $budget->participant->name ?? 'Participant' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
        }

        /* Header */
        .header {
            border-bottom: 3px solid #0066cc;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 28px;
            color: #0066cc;
            margin-bottom: 10px;
        }

        .header-meta {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }

        .header-meta-item {
            display: flex;
            flex-direction: column;
        }

        .header-meta-label {
            font-weight: bold;
            color: #0066cc;
        }

        /* Section */
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #0066cc;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        /* Participant Info */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 10px 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-label {
            font-weight: bold;
            color: #0066cc;
            width: 30%;
            background-color: #f9f9f9;
        }

        .info-value {
            color: #333;
        }

        /* Budget Summary */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .summary-card {
            background-color: #f0f4ff;
            border-left: 4px solid #0066cc;
            padding: 15px;
            border-radius: 4px;
        }

        .summary-card.warning {
            background-color: #fff3cd;
            border-left-color: #ff9800;
        }

        .summary-card.danger {
            background-color: #f8d7da;
            border-left-color: #dc3545;
        }

        .summary-card.success {
            background-color: #d4edda;
            border-left-color: #28a745;
        }

        .summary-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 24px;
            font-weight: bold;
            color: #0066cc;
        }

        .summary-card.warning .summary-value {
            color: #ff9800;
        }

        .summary-card.danger .summary-value {
            color: #dc3545;
        }

        .summary-card.success .summary-value {
            color: #28a745;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: #0066cc;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f0f4ff;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        /* Footer */
        .footer {
            border-top: 2px solid #e0e0e0;
            padding-top: 20px;
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #999;
        }

        .footer-note {
            margin-bottom: 10px;
            font-style: italic;
        }

        /* Page Break Utilities */
        .page-break {
            page-break-after: always;
        }

        /* Spacing Utilities */
        .mt-20 {
            margin-top: 20px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .text-muted {
            color: #999;
        }

        /* Quarter Display */
        .quarter-badge {
            display: inline-block;
            background-color: #0066cc;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
        }

        /* No Data Message */
        .no-data {
            text-align: center;
            padding: 30px;
            color: #999;
            background-color: #f9f9f9;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
        }

        /* Print Styles */
        @media print {
            body {
                background-color: white;
            }

            .container {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }

            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📊 Quarterly Budget Report</h1>
            <div class="header-meta">
                <div class="header-meta-item">
                    <span class="header-meta-label">Report Date</span>
                    <span>{{ now()->format('d/m/Y H:i') }}</span>
                </div>
                <div class="header-meta-item">
                    <span class="header-meta-label">Quarter</span>
                    <span>
                        @if ($budget->quarter_start_date && $budget->quarter_end_date)
                            {{ date('d/m/Y', strtotime($budget->quarter_start_date)) }} - {{ date('d/m/Y', strtotime($budget->quarter_end_date)) }}
                        @else
                            N/A
                        @endif
                    </span>
                </div>
                <div class="header-meta-item">
                    <span class="header-meta-label">Document ID</span>
                    <span>BDG-{{ $budget->id }}-{{ date('Ymd') }}</span>
                </div>
            </div>
        </div>

        <!-- Participant Information -->
        @if ($budget->participant)
        <div class="section">
            <div class="section-title">Participant Information</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell info-label">Name</div>
                    <div class="info-cell info-value">{{ $budget->participant->name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-cell info-label">Participant ID</div>
                    <div class="info-cell info-value">{{ $budget->participant->id }}</div>
                </div>
                @if ($budget->participant->email)
                <div class="info-row">
                    <div class="info-cell info-label">Email</div>
                    <div class="info-cell info-value">{{ $budget->participant->email }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Budget Summary -->
        <div class="section">
            <div class="section-title">Budget Summary</div>
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-label">Opening Balance</div>
                    <div class="summary-value">${{ number_format($openingBalance / 100, 2) }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Carry Over</div>
                    <div class="summary-value">${{ number_format($carryOver / 100, 2) }}</div>
                </div>
                <div class="summary-card {{ $remainingBalance < 0 ? 'danger' : ($remainingBalance < ($openingBalance / 4) ? 'warning' : 'success') }}">
                    <div class="summary-label">Total Available</div>
                    <div class="summary-value">${{ number_format($totalAvailable / 100, 2) }}</div>
                </div>
                <div class="summary-card {{ $remainingBalance < 0 ? 'danger' : 'success' }}">
                    <div class="summary-label">Remaining Balance</div>
                    <div class="summary-value">${{ number_format($remainingBalance / 100, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Budget Allocation -->
        <div class="section">
            <div class="section-title">Budget Allocation</div>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Committed Funds</td>
                        <td class="text-right"><strong>${{ number_format($committed / 100, 2) }}</strong></td>
                        <td class="text-right">{{ $totalAvailable > 0 ? number_format(($committed / $totalAvailable) * 100, 1) : 0 }}%</td>
                    </tr>
                    <tr>
                        <td>Approved Spend</td>
                        <td class="text-right"><strong>${{ number_format($approved / 100, 2) }}</strong></td>
                        <td class="text-right">{{ $totalAvailable > 0 ? number_format(($approved / $totalAvailable) * 100, 1) : 0 }}%</td>
                    </tr>
                    <tr>
                        <td>Paid Spend</td>
                        <td class="text-right"><strong>${{ number_format($paid / 100, 2) }}</strong></td>
                        <td class="text-right">{{ $totalAvailable > 0 ? number_format(($paid / $totalAvailable) * 100, 1) : 0 }}%</td>
                    </tr>
                    <tr style="background-color: #f0f4ff; font-weight: bold;">
                        <td>Total Used</td>
                        <td class="text-right">${{ number_format(($committed + $approved + $paid) / 100, 2) }}</td>
                        <td class="text-right">{{ $totalAvailable > 0 ? number_format((($committed + $approved + $paid) / $totalAvailable) * 100, 1) : 0 }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Status Indicators -->
        <div class="section">
            <div class="section-title">Budget Status</div>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                <div class="text-center">
                    <div class="badge {{ $remainingBalance >= 0 ? 'badge-success' : 'badge-danger' }}">
                        {{ $remainingBalance >= 0 ? 'HEALTHY' : 'OVERCOMMITTED' }}
                    </div>
                    <div class="text-muted" style="margin-top: 10px; font-size: 12px;">Balance Status</div>
                </div>
                <div class="text-center">
                    <div class="badge badge-info">QUARTER</div>
                    <div class="text-muted" style="margin-top: 10px; font-size: 12px;">Active Quarter</div>
                </div>
                <div class="text-center">
                    <div class="badge {{ ($committed + $approved) > 0 ? 'badge-warning' : 'badge-success' }}">
                        {{ ($committed + $approved) > 0 ? 'IN PROGRESS' : 'AVAILABLE' }}
                    </div>
                    <div class="text-muted" style="margin-top: 10px; font-size: 12px;">Usage Status</div>
                </div>
                <div class="text-center">
                    <div class="badge badge-success">CURRENT</div>
                    <div class="text-muted" style="margin-top: 10px; font-size: 12px;">Report Status</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-note">This is an automatically generated budget report. Please retain for your records.</div>
            <div>Allegiance Heart & Home Care Portal | Quarterly Budget Management System</div>
            <div style="margin-top: 10px; color: #ccc;">Generated on {{ now()->format('d/m/Y \a\t H:i:s') }}</div>
        </div>
    </div>
</body>
</html>
