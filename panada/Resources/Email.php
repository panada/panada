<?php
/**
 * Panada email API.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */
namespace Resources;

class Email {
    
    /**
     * @var array   Define the reception array variable.
     */
    public  $rcptTo            = array();
    
    /**
     * @var string  Var for saving user email(s) that just converted from $rcptTo array.
     */
    private $rcptToCtring     = '';
    
    /**
     * @var string  Define email subject.
     */
    public  $subject            = '';
    
    /**
     * @var string  Define email content.
     *              ID: Defenisikan isi email.
     */
    public  $message            = '';
    
    /**
     * @var string  Define email content type; plan or html.
     */
    public  $messageType       = 'plain';
    
    /**
     * @var string  Define sender's email.
     */
    public  $fromEmail         = '';
    
    /**
     * @var string  The sender name.
     */
    public  $fromName          = '';
    
    /**
     * @var string  Mail application option. The option is: native (PHP mail function) or smtp.
     */
    public  $mailerType        = 'native';
    
    /**
     * @var integer 1 = High, 3 = Normal, 5 = low.
     */
    public  $priority           = 3;
    
    /**
     * @var string  SMTP server host.
     */
    public  $smtpHost          = '';
    
    /**
     * @var integer SMTP server port.
     */
    public  $smtpPort          = 25;
    
    /**
     * @var string | bool SMTP secure type.
     */
    public  $smtpSecure        = false;
    
    /**
     * @var string  SMTP username.
     */
    public  $smtpUsername      = '';
    
    /**
     * @var string  SMTP password.
     */
    public  $smtpPassword      = '';
    
    /**
     * @var integer Define SMTP connection.
     */
    private $smtp_connection    = 0;
    
    /**
     * @var integer The SMTP connection timeout, in seconds.
     */
    private $timeout_connection = 30;
    
    /**
     * @var string  String to say "helo/ehlo" to smtp server.
     */
    public  $smtp_ehlo_host     = 'localhost';
    
    /**
     * @var string  Enter character.
     *              ID: Karakter enter.
     */
    private $break_line         = "\r\n";
    
    /**
     * @var array   Group of debug messages.
     */
    private $debug_messages     = array();
    
    /**
     * @var string  Mailer useragent.
     */
    private $panada_x_mailer    = 'Panada Mailer Version 0.3';
    
    
    /**
     * Main Panada method to send the email.
     *
     * @param string | array
     * @param string
     * @param string
     * @param string
     * @param string
     * @return boolean
     */
    public function mail($rcptTo = '', $subject = '', $message = '', $fromEmail = '', $fromName = ''){
        
        if( is_array($rcptTo) ) {
            $this->rcptTo  = $this->clean_email($rcptTo);
        }
        else {
            
            $rcpt_break = explode(',', $rcptTo);
            
            if( count($rcpt_break) > 0 )
                $this->rcptTo  = $this->clean_email($rcpt_break);
            else
                $this->rcptTo  = $this->clean_email(array($rcptTo));
        }
        
        $this->subject          = $subject;
        $this->message          = $message;
        $this->fromEmail       = $fromEmail;
        $this->fromName        = $fromName;
        $this->rcptToCtring   = implode(', ', $this->rcptTo);
        
        if($this->smtpHost != '' || $this->mailerType == 'smtp') {
            
            $this->mailerType = 'smtp';
            return $this->smtp_send();
        }
        else {
            return $this->mailer_native();
        }
    }
    
    /**
     * Print the debug messages.
     *
     * @return string
     */
    public function print_debug(){
        
        foreach($this->debug_messages as $message)
            echo $message.'<br />';
    }
    
    /**
     *  Make the email address string lower and unspace.
     *
     * @param string
     * @return array
     */
    private function clean_email($email){
        
        foreach($email as $email)
            $return[] = trim(strtolower($email));
        
        return $return;
    }
    
