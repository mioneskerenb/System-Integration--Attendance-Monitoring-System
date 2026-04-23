<?php
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

/*
|--------------------------------------------------------------------------
| SAFETY CHECK
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['userId']) || !isset($_SESSION['classId']) || !isset($_SESSION['classArmId'])) {
    die("Session expired or missing class information. Please login again.");
}

$userId      = $_SESSION['userId'];
$classId     = $_SESSION['classId'];
$classArmId  = $_SESSION['classArmId'];
$statusMsg   = "";
$dateTaken   = date("Y-m-d");

/*
|--------------------------------------------------------------------------
| GET TEACHER CLASS INFO
|--------------------------------------------------------------------------
*/
$query = "SELECT tblclass.className, tblclassarms.classArmName
          FROM tblclassteacher
          INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId
          INNER JOIN tblclassarms ON tblclassarms.Id = tblclassteacher.classArmId
          WHERE tblclassteacher.Id = '$userId'";

$rs  = $conn->query($query);
$rrw = $rs->fetch_assoc();

/*
|--------------------------------------------------------------------------
| GET ACTIVE SESSION / TERM
|--------------------------------------------------------------------------
*/
$sessionTermId = 0;
$querey = mysqli_query($conn, "SELECT * FROM tblsessionterm WHERE isActive ='1' LIMIT 1");
if ($querey && mysqli_num_rows($querey) > 0) {
    $rwws = mysqli_fetch_assoc($querey);
    $sessionTermId = $rwws['Id'];
}

/*
|--------------------------------------------------------------------------
| INSERT DEFAULT ATTENDANCE ROWS FOR TODAY IF NOT EXIST
|--------------------------------------------------------------------------
| This ensures every student in the teacher's class has a row for today.
| status = 0 means absent by default unless checked present.
|--------------------------------------------------------------------------
*/
$studentsQuery = mysqli_query($conn, "
    SELECT admissionNumber 
    FROM tblstudents
    WHERE classId = '$classId' AND classArmId = '$classArmId'
");

if ($studentsQuery) {
    while ($studentRow = mysqli_fetch_assoc($studentsQuery)) {
        $admissionNo = $studentRow['admissionNumber'];

        $checkExisting = mysqli_query($conn, "
            SELECT Id 
            FROM tblattendance
            WHERE admissionNo = '$admissionNo'
              AND classId = '$classId'
              AND classArmId = '$classArmId'
              AND sessionTermId = '$sessionTermId'
              AND dateTimeTaken = '$dateTaken'
            LIMIT 1
        ");

        if ($checkExisting && mysqli_num_rows($checkExisting) == 0) {
            mysqli_query($conn, "
                INSERT INTO tblattendance
                (admissionNo, classId, classArmId, sessionTermId, status, dateTimeTaken)
                VALUES
                ('$admissionNo', '$classId', '$classArmId', '$sessionTermId', '0', '$dateTaken')
            ");
        }
    }
}

/*
|--------------------------------------------------------------------------
| SAVE ATTENDANCE
|--------------------------------------------------------------------------
*/
if (isset($_POST['save'])) {

    $admissionNoList = isset($_POST['admissionNo']) ? $_POST['admissionNo'] : array();
    $checkedList     = isset($_POST['check']) ? $_POST['check'] : array();

    if (empty($admissionNoList)) {
        $statusMsg = "<div class='alert alert-danger'>No students found to save attendance.</div>";
    } else {

        $allSuccess = true;

        foreach ($admissionNoList as $admNo) {
            $status = in_array($admNo, $checkedList) ? '1' : '0';

            $updateQuery = mysqli_query($conn, "
                UPDATE tblattendance
                SET status = '$status'
                WHERE admissionNo = '$admNo'
                  AND classId = '$classId'
                  AND classArmId = '$classArmId'
                  AND sessionTermId = '$sessionTermId'
                  AND dateTimeTaken = '$dateTaken'
            ");

            if (!$updateQuery) {
                $allSuccess = false;
            }
        }

        if ($allSuccess) {
            $statusMsg = "<div class='alert alert-success'>Attendance saved successfully for today!</div>";
        } else {
            $statusMsg = "<div class='alert alert-danger'>An error occurred while saving attendance.</div>";
        }
    }
}
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
  <title>Take Attendance</title>

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
          <h1 class="h3 mb-0 text-gray-800">
            Take Attendance (Today's Date : <?php echo date("m-d-Y"); ?>)
          </h1>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">All Students in Class</li>
          </ol>
        </div>

        <div class="row">
          <div class="col-lg-12">

            <form method="post">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">
                    All Students in (<?php echo $rrw['className'] . ' - ' . $rrw['classArmName']; ?>)
                  </h6>
                  <h6 class="m-0 font-weight-bold text-danger">
                    Note: <i>Check the students who are present today.</i>
                  </h6>
                </div>

                <div class="card-body">
                  <?php echo $statusMsg; ?>

                  <div class="table-responsive">
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
                          <th>Present</th>
                        </tr>
                      </thead>
                      <tbody>

                      <?php
                      $query = "SELECT 
                                  tblstudents.Id,
                                  tblstudents.admissionNumber,
                                  tblstudents.firstName,
                                  tblstudents.lastName,
                                  tblstudents.otherName,
                                  tblclass.className,
                                  tblclassarms.classArmName,
                                  tblattendance.status
                                FROM tblstudents
                                INNER JOIN tblclass ON tblclass.Id = tblstudents.classId
                                INNER JOIN tblclassarms ON tblclassarms.Id = tblstudents.classArmId
                                LEFT JOIN tblattendance 
                                  ON tblattendance.admissionNo = tblstudents.admissionNumber
                                  AND tblattendance.classId = tblstudents.classId
                                  AND tblattendance.classArmId = tblstudents.classArmId
                                  AND tblattendance.sessionTermId = '$sessionTermId'
                                  AND tblattendance.dateTimeTaken = '$dateTaken'
                                WHERE tblstudents.classId = '$classId'
                                  AND tblstudents.classArmId = '$classArmId'
                                ORDER BY tblstudents.firstName ASC, tblstudents.lastName ASC";

                      $rs = $conn->query($query);
                      $num = $rs->num_rows;
                      $sn  = 0;

                      if ($num > 0) {
                          while ($rows = $rs->fetch_assoc()) {
                              $sn++;
                              $checked = ($rows['status'] == '1') ? "checked" : "";

                              echo "
                              <tr>
                                <td>{$sn}</td>
                                <td>{$rows['firstName']}</td>
                                <td>{$rows['lastName']}</td>
                                <td>{$rows['otherName']}</td>
                                <td>{$rows['admissionNumber']}</td>
                                <td>{$rows['className']}</td>
                                <td>{$rows['classArmName']}</td>
                                <td>
                                  <input name='check[]' type='checkbox' value='{$rows['admissionNumber']}' $checked>
                                  <input name='admissionNo[]' type='hidden' value='{$rows['admissionNumber']}'>
                                </td>
                              </tr>";
                          }
                      } else {
                          echo "
                          <tr>
                            <td colspan='8'>
                              <div class='alert alert-danger mb-0'>No Record Found!</div>
                            </td>
                          </tr>";
                      }
                      ?>

                      </tbody>
                    </table>
                  </div>

                  <br>
                  <button type="submit" name="save" class="btn btn-primary">
                    Save Attendance
                  </button>
                </div>
              </div>
            </form>

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