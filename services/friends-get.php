<?php

error_reporting(0);
require '../helpers/database-connection.php';

// Output as JSON
header('Content-Type: application/json');

$errorList = array();
$friendsDetails = array();

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

        // Get friends from database
        $friendsDetails = GetFriendsByUserId($link, $userId);

        // Close connection
        CloseConnection($link);
    }

    // Check for and display any errors
    if (count($errorList) > 0) {
        // Errors
        $outputType = 'Error';
        $outputDetailsList = $errorList;
    } else {
        // No errors
        $outputType = 'Success';
        $outputDetailsList = $friendsDetails;
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