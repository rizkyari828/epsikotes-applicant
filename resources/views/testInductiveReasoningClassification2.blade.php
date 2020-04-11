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
                                <span class="badge badge-light">CONTOH SOAL </span>
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
                        <div class="question_title"><br>
                            <div class="row">
                                <div class="col-md-10 col-center">
                                    <?php $img = $queList[$currentSoal]['QUESTION_IMG']; ?>
                                    <img src="{{URL::asset("images/QUESTION_MANAGEMENT/{$img}")}}" class="mr-3" alt="{{$img}}" width="80%">
                                </div>
                            </div><br>
                            <div class="alert alert-dismissible alert-secondary">
                                <p>{{$queList[$currentSoal]['QUESTION_TEXT']}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2"></div>
                            <div class="col-lg-4">
                                <div class="item">
                                    <label for="answer_1" style="padding: 20px 0;">
                                        <strong>POLA 3 GAMBAR</strong>
                                        <input type="text" name="choice" id="answer_1" class="txt-field-answer-number" autocomplete="off" placeholder="..." pattern="[a-zA-Z]{1,}" maxlength="3" title="Isi dengan huruf"  onblur="checkAnswer()">
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="item">
                                    <label for="answer_2" style="padding: 20px 0;">
                                        <strong>POLA 2 GAMBAR</strong>
                                        <input type="text" name="choice2" id="answer_2" class="txt-field-answer-number" autocomplete="off" placeholder="..." pattern="[a-zA-Z]{1,}" maxlength="2" title="Isi dengan huruf" onblur="checkAnswer()">
                                    </label>
                                </div>
                            </div>
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
                                        <img src="{{URL::asset("images/QUESTION_MANAGEMENT/{$imgs}")}}" class="col-center" alt="{{$imgs}}" width="150px">
                                    </td>
                                </tr>
                        <?php }else{  ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <h4>{!!$txt!!}</h4>
                                        <img src="{{URL::asset("images/QUESTION_MANAGEMENT/{$imgs}")}}" class="col-center" alt="{{$imgs}}" width="150px">
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
                    document.getElementById("timer").innerHTML = waktu;
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

        function checkAnswer() {
            var ans1 = document.getElementById('answer_1').value.toUpperCase();
            var ans2 = document.getElementById('answer_2').value.toUpperCase();
            var chars1 = ans1.split('');
            var chars2 = ans2.split('');
            var chars = chars1.concat(chars2);
            var check = true;
            for(var i = 0; i <= chars.length; i++) {
                for(var j = i; j <= chars.length; j++) {
                    if(i != j && chars[i] == chars[j]) {
                        check = false;
                    }
                }
            }
            if(!check){
                alert('Jawaban tidak boleh sama !');
                document.getElementById('answer_1').value = '';
                document.getElementById('answer_2').value = '';
            }
        }

        // function enterText(){

        //     var f = document.getElementById('answer_1');
        //     if (!/^[A-Za-z]+$/.test(f.value)) 
        //     { 
        //         alert("Input dengan text");
        //         f.value = f.value.substring(0,f.value.length-1);
        //     }
        // }

        // function enterText2(){

        //     var f = document.getElementById('answer_2');
        //     if (!/^[A-Za-z]+$/.test(f.value)) 
        //     { 
        //         alert("Input dengan text");
        //         f.value = f.value.substring(0,f.value.length-1);
        //     }
        // }
    </script>
@endsection