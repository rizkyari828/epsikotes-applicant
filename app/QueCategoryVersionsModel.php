<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QueCategoryVersionsModel extends Model
{
    protected $table = 'que_category_versions';
    protected $primaryKey = 'VERSION_ID';

    public function subCategoryList(){
        return $this->hasMany('App\SubCategoryListModel');
    }

    public function categories()
    {
        return $this->belongsTo('App\QueCategoriesModel','CATEGORY_ID');
    }
}
