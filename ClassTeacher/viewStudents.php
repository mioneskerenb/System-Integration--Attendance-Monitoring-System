<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$query = "SELECT tblclass.className,tblclassarms.classArmName 
    FROM tblclassteacher
    INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId
    INNER JOIN tblclassarms ON tblclassarms.Id = tblclassteacher.classArmId
    Where tblclassteacher.Id = '$_SESSION[userId]'";

$rs = $conn->query($query);
$num = $rs->num_rows;
$rrw = $rs->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>All Students in Class</title>

  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">

  <!-- DataTables -->
  <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

  <script>
    function classArmDropdown(str) {
      if (str == "") {
        document.getElementById("txtHint").innerHTML = "";
        return;
      } else { 
        if (window.XMLHttpRequest) {
          xmlhttp = new XMLHttpRequest();
        } else {
          xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }

        xmlhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
            document.getElementById("txtHint").innerHTML = this.responseText;
          }
        };

        xmlhttp.open("GET","ajaxClassArms2.php?cid="+str,true);
        xmlhttp.send();
      }
    }
  </script>
</head>

<body id="page-top">
  <div id="wrapper">

    <!-- Sidebar -->
    <?php include "Includes/sidebar.php";?>
    <!-- Sidebar -->

    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">

        <!-- TopBar -->
        <?php include "Includes/topbar.php";?>
        <!-- Topbar -->

        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">

          <?php
            $studentCountQuery = "SELECT tblstudents.Id,tblclass.className,tblclassarms.classArmName,tblclassarms.Id AS classArmId,tblstudents.firstName,
            tblstudents.lastName,tblstudents.otherName,tblstudents.admissionNumber,tblstudents.dateCreated
            FROM tblstudents
            INNER JOIN tblclass ON tblclass.Id = tblstudents.classId
            INNER JOIN tblclassarms ON tblclassarms.Id = tblstudents.classArmId
            where tblstudents.classId = '$_SESSION[classId]' and tblstudents.classArmId = '$_SESSION[classArmId]'";

            $studentCountResult = $conn->query($studentCountQuery);
            $totalStudents = $studentCountResult->num_rows;
          ?>

          <!-- Page Header -->
          <div class="row mb-4">
            <div class="col-lg-8">
              <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center">
                    <div class="mr-3">
                      <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm p-4">
                        <i class="fas fa-user-graduate fa-2x"></i>
                      </div>
                    </div>

                    <div>
                      <h3 class="font-weight-bold mb-1">
                        All Students in Class
                      </h3>

                      <p class="mb-2">
                        View and monitor the complete list of elementary learners assigned to your class.
                      </p>

                      <span class="badge badge-light text-primary px-3 py-2">
                        <i class="fas fa-chalkboard-teacher mr-1"></i>
                        <?php echo $rrw['className'].' - '.$rrw['classArmName'];?>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-4 mt-3 mt-lg-0">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                  <ol class="breadcrumb bg-transparent p-0 mb-2">
                    <li class="breadcrumb-item">
                      <a href="./">
                        <i class="fas fa-home mr-1"></i>Home
                      </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                      All Students in Class
                    </li>
                  </ol>

                  <div class="alert alert-info mb-0 py-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    This page displays students from your assigned class only.
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Summary Cards -->
          <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-3">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <p class="text-uppercase text-muted small font-weight-bold mb-1">
                        Total Students
                      </p>

                      <h2 class="font-weight-bold text-dark mb-1">
                        <?php echo $totalStudents;?>
                      </h2>

                      <span class="badge badge-primary px-3 py-2">
                        <i class="fas fa-users mr-1"></i>
                        Enrolled Learners
                      </span>
                    </div>

                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm p-4">
                      <i class="fas fa-users fa-2x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-3">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <p class="text-uppercase text-muted small font-weight-bold mb-1">
                        Class
                      </p>

                      <h5 class="font-weight-bold text-dark mb-2">
                        <?php echo $rrw['className'];?>
                      </h5>

                      <span class="badge badge-success px-3 py-2">
                        <i class="fas fa-school mr-1"></i>
                        Grade Level
                      </span>
                    </div>

                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm p-4">
                      <i class="fas fa-chalkboard fa-2x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xl-4 col-md-12 mb-3">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <p class="text-uppercase text-muted small font-weight-bold mb-1">
                        Section
                      </p>

                      <h5 class="font-weight-bold text-dark mb-2">
                        <?php echo $rrw['classArmName'];?>
                      </h5>

                      <span class="badge badge-warning text-dark px-3 py-2">
                        <i class="fas fa-code-branch mr-1"></i>
                        Class Arm
                      </span>
                    </div>

                    <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm p-4">
                      <i class="fas fa-code-branch fa-2x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Helpful Info Section -->
          <div class="row mb-4">
            <div class="col-lg-12">
              <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                  <div class="row align-items-center">
                    <div class="col-md-8">
                      <h5 class="font-weight-bold text-primary mb-2">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        Student Master List
                      </h5>
                      <p class="text-muted mb-md-0">
                        Use the table below to review student names, admission numbers, class, and section details.
                      </p>
                    </div>

                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                      <span class="badge badge-primary px-4 py-3 shadow-sm">
                        <i class="fas fa-user-check mr-1"></i>
                        <?php echo $totalStudents;?> Student(s) Found
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Student Table -->
          <div class="row">
            <div class="col-lg-12">
              <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white border-0 py-4">
                  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div>
                      <h5 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-ul mr-2"></i>
                        All Students in <?php echo $rrw['className'].' - '.$rrw['classArmName'];?>
                      </h5>
                      <p class="text-muted small mb-0 mt-1">
                        Complete list of students under your assigned elementary class.
                      </p>
                    </div>

                    <div class="mt-3 mt-md-0">
                      <span class="badge badge-light border px-3 py-2">
                        <i class="fas fa-table mr-1 text-primary"></i>
                        Search, sort, and view records
                      </span>
                    </div>
                  </div>
                </div>

                <div class="table-responsive p-3">
                  <table class="table table-bordered table-hover align-items-center" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th class="text-center">#</th>
                        <th>
                          <i class="fas fa-user mr-1 text-primary"></i>
                          First Name
                        </th>
                        <th>
                          <i class="fas fa-user mr-1 text-primary"></i>
                          Last Name
                        </th>
                        <th>
                          <i class="fas fa-user-tag mr-1 text-primary"></i>
                          Other Name
                        </th>
                        <th>
                          <i class="fas fa-id-card mr-1 text-primary"></i>
                          Admission No
                        </th>
                        <th>
                          <i class="fas fa-school mr-1 text-primary"></i>
                          Class
                        </th>
                        <th>
                          <i class="fas fa-code-branch mr-1 text-primary"></i>
                          Class Arm
                        </th>
                      </tr>
                    </thead>

                    <tbody>
                      <?php
                        $query = "SELECT tblstudents.Id,tblclass.className,tblclassarms.classArmName,tblclassarms.Id AS classArmId,tblstudents.firstName,
                        tblstudents.lastName,tblstudents.otherName,tblstudents.admissionNumber,tblstudents.dateCreated
                        FROM tblstudents
                        INNER JOIN tblclass ON tblclass.Id = tblstudents.classId
                        INNER JOIN tblclassarms ON tblclassarms.Id = tblstudents.classArmId
                        where tblstudents.classId = '$_SESSION[classId]' and tblstudents.classArmId = '$_SESSION[classArmId]'";

                        $rs = $conn->query($query);
                        $num = $rs->num_rows;
                        $sn = 0;
                        $status = "";

                        if($num > 0)
                        { 
                          while ($rows = $rs->fetch_assoc())
                          {
                            $sn = $sn + 1;

                            echo "
                              <tr>
                                <td class='text-center'>
                                  <span class='badge badge-primary px-3 py-2'>".$sn."</span>
                                </td>

                                <td class='font-weight-bold text-dark'>
                                  <i class='fas fa-child text-primary mr-1'></i>
                                  ".$rows['firstName']."
                                </td>

                                <td class='font-weight-bold text-dark'>
                                  ".$rows['lastName']."
                                </td>

                                <td>
                                  <span class='text-muted'>".$rows['otherName']."</span>
                                </td>

                                <td>
                                  <span class='badge badge-light border px-3 py-2'>
                                    <i class='fas fa-id-card mr-1 text-primary'></i>
                                    ".$rows['admissionNumber']."
                                  </span>
                                </td>

                                <td>
                                  <span class='badge badge-success px-3 py-2'>
                                    <i class='fas fa-school mr-1'></i>
                                    ".$rows['className']."
                                  </span>
                                </td>

                                <td>
                                  <span class='badge badge-warning text-dark px-3 py-2'>
                                    <i class='fas fa-code-branch mr-1'></i>
                                    ".$rows['classArmName']."
                                  </span>
                                </td>
                              </tr>";
                          }
                        }
                        else
                        {
                          echo "
                            <tr>
                              <td colspan='7' class='text-center py-5'>
                                <div class='alert alert-danger mb-0' role='alert'>
                                  <i class='fas fa-exclamation-circle fa-2x mb-2'></i>
                                  <h6 class='font-weight-bold mb-1'>No Record Found!</h6>
                                  <p class='mb-0'>There are no students assigned to this class and section yet.</p>
                                </div>
                              </td>
                            </tr>";
                        }
                      ?>
                    </tbody>
                  </table>
                </div>

              </div>
            </div>
          </div>
          <!-- Row -->

        </div>
        <!---Container Fluid-->

      </div>

      <!-- Footer -->
      <?php include "Includes/footer.php";?>
      <!-- Footer -->

    </div>
  </div>

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>

  <!-- Page level plugins -->
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

  <!-- Page level custom scripts -->
  <script>
    $(document).ready(function () {
      $('#dataTable').DataTable();
      $('#dataTableHover').DataTable();
    });
  </script>

</body>

</html>