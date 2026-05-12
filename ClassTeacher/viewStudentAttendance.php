<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';



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
  <title>Dashboard</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">

  <style>
    body {
      background: #f5f8fc;
      font-family: "Nunito", "Segoe UI", Arial, sans-serif;
    }

    #content-wrapper {
      background: linear-gradient(135deg, #eef7ff 0%, #f9fbff 45%, #fff7ed 100%);
      min-height: 100vh;
    }

    .attendance-hero {
      position: relative;
      overflow: hidden;
      border-radius: 24px;
      padding: 30px;
      margin-bottom: 25px;
      color: #ffffff;
      background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #14b8a6 100%);
      box-shadow: 0 18px 42px rgba(37, 99, 235, 0.24);
    }

    .attendance-hero::before {
      content: "";
      position: absolute;
      width: 250px;
      height: 250px;
      right: -85px;
      top: -110px;
      background: rgba(255, 255, 255, 0.18);
      border-radius: 50%;
    }

    .attendance-hero::after {
      content: "";
      position: absolute;
      width: 165px;
      height: 165px;
      right: 125px;
      bottom: -100px;
      background: rgba(255, 255, 255, 0.13);
      border-radius: 50%;
    }

    .attendance-hero-content {
      position: relative;
      z-index: 2;
    }

    .hero-icon {
      width: 76px;
      height: 76px;
      border-radius: 24px;
      background: rgba(255, 255, 255, 0.18);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 33px;
      color: #ffffff;
      border: 1px solid rgba(255, 255, 255, 0.28);
      box-shadow: inset 0 1px 0 rgba(255,255,255,0.25);
    }

    .attendance-title {
      font-size: 28px;
      font-weight: 900;
      margin-bottom: 8px;
      letter-spacing: -0.4px;
    }

    .attendance-subtitle {
      font-size: 15px;
      opacity: 0.96;
      margin-bottom: 0;
      line-height: 1.6;
    }

    .soft-breadcrumb {
      display: inline-block;
      background: rgba(255, 255, 255, 0.88);
      border-radius: 50px;
      padding: 10px 18px;
      box-shadow: 0 8px 22px rgba(15, 23, 42, 0.07);
    }

    .soft-breadcrumb .breadcrumb {
      margin-bottom: 0;
      background: transparent;
      padding: 0;
      font-size: 13px;
      font-weight: 800;
    }

    .soft-breadcrumb a {
      color: #2563eb;
    }

    .info-card {
      background: #ffffff;
      border: 0;
      border-radius: 20px;
      box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
      height: 100%;
      overflow: hidden;
    }

    .info-icon {
      width: 48px;
      height: 48px;
      border-radius: 16px;
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
      font-weight: 900;
      margin-bottom: 3px;
    }

    .info-value {
      color: #0f172a;
      font-size: 17px;
      font-weight: 900;
      margin-bottom: 0;
    }

    .filter-card,
    .record-card {
      border: none;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 18px 45px rgba(15, 23, 42, 0.09);
      background: #ffffff;
    }

    .filter-card .card-header,
    .record-card .card-header {
      background: #ffffff;
      border-bottom: 1px solid #e5eaf2;
      padding: 22px 24px;
    }

    .card-heading {
      color: #0f172a;
      font-size: 18px;
      font-weight: 900;
      margin-bottom: 4px;
    }

    .card-small-text {
      color: #64748b;
      font-size: 13px;
      font-weight: 700;
      margin-bottom: 0;
      line-height: 1.6;
    }

    .filter-box {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 20px;
      padding: 20px;
      height: 100%;
    }

    .form-control-label {
      color: #334155;
      font-size: 13px;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 0.45px;
    }

    .custom-control-input,
    .filter-box .form-control,
    .filter-box select,
    #txtHint .form-control,
    #txtHint select,
    #txtHint input {
      min-height: 50px;
      border-radius: 14px !important;
      border: 1px solid #dbe3ef !important;
      color: #0f172a;
      font-weight: 800;
      padding: 10px 14px;
      box-shadow: none;
      transition: 0.2s ease;
    }

    .filter-box .form-control:focus,
    .filter-box select:focus,
    #txtHint .form-control:focus,
    #txtHint select:focus,
    #txtHint input:focus {
      border-color: #2563eb !important;
      box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.14) !important;
    }

    #txtHint {
      margin-top: 4px;
    }

    #txtHint .form-group,
    #txtHint .row {
      margin-bottom: 0 !important;
    }

    .dynamic-filter-area {
      background: #f8fafc;
      border: 1px dashed #cbd5e1;
      border-radius: 20px;
      padding: 18px 20px;
      margin-bottom: 20px;
    }

    .dynamic-filter-title {
      color: #0f172a;
      font-size: 14px;
      font-weight: 900;
      margin-bottom: 12px;
    }

    .btn-view-attendance {
      border: none;
      border-radius: 50px;
      padding: 13px 26px;
      font-weight: 900;
      letter-spacing: 0.2px;
      background: linear-gradient(135deg, #2563eb, #0ea5e9);
      color: #ffffff;
      box-shadow: 0 10px 22px rgba(37, 99, 235, 0.28);
      transition: 0.2s ease;
    }

    .btn-view-attendance:hover {
      color: #ffffff;
      transform: translateY(-1px);
      box-shadow: 0 14px 26px rgba(37, 99, 235, 0.35);
    }

    .record-note {
      display: inline-flex;
      align-items: center;
      padding: 10px 14px;
      border-radius: 50px;
      background: #eff6ff;
      color: #2563eb;
      font-weight: 900;
      font-size: 13px;
      border: 1px solid #bfdbfe;
    }

    .table-shell {
      padding: 24px;
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
      white-space: nowrap;
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

    .session-badge {
      display: inline-block;
      padding: 7px 11px;
      border-radius: 50px;
      background: #eef2ff;
      color: #4f46e5;
      font-weight: 800;
      font-size: 12px;
      border: 1px solid #c7d2fe;
    }

    .term-badge {
      display: inline-block;
      padding: 7px 11px;
      border-radius: 50px;
      background: #fff7ed;
      color: #ea580c;
      font-weight: 800;
      font-size: 12px;
      border: 1px solid #fed7aa;
    }

    .status-present {
      display: inline-flex;
      align-items: center;
      padding: 8px 12px;
      border-radius: 50px;
      background: #dcfce7;
      color: #15803d;
      font-weight: 900;
      font-size: 12px;
      border: 1px solid #bbf7d0;
    }

    .status-absent {
      display: inline-flex;
      align-items: center;
      padding: 8px 12px;
      border-radius: 50px;
      background: #fee2e2;
      color: #b91c1c;
      font-weight: 900;
      font-size: 12px;
      border: 1px solid #fecaca;
    }

    .date-badge {
      display: inline-flex;
      align-items: center;
      padding: 7px 11px;
      border-radius: 50px;
      background: #f8fafc;
      color: #475569;
      font-weight: 800;
      font-size: 12px;
      border: 1px solid #e2e8f0;
    }

    .custom-alert {
      border: none;
      border-radius: 16px;
      padding: 15px 18px;
      font-weight: 700;
      box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
      white-space: normal;
    }

    .empty-state {
      text-align: center;
      padding: 35px 20px;
      color: #64748b;
    }

    .empty-state i {
      font-size: 38px;
      margin-bottom: 12px;
      color: #2563eb;
    }

    .empty-state h6 {
      color: #0f172a;
      font-weight: 900;
      margin-bottom: 6px;
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
        font-size: 23px;
      }

      .hero-icon {
        width: 60px;
        height: 60px;
        font-size: 25px;
        margin-top: 16px;
      }

      .filter-card .card-header,
      .record-card .card-header {
        padding: 20px;
      }

      .table-shell {
        padding: 18px;
      }

      .btn-view-attendance {
        width: 100%;
      }
    }
  </style>

<script>
    function typeDropDown(str) {
    if (str == "") {
        document.getElementById("txtHint").innerHTML = "";
        return;
    } else { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHint").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET","ajaxCallTypes.php?tid="+str,true);
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

          <div class="attendance-hero">
            <div class="attendance-hero-content">
              <div class="row align-items-center">
                <div class="col-lg-9">
                  <div class="d-flex align-items-center mb-3">
                    <div class="hero-icon mr-3 d-none d-md-flex">
                      <i class="fas fa-user-graduate"></i>
                    </div>

                    <div>
                      <h1 class="attendance-title">View Student Attendance</h1>
                      <p class="attendance-subtitle">
                        Select a student and filter attendance records by all records, single date, or date range.
                      </p>
                    </div>
                  </div>

                  <div class="soft-breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a href="./">
                          <i class="fas fa-home mr-1"></i>Home
                        </a>
                      </li>
                      <li class="breadcrumb-item active" aria-current="page">View Student Attendance</li>
                    </ol>
                  </div>
                </div>

                <div class="col-lg-3 text-lg-right mt-4 mt-lg-0">
                  <div class="hero-icon ml-lg-auto">
                    <i class="fas fa-search"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-3">
              <div class="card info-card">
                <div class="card-body d-flex align-items-center">
                  <div class="info-icon icon-blue">
                    <i class="fas fa-calendar-alt"></i>
                  </div>
                  <div>
                    <p class="info-label">Today</p>
                    <p class="info-value"><?php echo date("F d, Y"); ?></p>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-3">
              <div class="card info-card">
                <div class="card-body d-flex align-items-center">
                  <div class="info-icon icon-green">
                    <i class="fas fa-users"></i>
                  </div>
                  <div>
                    <p class="info-label">Student Records</p>
                    <p class="info-value">Attendance History</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xl-4 col-md-12 mb-3">
              <div class="card info-card">
                <div class="card-body d-flex align-items-center">
                  <div class="info-icon icon-orange">
                    <i class="fas fa-filter"></i>
                  </div>
                  <div>
                    <p class="info-label">Filter Options</p>
                    <p class="info-value">All / Date / Range</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <!-- Form Basic -->
              <div class="card filter-card mb-4">
                <div class="card-header">
                  <div class="row align-items-center">
                    <div class="col-lg-8">
                      <h6 class="card-heading">
                        <i class="fas fa-sliders-h mr-2 text-primary"></i>
                        Attendance Search Filter
                      </h6>
                      <p class="card-small-text">
                        Choose a student first, then select how you want to view the attendance record.
                      </p>
                    </div>

                    <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                      <?php echo $statusMsg; ?>
                    </div>
                  </div>
                </div>

                <div class="card-body p-4">
                  <form method="post">
                    <div class="form-group row mb-3">
                        <div class="col-xl-6 mb-3 mb-xl-0">
                          <div class="filter-box">
                            <label class="form-control-label">
                              <i class="fas fa-user mr-1 text-primary"></i>
                              Select Student<span class="text-danger ml-2">*</span>
                            </label>
                            <?php
                            $qry= "SELECT * FROM tblstudents where classId = '$_SESSION[classId]' and classArmId = '$_SESSION[classArmId]' ORDER BY firstName ASC";
                            $result = $conn->query($qry);
                            $num = $result->num_rows;		
                            if ($num > 0){
                              echo ' <select required name="admissionNumber" class="form-control mb-0">';
                              echo'<option value="">--Select Student--</option>';
                              while ($rows = $result->fetch_assoc()){
                              echo'<option value="'.$rows['admissionNumber'].'" >'.$rows['firstName'].' '.$rows['lastName'].'</option>';
                                  }
                                      echo '</select>';
                                  }
                                ?>  
                          </div>
                        </div>

                        <div class="col-xl-6">
                          <div class="filter-box">
                            <label class="form-control-label">
                              <i class="fas fa-list mr-1 text-primary"></i>
                              Type<span class="text-danger ml-2">*</span>
                            </label>
                            <select required name="type" onchange="typeDropDown(this.value)" class="form-control mb-0">
                              <option value="">--Select--</option>
                              <option value="1" >All</option>
                              <option value="2" >By Single Date</option>
                              <option value="3" >By Date Range</option>
                            </select>
                          </div>
                        </div>
                    </div>

                    <div class="dynamic-filter-area">
                      <div class="dynamic-filter-title">
                        <i class="fas fa-calendar-check mr-2 text-primary"></i>
                        Date Filter
                      </div>
                      <?php
                        echo"<div id='txtHint'></div>";
                      ?>
                    </div>

                    <!-- <div class="form-group row mb-3">
                        <div class="col-xl-6">
                        <label class="form-control-label">Select Student<span class="text-danger ml-2">*</span></label>
                        
                        </div>
                        <div class="col-xl-6">
                        <label class="form-control-label">Type<span class="text-danger ml-2">*</span></label>
                        
                        </div>
                    </div> -->

                    <button type="submit" name="view" class="btn btn-view-attendance">
                      <i class="fas fa-eye mr-2"></i>
                      View Attendance
                    </button>
                  </form>
                </div>
              </div>

              <!-- Input Group -->
              <div class="row">
                <div class="col-lg-12">
                  <div class="card record-card mb-4">
                    <div class="card-header">
                      <div class="row align-items-center">
                        <div class="col-lg-7">
                          <h6 class="card-heading">
                            <i class="fas fa-clipboard-list mr-2 text-primary"></i>
                            Student Attendance Record
                          </h6>
                          <p class="card-small-text">
                            The selected student's attendance results will be shown in the table below.
                          </p>
                        </div>

                        <div class="col-lg-5 text-lg-right mt-3 mt-lg-0">
                          <span class="record-note">
                            <i class="fas fa-info-circle mr-2"></i>
                            Present and absent overview
                          </span>
                        </div>
                      </div>
                    </div>

                    <div class="table-shell">
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
                                <th>Session</th>
                                <th>Term</th>
                                <th>Status</th>
                                <th>Date</th>
                              </tr>
                            </thead>
                           
                            <tbody>

                          <?php

                            if(isset($_POST['view'])){

                               $admissionNumber =  $_POST['admissionNumber'];
                               $type =  $_POST['type'];

                               if($type == "1"){ //All Attendance

                                $query = "SELECT tblattendance.Id,tblattendance.status,tblattendance.dateTimeTaken,tblclass.className,
                                tblclassarms.classArmName,tblsessionterm.sessionName,tblsessionterm.termId,tblterm.termName,
                                tblstudents.firstName,tblstudents.lastName,tblstudents.otherName,tblstudents.admissionNumber
                                FROM tblattendance
                                INNER JOIN tblclass ON tblclass.Id = tblattendance.classId
                                INNER JOIN tblclassarms ON tblclassarms.Id = tblattendance.classArmId
                                INNER JOIN tblsessionterm ON tblsessionterm.Id = tblattendance.sessionTermId
                                INNER JOIN tblterm ON tblterm.Id = tblsessionterm.termId
                                INNER JOIN tblstudents ON tblstudents.admissionNumber = tblattendance.admissionNo
                                where tblattendance.admissionNo = '$admissionNumber' and tblattendance.classId = '$_SESSION[classId]' and tblattendance.classArmId = '$_SESSION[classArmId]'";

                               }
                               if($type == "2"){ //Single Date Attendance

                                $singleDate =  $_POST['singleDate'];

                                 $query = "SELECT tblattendance.Id,tblattendance.status,tblattendance.dateTimeTaken,tblclass.className,
                                tblclassarms.classArmName,tblsessionterm.sessionName,tblsessionterm.termId,tblterm.termName,
                                tblstudents.firstName,tblstudents.lastName,tblstudents.otherName,tblstudents.admissionNumber
                                FROM tblattendance
                                INNER JOIN tblclass ON tblclass.Id = tblattendance.classId
                                INNER JOIN tblclassarms ON tblclassarms.Id = tblattendance.classArmId
                                INNER JOIN tblsessionterm ON tblsessionterm.Id = tblattendance.sessionTermId
                                INNER JOIN tblterm ON tblterm.Id = tblsessionterm.termId
                                INNER JOIN tblstudents ON tblstudents.admissionNumber = tblattendance.admissionNo
                                where tblattendance.dateTimeTaken = '$singleDate' and tblattendance.admissionNo = '$admissionNumber' and tblattendance.classId = '$_SESSION[classId]' and tblattendance.classArmId = '$_SESSION[classArmId]'";
                                

                               }
                               if($type == "3"){ //Date Range Attendance

                                 $fromDate =  $_POST['fromDate'];
                                 $toDate =  $_POST['toDate'];

                                 $query = "SELECT tblattendance.Id,tblattendance.status,tblattendance.dateTimeTaken,tblclass.className,
                                tblclassarms.classArmName,tblsessionterm.sessionName,tblsessionterm.termId,tblterm.termName,
                                tblstudents.firstName,tblstudents.lastName,tblstudents.otherName,tblstudents.admissionNumber
                                FROM tblattendance
                                INNER JOIN tblclass ON tblclass.Id = tblattendance.classId
                                INNER JOIN tblclassarms ON tblclassarms.Id = tblattendance.classArmId
                                INNER JOIN tblsessionterm ON tblsessionterm.Id = tblattendance.sessionTermId
                                INNER JOIN tblterm ON tblterm.Id = tblsessionterm.termId
                                INNER JOIN tblstudents ON tblstudents.admissionNumber = tblattendance.admissionNo
                                where tblattendance.dateTimeTaken between '$fromDate' and '$toDate' and tblattendance.admissionNo = '$admissionNumber' and tblattendance.classId = '$_SESSION[classId]' and tblattendance.classArmId = '$_SESSION[classArmId]'";
                                
                               }

                              $rs = $conn->query($query);
                              $num = $rs->num_rows;
                              $sn=0;
                              $status="";
                              if($num > 0)
                              { 
                                while ($rows = $rs->fetch_assoc())
                                  {
                                      if($rows['status'] == '1'){
                                        $status = "<span class='status-present'><i class='fas fa-check-circle mr-1'></i>Present</span>";
                                      }else{
                                        $status = "<span class='status-absent'><i class='fas fa-times-circle mr-1'></i>Absent</span>";
                                      }

                                     $sn = $sn + 1;
                                    echo"
                                      <tr>
                                        <td><span class='student-number'>".$sn."</span></td>
                                        <td>".$rows['firstName']."</td>
                                        <td>".$rows['lastName']."</td>
                                        <td>".$rows['otherName']."</td>
                                        <td><span class='admission-badge'>".$rows['admissionNumber']."</span></td>
                                        <td><span class='class-badge'>".$rows['className']."</span></td>
                                        <td><span class='class-badge'>".$rows['classArmName']."</span></td>
                                        <td><span class='session-badge'>".$rows['sessionName']."</span></td>
                                        <td><span class='term-badge'>".$rows['termName']."</span></td>
                                        <td>".$status."</td>
                                        <td><span class='date-badge'><i class='fas fa-calendar mr-1'></i>".$rows['dateTimeTaken']."</span></td>
                                      </tr>";
                                  }
                              }
                              else
                              {
                                   echo   
                                   "<tr>
                                      <td colspan='11'>
                                        <div class='empty-state'>
                                          <i class='fas fa-folder-open'></i>
                                          <h6>No Record Found</h6>
                                          <p class='mb-0'>No attendance record matched your selected student and filter.</p>
                                        </div>
                                      </td>
                                    </tr>";
                              }
                            }
                            else
                            {
                              echo"
                              <tr>
                                <td colspan='11'>
                                  <div class='empty-state'>
                                    <i class='fas fa-search'></i>
                                    <h6>Select a student to view attendance</h6>
                                    <p class='mb-0'>Choose a student and filter type above, then click <strong>View Attendance</strong>.</p>
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
                </div>
              </div>
          </div>
          <!--Row-->

          <!-- Documentation Link -->
          <!-- <div class="row">
            <div class="col-lg-12 text-center">
              <p>For more documentations you can visit<a href="https://getbootstrap.com/docs/4.3/components/forms/"
                  target="_blank">
                  bootstrap forms documentations.</a> and <a
                  href="https://getbootstrap.com/docs/4.3/components/input-group/" target="_blank">bootstrap input
                  groups documentations</a></p>
            </div>
          </div> -->

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
      $('#dataTableHover').DataTable({
        "pageLength": 25,
        "ordering": true,
        "responsive": true,
        "language": {
          "search": "Search record:",
          "lengthMenu": "Show _MENU_ records",
          "info": "Showing _START_ to _END_ of _TOTAL_ records",
          "emptyTable": "No records available",
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