<?php
define('DS', DIRECTORY_SEPARATOR);
define('THISPATH', dirname(__FILE__));
require_once(THISPATH . DS . 'apps' . DS . 'config.php');
?>
<!DOCTYPE html>
<html>
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Panada Installation Check</title>

	<style type="text/css">
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #454545;
        }
        a {
            color: #1010CE; text-decoration: underline;
        }
        a:hover {
            text-decoration: underline;
        }
        a:visited {
            color: #1010CE; text-decoration: underline;
        }
        .pass {
            color: #191;
        }
        .fail {
            color: #911;
        }
	</style>

</head>
<body>

	<h1>Panada Installation Check</h1>


    <h2>Minimum Requirements</h2>
	<?php $failed = FALSE ?>

    <div>
        <strong>PHP Version</strong>
        <?php if (version_compare(PHP_VERSION, '5.3', '>=')): ?>
            <?php echo PHP_VERSION ?> <span class="pass">PASS</span>
        <?php else: $failed = TRUE ?>
            <?php echo PHP_VERSION ?> <span class="fail">FAIL</span>
        <?php endif ?>
    </div>
    
    <div>
        <strong>System Directory</strong>
        <?php if (is_dir(THISPATH . DS . 'panada') AND is_file(THISPATH . DS . 'panada' . DS . 'gear.php')): ?>
            <?php echo THISPATH . DS . 'panada' ?> <span class="pass">PASS</span>
        <?php else: $failed = TRUE ?>
            <span class="fail">FAIL</span>
        <?php endif ?>
    </div>
    
    <div>
        <strong>Application Directory</strong>
        <?php if (is_dir(THISPATH . DS . 'apps') AND is_file(THISPATH . DS . 'apps' . DS . 'config.php')): ?>
            <?php echo THISPATH . DS . 'apps' ?> <span class="pass">PASS</span>
        <?php else: $failed = TRUE ?>
            <span class="fail">FAIL</span>
        <?php endif ?>
    </div>
	

	<?php if ($failed === TRUE): ?>
		<p class="fail">✘ Panada may not work correctly with your environment.</p>
	<?php else: ?>
		<p class="pass">✔ Your environment passed all requirements.</p>
	<?php endif ?>
	
	<h2>Optional</h2>
	
	<h3>Database</h3>
	
	<div>
        <strong>MySQL</strong>
        <?php if (function_exists('mysql_connect')): ?>
            <span class="pass">PASS</span>
        <?php else: ?>
            <span class="fail">FAIL</span>
        <?php endif ?>
	</div>
	
	<div>
        <strong>PostgreSQL</strong>
        <?php if (function_exists('pg_connect')): ?>
            <span class="pass">PASS</span>
        <?php else: ?>
            <span class="fail">FAIL</span>
        <?php endif ?>
	</div>
	
	<div>
        <strong>SQLite</strong>
        <?php if (function_exists('sqlite_open')): ?>
            <span class="pass">PASS</span>
        <?php else: ?>
            <span class="fail">FAIL</span>
        <?php endif ?>
	</div>
	
	<div>
        <strong>MongoDB</strong>
        <?php if (class_exists('Mongo')): ?>
            <span class="pass">PASS</span>
        <?php else: ?>
            <span class="fail">FAIL</span>
        <?php endif ?>
	</div>
	
	
</body>
</html>