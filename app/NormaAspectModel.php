<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NormaAspectModel extends Model
{
    protected $table = 'psy_norma_aspect';
    protected $primaryKey = 'PSY_ASPECT_ID';

    public function normaVersions()
    {
        return $this->belongsTo('App\NormaVersionsModel','VERSION_ID');
    }
}
