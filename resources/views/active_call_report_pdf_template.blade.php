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
  <table id="example" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th></th>
                <th>Interpreter</th>
                <th>Caller</th>
                <th>Language</th>
                <th>Purpose</th>
                <th>Date</th>
                <th>Call Duration</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $user_img =url('/uploads/user_profile/user.png');
            $interpreter_name = $caller_name = $language = $purpose = $status = $start_time = $location = '';
              
            foreach ($data['call_datas'] as $key => $row_data) { 
                if (isset($row_data['call_details']) && !empty($row_data['call_details'])) {   
                    $start_time = (isset($row_data['call_detail']['start_time'])?$row_data['call_detail']['start_time']:'');
                    $duration = (isset($row_data['call_detail']['duration'])?$row_data['call_detail']['duration']:'');
                    $first_name = (isset($row_data['call_detail']['user_profile']['first_name'])?$row_data['call_detail']['user_profile']['first_name']:'');
                    $last_name = (isset($row_data['call_detail']['user_profile']['last_name'])?$row_data['call_detail']['user_profile']['last_name']:'');
                    
                    $interpreter_name = $first_name." ".$last_name;
                    if(empty($interpreter_name)){
                      $interpreter_name = 'No Interpreter Found';  
                    }

                    if(isset($row_data['call_detail']['user_profile']['profile_photo']) && !empty($row_data['call_detail']['user_profile']['profile_photo'] && $row_data['call_detail']['user_profile']['profile_photo'] != '//default.png')){
                      $user_img =url($row_data['call_detail']['user_profile']['profile_photo']);
                    }else{
                      $user_img =url('/uploads/users/default.png');
                    }

                    
                }
                $caller_first_name = (isset($row_data['from_user_profile']['first_name'])?$row_data['from_user_profile']['first_name']:'');
                $caller_last_name = (isset($row_data['from_user_profile']['first_namelast_name'])?$row_data['from_user_profile']['last_name']:'');
                $caller_name = $caller_first_name .' '. $caller_last_name;
                $language = (isset($row_data['language']['name'])?$row_data['language']['name']:'');
                $purpose = (isset($row_data['purpose']['description'])?$row_data['purpose']['description']:'');
              ?>
            <tr>
                <td><img src=<?php echo $user_img ?> width="20px" height="20px"></td>
                <td><?php echo $interpreter_name; ?></td>
                <td><?php echo $caller_name; ?></td>
                <td><?php echo $language; ?></td>
                <td><?php echo $purpose; ?></td>
                <td><?php echo $start_time; ?></td>
                <td><?php echo $duration; ?></td>
            </tr>
          <?php }  ?>
            
        </tbody>
    </table>
</div>

</body>
</html>