<?php

/**
 * @author Nikhil Kataria <nikhil@projectheena.com>
 * 
 * Following are email types
 * 
 * 1 - authorization related mails (confirm registration, sign up, forgot password)
 * 2 - 
 */
class Emailer {

    var $ci;

    function __construct() {
        $this->ci = & get_instance();
    }

    function sign_up_email($data) {
        $name = isset($data['name']) ? $data['name'] : $data["first_name"] . ' ' . $data["last_name"];
        $emailData = array(
            'name' => $name,
            'to_email' => $data["email"],
            'from_name' => FROM_NAME,
            'from_email' => NO_REPLY_ID,
            'reply_to' => NO_REPLY_ID,
            'subject' => 'Welcome to Owljob',
            'body' => $this->ci->load->view('emailer/signup', $data, TRUE),
            'type' => 1,
        );
        $sparkPostData = json_encode(
                array('content' =>
                    array('from' => array('email' => NO_REPLY_ID, 'name' => FROM_NAME), 'subject' => $emailData['subject'],
                        "text" => strip_tags($emailData['body']),
                        "html" => $emailData['body']
                    ),
                    'recipients' => array(array('address' => array('email' => $emailData['to_email'])))));
        if ($this->_sparkPost($sparkPostData)) {
            $emailData['status'] = 1;
            return $this->ci->db->insert('email', $emailData);
        } else {
            return $this->ci->db->insert('email', $emailData);
        }
        return $this->ci->db->insert('email', $emailData);
    }

    function verify($data) {
        $name = isset($data['name']) ? $data['name'] : '';
        $emailData = array(
            'name' => $name,
            'to_email' => $data["email"],
            'from_name' => FROM_NAME,
            'from_email' => NO_REPLY_ID,
            'reply_to' => NO_REPLY_ID,
            'subject' => 'Verify your account',
            'body' => $this->ci->load->view('emailer/verify', $data, TRUE),
            'type' => 1,
        );
         $email = array(
                "from" => FROM_NAME,
                "return_path" => NO_REPLY_ID,
                "subject" => $emailData['subject'],
                'html' => $this->ci->load->view('emailer/verify', $data, TRUE),
                'name' => $name,
                'email' => $data["email"],
            );
        $status = $this->send_mail($email);
        if ($status) {
            $emailData['status'] = 1;
            return $this->ci->db->insert('email', $emailData);
        } else {
            return $this->ci->db->insert('email', $emailData);
        }
    }

    function send_mail($data) {
        $status = FALSE;
        $this->ci->load->config('email');
        $this->ci->load->library('email');
        $from = $data['return_path'];
        $formName = $data['from'];
        $to = $data['email'];
        $toName = $data['name'];
        $cc_email = isset($data['cc_email']) ? $data['cc_email'] : "";
        $bcc_email = isset($data['bcc_email']) ? $data['bcc_email'] : "";
        $subject = $data['subject']; 
        $message = $data['html']; 
        $this->ci->email->from($from, $formName);
        $this->ci->email->to($to, $toName);
        $this->ci->email->subject($subject);
        $this->ci->email->message($message);

        if ($cc_email)
            $this->ci->email->cc($cc_email);
        if ($bcc_email)
            $this->ci->email->bcc($bcc_email);
	   //var_dump('Mail',$this->ci->email->send());die;
        if ($this->ci->email->send()) {
            $status = TRUE;
        } else {
	    show_error($this->ci->email->print_debugger());
        }
        return $status;
    }
}
