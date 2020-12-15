<?php
require_once('src/PHPMailer.php');

use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// Only process POST reqeusts.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form fields and remove whitespace.
    $name = strip_tags(trim($_POST["name"]));
    $name = str_replace(array("\r", "\n"), array(" ", " "), $name);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $description = trim($_POST["description"]);
    $company = trim($_POST["company"]);
    $product = trim($_POST["product"]);
    $serial_no = trim($_POST["serial_no"]);

    // Check that data was sent to the mailer.
    if (empty($name) or empty($company) or empty($product) or empty($description) or !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Set a 400 (bad request) response code and exit.
        http_response_code(400);
        echo "Please complete the form and try again.";
        exit;
    }

    // $sql = "INSERT INTO `queries`(`name`, `email`, `number`, `message`,  `time`) VALUES ('$name','$email','$number', '$message', CONVERT_TZ(CURRENT_TIMESTAMP, '-07:00', '+05:30'))";
    $sql = "INSERT INTO `queries`(`name`, `email`, `number`, `message`, `type`, `time`) VALUES ('$name','$email','$number', '$message', 0, CURRENT_TIMESTAMP)";
    require 'db.php';
    $conn = DB::getConnection();
    if ($conn->query($sql) === true) {
        // Set the recipient email address.
        // FIXME: Update this to your desired email address.

        $bodytext = '<!DOCTYPE html>
        <html>
        
        <head>
            <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
        </head>
        
        <body style="font-family: Arial, Helvetica, sans-serif;margin:0px;background-color: #ffffff;">
            <table
                style="background-color: #eeeeee;padding: 8px 16px;width: 100%;box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.12), 0 1px 5px 0 rgba(0,0,0,0.2);">
                <tr>
                    <td><img src="https://www.#.com/assets/logo.png" height="50px" alt="Allianz Cyber Security" /></td>
                    <td style="line-height: 50px;vertical-align: top; margin:0px; font-size: 32px; font-weight: 500;">Enquiry
                        from Allianz Cyber Security</td>
                </tr>
            </table>
            <table style="padding: 8px 16px;width: 100%;font-weight: 500;" cellspacing="10">
                <tr>
                    <td style="color: #1a237e;width: 30%">Name:</td>
                    <td style="width: 70%;">' . $name . '</td>
                </tr>
                <tr>
                    <td style="color: #1a237e;width: 30%">Email:</td>
                    <td style="width: 70%;">' . $email . '</td>
                </tr>
                <tr>
                    <td style="color: #1a237e;width: 30%">Number:</td>
                    <td style="width: 70%;">' . $number . '</td>
                </tr>
                <tr>
                    <td style="color: #1a237e;width: 30%">Company:</td>
                    <td style="width: 70%;">' . $company . '</td>
                </tr>
                <tr>
                    <td style="color: #1a237e;width: 30%">Product:</td>
                    <td style="width: 70%;">' . $product . '</td>
                </tr>
                <?php if(!empty($serial_no)) { ?>
                <tr>
                    <td style="color: #1a237e;width: 30%">Serial Number:</td>
                    <td style="width: 70%;">' . $serial_no . '</td>
                </tr>
                <?php } ?>
                <tr>
                    <td style="color: #1a237e;width: 30%">Message:</td>
                    <td style="width: 70%;">' . $description . '</td>
                </tr>
            </table>
            <table style="background-color: #1a237e;padding: 8px 16px;width: 100%;color: #ffffff;">
                <tr>
                    <td style="line-height: 50px;vertical-align: top; margin:0px; font-size: 24px; font-weight: 500;"><a
                            href="https://www.#.com" style="color: #ffffff;text-decoration:none">Allianz Cyber Security</a>
                    </td>
                    <td><a href="https://www.#.com/about" style="color: #ffffff;text-decoration:none">About</a>
                    </td>
                    <td><a href="https://www.#.com/contact" style="color: #ffffff;text-decoration:none">Contact</a>
                    </td>
                </tr>
            </table>
        </body>
        
        </html>';

        $email = new PHPMailer();
        $email->SetFrom($email, $name); //Name is optional
        $email->Subject   = "New enquiry from Allianz Cyber Security.";
        $email->Body      = $bodytext;
        $email->AddAddress("rizwan.raza987@gmail.com");
        // $email->AddAddress("rizwan.raza987@gmail.com");

        // Attach multiple files one by one
        for ($ct = 0, $ctMax = count($_FILES['attachments']['tmp_name']); $ct < $ctMax; $ct++) {
            // Extract an extension from the provided filename
            $ext = PHPMailer::mb_pathinfo($_FILES['attachments']['name'], PATHINFO_EXTENSION);
            // Define a safe location to move the uploaded file to, preserving the extension
            $uploadfile = tempnam(sys_get_temp_dir(), hash('sha256', $_FILES['attachments']['name'][$ct])) . '.' . $ext;
            $filename = $_FILES['attachments']['name'][$ct];
            if (move_uploaded_file($_FILES['attachments']['tmp_name'][$ct], $uploadfile)) {
                if (!$email->addAttachment($uploadfile, $filename)) {
                    http_response_code(500);
                    echo 'Failed to attach file ' . $filename;
                }
            } else {
                http_response_code(500);
                echo 'Failed to move file to ' . $uploadfile;
            }
        }
        if ($email->send()) {
            // Set a 200 (okay) response code.
            http_response_code(200);
            echo "Thank You! Your message has been sent.";
        } else {
            // Set a 500 (internal server error) response code.
            http_response_code(500);
            echo "Oops! Something went wrong and we couldn't send your message.";
        }
    } else {
        // Set a 500 (internal server error) response code.
        http_response_code(500);
        echo "Something went wrong! Coudn't connect to our database";
    }
} else {
    // Not a POST request, set a 403 (forbidden) response code.
    http_response_code(403);
    echo "There was a problem with your submission, please try again.";
}
