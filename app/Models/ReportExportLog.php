<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportExportLog extends Model
{
    protected $fillable = [
        'user_id',
        'report_type',
        'export_format',
        'filters',
        'record_count',
        'file_path',
        'file_size',
        'ip_address',
        'user_agent',
        'exported_at',
    ];

    protected $casts = [
        'filters' => 'json',
        'exported_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByReportType($query, string $reportType)
    {
        return $query->where('report_type', $reportType);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByFormat($query, string $format)
    {
        return $query->where('export_format', $format);
    }

    public function scopeRecentFirst($query)
    {
        return $query->orderByDesc('exported_at');
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('exported_at', [$startDate, $endDate]);
    }

    public function getFiltersSummary(): string
    {
        if (! $this->filters || empty($this->filters)) {
            return 'No filters applied';
        }

        $parts = [];
        foreach ($this->filters as $key => $value) {
            if (is_array($value)) {
                $parts[] = ucfirst($key).': '.implode(', ', $value);
            } else {
                $parts[] = ucfirst($key).': '.$value;
            }
        }

        return implode(' | ', $parts);
    }
}
