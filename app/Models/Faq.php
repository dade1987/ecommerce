<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'active',
        'team_id',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
