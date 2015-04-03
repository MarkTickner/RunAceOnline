<?php

error_reporting(0);
require '../helpers/database-connection.php';

// Output as JSON
header('Content-Type: application/json');

$errorList = array();
$profileDetails = array();

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

        // Output user statistics
        $profileStatistics = GetUserProfileStatistics($link, $userId);
        $profileDetails['Score'] = $profileStatistics['SCORE'];
        $profileDetails['TotalRuns'] = $profileStatistics['TOTAL_RUNS'];
        $profileDetails['TotalChallenges'] = $profileStatistics['TOTAL_CHALLENGES'];;
        $profileDetails['TotalDistance'] = $profileStatistics['TOTAL_DISTANCE'];;
        $profileDetails['TotalTime'] = $profileStatistics['TOTAL_TIME'];;

        // Get badges from database
        $profileDetails['Badges'] = GetBadgesByUserId($link, $userId);

        // Close connection
        CloseConnection($link);
    }

    // Check for and display any errors
    if (count($errorList) > 0) {
        // Errors
        $outputType = 'Error';
        $profileDetails = $errorList;
    } else {
        // No errors
        $outputType = 'Success';
    }
} else {
    // Unauthorised request
    $outputType = 'Error';
    array_push($errorList, 100);

    $profileDetails = $errorList;

    // Redirect user
    header('Location: https://stuweb.cms.gre.ac.uk/~tm112/project/');
}

// Set JSON response
$outputJson = array('OutputType' => $outputType, 'Details' => $profileDetails);
exit(json_encode($outputJson));

?>