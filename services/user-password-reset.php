<?php

error_reporting(0);
require '../helpers/database-connection.php';
require '../helpers/email-helper.php';

// Output as JSON
header('Content-Type: application/json');

$errorList = array();
$outputDetailsList = array();

if (isset($_POST['requestFromApplication']) && strcmp($_POST['requestFromApplication'], 'true') == 0) {
    // Request has originated from mobile application
    // Create database connection
    if (!($link = GetConnection())) {
        // Database connection error occurred
        array_push($errorList, 200);
    } else {
        // Server-side validation
        // Validate email
        $email = mysqli_real_escape_string($link, stripslashes($_POST['email']));
        if (preg_match('/^$|\s+/', $email)) {
            // Email address not entered
            array_push($errorList, 402);
        } elseif (!preg_match('/^([\w\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/', $email)) {
            // Email address not valid
            array_push($errorList, 403);
        }

        // Get user details
        $user = GetUserByEmail($link, $email);

        // Generate verification string
        $verificationString = crypt($user['ID'] . $user['DATE_REGISTERED'], $user['NAME']);

        // Close connection
        CloseConnection($link);

        // Send password reset email
        $body = '<p>' . $user['NAME'] . ',</p>
                 <p>A request to reset your password has been made. Click the below link to reset it or ignore this email if you did not make the request yourself.</p>
                 <p><a href="https://stuweb.cms.gre.ac.uk/~tm112/project/password-reset.php?em=' . $user['EMAIL'] . '&vs=' . $verificationString . '" style="display: inline-block; height: 38px; padding: 0 30px; color: #555; text-align: center; font-size: 1rem; font-weight: 600; line-height: 38px; letter-spacing: .1rem; text-transform: uppercase; text-decoration: none; border-radius: 4px; border: 1px solid #bbb; box-sizing: border-box; margin-bottom: 1rem;">Reset Password</a></p>';
        SendEmail($email, 'RunAce Password Reset', $body);
    }

    // Check for and display any errors
    if (count($errorList) > 0) {
        // Errors
        $outputType = 'Error';
        $outputDetailsList = $errorList;
    } else {
        // No errors
        $outputType = 'Success';
    }
} else {
    // Unauthorised request
    $outputType = 'Error';
    array_push($errorList, 100);

    // Redirect user
    header('Location: https://stuweb.cms.gre.ac.uk/~tm112/project/');
}

// Set JSON response
$outputJson = array('OutputType' => $outputType, 'Details' => $outputDetailsList);
exit(json_encode($outputJson));

?>