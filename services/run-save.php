<?php

error_reporting(0);
require '../helpers/database-connection.php';

// Output as JSON
header('Content-Type: application/json');

$errorList = array();
$runDetails = array();
$badgeDetails = array();

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

                // Get run averages
                $runAverages = GetRunAverages($link, $userId);

                // Set the minimum score
                $minScore = 5;


                // Award points for distance
                $distanceAverage = $runAverages['AVERAGE_RUN_DISTANCE'];

                if ($distanceAverage > $distanceTotal) {
                    $pointsDistance = $minScore;
                } else {
                    $pointsDistance = round(20 * (1 - (1 / (1 + pow(10, ($distanceTotal - $distanceAverage) / 10)))));
                }

                AwardPoints($link, $userId, $pointsDistance);


                // Award points for pace
                $paceNew = ((double)$totalTime / (double)$distanceTotal) / 60;
                $paceAverage = $runAverages['AVERAGE_RUN_PACE'];

                if ($paceAverage < $paceNew) {
                    $pointsPace = $minScore;
                } else {
                    $pointsPace = round(20 * (1 - (1 / (1 + pow(10, ($paceAverage - $paceNew))))));
                }

                AwardPoints($link, $userId, $pointsPace);


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

                        // Award points for challenge
                        $userScoreA = GetUserScore($link, $userId);
                        $userScoreB = GetUserScore($link, GetChallengeByChallengeId($link, $challengeId, false)['RUN_ID']['USER_ID']['ID']);

                        if ($userScoreA < 250) {
                            $weight = 35;
                        } else if ($userScoreA >= 250 && $userScoreA < 500) {
                            $weight = 30;
                        } else if ($userScoreA >= 500 && $userScoreA < 750) {
                            $weight = 25;
                        } else if ($userScoreA >= 750 && $userScoreA < 1000) {
                            $weight = 20;
                        } else {
                            $weight = 15;
                        }

                        $pointsChallenge = round($weight * (1 - (1 / (1 + pow(10, ($userScoreB - $userScoreA) / 250)))));

                        if ($pointsChallenge < 5) {
                            $pointsChallenge = $minScore;
                        }

                        AwardPoints($link, $userId, $pointsChallenge);
                    }
                }

                // Get user's run count and award badge if necessary
                $runCount = GetRunCountByUserId($link, $userId);
                if (CheckAndAwardBadge($link, $userId, 'R', $runCount)) {
                    // Successfully awarded
                    array_push($badgeDetails, array('TYPE' => 'R', 'LEVEL' => $runCount));
                }

                // Get user's challenge count and award badge if necessary
                $challengeCount = GetChallengeCountByUserId($link, $userId);
                if (CheckAndAwardBadge($link, $userId, 'C', $challengeCount)) {
                    // Successfully awarded
                    array_push($badgeDetails, array('TYPE' => 'C', 'LEVEL' => $challengeCount));
                }

                // Close connection
                CloseConnection($link);

                // Output the points for this run
                array_push($runDetails, array('POINTS' => ($pointsDistance + $pointsPace + $pointsChallenge)));
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
    header('Location: https://stuweb.cms.gre.ac.uk/~tm112/project/');
}

// Set JSON response
$outputJson = array('OutputType' => $outputType, 'Details' => $outputDetailsList, 'AwardedBadges' => $badgeDetails);
exit(json_encode($outputJson));


// Function that checks if the specified badge badge has already been awarded and if not awards it
function CheckAndAwardBadge($link, $userId, $type, $level) {
    // Get relevant award intervals from database
    $awardIntervals = GetBadgeLevelsByType($link, $type);

    if (in_array($level, $awardIntervals) && !CheckIfBadgeAwarded($link, $userId, $type, $level)) {
        // Run count is an award interval and badge has not yet been awarded
        if (AwardBadge($link, $userId, $type, $level)) {
            // Badge successfully saved in database
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

?>