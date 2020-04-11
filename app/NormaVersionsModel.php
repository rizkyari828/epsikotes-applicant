<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NormaVersionsModel extends Model
{
    protected $table = 'psy_norma_versions';
    protected $primaryKey = 'VERSION_ID';

    public function normaScore()
    {
        return $this->hasMany('App\NormaScoreModel');
    }

    public function normaAspect()
    {
        return $this->hasMany('App\NormaAspectModel');
    }

    public function norma()
    {
        return $this->belongsTo('App\NormaModel','NORMA_ID');
    }
}
