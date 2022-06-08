<?php
require __DIR__ . '/vendor/autoload.php';
use mikehaertl\wkhtmlto\Pdf;


function generatePDF($content) {
  $pdf = new Pdf($content);
  $id = uniqid();
  if (!$pdf->saveAs('/var/www/html/pdfs/' . $id . '.pdf')) {
    $error = $pdf->getError();
  } else {
    header('Location: pdfs/' . $id . '.pdf');
    exit;
  }
}

if(isset($_POST["url"]) && $_POST["url"] != "") {
  generatePDF($_POST["url"]);
}

if(isset($_POST["designer"])) {
  generatePDF("<div>" . $_POST["designer"] . "</div>");
}


?>
<!DOCTYPE html>
<html>
  <head>
    <title>
      PDF Designer
    </title>
    <script src="js/tinymce.min.js"></script>
    <script type="text/javascript">
      tinymce.init({
        selector: '#designer'
      });
    </script>
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
      <form method="post">
        <h3>PDF Designer</h3>
        <textarea id="designer" name="designer" rows="30" cols="80"></textarea>
        <p>
          <label for="url">Or enter a URL:</label>
          <input style="width: calc(100% - 10px)" name="url" id="url"/>
        </p>
        <input type="submit" value="Generate PDF"><br/><br/>
      </form>
      <hr/>
      <a href="admin.php">System Administration</a>
    </div>
  </body>
</html>
