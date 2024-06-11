@extends('layouts.app')
@section('content')
    <div class="col-md-10 col-md-offset-2 col-center">
        @if($status == 0)
          <div class="alert alert-dismissible alert-warning" id="notif">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <center>
                Koneksi anda lambat<br>
                Mohon cek koneksi anda
            </center>
          </div>
        @endif
        <div class="card border-primary mb-3">
            <div class="card-body">
                <?php
                  $parameter =[
                                  'currentSoal' => $nextSoal,
                                  'jmlSoal' => $jmlSoal,
                                  'scheduleId' => $schId,
                                  'categoryId' => $categoryId,
                                  'testQueId' => $queList[$currentSoal]['TEST_QUESTION_ID'],
                                  'testCategoryId' => $testCatId,
                                  'jmlExample' => $jmlExample,
                              ];
                  $parameter= Crypt::encrypt($parameter);
                ?>

                {{ Form::open(array('method' => 'post', 'role' => 'form', 'id' => 'form-soal', 'action' => array(
                    'StartController@saveChoicesSession')) ) }}
                <input type="hidden" name="currentSoal" value="{{$nextSoal}}">
                <input type="hidden" name="jmlSoal" value="{{$jmlSoal}}">
                <input type="hidden" name="scheduleId" value="{{$schId}}">
                <input type="hidden" name="categoryId" value="{{$categoryId}}">
                <input type="hidden" name="testQueId" value="{{$queList[$currentSoal]['TEST_QUESTION_ID']}}">
                <input type="hidden" name="testCategoryId" value="{{$testCatId}}">
                <input type="hidden" name="jmlExample" value="{{$jmlExample}}">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="time-left">
                            @if($queList[$currentSoal]['EXAMPLE'] != 1)
                               <!--  <span class="badge badge-secondary">
                                    {{$queList[$currentSoal]['QUESTION_SEQ'] - $jmlExample}} / {{$jmlSoal - $jmlExample}}
                                </span> -->
                                <span class="badge badge-light" id="timer"></span> 
                                <input type="submit" name="next" class="btn btn-primary float-right" align="right" value="Selanjutnya" onclick="submitForm()"> 
                            @else
                                <span class="badge badge-light">CONTOH SOAL</span>
                                @if($queList[$currentSoal+1]['EXAMPLE'] != 1)
                                    <input type="button" name="next" class="btn btn-primary float-right" align="right" value="Mulai Soal" id="submitBtn" data-toggle="modal" data-target="#confirm-submit">
                                @else
                                    <input type="button" name="next" class="btn btn-primary float-right" align="right" value="Selanjutnya" id="submitBtn" data-toggle="modal" data-target="#confirm-submit">
                                @endif
                            @endif 
                            <!-- {!! Form::submit('Selanjutnya', ['class' => 'btn btn-primary float-right', 'align' => 'right']) !!} -->
                        </h3>  
                    </div>
                </div>
                <div id="middle-wizard">
                    <!-- First branch What Type of Project ============================== --> 
                    <?php echo  "<b>Soal No " .  $nextSoal . "</b>" ;  ?>

                    <div class="step" data-state="branchtype">
                        <div class="question_title">
                            <div class="row ">
                              <div class="col-md-10 col-center card-text">
                                <p>
                                    {!!$queList[$currentSoal][0]!!}
                                </p>
                              </div>
                            </div><br>
                            <div class="alert alert-dismissible alert-secondary">
                                <p>{!!$queList[$currentSoal]['QUESTION_TEXT']!!}</p>
                            </div>
                        </div>
                        <div class="row">
                            <?php
                                $queid = $queList[$currentSoal]['QUESTION_ID'];
                                $listAns = $ansList[$queid];
                            ?>
                            @foreach($listAns as $listChoices => $val)
                                <div class="col-lg-6">
                                    <div class="item">
                                        {{Form::radio('choice', $val['ANS_CHOICE_ID'], false, ['id' => $listChoices, 'class' => 'required'])}}
                                        <label for="{{$listChoices}}">
                                            <strong>{{$alphas[$listChoices]}}</strong>
                                            <p>{{$val['CHOICE_TEXT']}}</p>
                                        </label>
                                    </div>
                                </div>
                            @endforeach()
                        </div>
                        <!-- /row-->
                    </div>
                </div>

                    {!! Form::hidden('parameter', $parameter) !!}
                    {!! Form::hidden('data', json_encode($queList)) !!}
                    {!! Form::hidden('dataAns', json_encode($ansList)) !!}
                {{ Form::close()}}
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirm-submit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    Kunci Jawaban
                </div>
                <div class="modal-body">
                    <table class="table">
                        <?php $imgs = $queList[$currentSoal]['HINT_IMG']; 
                                $txt = $queList[$currentSoal]['HINT_TEXT'];
                            if($txt != '' && $imgs == ''){ ?>
                                 <tr>
                                    <td style="text-align: center;">
                                        <h4>{{$txt}}</h4>
                                    </td>
                                </tr>
                        <?php        
                            }else if($txt == '' && $imgs != ''){ ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <img src="{{ config('app.QUESTION_PATH').$imgs}}" class="col-center" alt="{{$imgs}}" width="150px">
                                    </td>
                                </tr>
                        <?php }else{  ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <h4>{!!$txt!!}</h4>
                                        <img src="{{ config('app.QUESTION_PATH').$imgs}}" class="col-center" alt="{{$imgs}}" width="150px">
                                    </td>
                                </tr>
                        <?php } 
                        ?>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <input type="submit" name="submitExample" id="submit" class="btn btn-success success" value="OK">
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('/js/jquery-3.3.1.min.js') }}"></script>
    <script type="text/javascript">
        
        var example = {!!$queList[$currentSoal]['EXAMPLE']!!};
        var curSoal = {!!$currentSoal!!};
        var status = {!!$status!!};
        window.onload=function(){
            if(example != 1){
                var check = localStorage.getItem("startTime");
                
                if(!check || check < 1){
                    var waktu = {!!$queList[$currentSoal]['DURATION_PER_QUE']!!} + 1;
                    localStorage.setItem("startTime", waktu);
                }else{
                    var waktu = check;
                }
                setInterval(function() {
                    if(status == 1){
                        waktu--;
                    }
                if(waktu < 0) {
                    document.getElementById("form-soal").submit();
                }else{
                    if(waktu <= 10){
                        document.getElementById("timer").className = "badge";
                        document.getElementById("timer").className += " badge-danger";
                    }
                    document.getElementById("timer").innerHTML =  "SISA WAKTU "+waktu+" DETIK";
                    localStorage.setItem("startTime", waktu);
                }
                }, 1000);
            }
        }
        function submitForm(){ 
          // Call submit() method on <form id='myform'>
          document.getElementById('form-soal').submit(); 
        } 
        $(document).ready(function() {
            $('#submit').click(function (e) {
                e.preventDefault();
                document.getElementById("form-soal").submit();
            });
        });
    </script>
@endsection
