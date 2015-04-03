<?php

error_reporting(0);
require '../helpers/database-connection.php';

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
        // Validate user ID 1
        $user1Id = mysqli_real_escape_string($link, stripslashes($_POST['user1Id']));
        if (!preg_match('/^[0-9]{1,}$/', $user1Id)) {
            // User ID 1 not valid
            array_push($errorList, 300);
        }

        // Validate user ID 2
        $user2Id = mysqli_real_escape_string($link, stripslashes($_POST['user2Id']));
        if (!preg_match('/^[0-9]{1,}$/', $user2Id)) {
            // User ID 2 not valid
            array_push($errorList, 300);
        }

        // Unfriend users
        if (Unfriend($link, $user1Id, $user2Id)) {
            // Successfully unfriended
            // Close connection
            CloseConnection($link);
        } else {
            // Database error occurred
            array_push($errorList, 201);
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