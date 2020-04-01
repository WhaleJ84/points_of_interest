<?php

function navbar()
{
    echo '<nav>';
    if (!isset($_SESSION['gatekeeper'])) {
        $session = 'Login';
        $s_link = '/~assign225/accounts?action=login';
        $account = 'Signup';
        $a_link = '/~assign225/accounts?action=signup';
    } else {
        $session = 'Logout';
        $s_link = 'logout';
        $account = 'Reset Password';
        $a_link = '/~assign225/accounts?action=reset';
    }
    $navbar = [ "$session" => "$s_link",
        "$account" => "$a_link",
        'Home' => '/~assign225' ];
    foreach ($navbar as $key => $value) {
        echo "<a href='$value' class='mainmenu'>$key</a>";
    }
    echo '</nav>';
}

function poi_options($ID=null)
{
    echo '<nav>';
    if (!isset($_SESSION['gatekeeper'])) {
        echo '<p>Please login to get further functionality.</p>';
    } else {
        if (stripos($_SERVER['REQUEST_URI'], '/view/')) {
            $name = 'Review';
            $link = "/~assign225/view/$ID/review";
        } else {
            if (!stripos($_SERVER['REQUEST_URI'], '/~assign225/add')) {
                $name = 'Add';
                $link = '/~assign225/add';
            }
        }
        $navbar=[ "$name"=>"$link",
                  "Back"=>"/~assign225" ];
        foreach ($navbar as $key => $value) {
            if ("$key" == 'Back') {
                echo "<input type='submit' id='link' value='$key' onclick='getPoi()'/>";
            } else {
                echo "<a href='$value'>$key</a>";
            }
        }
    }
}
