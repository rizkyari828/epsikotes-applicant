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
                                    <input type="button" name="next" class="btn btn-primary float-right" align="right" value="Selanjutnya 2" id="submitBtn" data-toggle="modal" data-target="#confirm-submit">
                                @endif
                            @endif
                            <!-- {!! Form::submit('Selanjutnya', ['class' => 'btn btn-primary float-right', 'align' => 'right']) !!} -->
                        </h3>
                    </div>
                </div>
                <div id="middle-wizard" style="padding: 5px;">
                    <!-- First branch What Type of Project ============================== -->
                    <?php echo  "<b>Soal No " .  $nextSoal . "</b>" ;  ?>
                    <div class="step" data-state="branchtype">
                        <div class="question_title">
                            <div class="row">
                                <div class="col-md-10 col-center">
                                    <?php $img = $queList[$currentSoal]['QUESTION_IMG']; ?>
                                    <img src="{{ config('app.QUESTION_PATH').$img}}" class="mr-3" alt="{{$img}}" width="60%">
                                </div>
                            </div><br>
                            <div class="alert alert-dismissible alert-secondary">
                                <p></p>
                            </div>
                        </div>
                        <div class="row" style="margin-top: -20px;">
                            <?php
                                $queid = $queList[$currentSoal]['QUESTION_ID'];
                                $listAns = $ansList[$queid];
                            ?>
                         
                            @foreach($listAns as $listChoices => $val)
                                <div class="col-sm-12 col-md">
                                    <div class="item">
                                        {{Form::radio('choice', $val['ANS_CHOICE_ID'], false, ['id' => $listChoices, 'class' => 'required'])}}
                                        <label for="{{$listChoices}}">
                                            <strong>{{$alphas[$listChoices]}}</strong>
                                            <?php if( $val['CHOICE_IMG']== "") {
                                                echo $val['CHOICE_TEXT'];
                                             } 
                                             else{?>
                                            <?php $imgs = $val['CHOICE_IMG'];  ?>
                                            <img src="{{ config('app.ANSWER_PATH').$imgs}}" class="col-center" alt="{{$imgs}}" width="40%">
                                             <?php }?>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div><br>
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
                            if($txt != '' && $imgs == ''){ echo "1"; ?>
                                 <tr>
                                    <td style="text-align: center;">
                                        <h4>{{$txt}}</h4>
                                    </td>
                                </tr>
                        <?php
                            }else if($txt == '' && $imgs != ''){ echo "2"; ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <img src="{{ config('app.QUESTION_PATH').$imgs}}" class="col-center" alt="{{$imgs}}" width="150px">
                                    </td>
                                </tr>
                        <?php }else{  echo "3";?>
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
        // function enterText(){

        //     var f = document.getElementById('answer_1');
        //     if (!/^[A-Za-z]+$/.test(f.value))
        //     {
        //         alert("Input dengan text");
        //         f.value = f.value.substring(0,f.value.length-1);
        //     }
        // }
    </script>
@endsection
