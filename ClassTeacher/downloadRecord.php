<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$filename = "Attendance list";
$dateTaken = date("Y-m-d");

header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=".$filename."-report.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>

<table style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;">
    <tr>
        <td colspan="11" style="
            background-color: #2563eb;
            color: #ffffff;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            padding: 16px;
            border: 1px solid #1e40af;
        ">
            STUDENT ATTENDANCE REPORT
        </td>
    </tr>

    <tr>
        <td colspan="11" style="
            background-color: #eff6ff;
            color: #1e3a8a;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            border: 1px solid #bfdbfe;
        ">
            Date Generated: <?php echo date("F d, Y"); ?>
        </td>
    </tr>

    <tr>
        <td colspan="11" style="height: 15px;"></td>
    </tr>

    <thead>
        <tr>
            <th style="background-color:#0f172a; color:#ffffff; padding:10px; border:1px solid #334155;">#</th>
            <th style="background-color:#0f172a; color:#ffffff; padding:10px; border:1px solid #334155;">First Name</th>
            <th style="background-color:#0f172a; color:#ffffff; padding:10px; border:1px solid #334155;">Last Name</th>
            <th style="background-color:#0f172a; color:#ffffff; padding:10px; border:1px solid #334155;">Other Name</th>
            <th style="background-color:#0f172a; color:#ffffff; padding:10px; border:1px solid #334155;">Admission No</th>
            <th style="background-color:#0f172a; color:#ffffff; padding:10px; border:1px solid #334155;">Class</th>
            <th style="background-color:#0f172a; color:#ffffff; padding:10px; border:1px solid #334155;">Class Arm</th>
            <th style="background-color:#0f172a; color:#ffffff; padding:10px; border:1px solid #334155;">Session</th>
            <th style="background-color:#0f172a; color:#ffffff; padding:10px; border:1px solid #334155;">Term</th>
            <th style="background-color:#0f172a; color:#ffffff; padding:10px; border:1px solid #334155;">Status</th>
            <th style="background-color:#0f172a; color:#ffffff; padding:10px; border:1px solid #334155;">Date</th>
        </tr>
    </thead>

    <tbody>
<?php 
$cnt = 1;			

$ret = mysqli_query($conn,"SELECT tblattendance.Id,tblattendance.status,tblattendance.dateTimeTaken,tblclass.className,
        tblclassarms.classArmName,tblsessionterm.sessionName,tblsessionterm.termId,tblterm.termName,
        tblstudents.firstName,tblstudents.lastName,tblstudents.otherName,tblstudents.admissionNumber
        FROM tblattendance
        INNER JOIN tblclass ON tblclass.Id = tblattendance.classId
        INNER JOIN tblclassarms ON tblclassarms.Id = tblattendance.classArmId
        INNER JOIN tblsessionterm ON tblsessionterm.Id = tblattendance.sessionTermId
        INNER JOIN tblterm ON tblterm.Id = tblsessionterm.termId
        INNER JOIN tblstudents ON tblstudents.admissionNumber = tblattendance.admissionNo
        where tblattendance.dateTimeTaken = '$dateTaken' and tblattendance.classId = '$_SESSION[classId]' and tblattendance.classArmId = '$_SESSION[classArmId]'");

if(mysqli_num_rows($ret) > 0 )
{
    while ($row = mysqli_fetch_array($ret)) 
    { 
        if($row['status'] == '1'){
            $status = "Present";
            $statusBg = "#dcfce7";
            $statusColor = "#166534";
        } else {
            $status = "Absent";
            $statusBg = "#fee2e2";
            $statusColor = "#991b1b";
        }

        echo '  
        <tr>  
            <td style="padding:9px; border:1px solid #cbd5e1; text-align:center; font-weight:bold; background-color:#f8fafc;">'.$cnt.'</td> 
            <td style="padding:9px; border:1px solid #cbd5e1;">'.$row['firstName'].'</td> 
            <td style="padding:9px; border:1px solid #cbd5e1;">'.$row['lastName'].'</td> 
            <td style="padding:9px; border:1px solid #cbd5e1;">'.$row['otherName'].'</td> 
            <td style="padding:9px; border:1px solid #cbd5e1; font-weight:bold; color:#1d4ed8;">'.$row['admissionNumber'].'</td> 
            <td style="padding:9px; border:1px solid #cbd5e1;">'.$row['className'].'</td> 
            <td style="padding:9px; border:1px solid #cbd5e1;">'.$row['classArmName'].'</td>	
            <td style="padding:9px; border:1px solid #cbd5e1;">'.$row['sessionName'].'</td>	 
            <td style="padding:9px; border:1px solid #cbd5e1;">'.$row['termName'].'</td>	
            <td style="padding:9px; border:1px solid #cbd5e1; background-color:'.$statusBg.'; color:'.$statusColor.'; font-weight:bold; text-align:center;">'.$status.'</td>	 	
            <td style="padding:9px; border:1px solid #cbd5e1; text-align:center;">'.$row['dateTimeTaken'].'</td>	 					
        </tr>';
        
        $cnt++;
    }
}
else
{
    echo '
    <tr>
        <td colspan="11" style="
            padding: 18px;
            text-align: center;
            color: #991b1b;
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            font-weight: bold;
            font-size: 14px;
        ">
            No attendance record found for today.
        </td>
    </tr>';
}
?>
    </tbody>
</table>

<table style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; margin-top: 20px;">
    <tr>
        <td colspan="11" style="
            background-color: #f8fafc;
            color: #475569;
            font-size: 12px;
            text-align: center;
            padding: 10px;
            border: 1px solid #cbd5e1;
        ">
            This report was generated automatically by the Student Attendance Management System.
        </td>
    </tr>
</table>