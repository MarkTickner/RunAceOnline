<?php

error_reporting(0);
require '../helpers/database-connection.php';

// Output as JSON
header('Content-Type: application/json');

$errorList = array();
$challengeDetails = array();

if (isset($_POST['requestFromApplication']) && strcmp($_POST['requestFromApplication'], 'true') == 0) {
    // Request has originated from mobile application
    // Create database connection
    if (!($link = GetConnection())) {
        // Database connection error occurred
        array_push($errorList, 200);
    } else {
        // Server-side validation
        // Validate challenge ID
        $challengeId = mysqli_real_escape_string($link, stripslashes($_POST['challengeId']));
        if (!preg_match('/^[0-9]{1,}$/', $challengeId)) {
            // Challenge ID not valid
            array_push($errorList, 600);
        }

        // Verify set read boolean
        $setRead = strcmp($_POST['setRead'], 'true') == 0;

        // Get challenge from database
        array_push($challengeDetails, GetChallengeByChallengeId($link, $challengeId, $setRead));

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
        $outputDetailsList = $challengeDetails;
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