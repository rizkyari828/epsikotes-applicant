<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NormaModel extends Model
{
    protected $table = 'psy_norma';
    protected $primaryKey = 'NORMA_ID';

    public function normaVersions()
    {
        return $this->hasMany('App\NormaVersionsModel');
    }

    public function queCategories()
    {
        return $this->belongsTo('App\QueCategoriesModel','CATEGORY_ID');
    }
}
