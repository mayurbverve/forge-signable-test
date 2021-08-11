<!DOCTYPE html>
<html lang="en">
<head>
  <title>Call History</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
  <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
  <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script> -->

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.0/css/buttons.bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
  <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.print.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.colVis.min.js"></script>
</head>
<style>
div.ex1 {
  width: 20%;
  height: 100px;
  border: 3px solid #f1f1f1;
  margin-bottom: 20px;
  display: inline-block;
}
div.ex2 {
  width: 20%;
  height: 100px;
  border: 3px solid #f1f1f1;
  margin-bottom: 20px;
  margin-left: 20px;
  display: inline-block;
}
.container {
  margin-bottom: 5%;
}
</style>

<body>
<div class="container">
  <h2><b>Call History</b></h2>
  <div class="ex1">
    <label>Your Calls</label>
    <br>
    {{$call_details['average_call_details']['total_calls']}}
  </div>
  <div class="ex2">
    <label>Avg Call Duration</label>
    <br>
    <?php 
    if($call_details['average_call_details']['average_times'] == '0:0:0'){
      $hour = 0;  
      $min = 0;  
      $sec = 0;  
    }else{
      $hour = date('h', strtotime($call_details['average_call_details']['average_times']));
      $min = date('i', strtotime($call_details['average_call_details']['average_times']));
      $sec = date('s', strtotime($call_details['average_call_details']['average_times']));
    }
    ?>
    {{$hour}}h: {{$min}}m: {{$sec}}s
  </div>
  <div class="ex2">
    <label>preferred Call Language</label>
    <br>
    {{$call_details['average_call_details']['average_language']['name']}}
  </div>
  <div class="ex2">
    <label>Popular Call Purpose</label>
    <br>
    {{$call_details['average_call_details']['average_language']['name']}}
  </div>
  <h4><b>Call List</b></h4>
  <table id="example" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th></th>
                <th> Interpreter</th>
                <th>Language</th>
                <th>Purpose</th>
                <th>Duration</th>
                <th>Date</th>
                <th>Feedback</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $star_img =url('/uploads/feedback_star.png');
            $user_img =url('/uploads/user_profile/user.png');
            foreach ($call_report_data as $key => $value) { 
                
                 $start_time1 = "";
                $start_time = "";
                $end_time = "";
                $feedback = "";
                $diff = "";
                $interpreter_name  ="";
                
                if (isset($value['call_details'][0])) {


                    $start_time1 = $value['call_details'][0]['start_time'];
                    $start_time = date('d M', strtotime($value['call_details'][0]['start_time']));
                    
                    $end_time = $value['call_details'][0]['end_time'];
                    $feedback = $value['call_details'][0]['feedback'];
                    $diff = strtotime($end_time) - strtotime($start_time1);

                    $hours = floor($diff / 3600);
                    $mins = floor(($diff - $hours * 3600) / 60);
                    $s = $diff - ($hours * 3600 + $mins * 60);
                    $duration = $hours . ":" . $mins . ":" . $s;   

                    $interpreter_name = $value['call_details'][0]['user_profile']['first_name']." ".$value['call_details'][0]['user_profile']['last_name'];

                    
                } else {
                    $start_time = $end_time = $duration ='-';
                    $interpreter_name = 'No Interpreter Found';
                }
              ?>
            <tr>
                <td><img src={{$user_img}} width="20px" height="20px"></td>
                <td>{{$interpreter_name}}</td>
                <td>{{$value['language']['name']}}</td>
                <td>{{$value['purpose']['description']}}</td>
                <td>{{$duration}}</td>
                <td>{{$start_time}}</td>
                <td>
                    {{(isset($feedback)&& !empty($feedback))?$feedback:'pending'}} 
                    <?php if(!empty($feedback)){?><img src={{$star_img}} width="15px" height="15px"> <?php }?>
                  </td>
                
            </tr>
          <?php } ?>
            
        </tbody>
        <tfoot>
            <tr>
                <th></th>
                <th> Interpreter</th>
                <th>Language</th>
                <th>Purpose</th>
                <th>Duration</th>
                <th>Date</th>
                <th>Feedback</th>
            </tr>
        </tfoot>
    </table>
</div>

</body>
</html>
<script type="text/javascript">
$(document).ready(function() {
    var table = $('#example').DataTable( {
        lengthChange: false,
        buttons: [ 'copy', 'csv', 'excel', 'pdf', 'colvis' ]
    } );
 
    table.buttons().container()
        .appendTo( '#example_wrapper .col-sm-6:eq(0)' );
} );
</script>