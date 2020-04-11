<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Session;


use App\ApplicantModel;
use App\AnsChoicesModel;
use App\AnsGroupModel;
use App\AnsTextSeriesModel;
use App\CategoryListModel;
use App\JobMappingVersionsModel;
use App\JobProfilesModel;
use App\JobProfileScoreModel;
use App\NormaModel;
use App\NormaScoreModel;
use App\NormaVersionsModel;
use App\NarrationsModel;
use App\QueCategoryVersionsModel;
use App\QueSubCategoryListModel;
use App\QueSubCategoriesModel;
use App\QueSubCategoryVersionsModel;
use App\QuestionModel;
use App\SchedulesModel;
use App\ScheduleHistoriesModel;
use App\TestCategoriesModel;
use App\TestQuestionsModel;
use App\TestChoicesModel;
use App\TestMemoriesModel;
use App\TestResultModel;
use App\TestScoreModel;

class StartController extends Controller
{
    public function startEpsikotest($id)
    {
        $scheduleHistoryId = Crypt::decrypt($id);
        $dateTimeNow = Carbon::now();
        $dateNow = date('Y-m-d');

        //GET DATA SCHEDULE HISTORY
        $dtScheduleHistory = ScheduleHistoriesModel::select('SCHEDULE_ID', 'JOB_MAPPING_ID', 'RESCHEDULE_SEQ')
            ->where('SCHEDULE_HISTORY_ID', $scheduleHistoryId)
            ->first();
        $scheduleId = $dtScheduleHistory->SCHEDULE_ID;
        $jobMappingIdNow = $dtScheduleHistory->JOB_MAPPING_ID;
        $scheduleSequence = $dtScheduleHistory->RESCHEDULE_SEQ;

        // UPDATE ACTUAL START DATE
        $updateActualDate = ScheduleHistoriesModel::where('SCHEDULE_HISTORY_ID', $scheduleHistoryId)
            ->update(['ACTUAL_START_DATE' => $dateTimeNow]);

        // GET JOB MAPPING VERSION ID
        $dtJobMappingVersion = JobMappingVersionsModel::select('VERSION_ID', 'RANDOM_CATEGORY')
            ->where('JOB_MAPPING_ID', $jobMappingIdNow)
            ->whereRaw('? between DATE_FROM and DATE_TO', $dateNow)
            ->first();
        $jobMappingVersionId = $dtJobMappingVersion->VERSION_ID;
        $cekRandomCategory = $dtJobMappingVersion->RANDOM_CATEGORY;

        //CEK RANDOM SOAL DALAM KATEGORI
        if ($cekRandomCategory == 1) {
            $CategoryList = CategoryListModel::where('VERSION_ID', $jobMappingVersionId)
                ->inRandomOrder()->pluck('CATEGORY_ID')->toArray();
        } else {
            $CategoryList = CategoryListModel::where('VERSION_ID', $jobMappingVersionId)
                ->pluck('CATEGORY_ID')->toArray();
        }

        //GET KATEGORI YG SUDAH ADA
        $categoryExist = TestCategoriesModel::where('SCHEDULE_ID', $scheduleId)
            ->pluck('CATEGORY_ID')->toArray();

        //INSERT KATEGORY KE TABLE psy_test_categories
        $seq = 0;
        foreach ($CategoryList as $categoryLst) {

            //GET VERSION ID YG BERELASI KE SUB CATEGORY
            $dtCategoryVersion = QueCategoryVersionsModel::select('VERSION_ID', 'RANDOM_SUB_CATEGORY', 'GET_ONE_SUB_CATEGORY')
                ->where('CATEGORY_ID', $categoryLst)
                ->whereRaw('? between DATE_FROM and DATE_TO', $dateNow)
                ->first();

            $checkRandomSubCategory = $dtCategoryVersion->RANDOM_SUB_CATEGORY;
            $checkAllSubCategory = $dtCategoryVersion->GET_ONE_SUB_CATEGORY;
            $versionIdCategory = $dtCategoryVersion->VERSION_ID;

            // // GET DATA SUB CATEGORY
            if ($checkAllSubCategory == 1) {
                if ($checkRandomSubCategory == 1) {
                    $SubCategoryList = QueSubCategoryListModel::select('SUB_CATEGORY_ID')
                        ->where('VERSION_ID', $versionIdCategory)
                        ->inRandomOrder()->first()->toArray();
                } else {
                    $SubCategoryList = QueSubCategoryListModel::where('VERSION_ID', $versionIdCategory)->first()->toArray();
                }
            } else {
                if ($checkRandomSubCategory == 1) {
                    $SubCategoryList = QueSubCategoryListModel::where('VERSION_ID', $versionIdCategory)
                        ->inRandomOrder()->pluck('SUB_CATEGORY_ID');
                } else {
                    $SubCategoryList = QueSubCategoryListModel::where('VERSION_ID', $versionIdCategory)->pluck('SUB_CATEGORY_ID');
                }
            }

            // //INSERT SUB KATEGORY KE TABLE psy_test_categories
            foreach ($SubCategoryList as $subCategoryLst) {
                // JIKA RESCHEDULE
                if ($scheduleSequence != 0) {

                    $cekarray = 0;
                    if (in_array($categoryLst, $categoryExist)) {
                        $cekarray = 1;
                    }

                    if ($cekarray == 0) {
                        $seq++;
                        $insertTestCategory = TestCategoriesModel::insert([
                            'SCHEDULE_ID' => $scheduleId,
                            'CATEGORY_SEQ' => $seq,
                            'CATEGORY_ID' => $categoryLst,
                            'SUB_CATEGORY_ID' => $subCategoryLst,
                            'IS_TEST_CATEGORY_ACTIVE' => '1',
                            'CREATION_DATE' => $dateTimeNow,
                            'LAST_UPDATE_DATE' => $dateTimeNow
                        ]);

                        // GET TEST CATEGORY ID
                        $testCategoryId = TestCategoriesModel::select('TEST_CATEGORY_ID')
                            ->where('SCHEDULE_ID', $scheduleId)
                            ->orderBy('TEST_CATEGORY_ID', 'desc')
                            ->first();
                        $testcatId = $testCategoryId->TEST_CATEGORY_ID;

                        //PANGGIL FUNGSI INSERT PSY TEST QUESTION
                        $this->insertTestQuestion($testcatId, $categoryLst, $subCategoryLst);
                    }
                } else {
                    //JIKA ORIGINAL SCHEDULE

                    $cekarray = 0;
                    if (in_array($categoryLst, $categoryExist)) {
                        $cekarray = 1;
                    }

                    if ($cekarray == 0) {
                        $seq++;
                        $insertTestCategory = TestCategoriesModel::insert([
                            'SCHEDULE_ID' => $scheduleId,
                            'CATEGORY_SEQ' => $seq,
                            'CATEGORY_ID' => $categoryLst,
                            'SUB_CATEGORY_ID' => $subCategoryLst,
                            'IS_TEST_CATEGORY_ACTIVE' => '1',
                            'CREATION_DATE' => $dateTimeNow,
                            'LAST_UPDATE_DATE' => $dateTimeNow
                        ]);

                        // GET TEST CATEGORY ID
                        $testCategoryId = TestCategoriesModel::select('TEST_CATEGORY_ID')
                            ->where('SCHEDULE_ID', $scheduleId)
                            ->orderBy('TEST_CATEGORY_ID', 'desc')
                            ->first();
                        $testcatId = $testCategoryId->TEST_CATEGORY_ID;

                        //PANGGIL FUNGSI INSERT PSY TEST QUESTION
                        $this->insertTestQuestion($testcatId, $categoryLst, $subCategoryLst);
                    }
                }
            }
        }

        //UBAH SEQUENCE KATEGORI JIKA RESCHEDULE
        if ($scheduleSequence != 0) {

            //INACTIVE CATEGORY YG TIDAK DIPAKAI
            foreach ($categoryExist as $catEx) {
                if (!in_array($catEx, $CategoryList)) {
                    $updateStatusCategory = TestCategoriesModel::where('SCHEDULE_ID', $scheduleId)
                        ->where('CATEGORY_ID', $catEx)
                        ->update(['CATEGORY_SEQ' => 0, 'IS_TEST_CATEGORY_ACTIVE' => 0]);
                }
            }

            $categorySeq = TestCategoriesModel::where('SCHEDULE_ID', $scheduleId)
                ->where('IS_TEST_CATEGORY_ACTIVE', 1)
                ->pluck('CATEGORY_ID')->toArray();

            $seq = 0;
            foreach ($categorySeq as $catSeq) {
                $seq++;
                $updateStatusCategory = TestCategoriesModel::where('SCHEDULE_ID', $scheduleId)
                    ->where('CATEGORY_ID', $catSeq)
                    ->update(['CATEGORY_SEQ' => $seq]);
            }
        }

        //GET DATA SCHEDULE HISTORY
        $JobMappingVersions = JobMappingVersionsModel::select('VERSION_ID')
            ->where('JOB_MAPPING_ID', $jobMappingIdNow)
            ->whereRaw('? between DATE_FROM and DATE_TO', $dateNow)
            ->first();
        $jobMappingVersionId = $JobMappingVersions->VERSION_ID;

        // UPDATE SCHEDULE HISTORY 
        $updateStatusSchedule = ScheduleHistoriesModel::where('SCHEDULE_ID', $scheduleId)
            ->whereRaw('? between PLAN_START_DATE and PLAN_END_DATE', $dateNow)
            ->update(['TEST_STATUS' => 'INCOMPLETE', 'JOB_MAPPING_VERSION_ID' => $jobMappingVersionId]);

        return $this->getSubTest($scheduleId, 0);
    }

