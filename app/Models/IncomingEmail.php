<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'subject',
        'from_address',
        'to_address',
        'body_html',
        'body_text',
        'analysis',
        'priority',
        'is_read',
        'received_at',
    ];
}
