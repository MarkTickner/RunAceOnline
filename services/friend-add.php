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
        // Validate user ID
        $userId = mysqli_real_escape_string($link, stripslashes($_POST['userId']));
        if (!preg_match('/^[0-9]{1,}$/', $userId)) {
            // User ID not valid
            array_push($errorList, 300);
        }

        // Validate friend email
        $friendEmail = mysqli_real_escape_string($link, stripslashes($_POST['friendEmail']));
        if (preg_match('/^$|\s+/', $friendEmail)) {
            // Friend email address not entered
            array_push($errorList, 402);
        } elseif (!preg_match('/^([\w\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/', $friendEmail)) {
            // Friend email address not valid
            array_push($errorList, 403);
        }

        // Validate random string
        $verificationString = mysqli_real_escape_string($link, stripslashes($_POST['verificationString']));
        if (!preg_match('/^[A-Z0-9]{20}$/', $verificationString)) {
            // Verification string not valid
            array_push($errorList, 401);
        }

        // Get inviting user details
        $user = GetUserByUserId($link, $userId);

        // Check if friend is registered
        if (CheckIfEmailAlreadyRegistered($link, $friendEmail)) {
            // Friend is registered
            $friend = GetUserByEmail($link, $friendEmail);

            if (!CheckIfFriendAlreadyAdded($link, $userId, $friend["ID"])) {
                // Save friend request
                if (SaveFriendRequest($link, $userId, $friend["ID"], $verificationString)) {
                    // Successfully saved
                    // Get new record ID
                    $id = mysqli_insert_id($link);

                    // Close connection
                    CloseConnection($link);

                    // Send invitation email
                    $body = '<p>You have been added as a friend by ' . $user['NAME'] . '.</p>
                             <p><a href="https://stuweb.cms.gre.ac.uk/~tm112/project/friend-accept.php?id=' . $id . '&vs=' . $verificationString . '" style="display: inline-block; height: 38px; padding: 0 30px; color: #555; text-align: center; font-size: 1rem; font-weight: 600; line-height: 38px; letter-spacing: .1rem; text-transform: uppercase; text-decoration: none; border-radius: 4px; border: 1px solid #bbb; box-sizing: border-box; margin-bottom: 1rem;">Accept</a></p>';
                    SendEmail($friendEmail, 'RunAce Friend Request', $body);

                    array_push($outputDetailsList, 'Sent');
                } else {
                    // Database error occurred
                    array_push($errorList, 201);
                }
            } else {
                array_push($outputDetailsList, 'Already');
            }
        } elseif (!count($errorList) > 0) {
            // Friend is not registered
            // Send invitation email
            $body = '<p>You have been invited by ' . $user['NAME'] . ' to join RunAce, the best way to increase your running motivation!</p>
                     <p><a href="https://stuweb.cms.gre.ac.uk/~tm112/project/" style="display: inline-block; height: 38px; padding: 0 30px; color: #555; text-align: center; font-size: 1rem; font-weight: 600; line-height: 38px; letter-spacing: .1rem; text-transform: uppercase; text-decoration: none; border-radius: 4px; border: 1px solid #bbb; box-sizing: border-box; margin-bottom: 1rem;">Get the Android App</a></p>';
            SendEmail($friendEmail, 'RunAce Invitation', $body);

            array_push($outputDetailsList, 'Invited');
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