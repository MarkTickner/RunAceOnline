<?php

error_reporting(0);
require 'services/database-connection.php';

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
        $output = '<p class="alert success">Friend accepted successfully.</p>';
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
    <title>RunAce</title>

    <meta name="author" content="Mark Tickner"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>

    <link href="http://fonts.googleapis.com/css?family=Oxygen:700,400,300" rel="stylesheet" type="text/css"/>

    <link rel="stylesheet" href="css/normalize.css" type="text/css"/>
    <link rel="stylesheet" href="css/skeleton.css" type="text/css"/>
    <link rel="stylesheet" href="css/style.css" type="text/css"/>

    <link rel="apple-touch-icon" sizes="57x57" href="favicons/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="favicons/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="favicons/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="favicons/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="favicons/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="favicons/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="favicons/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="favicons/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="favicons/apple-touch-icon-180x180.png">
    <link rel="icon" type="image/png" href="favicons/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="favicons/android-chrome-192x192.png" sizes="192x192">
    <link rel="icon" type="image/png" href="favicons/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="favicons/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="favicons/android-chrome-manifest.json">
    <link rel="shortcut icon" href="favicons/favicon.ico">
    <meta name="msapplication-TileColor" content="#2a69b2">
    <meta name="msapplication-TileImage" content="favicons/mstile-144x144.png">
    <meta name="msapplication-config" content="favicons/browserconfig.xml">
    <meta name="theme-color" content="#2a69b2">
</head>
<body>

<div class="container">
    <div class="row">
        <div class="column">

            <h1><img src="images/text-logo-small.png" alt="RunAce" height="66" width="200"/></h1>

            <div>
                <ul class="navigation">
                    <li><a href="index.php" title="Home">Home</a></li>
                    <li><a href="about.php" title="About">About</a></li>
                    <li><a href="download.php" title="Download">Download</a></li>
                </ul>
            </div>

            <?php echo $output; ?>

            <!--<h2>Home</h2>-->

            <p>
                Once you have accepted you are able to send and receive challenges with your friends. Get ready to
                increase your running motivation!
            </p>

            <div class="footer">Mark Tickner, 2015</div>

        </div>
    </div>
</div>

</body>
</html>