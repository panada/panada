<?php
/**
 * Hendler for controller process.
 *
 * @author Iskandar Soesman <k4ndar@yahoo.com>
 * @link http://panadaframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @since version 0.1.0
 * @package Resources
 */
namespace Resources;

class Controller {
    
    private $childNamespace, $viewCache, $viewFile;
    public $config = array();
    
    public function __construct(){
        
        $child      = get_class($this);
        $this->uri  = Uri::$cacheObj;
        
        $this->childClass = array(
                            'namespaceArray' => explode( '\\', $child),
                            'namespaceString' => $child
                        );
        
    }
    
    public function __get($class){
        
        $classNamespace = array(
            'model' => 'Models',
            'Model' => 'Models',
            'models' => 'Models',
            'Models' => 'Models',
            'library' => 'Libraries',
            'Library' => 'Libraries',
            'libraries' => 'Libraries',
            'Libraries' => 'Libraries',
        );
        
        try{
            if( ! isset($classNamespace[$class]) )
                throw new \Exception('Undefined property '.$class);
        }
        catch(\Exception $e){
            $arr = $e->getTrace();
            RunException::outputError($e->getMessage(), $arr[0]['file'], $arr[0]['line']);
        }
        
        
        return new PropertiesLoader($this->childClass['namespaceArray'], $classNamespace[$class]);
    }
    
    public static function outputError($file, $data = array(), $isReturnValue = false){
        
        $controller = new Controller;
        $controller->output($file, $data, $isReturnValue);
    }
    
    public function output( $panadaViewfile, $data = array(), $isReturnValue = false ){
        
        $panadaFilePath = APP.'views/'.$panadaViewfile;
        
        if( $this->childClass['namespaceArray'][0] == 'Modules' ){
            $configMain = Config::main();
            $panadaFilePath = $configMain['module']['path'].$this->childClass['namespaceArray'][0].'/'.$this->childClass['namespaceArray'][1].'/views/'.$panadaViewfile;
        }
        
        try{
            if( ! file_exists($this->viewFile = $panadaFilePath.'.php') )
                throw new \Resources\RunException('View file in '.$panadaViewFile.' does not exits');
        }
        catch(\Resources\RunException $e){
            $arr = $e->getTrace();
            RunException::outputError($e->getMessage(), $arr[0]['file'], $arr[0]['line']);
        }
        
        if( ! empty($data) ){
            $this->viewCache = array(
                'data' => $data,
                'prefix' => $this->childClass['namespaceString'],
            );
        }
        
        // We don't need this variables anymore.
        unset($panadaViewFile, $data, $panadaFilePath);
        
        if( ! empty($this->viewCache) && $this->viewCache['prefix'] == $this->childClass['namespaceString'] )
            extract( $this->viewCache['data'], EXTR_SKIP );
        
        include_once $this->viewFile;
    }
    
    public function outputJSON($data, $isReturnValue = false){
        
    }
    
    public function outputXML($data, $isReturnValue = false){
        
    }
    
    public function location($location = ''){
	return $this->uri->baseUri . $this->uri->indexFile . $location;
    }
    
    public function redirect($location = '', $status = 302){
        
        $location = ( empty($location) ) ? $this->location() : $location;
        
        if ( substr($location,0,4) != 'http' )
            $location = $this->location() . $location;
        
        header('Location:' . $location, true, $status);
        exit;
    }
}