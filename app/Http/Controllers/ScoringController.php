<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use App\ApplicantModel;
use App\AnsChoicesModel;
use App\AnsGroupModel;
use App\AnsTextSeriesModel;
use App\JobProfilesModel;
use App\JobProfileScoreModel;
use App\NarrationsModel;
use App\NormaModel;
use App\NormaScoreModel;
use App\NormaVersionsModel;
use App\TestCategoriesModel;
use App\TestQuestionsModel;
use App\TestChoicesModel;
use App\TestMemoriesModel;
use App\TestResultModel;
use App\TestScoreModel;
use App\ScheduleHistoriesModel;
use App\SchedulesModel;

class ScoringController extends Controller
{
    
    // SCORING
    public function getCategory($id){
        $data = Crypt::decrypt($id);
        $scheduleId = $data['scheduleId'];
        $testCategoryId = TestCategoriesModel::select('TEST_CATEGORY_ID')
            ->where('SCHEDULE_ID',$scheduleId)
            ->where('IS_TEST_CATEGORY_ACTIVE',1)
            ->where('CATEGORY_STATUS',['COMPLETE'])
            ->get()
            ->toArray();
        return $this->getQuestion($testCategoryId,$scheduleId);
        
    }

    public function getQuestion($TestCategoryId,$scheduleId){
        $listChoice = array();
        foreach ($TestCategoryId as $testCatId) {
            $queList = TestQuestionsModel::select('TEST_QUESTION_ID','QUESTION_ID','TYPE_SUB_CATEGORY','TYPE_ANSWER')
                ->where('TEST_CATEGORY_ID',$testCatId)
                ->where('EXAMPLE',0)
                ->get()
                ->toArray();
            array_push($listChoice, $queList);
        }
        return $this->getCorrectAnswer($listChoice,$scheduleId);
        
    }

    public function getCorrectAnswer($listChoice,$scheduleId){
        foreach ($listChoice as $key => $value) {
            foreach ($value as $a => $val) {
                $ans = '';
                if($val['TYPE_ANSWER'] == 'MULTIPLE_CHOICE'){
                    $anss = AnsChoicesModel::
                        where('QUESTION_ID',$val['QUESTION_ID'])
                        ->where('CORRECT_ANSWER',1)
                        ->pluck('ANS_CHOICE_ID')
                        ->toArray();
                    $ans = implode('', $anss);
                }
                else if($val['TYPE_ANSWER'] == 'TEXT_SERIES'){
                    $correct = AnsTextSeriesModel::where('QUESTION_ID',$val['QUESTION_ID'])
                        ->orderBy('ANS_SEQUENCE')
                        ->pluck('CORRECT_TEXT')
                        ->toArray();
                    $ans = implode("/", $correct);
                }
                else if($val['TYPE_ANSWER'] == 'MULTIPLE_GROUP'){
                    $get1 = AnsGroupModel::where('QUESTION_ID',$val['QUESTION_ID'])
                        ->where('GROUP_IMG',1)
                        ->pluck('IMG_SEQUENCE')
                        ->toArray();
                    $get2 = AnsGroupModel::where('QUESTION_ID',$val['QUESTION_ID'])
                        ->where('GROUP_IMG',2)
                        ->pluck('IMG_SEQUENCE')
                        ->toArray();
                    $ans1 = implode("", $get1);
                    $ans2 = implode("", $get2);
                    if(count($get1) == 3){
                        $anss = [$ans1, $ans2];
                        $ans = implode("/", $anss);
                    }else{
                        $anss = [$ans2, $ans1];
                        $ans = implode("/", $anss);
                    }
                }else{
                    $anss = TestMemoriesModel::where('TEST_QUESTION_ID',$val['TEST_QUESTION_ID'])
                        ->pluck('QUESTION_TEXT')
                        ->toArray();
                    $ans = implode('', $anss);
                }
                array_push($listChoice[$key][$a], $ans);
            }
        }
        return $this->ScoringRaw($listChoice,$scheduleId);
        
    }

