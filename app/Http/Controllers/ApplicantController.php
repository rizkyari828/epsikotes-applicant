<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\ApplicantModel;

class ApplicantController extends Controller
{
    //
    public function applicant($id_applicant){
        // $id_applicant = 41744;
        // $dt_applicant = ApplicantModel::where('applicant_id',$id_applicant)->first();
        $dt_applicant = ApplicantModel::find($id_applicant);
        return view('introduction', compact('dt_applicant'));
    }
}
