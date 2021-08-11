<?php use App\Models\CallStatus; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Active Call Report</title>
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
  <h2><b>Active Calls</b></h2>
  <div class="ex1">
    <label>Active Calls</label>
    <br>
    <?php echo $data['average_detail']['total_calls']; ?>
  </div>
  <div class="ex2">
    <label>Available Interpreter</label>
    <br>
    <?php echo $data['average_detail']['total_available_interpreter']; ?>
  </div>
  <div class="ex2">
    <label>Avg Call wait Time</label>
    <br>
    <?php echo $data['average_detail']['avg_call_wait_time']; ?>
  </div>
  <div class="ex2">
    <label>Most Active location</label>
    <br>
    <?php echo $data['average_detail']['most_active_location']; ?>
  </div>
  <h4><b>Call Participated In</b></h4>
  <table id="example" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th></th>
                <th>Interpreter</th>
                <th>Caller</th>
                <th>Language</th>
                <th>Purpose</th>
                <th>Location</th>
                <th>Start Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $user_img =url('/uploads/user_profile/user.png');
            $interpreter_name = $caller_name = $language = $purpose = $status = $start_time = $location = '';
              
            foreach ($data['call_datas'] as $key => $value) { 
                  //echo "<pre>"; print_r($value['call_details']);exit();
                if($value['call_details']->isEmpty()){
                  $interpreter_name = '-';
                  $start_time = '-';
                }else{
                  $interpreter_name = $value['call_details'][0]['user_profile']['first_name']." ".$value['call_details'][0]['user_profile']['last_name'];
                  if(isset($value['call_details'][0]['start_time']) && !empty($value['call_details'][0]['start_time'])){
                    $start_time = date('h:i:s', strtotime($value['call_details'][0]['start_time']));
                  }else{
                    $start_time = '0:0:0';
                  }
                }
                  $caller_name = $value['from_user_profile']['first_name'] .' '. $value['from_user_profile']['last_name'];
                  $language = $value['language']['name'];
                  $purpose = $value['purpose']['description'];
                  $location = $value['from_user_profile']['locations']['miles'];
                  
                  $status = CallStatus::where('id',$value['status'])->pluck('value')->first();
                  //$status = $value['status']['value'];
              ?>
            <tr>
                <td><img src=<?php echo $user_img ?> width="20px" height="20px"></td>
                <td><?php echo $interpreter_name; ?></td>
                <td><?php echo $caller_name; ?></td>
                <td><?php echo $language; ?></td>
                <td><?php echo $purpose; ?></td>
                <td><?php echo $location; ?></td>
                <td><?php echo $start_time; ?></td>
                <td><?php echo $status; ?></td>
            </tr>
          <?php }  ?>
            
        </tbody>
        <tfoot>
            <tr>
                <th></th>
                <th>Interpreter</th>
                <th>Caller</th>
                <th>Language</th>
                <th>Purpose</th>
                <th>Location</th>
                <th>Start Time</th>
                <th>Status</th>
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