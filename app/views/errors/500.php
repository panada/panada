<!DOCTYPE html>
<html>
<head>
    <title>Error 500 - Internal Server Error!</title>
</head>
<body>
<h2>Runtime Error!</h2>
<strong>Error message</strong>: <?php echo $message; ?><br />
<strong>Error in file</strong>: <?php echo $file; ?> Line: <?php echo $line; ?><br />
<pre>
<?php foreach($code as $code): ?>
<?php echo $code;?>
<?php endforeach; ?>
</body>
</html>