    /**
     * Built in mail function from PHP. This is the default function to send the email.
     *
     * @return booelan
     */
    private function mailer_native(){
        
        if( ! mail($this->rcptToCtring, $this->subject, $this->message, $this->header()) ) {
            $this->debug_messages[] = 'Error: Sending email failed';
            return false;
        }
        else {
            $this->debug_messages[] = 'Success: Sending email succeeded';
            return true;
        }
    }
    
    /**
     * Socket write command function.
     *
     * @param string
     * @return void
     */
    private function write_command($command){
        
        fwrite($this->smtp_connection, $command);
    }
    
    /**
     * Get string from smtp respnse.
     *
     * @return string
     */
    private function get_smtp_response() {
        
        $return = '';
        
        while($str = fgets($this->smtp_connection, 515)) {
            
            $this->debug_messages[] = 'Success: ' . $str;
            
            $return .= $str;
            
            //Stop the loop if we found space in 4th character.
            if(substr($str,3,1) == ' ')
                break;
        }
        
        return $return;
    }
    
    /**
     * Open connection to smtp server.
     *
     * @return boolean
     */
    private function smtp_connect() {
        
        //Connect to smtp server
        $this->smtp_connection = fsockopen(
                                    ($this->smtpSecure && $this->smtpSecure == 'ssl' ? 'ssl://' : '').$this->smtpHost,
                                    $this->smtpPort,
                                    $errno,
                                    $errstr,
                                    $this->timeout_connection
                                );
       
        if( empty($this->smtp_connection) ) {
            
            $this->debug_messages[] = 'Error: Failed to connect to server! Error number: ' .$errno . ' (' . $errstr . ')';
            
            return false;
        }
        
        //Add extra time to get respnose from server.
        socket_set_timeout($this->smtp_connection, $this->timeout_connection, 0);
        
        $response = $this->get_smtp_response();
        $this->debug_messages[] = 'Success: ' . $response;
        
        return true;
    }
    
    /**
     * Do login to smtp server.
     *
     * @return boolean
     */
    private function smtp_login() {
        
        //SMTP authentication command
        $this->write_command('AUTH LOGIN' . $this->break_line);
        
        $response = $this->get_smtp_response();
        $code = substr($response, 0, 3);
        
        if($code != 334) {
            
            $this->debug_messages[] = 'Error: AUTH not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
            
            return false;
        }
        
        // Send encoded username
        $this->write_command( base64_encode($this->smtpUsername) . $this->break_line );
        
        $response = $this->get_smtp_response();
        $code = substr($response, 0, 3);
        
        if($code != 334){
            
            $this->debug_messages[] = 'Error: Username not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
            
            return false;
        }
        
        // Send encoded password
        $this->write_command( base64_encode($this->smtpPassword) . $this->break_line );
        
        $response = $this->get_smtp_response();
        $code = substr($response, 0, 3);
        
        if($code != 235) {
            
            $this->debug_messages[] = 'Error: Password not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
           
            return false;
        }
        
        return true;
    }
    
    /**
     * Close smtp connection.
     *
     * @return void
     */
    private function smtp_close() {
        
        if( ! empty($this->smtp_connection) ) {
            fclose($this->smtp_connection);
            $this->smtp_connection = 0;
        }
    }
    
    /**
     * Initate the smtp ehlo function.
     *
     * @return boolean
     */
    private function make_ehlo() {  
        
        /**
         * IF smtp not accpeted EHLO then try HELO.
         */
        if( ! $this->smtp_ehlo('EHLO') )
            if( ! $this->smtp_ehlo('HELO') )
                return false;
        
        return true;
    }
    
    /**
     * Say ehlo to smtp server.
     *
     * @param string
     * @return boolean
     */
    private function smtp_ehlo($hello) {
        
        $this->write_command( $hello . ' ' . $this->smtp_ehlo_host . $this->break_line);
        
        $response = $this->get_smtp_response();
        $code = substr($response, 0, 3);
        
        $this->debug_messages[] = 'Success: helo reply from server is: ' . $response;
        
        if($code != 250){
            
            $this->debug_messages[] = 'Error: '.$hello.' not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
            
            return false;
        }
        
        return true;
    }
    