    public function insertTestQuestion($testCategoryId, $catId, $subCategoryId)
    {
        $dateNow = date('Y-m-d');
        $dateTimeNow = Carbon::now();

        //GET VERSION ID YG BERELASI KE LIST QUESTION
        $dtCategoryVersion = QueSubCategoryVersionsModel::select('VERSION_ID', 'RANDOM_QUESTION')
            ->where('SUB_CATEGORY_ID', $subCategoryId)
            ->whereRaw('? between DATE_FROM and DATE_TO', $dateNow)
            ->first();
        $VersionId = $dtCategoryVersion->VERSION_ID;
        $randomQuestion = $dtCategoryVersion->RANDOM_QUESTION;

        if ($catId == 1) {
            if ($randomQuestion == 1 OR $randomQuestion == true) {
                // ANALOGY
                $dtQuestionExample = QuestionModel::select('QUESTION_ID', 'TYPE_SUB_CATEGORY', 'EXAMPLE', 'TYPE_ANSWER', 'RANDOM_ANSWER', 'QUESTION_CHARACTER')
                    ->where('VERSION_ID', $VersionId)
                    ->where('EXAMPLE', 1)
                    ->where('TYPE_SUB_CATEGORY', 'ANALOGY')
                    ->get();
                $dtQuestion = QuestionModel::select('QUESTION_ID', 'TYPE_SUB_CATEGORY', 'EXAMPLE', 'TYPE_ANSWER', 'RANDOM_ANSWER', 'QUESTION_CHARACTER')
                    ->where('VERSION_ID', $VersionId)
                    ->where('EXAMPLE', 0)
                    ->where('TYPE_SUB_CATEGORY', 'ANALOGY')
                    ->inRandomOrder()
                    ->get()
                    ->toArray();
                // INSERT DATE KE PSY_TEST_QUESTION
                $seq = 0;
                foreach ($dtQuestionExample as $questionList2) {
                    $seq++;
                    $insertTestCategory = TestQuestionsModel::insert([
                        'TEST_CATEGORY_ID' => $testCategoryId,
                        'QUESTION_SEQ' => $seq,
                        'QUESTION_ID' => $questionList2->QUESTION_ID,
                        'EXAMPLE' => $questionList2->EXAMPLE,
                        'TYPE_SUB_CATEGORY' => $questionList2->TYPE_SUB_CATEGORY,
                        'TYPE_ANSWER' => $questionList2->TYPE_ANSWER,
                        'CREATED_DATE' => $dateTimeNow
                    ]);

                    if ($insertTestCategory) {
                        // GET TEST QUESTION ID
                        $testQuestionId = TestQuestionsModel::select('TEST_QUESTION_ID')
                            ->where('TEST_CATEGORY_ID', $testCategoryId)
                            ->orderBy('TEST_QUESTION_ID', 'desc')
                            ->first();
                        $testQueId = $testQuestionId->TEST_QUESTION_ID;

                        $randomChoices = $questionList2->RANDOM_ANSWER;
                        $this->insertTestChoices($testQueId, $questionList2->QUESTION_ID, $randomChoices);

                        //INSERT TEST MEMORIES
                        // QUESTION CHARACTER UNTUK JML KARAKTER YG DI RANDOM DI CATEGORY MEMORIES
                        $queCharacter = $questionList2->QUESTION_CHARACTER;
                        if ($catId == 6) {
                            $this->insertTestMemories($testQueId, $queCharacter);
                        }
                    }
                }
                foreach ($dtQuestion as $questionList) {
                    $seq++;
                    $insertTestCategory = TestQuestionsModel::insert([
                        'TEST_CATEGORY_ID' => $testCategoryId,
                        'QUESTION_SEQ' => $seq,
                        'QUESTION_ID' => $questionList['QUESTION_ID'],
                        'EXAMPLE' => $questionList['EXAMPLE'],
                        'TYPE_SUB_CATEGORY' => $questionList['TYPE_SUB_CATEGORY'],
                        'TYPE_ANSWER' => $questionList['TYPE_ANSWER'],
                        'CREATED_DATE' => $dateTimeNow
                    ]);

                    if ($insertTestCategory) {
                        // GET TEST QUESTION ID
                        $testQuestionId = TestQuestionsModel::select('TEST_QUESTION_ID')
                            ->where('TEST_CATEGORY_ID', $testCategoryId)
                            ->orderBy('TEST_QUESTION_ID', 'desc')
                            ->first();
                        $testQueId = $testQuestionId->TEST_QUESTION_ID;

                        $randomChoices = $questionList['RANDOM_ANSWER'];
                        $this->insertTestChoices($testQueId, $questionList['QUESTION_ID'], $randomChoices);
                    }
                }
                // CLASSIFICATION
                $dtQuestionExample2 = QuestionModel::select('QUESTION_ID', 'TYPE_SUB_CATEGORY', 'EXAMPLE', 'TYPE_ANSWER', 'RANDOM_ANSWER', 'QUESTION_CHARACTER')
                    ->where('VERSION_ID', $VersionId)
                    ->where('EXAMPLE', 1)
                    ->where('TYPE_SUB_CATEGORY', 'CLASSIFICATION')
                    ->get();
                $dtQuestion2 = QuestionModel::select('QUESTION_ID', 'TYPE_SUB_CATEGORY', 'EXAMPLE', 'TYPE_ANSWER', 'RANDOM_ANSWER', 'QUESTION_CHARACTER')
                    ->where('VERSION_ID', $VersionId)
                    ->where('EXAMPLE', 0)
                    ->where('TYPE_SUB_CATEGORY', 'CLASSIFICATION')
                    ->inRandomOrder()
                    ->get()
                    ->toArray();
                foreach ($dtQuestionExample2 as $questionList2) {
                    $seq++;
                    $insertTestCategory = TestQuestionsModel::insert([
                        'TEST_CATEGORY_ID' => $testCategoryId,
                        'QUESTION_SEQ' => $seq,
                        'QUESTION_ID' => $questionList2->QUESTION_ID,
                        'EXAMPLE' => $questionList2->EXAMPLE,
                        'TYPE_SUB_CATEGORY' => $questionList2->TYPE_SUB_CATEGORY,
                        'TYPE_ANSWER' => $questionList2->TYPE_ANSWER,
                        'CREATED_DATE' => $dateTimeNow
                    ]);

                    if ($insertTestCategory) {
                        // GET TEST QUESTION ID
                        $testQuestionId = TestQuestionsModel::select('TEST_QUESTION_ID')
                            ->where('TEST_CATEGORY_ID', $testCategoryId)
                            ->orderBy('TEST_QUESTION_ID', 'desc')
                            ->first();
                        $testQueId = $testQuestionId->TEST_QUESTION_ID;

                        $randomChoices = $questionList2->RANDOM_ANSWER;
                        $this->insertTestChoices($testQueId, $questionList2->QUESTION_ID, $randomChoices);

                        //INSERT TEST MEMORIES
                        // QUESTION CHARACTER UNTUK JML KARAKTER YG DI RANDOM DI CATEGORY MEMORIES
                        $queCharacter = $questionList2->QUESTION_CHARACTER;

                    }
                }
                foreach ($dtQuestion2 as $questionList) {
                    $seq++;
                    $insertTestCategory = TestQuestionsModel::insert([
                        'TEST_CATEGORY_ID' => $testCategoryId,
                        'QUESTION_SEQ' => $seq,
                        'QUESTION_ID' => $questionList['QUESTION_ID'],
                        'EXAMPLE' => $questionList['EXAMPLE'],
                        'TYPE_SUB_CATEGORY' => $questionList['TYPE_SUB_CATEGORY'],
                        'TYPE_ANSWER' => $questionList['TYPE_ANSWER'],
                        'CREATED_DATE' => $dateTimeNow
                    ]);

                    if ($insertTestCategory) {
                        // GET TEST QUESTION ID
                        $testQuestionId = TestQuestionsModel::select('TEST_QUESTION_ID')
                            ->where('TEST_CATEGORY_ID', $testCategoryId)
                            ->orderBy('TEST_QUESTION_ID', 'desc')
                            ->first();
                        $testQueId = $testQuestionId->TEST_QUESTION_ID;

                        $randomChoices = $questionList['RANDOM_ANSWER'];
                        $this->insertTestChoices($testQueId, $questionList['QUESTION_ID'], $randomChoices);
                    }
                }
                $dtQuestionExample3 = QuestionModel::select('QUESTION_ID', 'TYPE_SUB_CATEGORY', 'EXAMPLE', 'TYPE_ANSWER', 'RANDOM_ANSWER', 'QUESTION_CHARACTER')
                    ->where('VERSION_ID', $VersionId)
                    ->where('EXAMPLE', 1)
                    ->where('TYPE_SUB_CATEGORY', 'SERIES_COMPLETION')
                    ->get();
                $dtQuestion3 = QuestionModel::select('QUESTION_ID', 'TYPE_SUB_CATEGORY', 'EXAMPLE', 'TYPE_ANSWER', 'RANDOM_ANSWER', 'QUESTION_CHARACTER')
                    ->where('VERSION_ID', $VersionId)
                    ->where('EXAMPLE', 0)
                    ->where('TYPE_SUB_CATEGORY', 'SERIES_COMPLETION')
                    ->inRandomOrder()
                    ->get()
                    ->toArray();
                foreach ($dtQuestionExample3 as $questionList2) {
                    $seq++;
                    $insertTestCategory = TestQuestionsModel::insert([
                        'TEST_CATEGORY_ID' => $testCategoryId,
                        'QUESTION_SEQ' => $seq,
                        'QUESTION_ID' => $questionList2->QUESTION_ID,
                        'EXAMPLE' => $questionList2->EXAMPLE,
                        'TYPE_SUB_CATEGORY' => $questionList2->TYPE_SUB_CATEGORY,
                        'TYPE_ANSWER' => $questionList2->TYPE_ANSWER,
                        'CREATED_DATE' => $dateTimeNow
                    ]);

                    if ($insertTestCategory) {
                        // GET TEST QUESTION ID
                        $testQuestionId = TestQuestionsModel::select('TEST_QUESTION_ID')
                            ->where('TEST_CATEGORY_ID', $testCategoryId)
                            ->orderBy('TEST_QUESTION_ID', 'desc')
                            ->first();
                        $testQueId = $testQuestionId->TEST_QUESTION_ID;

                        $randomChoices = $questionList2->RANDOM_ANSWER;
                        $this->insertTestChoices($testQueId, $questionList2->QUESTION_ID, $randomChoices);

                        //INSERT TEST MEMORIES
                        // QUESTION CHARACTER UNTUK JML KARAKTER YG DI RANDOM DI CATEGORY MEMORIES
                        $queCharacter = $questionList2->QUESTION_CHARACTER;

                    }
                }
                foreach ($dtQuestion3 as $questionList) {
                    $seq++;
                    $insertTestCategory = TestQuestionsModel::insert([
                        'TEST_CATEGORY_ID' => $testCategoryId,
                        'QUESTION_SEQ' => $seq,
                        'QUESTION_ID' => $questionList['QUESTION_ID'],
                        'EXAMPLE' => $questionList['EXAMPLE'],
                        'TYPE_SUB_CATEGORY' => $questionList['TYPE_SUB_CATEGORY'],
                        'TYPE_ANSWER' => $questionList['TYPE_ANSWER'],
                        'CREATED_DATE' => $dateTimeNow
                    ]);

                    if ($insertTestCategory) {
                        // GET TEST QUESTION ID
                        $testQuestionId = TestQuestionsModel::select('TEST_QUESTION_ID')
                            ->where('TEST_CATEGORY_ID', $testCategoryId)
                            ->orderBy('TEST_QUESTION_ID', 'desc')
                            ->first();
                        $testQueId = $testQuestionId->TEST_QUESTION_ID;

                        $randomChoices = $questionList['RANDOM_ANSWER'];
                        $this->insertTestChoices($testQueId, $questionList['QUESTION_ID'], $randomChoices);
                    }
                }
            } else {
                $dtQuestion1 = QuestionModel::select('QUESTION_ID', 'TYPE_SUB_CATEGORY', 'EXAMPLE', 'TYPE_ANSWER', 'RANDOM_ANSWER', 'QUESTION_CHARACTER')
                    ->where('VERSION_ID', $VersionId)
                    ->where('TYPE_SUB_CATEGORY', 'ANALOGY')
                    ->orderBy('EXAMPLE', 'DESC', 'QUESTION_ID', 'ASC')
                    ->get()
                    ->toArray();
                // INSERT DATE KE PSY_TEST_QUESTION
                $seq = 0;
                foreach ($dtQuestion1 as $questionList) {
                    $seq++;
                    $insertTestCategory = TestQuestionsModel::insert([
                        'TEST_CATEGORY_ID' => $testCategoryId,
                        'QUESTION_SEQ' => $seq,
                        'QUESTION_ID' => $questionList['QUESTION_ID'],
                        'EXAMPLE' => $questionList['EXAMPLE'],
                        'TYPE_SUB_CATEGORY' => $questionList['TYPE_SUB_CATEGORY'],
                        'TYPE_ANSWER' => $questionList['TYPE_ANSWER'],
                        'CREATED_DATE' => $dateTimeNow
                    ]);

                    if ($insertTestCategory) {
                        // GET TEST QUESTION ID
                        $testQuestionId = TestQuestionsModel::select('TEST_QUESTION_ID')
                            ->where('TEST_CATEGORY_ID', $testCategoryId)
                            ->orderBy('TEST_QUESTION_ID', 'desc')
                            ->first();
                        $testQueId = $testQuestionId->TEST_QUESTION_ID;

                        $randomChoices = $questionList['RANDOM_ANSWER'];
                        $this->insertTestChoices($testQueId, $questionList['QUESTION_ID'], $randomChoices);
                    }
                }
                $dtQuestion2 = QuestionModel::select('QUESTION_ID', 'TYPE_SUB_CATEGORY', 'EXAMPLE', 'TYPE_ANSWER', 'RANDOM_ANSWER', 'QUESTION_CHARACTER')
                    ->where('VERSION_ID', $VersionId)
                    ->where('TYPE_SUB_CATEGORY', 'CLASSIFICATION')
                    ->orderBy('EXAMPLE', 'DESC', 'QUESTION_ID', 'ASC')
                    ->get()
                    ->toArray();
                // INSERT DATE KE PSY_TEST_QUESTION
                foreach ($dtQuestion2 as $questionList) {
                    $seq++;
                    $insertTestCategory = TestQuestionsModel::insert([
                        'TEST_CATEGORY_ID' => $testCategoryId,
                        'QUESTION_SEQ' => $seq,
                        'QUESTION_ID' => $questionList['QUESTION_ID'],
                        'EXAMPLE' => $questionList['EXAMPLE'],
                        'TYPE_SUB_CATEGORY' => $questionList['TYPE_SUB_CATEGORY'],
                        'TYPE_ANSWER' => $questionList['TYPE_ANSWER'],
                        'CREATED_DATE' => $dateTimeNow
                    ]);

                    if ($insertTestCategory) {
                        // GET TEST QUESTION ID
                        $testQuestionId = TestQuestionsModel::select('TEST_QUESTION_ID')
                            ->where('TEST_CATEGORY_ID', $testCategoryId)
                            ->orderBy('TEST_QUESTION_ID', 'desc')
                            ->first();
                        $testQueId = $testQuestionId->TEST_QUESTION_ID;

                        $randomChoices = $questionList['RANDOM_ANSWER'];
                        $this->insertTestChoices($testQueId, $questionList['QUESTION_ID'], $randomChoices);
                    }
                }
                $dtQuestion3 = QuestionModel::select('QUESTION_ID', 'TYPE_SUB_CATEGORY', 'EXAMPLE', 'TYPE_ANSWER', 'RANDOM_ANSWER', 'QUESTION_CHARACTER')
                    ->where('VERSION_ID', $VersionId)
                    ->where('TYPE_SUB_CATEGORY', 'SERIES_COMPLETION')
                    ->orderBy('EXAMPLE', 'DESC', 'QUESTION_ID', 'ASC')
                    ->get()
                    ->toArray();
                // INSERT DATE KE PSY_TEST_QUESTION
                foreach ($dtQuestion3 as $questionList) {
                    $seq++;
                    $insertTestCategory = TestQuestionsModel::insert([
                        'TEST_CATEGORY_ID' => $testCategoryId,
                        'QUESTION_SEQ' => $seq,
                        'QUESTION_ID' => $questionList['QUESTION_ID'],
                        'EXAMPLE' => $questionList['EXAMPLE'],
                        'TYPE_SUB_CATEGORY' => $questionList['TYPE_SUB_CATEGORY'],
                        'TYPE_ANSWER' => $questionList['TYPE_ANSWER'],
                        'CREATED_DATE' => $dateTimeNow
                    ]);

                    if ($insertTestCategory) {
                        // GET TEST QUESTION ID
                        $testQuestionId = TestQuestionsModel::select('TEST_QUESTION_ID')
                            ->where('TEST_CATEGORY_ID', $testCategoryId)
                            ->orderBy('TEST_QUESTION_ID', 'desc')
                            ->first();
                        $testQueId = $testQuestionId->TEST_QUESTION_ID;

                        $randomChoices = $questionList['RANDOM_ANSWER'];
                        $this->insertTestChoices($testQueId, $questionList['QUESTION_ID'], $randomChoices);
                    }
                }
            }
        } else {
            if ($randomQuestion == 1 OR $randomQuestion == true) {
                $dtQuestionExample = QuestionModel::select('QUESTION_ID', 'TYPE_SUB_CATEGORY', 'EXAMPLE', 'TYPE_ANSWER', 'RANDOM_ANSWER', 'QUESTION_CHARACTER')
                    ->where('VERSION_ID', $VersionId)
                    ->where('EXAMPLE', 1)
                    ->get();
                if ($catId == 3) {
                    $dtQuestion = QuestionModel::select('QUESTION_ID', 'TYPE_SUB_CATEGORY', 'EXAMPLE', 'TYPE_ANSWER', 'RANDOM_ANSWER', 'QUESTION_CHARACTER', 'NARRATION_ID')
                        ->where('VERSION_ID', $VersionId)
                        ->where('EXAMPLE', 0)
                        ->inRandomOrder()
                        ->get()
                        ->toArray();

                    usort($dtQuestion, function ($a, $b) {
                        return $a['NARRATION_ID'] - $b['NARRATION_ID'];
                    });
                } else {
                    $dtQuestion = QuestionModel::select('QUESTION_ID', 'TYPE_SUB_CATEGORY', 'EXAMPLE', 'TYPE_ANSWER', 'RANDOM_ANSWER', 'QUESTION_CHARACTER')
                        ->where('VERSION_ID', $VersionId)
                        ->where('EXAMPLE', 0)
                        ->inRandomOrder()
                        ->get()
                        ->toArray();
                }
            } else {

                $dtQuestionExample = QuestionModel::select('QUESTION_ID', 'TYPE_SUB_CATEGORY', 'EXAMPLE', 'TYPE_ANSWER', 'RANDOM_ANSWER', 'QUESTION_CHARACTER')
                    ->where('VERSION_ID', $VersionId)
                    ->where('EXAMPLE', 1)
                    ->get();
                $dtQuestion = QuestionModel::select('QUESTION_ID', 'TYPE_SUB_CATEGORY', 'EXAMPLE', 'TYPE_ANSWER', 'RANDOM_ANSWER', 'QUESTION_CHARACTER')
                    ->where('VERSION_ID', $VersionId)
                    ->orderBy('QUESTION_ID', 'ASC')
                    ->get()
                    ->toArray();
            }


            // INSERT DATE KE PSY_TEST_QUESTION
            $seq = 0;
            foreach ($dtQuestionExample as $questionList2) {
                $seq++;
                $insertTestCategory = TestQuestionsModel::insert([
                    'TEST_CATEGORY_ID' => $testCategoryId,
                    'QUESTION_SEQ' => $seq,
                    'QUESTION_ID' => $questionList2->QUESTION_ID,
                    'EXAMPLE' => $questionList2->EXAMPLE,
                    'TYPE_SUB_CATEGORY' => $questionList2->TYPE_SUB_CATEGORY,
                    'TYPE_ANSWER' => $questionList2->TYPE_ANSWER,
                    'CREATED_DATE' => $dateTimeNow
                ]);

                if ($insertTestCategory) {
                    // GET TEST QUESTION ID
                    $testQuestionId = TestQuestionsModel::select('TEST_QUESTION_ID')
                        ->where('TEST_CATEGORY_ID', $testCategoryId)
                        ->orderBy('TEST_QUESTION_ID', 'desc')
                        ->first();
                    $testQueId = $testQuestionId->TEST_QUESTION_ID;

                    $randomChoices = $questionList2->RANDOM_ANSWER;
                    $this->insertTestChoices($testQueId, $questionList2->QUESTION_ID, $randomChoices);

                    //INSERT TEST MEMORIES
                    // QUESTION CHARACTER UNTUK JML KARAKTER YG DI RANDOM DI CATEGORY MEMORIES
                    $queCharacter = $questionList2->QUESTION_CHARACTER;
                    if ($catId == 6) {
                        $this->insertTestMemories($testQueId, $queCharacter);
                    }
                }
            }
            if ($catId == 6) {
                usort($dtQuestion, function ($a, $b) {
                    return $a['QUESTION_CHARACTER'] - $b['QUESTION_CHARACTER'];
                });
            }

            foreach ($dtQuestion as $questionList) {
                $seq++;
                $insertTestCategory = TestQuestionsModel::insert([
                    'TEST_CATEGORY_ID' => $testCategoryId,
                    'QUESTION_SEQ' => $seq,
                    'QUESTION_ID' => $questionList['QUESTION_ID'],
                    'EXAMPLE' => $questionList['EXAMPLE'],
                    'TYPE_SUB_CATEGORY' => $questionList['TYPE_SUB_CATEGORY'],
                    'TYPE_ANSWER' => $questionList['TYPE_ANSWER'],
                    'CREATED_DATE' => $dateTimeNow
                ]);

                if ($insertTestCategory) {
                    // GET TEST QUESTION ID
                    $testQuestionId = TestQuestionsModel::select('TEST_QUESTION_ID')
                        ->where('TEST_CATEGORY_ID', $testCategoryId)
                        ->orderBy('TEST_QUESTION_ID', 'desc')
                        ->first();
                    $testQueId = $testQuestionId->TEST_QUESTION_ID;

                    $randomChoices = $questionList['RANDOM_ANSWER'];
                    $this->insertTestChoices($testQueId, $questionList['QUESTION_ID'], $randomChoices);

                    //INSERT TEST MEMORIES
                    // QUESTION CHARACTER UNTUK JML KARAKTER YG DI RANDOM DI CATEGORY MEMORIES
                    $queCharacter = $questionList['QUESTION_CHARACTER'];
                    if ($catId == 6) {
                        $this->insertTestMemories($testQueId, $queCharacter);
                    }
                }
            }
        }
    }

