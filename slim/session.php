<?php

    session_start();
   $_SESSION['pass_user'] = md5(uniqid());
    
?>