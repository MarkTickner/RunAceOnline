<?php

error_reporting(0);
require '../helpers/database-connection.php';
require '../helpers/email-helper.php';

// Output as JSON
header('Content-Type: application/json');

$errorList = $userDetails = array();

if (isset($_POST['requestFromApplication']) && strcmp($_POST['requestFromApplication'], 'true') == 0) {
    // Request has originated from mobile application
    $userId = 0;

    // Create database connection
    if (!($link = GetConnection())) {
        // Database connection error occurred
        array_push($errorList, 200);
    } else {
        // Server-side validation
        // Validate name
        $name = mysqli_real_escape_string($link, stripslashes($_POST['name']));
        if (preg_match('/^\s*$/', $name)) {
            // Name not entered
            array_push($errorList, 305);
        }

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
        } elseif (strlen($password) < 8) {
            // Password must be at least 8 characters long
            array_push($errorList, 306);
        }

        // Check if email is already registered
        if (CheckIfEmailAlreadyRegistered($link, $email)) {
            // Email entered is already registered
            array_push($errorList, 307);
        } elseif (!count($errorList) > 0) {
            // Email is not already registered, create account
            $salt = openssl_random_pseudo_bytes(16);
            $password = crypt($password, $salt);

            $salt = mysqli_real_escape_string($link, stripslashes($salt));

            if (RegisterUser($link, $name, $email, $password, $salt)) {
                // Successfully registered
                // Get user details from database
                $userDetails = GetUserByUserId($link, mysqli_insert_id($link));

                // Send confirmation email
                $body = '<p>' . $userDetails['NAME'] . ',</p>
                         <p>Welcome to RunAce, the best way to increase your running motivation!</p>';
                SendEmail($userDetails['EMAIL'], 'RunAce Registration', $body);

                // Send email to me
                $body = '<p>ID: ' . $userDetails['ID'] . '</p>
                            <p>Name: ' . $userDetails['NAME'] . '</p>
                            <p>Email: ' . $userDetails['EMAIL'] . '</p>';
                SendEmail('runace@mtickner.co.uk', 'New RunAce Registration', $body);

                // Close connection
                CloseConnection($link);
            } else {
                // Database error occurred
                array_push($errorList, 201);
            }
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
    header('Location: https://stuweb.cms.gre.ac.uk/~tm112/project/');
}

// Set JSON response
$outputJson = array('OutputType' => $outputType, 'Details' => $outputDetailsList);
exit(json_encode($outputJson));

?>