    public function insertTestChoices($testQuestionId, $questionId, $randomChoices)
    {

        $dateNow = date('Y-m-d');
        $dateTimeNow = Carbon::now();

        if ($randomChoices == 1) {
            $dtChoices = AnsChoicesModel::select('ANS_CHOICE_ID')
                ->where('QUESTION_ID', $questionId)
                ->inRandomOrder()
                ->get();
        } else {
            $dtChoices = AnsChoicesModel::select('ANS_CHOICE_ID')
                ->where('QUESTION_ID', $questionId)
                ->get();
        }

        // INSERT DATE KE PSY_TEST_CHOICES
        $seq = 0;
        foreach ($dtChoices as $choices) {
            $seq++;
            $insertTestChoice = TestChoicesModel::insert([
                'TEST_QUESTION_ID' => $testQuestionId,
                'ANS_CHOICE_SEQ' => $seq,
                'ANS_CHOICE_ID' => $choices->ANS_CHOICE_ID,
                'CREATION_DATE' => $dateTimeNow
            ]);
        }
    }

    public function insertTestMemories($testQuestionId, $questionCharacter)
    {
        $dateTimeNow = Carbon::now();
        $randNumber = array();
        for ($i = 0; $i < $questionCharacter; $i++) {
            $rand = rand(0, 9);
            array_push($randNumber, $rand);
        }
        $dtRandom = implode("", array_flatten($randNumber));

        $insertTestMemories = TestMemoriesModel::insert([
            'TEST_QUESTION_ID' => $testQuestionId,
            'QUESTION_TEXT' => $dtRandom,
            'CREATED_DATE' => $dateTimeNow
        ]);
    }

