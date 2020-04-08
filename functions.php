<?php

function navbar()
{
    echo '<nav>';
    if (!isset($_SESSION['gatekeeper'])) {
        $session = 'Login';
        $s_link = '/~assign225/accounts/login';
        $account = 'Signup';
        $a_link = '/~assign225/accounts/signup';
    } else {
        $session = 'Logout';
        $s_link = 'logout';
        $account = 'Reset Password';
        $a_link = '/~assign225/accounts/reset';
    }
    $navbar = [ "$session" => "$s_link",
        "$account" => "$a_link",
        'Home' => '/~assign225' ];
    foreach ($navbar as $key => $value) {
        echo "<a href='$value' class='mainmenu'>$key</a>";
    }
    echo '</nav>';
}

function poi_options()
{
    echo '<nav>';
    if (!isset($_SESSION['gatekeeper'])) {
        echo '<p>Please login to get further functionality.</p>';
    } else {
        $navbar=[ 'Add'=>'/~assign225/add',
            'Back'=>'/~assign225' ];
        foreach ($navbar as $key => $value) {
            if ("$key" == 'Back') {
                echo "<input type='submit' id='link' value='$key' onclick='getPoi()'/>";
            } else {
                echo "<a href='$value'>$key</a>";
            }
        }
    }
}
