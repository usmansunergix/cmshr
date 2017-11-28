<?php
class Cmshr_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function get_CMSusers($cms_username = "", $cms_password   = "") {
        if($cms_username == "") {
            $query = $this->db->get('cmshr_users');
            return $query->result_array();
        }
        else {
            $query = $this->db->get_where('cmshr_users', array('user_name' => $cms_username, 'user_password' => $cms_password));
            return $query->row_array() ? $query->row_array() : "Invalid Username or Password";
        }
    }
    
    public function update_emails($email) {
        $email_id = $email['email_id'];
        unset($email['email_id']);
        $this->db->where('email_id', $email_id);
        $this->db->update('email_credentials' ,$email);
        if(mysql_affected_rows() > 0) {
            return true;
        }
    }
}
?>