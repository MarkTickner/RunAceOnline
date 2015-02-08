<?php

error_reporting(0);
require 'database-connection.php';

// Output as plain text
header('Content-Type: application/json');

$errorList = array();

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
                $userId = mysqli_insert_id($link);

                // Send confirmation email
                $subject = 'RunAce Registration';
                $message = '<html>
                                    <head>
                                        <title>RunAce</title>
                                        <meta name="author" content="Mark Tickner">
                                        <meta name="viewport" content="width=device-width, initial-scale=1">
                                    </head>
                                    <body style="font-family: \'Arial\', sans-serif; background-color: #EEEEEE;">
                                        <div style="background-color: #FFFFFF; padding: 2rem; border-radius: 4px;">
                                            <h1 style="margin-top: 0;"><img src="http://stuweb.cms.gre.ac.uk/~tm112/project/images/text-logo-small.png" alt="RunAce" height="66" width="200"></h1>
                                            <p>' . $name . ',</p>
                                            <p>Welcome to RunAce, the best way to increase your running motivation!</p>
                                        </div>
                                    </body>
                                </html>';
                $headers = 'From: RunAce <runace@mtickner.co.uk>' . "\r\n";
                $headers .= 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                mail($email, $subject, $message, $headers);

                // Send email to me
                $subject = 'New RunAce Registration';
                $message = '<html>
                                    <head>
                                        <title>RunAce</title>
                                        <meta name="author" content="Mark Tickner">
                                        <meta name="viewport" content="width=device-width, initial-scale=1">
                                    </head>
                                    <body style="font-family: \'Arial\', sans-serif; background-color: #EEEEEE;">
                                        <div style="background-color: #FFFFFF; padding: 2rem; border-radius: 4px;">
                                            <h1 style="margin-top: 0;"><img src="http://stuweb.cms.gre.ac.uk/~tm112/project/images/text-logo-small.png" alt="RunAce" height="66" width="200"></h1>
                                            <p>ID: ' . $userId . '</p>
                                            <p>Name: ' . $name . '</p>
                                            <p>Email: ' . $email . '</p>
                                        </div>
                                    </body>
                                </html>';
                mail('runace@mtickner.co.uk', $subject, $message, $headers);

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
    } else {
        // No errors
        $outputType = 'Success';
        array_push($errorList, $userId);
    }
} else {
    // Unauthorised request
    $outputType = 'Error';
    array_push($errorList, 100);

    // Redirect user
    header('Location: http://stuweb.cms.gre.ac.uk/~tm112/project/');
}

// Set JSON response
$outputJson = array('OutputType' => $outputType, 'Details' => $errorList);
exit(json_encode($outputJson));

?>