    public function ScoringRaw($listChoice,$scheduleId){
        foreach ($listChoice as $key => $value) {
            foreach ($value as $a => $val) {
                $correct = 0;
                if ($listChoice[$key][$a]['TYPE_SUB_CATEGORY'] == 'CLASSIFICATION') {                
                    $getss = TestScoreModel::select('ORIGINAL_ANSWER')
                        ->where('TEST_QUESTION_ID',$listChoice[$key][$a]['TEST_QUESTION_ID'])
                        ->first();
                    if($getss != null){
                        $gets = strtoupper($getss->ORIGINAL_ANSWER);
                        if($gets != "/"){
                            $ans_applicant = explode("/", $gets);
                            $ans_applicant1 = str_split($ans_applicant[0]);
                            $ans_applicant2 = str_split($ans_applicant[1]);
                            $ans_correct = explode("/", $listChoice[$key][$a][0]);
                            $ans_correct1 = str_split($ans_correct[0]);
                            $ans_correct2 = str_split($ans_correct[1]);

                            $correct = 1;
                            foreach ($ans_applicant1 as $ans_a) {
                                if(!in_array($ans_a, $ans_correct1)){
                                    $correct = 0;
                                }
                            }
                            foreach ($ans_applicant2 as $ans_a2) {
                                if(!in_array($ans_a2, $ans_correct2)){
                                    $correct = 0;
                                }
                            }
                        }
                    }else{
                        $correct = 0;
                    }
                }else{
                    $get = TestScoreModel::select('ORIGINAL_ANSWER')
                        ->where('TEST_QUESTION_ID',$listChoice[$key][$a]['TEST_QUESTION_ID'])
                        ->where('ORIGINAL_ANSWER',$listChoice[$key][$a][0])
                        ->count();
                    if($get > 0){
                        $correct = 1;    
                    }
                }
                if($correct != 0){
                    $updateRaw = TestScoreModel::where('TEST_QUESTION_ID',$listChoice[$key][$a]['TEST_QUESTION_ID'])
                        ->update(['RAW_SCORE' => 1]);   
                }
            }
        }
        return $this->CalculateRawScore($listChoice,$scheduleId);
    }

