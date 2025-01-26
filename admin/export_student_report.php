<?php
require_once dirname(__FILE__) . '/../config.php';
require_once 'vendor/autoload.php'; // You'll need to install PhpSpreadsheet via Composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if(isset($_GET['student_id']) && isset($_GET['format'])) {
    $student_id = $_GET['student_id'];
    $format = $_GET['format'];

    // Get student details
    $student_query = "SELECT * FROM students WHERE id = ?";
    $stmt = mysqli_prepare($conn, $student_query);
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $student = mysqli_stmt_get_result($stmt)->fetch_assoc();

    // Get reservation history
    $history_query = "SELECT * FROM pc_reservations WHERE student_id = ? ORDER BY start_time DESC";
    $stmt = mysqli_prepare($conn, $history_query);
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $reservations = mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);

    if($format === 'excel') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set student details
        $sheet->setCellValue('A1', 'Student Report');
        $sheet->setCellValue('A3', 'Name:');
        $sheet->setCellValue('B3', $student['first_name'] . ' ' . $student['last_name']);
        $sheet->setCellValue('A4', 'Registration Number:');
        $sheet->setCellValue('B4', $student['registration_number']);

        // Set headers for reservation history
        $sheet->setCellValue('A6', 'PC Number');
        $sheet->setCellValue('B6', 'Start Time');
        $sheet->setCellValue('C6', 'End Time');
        $sheet->setCellValue('D6', 'Duration');

        // Fill data
        $row = 7;
        foreach($reservations as $reservation) {
            $duration = strtotime($reservation['end_time']) - strtotime($reservation['start_time']);
            $duration_formatted = floor($duration/3600).'h '.(($duration/60)%60).'m';

            $sheet->setCellValue('A'.$row, 'PC '.$reservation['pc_number']);
            $sheet->setCellValue('B'.$row, $reservation['start_time']);
            $sheet->setCellValue('C'.$row, $reservation['end_time']);
            $sheet->setCellValue('D'.$row, $duration_formatted);
            $row++;
        }

        // Generate Excel file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="student_report.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    } else if($format === 'pdf') {
        // Generate PDF using TCPDF or other PDF library
        // Implementation depends on your preferred PDF library
    }
}
?>
