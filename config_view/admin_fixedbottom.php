<!-- <div class="fixed-div-bottom">
    <p> -->
        <?php
        if (isset($_COOKIE['adminstart'])) {
            $cookieContent = $dec= encrypt($_COOKIE['adminstart'], $action = 'd' ); 

            $lines = explode("&", $cookieContent);

            foreach ($lines as $key => $line) {
                echo $key." { ".$line . "}<br>";
            }
        } else {
            echo "O cookie 'adminstart' não está definido.";
        }
        
        ?>
<!-- 
    </p>
</div> -->