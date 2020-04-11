<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\ApplicantModel;
use App\SchedulesModel;
use App\ScheduleHistoriesModel;
use App\JobMappingVersionsModel;
use App\NarrationsModel;
use App\TestAccessModel;
use \GuzzleHttp\Client;


class LoginController extends Controller
{
    
    public function checkSchedule($applicantId){
   		$isOnSchedule = false;
        $dateTimeNow = Carbon::now();
   		$dateNow = date('Y-m-d');
        $pesan = '';

    

        $applicantIdDecrypt =  (int) base64_decode($applicantId);

   


        $candidateIdList = ApplicantModel::select('CANDIDATE_ID')
            ->where('APPLICANT_ID',$applicantIdDecrypt)
            ->first();


        $candidateId =  isset($candidateIdList) ?  $candidateIdList->CANDIDATE_ID : 0  ;



        $isSchedule = SchedulesModel::join("psy_schedule_histories","psy_schedule_histories.SCHEDULE_ID","=","psy_schedules.SCHEDULE_ID")
            ->where('psy_schedules.CANDIDATE_ID',$candidateId)
            ->whereIn('psy_schedule_histories.TEST_STATUS',['NOT_ATTEMPT','INCOMPLETE'])
            ->whereRaw('? between psy_schedule_histories.PLAN_START_DATE and psy_schedule_histories.PLAN_END_DATE',$dateNow)
	     ->select('psy_schedules.SCHEDULE_ID')
            ->first();
    	
    	if($isSchedule){
    		// cek jadwal psychotest 
    		$isScheduleHistories = ScheduleHistoriesModel::select('SCHEDULE_HISTORY_ID','JOB_MAPPING_ID')
                ->where('SCHEDULE_ID',$isSchedule->SCHEDULE_ID)
	            ->whereIn('TEST_STATUS',['NOT_ATTEMPT','INCOMPLETE'])
	            ->whereRaw('? between PLAN_START_DATE and PLAN_END_DATE', $dateNow)
	    		->first();

            $ip = $this->get_client_ip();
            $browser = $this->get_client_browser();
            $os = $_SERVER['HTTP_USER_AGENT'];

	    	if($isScheduleHistories){
                $scheduleHistoryId = $isScheduleHistories->SCHEDULE_HISTORY_ID;

                $isAccessHistories = TestAccessModel::select('TEST_ACCESS_ID','IP_ADDRESS','BROWSER','OS')
                    ->where('SCHEDULE_HISTORY_ID',$scheduleHistoryId)
                    ->first();
                if($isAccessHistories){
                    $accessId = $isAccessHistories->TEST_ACCESS_ID;
                    $ipExist = $isAccessHistories->IP_ADDRESS;
                    $browserExist = $isAccessHistories->BROWSER;
                    $osExist = $isAccessHistories->OS;

                    if($browser == $browserExist && $os == $osExist){
                        $isOnSchedule = true;
                        $updateLastLogin = TestAccessModel::where('TEST_ACCESS_ID',$accessId)
                            ->update(['LAST_LOGIN' => $dateTimeNow]);
                    }else{
                        $pesan = "Maaf anda harus menggunakan browser dan perangkat yang sama untuk mengakses halaman ini";
                        $isOnSchedule = false;
                    }
                }else{
                    $insertAccess = TestAccessModel::insert([
                        'SCHEDULE_HISTORY_ID' => $scheduleHistoryId,
                        'IP_ADDRESS' => $ip,
                        'BROWSER' => $browser,
                        'OS' => $os  
                    ]);
                    $isOnSchedule = true;
                }
            }else{
                $pesan = "Maaf Anda Tidak Memenuhi Syarat untuk Mengakses Halaman Ini";
                $isOnSchedule = false;
            }
	    }

    	if($isOnSchedule){
            // DIRECT TO VIEW
    		$dt_applicant = ApplicantModel::find($candidateId);
            $jobMappingVersion = JobMappingVersionsModel::select('GENERAL_INSTRUCTION')
                ->where('JOB_MAPPING_ID',($isScheduleHistories->JOB_MAPPING_ID))
                ->first();
            $narrationId = $jobMappingVersion->GENERAL_INSTRUCTION;
            $generalInstruction = NarrationsModel::find($narrationId);
    		return view('introduction')
                    ->with('dt_applicant',$dt_applicant)
                    ->with('generalInstruction',$generalInstruction)
                    ->with('scheduleHistoryId', $scheduleHistoryId);
    	}else{
    		return view('errorPage')
                ->with('pesan',$pesan);
    	}
    } 

    // Mendapatkan IP pengunjung menggunakan $_SERVER
    function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'IP tidak dikenali';
        return $ipaddress;
    }
     
    // Mendapatkan jenis web browser pengunjung
    function get_client_browser() {
        $browser = '';
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'Netscape'))
            $browser = 'Netscape';
        else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox'))
            $browser = 'Firefox';
        else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome'))
            $browser = 'Chrome';
        else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera'))
            $browser = 'Opera';
        else if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
            $browser = 'Internet Explorer';
        else
            $browser = 'Other';
        return $browser;
    }
}
