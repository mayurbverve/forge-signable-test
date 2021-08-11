<!DOCTYPE html>
<html lang="en">
<head>
  <title>Active Users Report</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
  <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
  <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

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
  <h4><b>Active Users</b></h4>
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
            </tr>
        </tfoot>
    </table>
</div>

</body>
</html>
<script type="text/javascript">
  $(document).ready(function() {
    $('#example').DataTable();
} );
</script>