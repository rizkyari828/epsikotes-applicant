<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryListModel extends Model
{
    protected $table = 'psy_job_category_list'; 
    protected $primaryKey = 'CATEGORY_LIST_ID';

    public function subCategories()
    {
        return $this->belongsTo('App\QueSubCategoriesModel','SUB_CATEGORY_ID');
    }

    public function queCategories()
    {
        return $this->belongsTo('App\QueCategoriesModel','CATEGORY_ID');
    }

    public function jobMappingVersion()
    {
        return $this->belongsTo('App\JobMappingVersionsModel','VERSION_ID');
    }
}