    public function getSubTest($scheduleId, $pesan)
    {
        $dateNow = date('Y-m-d');
        // GET TEST CATEGORY ID
        $testCategoryId = TestCategoriesModel::select('SUB_CATEGORY_ID', 'CATEGORY_ID', 'TEST_CATEGORY_ID', 'CATEGORY_SEQ')
            ->where('SCHEDULE_ID', $scheduleId)
            ->where('IS_TEST_CATEGORY_ACTIVE', 1)
            ->whereNotIn('CATEGORY_STATUS', ['COMPLETE'])
            ->first();
        if ($testCategoryId) {
            // if(session()->get('ansMemory')){
            //     session()->forget('ansMemory');
            // }
            $subCatId = $testCategoryId->SUB_CATEGORY_ID;
            $catId = $testCategoryId->CATEGORY_ID;
            $testcatId = $testCategoryId->TEST_CATEGORY_ID;
            $testcatSeq = $testCategoryId->CATEGORY_SEQ;

            $dtCategoryVersion = QueSubCategoryVersionsModel::select('WORK_INSTRUCTION')
                ->where('SUB_CATEGORY_ID', $subCatId)
                ->whereRaw('? between DATE_FROM and DATE_TO', $dateNow)
                ->first();
            $instruction = $dtCategoryVersion->WORK_INSTRUCTION;

            if ($pesan == 0) {
                return view('subtestInstruction')
                    ->with('ins', $instruction)
                    ->with('catId', $catId)
                    ->with('testcatId', $testcatId)
                    ->with('testcatSeq', $testcatSeq)
                    ->with('schId', $scheduleId);
            } else {
                return view('subtestInstruction')
                    ->with('ins', $instruction)
                    ->with('catId', $catId)
                    ->with('testcatId', $testcatId)
                    ->with('testcatSeq', $testcatSeq)
                    ->with('schId', $scheduleId)
                    ->with('pesan', $pesan);
            }
        } else {
            $updateStatusSchedule = ScheduleHistoriesModel::where('SCHEDULE_ID', $scheduleId)
                ->whereRaw('? between PLAN_START_DATE and PLAN_END_DATE', $dateNow)
                ->update(['TEST_STATUS' => 'COMPLETE']);
            // if($updateStatusSchedule){
            //     $parameter =[
            //                   'scheduleId' => $scheduleId,
            //               ];
            //     $parameter= Crypt::encrypt($parameter);
            //     // return redirect()->action('ScoringController@getCategory', ['id' => $parameter]);
            //     return redirect()->route('score', $parameter);
            DB::commit();
            return $this->getCategory($scheduleId);

            //     // return view('finalGreeting');
            // }else{
            //     // $pesan = "Gagal Update Data";
            //     // return view('errorPage')
            //     //     ->with('pesan',$pesan);
            //     $parameter =[
            //                   'scheduleId' => $scheduleId,
            //               ];
            //     $parameter= Crypt::encrypt($parameter);
            //     // return redirect()->action('ScoringController@getCategory', ['id' => $parameter]);
            //     return redirect()->route('score', $parameter);
            // }
            // $parameter =[
            //               'scheduleId' => $scheduleId,
            //           ];
            // $parameter= Crypt::encrypt($parameter);
            // return redirect("/scoring/". $parameter);
        }
    }

