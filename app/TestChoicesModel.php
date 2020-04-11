<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestChoicesModel extends Model
{
    protected $table = 'psy_test_choices';
    protected $primaryKey = 'TEST_CHOICE_ID';
    protected $fillable = ['TEST_QUESTION_ID','ANS_CHOICE_SEQ','ANS_CHOICE_ID','CREATION_DATE'];

    public function testQuestion()
    {
        return $this->belongsTo('App\TestQuestionModel','TEST_QUESTION_ID');
    }
}
