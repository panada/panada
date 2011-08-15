<?php defined('THISPATH') or die('Can\'t access directly!');

class Controller_home extends Panada {
    
    public function __construct(){        
        parent::__construct();
    }
    
    public function index(){
        $views['doc_type']      = $this->html->doctype('xhtml1-strict');
        $views['css_file']      = $this->html->load_css( $this->config->base_url() . 'apps/asset/css/main.css', false);
        $views['page_title']    = 'Hello World!';
        $views['head']          = 'Panada has been installed successfully!';
        $views['content']       = '<p>This is sample page. You find this file at:</p>';
        $views['content']      .= '<code>'.pathinfo(APPLICATION, PATHINFO_BASENAME).'/view/index.php</code>';
        $views['content']      .= '<code>'.pathinfo(APPLICATION, PATHINFO_BASENAME).'/controller/home.php</code>';
        $views['footer']        = '<div id="foot">Powered by <a href="http://panadaframework.com/">Panada</a> version '.PANADA_VERSION.'';
        $views['footer']       .= '<span class="right">Page rendered in '.Library_time_execution::stop().' seconds</span></div>';
        $this->view_index($views);
    }
}
