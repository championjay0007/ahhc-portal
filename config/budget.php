<?php

return [
    // How to compute 'used' budget from invoices: 'approved' or 'paid'
    'used_mode' => env('BUDGET_USED_MODE', 'approved'),

    // Whether to include pre-approval committed amounts in remaining calculation
    'include_committed' => env('BUDGET_INCLUDE_COMMITTED', true),
];