    public function CalculateRawScore($listChoice,$scheduleId){
        $testCategoryId = TestCategoriesModel::select('TEST_CATEGORY_ID','CATEGORY_ID')
            ->where('SCHEDULE_ID',$scheduleId)
            ->where('IS_TEST_CATEGORY_ACTIVE',1)
            ->where('CATEGORY_STATUS',['COMPLETE'])
            ->get()
            ->toArray();

        $listChoice = array();
        foreach ($testCategoryId as $testCatId => $val) {
            $totRaw = 0;
            $queList = TestQuestionsModel::where('TEST_CATEGORY_ID',$val['TEST_CATEGORY_ID'])
                ->where('EXAMPLE',0)
                ->pluck('TEST_QUESTION_ID')
                ->toArray();
            if($val['CATEGORY_ID'] != 6){
                foreach ($queList as $queId) {
                    // COUNT TOTAL RAW SCORE PER CATEGORY
                    $total = TestScoreModel::select('RAW_SCORE')
                        ->where('TEST_QUESTION_ID',$queId)
                        ->where('RAW_SCORE',1)
                        ->first();
                    if($total){
                        $totRaw++;
                    }
                }
            }else{
                $getlastans = TestScoreModel::select('ORIGINAL_ANSWER')
                    ->whereIn('TEST_QUESTION_ID',$queList)
                    ->where('RAW_SCORE',1)
                    ->orderBy('TEST_SCORE_ID', 'desc')
                    ->first();
                if($getlastans != null){
                    $getStimulus = strlen($getlastans->ORIGINAL_ANSWER);

                    $getQuestionByStimulus = TestMemoriesModel::select('TEST_QUESTION_ID')
                            ->whereIn('TEST_QUESTION_ID',$queList)
                            ->whereRaw('CHAR_LENGTH(QUESTION_TEXT) = ?', $getStimulus)
                            ->get()
                            ->toArray();

                    $totRaw2 = 0;
                    foreach ($getQuestionByStimulus as $queId) {
                        $total = TestScoreModel::select('RAW_SCORE')
                            ->where('TEST_QUESTION_ID',$queId)
                            ->where('RAW_SCORE',1)
                            ->first();
                        if($total){
                            $totRaw2++;
                        }
                    }

                    if($getStimulus == 4){
                        if($totRaw2 == 1){
                            $totRaw = 4;
                        }
                        else if($totRaw2 == 2){
                            $totRaw = 5;
                        }
                        else if($totRaw2 == 3){
                            $totRaw = 6;
                        }
                    }
                    else if($getStimulus == 5){
                        if($totRaw2 == 1){
                            $totRaw = 7;
                        }
                        else if($totRaw2 == 2){
                            $totRaw = 8;
                        }
                        else if($totRaw2 == 3){
                            $totRaw = 9;
                        }
                    }
                    else if($getStimulus == 6){
                        if($totRaw2 == 1){
                            $totRaw = 10;
                        }
                        else if($totRaw2 == 2){
                            $totRaw = 11;
                        }
                        else if($totRaw2 == 3){
                            $totRaw = 12;
                        }
                    }
                    else if($getStimulus == 7){
                        if($totRaw2 == 1){
                            $totRaw = 13;
                        }
                        else if($totRaw2 == 2){
                            $totRaw = 14;
                        }
                        else if($totRaw2 == 3){
                            $totRaw = 15;
                        }
                    }
                    else if($getStimulus == 8){
                        if($totRaw2 == 1){
                            $totRaw = 16;
                        }
                        else if($totRaw2 == 2){
                            $totRaw = 17;
                        }
                        else if($totRaw2 == 3){
                            $totRaw = 18;
                        }
                    }
                    else if($getStimulus == 9){
                        if($totRaw2 == 1){
                            $totRaw = 19;
                        }
                        else if($totRaw2 == 2){
                            $totRaw = 20;
                        }
                        else if($totRaw2 == 3){
                            $totRaw = 21;
                        }
                    }
                    else if($getStimulus == 10){
                        if($totRaw2 == 1){
                            $totRaw = 22;
                        }
                        else if($totRaw2 == 2){
                            $totRaw = 23;
                        }
                        else if($totRaw2 == 3){
                            $totRaw = 24;
                        }
                    }
                }else{
                    $totRaw = 0;
                }
            }

            // GET STANDARD SCORE
            $standarScore2 = normaScoreModel::select('STANDARD_SCORE')
                ->join('psy_norma_versions', 'psy_norma_versions.VERSION_ID', '=', 'psy_norma_score.VERSION_ID')
                ->join('psy_norma', 'psy_norma.NORMA_ID', '=', 'psy_norma_versions.NORMA_ID')
                ->where('psy_norma.CATEGORY_ID', $val['CATEGORY_ID'])
                ->where('RAW_SCORE',$totRaw)
                ->first();
            $standarScore = $standarScore2['STANDARD_SCORE']." ";

            if($standarScore2){
                $updateTestCategories = TestCategoriesModel::where('TEST_CATEGORY_ID',$val['TEST_CATEGORY_ID'])
                    ->update(['SUM_RAWSCORE' => $totRaw, 'STANDARD_SCORE' => $standarScore]);   
            }else{
                $updateTestCategories = TestCategoriesModel::where('TEST_CATEGORY_ID',$val['TEST_CATEGORY_ID'])
                    ->update(['SUM_RAWSCORE' => $totRaw, 'STANDARD_SCORE' => 0]); 
            }
        }

        return $this->JobResult($testCategoryId,$scheduleId);
    }

