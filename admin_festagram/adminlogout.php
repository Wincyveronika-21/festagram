<?php
@session_start();
if (isset($_SESSION['a']))
{
    if(session_destroy())
    {   
        $URL="http://localhost/front_festagram/adminlogin.php";
          echo "<script>location.href='$URL'</script>";
    }
}
?>