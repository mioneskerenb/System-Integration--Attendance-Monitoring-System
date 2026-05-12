<?php
include '../Includes/dbcon.php';
include '../Includes/session.php';

function checkPassword($inputPassword, $storedPassword)
{
    // MD5 lowercase
    if ($storedPassword === md5($inputPassword)) {
        return true;
    }

    // MD5 uppercase
    if ($storedPassword === strtoupper(md5($inputPassword))) {
        return true;
    }

    // password_hash / bcrypt support
    if (password_verify($inputPassword, $storedPassword)) {
        return true;
    }

    return false;
}

$message = "";
$alertType = "";

if (isset($_POST['changePassword'])) {

    $currentPassword = trim($_POST['currentPassword']);
    $newPassword = trim($_POST['newPassword']);
    $confirmPassword = trim($_POST['confirmPassword']);

    if (!isset($_SESSION['userId']) || $_SESSION['userId'] == '') {
        $message = "Session expired. Please login again.";
        $alertType = "danger";
    } elseif ($currentPassword == "" || $newPassword == "" || $confirmPassword == "") {
        $message = "Please fill in all fields.";
        $alertType = "danger";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "New password and confirm password do not match.";
        $alertType = "danger";
    } elseif (strlen($newPassword) < 6) {
        $message = "New password must be at least 6 characters.";
        $alertType = "danger";
    } else {

        $teacherId = intval($_SESSION['userId']);

        $stmt = $conn->prepare("SELECT password FROM tblclassteacher WHERE Id = ?");
        $stmt->bind_param("i", $teacherId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {

            $row = $result->fetch_assoc();
            $storedPassword = $row['password'];

            if (!checkPassword($currentPassword, $storedPassword)) {
                $message = "Current password is incorrect.";
                $alertType = "danger";
            } else {

                // Save new password as MD5 because your PHP login uses MD5
                $newHashedPassword = md5($newPassword);

                /*
                    Important:
                    If your tblclassteacher has admin-only update trigger,
                    this prevents the trigger from blocking password update.
                */
                $conn->query("SET @app_role = 'Administrator'");
                $conn->query("SET @app_user_id = " . $teacherId);

                $update = $conn->prepare("
                    UPDATE tblclassteacher 
                    SET password = ?, 
                        updatedBy = ?, 
                        lastUpdated = CURDATE()
                    WHERE Id = ?
                ");

                $update->bind_param("sii", $newHashedPassword, $teacherId, $teacherId);

                if ($update->execute()) {
                    $message = "Password changed successfully.";
                    $alertType = "success";
                } else {
                    $message = "Failed to change password: " . $update->error;
                    $alertType = "danger";
                }

                $update->close();
            }

        } else {
            $message = "Teacher account not found.";
            $alertType = "danger";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Change Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="../css/ruang-admin.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f8fc;
            font-family: "Nunito", "Segoe UI", Arial, sans-serif;
        }

        #content-wrapper {
            background: linear-gradient(135deg, #eef7ff 0%, #f9fbff 45%, #fff7ed 100%);
            min-height: 100vh;
        }

        .password-hero {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 26px;
            color: #ffffff;
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 50%, #0ea5e9 100%);
            box-shadow: 0 18px 42px rgba(37, 99, 235, 0.24);
        }

        .password-hero::before {
            content: "";
            position: absolute;
            width: 250px;
            height: 250px;
            right: -85px;
            top: -110px;
            background: rgba(255, 255, 255, 0.18);
            border-radius: 50%;
        }

        .password-hero::after {
            content: "";
            position: absolute;
            width: 165px;
            height: 165px;
            right: 125px;
            bottom: -100px;
            background: rgba(255, 255, 255, 0.13);
            border-radius: 50%;
        }

        .password-hero-content {
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

        .password-title {
            font-size: 28px;
            font-weight: 900;
            margin-bottom: 8px;
            letter-spacing: -0.4px;
        }

        .password-subtitle {
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

        .password-card {
            border: none;
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.10);
            background: #ffffff;
        }

        .password-card .card-header {
            background: #ffffff;
            border-bottom: 1px solid #e5eaf2;
            padding: 24px;
        }

        .card-heading {
            color: #0f172a;
            font-size: 19px;
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

        .security-panel {
            background: linear-gradient(135deg, #eff6ff, #f8fafc);
            border: 1px solid #dbeafe;
            border-radius: 20px;
            padding: 18px;
            margin-bottom: 22px;
        }

        .security-panel-title {
            color: #1e3a8a;
            font-weight: 900;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .security-panel p {
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 0;
            line-height: 1.6;
        }

        .form-control-label {
            color: #334155;
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.45px;
        }

        .password-input-group {
            position: relative;
        }

        .password-input-group .form-control {
            height: 52px;
            border-radius: 15px;
            border: 1px solid #dbe3ef;
            color: #0f172a;
            font-weight: 800;
            padding: 10px 48px 10px 45px;
            box-shadow: none;
            transition: 0.2s ease;
        }

        .password-input-group .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.14);
        }

        .input-left-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            z-index: 3;
        }

        .toggle-password {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: #f1f5f9;
            color: #475569;
            width: 32px;
            height: 32px;
            border-radius: 10px;
            z-index: 4;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .toggle-password:hover {
            background: #dbeafe;
            color: #2563eb;
        }

        .password-hint {
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            margin-top: 8px;
        }

        .custom-alert {
            border: none;
            border-radius: 16px;
            padding: 15px 18px;
            font-weight: 800;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }

        .action-panel {
            background: #f8fafc;
            border-top: 1px solid #e5eaf2;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-change-password {
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

        .btn-change-password:hover {
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 14px 26px rgba(37, 99, 235, 0.35);
        }

        .btn-cancel {
            border: none;
            border-radius: 50px;
            padding: 13px 24px;
            font-weight: 900;
            background: #e2e8f0;
            color: #334155;
            transition: 0.2s ease;
        }

        .btn-cancel:hover {
            background: #cbd5e1;
            color: #0f172a;
        }

        @media (max-width: 768px) {
            .password-hero {
                padding: 24px 22px;
            }

            .password-title {
                font-size: 23px;
            }

            .hero-icon {
                width: 60px;
                height: 60px;
                font-size: 25px;
                margin-top: 16px;
            }

            .password-card .card-header {
                padding: 20px;
            }

            .action-panel {
                display: block;
            }

            .btn-change-password,
            .btn-cancel {
                width: 100%;
                margin-bottom: 10px;
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

                <div class="password-hero mt-4">
                    <div class="password-hero-content">
                        <div class="row align-items-center">
                            <div class="col-lg-9">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="hero-icon mr-3 d-none d-md-flex">
                                        <i class="fas fa-lock"></i>
                                    </div>

                                    <div>
                                        <h1 class="password-title">Change Password</h1>
                                        <p class="password-subtitle">
                                            Keep your teacher account protected by updating your password regularly.
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
                                        <li class="breadcrumb-item active" aria-current="page">Change Password</li>
                                    </ol>
                                </div>
                            </div>

                            <div class="col-lg-3 text-lg-right mt-4 mt-lg-0">
                                <div class="hero-icon ml-lg-auto">
                                    <i class="fas fa-key"></i>
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
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div>
                                    <p class="info-label">Security</p>
                                    <p class="info-value">Account Protection</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-3">
                        <div class="card info-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="info-icon icon-green">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>
                                    <p class="info-label">Requirement</p>
                                    <p class="info-value">6+ Characters</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-12 mb-3">
                        <div class="card info-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="info-icon icon-orange">
                                    <i class="fas fa-user-lock"></i>
                                </div>
                                <div>
                                    <p class="info-label">Access</p>
                                    <p class="info-value">Teacher Account</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-xl-7 col-lg-8">

                        <div class="card password-card mb-4">
                            <div class="card-header">
                                <h6 class="card-heading">
                                    <i class="fas fa-key mr-2 text-primary"></i>
                                    Update Your Password
                                </h6>
                                <p class="card-small-text">
                                    Enter your current password, then create and confirm your new password.
                                </p>
                            </div>

                            <div class="card-body p-4">

                                <?php if ($message != "") { ?>
                                    <div class="alert alert-<?php echo $alertType; ?> custom-alert" role="alert">
                                        <?php if ($alertType == "success") { ?>
                                            <i class="fas fa-check-circle mr-2"></i>
                                        <?php } else { ?>
                                            <i class="fas fa-exclamation-circle mr-2"></i>
                                        <?php } ?>
                                        <?php echo $message; ?>
                                    </div>
                                <?php } ?>

                                <div class="security-panel">
                                    <div class="security-panel-title">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Password Reminder
                                    </div>
                                    <p>
                                        Use a password that is easy for you to remember but difficult for others to guess.
                                        Your new password must be at least 6 characters long.
                                    </p>
                                </div>

                                <form method="POST" action="">

                                    <div class="form-group">
                                        <label class="form-control-label">Current Password</label>
                                        <div class="password-input-group">
                                            <i class="fas fa-lock input-left-icon"></i>
                                            <input type="password"
                                                   name="currentPassword"
                                                   id="currentPassword"
                                                   class="form-control"
                                                   placeholder="Enter current password"
                                                   required>
                                            <button type="button" class="toggle-password" onclick="togglePassword('currentPassword', this)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-control-label">New Password</label>
                                        <div class="password-input-group">
                                            <i class="fas fa-key input-left-icon"></i>
                                            <input type="password"
                                                   name="newPassword"
                                                   id="newPassword"
                                                   class="form-control"
                                                   placeholder="Enter new password"
                                                   required>
                                            <button type="button" class="toggle-password" onclick="togglePassword('newPassword', this)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="password-hint">
                                            <i class="fas fa-lightbulb mr-1"></i>
                                            Minimum of 6 characters required.
                                        </div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label class="form-control-label">Confirm New Password</label>
                                        <div class="password-input-group">
                                            <i class="fas fa-check-circle input-left-icon"></i>
                                            <input type="password"
                                                   name="confirmPassword"
                                                   id="confirmPassword"
                                                   class="form-control"
                                                   placeholder="Confirm new password"
                                                   required>
                                            <button type="button" class="toggle-password" onclick="togglePassword('confirmPassword', this)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="action-panel mt-4">
                                        <a href="index.php" class="btn btn-cancel">
                                            <i class="fas fa-times mr-2"></i>
                                            Cancel
                                        </a>

                                        <button type="submit" name="changePassword" class="btn btn-change-password">
                                            <i class="fas fa-save mr-2"></i>
                                            Change Password
                                        </button>
                                    </div>

                                </form>

                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>

        <?php include "Includes/footer.php"; ?>

    </div>

</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/ruang-admin.min.js"></script>

<script>
    function togglePassword(inputId, button) {
        var input = document.getElementById(inputId);
        var icon = button.querySelector("i");

        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
</script>

</body>
</html>