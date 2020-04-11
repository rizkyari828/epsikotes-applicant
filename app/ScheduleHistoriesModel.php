<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleHistoriesModel extends Model
{
    protected $table = 'psy_schedule_histories'; 
    protected $primaryKey = 'SCHEDULE_HISTORY_ID';
    protected $fillable = ['ACTUAL_START_DATE'];
    public $timestamps = false;

    public function Schedules()
    {
        return $this->belongsTo('App\SchedulesModel','SCHEDULE_ID');
    }

    public function jobMappings()
    {
        return $this->belongsTo('JobMappingsModel','JOB_MAPPING_ID');
    }
}
