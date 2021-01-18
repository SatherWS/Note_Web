#!/usr/bin/php
<?php
require_once "/var/www/html/libs/PHPMailer.php";
require_once "/var/www/html/libs/SMTP.php";
require_once "/var/www/html/libs/Exception.php";
include_once "../config/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailScheduler {

    public function contactServer($assignee, $task, $deadline) {
        //Server settings
        try {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'swoopctms@gmail.com';                  // SMTP username
            $mail->Password   = 'qlfwsrjhrzzbfknk';                     // SMTP gmail app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom('swoopctms@gmail.com', 'Mailer');
            $mail->addAddress($assignee, 'Swoop User');
            $mail->addReplyTo('swoopctms@gmail.com', 'Information');

            // Content
            $mail->isHTML(true);                                         
            $mail->Subject = 'Email Reminder';
            $mail->Body    = '<h2>The task: '.$task.' is due on '.$deadline.'</h2>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            $mail->send();
            echo "EMAIL HAS BEEN SENT!\n";
        
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}

$db = new Database();
$curs = $db -> getConnection();

$sql = "select assignee, task_name, deadline from reminders order by exec_time";
$stmnt = mysqli_prepare($curs, $sql);
$stmnt -> execute();
$results = $stmnt -> get_result();
$data = mysqli_fetch_row($results);

$schedule = new EmailScheduler();
$schedule -> contactServer($data[0], $data[1], $data[2]);

/*
// Instantiation and passing `true` enables exceptions
$mail = new PHPMailer(true);
try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = 'swoopctms@gmail.com';                  // SMTP username
    $mail->Password   = 'qlfwsrjhrzzbfknk';                     // SMTP gmail app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

    //Recipients
    $mail->setFrom('swoopctms@gmail.com', 'Mailer');
    $mail->addAddress($argv[1], 'Swoop User');
    $mail->addReplyTo('swoopctms@gmail.com', 'Information');

    // Content
    $mail->isHTML(true);                                         // Set email format to HTML
    $mail->Subject = 'Email Reminder';
    $mail->Body    = '<h1>Task X is due</h1> This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    $mail->send();
    echo "email sent!";
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
*/
?>
