<?php

// Prevent direct access to this file
// Source: http://board.phpbuilder.com/showthread.php?10263469#post10986948
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    // File requested directly, redirect user
    header('Location: http://www.mtickner.co.uk/runace/');
} else {
    // Function to create a database connection
    function GetConnection() {
        require 'mysql.php';

        // Return database link
        return mysqli_connect($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);
    }

    // Function to close the database connection
    function CloseConnection($link) {
        mysqli_close($link);
    }

    // Function to check if an email address is already registered
    function CheckIfEmailAlreadyRegistered($link, $email) {
        $sql = "SELECT EMAIL FROM PROJECT_USER WHERE EMAIL = '$email'";
        $result = mysqli_query($link, $sql);

        if (mysqli_num_rows($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

    // Function to add a new user to the database
    function RegisterUser($link, $name, $email, $password, $salt) {
        $sql = "INSERT INTO PROJECT_USER (NAME, EMAIL, PASSWORD, PASSWORD_SALT) VALUES ('$name', '$email', '$password', '$salt')";

        return mysqli_query($link, $sql);
    }

    // Function to authenticate a user
    function LoginUser($link, $email, $password) {
        // Get salt from database
        $sql = "SELECT PASSWORD_SALT FROM PROJECT_USER WHERE EMAIL = '$email'";
        $result = mysqli_query($link, $sql);
        //$salt = mysqli_fetch_assoc($result)['PASSWORD_SALT']; // PHP 5.4+
        $saltArray = mysqli_fetch_assoc($result);
        $salt = $saltArray['PASSWORD_SALT'];
        $password = crypt($password, $salt);

        $sql = "SELECT ID FROM PROJECT_USER WHERE EMAIL = '$email' AND PASSWORD = '$password'";
        $result = mysqli_query($link, $sql);

        if (mysqli_num_rows($result) == 1) {
            return true;
        } else {
            return false;
        }
    }

    // Function to reset a users password
    function ResetPassword($link, $userId, $password, $salt) {
        $sql = "UPDATE PROJECT_USER SET PASSWORD = '$password', PASSWORD_SALT = '$salt' WHERE ID = $userId";
        return mysqli_query($link, $sql);
    }


    // Function to get a user by user ID
    function GetUserByUserId($link, $userId) {
        $sql = "SELECT ID, NAME, EMAIL, DATE_REGISTERED, USER_TYPE_ID AS USER_TYPE FROM PROJECT_USER WHERE ID = $userId";
        $result = mysqli_query($link, $sql);
        $user = mysqli_fetch_assoc($result);

        $user['USER_TYPE'] = GetUserTypeByTypeId($link, $user['USER_TYPE']);

        return $user;
    }

    // Function to get a user by email
    function GetUserByEmail($link, $email) {
        $sql = "SELECT ID, NAME, EMAIL, DATE_REGISTERED, USER_TYPE_ID AS USER_TYPE FROM PROJECT_USER WHERE EMAIL = '$email'";
        $result = mysqli_query($link, $sql);
        $user = mysqli_fetch_assoc($result);

        $user['USER_TYPE'] = GetUserTypeByTypeId($link, $user['USER_TYPE']);

        return $user;
    }

    // Function that gets the specified user type
    function GetUserTypeByTypeId($link, $userTypeId) {
        $sql = "SELECT ID, NAME, DESCRIPTION FROM PROJECT_USER_TYPE WHERE ID = $userTypeId";
        $result = mysqli_query($link, $sql);
        $userType = mysqli_fetch_assoc($result);

        return $userType;
    }

    // Function that gets statistics about the specified user
    function GetUserProfileStatistics($link, $userId) {
        $sql = "SELECT s.SCORE, tr.TOTAL_RUNS, tc.TOTAL_CHALLENGES, td.TOTAL_DISTANCE, tt.TOTAL_TIME
                FROM
                  (SELECT SUM(POINTS) AS SCORE FROM PROJECT_SCORE WHERE USER_ID = $userId) s,
                  (SELECT COUNT(ID) AS TOTAL_RUNS FROM PROJECT_RUN WHERE USER_ID = $userId) tr,
                  (SELECT COUNT(ID) AS TOTAL_CHALLENGES FROM PROJECT_CHALLENGE WHERE USER_ID = $userId AND DATE_COMPLETED IS NOT NULL) tc,
                  (SELECT SUM(DISTANCE_TOTAL) AS TOTAL_DISTANCE FROM PROJECT_RUN WHERE USER_ID = $userId) td,
                  (SELECT SUM(TOTAL_TIME) AS TOTAL_TIME FROM PROJECT_RUN WHERE USER_ID = $userId) tt";
        $result = mysqli_query($link, $sql);

        return mysqli_fetch_assoc($result);
    }

    // Function that gets the specified user's score
    function GetUserScore($link, $userId) {
        $sql = "SELECT SUM(POINTS) AS SCORE FROM PROJECT_SCORE WHERE USER_ID = $userId";
        $result = mysqli_query($link, $sql);

        //return mysqli_fetch_assoc($result)['SCORE']; // PHP 5.4+
        $scoreArray = mysqli_fetch_assoc($result);
        return $scoreArray['SCORE'];
    }


    // Function to get friends by user ID
    function GetFriendsByUserId($link, $userId) {
        $sql = "SELECT
                  FRIEND.ID,
                  FRIEND.USER_ID,
                  IF(FRIEND.DATE_ACCEPTED IS NULL, 'P', 'A') AS STATUS,
                  IF(FRIEND.DATE_ACCEPTED IS NULL, FRIEND.DATE_SENT, FRIEND.DATE_ACCEPTED) AS STATUS_DATE
                FROM (
                  SELECT
                    F.ID,
                    USER_2.ID AS USER_ID,
                    F.DATE_SENT,
                    (SELECT F2.DATE_SENT FROM PROJECT_FRIEND F2 WHERE F2.USER_1_ID = USER_2.ID AND F2.USER_2_ID = USER_1.ID AND F2.IS_ACTIVE = 1) AS DATE_ACCEPTED
                  FROM
                    PROJECT_FRIEND F
                    JOIN PROJECT_USER USER_1 ON F.USER_1_ID = USER_1.ID
                    JOIN PROJECT_USER USER_2 ON F.USER_2_ID = USER_2.ID
                  WHERE
                    USER_1.ID = $userId
                  AND
                    F.IS_ACTIVE = 1
                  ORDER BY
                    USER_2.NAME
                ) FRIEND";
        $result = mysqli_query($link, $sql);

        $friends = array();

        // Add each user to the friends array
        while ($user = mysqli_fetch_assoc($result)) {
            // Get user array
            $user['USER_ID'] = GetUserByUserId($link, $user['USER_ID']);

            array_push($friends, $user);
        }

        return $friends;
    }

    // Method that saves a friend request in the database
    function SaveFriendRequest($link, $userId, $friendUserId, $verificationString) {
        $sql = "INSERT INTO PROJECT_FRIEND (USER_1_ID, USER_2_ID, VERIFICATION_STRING) VALUES ($userId, $friendUserId, '$verificationString')";

        return mysqli_query($link, $sql);
    }

    // Function to check if a friend request has already been accepted
    function CheckIfFriendRequestAlreadyAccepted($link, $friendId) {
        $sql = "SELECT ID FROM PROJECT_FRIEND WHERE USER_2_ID = (SELECT USER_1_ID FROM PROJECT_FRIEND WHERE ID = $friendId) AND USER_1_ID = (SELECT USER_2_ID FROM PROJECT_FRIEND WHERE ID = $friendId)  AND IS_ACTIVE = 1";
        $result = mysqli_query($link, $sql);

        if (mysqli_num_rows($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

    // Function that checks if the two specified users are friends
    function CheckIfFriends($link, $user1Id, $user2Id) {
        $sql = "SELECT
                  FRIEND.ID,
                  FRIEND.USER_ID_1,
                  FRIEND.USER_ID_2
                FROM (
                  SELECT
                    F.ID,
                    USER_1.ID AS USER_ID_1,
                    USER_2.ID AS USER_ID_2,
                    (SELECT F2.DATE_SENT FROM PROJECT_FRIEND F2 WHERE F2.USER_1_ID = USER_2.ID AND F2.USER_2_ID = USER_1.ID) AS DATE_ACCEPTED
                  FROM
                    PROJECT_FRIEND F
                    JOIN PROJECT_USER USER_1 ON F.USER_1_ID = USER_1.ID
                    JOIN PROJECT_USER USER_2 ON F.USER_2_ID = USER_2.ID
                  WHERE
                    USER_1.ID = $user1Id
                  AND
                    USER_2.ID = $user2Id
                  AND
                    F.IS_ACTIVE = 1
                ) FRIEND
                WHERE
                  IF(FRIEND.DATE_ACCEPTED IS NULL, 'P', 'A') = 'A'";
        $result = mysqli_query($link, $sql);

        if (mysqli_num_rows($result) == 1) {
            return true;
        } else {
            return false;
        }
    }

    // Method that updates the database when a friend request is accepted
    function AcceptFriendRequest($link, $friendId, $verificationString) {
        $sql = "INSERT INTO PROJECT_FRIEND (USER_1_ID, USER_2_ID) SELECT USER_2_ID, USER_1_ID FROM PROJECT_FRIEND WHERE ID = $friendId AND VERIFICATION_STRING = '$verificationString'";
        mysqli_query($link, $sql);

        $sql = "UPDATE PROJECT_FRIEND SET VERIFICATION_STRING = NULL WHERE ID = $friendId AND IS_ACTIVE = 1";
        return mysqli_query($link, $sql);
    }

    // Function to check if a user has already added a user
    function CheckIfFriendAlreadyAdded($link, $userId, $friendUserId) {
        $sql = "SELECT ID FROM PROJECT_FRIEND WHERE USER_1_ID = $userId AND USER_2_ID = $friendUserId AND IS_ACTIVE = 1";
        $result = mysqli_query($link, $sql);

        if (mysqli_num_rows($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

    // Function that unfriends the specified users
    function Unfriend($link, $user1Id, $user2Id) {
        $sql = "UPDATE PROJECT_FRIEND
                SET IS_ACTIVE = 0
                WHERE IS_ACTIVE = 1
                AND ((USER_1_ID = $user1Id AND USER_2_ID = $user2Id) OR (USER_1_ID = $user2Id AND USER_2_ID = $user1Id))";

        return mysqli_query($link, $sql);
    }


    // Function to a challenge by challenge ID
    function GetChallengeByChallengeId($link, $challengeId, $setRead) {
        $sql = "SELECT ID, USER_ID, RUN_ID, MESSAGE, DATE_CREATED, IS_NOTIFIED, IS_READ, DATE_COMPLETED FROM PROJECT_CHALLENGE WHERE ID = $challengeId";
        $result = mysqli_query($link, $sql);
        $challenge = mysqli_fetch_assoc($result);

        // Get run array
        $challenge['RUN_ID'] = GetRunByRunId($link, $challenge['RUN_ID']);

        if ($setRead) {
            // Set the challenge as read
            $sql = "UPDATE PROJECT_CHALLENGE SET IS_NOTIFIED = 1, IS_READ = 1 WHERE ID = $challengeId";
            mysqli_query($link, $sql);
        }

        return $challenge;
    }

    // Function to get challenges by user ID
    function GetChallengesByUserId($link, $userId, $requestFromService) {
        $sql = "SELECT ID, USER_ID, RUN_ID, MESSAGE, DATE_CREATED, IS_NOTIFIED, IS_READ, DATE_COMPLETED FROM PROJECT_CHALLENGE WHERE USER_ID = $userId ORDER BY DATE_CREATED DESC";
        $result = mysqli_query($link, $sql);

        $challenges = array();

        // Add each challenge to the challenges array
        while ($challenge = mysqli_fetch_assoc($result)) {
            // Get run array
            $challenge['RUN_ID'] = GetRunByRunId($link, $challenge['RUN_ID']);

            array_push($challenges, $challenge);
        }

        if ($requestFromService) {
            // Update as notified
            $sql = "UPDATE PROJECT_CHALLENGE SET IS_NOTIFIED = 1 WHERE USER_ID = $userId";
            mysqli_query($link, $sql);
        }

        return $challenges;
    }

    // Function that returns a count of a user's completed challenges
    function GetChallengeCountByUserId($link, $userId) {
        $sql = "SELECT COUNT(ID) AS CHALLENGE_COUNT FROM PROJECT_CHALLENGE WHERE USER_ID = $userId AND DATE_COMPLETED IS NOT NULL";
        $result = mysqli_query($link, $sql);
        //$challengeCount = mysqli_fetch_assoc($result)['CHALLENGE_COUNT']; // PHP 5.4+
        $challengeCountArray = mysqli_fetch_assoc($result);
        $challengeCount = $challengeCountArray['CHALLENGE_COUNT'];

        return $challengeCount;
    }

    // Function to add a new challenge to the database
    function SaveChallenge($link, $userId, $runId, $message) {
        $sql = "INSERT INTO PROJECT_CHALLENGE (USER_ID, RUN_ID, MESSAGE) VALUES ($userId, $runId, '$message')";

        return mysqli_query($link, $sql);
    }

    // Function to set a challenge as completed in the database
    function SetChallengeCompleted($link, $challengeId) {
        $sql = "UPDATE PROJECT_CHALLENGE SET DATE_COMPLETED = NOW() WHERE ID = $challengeId AND DATE_COMPLETED IS NULL";

        mysqli_query($link, $sql);
    }


    // Function to get a run by run ID
    function GetRunByRunId($link, $runId) {
        $sql = "SELECT ID, USER_ID, DISTANCE_TOTAL, TOTAL_TIME, DATE_RUN FROM PROJECT_RUN WHERE ID = $runId";
        $result = mysqli_query($link, $sql);
        $run = mysqli_fetch_assoc($result);

        // Get user array
        $run['USER_ID'] = GetUserByUserId($link, $run['USER_ID']);

        return $run;
    }

    // Function to get runs by user ID
    function GetRunsByUserId($link, $userId) {
        $sql = "SELECT ID, USER_ID, DISTANCE_TOTAL, TOTAL_TIME, DATE_RUN FROM PROJECT_RUN WHERE USER_ID = $userId ORDER BY DATE_RUN DESC";
        $result = mysqli_query($link, $sql);

        $runs = array();

        // Add each run to the runs array
        while ($run = mysqli_fetch_assoc($result)) {
            // Get user array
            $run['USER_ID'] = GetUserByUserId($link, $run['USER_ID']);

            array_push($runs, $run);
        }

        return $runs;
    }

    // Function that returns a count of a user's runs
    function GetRunCountByUserId($link, $userId) {
        $sql = "SELECT COUNT(ID) AS RUN_COUNT FROM PROJECT_RUN WHERE USER_ID = $userId";
        $result = mysqli_query($link, $sql);
        //$runCount = mysqli_fetch_assoc($result)['RUN_COUNT']; // PHP 5.4+
        $runCountArray = mysqli_fetch_assoc($result);
        $runCount = $runCountArray['RUN_COUNT'];

        return $runCount;
    }

    // Function to add a new run to the database
    function SaveRun($link, $userId, $distanceTotal, $totalTime) {
        $sql = "INSERT INTO PROJECT_RUN (USER_ID, DISTANCE_TOTAL, TOTAL_TIME) VALUES ($userId, $distanceTotal, $totalTime)";

        return mysqli_query($link, $sql);
    }

    // Function that gets the average distance and pace of the specified user's runs
    function GetRunAverages($link, $userId) {
        $sql = "SELECT
                  AVG(DISTANCE_TOTAL) AS AVERAGE_RUN_DISTANCE,
                  ((AVG(TOTAL_TIME) / AVG(DISTANCE_TOTAL)) / 60) AS AVERAGE_RUN_PACE
                FROM PROJECT_RUN
                WHERE USER_ID = $userId";
        $result = mysqli_query($link, $sql);
        return mysqli_fetch_assoc($result);
    }


    // Function to get badges by user ID
    function GetBadgesByUserId($link, $userId) {
        $sql = "SELECT
                  B.TYPE AS TYPE,
                  B.LEVEL AS LEVEL,
                  UB.DATE_AWARDED AS DATE_AWARDED
                FROM
                  PROJECT_BADGE B
                  JOIN PROJECT_USER_BADGE UB ON B.ID = UB.BADGE_ID
                WHERE UB.USER_ID = $userId
                ORDER BY FIELD(B.TYPE,'R','C'), B.LEVEL ASC";
        $result = mysqli_query($link, $sql);

        $badges = array();

        while ($badge = mysqli_fetch_assoc($result)) {
            array_push($badges, $badge);
        }

        return $badges;
    }

    // Function to get badge levels by type
    function GetBadgeLevelsByType($link, $type) {
        $sql = "SELECT LEVEL FROM PROJECT_BADGE WHERE TYPE = '$type'";
        $result = mysqli_query($link, $sql);

        $badges = array();

        while ($badge = mysqli_fetch_array($result, MYSQLI_NUM)) {
            array_push($badges, $badge[0]);
        }

        return $badges;
    }

    // Function that checks if the specified user has been awarded the specified badge
    function CheckIfBadgeAwarded($link, $userId, $type, $level) {
        $sql = "SELECT ID
                FROM PROJECT_USER_BADGE
                WHERE USER_ID = $userId
                AND BADGE_ID = (SELECT ID FROM PROJECT_BADGE WHERE TYPE = '$type' AND LEVEL = $level)";
        $result = mysqli_query($link, $sql);

        if (mysqli_num_rows($result) == 1) {
            // User has badge
            return true;
        } else {
            return false;
        }
    }

    // Function that awards the specified user the specified badge
    function AwardBadge($link, $userId, $type, $level) {
        $sql = "INSERT INTO PROJECT_USER_BADGE (USER_ID, BADGE_ID)
                VALUES ($userId, (SELECT ID FROM PROJECT_BADGE WHERE TYPE = '$type' AND LEVEL = $level))";

        return mysqli_query($link, $sql);
    }

    // Function that awards the specified user the specified number of points
    function AwardPoints($link, $userId, $points) {
        $sql = "INSERT INTO PROJECT_SCORE (USER_ID, POINTS) VALUES ($userId, $points)";

        return mysqli_query($link, $sql);
    }
}

?>