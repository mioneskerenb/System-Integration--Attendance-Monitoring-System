<?php 
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
  <title>Class Teacher Dashboard</title>

  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">

  <!-- Animation Library -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
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

          <!-- Page Header -->
          <div class="row mb-4 animate__animated animate__fadeInDown">
            <div class="col-lg-8">
              <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center">
                    <div class="mr-3">
                      <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                           style="width: 70px; height: 70px;">
                        <i class="fas fa-chalkboard-teacher fa-2x"></i>
                      </div>
                    </div>

                    <div>
                      <h3 class="font-weight-bold mb-1">
                        Class Teacher Dashboard
                      </h3>

                      <p class="mb-1">
                        Welcome back! Manage your class attendance and student records easily.
                      </p>

                      <span class="badge badge-light text-primary px-3 py-2">
                        <i class="fas fa-users mr-1"></i>
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
                      Dashboard
                    </li>
                  </ol>

                  <div class="alert alert-info mb-0 py-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Monitor your assigned elementary class.
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Statistics Cards -->
          <div class="row mb-4">

            <!-- Students Card -->
            <?php 
            $query1=mysqli_query($conn,"SELECT * from tblstudents where classId = '$_SESSION[classId]' and classArmId = '$_SESSION[classArmId]'");                       
            $students = mysqli_num_rows($query1);
            ?>

            <div class="col-xl-3 col-md-6 mb-4 animate__animated animate__fadeInUp">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <p class="text-uppercase text-muted small font-weight-bold mb-1">
                        Students
                      </p>

                      <h2 class="font-weight-bold text-dark mb-1">
                        <?php echo $students;?>
                      </h2>

                      <span class="badge badge-info px-3 py-2">
                        <i class="fas fa-user-graduate mr-1"></i>
                        Enrolled Learners
                      </span>
                    </div>

                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 70px; height: 70px;">
                      <i class="fas fa-users fa-2x"></i>
                    </div>
                  </div>

                  <hr>

                  <p class="small text-muted mb-0">
                    <i class="fas fa-check-circle text-success mr-1"></i>
                    Total students assigned to your class.
                  </p>
                </div>
              </div>
            </div>

            <!-- Classes Card -->
            <?php 
            $query1=mysqli_query($conn,"SELECT * from tblclass");                       
            $class = mysqli_num_rows($query1);
            ?>

            <div class="col-xl-3 col-md-6 mb-4 animate__animated animate__fadeInUp animate__delay-1s">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <p class="text-uppercase text-muted small font-weight-bold mb-1">
                        Classes
                      </p>

                      <h2 class="font-weight-bold text-dark mb-1">
                        <?php echo $class;?>
                      </h2>

                      <span class="badge badge-primary px-3 py-2">
                        <i class="fas fa-chalkboard mr-1"></i>
                        Grade Levels
                      </span>
                    </div>

                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 70px; height: 70px;">
                      <i class="fas fa-chalkboard fa-2x"></i>
                    </div>
                  </div>

                  <hr>

                  <p class="small text-muted mb-0">
                    <i class="fas fa-layer-group text-primary mr-1"></i>
                    Total classes registered in the system.
                  </p>
                </div>
              </div>
            </div>

            <!-- Class Arms Card -->
            <?php 
            $query1=mysqli_query($conn,"SELECT * from tblclassarms");                       
            $classArms = mysqli_num_rows($query1);
            ?>

            <div class="col-xl-3 col-md-6 mb-4 animate__animated animate__fadeInUp animate__delay-1s">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <p class="text-uppercase text-muted small font-weight-bold mb-1">
                        Class Arms
                      </p>

                      <h2 class="font-weight-bold text-dark mb-1">
                        <?php echo $classArms;?>
                      </h2>

                      <span class="badge badge-success px-3 py-2">
                        <i class="fas fa-code-branch mr-1"></i>
                        Sections
                      </span>
                    </div>

                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 70px; height: 70px;">
                      <i class="fas fa-code-branch fa-2x"></i>
                    </div>
                  </div>

                  <hr>

                  <p class="small text-muted mb-0">
                    <i class="fas fa-sitemap text-success mr-1"></i>
                    Total sections available in the school system.
                  </p>
                </div>
              </div>
            </div>

            <!-- Attendance Card -->
            <?php 
            $query1=mysqli_query($conn,"SELECT * from tblattendance where classId = '$_SESSION[classId]' and classArmId = '$_SESSION[classArmId]'");                       
            $totAttendance = mysqli_num_rows($query1);
            ?>

            <div class="col-xl-3 col-md-6 mb-4 animate__animated animate__fadeInUp animate__delay-2s">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <p class="text-uppercase text-muted small font-weight-bold mb-1">
                        Total Attendance
                      </p>

                      <h2 class="font-weight-bold text-dark mb-1">
                        <?php echo $totAttendance;?>
                      </h2>

                      <span class="badge badge-warning text-dark px-3 py-2">
                        <i class="fas fa-calendar-check mr-1"></i>
                        Attendance Logs
                      </span>
                    </div>

                    <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 70px; height: 70px;">
                      <i class="fas fa-calendar fa-2x"></i>
                    </div>
                  </div>

                  <hr>

                  <p class="small text-muted mb-0">
                    <i class="fas fa-clipboard-check text-warning mr-1"></i>
                    Total attendance records for your assigned class.
                  </p>
                </div>
              </div>
            </div>

          </div>

          <!-- Quick Access Section -->
          <div class="row mb-4 animate__animated animate__fadeInUp animate__delay-2s">

            <div class="col-lg-8 mb-4">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                  <h5 class="font-weight-bold text-primary mb-0">
                    <i class="fas fa-bolt mr-2"></i>
                    Quick Class Management
                  </h5>
                </div>

                <div class="card-body">
                  <div class="row text-center">

                    <div class="col-md-4 mb-3">
                      <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                          <div class="bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                               style="width: 60px; height: 60px;">
                            <i class="fas fa-user-graduate fa-2x"></i>
                          </div>

                          <h6 class="font-weight-bold">View Students</h6>
                          <p class="small text-muted mb-0">
                            Check all learners assigned to your class.
                          </p>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-4 mb-3">
                      <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                          <div class="bg-success text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                               style="width: 60px; height: 60px;">
                            <i class="fas fa-clipboard-list fa-2x"></i>
                          </div>

                          <h6 class="font-weight-bold">Take Attendance</h6>
                          <p class="small text-muted mb-0">
                            Record daily class attendance accurately.
                          </p>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-4 mb-3">
                      <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                          <div class="bg-warning text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                               style="width: 60px; height: 60px;">
                            <i class="fas fa-chart-bar fa-2x"></i>
                          </div>

                          <h6 class="font-weight-bold">View Reports</h6>
                          <p class="small text-muted mb-0">
                            Review class attendance summaries.
                          </p>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-4 mb-4">
              <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                <div class="card-body p-4 d-flex flex-column justify-content-center">
                  <div class="text-center">
                    <div class="bg-white text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm"
                         style="width: 80px; height: 80px;">
                      <i class="fas fa-school fa-3x"></i>
                    </div>

                    <h5 class="font-weight-bold mb-2">
                      Elementary Attendance Monitoring
                    </h5>

                    <p class="mb-3">
                      Keep student attendance organized, clear, and easy to manage every school day.
                    </p>

                    <span class="badge badge-light text-primary px-3 py-2">
                      <i class="fas fa-shield-alt mr-1"></i>
                      Secure Teacher Access
                    </span>
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>
        <!---Container Fluid-->

      </div>

      <!-- Footer -->
      <?php include 'includes/footer.php';?>
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
  <script src="../vendor/chart.js/Chart.min.js"></script>
  <script src="js/demo/chart-area-demo.js"></script>  

</body>

</html>