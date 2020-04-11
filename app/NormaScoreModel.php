<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NormaScoreModel extends Model
{
    protected $table = 'psy_norma_score';
    protected $primaryKey = 'NORMA_SCORE_ID';

    public function normaVersions()
    {
        return $this->belongsTo('App\NormaVersionsModel','VERSION_ID');
    }
}
