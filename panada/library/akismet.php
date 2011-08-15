<?php
/*
 * Akismet library as comment checker for SPAM
 * Made for Panada framework. Tested on Panada v0.2.1 and v0.3.1
 * Author: Aryo Pinandito (aryoxp@gmail.com)
 * Requirements:
 * - Akismet API key, sign up for your key here: https://akismet.com/signup/
   - A working installation of Panada framework
 
    Akismet library for Panada Framework v1.0
    Copyright (C) 2011 Aryo Pinandito (aryoxp@gmail.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 Quick README on how to check a comment for SPAM:
 
 	INSTALLATION:
	copy akismet.php on your apps/library/ and you're done.
 
	// How to call for comment checking
	$data = array('blog' => 'http://yourblogdomainname.com',
				  'user_ip' => '127.0.0.1',
				  'user_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6',
				  'referrer' => 'http://www.google.com',
				  'permalink' => 'http://yourblogdomainname.com/blog/post=1',
				  'comment_type' => 'comment',
				  'comment_author' => 'admin',
				  'comment_author_email' => 'test@test.com',
				  'comment_author_url' => 'http://www.CheckOutMyCoolSite.com',
				  'comment_content' => 'It means a lot that you would take the time to review our software.  Thanks again.');
				  
	$akismet = new library_akismet();
	$result = $akismet->akismet_comment_check( '123YourAPIKey', $data ); 
	
	if( $result ) echo "Hooray! The message is clean!";
	else
		echo "Ooops, I think I found a SPAM. :(";
 
 */

class Library_akismet {
	 
    public function __construct(){
        require APPLICATION . 'config.php';        
        foreach($CONFIG as $key => $val)
            $this->$key = Library_tools::array_to_object($val);
    }
		
	public function akismet_verify_key( $key, $blog ) {
		$blog = urlencode($blog);
		$request = 'key='. $key .'&blog='. $blog;
		$host = $http_host = 'rest.akismet.com';
		$path = '/1.1/verify-key';
		$port = 80;
		$akismet_ua = "WordPress/3.1.1 | Akismet/2.5.3";
		$content_length = strlen( $request );
		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$http_request .= "Content-Length: {$content_length}\r\n";
		$http_request .= "User-Agent: {$akismet_ua}\r\n";
		$http_request .= "\r\n";
		$http_request .= $request;
		$response = '';
		if( false != ( $fs = @fsockopen( $http_host, $port, $errno, $errstr, 10 ) ) ) {
			 
			fwrite( $fs, $http_request );
	 
			while ( !feof( $fs ) )
				$response .= fgets( $fs, 1160 ); // One TCP-IP packet
			fclose( $fs );
			 
			$response = explode( "\r\n\r\n", $response, 2 );
		}
		 
		if ( 'valid' == @$response[1] )
			return true;
		else
			return false;
	}
		
	// Passes back true (it's spam) or false (it's ham)
	public function akismet_comment_check( $key, $data ) {
		$request = 'blog='. urlencode($data['blog']) .
				   '&user_ip='. urlencode($data['user_ip']) .
				   '&user_agent='. urlencode($data['user_agent']) .
				   '&referrer='. urlencode($data['referrer']) .
				   '&permalink='. urlencode($data['permalink']) .
				   '&comment_type='. urlencode($data['comment_type']) .
				   '&comment_author='. urlencode($data['comment_author']) .
				   '&comment_author_email='. urlencode($data['comment_author_email']) .
				   '&comment_author_url='. urlencode($data['comment_author_url']) .
				   '&comment_content='. urlencode($data['comment_content']);
		$host = $http_host = $key.'.rest.akismet.com';
		$path = '/1.1/comment-check';
		$port = 80;
		$akismet_ua = "WordPress/3.1.1 | Akismet/2.5.3";
		$content_length = strlen( $request );
		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$http_request .= "Content-Length: {$content_length}\r\n";
		$http_request .= "User-Agent: {$akismet_ua}\r\n";
		$http_request .= "\r\n";
		$http_request .= $request;
		$response = '';
		if( false != ( $fs = @fsockopen( $http_host, $port, $errno, $errstr, 10 ) ) ) {
			 
			fwrite( $fs, $http_request );
	 
			while ( !feof( $fs ) )
				$response .= fgets( $fs, 1160 ); // One TCP-IP packet
			fclose( $fs );
			 
			$response = explode( "\r\n\r\n", $response, 2 );
		}
		if ( 'true' == @$response[1] )
			return true;
		else
			return false;
	}
	
