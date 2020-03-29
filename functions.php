<?php

function navbar() {
    echo '<nav>';
    if (!isset ($_SESSION['gatekeeper'])){
        $session = 'Login';
        $s_link = '/pointsofinterest/accounts?action=login';
        $account = 'Signup';
        $a_link = '/pointsofinterest/accounts?action=signup';
    }else{
        $session = 'Logout';
        $s_link = 'logout';
        $account = 'Reset Password';
        $a_link = '/pointsofinterest/accounts?action=reset';
    }
    $navbar = [ "$session" => "$s_link",
        "$account" => "$a_link",
        'Home' => '/pointsofinterest' ];
    foreach ($navbar as $key => $value){
        echo "<a href='$value' class='mainmenu'>$key</a>";
    }
    echo '</nav>';
}

function poi_options($ID=null){
    echo '<nav>';
    if (!isset ($_SESSION['gatekeeper'])){
        echo '<p>Please login to get further functionality.</p>';
    }else{
        if (stripos($_SERVER['REQUEST_URI'], '/view/')){
            $name = 'Review';
            $link = "/pointsofinterest/view/$ID/review";
        }else{
            if (!stripos($_SERVER['REQUEST_URI'], '/pointsofinterest/add')){
                $name = 'Add';
                $link = '/pointsofinterest/add';
            }
        }
        $navbar=[ "$name"=>"$link",
                  "Back"=>"/pointsofinterest" ];
        foreach ($navbar as $key => $value){
            if ("$key" == 'Back'){
                echo "<input type='submit' id='link' value='$key' onclick='getPoi()'/>";
            }else{
                echo "<a href='$value'>$key</a>";
            }
        }
        echo "</nav><br style='clear:both'/>";
    }
}

?>
