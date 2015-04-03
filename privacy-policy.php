<?php

// Determine if request originated from withing the privacy policy link in the app to show simplified version
$inApp = false;
if (isset($_GET['inApp']) && strcmp($_GET['inApp'], 'true') == 0) {
    $inApp = true;
}

?>
<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb">

<head>
    <title>RunAce - Privacy Policy</title>

    <?php (!$inApp) ? include 'head.php' : ''; ?>
</head>
<body>

<?php (!$inApp) ? include 'page-header.php' : ''; ?>

<?php echo (!$inApp) ? '<h3>Privacy Policy</h3>' : ''; ?>

<p>
    This app is aimed at people who are over 13 years old.
</p>

<p>
    This app collects:
</p>
<ul>
    <li>Your name and email address during registration</li>
    <li>Your location for the purpose of recording runs, but is not saved</li>
    <li>Your weight, height, age and gender for the purpose of fitness measurements (optional)</li>
</ul>

<p>This app uses your devices contacts list for the purpose of adding friends.</p>

<p>This app allows you to communicate and share your runs with friends. You have complete control over who you wish to
    be friends with and hence who can view this data.</p>

<p>All data is stored securely. Sensitive data is encrypted using industry standard methods. The internet connection
    between the app and the server is secure.</p>

<p>All personal data can be amended upon request.</p>

<?php (!$inApp) ? include 'page-footer.php' : ''; ?>

</body>
</html>