<?php

error_reporting(0);
require '../helpers/database-connection.php';

// Output as JSON
header('Content-Type: application/json');

$errorList = array();

if (isset($_POST['requestFromApplication']) && strcmp($_POST['requestFromApplication'], 'true') == 0) {
    // Request has originated from mobile application
    // Create database connection
    if (!($link = GetConnection())) {
        // Database connection error occurred
        array_push($errorList, 200);
    } else {
        // Server-side validation
        // Validate user ID
        $userId = mysqli_real_escape_string($link, stripslashes($_POST['userId']));
        if (!preg_match('/^[0-9]{1,}$/', $userId)) {
            // User ID not valid
            array_push($errorList, 300);
        }

        // Validate friend user ID
        $friendUserId = mysqli_real_escape_string($link, stripslashes($_POST['friendUserId']));
        if (!preg_match('/^[0-9]{1,}$/', $friendUserId)) {
            // Friend user ID not valid
            array_push($errorList, 400);
        }

        // Validate run ID
        $runId = mysqli_real_escape_string($link, stripslashes($_POST['runId']));
        if (!preg_match('/^[0-9]{1,}$/', $runId)) {
            // Run ID not valid
            array_push($errorList, 500);
        }

        // Get message
        $message = mysqli_real_escape_string($link, stripslashes($_POST['message']));

        // Check if users are friends
        if (CheckIfFriends($link, $userId, $friendUserId)) {
            // Users are friends
            // Save challenge
            if (SaveChallenge($link, $friendUserId, $runId, $message)) {
                // Successfully saved
                // Close connection
                CloseConnection($link);
            } else {
                // Database error occurred
                array_push($errorList, 201);
            }
        } else {
            // Users are not friends
            // Can only send challenges to friends
            array_push($errorList, 601);
        }
    }

    // Check for and display any errors
    if (count($errorList) > 0) {
        // Errors
        $outputType = 'Error';
    } else {
        // No errors
        $outputType = 'Success';
    }
} else {
    // Unauthorised request
    $outputType = 'Error';
    array_push($errorList, 100);

    // Redirect user
    header('Location: http://www.mtickner.co.uk/runace/');
}

// Set JSON response
$outputJson = array('OutputType' => $outputType, 'Details' => $errorList);
exit(json_encode($outputJson));

?>