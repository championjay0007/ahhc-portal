@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Reports Center</h1>
        <p class="text-gray-600 mt-2">Generate, export, and manage enterprise reports</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Exports Card -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Total Exports</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" id="total-exports">-</p>
                    <p class="text-gray-500 text-xs mt-1">All time</p>
                </div>
                <div class="text-blue-500 text-4xl">📊</div>
            </div>
        </div>

        <!-- This Month Card -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">This Month</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" id="this-month">-</p>
                    <p class="text-gray-500 text-xs mt-1">Exports</p>
                </div>
                <div class="text-green-500 text-4xl">📈</div>
            </div>
        </div>

        <!-- Most Used Format Card -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Popular Format</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2 uppercase" id="popular-format">-</p>
                    <p class="text-gray-500 text-xs mt-1">Most used</p>
                </div>
                <div class="text-purple-500 text-4xl">📁</div>
            </div>
        </div>

        <!-- Records Exported Card -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Records Exported</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" id="records-exported">-</p>
                    <p class="text-gray-500 text-xs mt-1">Total</p>
                </div>
                <div class="text-orange-500 text-4xl">📝</div>
            </div>
        </div>
    </div>

    <!-- Generate Report Section -->
    <div class="bg-white rounded-lg shadow p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Generate Report</h2>
        <form id="report-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Report Type -->
            <div>
                <label class="block text-gray-700 font-bold mb-2">Report Type</label>
                <select name="report_type" id="report_type" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-blue-500" required>
                    <option value="">Select a report type...</option>
                </select>
            </div>

            <!-- Export Format -->
            <div>
                <label class="block text-gray-700 font-bold mb-2">Export Format</label>
                <select name="export_format" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-blue-500" required>
                    <option value="">Select format...</option>
                    <option value="csv">CSV</option>
                    <option value="excel">Excel (XLSX)</option>
                    <option value="pdf">PDF</option>
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <label class="block text-gray-700 font-bold mb-2">Start Date (Optional)</label>
                <input type="date" name="start_date" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-bold mb-2">End Date (Optional)</label>
                <input type="date" name="end_date" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-blue-500">
            </div>

            <!-- Participant Filter -->
            <div>
                <label class="block text-gray-700 font-bold mb-2">Participant (Optional)</label>
                <select name="participant_id" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-blue-500">
                    <option value="">All participants</option>
                </select>
            </div>

            <!-- Worker/Manager Filter -->
            <div>
                <label class="block text-gray-700 font-bold mb-2">Worker/Manager (Optional)</label>
                <select name="worker_id" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-blue-500">
                    <option value="">All workers/managers</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-gray-700 font-bold mb-2">Status (Optional)</label>
                <select name="status" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-blue-500">
                    <option value="">All statuses</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 md:col-span-2 lg:col-span-3">
                <button type="button" onclick="previewReport()" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition">
                    👁️ Preview
                </button>
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                    📥 Export Report
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Exports Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Recent Exports -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Recent Exports</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Report Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Format</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Records</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="exports-table">
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Exporters -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Top Exporters</h2>
            <div id="top-exporters" class="space-y-4">
                <p class="text-center text-gray-500">Loading...</p>
            </div>
        </div>
    </div>

    <!-- Report & Format Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Report Type Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Report Statistics</h2>
            <div id="report-stats" class="space-y-3">
                <p class="text-center text-gray-500">Loading...</p>
            </div>
        </div>

        <!-- Format Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Export Format Usage</h2>
            <div id="format-stats" class="space-y-3">
                <p class="text-center text-gray-500">Loading...</p>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="preview-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-96 overflow-auto">
        <div class="sticky top-0 px-6 py-4 border-b border-gray-200 bg-white">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">Report Preview</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
        </div>
        <div class="px-6 py-4">
            <div id="preview-content" class="text-gray-500">Loading...</div>
        </div>
    </div>
</div>

