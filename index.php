<?php 
include 'Includes/dbcon.php';
session_start();

function checkPassword($inputPassword, $storedPassword)
{
    // For teacher default password saved as md5("pass123")
    if ($storedPassword === md5($inputPassword)) {
        return true;
    }

    // For admin password if your old admin uses uppercase MD5
    if ($storedPassword === strtoupper(md5($inputPassword))) {
        return true;
    }

    // For admin/teacher password if ever saved using password_hash()
    if (password_verify($inputPassword, $storedPassword)) {
        return true;
    }

    return false;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="img/logo/attnlg.jpg" rel="icon">
    <title>Student Attendance System - Login</title>

    <!-- Bootstrap and Font Awesome -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">

    <!-- Animation Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>

<body>

    <div class="min-vh-100 d-flex align-items-center justify-content-center py-5"
         style="background-image: linear-gradient(rgba(13, 110, 253, 0.55), rgba(255, 255, 255, 0.75)), url('img/background/elementary-bg.avif'); background-size: cover; background-position: center; background-repeat: no-repeat;">

        <div class="container">
            <div class="row justify-content-center mx-0">
                <div class="col-xl-10 col-lg-11 col-md-10">

                    <div class="card border-0 shadow-lg rounded-lg overflow-hidden animate__animated animate__fadeInUp">
                        <div class="row no-gutters">

                            <!-- Left Welcome Panel -->
                            <div class="col-lg-6 bg-primary text-white d-flex align-items-center">
                                <div class="p-5 text-center text-lg-left w-100">

                                    <div class="mb-4 animate__animated animate__bounceIn">
                                        <img src="img/logo/attnlg.jpg" 
                                             class="rounded-circle border border-white shadow bg-white p-1" 
                                             width="115" 
                                             height="115" 
                                             alt="School Logo">
                                    </div>

                                    <h2 class="font-weight-bold mb-3 animate__animated animate__fadeInLeft">
                                        Student Attendance System
                                    </h2>

                                    <p class="lead mb-4 animate__animated animate__fadeInLeft animate__delay-1s">
                                        A simple, secure, and organized attendance monitoring system for elementary school learners.
                                    </p>

                                    <div class="row text-center animate__animated animate__fadeInUp animate__delay-1s">
                                        <div class="col-4 mb-3">
                                            <div class="border rounded p-3 bg-white text-primary shadow-sm h-100">
                                                <i class="fas fa-user-graduate fa-2x mb-2"></i>
                                                <p class="small mb-0 font-weight-bold">Students</p>
                                            </div>
                                        </div>

                                        <div class="col-4 mb-3">
                                            <div class="border rounded p-3 bg-white text-primary shadow-sm h-100">
                                                <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                                                <p class="small mb-0 font-weight-bold">Teachers</p>
                                            </div>
                                        </div>

                                        <div class="col-4 mb-3">
                                            <div class="border rounded p-3 bg-white text-primary shadow-sm h-100">
                                                <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                                                <p class="small mb-0 font-weight-bold">Attendance</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-light text-primary mt-4 mb-0 shadow-sm animate__animated animate__fadeIn animate__delay-2s">
                                        <i class="fas fa-shield-alt mr-1"></i>
                                        Secure access for administrators and class teachers.
                                    </div>

                                </div>
                            </div>

                            <!-- Right Login Panel -->
                            <div class="col-lg-6 bg-white">
                                <div class="p-5">

                                    <div class="text-center mb-4 animate__animated animate__fadeInDown">
                                        <h4 class="font-weight-bold text-dark mb-2">
                                            Welcome Back
                                        </h4>
                                        <p class="text-muted mb-0">
                                            Please login to continue to your dashboard.
                                        </p>
                                    </div>

                                    <form class="user animate__animated animate__fadeIn animate__delay-1s" method="POST" action="">

                                        <div class="form-group">
                                            <label class="font-weight-bold text-dark">
                                                Email Address
                                            </label>

                                            <div class="input-group input-group-lg">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-primary text-white">
                                                        <i class="fas fa-envelope"></i>
                                                    </span>
                                                </div>

                                                <input type="text" 
                                                       class="form-control" 
                                                       required 
                                                       name="username" 
                                                       placeholder="Enter Email Address">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold text-dark">
                                                Password
                                            </label>

                                            <div class="input-group input-group-lg">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-primary text-white">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                </div>

                                                <input type="password" 
                                                       name="password" 
                                                       required 
                                                       class="form-control" 
                                                       placeholder="Enter Password">
                                            </div>
                                        </div>

                                        <div class="form-group mt-4">
                                            <button type="submit" 
                                                    class="btn btn-primary btn-block btn-lg font-weight-bold shadow-sm" 
                                                    name="login">
                                                <i class="fas fa-sign-in-alt mr-1"></i>
                                                Login
                                            </button>
                                        </div>

                                    </form>

                                    <?php
                                    if (isset($_POST['login'])) {

                                        $username = trim($_POST['username']);
                                        $rawPassword = trim($_POST['password']);

                                        if ($username == '' || $rawPassword == '') {
                                            echo "<div class='alert alert-danger mt-3 animate__animated animate__shakeX' role='alert'>
                                                <i class='fas fa-exclamation-circle mr-1'></i>
                                                Please enter email and password.
                                            </div>";
                                        } else {

                                            /*
                                            |--------------------------------------------------------------------------
                                            | 1. CHECK ADMIN FIRST
                                            |--------------------------------------------------------------------------
                                            */
                                            $stmt = $conn->prepare("SELECT * FROM tbladmin WHERE emailAddress = ? LIMIT 1");
                                            $stmt->bind_param("s", $username);
                                            $stmt->execute();
                                            $rs = $stmt->get_result();

                                            if ($rs && $rs->num_rows > 0) {
                                                $rows = $rs->fetch_assoc();

                                                if (checkPassword($rawPassword, $rows['password'])) {
                                                    $_SESSION['userType'] = "Administrator";
                                                    $_SESSION['userId'] = $rows['Id'];
                                                    $_SESSION['firstName'] = $rows['firstName'];
                                                    $_SESSION['lastName'] = $rows['lastName'];
                                                    $_SESSION['emailAddress'] = $rows['emailAddress'];

                                                    echo "<script type=\"text/javascript\">
                                                        window.location = ('Admin/index.php');
                                                    </script>";
                                                    exit();
                                                }
                                            }

                                            $stmt->close();

                                            /*
                                            |--------------------------------------------------------------------------
                                            | 2. IF NOT ADMIN, CHECK CLASS TEACHER
                                            |--------------------------------------------------------------------------
                                            */
                                            $stmt = $conn->prepare("SELECT * FROM tblclassteacher WHERE emailAddress = ? LIMIT 1");
                                            $stmt->bind_param("s", $username);
                                            $stmt->execute();
                                            $rs = $stmt->get_result();

                                            if ($rs && $rs->num_rows > 0) {
                                                $rows = $rs->fetch_assoc();

                                                if (checkPassword($rawPassword, $rows['password'])) {
                                                    $_SESSION['userType'] = "ClassTeacher";
                                                    $_SESSION['userId'] = $rows['Id'];
                                                    $_SESSION['firstName'] = $rows['firstName'];
                                                    $_SESSION['lastName'] = $rows['lastName'];
                                                    $_SESSION['emailAddress'] = $rows['emailAddress'];
                                                    $_SESSION['classId'] = $rows['classId'];
                                                    $_SESSION['classArmId'] = $rows['classArmId'];

                                                    echo "<script type=\"text/javascript\">
                                                        window.location = ('ClassTeacher/index.php');
                                                    </script>";
                                                    exit();
                                                }
                                            }

                                            $stmt->close();

                                            echo "<div class='alert alert-danger mt-3 animate__animated animate__shakeX' role='alert'>
                                                <i class='fas fa-times-circle mr-1'></i>
                                                Invalid email or password.
                                            </div>";
                                        }
                                    }
                                    ?>

                                    <hr>

                                    <div class="text-center animate__animated animate__fadeIn animate__delay-2s">
                                        <p class="small text-muted mb-1">
                                            <i class="fas fa-school mr-1 text-primary"></i>
                                            Elementary School Attendance Monitoring
                                        </p>
                                        <p class="small text-muted mb-0">
                                            Manage attendance records with ease and accuracy.
                                        </p>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="text-center mt-4 animate__animated animate__fadeIn animate__delay-2s">
                        <span class="badge badge-light text-primary shadow-sm px-3 py-2">
                            <i class="fas fa-heart mr-1"></i>
                            Designed for elementary school attendance management
                        </span>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>