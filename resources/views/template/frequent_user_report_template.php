<!DOCTYPE html>
<html lang="en">
<head>
  <title>Frequent Users Report</title>
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
  <h4><b>Frequent Users</b></h4>
   <a href="<?php echo url('report/frequent_user_report_history_export') ?>">Export</a>
  <table id="example" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>phone</th>
                <th>gender</th>
                <th>date_of_join</th>
                <th>date_of_birth</th>
                <th>Company Name</th>
                <th>Total Calls</th>
            </tr>
        </thead>
        <tbody>
            <?php 
              
            $star_img =url('/uploads/feedback_star.png');
            $user_img =url('/uploads/user_profile/user.png');
            $full_name = $email = $phone= $date_of_join= $date_of_birth= $gender = '';
            foreach ($user_profiles_data as $key => $value) { 

                $full_name = $value['user_profile']['first_name'] .' '. $value['user_profile']['last_name'];
                $email = $value['email'];
                $phone = $value['phone'];
                $date_of_join = $value['user_profile']['date_of_join'];
                $date_of_birth = $value['user_profile']['date_of_birth'];
                $company_name = $value['user_profile']['company']['company_name'];
                $call_count = $value['total_call'];
                if($value['user_profile']['gender'] == 1){
                  $gender = 'male';
                }
                if($value['user_profile']['gender'] == 2){
                  $gender = 'female';
                }
              ?>
            <tr>
                
                <td><?php echo $full_name; ?></td>
                <td><?php echo $email; ?></td>
                <td><?php echo $phone; ?></td>
                <td><?php echo $gender; ?></td>
                <td><?php echo $date_of_join; ?></td>
                <td><?php echo $date_of_birth; ?></td>
                <td><?php echo $company_name; ?></td>
                <td><?php echo $call_count; ?></td>
            </tr>
          <?php } ?>
            
        </tbody>
        <tfoot>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>phone</th>
                <th>gender</th>
                <th>date_of_join</th>
                <th>date_of_birth</th>
                <th>Company Name</th>
                <th>Total Calls</th>
            </tr>
        </tfoot>
    </table>
</div>

</body>
</html>
<script type="text/javascript">
  /*$(document).ready(function() {
    $('#example').DataTable( {
        "order": [[ 7, "desc" ]],
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    } );
} );*/
$(document).ready(function() {
    var table = $('#example').DataTable( {
        "order": [[ 7, "desc" ]],
        lengthChange: false,
        buttons: [ 'copy', 'csv', 'excel', 'pdf', 'colvis' ]
    } );
 
    table.buttons().container()
        .appendTo( '#example_wrapper .col-sm-6:eq(0)' );
} );
</script>