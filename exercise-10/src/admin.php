<?php

if($_SERVER['REMOTE_ADDR'] != "127.0.0.1") {
	echo "Access Denied! Admin access only allowed from 127.0.0.1. Your IP is ". $_SERVER['REMOTE_ADDR'];
	exit();
} 

?>
<!DOCTYPE html>
<html>
  <head>
    <title>
      PDF Designer
    </title>
    <style>
       body {
           background:#333333;
           font-family: Arial;
       }
       .main_cont {
          width:700px;
          margin-left:auto;
          margin-right:auto;
          margin-top:20px;
          background:#FFFFFF;
          padding: 20px;
          border-radius: 10px;
       }
       input {
          width: 100%;
          border: 1px solid rgb(249, 188, 166);
          padding: 5px;
          margin-top: 5px;
       }
       input[type=button], input[type=submit], input[type=reset] {           
          border: 1px solid #CCC;
          cursor: pointer;
       }
       h3 {
          margin-top: 0;
       }
    </style>
  </head>
  <body>
    <div class="main_cont">
      <h3>System Administration</h3>
      <p>The following PDF files have been created:</p>
      <?php
        $files = scandir("pdfs/");
        foreach ($files as &$file) {
           echo "<p>" . $file . "</p>";
        }
       ?>
    </div>
  </body>
</html>
