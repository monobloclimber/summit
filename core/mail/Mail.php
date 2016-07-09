<?php
/**
 * Mail class
 * ------------------------------------ 
 * Allows to send emails
 * 
 */

namespace Core\Mail;

use \Core\Template\Template;

class Mail{

	public $to;
	public $subject;
	public $headers  = '';
	public $additional_headers = '';
	public $html;
	public $from     = null;
	public $fromName = null;
	public $reply    = null;
	public $cc       = null;
	public $bcc      = null;
	public $message;
	private $boundary;

	public function __construct(){
		$this->html = 'Content-Type: text/html; charset="UTF-8"'."\n";
		$this->headers = $this->html."\n";
		$this->boundary = md5(uniqid(rand(), true));
	}

	public function to($to){
		$this->to = $to;
	}

	public function subject($subject){
		$this->subject = $subject;
	}

	public function from($from, $fromName = null){
		if($fromName){
			$this->fromName = $fromName;
		}
		$this->from = $from;
	}

	public function reply($reply){
		$this->reply = $reply;
	}

	public function cc($cc){
		$this->cc = $cc;
	}

	public function bcc($bcc){
		$this->bcc = $bcc;
	}

	public function message($template, $data){
		$this->render = new Template;
		ob_start();
		$this->render->make($template, $data);
		$this->message .= ob_get_clean();
	}

	public function attach($file, $filename, $mime){
		$headers = $this->headers;
		$message = $this->message;

		$this->headers = '';
		$this->additional_headers .= 'Content-Type: multipart/mixed;'."\n".' boundary="'.$this->boundary.'"';

		$this->message = 'This is a multi-part message in MIME format.'."\n";
		$this->message .= '--'.$this->boundary."\n";
		$this->message .= $headers."\n";
		$this->message .= $message."\n";
		$this->message .= '--'.$this->boundary."\n";
		$this->message .= 'Content-Type: '.$mime.'; name="'.$filename.'"'."\n";
		$this->message .= 'Content-Transfer-Encoding: base64'."\n";
		$this->message .= 'Content-Disposition: attachment; filename="'.$filename.'"'."\n\n";

		$source = file_get_contents($file);
		$source = base64_encode ($source);
		$source = chunk_split($source);

		$this->message .= $source."\n";
		$this->message .= '--'.$this->boundary.'--';
	}

	public function send(){
		$headers = $this->headers;
		$this->headers = '';

		if($this->from && $this->fromName){
			$this->headers .= 'From: '.$this->fromName.' <'.$this->from.'>'."\n";
		}elseif($this->from){
			$this->headers .= 'From: '.$this->from."\n";
		}

		if($this->reply){
			$this->headers .= 'Reply-To: '.$this->reply."\n";
		}

		if($this->cc){
			$this->headers .= 'Cc: '.$this->cc."\n";
		}

		if($this->bcc){
			$this->headers .= 'Bcc: '.$this->bcc."\n";
		}

		$this->headers = $this->headers.$headers.$this->additional_headers;

		return mail($this->to, $this->subject, $this->message, $this->headers);
	}
}