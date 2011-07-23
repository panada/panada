<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd"
    >
<html lang="en">
<head>
    <title>Module: <?php echo $module_name;?></title>
</head>
<body>
    <h2>Hello World!</h2>
    <p>This is greating from <strong><?php echo $module_name;?></strong> module.</p>
    <p><?php echo $library_string;?></p>
    <p><?php echo $model_string;?></p>
    <p>You can also access main library from this controller module, <a href="?name=budi">click here</a>
    
    <?php if($main_lib): ?>
        <p>The value from Library_request is: <strong><?php echo $main_lib;?></strong></p>
    <?php endif; ?>
</body>
</html>
