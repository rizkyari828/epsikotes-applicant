<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NarrationsModel extends Model
{
    protected $table = 'que_narrations';
    protected $primaryKey = 'NARRATION_ID'; // Misal kita memakai nama id_kendaraan
    public $timestamps = false;

    public function question()
    {
        return $this->hasOne('App\QuestionModel');
    }

    public function jobMappingVersions()
    {
        return $this->hasMany('App\JobMappingVersions');
    }
}
