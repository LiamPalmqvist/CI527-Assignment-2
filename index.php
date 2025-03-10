<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index of examples</title>
</head>
<body>
    <h2>Index of examples</h2>
    <?php
    // Get the working directory
    $dir = opendir('.');
    // read all of the files in the directory and assign them to $page one at a time
    // check that it opened correctly
    while(false !== ($page = readdir($dir))) {
        // if the page is not '.', '..' or 'api.php'
        if ($page != '.' && $page != '..' && $page != basename(__FILE__) && $page != '.git' && $page != '.gitignore') {
            // create a link to each page
            echo "<p><a href='./$page'>$page</a></p>";
        }
    }
    // close the directory
    closedir($dir);
    ?>
</body>
</html>