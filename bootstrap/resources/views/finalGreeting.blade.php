@extends('layouts.app')
@section('content')
    <div class="col-md-6 col-md-offset-2 col-center">
        <div class="card border-primary mb-3">
            <div class="card-header">
                <div class="media">
                  <img src="{{ asset('images/logo.png') }}" class="mr-3 img-thumbnail" alt="head" width="20%">
                  <div class="media-body">
                    <h6 class="mt-0"></h6>
                    <h4>ONLINE PSYCHOTEST</h4>
                  </div>
                </div>
            </div>
            <div class="card-body">
                <div class="panel panel-default">
                    <div class="row">
                        <div class="col-md-12 col-center">
                            <center>
                                <img src="{{ asset('images/congrats.png') }}" class="mr-3" alt="Congratulations" width="70%">
                                <p>
                                    Anda telah menyelesaikan tes ini<br>
                                    Terima Kasih <b class="text-uppercase">{{$dt_applicant->FULL_NAME}}</b> sudah berpartisipasi.
                                </p>
                            </center>
                            <div class="card">
                              <div class="card-body">
                                 {!!$finalgret!!}
                              </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection