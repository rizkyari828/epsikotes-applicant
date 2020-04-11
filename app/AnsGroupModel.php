<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnsGroupModel extends Model
{
    protected $table = 'que_ans_group';
    protected $primaryKey = 'QUE_ANS_GROUP_ID';

    public function question()
    {
        return $this->belongsTo('App\QuestionModel','QUESTION_ID');
    }
}
