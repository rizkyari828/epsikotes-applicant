@extends('layouts.app')
@section('content')
    <div class="col-md-6 col-md-offset-2 col-center">
        <div class="card border-primary mb-3">
            <div class="card-header">
                <div class="media">
                  <img src="{{ asset('images/logo.png') }}" class="mr-3 img-thumbnail" alt="head" width="18%">
                  <div class="media-body">
                    <h6 class="mt-0">Selamat Datang di</h6>
                    <h5>ONLINE PSYCHOTEST</h5>
                  </div>
                </div>
            </div>
            <div class="card-body">
                <div class="panel panel-default">
                    <h5>Informasi Diri</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-borderless">
                              <tbody>
                                <tr>
                                  <td scope="row" class="text-align-right">Nama Pelamar</td>
                                  <td class="text-uppercase">{{$dt_applicant->FULL_NAME}}</td>
                                </tr>
                                <tr>
                                  <td scope="row" class="text-align-right">User Name</td>
                                  <td>{{$dt_applicant->USER_NAME}}</td>
                                </tr>
                                <tr>
                                  <td scope="row" class="text-align-right">Tanggal Lahir</td>
                                  <td>{{$dt_applicant->BIRTH_DATE}}</td>
                                </tr>
                                <tr>
                                  <td scope="row" class="text-align-right">KTP</td>
                                  <td>{{$dt_applicant->KTP}}</td>
                                </tr>
                                <tr>
                                  <td scope="row" class="text-align-right">Nomor Telpon/ HP</td>
                                  <td>{{$dt_applicant->PHONE_NUMBER}}</td>
                                </tr>
                              </tbody>
                            </table>
                        </div>        
                    </div>
                    <h5>Instruksi Umum</h5>
                    <div class="row">
                        <div class="col-md-12 col-center">
                            <div class="card">
                              <div class="card-body card-text" style="text-align: left;">
                                <p>
                                  {!!$generalInstruction->NARRATION_TEXT!!}
                                </p>
                              </div>
                            </div>
                        </div>
                    </div>
                </div><br>
                <?php
                  $parameter =[
                                  'id' => $scheduleHistoryId,
                              ];
                  $parameter= Crypt::encrypt($parameter);
                ?>
                <a href="{{ url('/subtestInstruction/'.$parameter.'/') }}"> <button type="button" class="btn btn-outline-primary float-right">Mulai E-Psychotest</button> </a>
            </div>
        </div>
    </div>
@endsection