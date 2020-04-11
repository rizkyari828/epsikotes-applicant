<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnsTextSeriesModel extends Model
{
    protected $table = 'que_ans_text_series';
    protected $primaryKey = 'ANS_TEXT_SERIES_ID';

    public function question()
    {
        return $this->belongsTo('App\QuestionModel','QUESTION_ID');
    }
}
