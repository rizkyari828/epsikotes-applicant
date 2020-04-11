<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnsChoicesModel extends Model
{
    protected $table = 'que_ans_choices';
    protected $primaryKey = 'ANS_CHOICE_ID';


    public function question()
    {
        return $this->belongsTo('App\QuestionModel','QUESTION_ID');
    }
}
