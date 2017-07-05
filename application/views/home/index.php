<?php

if ( !empty($userData['oauth_provider']) && $userData['oauth_provider'] == 'linkedin' && !empty($userData) ) {
    // if oauth linkedin
    echo '<h2>'.$userData['first_name'] .' from '. $userData['oauth_provider'].'</h2>';
    echo '<img src="'.$userData['picture_url'].'" alt=""/>';
    echo '<a href="'.$logoutUrl.'">Logout</a>';

} elseif ( !empty($userData['oauth_provider']) && $userData['oauth_provider'] == 'facebook' && !empty($userData) ) {        
    // if oauth facebook
    echo '<h2>'.$userData['first_name'] .' from '. $userData['oauth_provider'].'</h2>';
    echo '<p class="image"><img src="'.$userData['picture_url'].'" alt=""/></p>';
    echo '<p><b><a href="'.$logoutUrl.'">Logout</a> from VCET Engineers</b></p>';

} elseif( !empty($error_msg) ){
    // if error
    echo '<p class="error">'.$error_msg.'</p>';

} else{

    // facebook login
    if( !empty($authUrl) ) {            
        echo '<a href="'.$authUrl.'">Login</a>';
    }
    
    // linkedin login
    if( !empty($oauthURL) ) {
        echo '<div class="linkedin_btn">
                <a href="'.$oauthURL.'">
                    <img src="'.base_url('/assets/images/sign-in-with-linkedin.png').'" />
                </a>
            </div>';
    }
}

