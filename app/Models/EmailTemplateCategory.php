<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailTemplateCategory extends Model
{
    protected $table = 'email_template_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function (self $category) {
            $category->slug = $category->slug ?: self::makeUniqueSlug($category->name);

            if (! isset($category->is_active)) {
                $category->is_active = true;
            }
        });
    }

    public static function makeUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
    }

    public static function findOrCreateByName(?string $name): ?self
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        return self::firstOrCreate([
            'name' => $name,
        ], [
            'slug' => self::makeUniqueSlug($name),
            'is_active' => true,
        ]);
    }

    public function templates()
    {
        return $this->hasMany(EmailTemplate::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
