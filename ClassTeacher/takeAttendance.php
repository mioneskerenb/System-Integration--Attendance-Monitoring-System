<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../Includes/dbcon.php';
include '../Includes/session.php';

/*
|--------------------------------------------------------------------------
| DATABASE TRIGGER FIX
|--------------------------------------------------------------------------
| Your tblattendance table has triggers that require @app_role.
| Without this, MySQL blocks INSERT/UPDATE.
|--------------------------------------------------------------------------
*/
mysqli_query($conn, "SET @app_role = 'ClassTeacher'");

$statusMsg = "";

if (!isset($_SESSION['userId'])) {
    die("Session expired. Please login again.");
}

$userId = $_SESSION['userId'];

/*
|--------------------------------------------------------------------------
| GET LOGGED-IN TEACHER CLASS AND CLASS ARM
|--------------------------------------------------------------------------
*/
$teacherQuery = mysqli_query($conn, "
    SELECT 
        tblclassteacher.Id,
        tblclassteacher.classId,
        tblclassteacher.classArmId,
        tblclass.className,
        tblclassarms.classArmName
    FROM tblclassteacher
    INNER JOIN tblclass 
        ON tblclass.Id = tblclassteacher.classId
    INNER JOIN tblclassarms 
        ON tblclassarms.Id = tblclassteacher.classArmId
    WHERE tblclassteacher.Id = '$userId'
    LIMIT 1
");

if (!$teacherQuery) {
    die("Teacher query error: " . mysqli_error($conn));
}

if (mysqli_num_rows($teacherQuery) == 0) {
    die("Class teacher assignment not found. Please assign this teacher to a class and class arm.");
}

$teacherRow = mysqli_fetch_assoc($teacherQuery);

$classId = $teacherRow['classId'];
$classArmId = $teacherRow['classArmId'];
$className = $teacherRow['className'];
$classArmName = $teacherRow['classArmName'];

/*
|--------------------------------------------------------------------------
| GET ACTIVE SESSION TERM
|--------------------------------------------------------------------------
*/
$sessionQuery = mysqli_query($conn, "
    SELECT Id, sessionName, termId
    FROM tblsessionterm
    WHERE isActive = '1'
    LIMIT 1
");

if (!$sessionQuery) {
    die("Session term query error: " . mysqli_error($conn));
}

if (mysqli_num_rows($sessionQuery) == 0) {
    die("No active session term found. Please activate one session term first.");
}

$sessionRow = mysqli_fetch_assoc($sessionQuery);
$sessionTermId = $sessionRow['Id'];

$dateTaken = date("Y-m-d");

/*
|--------------------------------------------------------------------------
| SAVE ATTENDANCE
|--------------------------------------------------------------------------
*/
if (isset($_POST['save'])) {

    mysqli_query($conn, "SET @app_role = 'ClassTeacher'");

    $admissionNoList = isset($_POST['admissionNo']) ? $_POST['admissionNo'] : array();
    $checkedList = isset($_POST['check']) ? $_POST['check'] : array();

    if (empty($admissionNoList)) {
        $statusMsg = "<div class='alert alert-danger custom-alert'><i class='fas fa-exclamation-circle mr-2'></i>No students found to save attendance.</div>";
    } else {

        $savedCount = 0;
        $failedCount = 0;
        $errorMessages = array();

        foreach ($admissionNoList as $admissionNo) {

            $admissionNo = mysqli_real_escape_string($conn, $admissionNo);
            $status = in_array($admissionNo, $checkedList) ? '1' : '0';

            /*
            |--------------------------------------------------------------------------
            | CHECK IF STUDENT BELONGS TO THIS TEACHER'S CLASS
            |--------------------------------------------------------------------------
            */
            $studentCheck = mysqli_query($conn, "
                SELECT Id
                FROM tblstudents
                WHERE admissionNumber = '$admissionNo'
                AND classId = '$classId'
                AND classArmId = '$classArmId'
                LIMIT 1
            ");

            if (!$studentCheck) {
                $failedCount++;
                $errorMessages[] = "Student check error for $admissionNo: " . mysqli_error($conn);
                continue;
            }

            if (mysqli_num_rows($studentCheck) == 0) {
                $failedCount++;
                $errorMessages[] = "Student $admissionNo does not belong to this teacher's class.";
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | CHECK IF ATTENDANCE ALREADY EXISTS TODAY
            |--------------------------------------------------------------------------
            */
            $checkAttendance = mysqli_query($conn, "
                SELECT Id
                FROM tblattendance
                WHERE admissionNo = '$admissionNo'
                AND classId = '$classId'
                AND classArmId = '$classArmId'
                AND sessionTermId = '$sessionTermId'
                AND dateTimeTaken = '$dateTaken'
                LIMIT 1
            ");

            if (!$checkAttendance) {
                $failedCount++;
                $errorMessages[] = "Check attendance error for $admissionNo: " . mysqli_error($conn);
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE EXISTING ATTENDANCE
            |--------------------------------------------------------------------------
            */
            if (mysqli_num_rows($checkAttendance) > 0) {

                $attendanceRow = mysqli_fetch_assoc($checkAttendance);
                $attendanceId = $attendanceRow['Id'];

                mysqli_query($conn, "SET @app_role = 'ClassTeacher'");

                $saveQuery = mysqli_query($conn, "
                    UPDATE tblattendance
                    SET 
                        status = '$status',
                        updatedBy = '$userId',
                        lastUpdated = NOW()
                    WHERE Id = '$attendanceId'
                ");

                if ($saveQuery) {
                    $savedCount++;
                } else {
                    $failedCount++;
                    $errorMessages[] = "Failed to update $admissionNo: " . mysqli_error($conn);
                }

            } else {

                /*
                |--------------------------------------------------------------------------
                | INSERT NEW ATTENDANCE
                |--------------------------------------------------------------------------
                */
                mysqli_query($conn, "SET @app_role = 'ClassTeacher'");

                $saveQuery = mysqli_query($conn, "
                    INSERT INTO tblattendance
                    (
                        admissionNo,
                        classId,
                        classArmId,
                        sessionTermId,
                        status,
                        dateTimeTaken,
                        createdBy,
                        updatedBy,
                        lastUpdated
                    )
                    VALUES
                    (
                        '$admissionNo',
                        '$classId',
                        '$classArmId',
                        '$sessionTermId',
                        '$status',
                        '$dateTaken',
                        '$userId',
                        '$userId',
                        NOW()
                    )
                ");

                if ($saveQuery) {
                    $savedCount++;
                } else {
                    $failedCount++;
                    $errorMessages[] = "Failed to insert $admissionNo: " . mysqli_error($conn);
                }
            }
        }

        if ($savedCount > 0 && $failedCount == 0) {
            $statusMsg = "
                <div class='alert alert-success custom-alert'>
                    <i class='fas fa-check-circle mr-2'></i>
                    Attendance saved successfully! Saved: $savedCount student(s).
                </div>
            ";
        } elseif ($savedCount > 0 && $failedCount > 0) {
            $statusMsg = "
                <div class='alert alert-warning custom-alert'>
                    <i class='fas fa-exclamation-triangle mr-2'></i>
                    Some attendance was saved.<br>
                    Saved: $savedCount<br>
                    Failed: $failedCount<br><br>
                    " . implode("<br>", $errorMessages) . "
                </div>
            ";
        } else {
            $statusMsg = "
                <div class='alert alert-danger custom-alert'>
                    <i class='fas fa-times-circle mr-2'></i>
                    Attendance was not saved.<br><br>
                    " . implode("<br>", $errorMessages) . "
                </div>
            ";
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

  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>Take Attendance</title>

  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

  <style>
    body {
      background: #f4f8fb;
      font-family: "Nunito", "Segoe UI", Arial, sans-serif;
    }

    #content-wrapper {
      background: linear-gradient(135deg, #eef7ff 0%, #f9fbff 45%, #fff7ed 100%);
    }

    .attendance-hero {
      position: relative;
      overflow: hidden;
      border-radius: 24px;
      padding: 28px 30px;
      margin-bottom: 25px;
      color: #ffffff;
      background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 48%, #14b8a6 100%);
      box-shadow: 0 18px 40px rgba(37, 99, 235, 0.22);
    }

    .attendance-hero::before {
      content: "";
      position: absolute;
      width: 230px;
      height: 230px;
      right: -70px;
      top: -90px;
      background: rgba(255, 255, 255, 0.18);
      border-radius: 50%;
    }

    .attendance-hero::after {
      content: "";
      position: absolute;
      width: 160px;
      height: 160px;
      right: 120px;
      bottom: -100px;
      background: rgba(255, 255, 255, 0.13);
      border-radius: 50%;
    }

    .attendance-hero-content {
      position: relative;
      z-index: 2;
    }

    .attendance-title {
      font-size: 27px;
      font-weight: 800;
      margin-bottom: 8px;
      letter-spacing: -0.4px;
    }

    .attendance-subtitle {
      font-size: 15px;
      opacity: 0.95;
      margin-bottom: 0;
    }

    .hero-icon {
      width: 74px;
      height: 74px;
      border-radius: 22px;
      background: rgba(255, 255, 255, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
      color: #ffffff;
      border: 1px solid rgba(255, 255, 255, 0.32);
      box-shadow: inset 0 1px 0 rgba(255,255,255,0.2);
    }

    .soft-breadcrumb {
      background: rgba(255, 255, 255, 0.86);
      border-radius: 50px;
      padding: 10px 18px;
      box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
    }

    .soft-breadcrumb .breadcrumb {
      margin-bottom: 0;
      background: transparent;
      padding: 0;
      font-size: 13px;
      font-weight: 700;
    }

    .info-card {
      background: #ffffff;
      border: 0;
      border-radius: 20px;
      box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
      overflow: hidden;
      height: 100%;
    }

    .info-card .icon-box {
      width: 46px;
      height: 46px;
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      margin-right: 14px;
    }

    .icon-blue {
      background: #dbeafe;
      color: #2563eb;
    }

    .icon-green {
      background: #dcfce7;
      color: #16a34a;
    }

    .icon-orange {
      background: #ffedd5;
      color: #ea580c;
    }

    .info-label {
      color: #64748b;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      font-weight: 800;
      margin-bottom: 3px;
    }

    .info-value {
      color: #0f172a;
      font-size: 17px;
      font-weight: 800;
      margin-bottom: 0;
    }

    .main-attendance-card {
      border: none;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 18px 45px rgba(15, 23, 42, 0.09);
      background: #ffffff;
    }

    .main-attendance-card .card-header {
      background: #ffffff;
      border-bottom: 1px solid #e5eaf2;
      padding: 22px 24px;
    }

    .card-heading {
      color: #0f172a;
      font-size: 18px;
      font-weight: 800;
      margin-bottom: 4px;
    }

    .card-small-text {
      color: #64748b;
      font-size: 13px;
      margin-bottom: 0;
    }

    .note-pill {
      display: inline-flex;
      align-items: center;
      padding: 10px 14px;
      border-radius: 50px;
      background: #fff7ed;
      color: #c2410c;
      font-weight: 800;
      font-size: 13px;
      border: 1px solid #fed7aa;
    }

    .custom-alert {
      border: none;
      border-radius: 16px;
      padding: 15px 18px;
      font-weight: 700;
      box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    }

    .table-wrapper {
      border: 1px solid #e5eaf2;
      border-radius: 20px;
      overflow: hidden;
      background: #ffffff;
    }

    table.dataTable {
      margin-top: 0 !important;
      margin-bottom: 0 !important;
    }

    .table {
      color: #334155;
      margin-bottom: 0;
    }

    .table thead th {
      background: #f8fafc !important;
      color: #475569;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.55px;
      font-weight: 900;
      border-bottom: 1px solid #e2e8f0 !important;
      padding: 15px 12px;
      white-space: nowrap;
    }

    .table tbody td {
      vertical-align: middle;
      padding: 14px 12px;
      border-top: 1px solid #eef2f7;
      font-size: 14px;
      font-weight: 650;
    }

    .table-hover tbody tr:hover {
      background: #f0f9ff;
      transition: 0.2s ease;
    }

    .student-number {
      width: 35px;
      height: 35px;
      border-radius: 12px;
      background: #eff6ff;
      color: #2563eb;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 900;
      font-size: 13px;
    }

    .admission-badge {
      display: inline-block;
      padding: 7px 11px;
      border-radius: 50px;
      background: #f1f5f9;
      color: #334155;
      font-weight: 800;
      font-size: 12px;
      border: 1px solid #e2e8f0;
    }

    .class-badge {
      display: inline-block;
      padding: 7px 11px;
      border-radius: 50px;
      background: #ecfeff;
      color: #0891b2;
      font-weight: 800;
      font-size: 12px;
      border: 1px solid #cffafe;
    }

    .present-toggle {
      position: relative;
      display: inline-block;
      width: 58px;
      height: 30px;
      margin-bottom: 0;
    }

    .present-toggle input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .present-slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #cbd5e1;
      transition: .3s;
      border-radius: 999px;
      box-shadow: inset 0 2px 4px rgba(15, 23, 42, 0.16);
    }

    .present-slider:before {
      position: absolute;
      content: "";
      height: 24px;
      width: 24px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: .3s;
      border-radius: 50%;
      box-shadow: 0 3px 8px rgba(15, 23, 42, 0.25);
    }

    .present-toggle input:checked + .present-slider {
      background: linear-gradient(135deg, #22c55e, #16a34a);
    }

    .present-toggle input:checked + .present-slider:before {
      transform: translateX(28px);
    }

    .save-panel {
      background: #f8fafc;
      border-top: 1px solid #e5eaf2;
      padding: 20px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }

    .save-text {
      color: #64748b;
      font-size: 13px;
      font-weight: 700;
      margin-bottom: 0;
    }

    .btn-save-attendance {
      border: none;
      border-radius: 50px;
      padding: 12px 24px;
      font-weight: 900;
      letter-spacing: 0.2px;
      background: linear-gradient(135deg, #2563eb, #0ea5e9);
      color: #ffffff;
      box-shadow: 0 10px 22px rgba(37, 99, 235, 0.28);
      transition: 0.2s ease;
    }

    .btn-save-attendance:hover {
      color: #ffffff;
      transform: translateY(-1px);
      box-shadow: 0 14px 26px rgba(37, 99, 235, 0.35);
    }

    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label,
    .dataTables_wrapper .dataTables_info {
      color: #64748b;
      font-size: 13px;
      font-weight: 700;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
      border-radius: 12px;
      border: 1px solid #dbe3ef;
      padding: 7px 10px;
      outline: none;
    }

    .page-item.active .page-link {
      background: #2563eb;
      border-color: #2563eb;
    }

    .page-link {
      color: #2563eb;
      border-radius: 10px;
      margin: 0 2px;
      border: 1px solid #e2e8f0;
      font-weight: 700;
    }

    @media (max-width: 768px) {
      .attendance-hero {
        padding: 24px 22px;
      }

      .attendance-title {
        font-size: 22px;
      }

      .hero-icon {
        width: 58px;
        height: 58px;
        font-size: 25px;
        margin-top: 16px;
      }

      .main-attendance-card .card-header {
        padding: 20px;
      }

      .save-panel {
        display: block;
      }

      .btn-save-attendance {
        width: 100%;
        margin-top: 14px;
      }
    }
  </style>
</head>

<body id="page-top">

<div id="wrapper">

  <?php include "Includes/sidebar.php"; ?>

  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">

      <?php include "Includes/topbar.php"; ?>

      <div class="container-fluid" id="container-wrapper">

        <div class="attendance-hero">
          <div class="attendance-hero-content">
            <div class="row align-items-center">
              <div class="col-lg-9">
                <div class="d-flex align-items-center mb-3">
                  <div class="hero-icon mr-3 d-none d-md-flex">
                    <i class="fas fa-user-check"></i>
                  </div>
                  <div>
                    <h1 class="attendance-title">
                      Take Attendance
                    </h1>
                    <p class="attendance-subtitle">
                      Mark today’s student attendance quickly and accurately for your assigned class.
                    </p>
                  </div>
                </div>

                <div class="soft-breadcrumb d-inline-block">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                      <a href="./">
                        <i class="fas fa-home mr-1"></i>Home
                      </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Take Attendance</li>
                  </ol>
                </div>
              </div>

              <div class="col-lg-3 text-lg-right mt-4 mt-lg-0">
                <div class="hero-icon ml-lg-auto">
                  <i class="fas fa-calendar-check"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-xl-4 col-md-6 mb-3">
            <div class="card info-card">
              <div class="card-body d-flex align-items-center">
                <div class="icon-box icon-blue">
                  <i class="fas fa-calendar-day"></i>
                </div>
                <div>
                  <p class="info-label">Today's Date</p>
                  <p class="info-value"><?php echo date("F d, Y"); ?></p>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-4 col-md-6 mb-3">
            <div class="card info-card">
              <div class="card-body d-flex align-items-center">
                <div class="icon-box icon-green">
                  <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                  <p class="info-label">Assigned Class</p>
                  <p class="info-value"><?php echo $className . " - " . $classArmName; ?></p>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-4 col-md-12 mb-3">
            <div class="card info-card">
              <div class="card-body d-flex align-items-center">
                <div class="icon-box icon-orange">
                  <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                  <p class="info-label">Instruction</p>
                  <p class="info-value">Check present students</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">

            <form method="post">

              <div class="card main-attendance-card mb-4">

                <div class="card-header">
                  <div class="row align-items-center">
                    <div class="col-lg-7">
                      <h6 class="card-heading">
                        <i class="fas fa-users mr-2 text-primary"></i>
                        All Students in <?php echo $className . " - " . $classArmName; ?>
                      </h6>
                      <p class="card-small-text">
                        Review the list below and switch on the attendance toggle for students who are present today.
                      </p>
                    </div>

                    <div class="col-lg-5 text-lg-right mt-3 mt-lg-0">
                      <span class="note-pill">
                        <i class="fas fa-info-circle mr-2"></i>
                        Present = toggle switched on
                      </span>
                    </div>
                  </div>
                </div>

                <div class="card-body p-4">

                  <?php echo $statusMsg; ?>

                  <div class="table-wrapper">
                    <div class="table-responsive">
                      <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Other Name</th>
                            <th>Admission No</th>
                            <th>Class</th>
                            <th>Class Arm</th>
                            <th class="text-center">Present</th>
                          </tr>
                        </thead>

                        <tbody>

                        <?php
                        $studentsQuery = mysqli_query($conn, "
                            SELECT 
                                tblstudents.Id,
                                tblstudents.admissionNumber,
                                tblstudents.firstName,
                                tblstudents.lastName,
                                tblstudents.otherName,
                                tblclass.className,
                                tblclassarms.classArmName,
                                tblattendance.status
                            FROM tblstudents
                            INNER JOIN tblclass 
                                ON tblclass.Id = tblstudents.classId
                            INNER JOIN tblclassarms 
                                ON tblclassarms.Id = tblstudents.classArmId
                            LEFT JOIN tblattendance 
                                ON tblattendance.admissionNo = tblstudents.admissionNumber
                                AND tblattendance.classId = tblstudents.classId
                                AND tblattendance.classArmId = tblstudents.classArmId
                                AND tblattendance.sessionTermId = '$sessionTermId'
                                AND tblattendance.dateTimeTaken = '$dateTaken'
                            WHERE tblstudents.classId = '$classId'
                            AND tblstudents.classArmId = '$classArmId'
                            ORDER BY tblstudents.firstName ASC, tblstudents.lastName ASC
                        ");

                        if (!$studentsQuery) {

                            echo "
                            <tr>
                              <td colspan='8'>
                                <div class='alert alert-danger custom-alert mb-0'>
                                  <i class='fas fa-exclamation-circle mr-2'></i>
                                  Student query error: " . mysqli_error($conn) . "
                                </div>
                              </td>
                            </tr>";

                        } else {

                            $num = mysqli_num_rows($studentsQuery);
                            $sn = 0;

                            if ($num > 0) {

                                while ($row = mysqli_fetch_assoc($studentsQuery)) {

                                    $sn++;
                                    $checked = ($row['status'] == '1') ? "checked" : "";

                                    echo "
                                    <tr>
                                      <td>
                                        <span class='student-number'>" . $sn . "</span>
                                      </td>
                                      <td>" . $row['firstName'] . "</td>
                                      <td>" . $row['lastName'] . "</td>
                                      <td>" . $row['otherName'] . "</td>
                                      <td>
                                        <span class='admission-badge'>" . $row['admissionNumber'] . "</span>
                                      </td>
                                      <td>
                                        <span class='class-badge'>" . $row['className'] . "</span>
                                      </td>
                                      <td>
                                        <span class='class-badge'>" . $row['classArmName'] . "</span>
                                      </td>
                                      <td class='text-center'>
                                        <label class='present-toggle'>
                                          <input name='check[]' type='checkbox' value='" . $row['admissionNumber'] . "' " . $checked . ">
                                          <span class='present-slider'></span>
                                        </label>
                                        <input name='admissionNo[]' type='hidden' value='" . $row['admissionNumber'] . "'>
                                      </td>
                                    </tr>";
                                }

                            } else {

                                echo "
                                <tr>
                                  <td colspan='8'>
                                    <div class='alert alert-danger custom-alert mb-0'>
                                      <i class='fas fa-user-slash mr-2'></i>
                                      No students found in this teacher's class.
                                    </div>
                                  </td>
                                </tr>";
                            }
                        }
                        ?>

                        </tbody>
                      </table>
                    </div>
                  </div>

                </div>

                <div class="save-panel">
                  <p class="save-text">
                    <i class="fas fa-shield-alt mr-1 text-primary"></i>
                    Please review all attendance marks before saving.
                  </p>

                  <button type="submit" name="save" class="btn btn-save-attendance">
                    <i class="fas fa-save mr-2"></i>
                    Save Attendance
                  </button>
                </div>

              </div>

            </form>

          </div>
        </div>

      </div>
    </div>

    <?php include "Includes/footer.php"; ?>

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
    $('#dataTableHover').DataTable({
        "pageLength": 100,
        "ordering": true,
        "responsive": true,
        "language": {
            "search": "Search student:",
            "lengthMenu": "Show _MENU_ students",
            "info": "Showing _START_ to _END_ of _TOTAL_ students",
            "paginate": {
                "previous": "<i class='fas fa-chevron-left'></i>",
                "next": "<i class='fas fa-chevron-right'></i>"
            }
        }
    });
});
</script>

</body>
</html>