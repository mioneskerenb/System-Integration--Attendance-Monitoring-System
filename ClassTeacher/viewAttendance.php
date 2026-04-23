<?php
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$statusMsg = "";
$dateTaken = "";

if (!isset($_SESSION['userId']) || !isset($_SESSION['classId']) || !isset($_SESSION['classArmId'])) {
    die("Session expired or missing class information. Please login again.");
}

$classId    = $_SESSION['classId'];
$classArmId = $_SESSION['classArmId'];

/*
|--------------------------------------------------------------------------
| GET CLASS / ARM NAME FOR HEADER
|--------------------------------------------------------------------------
*/
$classQuery = "SELECT tblclass.className, tblclassarms.classArmName
               FROM tblclassteacher
               INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId
               INNER JOIN tblclassarms ON tblclassarms.Id = tblclassteacher.classArmId
               WHERE tblclassteacher.Id = '$_SESSION[userId]'";
$classResult = $conn->query($classQuery);
$classRow = $classResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>View Attendance</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body id="page-top">
  <div id="wrapper">

    <?php include "Includes/sidebar.php";?>

    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">

        <?php include "Includes/topbar.php";?>

        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">View Class Attendance</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">View Class Attendance</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">

              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">
                    View Attendance for <?php echo $classRow['className'] . " - " . $classRow['classArmName']; ?>
                  </h6>
                </div>
                <div class="card-body">

                  <?php echo $statusMsg; ?>

                  <form method="post">
                    <div class="form-group row mb-3">
                      <div class="col-xl-6">
                        <label class="form-control-label">Select Date<span class="text-danger ml-2">*</span></label>
                        <input
                          type="date"
                          class="form-control"
                          name="dateTaken"
                          value="<?php echo isset($_POST['dateTaken']) ? $_POST['dateTaken'] : ''; ?>"
                          required
                        >
                      </div>
                    </div>
                    <button type="submit" name="view" class="btn btn-primary">View Attendance</button>
                  </form>
                </div>
              </div>

              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Class Attendance Record</h6>
                </div>

                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Other Name</th>
                        <th>Admission No</th>
                        <th>Class</th>
                        <th>Class Arm</th>
                        <th>Session</th>
                        <th>Term</th>
                        <th>Status</th>
                        <th>Date</th>
                      </tr>
                    </thead>
                    <tbody>

                    <?php
                    if (isset($_POST['view'])) {

                        $dateTaken = $_POST['dateTaken'];

                        if (empty($dateTaken)) {
                            echo "
                            <tr>
                              <td colspan='11'>
                                <div class='alert alert-danger mb-0'>Please select a date first.</div>
                              </td>
                            </tr>";
                        } else {

                            $query = "SELECT 
                                        tblattendance.Id,
                                        tblattendance.status,
                                        tblattendance.dateTimeTaken,
                                        tblclass.className,
                                        tblclassarms.classArmName,
                                        tblsessionterm.sessionName,
                                        tblsessionterm.termId,
                                        tblterm.termName,
                                        tblstudents.firstName,
                                        tblstudents.lastName,
                                        tblstudents.otherName,
                                        tblstudents.admissionNumber
                                      FROM tblattendance
                                      INNER JOIN tblclass ON tblclass.Id = tblattendance.classId
                                      INNER JOIN tblclassarms ON tblclassarms.Id = tblattendance.classArmId
                                      INNER JOIN tblsessionterm ON tblsessionterm.Id = tblattendance.sessionTermId
                                      INNER JOIN tblterm ON tblterm.Id = tblsessionterm.termId
                                      INNER JOIN tblstudents ON tblstudents.admissionNumber = tblattendance.admissionNo
                                      WHERE tblattendance.dateTimeTaken = '$dateTaken'
                                        AND tblattendance.classId = '$classId'
                                        AND tblattendance.classArmId = '$classArmId'
                                      ORDER BY tblstudents.firstName ASC, tblstudents.lastName ASC";

                            $rs = $conn->query($query);
                            $num = $rs->num_rows;
                            $sn = 0;

                            if ($num > 0) {
                                while ($rows = $rs->fetch_assoc()) {
                                    $sn++;

                                    if ($rows['status'] == '1') {
                                        $status = "Present";
                                        $badge = "<span class='badge badge-success'>Present</span>";
                                    } else {
                                        $status = "Absent";
                                        $badge = "<span class='badge badge-danger'>Absent</span>";
                                    }

                                    echo "
                                    <tr>
                                      <td>".$sn."</td>
                                      <td>".$rows['firstName']."</td>
                                      <td>".$rows['lastName']."</td>
                                      <td>".$rows['otherName']."</td>
                                      <td>".$rows['admissionNumber']."</td>
                                      <td>".$rows['className']."</td>
                                      <td>".$rows['classArmName']."</td>
                                      <td>".$rows['sessionName']."</td>
                                      <td>".$rows['termName']."</td>
                                      <td>".$badge."</td>
                                      <td>".$rows['dateTimeTaken']."</td>
                                    </tr>";
                                }
                            } else {
                                echo "
                                <tr>
                                  <td colspan='11'>
                                    <div class='alert alert-danger mb-0'>No attendance record found for the selected date.</div>
                                  </td>
                                </tr>";
                            }
                        }
                    } else {
                        echo "
                        <tr>
                          <td colspan='11'>
                            <div class='alert alert-info mb-0'>Please select a date and click View Attendance.</div>
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
        </div>

      </div>

      <?php include "Includes/footer.php";?>
    </div>
  </div>

  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

  <script>
    $(document).ready(function () {
      $('#dataTableHover').DataTable();
    });
  </script>
</body>
</html>