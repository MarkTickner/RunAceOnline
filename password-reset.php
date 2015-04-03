<?php

error_reporting(0);
require 'helpers/database-connection.php';
require 'helpers/email-helper.php';

$errorList = array();

$em = $_GET['em'];
$vs = $_GET['vs'];

// Create database connection
if (!($link = GetConnection())) {
    // Database connection error occurred
    array_push($errorList, 200);
} else {
    if (isset($em) && isset($vs)) {
        // Request has originated from valid link
        if (isset($_POST['resetPassword'])) {
            // 'Reset Password' button pressed
            // Server-side validation
            // Validate email
            $em = mysqli_real_escape_string($link, stripslashes($em));
            if (preg_match('/^$|\s+/', $em)) {
                // Email address not entered
                array_push($errorList, 402);
            } elseif (!preg_match('/^([\w\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/', $em)) {
                // Email address not valid
                array_push($errorList, 403);
            }

            // Validate random string
            $vs = mysqli_real_escape_string($link, stripslashes($vs));
            if (!preg_match('/^[a-zA-Z0-9]{13}$/', $vs)) {
                // Verification string not valid
                array_push($errorList, 401);
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

            // Get user details
            $user = GetUserByEmail($link, $em);

            // Generate verification string
            $vsDb = crypt($user['ID'] . $user['DATE_REGISTERED'], $user['NAME']);

            if (!count($errorList) > 0) {
                // Verify random string
                if ($vs === $vsDb) {
                    // Verification string is correct
                    // Generate new password and salt
                    $salt = openssl_random_pseudo_bytes(16);
                    $password = crypt($password, $salt);

                    $salt = mysqli_real_escape_string($link, stripslashes($salt));

                    // Save new password in database
                    if (ResetPassword($link, $user['ID'], $password, $salt)) {
                        // Successfully reset
                        // Close connection
                        CloseConnection($link);
                    } else {
                        // Database error occurred
                        array_push($errorList, 201);
                    }
                } else {
                    // Verification string not valid
                    array_push($errorList, 401);
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
                $output = '<p class="alert success">Password successfully changed, now try logging in with your new password.</p>';

                // Send password reset confirmation email
                $body = '<p>' . $user['NAME'] . ',</p>
                         <p>Your password has been successfully changed, now try logging in with your new password.</p>
                         <p>Reply to this email immediately if you did not make this request yourself.</p>';
                SendEmail($em, 'RunAce Password Reset Confirmation', $body);
            }
        }
    } else {
        // Link not valid
        $output = '<p class="alert error">You followed an invalid link, please try again.</p>';
    }
}

?>
<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb">

<head>
    <title>RunAce - Reset Password</title>

    <?php include 'head.php'; ?>

    <script type="text/javascript">
        <!--
        // Client-side form validation
        // Function to display any error messages on form submit
        /**
         * @return {boolean}
         */
        function ValidateForm() {
            var isValid = true;

            // Validate password
            if (ValidatePassword(document.getElementById('password').value) != '') isValid = false;

            return isValid;
        }

        // Function to validate the password
        function ValidatePassword(password) {
            var output;
            if (/^$|\s+/.test(password)) {
                output = 'Password not entered';
            } else if (password.length < 8) {
                output = 'Password must be at least 8 characters long';
            } else {
                output = '';
            }

            document.getElementById('passwordValidation').innerHTML = output;
            return output;
        }
        //-->
    </script>

</head>
<body>

<?php include 'page-header.php'; ?>

<?php echo $output; ?>

<h3>Password Reset</h3>

<p>
    Enter a new password below.
</p>

<form action="password-reset.php?em=<?php echo $em; ?>&vs=<?php echo $vs; ?>" method="post"
      id="passwordReset">
    <div class="row">
        <div class="six columns">
            <label for="password">New password</label>
            <input id="password" name="password" type="password" maxlength="50" onkeyup="ValidatePassword(this.value);"
                   onblur="ValidatePassword(this.value);" class="u-full-width"/>
            <span id="passwordValidation" class="validation-error"></span>
        </div>
    </div>
    <div class="row">
        <input type="submit" value="Reset Password" name="resetPassword" onclick="return ValidateForm();"/>
    </div>
</form>

<?php include 'page-footer.php'; ?>

</body>
</html>