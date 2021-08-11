<!DOCTYPE html>
<html lang="en">
<head>
  <title>Interpreter User Report</title>
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
  <h2><b>Interpreter User Report</b></h2>
  <table id="example" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th></th>
                <th>Interpreter</th>
                <th>Language</th>
                <th>Calls</th>
                <th>Avg ratings</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $user_img =url('/uploads/users/default.png');
            foreach ($data['data'] as $key => $row_data) { 
                if (isset($row_data['user_profile']) && !empty($row_data['user_profile'])) {   
                    $first_name = (isset($row_data['user_profile']['first_name'])?$row_data['user_profile']['first_name']:'');
                    $last_name = (isset($row_data['user_profile']['last_name'])?$row_data['user_profile']['last_name']:'');
                    $avg_user_rating = (isset($row_data['user_profile']['avg_user_rating'])?$row_data['user_profile']['avg_user_rating']:'');
                    
                    $interpreter_name = $first_name." ".$last_name;
                    if(empty($interpreter_name)){
                      $interpreter_name = 'No Interpreter Found';  
                    }

                    if(!empty($row_data['user_profile']['profile_photo'] && $row_data['user_profile']['profile_photo'] != '//default.png')){
                      $user_img =url($row_data['user_profile']['profile_photo']);
                    }else{
                      $user_img =url('/uploads/users/default.png');
                    }
                }
                $interpreter_language = (isset($row_data['interpreter_language'])?$row_data['interpreter_language']:'');
                $calls_count = (isset($row_data['calls_count'])?$row_data['calls_count']:'');
                $status = (isset($row_data['status'])?$row_data['status']:'');


              ?>
            <tr>
                <td><img src=<?php echo $user_img ?> width="20px" height="20px"></td>
                <td><?php echo $interpreter_name; ?></td>
                <td><?php echo $interpreter_language; ?></td>
                <td><?php echo $calls_count; ?></td>
                <td><?php echo $avg_user_rating; ?></td>
                <td><?php echo $status; ?></td>
            </tr>
          <?php }  ?>
            
        </tbody>
    </table>
</div>

</body>
</html>