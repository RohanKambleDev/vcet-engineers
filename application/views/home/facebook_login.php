<?php
    if(!empty($authUrl)) {
        echo '<a href="'.$authUrl.'">Login</a>';
    } elseif ( !empty($error) ) {
        echo $error_msg;
    } else {
    ?>
        <div class="wrapper">
            <h1> Profile Details </h1>
            <div class="welcome_txt">Welcome <b><?php echo $userData['first_name']; ?></b></div>
            <div class="fb_box">
                <!-- <p class="image"><img src="<?php echo $userData['picture_url']; ?>" alt="" width="300" height="220"/></p> -->
                <p><b>Facebook ID : </b><?php echo $userData['oauth_uid']; ?></p>
                <p><b>Name : </b><?php echo $userData['first_name'].' '.$userData['last_name']; ?></p>
                <p><b>Email : </b><?php echo $userData['email']; ?></p>
                <p><b>Gender : </b><?php echo $userData['gender']; ?></p>
                <p><b>Locale : </b><?php echo $userData['locale']; ?></p>
                <p><b>You are login with : </b>Facebook</p>
                <p><a href="<?php echo $userData['profile_url']; ?>" target="_blank">Click to Visit Facebook Page</a></p>
                <p><b><a href="<?php echo $logoutUrl; ?>">Logout</a> from VCET Engineers</b></p>
            </div>

            <div
              class="fb-like"
              data-share="true"
              data-width="450"
              data-show-faces="true">
            </div>
        </div>
<?php } ?>