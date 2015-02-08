<?php

error_reporting(0);
require 'database-connection.php';

// Output as plain text
header('Content-Type: application/json');

$errorList = array();
$runDetails = array();

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

        // Validate distance total
        $distanceTotal = mysqli_real_escape_string($link, stripslashes($_POST['distanceTotal']));
        if (!preg_match('/^[0-9]{1,3}.[0-9]{2}$/', $distanceTotal)) {
            // Distance not valid
            array_push($errorList, 501);
        }

        // Validate total time
        $totalTime = mysqli_real_escape_string($link, stripslashes($_POST['totalTime']));
        if (!preg_match('/^[0-9]{1,}$/', $totalTime)) {
            // Time not valid
            array_push($errorList, 502);
        }

        if (!count($errorList) > 0) {
            // Save run
            if (SaveRun($link, $userId, $distanceTotal, $totalTime)) {
                // Successfully saved
                // Get run from database
                array_push($runDetails, GetRunByRunId($link, mysqli_insert_id($link)));

                // Get challenge success boolean
                $challengeSuccess = strcmp($_POST['challengeSuccess'], 'true') == 0;
                if ($challengeSuccess) {
                    // Validate challenge ID
                    $challengeId = mysqli_real_escape_string($link, stripslashes($_POST['challengeId']));

                    if (!preg_match('/^[0-9]{1,}$/', $challengeId)) {
                        // Challenge ID not valid
                        array_push($errorList, 600);
                    } else {
                        SetChallengeCompleted($link, $challengeId);
                    }
                }

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
        $outputDetailsList = $runDetails;
    }
} else {
    // Unauthorised request
    $outputType = 'Error';
    array_push($errorList, 100);

    $outputDetailsList = $errorList;

    // Redirect user
    header('Location: http://stuweb.cms.gre.ac.uk/~tm112/project/');
}

// Set JSON response
$outputJson = array('OutputType' => $outputType, 'Details' => $outputDetailsList);
exit(json_encode($outputJson));

?>