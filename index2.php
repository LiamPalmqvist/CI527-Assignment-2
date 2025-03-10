<?php
    $year = date("Y");
    $html = '<DOCTYPE html>'
          . '<html><head><meta charset="utf-8">'
          . '<title>New Year Greetings</title>'
          . '</head><body><h1>Happy ' . $year . '!</h1>'
          . '</body></html>';
    echo $html;
?>