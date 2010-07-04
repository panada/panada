<?php defined('THISPATH') or die('Tidak diperkenankan mengakses file secara langsung.');
/**
 * Panada email API.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

class Library_email {
    
    /**
     * @var array   EN: Define the reception array variable.
     *              ID: Defenisikan variable penerima email.
     */
    public  $rcpt_to            = array();
    
    /**
     * @var string  EN: Var for saving user email(s) that just converted from $rcpt_to array.
     *              ID: Penimpan email-email yang sudah dikonversi dari array $rcpt_to.
     */
    private $rcpt_to_string     = '';
    
    /**
     * @var string  EN: Define email subject.
     *              ID: Defenisikan subject email.
     */
    public  $subject            = '';
    
    /**
     * @var string  EN: Define email content.
     *              ID: Defenisikan isi email.
     */
    public  $message            = '';
    
    /**
     * @var string  EN: Define email content type; plan or html.
     *              ID: Defenisikan tipe email yang akan dikirim; plan atau html.
     */
    public  $message_type       = 'plain';
    
    /**
     * @var string  EN: Define sender's email.
     *              ID: alamat email pengirim.
     */
    public  $from_email         = '';
    
    /**
     * @var string  EN: The sender name.
     *              ID: Nama pengirim.
     */
    public  $from_name          = '';
    
    /**
     * @var string  EN: Mail application option. The option is: native (PHP mail function) or smtp.
     *              ID: Opsi aplikasi pengirim email. Pilihannya adalah native (PHP mail function) atau smtp.
     */
    public  $mailer_type        = 'native';
    
    /**
     * @var integer EN: 1 = High, 3 = Normal, 5 = low.
     */
    public  $priority           = 3;
    
    /**
     * @var string  EN: SMTP server host.
     */
    public  $smtp_host          = '';
    
    /**
     * @var integer EN: SMTP server port.
     */
    public  $smtp_port          = 25;
    
    /**
     * @var string  EN: SMTP username.
     */
    public  $smtp_username      = '';
    
    /**
     * @var string  EN: SMTP password.
     */
    public  $smtp_password      = '';
    
    /**
     * @var integer EN: Define SMTP connection.
     *              ID: Defenisi koneksi SMTP.
     */
    private $smtp_connection    = 0;
    
    /**
     * @var integer EN: The SMTP connection timeout, in seconds.
     *              ID: Maksimum waktu untuk koneksi ke SMTP server.
     */
    private $timeout_connection = 30;
    
    /**
     * @var string  EN: String to say "helo/ehlo" to smtp server.
     */
    public  $smtp_ehlo_host     = 'localhost';
    
    /**
     * @var string  EN: Enter character.
     *              ID: Karakter enter.
     */
    private $break_line         = "\r\n";
    
    /**
     * @var array   EN: Group of debug messages.
     *              ID: Kumpulan pesan-pesan debug.
     */
    private $debug_messages     = array();
    
    /**
     * @var string  EN: Mailer useragent.
     */
    private $panada_x_mailer    = 'Panada Mailer Version 0.1';
    
    
    /**
     * EN: Main Panada method to send the email.
     * ID: Method utama untuk mengirim email.
     *
     * @param string | array
     * @param string
     * @param string
     * @param string
     * @param string
     * @return boolean
     */
    public function mail($rcpt_to = '', $subject = '', $message = '', $from_email = '', $from_name = ''){
        
        if( is_array($rcpt_to) ) {
            $this->rcpt_to  = $this->clean_email($rcpt_to);
        }
        else {
            
            $rcpt_break = explode(',', $rcpt_to);
            
            if( count($rcpt_break) > 0 )
                $this->rcpt_to  = $this->clean_email($rcpt_break);
            else
                $this->rcpt_to  = $this->clean_email(array($rcpt_to));
        }
        
        $this->subject          = $subject;
        $this->message          = $message;
        $this->from_email       = $from_email;
        $this->from_name        = $from_name;
        $this->rcpt_to_string   = implode(', ', $this->rcpt_to);
        
        if($this->smtp_host != '' || $this->mailer_type == 'smtp') {
            
            $this->mailer_type = 'smtp';
            return $this->smtp_send();
        }
        else {
            return $this->mailer_native();
        }
    }
    
    /**
     * EN: Print the debug messages.
     * ID: Tampilkan pesan-pesan debug.
     *
     * @return string
     */
    public function print_debug(){
        
        foreach($this->debug_messages as $message)
            echo $message.'<br />';
    }
    
    /**
     * EN:  Make the email address string lower and unspace.
     * ID:  Kecilkan karakter email dan hilangkan spasi.
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
     * EN: Built in mail function from PHP. This is the default function to send the email.
     * ID: Fungsi untuk mengirim email bawaan PHP. Ini adalah fungsi default untuk mengirim email.
     *
     * @return booelan
     */
    private function mailer_native(){
        
        if( ! mail($this->rcpt_to_string, $this->subject, $this->message, $this->header()) ) {
            $this->debug_messages[] = 'Error: Sending email failed';
            return false;
        }
        else {
            $this->debug_messages[] = 'Success: Sending email succeeded';
            return true;
        }
    }
    
    /**
     * EN: Socket write command function.
     * ID: Fungsi menulis command pada socket.
     *
     * @param string
     * @return void
     */
    private function write_command($command){
        
        fwrite($this->smtp_connection, $command);
    }
    
    /**
     * EN: Get string from smtp respnse.
     * ID: Mendapatkan string dari respnse smtp.
     *
     * @return string
     */
    private function get_smtp_response() {
        
        $return = '';
        
        while($str = fgets($this->smtp_connection, 515)) {
            
            $this->debug_messages[] = 'Success: ' . $str;
            
            $return .= $str;
            
            //EN: Stop the loop if we found space in 4th character.
            if(substr($str,3,1) == ' ')
                break;
        }
        
        return $return;
    }
    
    /**
     * EN: Open connection to smtp server.
     * ID: Membuka koneksi ke server smtp.
     *
     * @return boolean
     */
    private function smtp_connect() {
        
        //EN: Connect to smtp server
        $this->smtp_connection = fsockopen($this->smtp_host, $this->smtp_port, $errno, $errstr, $this->timeout_connection);
       
        if( empty($this->smtp_connection) ) {
            
            $this->debug_messages[] = 'Error: Failed to connect to server! Error number: ' .$errno . ' (' . $errstr . ')';
            
            return false;
        }
        
        //EN: Add extra time to get respnose from server.
        socket_set_timeout($this->smtp_connection, $this->timeout_connection, 0);
        
        $response = $this->get_smtp_response();
        $this->debug_messages[] = 'Success: ' . $response;
        
        return true;
    }
    
    /**
     * EN: Do login to smtp server.
     * ID: Melakukan otentifikasi ke server smtp.
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
        $this->write_command( base64_encode($this->smtp_username) . $this->break_line );
        
        $response = $this->get_smtp_response();
        $code = substr($response, 0, 3);
        
        if($code != 334){
            
            $this->debug_messages[] = 'Error: Username not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
            
            return false;
        }
        
        // Send encoded password
        $this->write_command( base64_encode($this->smtp_password) . $this->break_line );
        
        $response = $this->get_smtp_response();
        $code = substr($response, 0, 3);
        
        if($code != 235) {
            
            $this->debug_messages[] = 'Error: Password not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
           
            return false;
        }
        
        return true;
    }
    
    /**
     * EN: Close smtp connection.
     * ID: Tutup koneksi smtp.
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
     * EN: Initate the smtp ehlo function.
     * ID: Memulai fungsi ehlo smtp.
     *
     * @return boolean
     */
    private function make_ehlo() {  
        
        /**
         * EN: IF smtp not accpeted EHLO then try HELO.
         * ID: Jika smtp tidak menerima EHLO, coba dengan HELO.
         */
        if( ! $this->smtp_ehlo('EHLO') )
            if( ! $this->smtp_ehlo('HELO') )
                return false;
        
        return true;
    }
    
    /**
     * EN: Say ehlo to smtp server.
     * ID: Ucapkan ehlo pada server smtp.
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
     * EN: This is email from method.
     * ID: Fungsi email dari.
     *
     * @return boolean
     */
    private function smtp_from() {
        
        $this->write_command("MAIL FROM:<" . $this->from_email . ">" . $this->break_line);
        
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
     * EN: Email to method.
     * ID: Method email dari.
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
     * EN: Create the email header.
     * ID: Buat header untuk email.
     *
     * @return string
     */
    private function header(){
        
        $from_name  = ($this->from_name != '') ? $this->from_name : $this->from_email;
        
        $headers['from']        = 'From: ' . $from_name . ' <' . $this->from_email . '>' . $this->break_line;
        $headers['priority']    = 'X-Priority: '. $this->priority . $this->break_line;
        $headers['mailer']      = 'X-Mailer: ' .$this->panada_x_mailer . $this->break_line;
        $headers['mime']        = 'MIME-Version: 1.0' . $this->break_line;
        $headers['cont_type']   = 'Content-type: text/'.$this->message_type.'; charset=iso-8859-1' . $this->break_line;
        
        if($this->mailer_type == 'native') {
            $return = '';
            foreach($headers as $headers)
                $return .= $headers;
            
            return $return;
        }
        else {
            
            // EN: Additional headers needed by smtp.
            $this->write_command('To: ' . $this->rcpt_to_string . $this->break_line);
            $this->write_command('Subject:' . $this->subject. $this->break_line);
            
            foreach($headers as $key => $val) {
                
                if($key == 'cont_type')
                    $val = str_replace($this->break_line, "\n\n", $val);
                
                $this->write_command($val);
            }
        }
    }
    
    /**
     * EN: Send the mail data.
     * ID: Kirim data email.
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
        
        
        //EN: All messages have sent
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
     * EN: execute the smtp connection.
     *
     * @return boolean
     */
    private function do_connect() {
        
        if( $this->smtp_connect() ) {
            
            $this->make_ehlo();
            
            if( ! empty($this->smtp_username) ){
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
     * EN: Sending the data to smtp
     * ID: Kirim data ke sever smtp.
     *
     * @return boolean
     */
    private function smtp_send() {
       
        if(!$this->do_connect())
            return false;
        
        if( ! $this->smtp_from())
            return false;
        
        foreach($this->rcpt_to as $recipient)
            $this->smtp_recipient($recipient);
        
        if( ! $this->smtp_data() )
            return false;
        
        $this->smtp_close();
        
        return true;
    }
    
} // End email class