<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestMemoriesModel extends Model
{
    protected $table = 'psy_test_memories';
    protected $primaryKey = 'TEST_MEMORY_ID';
    protected $fillable = ['TEST_QUESTION_ID','QUESTION_TEXT','CREATED_DATE'];
    public $timestamps = false;

    public function testQuestion()
    {
        return $this->belongsTo('App\TestQuestionModel','TEST_QUESTION_ID');
    }
    
}
