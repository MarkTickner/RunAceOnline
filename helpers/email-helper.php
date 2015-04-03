<?php

// Prevent direct access to this file
// Source: http://board.phpbuilder.com/showthread.php?10263469#post10986948
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    // File requested directly, redirect user
    header('Location: https://stuweb.cms.gre.ac.uk/~tm112/project/');
} else {
    // Function that returns email headers
    function GetHeaders() {
        return 'From: RunAce <runace@mtickner.co.uk>' . "\r\n" .
        'MIME-Version: 1.0' . "\r\n" .
        'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    }

    // Function that sends an email to the specified address in a template
    function SendEmail($email, $subject, $body) {
        $message = '<html>
                        <head>
                            <meta name="author" content="Mark Tickner">
                            <meta name="viewport" content="width=device-width, initial-scale=1">
                        </head>
                        <body style="font-family: \'Arial\', sans-serif; background-color: #EEEEEE;">
                            <div style="background-color: #FFFFFF; padding: 2rem; border-radius: 4px;">
                                <h1 style="margin-top: 0;"><img src="https://stuweb.cms.gre.ac.uk/~tm112/project/images/text-logo-small.png" alt="RunAce" height="66" width="200"></h1>
                                ' . $body . '
                            </div>
                        </body>
                    </html>';

        mail($email, $subject, $message, GetHeaders());
    }
}

?>