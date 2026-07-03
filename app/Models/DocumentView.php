<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentView extends Model
{
    protected $table = 'document_views';

    protected $fillable = [
        'participant_id',
        'document_id',
        'supporting_id',
        'viewed_at',
        'ip_address',
        'user_agent',
    ];

    protected $dates = ['viewed_at'];
}
