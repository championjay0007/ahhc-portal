@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Care Review Management</h1>
        <p class="text-gray-600 mt-2">Monitor and manage monthly care reviews for participants</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Reviews Due Card -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Reviews Due</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" id="due-count">-</p>
                    <p class="text-gray-500 text-xs mt-1">Next 7 days</p>
                </div>
                <div class="text-yellow-500 text-4xl">📋</div>
            </div>
        </div>

        <!-- Reviews Overdue Card -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Overdue</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" id="overdue-count">-</p>
                    <p class="text-gray-500 text-xs mt-1">Require immediate attention</p>
                </div>
                <div class="text-red-500 text-4xl">⚠️</div>
            </div>
        </div>

        <!-- Completed Reviews Card -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Completed</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" id="completed-count">-</p>
                    <p class="text-gray-500 text-xs mt-1">This month</p>
                </div>
                <div class="text-green-500 text-4xl">✓</div>
            </div>
        </div>

        <!-- Compliance Rate Card -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">Compliance Rate</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" id="compliance-rate">-</p>
                    <p class="text-gray-500 text-xs mt-1">Overall completion %</p>
                </div>
                <div class="text-blue-500 text-4xl">📊</div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mb-8 flex gap-4 flex-wrap">
        <a href="#create-review" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + New Review
        </a>
        <button onclick="exportOutstanding()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            📥 Export Outstanding
        </button>
        <button onclick="loadDashboard()" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            🔄 Refresh Data
        </button>
    </div>

    <!-- Tabs for Different Views -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8" aria-label="Tabs" role="tablist">
                <button onclick="switchTab('due')" role="tab" class="tab-button active py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600">
                    Due Reviews
                </button>
                <button onclick="switchTab('overdue')" role="tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700">
                    Overdue Reviews
                </button>
                <button onclick="switchTab('completed')" role="tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700">
                    Completed
                </button>
                <button onclick="switchTab('workload')" role="tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700">
                    Care Manager Workload
                </button>
            </nav>
        </div>

        <!-- Due Reviews Tab -->
        <div id="due-tab" class="tab-content p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Participant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Care Manager</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="due-reviews-body">
                        <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Overdue Reviews Tab -->
        <div id="overdue-tab" class="tab-content hidden p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Participant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Care Manager</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Overdue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="overdue-reviews-body">
                        <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Completed Reviews Tab -->
        <div id="completed-tab" class="tab-content hidden p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Participant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Care Manager</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="completed-reviews-body">
                        <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Workload Tab -->
        <div id="workload-tab" class="tab-content hidden p-6">
            <div class="space-y-4" id="workload-body">
                <p class="text-center text-gray-500">Loading...</p>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="review-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">New Care Review</h2>
        </div>
        <div class="px-6 py-4">
            <form id="review-form" onsubmit="submitReview(event)">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Participant</label>
                    <select name="participant_id" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        <option value="">Select a participant...</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Care Manager</label>
                    <select name="care_manager_id" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        <option value="">Select a care manager...</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Review Type</label>
                    <select name="review_type" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        <option value="">Select review type...</option>
                    </select>
                </div>
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Create
                    </button>
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentTab = 'due';

    function switchTab(tabName) {
        currentTab = tabName;
        
        // Update button styles
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });
        event.target.classList.add('border-blue-500', 'text-blue-600');
        
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });
        
        // Show selected tab
        document.getElementById(`${tabName}-tab`).classList.remove('hidden');
    }

    function loadDashboard() {
        fetch('/portal/admin/care-reviews/dashboard/stats')
            .then(response => response.json())
            .then(data => {
                document.getElementById('due-count').textContent = data.reviews_due.count;
                document.getElementById('overdue-count').textContent = data.reviews_overdue.count;
                document.getElementById('completed-count').textContent = data.reviews_completed.count;
                document.getElementById('compliance-rate').textContent = data.compliance_rate.rate + '%';
                
                // Load table data
                loadDueReviews(data.reviews_due.reviews);
                loadOverdueReviews(data.reviews_overdue.reviews);
                loadCompletedReviews(data.reviews_completed.recent);
                loadWorkload(data);
            })
            .catch(error => console.error('Error loading dashboard:', error));
    }

    function loadDueReviews(reviews) {
        const tbody = document.getElementById('due-reviews-body');
        if (!reviews || reviews.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No due reviews</td></tr>';
            return;
        }
        
        tbody.innerHTML = reviews.map(review => `
            <tr>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${review.participant_name}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${review.care_manager}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${review.due_date}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${review.review_type}</td>
                <td class="px-6 py-4 text-sm">
                    <a href="/portal/admin/care-reviews/${review.id}" class="text-blue-600 hover:text-blue-800">View</a>
                </td>
            </tr>
        `).join('');
    }

    function loadOverdueReviews(reviews) {
        const tbody = document.getElementById('overdue-reviews-body');
        if (!reviews || reviews.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No overdue reviews</td></tr>';
            return;
        }
        
        tbody.innerHTML = reviews.map(review => `
            <tr class="bg-red-50">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${review.participant_name}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${review.care_manager}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${review.due_date}</td>
                <td class="px-6 py-4 text-sm text-red-600 font-bold">${review.days_overdue} days</td>
                <td class="px-6 py-4 text-sm">
                    <a href="/portal/admin/care-reviews/${review.id}" class="text-blue-600 hover:text-blue-800">Complete</a>
                </td>
            </tr>
        `).join('');
    }

    function loadCompletedReviews(reviews) {
        const tbody = document.getElementById('completed-reviews-body');
        if (!reviews || reviews.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No completed reviews</td></tr>';
            return;
        }
        
        tbody.innerHTML = reviews.map(review => `
            <tr>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${review.participant_name}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${review.care_manager}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${review.completed_date}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${review.completed_by || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${review.review_type}</td>
            </tr>
        `).join('');
    }

    function loadWorkload(data) {
        const container = document.getElementById('workload-body');
        // Workload data would be loaded here - for now showing placeholder
        container.innerHTML = '<p class="text-center text-gray-600">Care manager workload data loading...</p>';
    }

    function exportOutstanding() {
        window.location.href = '/portal/admin/care-reviews/export/outstanding';
    }

    // Load dashboard on page load
    document.addEventListener('DOMContentLoaded', loadDashboard);
    
    // Refresh every 5 minutes
    setInterval(loadDashboard, 300000);
</script>

<style>
    .tab-button {
        transition: all 0.3s ease;
    }
    
    .tab-content {
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
</style>
@endsection
