<?php

error_reporting(0);
require 'helpers/database-connection.php';

$errorList = array();

$id = $_GET['id'];
$vs = $_GET['vs'];

if (isset($id) && isset($vs)) {
    // Request has originated from valid link
    // Create database connection
    if (!($link = GetConnection())) {
        // Database connection error occurred
        array_push($errorList, 200);
    } else {
        // Server-side validation
        // Validate friend ID
        $id = mysqli_real_escape_string($link, stripslashes($id));
        if (!preg_match('/^[0-9]{1,}$/', $id)) {
            // Friend ID not valid
            array_push($errorList, 400);
        }

        // Validate random string
        $vs = mysqli_real_escape_string($link, stripslashes($vs));
        if (!preg_match('/^[A-Z0-9]{20}$/', $vs)) {
            // Verification string not valid
            array_push($errorList, 401);
        }

        if (!count($errorList) > 0) {
            if (!CheckIfFriendRequestAlreadyAccepted($link, $id)) {
                // Friend request not already accepted
                // Accept friend request
                if (AcceptFriendRequest($link, $id, $vs)) {
                    // Successfully accepted
                    // Close connection
                    CloseConnection($link);
                } else {
                    // Database error occurred
                    array_push($errorList, 201);
                }
            } else {
                // Friend request already accepted
                array_push($errorList, 404);
            }
        }
    }

    // Check for and display any errors
    if (count($errorList) > 0) {
        // Errors
        $output = '<p class="alert error">There was an error processing the request. Error code(s):';

        for ($i = 0; $i < count($errorList); $i++) {
            if ($i > 0) $output .= ',';

            $output .= ' ' . $errorList[$i] . '';
        }

        $output .= '.</p>';
    } else {
        // No errors
        $output = '<p class="alert success">Friend successfully accepted.</p>';
    }
} else {
    // Link not valid
    $output = '<p class="alert error">You followed an invalid link, please try again.</p>';
}

?>
<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb">

<head>
    <title>RunAce - Accept Friend</title>

    <?php include 'head.php'; ?>
</head>
<body>

<?php include 'page-header.php'; ?>

<?php echo $output; ?>

<p>
    Once you have accepted you are able to send and receive challenges with your friends. Get ready to increase
    your running motivation!
</p>

<?php include 'page-footer.php'; ?>

</body>
</html>