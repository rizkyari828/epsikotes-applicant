<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PsiCronn extends Model
{

    protected $table = "tbl_cronn";
    protected $fillable = [
            'schedule_id',
            'status',
            'job_id',    
            'recommendation',
            'induktif',
            'deduktif',    
            'pemahaman_bacaan',
            'arimatika',
            'spasial',    
            'memori',
            'achieve_total_score',    
            'sim_id',
            'tanggal_test',
            'id_cabang',   
    ];

    // protected $primaryKey = "id";

    // public $timestamps = false;

    // public function question()
    // {
    //     return $this->belongsTo(PsiQuestion::class, 'QUESTION_ID', 'QUESTION_ID');
    // }

}