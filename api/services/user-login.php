<?php

error_reporting(0);
require '../helpers/database-connection.php';

// Output as JSON
header('Content-Type: application/json');

$errorList = $userDetails = array();

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
            array_push($errorList, 301);
        } elseif (!preg_match('/^([\w\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/', $email)) {
            // Email address not valid
            array_push($errorList, 302);
        }

        // Validate password
        $password = mysqli_real_escape_string($link, stripslashes($_POST['password']));
        if (preg_match('/^$|\s+/', $password)) {
            // Password not entered
            array_push($errorList, 303);
        }

        // Check for user in database
        if (LoginUser($link, $email, $password)) {
            // Logged in successfully
            // Get user details from database
            $userDetails = GetUserByEmail($link, $email);

            // Close connection
            CloseConnection($link);
        } else {
            // Authentication failed
            // Email and password match not found
            array_push($errorList, 304);
        }
    }

    // Check for and display any errors
    if (count($errorList) > 0) {
        // Errors
        $outputType = 'Error';
        $outputDetailsList = $errorList;
    } else {
        // No errors
        $outputType = 'Success';
        $outputDetailsList = $userDetails;
    }
} else {
    // Unauthorised request
    $outputType = 'Error';
    array_push($errorList, 100);

    $outputDetailsList = $errorList;

    // Redirect user
    header('Location: http://www.mtickner.co.uk/runace/');
}

// Set JSON response
$outputJson = array('OutputType' => $outputType, 'Details' => $outputDetailsList);
exit(json_encode($outputJson));

?>