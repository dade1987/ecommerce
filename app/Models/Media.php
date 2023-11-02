<?php

namespace App\Models;

use App\Models\Traits\HasTeams;
use Awcodes\Curator\Models\Media as ModelsMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Media extends ModelsMedia
{
    use HasFactory;

    use HasTeams;
}
