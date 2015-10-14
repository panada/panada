<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Error 500 - Internal Server Error!</title>
<style type="text/css">
html{background:#ddd;;}
body{background:#fff;color:#333;font-family:"Lucida Grande",Verdana,Arial,sans-serif;margin:1em auto;width:70%;padding:1em 2em;border-radius:11px;border:1px solid #999;}
a{color:#2583ad;text-decoration:none;}
a:hover{color:#d54e21;}
a img{border:0;}
h1{border-bottom:1px solid #dadada;clear:both;color:#666;font:24px Georgia,"Times New Roman",Times,serif;margin:5px 0 0 -4px; padding-top:5px;}
h1.logo{margin:7px 0 20px 0;border-bottom:none; text-align:center;}
h2{font-size:16px;}p,li,dd,dt,td{padding-bottom:2px;font-size:12px;line-height:18px;}
h3{font-size:14px;}
ul,ol,dl{padding:5px 5px 5px 22px; }
pre {
    font:14px Consolas,Courier New,Verdana;
    background:#ddf;
    border:1px solid #D0D0D0;
    display:block;
    margin:14px 0;
    padding:10px;
    border-radius:8px;
    overflow: auto;
    word-wrap: normal;
    white-space: pre;
}
#foot {text-align: left; margin-top: 1.8em; border-top: 1px solid #dadada; padding-top: 1em; font-size: 0.7em;}
#foot span.right {float:right;}
</style>
</head>
<body>
<div id="konten">
<h1>Runtime Error!</h1>
<p><strong>Error message</strong>: <?php echo $message; ?></p>
<?php if ( error_reporting() ):?>
<?php if( $file ): ?>
<p><strong>Error in file</strong>:</p>
<pre><?php echo $file; ?> Line: <?php echo $line; ?></pre>
<p><strong>Source Code</strong></p>
<pre>
<?php foreach($code as $code): ?>
<?php echo $code;?>
<?php endforeach; ?>
</pre>
<?php endif; ?>
<p><strong>Debug Trace</strong></p>
<?php if ($trace): ?>
<pre>
<?php echo str_replace(array('<pre>', '</pre>'), '',$trace) ?>
</pre>
<?php endif; ?>
</div>
<?php endif; ?>
<div id="foot">&nbsp;</div>
</body>
</html>