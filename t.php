<!--todo delete?-->
<html>
<head>
    <script>
        // http://www.3dkingdoms.com/chess/elo.htm
        function CalculateRatingChange() {
/*
            // Inputs
            var playerRatingA = document.rating.elo1.value * 1;
            var playerRatingB = document.rating.elo2.value * 1;

            var minScore = 5;
            var weight;
            var score;

            if (playerRatingA < 250) {
                weight = 35;
            } else if (playerRatingA >= 250 && playerRatingA < 500) {
                weight = 30;
            } else if (playerRatingA >= 500 && playerRatingA < 750) {
                weight = 25;
            } else if (playerRatingA >= 750 && playerRatingA < 1000) {
                weight = 20;
            } else {
                weight = 15;
            }

            score = Math.round(weight * (1 - (1 / (1 + Math.pow(10, (playerRatingB - playerRatingA) / 250)))));

            if (score < 5) {
                score = minScore;
            }

            // Output
            document.ratingchange.win.value = score;

*/

             var distanceNew = document.rating.elo1.value * 1;
             var distanceAverage = document.rating.elo2.value * 1;

             var minScore = 5;
             var score;

             if (distanceAverage > distanceNew) {
             score = minScore;
             }
             else {
             score = (Math.round(20 * (1 - (1 / (1 + Math.pow(10, (distanceNew - distanceAverage) / 10))))));
             }

             // Output
             document.ratingchange.win.value = score;



            /*
             var paceNew = document.rating.elo1.value * 1;
             var paceAverage = document.rating.elo2.value * 1;

             var minScore = 5;
             var score;

             if (paceAverage < paceNew) {
             score = minScore;
             }
             else {
             score = (Math.round(20 * (1 - (1 / (1 + Math.pow(10, (paceAverage - paceNew)))))));
             }

             // Output
             document.ratingchange.win.value = score;
             */

        }
    </script>
</head>
<body>
<form action="" name="rating" id="rating">

    <table width="540" style="padding: 2px; border: 1px solid #888899; background-color:#EEEEEE; ">
        chal
        <tbody>
        <tr>

            <td>

                Player A/ New Distance or Pace:

                <input name="elo1" value="2000" size="8">

            </td>

            <td>


            </td>

        </tr>
        <tr>

            <td>

                Player B/ Avg Distance or Pace:

                <input name="elo2" value="2000" size="8"></td>

            <td><input type="button" value="Calculate Change" onclick="CalculateRatingChange()" class="button"></td>

        </tr>
        </tbody>
    </table>

</form>


<form name="ratingchange" id="ratingchange" action="">

    <table width="540" style="border: 1px solid #888899; background-color:#EEEEEE">
        <tbody>
        <tr>
            <td>

                <table width="100%" style="margin: 4px 4px 4px 4px;">
                    <tbody>
                    <tr>

                        <td width="33%">Win</td>

                        <td width="33%">Draw</td>

                        <td width="33%">Loss</td>

                    </tr>

                    <tr>
                        <td>

                            <input name="win" value="0" size="8"
                                   style="border: 0px; font-weight:bold; background-color:#EEEEEE">

                        </td>
                        <td>

                            <input name="draw" value="0" size="8"
                                   style="border: 0px; font-weight:bold; background-color:#EEEEEE">

                        </td>
                        <td>

                            <input name="loss" value="0" size="8"
                                   style="border: 0px; font-weight:bold; background-color:#EEEEEE">

                        </td>
                    </tr>
                    </tbody>
                </table>

                <br>

                Expected Percentage : <input name="percent" value="0" size="8"
                                             style="border: 0px; font-weight:bold; background-color:#EEEEEE">

            </td>

        </tr>
        </tbody>
    </table>

</form>
</body>

</html>