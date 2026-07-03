<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplateVersion extends Model
{
    protected $table = 'email_template_versions';

    protected $fillable = [
        'email_template_id',
        'version_number',
        'name',
        'slug',
        'subject',
        'html_body',
        'text_body',
        'variables',
        'category_id',
        'category',
        'is_active',
        'created_by',
        'note',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function emailTemplate()
    {
        return $this->belongsTo(EmailTemplate::class);
    }
}
