<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QueCategoriesModel extends Model
{
    protected $table = 'que_categories';
    protected $primaryKey = 'CATEGORY_ID';

    public function queCategoryVersions(){
        return $this->hasMany('App\QueCategoryVersionsModel');
    }
    
    public function jobProfileScore(){
        return $this->hasMany('App\JobProfileScoreModel');
    }

    public function jobCategoryList(){
        return $this->hasMany('App\CategoryListModel');
    }

    public function testCategory(){
        return $this->hasMany('App\TestCategoryModel');
    }
    
    public function norma(){
        return $this->hasMany('App\normaModel');
    }
}