	public function akismet_submit_spam( $key, $data ) {
		$request = 'blog='. urlencode($data['blog']) .
				   '&user_ip='. urlencode($data['user_ip']) .
				   '&user_agent='. urlencode($data['user_agent']) .
				   '&referrer='. urlencode($data['referrer']) .
				   '&permalink='. urlencode($data['permalink']) .
				   '&comment_type='. urlencode($data['comment_type']) .
				   '&comment_author='. urlencode($data['comment_author']) .
				   '&comment_author_email='. urlencode($data['comment_author_email']) .
				   '&comment_author_url='. urlencode($data['comment_author_url']) .
				   '&comment_content='. urlencode($data['comment_content']);
		$host = $http_host = $key.'.rest.akismet.com';
		$path = '/1.1/submit-spam';
		$port = 80;
		$akismet_ua = "WordPress/3.1.1 | Akismet/2.5.3";
		$content_length = strlen( $request );
		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$http_request .= "Content-Length: {$content_length}\r\n";
		$http_request .= "User-Agent: {$akismet_ua}\r\n";
		$http_request .= "\r\n";
		$http_request .= $request;
		$response = '';
		if( false != ( $fs = @fsockopen( $http_host, $port, $errno, $errstr, 10 ) ) ) {
			 
			fwrite( $fs, $http_request );
	 
			while ( !feof( $fs ) )
				$response .= fgets( $fs, 1160 ); // One TCP-IP packet
			fclose( $fs );
			 
			$response = explode( "\r\n\r\n", $response, 2 );
		}
		 
		if ( 'valid' == @$response[1] )
			return true;
		else
			return false;
	}

	public function akismet_submit_ham( $key, $data ) {
		$request = 'blog='. urlencode($data['blog']) .
				   '&user_ip='. urlencode($data['user_ip']) .
				   '&user_agent='. urlencode($data['user_agent']) .
				   '&referrer='. urlencode($data['referrer']) .
				   '&permalink='. urlencode($data['permalink']) .
				   '&comment_type='. urlencode($data['comment_type']) .
				   '&comment_author='. urlencode($data['comment_author']) .
				   '&comment_author_email='. urlencode($data['comment_author_email']) .
				   '&comment_author_url='. urlencode($data['comment_author_url']) .
				   '&comment_content='. urlencode($data['comment_content']);
		$host = $http_host = $key.'.rest.akismet.com';
		$path = '/1.1/submit-ham';
		$port = 80;
		$akismet_ua = "WordPress/3.1.1 | Akismet/2.5.3";
		$content_length = strlen( $request );
		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$http_request .= "Content-Length: {$content_length}\r\n";
		$http_request .= "User-Agent: {$akismet_ua}\r\n";
		$http_request .= "\r\n";
		$http_request .= $request;
		$response = '';
		if( false != ( $fs = @fsockopen( $http_host, $port, $errno, $errstr, 10 ) ) ) {
			 
			fwrite( $fs, $http_request );
	 
			while ( !feof( $fs ) )
				$response .= fgets( $fs, 1160 ); // One TCP-IP packet
			fclose( $fs );
			 
			$response = explode( "\r\n\r\n", $response, 2 );
		}
		 
		if ( 'valid' == @$response[1] )
			return true;
		else
			return false;
	}
	
}
?>