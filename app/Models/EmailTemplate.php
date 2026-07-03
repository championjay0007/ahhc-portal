<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
            $template->variables = self::extractVariables($template->subject, $template->html_body, $template->text_body);

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

            $template->variables = self::extractVariables($template->subject, $template->html_body, $template->text_body);
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
            'subject' => $this->replaceVariables($this->subject, $variables),
            'html' => $this->replaceVariables($this->html_body, $variables),
            'text' => $this->replaceVariables($this->text_body ?? '', $variables),
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

    protected function replaceVariables(string $content, array $variables): string
    {
        return preg_replace_callback('/\{\{\s*(\w+)\s*\}\}/', function ($matches) use ($variables) {
            $key = $matches[1] ?? null;

            if ($key === null) {
                return $matches[0];
            }

            return $variables[$key] ?? $variables[strtolower($key)] ?? $variables[strtoupper($key)] ?? '';
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
        $defaults = [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'unsubscribe_url' => url('/unsubscribe'),
            'organization' => config('app.name', 'Website Name'),
            'date' => now()->toFormattedDateString(),
            'company' => 'Acme Corp',
            'reset_link' => url('/password/reset'),
        ];

        $samples = [];

        foreach ($this->variables ?? [] as $variable) {
            $samples[$variable] = $defaults[$variable] ?? 'Sample '.Str::title(str_replace('_', ' ', $variable));
        }

        return $samples;
    }
}
