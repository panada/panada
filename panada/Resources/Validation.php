<?php
/**
 * Panada validation API.
 *
 * @package	Resources
 * @link	http://panadaframework.com/
 * @license	http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 * @since	Version 0.1
 */
namespace Resources;

class Validation
{
    private
	$errorMessages = array(),
	$rules = array(),
	$validValues = array();
    
    protected
	$ruleErrorMessages = array();
	
    public function __construct()
    {
	$this->setRuleErrorMessages();
    }
    
    public function trimLower($string)
    {    
        return trim(strtolower($string));
    }
    
    public function isEmail($string)
    {
	$string = $this->trimLower($string);
        
	$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
	
        if (strpos($string, '@') === false && strpos($string, '.') === false)
            return false;
	
        if ( ! preg_match($chars, $string))
            return false;
        
        return $string;
    }
    
    public function isUrl($string)
    {    
        $string = $this->trimLower($string);
        return filter_var($string, FILTER_VALIDATE_URL);
        /*
        $chars = '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i';
        
        if( ! preg_match( $chars, $string ))
            return false;
        else
            return $string;
        */
    }
    
    public function stripNumeric($string)
    {    
        return filter_var($string, FILTER_SANITIZE_NUMBER_INT);
        //return preg_replace('/[^0-9]/', '', $string);
    }
    
    public function isPositiveNumeric($string)
    {
        return (bool) preg_match( '/^[0-9]*\.?[0-9]+$/', $string);
    }
    
    /**
     * Use this for validate user first name and last name.
     */
    public function displayName($string)
    {    
        //Only permit a-z, 0-9 and .,'" space this is enough for a name right?
        
        //'/[^a-zA-Z0-9s -_.,]/'
        $string = $this->trimLower($string);
        $string = strip_tags($string);
        $string = stripslashes($string);
        $string = preg_replace( '/[^a-zA-Z0-9s .,"\']/', '', $string);
        
        return ucwords($string);
    }
    
    /**
     * specify the validation rule
     */
    public function setRules()
    {
	return array();
    }
    
    /**
     * performs the validation
     */
    public function validate( $fields = array() )
    {
	$return = true;
	$rules = $this->setRules();
	
	foreach($fields as $field => $value) {
	    
	    // applay filter if any
	    if( isset($rules[$field]['filter']) ) {
		
		foreach($rules[$field]['filter'] as $filter)
		    $value = $filter($value);
	    }
	    
	    if( isset($rules[$field]) ) {
		
		foreach($rules[$field]['rules'] as $key => $rule) {
		    
		    if( is_numeric($key) ) {
			
			$method = 'rule'.ucwords($rule);
			
			$response = $this->$method($field, $value, $rules[$field]['label']);
		    }
		    else {
			
			if( $key == 'callback' ) {
			    
			    $response = $this->$rule($field, $value, $rules[$field]['label']);
			}
			else {
			    
			    $method = 'rule'.ucwords($key);
			    
			    $response = $this->$method($field, $value, $rules[$field]['label'], $rule);
			}
		    }
		    
		    if( ! $response ) {
			
			$return = false;
			
			unset($this->validValues[$field]);
			break;
		    }
		    else {
			
			$this->validValues[$field] = $value;
		    }
		}
	    }
	    
	}
	
	return $return;
    }
    
    /**
     * Populate the error message(s)
     */
    public function errorMessages($field = false)
    {
	if( empty($this->errorMessages) )
	    return null;
	
	return $this->errorMessages;
    }
    
    /**
     * Setter for error message
     */
    public function setErrorMessage($field, $message)
    {
	$this->errorMessages[$field] = $message;
    }
    
    public function value($field)
    {
	if( ! isset($this->validValues[$field]) )
	    return null;
	
	return $this->validValues[$field];
    }
    
    private function ruleErrorMessage($rule, $label)
    {
	return str_replace('%label%', $label, $this->ruleErrorMessages[$rule]);
    }
    
    private function ruleRequired($field, $value, $label)
    {
	if( empty($value) ) {
	    
	    $this->setErrorMessage( $field, $this->ruleErrorMessage('required', $label) );
	    
	    return false;
	}
	
	return true;
    }
    
    private function ruleEmail($field, $value, $label)
    {
	if( ! $this->isEmail($value) ) {
	    
	    $this->setErrorMessage($field, $this->ruleErrorMessage('email', $label));
	    
	    return false;
	}
	
	return true;
    }
    
    private function ruleMin($field, $value, $label, $minVal)
    {
	if( strlen($value) < $minVal ) {
	    
	    $this->setErrorMessage($field, str_replace(array('%label%', '%size%'), array($label, $minVal), $this->ruleErrorMessages['min']));
	    return false;
	}
	
	return true;
    }
    
    private function ruleMax($field, $value, $label, $maxVal)
    {
	if( strlen($value) > $maxVal ) {
	    
	    $this->setErrorMessage($field, str_replace(array('%label%', '%size%'), array($label, $maxVal), $this->ruleErrorMessages['max']));
	    return false;
	}
	
	return true;
    }
    
    private function setRuleErrorMessages()
    {
	$this->ruleErrorMessages = array(
	    'required' => '%label% can not be empty',
	    'email' => '%label% not email valid format',
	    'min' => '%label% need more then %size% character',
	    'max' => '%label% need less then %size% character'
	);
    }
}