<script>
    let reportTypes = {};
    let exportFormats = {};

    function loadDashboard() {
        fetch('/portal/admin/reports/')
            .then(response => response.json())
            .then(data => {
                document.getElementById('total-exports').textContent = data.stats.total_exports;
                document.getElementById('this-month').textContent = data.stats.exports_this_month;
                document.getElementById('popular-format').textContent = data.stats.most_used_format || 'N/A';
                document.getElementById('records-exported').textContent = data.stats.total_records_exported;
                
                loadRecentExports(data.history);
                loadReportStats(data.report_stats);
                loadFormatStats(data.format_stats);
            })
            .catch(error => console.error('Error loading dashboard:', error));
    }

    function loadAvailableReports() {
        fetch('/portal/admin/reports/available')
            .then(response => response.json())
            .then(data => {
                reportTypes = data.report_types;
                exportFormats = data.export_formats;
                
                const select = document.getElementById('report_type');
                Object.entries(reportTypes).forEach(([key, label]) => {
                    const option = document.createElement('option');
                    option.value = key;
                    option.textContent = label;
                    select.appendChild(option);
                });
            });
    }

    function loadRecentExports(history) {
        const tbody = document.getElementById('exports-table');
        if (!history || history.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No exports yet</td></tr>';
            return;
        }
        
        tbody.innerHTML = history.map(item => `
            <tr>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${item.report_type}</td>
                <td class="px-6 py-4 text-sm text-gray-600 uppercase">${item.export_format}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${item.record_count}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${item.user}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${item.exported_at}</td>
            </tr>
        `).join('');
    }

    function loadReportStats(stats) {
        const container = document.getElementById('report-stats');
        if (!stats || stats.length === 0) {
            container.innerHTML = '<p class="text-center text-gray-500">No data</p>';
            return;
        }
        
        container.innerHTML = stats.map(stat => `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                <span class="text-gray-700">${stat.report_type}</span>
                <span class="text-right">
                    <div class="text-sm font-bold text-blue-600">${stat.exports_count} exports</div>
                    <div class="text-xs text-gray-500">${stat.total_records} records</div>
                </span>
            </div>
        `).join('');
    }

    function loadFormatStats(stats) {
        const container = document.getElementById('format-stats');
        if (!stats || stats.length === 0) {
            container.innerHTML = '<p class="text-center text-gray-500">No data</p>';
            return;
        }
        
        container.innerHTML = stats.map(stat => `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                <span class="text-gray-700 uppercase">${stat.format}</span>
                <span class="text-right">
                    <div class="text-sm font-bold text-purple-600">${stat.exports} exports</div>
                    <div class="text-xs text-gray-500">${stat.total_size}</div>
                </span>
            </div>
        `).join('');
    }

    function previewReport() {
        const formData = new FormData(document.getElementById('report-form'));
        const data = Object.fromEntries(formData);

        if (!data.report_type) {
            alert('Please select a report type');
            return;
        }

        document.getElementById('preview-content').innerHTML = 'Loading preview...';
        document.getElementById('preview-modal').classList.remove('hidden');

        fetch('/portal/admin/reports/preview', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            const preview = `
                <div>
                    <p class="mb-4"><strong>Report Type:</strong> ${data.report_type}</p>
                    <p class="mb-4"><strong>Total Records:</strong> ${data.record_count}</p>
                    <div class="mt-6">
                        <h4 class="font-bold mb-3">Preview (First 5 records):</h4>
                        <table class="w-full text-sm border">
                            <thead class="bg-gray-100">
                                <tr>
                                    ${Object.keys(data.preview_data[0] || {}).map(key => 
                                        '<th class="border px-3 py-2 text-left">' + key + '</th>'
                                    ).join('')}
                                </tr>
                            </thead>
                            <tbody>
                                ${data.preview_data.map(row => 
                                    '<tr>' + 
                                    Object.values(row).map(val => 
                                        '<td class="border px-3 py-2">' + val + '</td>'
                                    ).join('') + 
                                    '</tr>'
                                ).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            document.getElementById('preview-content').innerHTML = preview;
        })
        .catch(error => {
            document.getElementById('preview-content').innerHTML = '<p class="text-red-600">Error loading preview: ' + error.message + '</p>';
        });
    }

    function closeModal() {
        document.getElementById('preview-modal').classList.add('hidden');
    }

    function submitReport(event) {
        event.preventDefault();
        const formData = new FormData(document.getElementById('report-form'));
        const data = Object.fromEntries(formData);

        if (!data.report_type) {
            alert('Please select a report type');
            return;
        }

        // Submit and download
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/portal/admin/reports/export';
        form.style.display = 'none';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrfInput);

        Object.entries(data).forEach(([key, value]) => {
            if (value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    document.getElementById('report-form').addEventListener('submit', submitReport);

    // Load data on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboard();
        loadAvailableReports();
        // Refresh every 5 minutes
        setInterval(loadDashboard, 300000);
    });
</script>
@endsection