    public function JobResult($testCategoryId,$scheduleId){
        $dateNow = date('Y-m-d');
        $JobMapingId = ScheduleHistoriesModel::select('JOB_MAPPING_VERSION_ID','SCHEDULE_HISTORY_ID')
            ->where('SCHEDULE_ID',$scheduleId)
            ->where('TEST_STATUS','=','COMPLETE')
            ->whereRaw('? between PLAN_START_DATE and PLAN_END_DATE', $dateNow)
            ->first();
        
        $JobMapping2 = $JobMapingId['JOB_MAPPING_VERSION_ID'];

        $jobProfileId = JobProfilesModel::select('JOB_PROFILE_ID','JOB_ID','TOTAL_PASS_SCORE')
            // ->join("psy_job_mapping_versions", 'psy_job_mapping_versions.VERSION_ID', '=', 'psy_job_profiles.VERSION_ID')
            // ->where('psy_job_mapping_versions.JOB_MAPPING_ID', $JobMapping2)
            ->where('VERSION_ID',$JobMapping2)
            ->get()
            ->toArray();

        $totalScore = 0;

        foreach ($jobProfileId as $key => $value) {
            $profileScore = JobProfileScoreModel::select('CATEGORY_ID','PASS_SCORE','MANDATORY')
                ->where('JOB_PROFILE_ID',$value['JOB_PROFILE_ID'])
                ->get()
                ->toArray();
            
            $mandatory = 0;
            $achieveMandatory = 0;
            $totalAchieve = 0;
            $totalCategory = count($profileScore);

            foreach ($profileScore as $key2 => $value2) {
                $getCategoryResult = TestCategoriesModel::select('SUM_RAWSCORE','STANDARD_SCORE')
                    ->where('SCHEDULE_ID', $scheduleId)
                    ->where('CATEGORY_ID', $value2['CATEGORY_ID'])
                    ->get()
                    ->toArray();

                //GET TOTAL SCORE BY STANDARD SCORE
                if($key < 1){
                    if(count($getCategoryResult) > 0){
                        $totalScore = $totalScore + $getCategoryResult[0]['STANDARD_SCORE'];
                    }
                }


                if(count($getCategoryResult) > 0){
                    //Hitung achieve per kategori
                    if($getCategoryResult[0]['STANDARD_SCORE'] >= $value2['PASS_SCORE']){
                        $totalAchieve++;
                        //hitung achieve mandatory
                        if($value2['MANDATORY'] == 1){
                            $mandatory++;
                            $achieveMandatory++;
                        }
                    }
                }
            }

            if($totalScore >= $jobProfileId[$key]['TOTAL_PASS_SCORE']){
                $IS_ACHIEVE_TOTAL_SCORE = 1;
            }
            else{
                $IS_ACHIEVE_TOTAL_SCORE = 0;
            }

            // cek has mandatory
            if($mandatory > 0)
                $hasMandatory = 1;
            else
                $hasMandatory = 0;

            //recomendation by system
            $recomendBySystem = '';
            if ($hasMandatory == 1 && $mandatory >= $achieveMandatory && $IS_ACHIEVE_TOTAL_SCORE == 1) {
                $recomendBySystem = 'ABOVE_REQUIREMENT';
            }elseif($hasMandatory == 0 && $IS_ACHIEVE_TOTAL_SCORE == 1){
                $recomendBySystem = 'ABOVE_REQUIREMENT';
            }elseif($hasMandatory == 1 && $achieveMandatory >= 1 && $IS_ACHIEVE_TOTAL_SCORE == 1){
                $recomendBySystem = 'MEET_REQUIREMENT';
            }else{
                $recomendBySystem = 'BELOW_REQUIREMENT';
            }

            // GET SCHEDULE HISTORY ID
            // $isScheduleHistories = ScheduleHistoriesModel::select('SCHEDULE_HISTORY_ID')
            //     ->where('SCHEDULE_ID',$scheduleId)
            //     ->whereRaw('? between PLAN_START_DATE and PLAN_END_DATE', $dateNow)
            //     ->first();
            $scheduleHistoryId = $JobMapingId['SCHEDULE_HISTORY_ID'];
            //INSERT DATA KE TABLE PSY_TEST_RESULT
            $insertTestCategory = TestResultModel::insert([
                'SCHEDULE_HISTORY_ID' => $scheduleHistoryId,
                'SCHEDULE_ID' => $scheduleId,
                'JOB_ID' => $jobProfileId[$key]['JOB_ID'],
                'ACHIEVE_TOTAL_SCORE' => $totalScore,
                'IS_ACHIEVE_TOTAL_SCORE' => $IS_ACHIEVE_TOTAL_SCORE,
                'HAS_MANDATORY' => $hasMandatory,
                'TOTAL_MANDATORY' => $mandatory,
                'TOTAL_ACHIEVE_MANDATORY' => $achieveMandatory,
                'RECOMENDATION_BY_SYSTEM' => $recomendBySystem 
            ]);
            // echo $mandatory.'<-mandatory '. $achieveMandatory.'<-achieveMandatory '.$totalAchieve.'<-totalAchieve '.$hasMandatory.'<-hasMandatory '.$IS_ACHIEVE_TOTAL_SCORE.'<-IS_ACHIEVE_TOTAL_SCORE '.$totalScore.'<-totalScore'.$jobProfileId[$key]['TOTAL_PASS_SCORE'].'<-PASS_SCORE';
            // echo "<br>";
            // print_r($profileScore);
            // echo "<br><br>";
        }
        $applicant = SchedulesModel::select('CANDIDATE_ID')
            ->where('SCHEDULE_ID',$scheduleId)
            ->first();
        $id_applicant = $applicant->CANDIDATE_ID;
        $dt_applicant = ApplicantModel::find($id_applicant);
        $finalGreeting = NarrationsModel::select('NARRATION_TEXT')
            ->where('NARRATION_NAME','FINAL GREATING')
            ->first();        
        session()->forget('login');
        return view('finalGreeting')
            ->with('dt_applicant', $dt_applicant)
            ->with('finalgret',$finalGreeting['NARRATION_TEXT']);
    }
}
