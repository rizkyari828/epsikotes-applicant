<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QueSubCategoryVersionsModel extends Model
{
    protected $table = 'que_sub_category_versions';
    protected $primaryKey = 'VERSION_ID';

    public function queQuestion()
    {
        return $this->hasMany('App\QuestionModel');
    }

    public function queSubCategories()
    {
        return $this->belongsTo('App\QueSubCategories','SUB_CATEGORY_ID');
    }
}
