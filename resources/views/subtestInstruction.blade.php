@extends('layouts.app')
@section('content')
    <div class="col-md-8 col-md-offset-2 col-center">
        @isset($pesan)
          <div class="alert alert-dismissible alert-info" id="notif">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <center>Jawaban anda berhasil disimpan.</center>
          </div>
        @endisset
        <div class="card border-primary mb-3">
            <div class="card-body">
                <h4>Instruksi Subtes {{$testcatSeq}}</h4>
                <div class="row">
                    <div class="col-md-12 col-center">
                        <div class="card">
                          <div class="card-body">
                             {!!$ins!!}
                          </div>
                        </div>
                        <br>
                        <?php
                          $parameter =[
                                          'categoryId' => $catId,
                                          'scheduleId' => $schId,
                                          'testCategoryId' => $testcatId,
                                      ];
                          $parameter= Crypt::encrypt($parameter);
                        ?>
                        <a href="{{ url('/startsubtest/'.$parameter.'/') }}">
                            <button type="button" class="btn btn-outline-primary float-right" align="right">
                                Mulai Tes
                            </button>
                        </a>                    
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
      setTimeout(function() {
          $('#notif').fadeOut('slow');
      }, 3000);

    </script>
@endsection