    /**
     * This is email from method.
     *
     * @return boolean
     */
    private function smtp_from() {
        
        $this->write_command("MAIL FROM:<" . $this->fromEmail . ">" . $this->break_line);
        
        $response = $this->get_smtp_response();
        $code = substr($response, 0, 3);
        
        $this->debug_messages[] = 'Success: ' . $response;
        
        if($code != 250) {
            
            $this->debug_messages[] = 'Error: MAIL not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Email to method.
     *
     * @param string
     * @return boolean
     */
    private function smtp_recipient($to) {
        
        $this->write_command("RCPT TO:<" . $to . ">" . $this->break_line);
        
        $response = $this->get_smtp_response();
        $code = substr($response, 0, 3);
        
        $this->debug_messages[] = 'Success: ' . $response;
        
        if($code != 250 && $code != 251) {
            
            $this->debug_messages[] = 'Error: RCPT not accepted from server! Error number: ' .$code . ' (' . substr($response,4) . ')';
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Create the email header.
     *
     * @return string
     */
    private function header(){
        
        $fromName  = ($this->fromName != '') ? $this->fromName : $this->fromEmail;
        
        $headers['from']        = 'From: ' . $fromName . ' <' . $this->fromEmail . '>' . $this->break_line;
        $headers['priority']    = 'X-Priority: '. $this->priority . $this->break_line;
        $headers['mailer']      = 'X-Mailer: ' .$this->panada_x_mailer . $this->break_line;
        $headers['mime']        = 'MIME-Version: 1.0' . $this->break_line;
        $headers['cont_type']   = 'Content-type: text/'.$this->messageType.'; charset=iso-8859-1' . $this->break_line;
        
        if($this->mailerType == 'native') {
            $return = '';
            foreach($headers as $headers)
                $return .= $headers;
            
            return $return;
        }
        else {
            
            // Additional headers needed by smtp.
            $this->write_command('To: ' . $this->rcptToCtring . $this->break_line);
            $this->write_command('Subject:' . $this->subject. $this->break_line);
            
            foreach($headers as $key => $val) {
                
                if($key == 'cont_type')
                    $val = str_replace($this->break_line, "\n\n", $val);
                
                $this->write_command($val);
            }
        }
    }
    
    /**
     * Send the mail data.
     *
     * @return boolean
     */
    private function smtp_data() {
        
        $this->write_command('DATA' . $this->break_line);
        
        $response = $this->get_smtp_response();
        $code = substr($response, 0, 3);
        
        $this->debug_messages[] = 'Success: ' . $response;
        
        if($code != 354) {
            
            $this->debug_messages[] = 'Error: DATA command not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
            
            return false;
        }
        
        $this->header();
        $this->write_command($this->message . $this->break_line);
        
        
        //All messages have sent
        $this->write_command( $this->break_line . '.' . $this->break_line);
        
        $response = $this->get_smtp_response();
        $code = substr($response, 0, 3);
        
        $this->debug_messages[] = 'Success: ' . $response;
        
        if($code != 250){
            
            $this->debug_messages[] = 'Error: DATA command not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
            
            return false;
        }
        
        return true;
    }
   
    /**
     * execute the smtp connection.
     *
     * @return boolean
     */
    private function do_connect() {
        
        if( $this->smtp_connect() ) {
            
            $this->make_ehlo();
            
            if( ! empty($this->smtpUsername) ){
                if( ! $this->smtp_login() )
                    $connection = false;
            }
            
            $connection = true;
        }
           
        if( ! $connection )
            return false;
        
        return $connection;
    }
    
    /**
     * Sending the data to smtp
     *
     * @return boolean
     */
    private function smtp_send() {
       
        if(!$this->do_connect())
            return false;
        
        if( ! $this->smtp_from())
            return false;
        
        foreach($this->rcptTo as $recipient)
            $this->smtp_recipient($recipient);
        
        if( ! $this->smtp_data() )
            return false;
        
        $this->smtp_close();
        
        return true;
    }
    
}