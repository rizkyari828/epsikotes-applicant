<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/SessionExpired', function () {
//     // return view('pages.landing-pages');
    return view('errorPage')
    	->with('pesan','Anda harus Login dari gawe untuk mengakses halaman ini');
});
Route::get('/', function () {
//     // return view('pages.landing-pages');
    return view('errorPage')
    	->with('pesan','Anda harus Login dari gawe untuk mengakses halaman ini');
});

Auth::routes();

// Route::get('/', 'HomeController@index')->name('index');
// Route::get('/', 'HomeController@index')->name('index');

// Route::get('/introduction', function () {
//     return view('introduction');
// });

// Route::get('/subtestInstruction/{id}', function () {
//     return view('subtestInstruction');
// });

// Route::get('/finalGreeting', function () {
//     return view('finalGreeting');
// });

// Route::get('/inductiveReasoning', function () {
//     return view('testInductiveReasoning');
// });

// Route::get('/inductiveReasoning2', function () {
//     return view('testInductiveReasoning2');
// });

// Route::get('/inductiveReasoningClassification', function () {
//     return view('testInductiveReasoning3');
// });

// Route::get('/inductiveReasoningClassification2', function () {
//     return view('testInductiveReasoning4');
// });

// Route::get('/deductiveReasoning', function () {
//     return view('testDeductiveReasoning');
// });

// Route::get('/readingComprehension', function () {
//     return view('testReadingComprehension');
// });

// Route::get('/arithmeticAbility', function () {
//     return view('testArithmeticAbility');
// });

// Route::get('/spatialAbility', function () {
//     return view('testSpatialAbility');
// });

// Route::get('/memory', function () {
//     return view('testMemory');
// });

Route::get('introduction/{id}', 'ApplicantController@applicant')->where('id', '[0-9]+');

// Route::get('/error', function(){

//     $dt = \App\scheduleHistoriesModel::find(1);
//     $npk = $dt->Schedules->Applicant->APPLICANT_ID;
//     if($npk != 41744)
// 	    return view('errorPage', compact('dt'));
// 	else{
// 		$dt_applicant = \App\ApplicantModel::find(1);
// 		return view('introduction', compact('dt_applicant'));
// 	}
// });

Route::get('auth/{id}', 'LoginController@checkSchedule');

Route::get('subtestInstruction/{id}', 'StartController@startEpsikotest');

Route::get('subtest/{id}', 'StartController@getSubTest');

// Route::post('getchoice/{cur}/{jml}/{cat}/{queid}/{schid}/{testCatId}/{jmlExample}', 'StartController@saveChoicesSession');
//Route::post('getchoice', ['as' => 'search', 'uses' => 'StartController@saveChoicesSession']);
Route::match(['get', 'post'],'getchoice', ['as' => 'search', 'uses' => 'StartController@saveChoicesSession']);

// Route::get('startsubtest/{categoryId}/{schId}/{testCategoryId}', 'StartController@startTest');
Route::get('startsubtest/{id}', 'StartController@startTest');

Route::get('scoring/{id}', 'ScoringController@getCategory');

Route::get('finalGreeting/{id}', 'StartController@finalGreeting');