    // public function startTest($categoryId, $scheduleId, $testCategoryId){
    public function startTest($id)
    {
        $data = Crypt::decrypt($id);
        $categoryId = $data['categoryId'];
        $scheduleId = $data['scheduleId'];
        $testCategoryId = $data['testCategoryId'];
        $dateTimeNow = Carbon::now();

        // GET ALL SOAL
        $queList = QuestionModel::join("psy_test_questions", 'psy_test_questions.QUESTION_ID', '=', 'que_questions.QUESTION_ID')
            ->where('psy_test_questions.TEST_CATEGORY_ID', $testCategoryId)
            ->where('psy_test_questions.STATUS', 0)
            ->get()
            ->toArray();
        $queListExample = QuestionModel::join("psy_test_questions", 'psy_test_questions.QUESTION_ID', '=', 'que_questions.QUESTION_ID')
            ->where('psy_test_questions.TEST_CATEGORY_ID', $testCategoryId)
            ->where('psy_test_questions.EXAMPLE', 1)
            ->get()
            ->toArray();
        $jmlExample = count($queListExample);
        $updateStartdateCategory = TestCategoriesModel::where('TEST_CATEGORY_ID', $testCategoryId)
            ->update(['CATEGORY_START_DATE' => $dateTimeNow]);

        $ansList = array();
        // jikan bukan category memory makan akan mengambil data choices
        if ($categoryId != 6) {
            foreach ($queList as $key => $value) {
                $queids = $value['QUESTION_ID'];

                $ansList2 = AnsChoicesModel::where('QUESTION_ID', $queids)
                    ->get()
                    ->toArray();
                $ansList[$queids] = $ansList2;
            }
        }
        //mengambil naration
        if ($categoryId == 2 || $categoryId == 3) {
            foreach ($queList as $key => $value) {
                $narids = $value['NARRATION_ID'];
                $queids = $value['QUESTION_ID'];

                $getNaration = NarrationsModel::select('NARRATION_TEXT')
                    ->where('NARRATION_ID', $narids)
                    ->first();
                if ($getNaration)
                    array_push($queList[$key], $getNaration->NARRATION_TEXT);
                else
                    array_push($queList[$key], "-");
            }
        }

        $currentSoal = 0;
        $jmlSoal = count($queList);
        //status 1 = new
        $status = 1;
        // echo '<script type="text/javascript">localStorage.removeItem("startTime");</script>';
        if ($categoryId == 1) {
            return $this->soalInductiveReasoning($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
        } else if ($categoryId == 2) {
            return $this->soalDeductiveReasoning($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
        } else if ($categoryId == 3) {
            return $this->soalReadingComprehension($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
        } else if ($categoryId == 4) {
            return $this->soalArithmeticAbility($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
        } else if ($categoryId == 5) {
            return $this->soalSpatialAbility($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
        } else if ($categoryId == 6) {
            foreach ($queList as $key => $value) {
                $getQueMemoryList = TestMemoriesModel::select('QUESTION_TEXT')->where('TEST_QUESTION_ID', $queList[$key]['TEST_QUESTION_ID'])->first();
                $getQueText = $getQueMemoryList->QUESTION_TEXT;
                array_push($queList[$key], $getQueText);
            }
            return $this->soalMemory($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
        }
    }

    public function soalInductiveReasoning($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status)
    {
        // $ansList = AnsChoicesModel::where('QUESTION_ID',$queList[$currentSoal]['QUESTION_ID'])
        //     ->get()
        //     ->toArray();
        $nextSoal = $currentSoal + 1;
        $alphas = range('A', 'Z');
        $typeSoal = $queList[$currentSoal]['TYPE_SUB_CATEGORY'];
        $typeAnswer = $queList[$currentSoal]['TYPE_ANSWER'];
        if ($typeSoal == 'SERIES_COMPLETION') {
            if ($typeAnswer == 'MULTIPLE_CHOICE') {
                return view('testInductiveReasoningSeriesCompletion')
                    ->with('queList', $queList)
                    ->with('currentSoal', $currentSoal)
                    ->with('nextSoal', $nextSoal)
                    ->with('categoryId', $categoryId)
                    ->with('jmlSoal', $jmlSoal)
                    ->with('ansList', $ansList)
                    ->with('alphas', $alphas)
                    ->with('schId', $scheduleId)
                    ->with('testCatId', $testCategoryId)
                    ->with('jmlExample', $jmlExample)
                    ->with('status', $status);
            } else {
                return view('testInductiveReasoningSeriesCompletion2')
                    ->with('queList', $queList)
                    ->with('currentSoal', $currentSoal)
                    ->with('nextSoal', $nextSoal)
                    ->with('categoryId', $categoryId)
                    ->with('jmlSoal', $jmlSoal)
                    ->with('ansList', $ansList)
                    ->with('schId', $scheduleId)
                    ->with('testCatId', $testCategoryId)
                    ->with('jmlExample', $jmlExample)
                    ->with('status', $status);
            }
        } else if ($typeSoal == 'ANALOGY') {
            if ($typeAnswer == 'MULTIPLE_CHOICE') {
                return view('testInductiveReasoningAnalogy2')
                    ->with('queList', $queList)
                    ->with('currentSoal', $currentSoal)
                    ->with('nextSoal', $nextSoal)
                    ->with('categoryId', $categoryId)
                    ->with('jmlSoal', $jmlSoal)
                    ->with('ansList', $ansList)
                    ->with('alphas', $alphas)
                    ->with('schId', $scheduleId)
                    ->with('testCatId', $testCategoryId)
                    ->with('jmlExample', $jmlExample)
                    ->with('status', $status);
            } else {
                return view('testInductiveReasoningAnalogy')
                    ->with('queList', $queList)
                    ->with('currentSoal', $currentSoal)
                    ->with('nextSoal', $nextSoal)
                    ->with('categoryId', $categoryId)
                    ->with('jmlSoal', $jmlSoal)
                    ->with('ansList', $ansList)
                    ->with('alphas', $alphas)
                    ->with('schId', $scheduleId)
                    ->with('testCatId', $testCategoryId)
                    ->with('jmlExample', $jmlExample)
                    ->with('status', $status);
            }
        } else if ($typeSoal == 'CLASSIFICATION') {
            if ($typeAnswer == 'MULTIPLE_GROUP') {
                return view('testInductiveReasoningClassification2')
                    ->with('queList', $queList)
                    ->with('currentSoal', $currentSoal)
                    ->with('nextSoal', $nextSoal)
                    ->with('categoryId', $categoryId)
                    ->with('jmlSoal', $jmlSoal)
                    ->with('ansList', $ansList)
                    ->with('alphas', $alphas)
                    ->with('schId', $scheduleId)
                    ->with('testCatId', $testCategoryId)
                    ->with('jmlExample', $jmlExample)
                    ->with('status', $status);
            } else {
                return view('testInductiveReasoningClassification')
                    ->with('queList', $queList)
                    ->with('currentSoal', $currentSoal)
                    ->with('nextSoal', $nextSoal)
                    ->with('categoryId', $categoryId)
                    ->with('jmlSoal', $jmlSoal)
                    ->with('ansList', $ansList)
                    ->with('alphas', $alphas)
                    ->with('schId', $scheduleId)
                    ->with('testCatId', $testCategoryId)
                    ->with('jmlExample', $jmlExample)
                    ->with('status', $status);
            }
        }
    }

    public function soalReadingComprehension($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status)
    {

        // $ansList = AnsChoicesModel::where('QUESTION_ID',$queList[$currentSoal]['QUESTION_ID'])
        //     ->get()
        //     ->toArray();
        // $getNaration = NarrationsModel::select('NARRATION_TEXT')
        //     ->where('NARRATION_ID',$queList[$currentSoal]['NARRATION_ID'])
        //     ->first();
        $nextSoal = $currentSoal + 1;
        // $naration = $getNaration->NARRATION_TEXT;
        $alphas = range('A', 'Z');
        return view('testReadingComprehension')
            ->with('queList', $queList)
            ->with('ansList', $ansList)
            ->with('currentSoal', $currentSoal)
            ->with('nextSoal', $nextSoal)
            ->with('categoryId', $categoryId)
            ->with('jmlSoal', $jmlSoal)
            // ->with('naration',$naration)
            ->with('alphas', $alphas)
            ->with('schId', $scheduleId)
            ->with('testCatId', $testCategoryId)
            ->with('jmlExample', $jmlExample)
            ->with('status', $status);
    }

    public function soalDeductiveReasoning($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status)
    {
        // $ansList = AnsChoicesModel::where('QUESTION_ID',$queList[$currentSoal]['QUESTION_ID'])
        //     ->get()
        //     ->toArray();
        $getNaration = NarrationsModel::select('NARRATION_TEXT')
            ->where('NARRATION_ID', $queList[$currentSoal]['NARRATION_ID'])
            ->first();
        // if($getNaration){
        //     $naration = $getNaration->NARRATION_TEXT;
        // }else{
        //     $naration = "";
        // }
        $nextSoal = $currentSoal + 1;
        $alphas = range('A', 'Z');
        return view('testDeductiveReasoning')
            ->with('queList', $queList)
            ->with('ansList', $ansList)
            ->with('currentSoal', $currentSoal)
            ->with('nextSoal', $nextSoal)
            ->with('categoryId', $categoryId)
            ->with('jmlSoal', $jmlSoal)
            // ->with('naration',$naration)
            ->with('alphas', $alphas)
            ->with('schId', $scheduleId)
            ->with('testCatId', $testCategoryId)
            ->with('jmlExample', $jmlExample)
            ->with('status', $status);
    }

    public function soalSpatialAbility($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status)
    {
        // $ansList = AnsChoicesModel::where('QUESTION_ID',$queList[$currentSoal]['QUESTION_ID'])
        //     ->get()
        //     ->toArray();
        $nextSoal = $currentSoal + 1;
        $alphas = range('A', 'Z');
        return view('testSpatialAbility')
            ->with('queList', $queList)
            ->with('ansList', $ansList)
            ->with('currentSoal', $currentSoal)
            ->with('nextSoal', $nextSoal)
            ->with('categoryId', $categoryId)
            ->with('jmlSoal', $jmlSoal)
            ->with('alphas', $alphas)
            ->with('schId', $scheduleId)
            ->with('testCatId', $testCategoryId)
            ->with('jmlExample', $jmlExample)
            ->with('status', $status);
    }

    public function soalArithmeticAbility($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status)
    {
        // $ansList = AnsChoicesModel::where('QUESTION_ID',$queList[$currentSoal]['QUESTION_ID'])
        //     ->get()
        //     ->toArray();
        $nextSoal = $currentSoal + 1;
        $alphas = range('A', 'Z');
        return view('testArithmeticAbility')
            ->with('queList', $queList)
            ->with('ansList', $ansList)
            ->with('currentSoal', $currentSoal)
            ->with('nextSoal', $nextSoal)
            ->with('categoryId', $categoryId)
            ->with('jmlSoal', $jmlSoal)
            ->with('alphas', $alphas)
            ->with('schId', $scheduleId)
            ->with('testCatId', $testCategoryId)
            ->with('jmlExample', $jmlExample)
            ->with('status', $status);
    }

    public function soalMemory($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status)
    {
        $nextSoal = $currentSoal + 1;
        return view('testMemory')
            ->with('queList', $queList)
            ->with('ansList', $ansList)
            ->with('currentSoal', $currentSoal)
            ->with('nextSoal', $nextSoal)
            ->with('categoryId', $categoryId)
            ->with('jmlSoal', $jmlSoal)
            ->with('schId', $scheduleId)
            ->with('testCatId', $testCategoryId)
            ->with('jmlExample', $jmlExample)
            ->with('status', $status);
    }

    public function saveChoicesSession()
    {
        // $data = Crypt::decrypt($id);
        $param = Input::post('parameter') !== null ? Crypt::decrypt(Input::post('parameter')) : Input::all(); // (Crypt::decrypt(Input::post('parameter')) !== null) ? Crypt::decrypt(Input::post('parameter')) : Input::all();
        //  echo $param['currentSoal'];

        $currentSoalSessions = null;
        $jmlSoalSessions = null;
        $scheduleIdSessions = null;
        $categoryIdSessions = null;
        $testQueIdSessions = null;
        $testCategoryIdSessions = null;
        $jmlExampleSessions = null;
        $dataSessions = null;
        $dataAnsSessions = null;
        $choiceSessions = null;
        $choice2Sessions = null;


        if (Input::isMethod('get')) {
            $currentSoal = session()->get('currentSoal');
            $jmlSoal = session()->get('jmlSoal');
            $scheduleId = session()->get('scheduleId');
            $categoryId = session()->get('categoryId');
            $testQueId = session()->get('testQueId');
            $testCategoryId = session()->get('testCategoryId');
            $jmlExample = session()->get('jmlExample');
            $dateTimeNow = Carbon::now();
            $queList = json_decode(session()->get('data'), true);
            $ansList = json_decode(session()->get('dataAns'), true);
            $prevSoal = $currentSoal - 1;
            if ($categoryId == 1 AND $queList[$prevSoal]['TYPE_SUB_CATEGORY'] != 'ANALOGY') {
                if ($queList[$prevSoal]['TYPE_ANSWER'] == 'TEXT_SERIES' OR $queList[$prevSoal]['TYPE_ANSWER'] == 'MULTIPLE_GROUP') {
                    $arr = [session()->get('choice'), session()->get('choice2')];
                    $choice = implode("/", $arr);
                } else {
                    $choice = session()->get('choice');
                }
            } else {
                $choice = session()->get('choice');
            }
        }
        if (Input::isMethod('post')) {
            $currentSoal = $param['currentSoal'];
            $jmlSoal = $param['jmlSoal'];
            $scheduleId = $param['scheduleId'];
            $categoryId = $param['categoryId'];
            $testQueId = $param['testQueId'];
            $testCategoryId = $param['testCategoryId'];
            $jmlExample = $param['jmlExample'];
            $dateTimeNow = Carbon::now();
            $queList = json_decode(Input::post('data'), true);
            $ansList = json_decode(Input::post('dataAns'), true);
            $prevSoal = $currentSoal - 1;
            if ($categoryId == 1 AND $queList[$prevSoal]['TYPE_SUB_CATEGORY'] != 'ANALOGY') {
                if ($queList[$prevSoal]['TYPE_ANSWER'] == 'TEXT_SERIES' OR $queList[$prevSoal]['TYPE_ANSWER'] == 'MULTIPLE_GROUP') {
                    $arr = [Input::post('choice'), Input::post('choice2')];
                    $choice = implode("/", $arr);
                } else {
                    $choice = Input::post('choice');
                }
            } else {
                $choice = Input::post('choice');
            }

            session()->put('currentSoal', $param['currentSoal']);
            session()->put('jmlSoal', $param['jmlSoal']);;
            session()->put('scheduleId', $param['scheduleId']);;
            session()->put('categoryId', $param['categoryId']);;
            session()->put('testQueId', $param['testQueId']);;
            session()->put('testCategoryId', $param['testCategoryId']);;
            session()->put('jmlExample', $param['jmlExample']);;

            session()->put('data', Input::post('data'));
            session()->put('dataAns', Input::post('dataAns'));
            session()->put('choice', Input::post('choice'));
            session()->put('choice2', Input::post('choice2'));
        }
        // exit();

        //var_dump($param);

        //JIKA BUKAN EXAMPLE SOAL MAKA JAWABAN AKAN DISIMPAN 
        if ($queList[$prevSoal]['EXAMPLE'] != 1) {
            $checkChoiceByQuestion = TestScoreModel::select('TEST_SCORE_ID')
                ->where('TEST_QUESTION_ID', $testQueId)
                ->first();
            if (!$checkChoiceByQuestion) {
                // SIMPAN JAWABAN KE DB
                try {
                    $insertChoices = TestScoreModel::insert([
                        'TEST_QUESTION_ID' => $testQueId,
                        'ORIGINAL_ANSWER' => $choice,
                        'CREATION_DATE' => $dateTimeNow
                    ]);
                    if ($insertChoices) {
                        $updateStatusQuestion = TestQuestionsModel::where('TEST_QUESTION_ID', $testQueId)
                            ->update(['STATUS' => 1]);
                        echo '<script type="text/javascript">localStorage.clear();</script>';
                        DB::commit();
                    }
                } catch (Exception $e) {
                    //status 0 = old
                    $status = 0;
                    if ($categoryId == 1) {
                        return $this->soalInductiveReasoning($queList, $categoryId, $prevSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
                    } else if ($categoryId == 2) {
                        return $this->soalDeductiveReasoning($queList, $categoryId, $prevSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
                    } else if ($categoryId == 3) {
                        return $this->soalReadingComprehension($queList, $categoryId, $prevSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
                    } else if ($categoryId == 4) {
                        return $this->soalArithmeticAbility($queList, $categoryId, $prevSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
                    } else if ($categoryId == 5) {
                        return $this->soalSpatialAbility($queList, $categoryId, $prevSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
                    } else if ($categoryId == 6) {
                        return $this->soalMemory($queList, $categoryId, $prevSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
                    }
                }
                // CEK JAWABAN MEMORY APABILA JAWABAN BENAR DAN MENUJU KAREK
                if ($categoryId == 6 && $currentSoal < $jmlSoal) {
                    $ansCorrect = $queList[$prevSoal][0];
                    $ansMemory = session()->get('ansMemory');
                    if (!$ansMemory) {
                        session()->put('ansMemory', 0);
                    }
                    if ($ansCorrect == $choice) {
                        $ansMemory2 = session()->get('ansMemory');
                        session()->put('ansMemory', $ansMemory2 + 1);
                    }
                    $ansMemory3 = session()->get('ansMemory');
                    if ($queList[$currentSoal]['QUESTION_CHARACTER'] > $queList[$prevSoal]['QUESTION_CHARACTER']) {
                        if ($ansMemory3 < 2 or $ansMemory3 == null) {
                            $currentSoal = $jmlSoal + 1;
                        }
                        session()->forget('ansMemory');
                    }
                }
            }
        } else {
            $updateStatusQuestion = TestQuestionsModel::where('TEST_QUESTION_ID', $testQueId)
                ->update(['STATUS' => 1]);
            DB::commit();
        }
        if ($currentSoal >= $jmlSoal) {
            $updateStatusCategory = TestCategoriesModel::where('TEST_CATEGORY_ID', $testCategoryId)
                ->update(['CATEGORY_STATUS' => 'COMPLETE', 'CATEGORY_SUBMIT_DATE' => $dateTimeNow]);
            if ($updateStatusCategory) {
                if ($categoryId == 6) {
                    for ($i = 0; $i < count($queList); $i++) {
                        $updateStatusQuestion = TestQuestionsModel::where('TEST_QUESTION_ID', $queList[$i]['TEST_QUESTION_ID'])
                            ->update(['STATUS' => 1]);
                    }
                    // echo "masuk";
                    // exit();
                    DB::commit();
                }
                return $this->getSubTest($scheduleId, 1);
            }
        } else {
            //status 1 = new
            $status = 1;
            if ($categoryId == 1) {
                return $this->soalInductiveReasoning($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
            } else if ($categoryId == 2) {
                return $this->soalDeductiveReasoning($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
            } else if ($categoryId == 3) {
                return $this->soalReadingComprehension($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
            } else if ($categoryId == 4) {
                return $this->soalArithmeticAbility($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
            } else if ($categoryId == 5) {
                return $this->soalSpatialAbility($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);
            } else if ($categoryId == 6) {
                return $this->soalMemory($queList, $categoryId, $currentSoal, $jmlSoal, $scheduleId, $testCategoryId, $jmlExample, $ansList, $status);

            }
        }
    }


    // SCORING
    public function getCategory($scheduleId)
    {
        $testCategoryId = TestCategoriesModel::select('TEST_CATEGORY_ID')
            ->where('SCHEDULE_ID', $scheduleId)
            ->where('IS_TEST_CATEGORY_ACTIVE', 1)
            ->where('CATEGORY_STATUS', ['COMPLETE'])
            ->get()
            ->toArray();
        return $this->getQuestion($testCategoryId, $scheduleId);

    }

    public function getQuestion($TestCategoryId, $scheduleId)
    {
        $listChoice = array();
        foreach ($TestCategoryId as $testCatId) {
            $queList = TestQuestionsModel::select('TEST_QUESTION_ID', 'QUESTION_ID', 'TYPE_SUB_CATEGORY', 'TYPE_ANSWER')
                ->where('TEST_CATEGORY_ID', $testCatId)
                ->where('EXAMPLE', 0)
                ->get()
                ->toArray();
            array_push($listChoice, $queList);
        }
        return $this->getCorrectAnswer($listChoice, $scheduleId);

    }

    public function getCorrectAnswer($listChoice, $scheduleId)
    {
        foreach ($listChoice as $key => $value) {
            foreach ($value as $a => $val) {
                $ans = '';
                if ($val['TYPE_ANSWER'] == 'MULTIPLE_CHOICE') {
                    $anss = AnsChoicesModel::
                    where('QUESTION_ID', $val['QUESTION_ID'])
                        ->where('CORRECT_ANSWER', 1)
                        ->pluck('ANS_CHOICE_ID')
                        ->toArray();
                    $ans = implode('', $anss);
                } else if ($val['TYPE_ANSWER'] == 'TEXT_SERIES') {
                    $correct = AnsTextSeriesModel::where('QUESTION_ID', $val['QUESTION_ID'])
                        ->orderBy('ANS_SEQUENCE')
                        ->pluck('CORRECT_TEXT')
                        ->toArray();
                    $ans = implode("/", $correct);
                } else if ($val['TYPE_ANSWER'] == 'MULTIPLE_GROUP') {
                    $get1 = AnsGroupModel::where('QUESTION_ID', $val['QUESTION_ID'])
                        ->where('GROUP_IMG', 1)
                        ->pluck('IMG_SEQUENCE')
                        ->toArray();
                    $get2 = AnsGroupModel::where('QUESTION_ID', $val['QUESTION_ID'])
                        ->where('GROUP_IMG', 2)
                        ->pluck('IMG_SEQUENCE')
                        ->toArray();
                    $ans1 = implode("", $get1);
                    $ans2 = implode("", $get2);
                    if (count($get1) == 3) {
                        $anss = [$ans1, $ans2];
                        $ans = implode("/", $anss);
                    } else {
                        $anss = [$ans2, $ans1];
                        $ans = implode("/", $anss);
                    }
                } else {
                    $anss = TestMemoriesModel::where('TEST_QUESTION_ID', $val['TEST_QUESTION_ID'])
                        ->pluck('QUESTION_TEXT')
                        ->toArray();
                    $ans = implode('', $anss);
                }
                array_push($listChoice[$key][$a], $ans);
            }
        }
        return $this->ScoringRaw($listChoice, $scheduleId);

    }

    public function ScoringRaw($listChoice, $scheduleId)
    {
        foreach ($listChoice as $key => $value) {
            foreach ($value as $a => $val) {
                $correct = 0;
                if ($listChoice[$key][$a]['TYPE_SUB_CATEGORY'] == 'CLASSIFICATION') {
                    $getss = TestScoreModel::select('ORIGINAL_ANSWER')
                        ->where('TEST_QUESTION_ID', $listChoice[$key][$a]['TEST_QUESTION_ID'])
                        ->first();
                    if ($getss != null) {
                        $gets = strtoupper($getss->ORIGINAL_ANSWER);
                        if ($gets != "/") {
                            $ans_applicant = explode("/", $gets);

                            if (count($ans_applicant) < 2) {
                                $correct = 0;
                            } else {
                                $ans_applicant1 = str_split($ans_applicant[0]);
                                $ans_applicant2 = str_split($ans_applicant[1]);
                                $ans_correct = explode("/", $listChoice[$key][$a][0]);
                                $ans_correct1 = str_split($ans_correct[0]);
                                $ans_correct2 = str_split($ans_correct[1]);
                                $correct = 1;
                                foreach ($ans_applicant1 as $ans_a) {
                                    if (!in_array($ans_a, $ans_correct1)) {
                                        $correct = 0;
                                    }
                                }
                                foreach ($ans_applicant2 as $ans_a2) {
                                    if (!in_array($ans_a2, $ans_correct2)) {
                                        $correct = 0;
                                    }
                                }
                            }
                        }
                    } else {
                        $correct = 0;
                    }
                } else {
                    $get = TestScoreModel::select('ORIGINAL_ANSWER')
                        ->where('TEST_QUESTION_ID', $listChoice[$key][$a]['TEST_QUESTION_ID'])
                        ->where('ORIGINAL_ANSWER', $listChoice[$key][$a][0])
                        ->count();
                    if ($get > 0) {
                        $correct = 1;
                    }
                }
                if ($correct != 0) {
                    $updateRaw = TestScoreModel::where('TEST_QUESTION_ID', $listChoice[$key][$a]['TEST_QUESTION_ID'])
                        ->update(['RAW_SCORE' => 1]);
                }
            }
        }
        return $this->CalculateRawScore($listChoice, $scheduleId);
    }

    public function CalculateRawScore($listChoice, $scheduleId)
    {
        $testCategoryId = TestCategoriesModel::select('TEST_CATEGORY_ID', 'CATEGORY_ID')
            ->where('SCHEDULE_ID', $scheduleId)
            ->where('IS_TEST_CATEGORY_ACTIVE', 1)
            ->where('CATEGORY_STATUS', ['COMPLETE'])
            ->get()
            ->toArray();

        $listChoice = array();
        foreach ($testCategoryId as $testCatId => $val) {
            $totRaw = 0;
            $queList = TestQuestionsModel::where('TEST_CATEGORY_ID', $val['TEST_CATEGORY_ID'])
                ->where('EXAMPLE', 0)
                ->pluck('TEST_QUESTION_ID')
                ->toArray();
            if ($val['CATEGORY_ID'] != 6) {
                foreach ($queList as $queId) {
                    // COUNT TOTAL RAW SCORE PER CATEGORY
                    $total = TestScoreModel::select('RAW_SCORE')
                        ->where('TEST_QUESTION_ID', $queId)
                        ->where('RAW_SCORE', 1)
                        ->first();
                    if ($total) {
                        $totRaw++;
                    }
                }
            } else {
                $getlastans = TestScoreModel::select('ORIGINAL_ANSWER')
                    ->whereIn('TEST_QUESTION_ID', $queList)
                    ->where('RAW_SCORE', 1)
                    ->orderBy('TEST_SCORE_ID', 'desc')
                    ->first();
                if ($getlastans) {
                    $getStimulus = strlen($getlastans->ORIGINAL_ANSWER);

                    $getQuestionByStimulus = TestMemoriesModel::select('TEST_QUESTION_ID')
                        ->whereIn('TEST_QUESTION_ID', $queList)
                        ->whereRaw('CHAR_LENGTH(QUESTION_TEXT) = ?', $getStimulus)
                        ->get()
                        ->toArray();

                    $totRaw2 = 0;
                    foreach ($getQuestionByStimulus as $queId) {
                        $total = TestScoreModel::select('RAW_SCORE')
                            ->where('TEST_QUESTION_ID', $queId)
                            ->where('RAW_SCORE', 1)
                            ->first();
                        if ($total) {
                            $totRaw2++;
                        }
                    }

                    if ($getStimulus == 4) {
                        if ($totRaw2 == 1) {
                            $totRaw = 4;
                        } else if ($totRaw2 == 2) {
                            $totRaw = 5;
                        } else if ($totRaw2 == 3) {
                            $totRaw = 6;
                        }
                    } else if ($getStimulus == 5) {
                        if ($totRaw2 == 1) {
                            $totRaw = 7;
                        } else if ($totRaw2 == 2) {
                            $totRaw = 8;
                        } else if ($totRaw2 == 3) {
                            $totRaw = 9;
                        }
                    } else if ($getStimulus == 6) {
                        if ($totRaw2 == 1) {
                            $totRaw = 10;
                        } else if ($totRaw2 == 2) {
                            $totRaw = 11;
                        } else if ($totRaw2 == 3) {
                            $totRaw = 12;
                        }
                    } else if ($getStimulus == 7) {
                        if ($totRaw2 == 1) {
                            $totRaw = 13;
                        } else if ($totRaw2 == 2) {
                            $totRaw = 14;
                        } else if ($totRaw2 == 3) {
                            $totRaw = 15;
                        }
                    } else if ($getStimulus == 8) {
                        if ($totRaw2 == 1) {
                            $totRaw = 16;
                        } else if ($totRaw2 == 2) {
                            $totRaw = 17;
                        } else if ($totRaw2 == 3) {
                            $totRaw = 18;
                        }
                    } else if ($getStimulus == 9) {
                        if ($totRaw2 == 1) {
                            $totRaw = 19;
                        } else if ($totRaw2 == 2) {
                            $totRaw = 20;
                        } else if ($totRaw2 == 3) {
                            $totRaw = 21;
                        }
                    } else if ($getStimulus == 10) {
                        if ($totRaw2 == 1) {
                            $totRaw = 22;
                        } else if ($totRaw2 == 2) {
                            $totRaw = 23;
                        } else if ($totRaw2 == 3) {
                            $totRaw = 24;
                        }
                    }
                } else {
                    $totRaw = 0;
                }

            }

            // GET STANDARD SCORE
            $standarScore2 = normaScoreModel::select('STANDARD_SCORE')
                ->join('psy_norma_versions', 'psy_norma_versions.VERSION_ID', '=', 'psy_norma_score.VERSION_ID')
                ->join('psy_norma', 'psy_norma.NORMA_ID', '=', 'psy_norma_versions.NORMA_ID')
                ->where('psy_norma.CATEGORY_ID', $val['CATEGORY_ID'])
                ->where('RAW_SCORE', $totRaw)
                ->first();
            $standarScore = $standarScore2['STANDARD_SCORE'] . " ";

            if ($standarScore2) {
                $updateTestCategories = TestCategoriesModel::where('TEST_CATEGORY_ID', $val['TEST_CATEGORY_ID'])
                    ->update(['SUM_RAWSCORE' => $totRaw, 'STANDARD_SCORE' => $standarScore]);
            } else {
                $updateTestCategories = TestCategoriesModel::where('TEST_CATEGORY_ID', $val['TEST_CATEGORY_ID'])
                    ->update(['SUM_RAWSCORE' => $totRaw, 'STANDARD_SCORE' => 0]);
            }
        }


        return $this->JobResult($testCategoryId, $scheduleId);
    }

    public function JobResult($testCategoryId, $schedule_id)
    {
        $this->proceedTestResults($schedule_id);
        $candidate_id = $this->findCandidateIdFromScheduleId($schedule_id);
        $parameter = [
            'id_applicant' => $candidate_id,
        ];
        $parameter = Crypt::encrypt($parameter);
        return redirect()->action('StartController@finalGreeting', ['id' => $parameter]);
    }

    public function proceedTestResults($schedule_id)
    {
        $schedule_history = $this->findScheduleHistory($schedule_id);
        $job_mapping_version_id = $schedule_history['JOB_MAPPING_VERSION_ID'];
        $job_profiles = $this->findJobProfiles($job_mapping_version_id);

        $total_score = 0;
        $test_results = [];
        foreach ($job_profiles as $job_profile_seq => $job_profile) {
            $job_profile_scores = $this->findJobProfileScores($job_profile['JOB_PROFILE_ID']);
            $mandatory_count = 0;
            $achieved_mandatory_count = 0;
            $achieved_count = 0;

            foreach ($job_profile_scores as $job_profile_score_seq => $job_profile_score) {
                $test_categories = $this->findTestCategories($schedule_id, $job_profile_score['CATEGORY_ID']);

                if ($job_profile_seq < 1) {
                    if (count($test_categories) > 0) {
                        $total_score = $total_score + $test_categories[0]['STANDARD_SCORE'];
                    }
                }

                if (count($test_categories) > 0) {
                    if ($test_categories[0]['STANDARD_SCORE'] >= $job_profile_score['PASS_SCORE']) {
                        $achieved_count++;

                    }
                    if ($job_profile_score['MANDATORY'] == 1) {
                        $mandatory_count++;
                        if ($test_categories[0]['STANDARD_SCORE'] >= $job_profile_score['PASS_SCORE']) {
                            $achieved_mandatory_count++;
                        }
                    }
                }

            }

            if ($total_score >= $job_profiles[$job_profile_seq]['TOTAL_PASS_SCORE']) {
                $is_total_score_achieved = true;
            } else {
                $is_total_score_achieved = false;
            }

            $recommendation = $this->determineRecommendationBySystem($mandatory_count, $achieved_mandatory_count, $is_total_score_achieved);

            $schedule_history_id = $schedule_history['SCHEDULE_HISTORY_ID'];
            $test_result = [
                'SCHEDULE_HISTORY_ID' => $schedule_history_id,
                'SCHEDULE_ID' => $schedule_id,
                'JOB_ID' => $job_profiles[$job_profile_seq]['JOB_ID'],
                'ACHIEVE_TOTAL_SCORE' => $total_score,
                'IS_ACHIEVE_TOTAL_SCORE' => $is_total_score_achieved,
                'HAS_MANDATORY' => $mandatory_count > 0,
                'TOTAL_MANDATORY' => $mandatory_count,
                'TOTAL_ACHIEVE_MANDATORY' => $achieved_mandatory_count,
                'RECOMENDATION_BY_SYSTEM' => $recommendation
            ];
            TestResultModel::query()->insert($test_result);
            array_push($test_results, $test_result);

        }
        return $test_results;
    }

    public function finalGreeting($id)
    {
        $data = Crypt::decrypt($id);
        $id_applicant = $data['id_applicant'];
        $dt_applicant = ApplicantModel::find($id_applicant);
        $finalGreeting = NarrationsModel::select('NARRATION_TEXT')
            ->where('NARRATION_NAME', 'FINAL GREATING')
            ->first();
        session()->flush();
        DB::commit();
        return view('finalGreeting')
            ->with('dt_applicant', $dt_applicant)
            ->with('finalgret', $finalGreeting['NARRATION_TEXT']);
    }

    public function is_connected()
    {
        $connected = @fsockopen("www.example.com", 80);
        //website, port  (try 80 or 443)
        if ($connected) {
            $is_conn = true; //action when connected
            fclose($connected);
        } else {
            $is_conn = false; //action in connection failure
        }
        return $is_conn;

    }

    private function findScheduleHistory($schedule_id)
    {
        $dateNow = date('Y-m-d');
        $query = ScheduleHistoriesModel::query()
            ->select('JOB_MAPPING_VERSION_ID', 'SCHEDULE_HISTORY_ID')
            ->where('SCHEDULE_ID', $schedule_id);
        if (!env('APP_DEBUG')) {
            $query = $query
                ->where('TEST_STATUS', '=', 'COMPLETE')
                ->whereRaw('? between PLAN_START_DATE and PLAN_END_DATE', $dateNow)
                ->first();
        }
        return $query->first();
    }

    private function findJobProfiles($job_mapping_version_id)
    {
        return JobProfilesModel::query()
            ->select('JOB_PROFILE_ID', 'JOB_ID', 'TOTAL_PASS_SCORE')
            ->where('VERSION_ID', $job_mapping_version_id)
            ->get()
            ->toArray();
    }

    private function findJobProfileScores($job_profile_id)
    {
        return JobProfileScoreModel::query()
            ->select('CATEGORY_ID', 'PASS_SCORE', 'MANDATORY')
            ->where('JOB_PROFILE_ID', $job_profile_id)
            ->get()
            ->toArray();
    }

    private function findTestCategories($schedule_id, $category_id)
    {
        return TestCategoriesModel::query()
            ->select('SUM_RAWSCORE', 'STANDARD_SCORE')
            ->where('SCHEDULE_ID', $schedule_id)
            ->where('CATEGORY_ID', $category_id)
            ->get()
            ->toArray();
    }

    private function determineRecommendationBySystem($mandatory_count, $achieved_mandatory_count, $is_total_score_achieved)
    {
        $has_mandatory = $mandatory_count > 0;
        if ($has_mandatory && $achieved_mandatory_count >= $mandatory_count && $is_total_score_achieved) {
            return 'ABOVE_REQUIREMENT';
        } elseif (!$has_mandatory && $is_total_score_achieved) {
            return 'ABOVE_REQUIREMENT';
        } elseif ($has_mandatory && $achieved_mandatory_count >= 1 && $is_total_score_achieved) {
            return 'MEET_REQUIREMENT';
        } else {
            return 'BELOW_REQUIREMENT';
        }
    }

    private function findCandidateIdFromScheduleId($schedule_id)
    {
        return SchedulesModel::query()
            ->select('CANDIDATE_ID')
            ->where('SCHEDULE_ID', $schedule_id)
            ->first()->CANDIDATE_ID;
    }

}
