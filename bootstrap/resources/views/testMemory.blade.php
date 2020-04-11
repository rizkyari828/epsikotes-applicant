@extends('layouts.app')
@section('content')
    <div class="col-md-10 col-md-offset-2 col-center">
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
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="time-left">
                            @if($queList[$currentSoal]['EXAMPLE'] != 1)
                                <!-- <span class="badge badge-secondary">
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
                <div id="middle-wizard" style="padding: 5px;">
                    <!-- First branch What Type of Project ============================== -->
                    <div class="step" data-state="branchtype">
                        <div class="question_title">
                            <div class="row">
                                <div class="col-md-6 col-center" id="cont-anim">
                                    <?php 
                                        $soal = str_split($queList[$currentSoal][0]); 
                                        $jmlNumber = $queList[$currentSoal]['QUESTION_CHARACTER'];
                                    ?>
                                    @foreach($soal as $que)
                                        <div class="anim-number">{{$que}}</div>
                                    @endforeach()
                                </div>
                            </div><br>
                            <div class="alert alert-dismissible alert-secondary" style="margin-top: 130px;">
                                <p id="soal">{{$queList[$currentSoal]['HINT_TEXT']}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2"></div>
                            <div class="col-lg-8">
                                <div class="item" id="jawab">
                                    <label for="answer_1" style="padding: 20px 0;">
                                        <input type="number" name="choice" id="answer_1" autocomplete="off" class="txt-field-answer-number" maxlength="{{$jmlNumber}}" placeholder="..." title="Isi dengan angka" style="border: none;" required>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <!-- /row-->
                    </div>
                </div>
                

                    {!! Form::hidden('data', json_encode($queList)) !!}
                    {!! Form::hidden('dataAns', json_encode($ansList)) !!}
                    {!! Form::hidden('parameter', $parameter) !!}
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
                        <tr>
                            <td><h4 align="center">{!!$queList[$currentSoal][0]!!}</h4></td>
                        </tr>
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
        
        // ANIMATION NUMBER MEMORY TEST
      $("#cont-anim > div:gt(0)").hide();
      $("#soal").hide();
      $("#jawab").hide();
      var jml = {!!$jmlNumber!!};
      var count = 0;
      var itv = setInterval(change,  1500);
      function change() { 
        $('#cont-anim > div:first')
          .fadeOut(200)
          .next()
          .fadeIn(600)
          .end()
          .appendTo('#cont-anim');
        count++;
        if(count == jml){
            clearInterval(itv);
            $("#cont-anim").fadeOut("fast");
            $("#soal").fadeIn("slow");
            $("#jawab").fadeIn("slow");
            document.getElementById("answer_1").focus();
        }
      }

        
        var example = {!!$queList[$currentSoal]['EXAMPLE']!!};
        var curSoal = {!!$currentSoal!!};
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
                waktu--;
                if(waktu < 0) {
                    document.getElementById("form-soal").submit();
                }else{
                    if(waktu <= 10){
                        document.getElementById("timer").className = "badge";
                        document.getElementById("timer").className += " badge-danger";
                    }
                    document.getElementById("timer").innerHTML = waktu;
                    localStorage.setItem("startTime", waktu);
                }
                }, 1000);
            }
        }

        // function enterNumber(){

        //     var e = document.getElementById('answer_1');
        //       if (!/^[0-9]+$/.test(e.value)) 
        //     { 
        //     alert("Input dengan angka");
        //     e.value = e.value.substring(0,e.value.length-1);
        //     }
        // }  
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