<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QueSubCategoriesModel extends Model
{
    protected $table = 'que_sub_categories';
    protected $primaryKey = 'SUB_CATEGORY_ID';

    public function queSubCategoryVersion()
    {
        return $this->hasMany('App\QueSubCategoryVersionsModel');
    }

    public function testCategories()
    {
        return $this->hasMany('App\TestCategoriesModel');
    }

    public function queSubCategoryList()
    {
        return $this->hasMany('App\QueSubCategoryListModel');
    }

}
