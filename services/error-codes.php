<?php

    // Codes starting with 1 are request related
    // 100 - Unauthorised request

    // Codes starting with 2 are database related
    // 200 - Database connection error occurred
    // 201 - Database error occurred

    // Codes starting with 3 are user related
    // 300 - User ID not valid
    // 301 - Email address not entered
    // 302 - Email address not valid
    // 303 - Password not entered
    // 304 - Email and password match not found
    // 305 - Name not entered
    // 306 - Password must be at least 8 characters long
    // 307 - Email entered is already registered

    // Codes starting with 4 are friend related
    // 400 - Friend user ID not valid
    // 401 - Verification string not valid
    // 402 - Friend email address not entered
    // 403 - Friend email address not valid
    // 404 - Friend request already accepted

    // Codes starting with 5 are run related
    // 500 - Run ID not valid
    // 501 - Distance not valid
    // 502 - Time not valid

    // Codes starting with 6 are challenge related
    // 600 - Challenge ID not valid
    // 601 - Can only send challenges to friends

?>