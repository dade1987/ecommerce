<?php

namespace App\Models;

use App\Models\Traits\HasTeams;
use Awcodes\Curator\Models\Media as ModelsMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends ModelsMedia
{
    use HasFactory;

    //use HasTeams;
}
