<?php $this->view_header();?>
<body>
<h1 class="logo"><img alt="Logo" src="<?php echo $this->config->base_url(); ?>/apps/asset/img/logo.png" /></h1>
<div id="konten">
    <h1><?php echo $head;?></h1>
    <?php echo $content;?>
    <?php echo '<p>Base URL for this application is:</p><code>'.$this->config->base_url().'</code>'; ?>

    <h1>Documentation and resource</h1>
    <ul>
        <li>To see Hello World page from a module sample, <a href="<?php echo $this->config->base_url(); ?>index.php/mod_sample">click here.</a></li>
        <li>To get latest update, follow our twitter <a target="_blank" href="http://twitter.com/panadaframework">@panadaframework</a>.</li>
        <li>To contribute the project, fork on github <a target="_blank" href="https://github.com/k4ndar/Panada">https://github.com/k4ndar/Panada</a>.</li>
        <li>For help and bug report, submit to <a target="_blank" href="https://github.com/k4ndar/Panada/issues">issues</a> page.</li>
    </ul>

    <p>
        To get more hint how to use Panada, see this offline <a href="<?php echo $this->config->base_url(); ?>documentation/id/index.html">documentation</a> page.
        Panada licensed under <a href="<?php echo $this->config->base_url(); ?>LICENSE">BSD License</a>
    </p>
</div>

<?php echo $footer;?>
<a href="http://github.com/k4ndar/Panada">
<img src="<?php echo $this->config->base_url(); ?>apps/asset/img/forkgithub.png" style="position: absolute; top: 0; right: 0; border: 0;" alt="Fork me on GitHub"></a>	
</body>
</html>