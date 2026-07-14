<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Services\TemplateVariableService;

class EmailTemplate extends Model
{
    protected $table = 'email_templates';

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'html_body',
        'text_body',
        'variables',
        'category',
        'category_id',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function (self $template) {
            $template->slug = $template->slug ?: self::makeUniqueSlug($template->name);
            $template->variables = self::resolveVariables($template->getAttribute('variables'), $template->subject, $template->html_body, $template->text_body);

            if (! isset($template->is_active)) {
                $template->is_active = true;
            }
        });

        static::created(function (self $template) {
            $template->createVersionSnapshot();
        });

        static::updating(function (self $template) {
            if ($template->isDirty(['name', 'subject', 'html_body', 'text_body', 'category', 'category_id', 'is_active'])) {
                $template->createVersionFromOriginal($template->getOriginal());
            }

            if (! $template->slug || $template->isDirty('name')) {
                $template->slug = self::makeUniqueSlug($template->name, $template->id);
            }

            $template->variables = self::resolveVariables($template->getAttribute('variables'), $template->subject, $template->html_body, $template->text_body);
        });
    }

    public static function extractVariablesFromText(string $text): array
    {
        preg_match_all('/\{\{\s*(\w+)\s*\}\}/', $text, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    public static function extractVariables(string $subject, string $htmlBody, ?string $textBody = null): array
    {
        $variables = [];

        $variables = array_merge($variables, self::extractVariablesFromText($subject));
        $variables = array_merge($variables, self::extractVariablesFromText($htmlBody));

        if ($textBody !== null) {
            $variables = array_merge($variables, self::extractVariablesFromText($textBody));
        }

        return array_values(array_unique($variables));
    }

    public static function resolveVariables($providedVariables, string $subject, string $htmlBody, ?string $textBody = null): array
    {
        $explicitVariables = [];

        if (is_string($providedVariables)) {
            $explicitVariables = preg_split('/\r\n|\n|\r/', $providedVariables);
        } elseif (is_array($providedVariables)) {
            $explicitVariables = $providedVariables;
        }

        $explicitVariables = collect($explicitVariables)
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '')
            ->map(fn ($item) => preg_replace('/[^A-Za-z0-9_]/', '', $item))
            ->filter(fn ($item) => $item !== '')
            ->values()
            ->all();

        $detectedVariables = self::extractVariables($subject, $htmlBody, $textBody);

        return array_values(array_unique(array_merge($detectedVariables, $explicitVariables)));
    }

    public static function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (self::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
    }

    public function render(array $variables = []): array
    {
        return [
            'subject' => $this->replaceVariables($this->subject, $variables, 'subject'),
            'html' => $this->replaceVariables($this->html_body, $variables, 'html'),
            'text' => $this->replaceVariables($this->text_body ?? '', $variables, 'text'),
        ];
    }

    public function categoryRelation()
    {
        return $this->belongsTo(EmailTemplateCategory::class, 'category_id');
    }

    public function versions()
    {
        return $this->hasMany(EmailTemplateVersion::class)->orderByDesc('version_number');
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->categoryRelation->name ?? $this->category ?? 'General';
    }

    protected function replaceVariables(string $content, array $variables, string $context = 'html'): string
    {
        return preg_replace_callback('/\{\{\s*(\w+)\s*\}\}/', function ($matches) use ($variables, $context) {
            $key = $matches[1] ?? null;

            if ($key === null) {
                return $matches[0];
            }

            $lookup = $variables[$key] ?? $variables[strtolower($key)] ?? $variables[strtoupper($key)] ?? null;

            if ($lookup !== null && $lookup !== '') {
                if ($key === 'logo' && $context === 'html' && filter_var($lookup, FILTER_VALIDATE_URL)) {
                    return '<img src="'.e($lookup).'" alt="'.e(config('app.name', 'Logo')).'" style="max-height:90px;width:auto;display:block;" />';
                }

                return $lookup;
            }

            $default = TemplateVariableService::sampleValuesFor([$key]);
            $fallback = $default[$key] ?? 'Sample '.Str::title(str_replace('_', ' ', $key));

            if ($key === 'logo') {
                if ($context === 'text') {
                    return filter_var($fallback, FILTER_VALIDATE_URL) ? $fallback : strip_tags($fallback);
                }

                if (filter_var($fallback, FILTER_VALIDATE_URL)) {
                    return '<img src="'.e($fallback).'" alt="'.e(config('app.name', 'Logo')).'" style="max-height:90px;width:auto;display:block;" />';
                }
            }

            return (string) $fallback;
        }, $content);
    }

    public function createVersionSnapshot(?string $note = null): EmailTemplateVersion
    {
        return $this->versions()->create([
            'version_number' => ($this->versions()->max('version_number') ?? 0) + 1,
            'name' => $this->name,
            'slug' => $this->slug,
            'subject' => $this->subject,
            'html_body' => $this->html_body,
            'text_body' => $this->text_body,
            'variables' => $this->variables,
            'category_id' => $this->category_id,
            'category' => $this->category,
            'is_active' => $this->is_active,
            'created_by' => auth()->id(),
            'note' => $note,
        ]);
    }

    public function createVersionFromOriginal(array $original, ?string $note = null): EmailTemplateVersion
    {
        $variables = $original['variables'] ?? [];

        if (is_string($variables)) {
            $variables = json_decode($variables, true) ?: [];
        }

        return $this->versions()->create([
            'version_number' => ($this->versions()->max('version_number') ?? 0) + 1,
            'name' => $original['name'],
            'slug' => $original['slug'],
            'subject' => $original['subject'],
            'html_body' => $original['html_body'],
            'text_body' => $original['text_body'],
            'variables' => $variables,
            'category_id' => $original['category_id'] ?? null,
            'category' => $original['category'] ?? null,
            'is_active' => $original['is_active'] ?? false,
            'created_by' => auth()->id(),
            'note' => $note,
        ]);
    }

    public function sampleVariables(): array
    {
        $vars = $this->variables ?? [];

        if (empty($vars)) {
            // fallback: return a small set of common samples
            return TemplateVariableService::sampleValuesFor(['name', 'email', 'organization', 'date']);
        }

        return TemplateVariableService::sampleValuesFor($vars);
    }
}
