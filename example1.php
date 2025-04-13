<!-- Version 3 -->
<?php
    $year = date("Y");
    $html = "<h1>Happy $year!</h1>"
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <!-- Version 1 -->
    <?php echo "Hello, World!"; ?>

    <!-- Version 2 -->
    <?php 
        $year = date("Y");
        echo "Hello $year";
    ?>
            
    <!-- Version 3 -->
    <?php echo $html; ?>
</body>
</html>