<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestAccessModel extends Model
{
    protected $table = 'psy_test_access';
    protected $primaryKey = 'TEST_ACCESS_ID';
    protected $fillable = ['SCHEDULE_HISTORY_ID','IP_ADDRESS','BROWSER','OS','LAST_LOGIN'];
    public $timestamps = false;
}
