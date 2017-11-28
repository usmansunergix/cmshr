<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
ini_set('max_execution_time', 30000); 
ini_set('memory_limit','2048M');
class Cmshr_controller extends CI_Controller {
        
    public function __construct() {
        parent::__construct();
        $this->load->model('cmshr_model'); //load parser model
        
        $this->load->library('session');
        $this->load->helper(array('form', 'url','html'));
        $this->load->library('form_validation');
    }
        
    //Home Page
    public function index() {
        if($this->session->userdata("cms_username") == "") {
            redirect('/cmshr_controller/login', 'refresh');
        }

        $data['title'] ="Dashboard";
        $this->load->view('cmshr/header', $data);
        $this->load->view('cmshr/index');
        $this->load->view('cmshr/footer');
    }

    //Login functionality
    public function login($logout = "") {
        //logout
        if($logout == "logout") {
            $this->session->unset_userdata(array('cms_username' => "", 'cms_userrole' => ""));
            redirect('/cmshr_controller/login', 'refresh');
        }

        if($this->input->post('cms_username') && $this->input->post('cms_password')) {
            $data['user_data'] = $this->cmshr_model->get_CMSusers($this->input->post('cms_username'), $this->input->post('cms_password'));
//            print_r($data);
//            exit();
            $this->session->set_userdata("cms_username", $this->input->post('cms_username'));
            $this->session->set_userdata("cms_userrole", $this->input->post('cms_userrole'));
            redirect('/cmshr_controller/', 'refresh');
        }

        $data['title'] ="Login - Email Parser";
        $this->load->view('cmshr/header', $data);
        $this->load->view('cmshr/login');
        $this->load->view('cmshr/footer');
    }

    //Manage users function
    public function manage_users($action = "", $user_id = "") {
        if($this->session->userdata("ep_username") == "") {
            redirect('/parser_controller/login', 'refresh');
        }

        //Fetching and storing data from emails/users.json to $login array
        $users = file_get_contents("emails/users.json");
        $users = json_decode($users, TRUE);

        $data['users'] = $users;
        $data['message']=""; //declaring message variable
        $data['error']=""; //declaring error variable
        $data['user_values'] = array(); //for edit purposes
        $data['user_id']= $user_id; //for edit purposes

        //add user
        if($action == "add") {

            //if new user add form submitted
            if($this->input->post('add_user')) {
                end($users);
                $count = key($users) ? key($users) : 0;
                foreach ($users as $k => $v) {

                    //checking for username duplication
                    if($this->input->post('ep_username') == $v['ep_username']) {
                        $data['error']="User Already Exist";
                        break;
                    }

                    if($this->input->post('ep_userinitials') == $v['ep_userinitials']) {
                        $data['error']="User Initials Already Exist";
                        break;
                    }
                }

                //If same username not found
                if($data['error'] == "") {

                    //storing all post data with escaping last add_user (is a button)
                    $postvalues=array_splice($this->input->post(), 0,-1);
                    $users[$count+1]= $postvalues; //saving post data in $values array

                    $data['message'] ="New User Added";

                    //storing all $values data in uploads/clo_posibilities.json
                    $fp = fopen('emails/users.json', 'w');
                    fwrite($fp, json_encode($users, 50000));
                    fclose($fp);

                    redirect('/parser_controller/manage_users/successful', 'refresh');
                }
            }

            $data['title'] ="Add new User - Email Parser";
            $this->load->view('parser/header', $data);
            $this->load->view('parser/add_user');
            $this->load->view('parser/footer');
        }

        //edit user
        if($action == "edit") {
            $data['user_values']=$users[$user_id]; //Storing user value (of user id) in user_values
            $data['user_id']=$user_id; //storing user id (received) in user_id

            if($this->input->post('edit_user')) {

                //storing all post data with escaping last edit_user (is a button)
                $postvalues=array_splice($this->input->post(), 0,-1);
                $users[$user_id]= $postvalues; //saving post data in $users array at $user_id position

                //storing all $values data in uploads/clo_posibilities.json
                $fp = fopen('emails/users.json', 'w');
                fwrite($fp, json_encode($users, 50000));
                fclose($fp);

                redirect('/parser_controller/manage_users/successful', 'refresh');
            }

            $data['title'] ="Edit User - Email Parser";
            $this->load->view('parser/header', $data);
            $this->load->view('parser/add_user');
            $this->load->view('parser/footer');
        }

        //delete user
        if($action == "delete") {
            unset($users[$user_id]); //removing/deleting user (of user id)

            //storing all $values data in uploads/clo_posibilities.json
            $fp = fopen('emails/users.json', 'w');
            fwrite($fp, json_encode($users, 50000));
            fclose($fp);

            redirect('/parser_controller/manage_users/successful', 'refresh');
        }

        if($action == "successful") {
            $data['message'] = "Successfully Done";
        }

        if($action != "add" && $action != "edit") {
            $data['title'] ="Users - Email Parser";
            $this->load->view('parser/header', $data);
            $this->load->view('parser/users');
            $this->load->view('parser/footer');
        }
    }

    public function manage_email_credentials($action = "", $email_id = "") {
        if($this->session->userdata("ep_username") == "") {
            redirect('/parser_controller/login', 'refresh');
        }

        //Fetching and storing data from emails/users.json to $login array
        $data['emails'] = $this->parser_model->get_emails();

        $data['message']=""; //declaring message variable
        $data['error']=""; //declaring error variable
        $data['user_values'] = array(); //for edit purposes
        $data['email_id']= $email_id; //for edit purposes

        //add user
//        if($action == "add") {
//
//            //if new user add form submitted
//            if($this->input->post('add_user')) {
//                end($users);
//                $count = key($users) ? key($users) : 0;
//                foreach ($users as $k => $v) {
//
//                    //checking for username duplication
//                    if($this->input->post('ep_username') == $v['ep_username']) {
//                        $data['error']="User Already Exist";
//                        break;
//                    }
//
//                    if($this->input->post('ep_userinitials') == $v['ep_userinitials']) {
//                        $data['error']="User Initials Already Exist";
//                        break;
//                    }
//                }
//
//                //If same username not found
//                if($data['error'] == "") {
//
//                    //storing all post data with escaping last add_user (is a button)
//                    $postvalues=array_splice($this->input->post(), 0,-1);
//                    $users[$count+1]= $postvalues; //saving post data in $values array
//
//                    $data['message'] ="New User Added";
//
//                    //storing all $values data in uploads/clo_posibilities.json
//                    $fp = fopen('emails/users.json', 'w');
//                    fwrite($fp, json_encode($users, 50000));
//                    fclose($fp);
//
//                    redirect('/parser_controller/manage_users/successful', 'refresh');
//                }
//            }
//
//            $data['title'] ="Add new User - Email Parser";
//            $this->load->view('parser/header', $data);
//            $this->load->view('parser/add_user');
//            $this->load->view('parser/footer');
//        }

        //edit user
        if($action == "edit") {
            
            //Fetching and storing data from emails/users.json to $login array
            $data['email_data'] = $this->parser_model->get_emails($email_id);
            
            if($this->input->post('edit_email')) {
                
                $data['update_email'] = $this->parser_model->update_emails(array_splice($this->input->post(), 0,-1));
                if($data['update_email']) {
                    redirect('/parser_controller/manage_email_credentials/successful', 'refresh');
                }
                else {
                    $data['error'] = "Please check data and try again";
                }
            }

            $data['title'] ="Edit User - Email Parser";
            $this->load->view('parser/header', $data);
            $this->load->view('parser/add_email_credentials');
            $this->load->view('parser/footer');
        }

        //delete user
//        if($action == "delete") {
//            unset($users[$user_id]); //removing/deleting user (of user id)
//
//            //storing all $values data in uploads/clo_posibilities.json
//            $fp = fopen('emails/users.json', 'w');
//            fwrite($fp, json_encode($users, 50000));
//            fclose($fp);
//
//            redirect('/parser_controller/manage_users/successful', 'refresh');
//        }

        if($action == "successful") {
            $data['message'] = "Successfully Done";
        }

        if($action != "add" && $action != "edit") {
            $data['title'] ="Email Credentials - Email Parser";
            $this->load->view('parser/header', $data);
            $this->load->view('parser/email_credentials');
            $this->load->view('parser/footer');
        }
    }

    //dellete all emails from parsed and discard email folders
    public function delete_all_emails() {
        $folders = array("Discard_Emails", "Parse_Emails"); //folders to delete files from
        foreach ($folders as $folder) {
            $files = glob('emails/'.$folder.'/*'); //get all file names from $folder
            foreach($files as $file) { //loop files
                if(is_file($file)) { //if file
                    unlink($file); //delete file
                }
            }
        }
    }

    //make progress bar
    public function progressbar() {
        $parse_emails = count(glob('emails/Parse_Emails/*.*'));
        $discard_emails = count(glob('emails/Discard_Emails/*.*'));
        echo $parse_emails." / ".$discard_emails;
    }

    public function get_email_count() {
        if($this->input->post('start_time') && $this->input->post('end_time')) {
            $from_datetime = strtotime($this->input->post('start_time'));
            $from_date = date('d F Y G:i:s', $from_datetime); //convert date required to fetch emails accodingly

            $to_datetime = strtotime($this->input->post('end_time'));
            $to_date = date('d-M-Y G:i:s', $to_datetime); //convert $to_date to standard format

            //imap credentials
            $inbox = imap_open("{imap.gmail.com:993/imap/ssl/novalidate-cert}","sci.temp@creditmarketintelligence.com","CMI20132013");

            /* grab emails */
            $emails = imap_search($inbox,'SINCE "'.$from_date.'"');

            $counter = 0; //email counter

            foreach($emails as $email_number) {

                //get information specific to this email like subject, date, sender etc
                $overview = imap_fetch_overview($inbox,$email_number,0);

                //converting Email date to standard date/time
                $email_date = new DateTime($overview[0]->date);

                //converting post to_date to standard date/time
                $too_date = new DateTime($to_date);

                //converting post from_date to standard date/time
                $fromm_date = new DateTime($from_date);

                //if email date is less then from date, go to next email
                if($email_date < $fromm_date) {
                    continue;
                }

                //if email date/time get greater then to_date, break the foreach loop
                if($email_date > $too_date) {
                    break;
                }

                if(!empty($overview)) {
                    $counter++;
                }
            }
        }
        echo $counter;
    }

    //Parsing Function (between two date/time range)
    public function parsing() {
        if($this->input->post('start_time') && $this->input->post('end_time')) {
            $from_datetime = strtotime($this->input->post('start_time'));
            $from_date = date('d F Y G:i:s', $from_datetime); //convert date required to fetch emails accodingly

            $to_datetime = strtotime($this->input->post('end_time'));
            $to_date = date('d-M-Y G:i:s', $to_datetime); //convert $to_date to standard format

            //valid email keywords list
            $email_keywords = array(
                "valid_pricetype" => array( //Valid Price Types
                "Talk",
                "PxT", 
                "Px Tlk", 
                "Px.Talk", 
                "Thoughts", 
                "Thts", 
                "px thts", 
                "px thots", 
                "thots", 
                "price thots", 
                "Takl",
                "Cover", 
                "Covers", 
                "CVR",
                "Cvr", 
                "Cvrs", 
                "Color", 
                "colors", 
                "results", 
                "result", 
                "colour", 
                "colours", 
                "Covered",
                "Trade", 
                "Trades",
                "Traded", 
                "TRD",
                "indications", 
                "indication"
                ),
                "valid_dealtype" => array( //Valid Deal Type
                "CMBS ",     
                "RMBS ", 
                "CLO ", 
                "CDO ", 
                "Trups ", 
                "Trup ", 
                "ABS "
                ),
                "valid_bwic" => array( //Valid BWIC Types
                "BWIC",     
                "BWICs"
                ),
                "valid_cover" => array( //Valid Cover Types
                "Cover",
                "Covers", 
                "CVR", 
                "Cvr", 
                "Cvrs", 
                "Color", 
                "colors", 
                "results", 
                "result", 
                "colour", 
                "colours", 
                "Covered", 
                "Trade", 
                "Trades", 
                "Traded", 
                "TRD"
                )
            );

            //Discard Subject Keywords
            $discard_subject_keywords = array(
                "Axes",
                "Consumer", 
                "Auto/Card", 
                "Student", 
                "Bid/Offer", 
                "Utility", 
                "Equipment", 
                "Auto/Equipment", 
                "Insurance", 
                "Offer", 
                "Offers", 
                "Offering"
            );

            //Discard Message Body Keywords
            $discard_message_keywords = array(
                "axes",     
                "offer", 
                "offering", 
                "offers", 
                "consumer", 
                "auto/card", 
                "student",
                "bid/offer",
                "utility",
                "equipment",
                "Auto/Equipment",
                "insurance"
            );

            //imap credentials
            $inbox = imap_open("{imap.gmail.com:993/imap/ssl/novalidate-cert}","sci.temp@creditmarketintelligence.com","CMI20132013");

            //if found error in connection
            if(imap_last_error()){
                $e = 'ec101:[ '.$inbox['account_title'].' ][ Error ] : ' . imap_last_error();
                $data['errors'] = $e;
                exit();
            }

            /* grab emails */
            $emails = imap_search($inbox,'SINCE "'.$from_date.'"');
//                $emails = imap_search($inbox,'UID SEARCH UID 767525:*');
            //BEFORE "'.$to_date.'"

            $counter = 0; //email counter to make email array for print and to remove duplication
            $emails_array = array(); //will contain all read emails information

            foreach($emails as $email_number) {
                //get information specific to this email like subject, date, sender etc
                $overview = imap_fetch_overview($inbox,$email_number,0);

                //Get email structure like Email encoding, subtype etc
                $structure = imap_fetchstructure ( $inbox , $email_number);

                //converting Email date to standard date/time
                $email_date = new DateTime($overview[0]->date);

                //converting post to_date to standard date/time
                $too_date = new DateTime($to_date);

                //converting post from_date to standard date/time
                $fromm_date = new DateTime($from_date);

                //if email date is less then from date, go to next email
                if($email_date < $fromm_date) {
                    continue;
                }

                //if email date/time get greater then to_date, break the foreach loop
                if($email_date > $too_date) {
                    break;
                }

                //checking if email exist
                if(!empty($overview)) {
                    $message = ""; //initializing $message variable as empty

                    //checking email if in MULTIPART/ALTERNATIVE format
                    $message = imap_fetchbody($inbox,$email_number,1);

                    //converting suject to proper UTF-8 format (due to 2½ cases)
                    $overview[0]->subject = iconv_mime_decode($overview[0]->subject,0,"UTF-8");

                    //checking email subtype
                    //1. if subtype is HTML
                    if($structure->subtype == "HTML") {
                        $message2 = imap_fetchbody($inbox,$email_number,1);
                        $message = quoted_printable_decode(strip_tags($message2));
                    }
                    //2. if subtype is MIXED
                    if($structure->subtype == "MIXED") {
                        $found = 0; //for stoping (unknown) loop checking again and again
                        if (isset($structure->parts)) { //if found parts
                            $parts = $structure->parts;

                            //2.1: if subtype is PLAIN
                            if ($parts[0]->subtype == 'PLAIN') {
                                $message2 = imap_fetchbody($inbox, $email_number, 1); //read email in MULTIPART/ALTERNATIVE format
                                $message = quoted_printable_decode(base64_decode($message2)); //decode email from base64 and make it normal printable (encoding = 3)

                                if ($parts[0]->encoding == 4) { //if encoding = 4, not required to decode it
                                    $message = quoted_printable_decode($message2);
                                }

                                $found = 1; //email read successfully (stop unkonwn loop)
                            }

                            //2.2: if subtype is ALTERNATIVE and $found != 1
                            if ($parts[0]->subtype == 'ALTERNATIVE' && $found != 1) {
                                $message2 = imap_fetchbody($inbox, $email_number, 1.1); //read email in TEXT/PLAIN format

                                if ($message2 == null) { //if found empty
                                    $message2 = imap_fetchbody($inbox, $email_number, 1); //read email in MULTIPART/ALTERNATIVE format
                                }

                                $message = quoted_printable_decode($message2); //convert to printable form

                                if(isset($parts[0]->parts)) { //email has inner parts
                                    $inner_parts = $parts[0]->parts; //get inner parts

                                    if ($inner_parts[0]->subtype == 'PLAIN') { //if inner parts subtype is PLAIN
                                        $message2 = imap_fetchbody($inbox, $email_number, 1.1); //read email in TEXT/PLAIN format
                                        $message = base64_decode($message2); //decode email (encoding = 3)

                                        if ($inner_parts[0]->encoding == 4) { //if inner parts encoding = 4
                                            $message2 = imap_fetchbody($inbox, $email_number, 1); //read email in MULTIPART/ALTERNATIVE format
                                            $message = quoted_printable_decode($message2); //convert it to printable form
                                        }
                                    }
                                }
                                $found = 1; //email read successfully (stop unkonwn loop)
                            }

                            //2.3: if subtype is RELATED and $found != 1
                            if ($parts[0]->subtype == 'RELATED' && $found != 1) {
                                if (isset($parts[0]->parts)) { //if found inner parts (it should found inner parts these type of emails contains images, attachments etc)
                                    $related_inner_parts = $parts[0]->parts; //get the inner parts

                                    //1.3.1: if inner parts subtype is ALTERNATIVE
                                    if ($related_inner_parts->subtype == 'ALTERNATIVE') {
                                        $message2 = imap_fetchbody($inbox, $email_number, 1.1); //read email in TEXT/PLAIN format

                                        if ($message2 == null) { //if found empty
                                            $message2 = imap_fetchbody($inbox, $email_number, 1); //read email in MULTIPART/ALTERNATIVE format
                                        }
                                        $message = base64_decode($message2); //decode email
                                    }
                                }
                                $found = 1; //email read successfully (stop unkonwn loop)
                            }
                        }
                    }

                    //3. if subtype is ALTERNATIVE
                    if ($structure->subtype == 'ALTERNATIVE') {

                        if (isset($structure->parts)) { //if found parts
                            $parts = $structure->parts; //get these parts
                            $message2 = imap_fetchbody($inbox, $email_number, 1.1); //read email in TEXT/PLAIN format
                            if ($message2 == null) { //if found empty
                                $message2 = imap_fetchbody($inbox, $email_number, 1); //read email in MULTIPART/ALTERNATIVE format
                            }
                            $message = quoted_printable_decode($message2); //convert to printable form

                            if($parts[0]->encoding == 3) { //if parts encoing = 3
                                $message = quoted_printable_decode(base64_decode($message2)); //decode it an make it in printable form
                            }
                        }
                    }

                    //4. if subtype is PLAIN
                    if ($structure->subtype == 'PLAIN') {
                        $message2 = imap_fetchbody($inbox, $email_number, 1); //read email in MULTIPART/ALTERNATIVE format
                        $message = quoted_printable_decode(base64_decode($message2));  //decode it and convert to printable form

                        if ($structure->encoding == 4) { //if encoding = 4
                            $message = quoted_printable_decode($message2); //just convert it to printable form
                        }
                    }

                    //5. if subtype is RELATED
                    if ($structure->subtype == 'RELATED') {
                        if (isset($structure->parts)) { //if email has parts (it should have)
                            $parts = $structure->parts; //get these parts

                            //5.1: if parts subtype is ALTERNATIVE
                            if ($parts[0]->subtype == 'ALTERNATIVE') {
                                $message2 = imap_fetchbody($inbox, $email_number, 1.1); //read email in TEXT/PLAIN format

                                if ($message2 == null) { //if email got empty
                                    $message2 = imap_fetchbody($inbox, $email_number, 1); //read email in MULTIPART/ALTERNATIVE format
                                }

                                $message = base64_decode($message2); //decode it

                                if(isset($parts[0]->parts)) {
                                    $sub_parts = $parts[0]->parts;
                                    if($sub_parts[0]->subtype == "PLAIN") {
                                        //for encoding = 4
                                        $message2 = imap_fetchbody($inbox, $email_number, 1.1);
                                        $message = $message2;

                                        if($sub_parts[0]->encoding == 3) {
                                            $message = base64_decode($message2); //decode it
                                        }
                                    }
                                }

                            }
                        }
                    }


                    /* Filtering Logic */

                    //1. Remove Duplicate Emails
                    $duplicate = FALSE; //initially false
                    $pass_fail = "Fail"; //initially false
                    $pass_rule = "";
                    $filter_data = "";
                    foreach ($emails_array as $erkey => $ervalue) {
                        $diff = date_diff(new DateTime($ervalue->date),$email_date); //find difference in email date and email array date
                        $minutes = $diff->days * 24 * 60; //for perfect daylight saving issue
                        $minutes += $diff->h * 60;
                        $minutes += $diff->i;
                        if(strtolower($overview[0]->subject) == strtolower($ervalue->subject) && $minutes <= 3) {
                            $pass_rule = "Email Duplication Filter";
                            $filter_data = "Duplicate Email Found";
                            $duplicate = TRUE;
                            break;
                        }
                    }

                    //if no duplicate email found
                    if($duplicate == FALSE) {

                        //valid subject keyword(if found) will save here
                        $valid_emails_subject = array(
                            "valid_pricetype" => "", 
                            "valid_dealtype" => "", 
                            "valid_bwic" => "", 
                            "valid_cover" => ""
                        );

                        //valid body keyword(if found) will save here
                        $valid_emails_body = array(
                            "valid_pricetype" => "", 
                            "valid_dealtype" => "", 
                            "valid_bwic" => "", 
                            "valid_cover" => ""
                        );

                        //looping valid email list keywords
                        foreach ($email_keywords as $ekkey => $ekvalues) {
                            $found_keyword_in_subject = FALSE; //initialize as false
                            $found_keyword_in_body = FALSE; //initialize as false
                            foreach ($ekvalues as $ekvalue) { //looping values found in $ekvalues

                                //if keyword found in subject
                                if (strpos(strtolower($overview[0]->subject),strtolower($ekvalue)) !== FALSE && $found_keyword_in_subject != TRUE) {
                                    $valid_emails_subject[$ekkey] = $ekvalue; //put that value in $valid_emails_subject array
                                    $found_keyword_in_subject = TRUE; //keyword found
                                }

                                //if keyword found in message
                                if (strpos(strtolower($message),strtolower($ekvalue)) !== FALSE && $found_keyword_in_body != TRUE) {
                                    $valid_emails_body[$ekkey] = $ekvalue; //put that value in $valid_emails_body array
                                    $found_keyword_in_body = TRUE; //keyword found
                                }

                                //if keyword found in both subject and body then no need to loop further, go to next $email_keywords iteration
                                if($found_keyword_in_subject == TRUE && $found_keyword_in_body == TRUE) {
                                    break;
                                }
                            }
                        }

                        // 2. Deal-Type and Price-Type Filtering
                        //    *If Subject-Line or Message-Body contains an indication of valid Price-Type
                        //    And 
                        //    *If Subject-Line or Message-Body contains an indication of valid Deal-Type
                        if(($valid_emails_subject['valid_pricetype'] != "" || $valid_emails_body['valid_pricetype'] != "") && ($valid_emails_subject['valid_dealtype'] != "" || $valid_emails_body['valid_dealtype'] != "")) {
                            $pass_fail = "Pass";
                            $pass_rule = "Deal-Type and Price-Type Filtering";
                            $filter_data = "Matched data for price type filtering * ".($valid_emails_subject['valid_pricetype'] ? $valid_emails_subject['valid_pricetype'] : $valid_emails_body['valid_pricetype'])." * Matched data for deal type filtering  *  ".($valid_emails_subject['valid_dealtype'] ? $valid_emails_subject['valid_dealtype'] : $valid_emails_body['valid_dealtype'])." *";
                        }

                        // 3. Cover Filtering
                        //    *Subject-Line includes Cover or derivative of Cover
                        //    And
                        //    *Message-Body includes Cover or derivative of Cover
                        else if($valid_emails_subject['valid_cover'] != "" && $valid_emails_body['valid_cover'] != "") {
                            $pass_fail = "Pass";
                            $pass_rule = "Cover Filtering";
                            $filter_data = "Matched data for Cover Filtering  * ".$valid_emails_subject['valid_cover']." * and Matched data for Cover Body filtering  * ".$valid_emails_body['valid_cover']." *";
                        }

                        // 4. TALK Filtering
                        //    *Subject-Line includes TALK or derivative of Talk
                        //    And
                        //    *Message-Body includes Talk or derivative of Talk
                        else if($valid_emails_subject['valid_pricetype'] != "" && $valid_emails_body['valid_pricetype'] != "") {
                            $pass_fail = "Pass";
                            $pass_rule = "Talk Filtering";
                            $filter_data = "Matched data for TALK Subject Filtering * ".$valid_emails_subject['valid_pricetype']." * and Matched data for Talk Body filtering  * ".$valid_emails_body['valid_pricetype']." *";
                        }

                        // 5. BWIC and Price-Type Filtering
                        //    *If Subject-Line or Message-Body contains an indication of valid Price-Type
                        //    And
                        //    *If Subject-Line or Message-Body contains “BWIC”, “BWICs”
                        else if(($valid_emails_subject['valid_pricetype'] != "" || $valid_emails_body['valid_pricetype'] != "") && ($valid_emails_subject['valid_bwic'] != "" || $valid_emails_body['valid_bwic'] != "")) {
                            $pass_fail = "Pass";
                            $pass_rule = "BWIC and Price-Type Filtering";
                            $filter_data = "Matched data for BWIC and Price-Type Filtering * ".($valid_emails_subject['valid_pricetype'] ? $valid_emails_subject['valid_pricetype'] : $valid_emails_body['valid_pricetype'])." * BWIC or BWICs match *  ".($valid_emails_subject['valid_bwic'] ? $valid_emails_subject['valid_bwic'] : $valid_emails_body['valid_bwic'])." *";
                        }

                        // 6. Price-Type Only Filtering
                        //    *If Subject-Line or Message-Body contains an indication of valid Price-Type
                        else if($valid_emails_subject['valid_pricetype'] != "" || $valid_emails_body['valid_pricetype'] != "") {
                            $pass_fail = "Pass";
                            $pass_rule = "Price-Type Only Filtering";
                            $filter_data = "Matched data for Price-Type Only Filtering  * ".($valid_emails_subject['valid_pricetype'] ? $valid_emails_subject['valid_pricetype'] : $valid_emails_body['valid_pricetype'])." *";
                        }

                        //7. Discard Emails
                        if($pass_fail == "Fail") {
                            $invalid_email_keyword = ""; //initialize

                            //7.1: checking Subject for invalid keyword
                            foreach ($discard_subject_keywords as $dskvalue) { //looping values found in $dskvalue
                                //if keyword found in subject
                                if (strpos(strtolower($overview[0]->subject),strtolower($dskvalue)) !== FALSE) {
                                    $invalid_email_keyword = $dskvalue; //put that value in $invalid_email_keyword array
                                    $pass_rule = "Subject-Line Filtering";
                                    $filter_data = "Invalid Keyword * ".$invalid_email_keyword." * found in Subject";
                                    break;
                                }
                            }

                            //7.2: If not found in Subject, checking Message Body for invalid keyword
                            if($invalid_email_keyword == "") {
                                foreach ($discard_message_keywords as $dmkvalue) { //looping values found in $dskvalue
                                    //if keyword found in subject
                                    if (strpos(strtolower($message),strtolower($dmkvalue)) !== FALSE) {
                                        $invalid_email_keyword = $dmkvalue; //put that value in $invalid_email_keyword array
                                        $pass_rule = "Message-Body Filtering";
                                        $filter_data = "Invalid Keyword * ".$invalid_email_keyword." * found in Message Body";
                                        break;
                                    }
                                }
                            }


                            //7.3: No Rule Fired
                            if($invalid_email_keyword == "") {
                                $pass_fail = "No Rule Fired";
                            }
                        }
                    }

                    /* End of Filtering Logic */

                    $emails_array[$counter] = $overview[0]; //adding email overview information in array
                    $emails_array[$counter]->email_message = strip_tags($message); //adding Email body to array
                    $emails_array[$counter]->pass_fail = $pass_fail;
                    $emails_array[$counter]->pass_rule = $pass_rule;
                    $emails_array[$counter]->filter_data = $filter_data;
                }

                if($pass_fail == "Pass") {
                    $folder = "Parse_Emails";
                }
                else {
                    $folder = "Discard_Emails";
                }

                $complete_email = "ID : ".$counter.PHP_EOL.PHP_EOL."Sender Name : ".(str_replace('"','',$overview[0]->from)).PHP_EOL.PHP_EOL."Subject-Line : ".($overview[0]->subject).PHP_EOL.PHP_EOL."Date-Received : ".(date("d-M-y h-i:s A", strtotime($overview[0]->date))).PHP_EOL.PHP_EOL.PHP_EOL."*****************************************Email Body*****************************************".PHP_EOL.PHP_EOL.$message;

                //creating "($counter) email date time.txt" file and puting email in it
                $fp = fopen('emails/'.$folder.'/('.$counter.') '.date("d-M-y h-i A", strtotime($overview[0]->date)).'.txt', 'w+');
                fwrite($fp, $complete_email);
                fclose($fp);

                $counter++;
            }

            imap_close($inbox);
        }

        $data['emails_array'] = $emails_array; //passing email data to print
//            $this->session->set_userdata("emails_array", $emails_array);

        //creating "emails_array.txt" file and puting $emails_array in it to use it in export_report function
        $fp = fopen('emails/emails_array.txt', 'w');
        fwrite($fp, serialize($emails_array));
        fclose($fp);

//            print_r($this->session->userdata("emails_array"));
//            echo "<script type='text/javascript'>jQuery('#progressbar').progressbar('value', 100);alert('Process Completed Successfully!!');</script>";
        $data['title'] ="Email Parser";
        $this->load->view('parser/header', $data);
        $this->load->view('parser/parsing');
        $this->load->view('parser/footer');	
    }

    //Parsing Function (to get latest emails)
    public function parsing_realtime() {

        $current_date_time = date("d-M-y h-i A"); //current date time
        $filename = "email_parsed_report_".date('d-m-Y h-i-A').".xlsx"; //excel report filename

        /* Dropbox connect */

        $appInfo = Dropbox\AppInfo::loadFromJsonFile("emails/dropbox_auth.json");
        $webAuth = new Dropbox\WebAuthNoRedirect($appInfo, "PHP-Example/1.0");

        $dbxClient = new Dropbox\Client("CT3SlRGcrtAAAAAAAAAACDgjAMqow_igOzisa5ukp7eXVXYJsOQJWXgtmtT4NCZs", "PHP-Example/1.0");
        $accountInfo = $dbxClient->getAccountInfo();

        /* End of Dropbox connect */

        //valid email keywords list
        $email_keywords = array(
            "valid_pricetype" => array( //Valid Price Types
            "Talk",
            "PxT", 
            "Px Tlk", 
            "Px.Talk", 
            "Thoughts", 
            "Thts", 
            "px thts", 
            "px thots", 
            "thots", 
            "price thots", 
            "Takl",
            "Cover", 
            "Covers", 
            "CVR",
            "Cvr", 
            "Cvrs", 
            "Color", 
            "colors", 
            "results", 
            "result", 
            "colour", 
            "colours", 
            "Covered",
            "Trade", 
            "Trades",
            "Traded", 
            "TRD",
            "indications", 
            "indication"
            ),
            "valid_dealtype" => array( //Valid Deal Type
            "CMBS ",     
            "RMBS ", 
            "CLO ", 
            "CDO ", 
            "Trups ", 
            "Trup ", 
            "ABS "
            ),
            "valid_bwic" => array( //Valid BWIC Types
            "BWIC",     
            "BWICs"
            ),
            "valid_cover" => array( //Valid Cover Types
            "Cover",
            "Covers", 
            "CVR", 
            "Cvr", 
            "Cvrs", 
            "Color", 
            "colors", 
            "results", 
            "result", 
            "colour", 
            "colours", 
            "Covered", 
            "Trade", 
            "Trades", 
            "Traded", 
            "TRD"
            )
        );

        //Discard Subject Keywords
        $discard_subject_keywords = array(
            "Axes",
            "Consumer", 
            "Auto/Card", 
            "Student", 
            "Bid/Offer", 
            "Utility", 
            "Equipment", 
            "Auto/Equipment", 
            "Insurance", 
            "Offer", 
            "Offers", 
            "Offering"
        );

        //Discard Message Body Keywords
        $discard_message_keywords = array(
            "axes",     
            "offer", 
            "offering", 
            "offers", 
            "consumer", 
            "auto/card", 
            "student",
            "bid/offer",
            "utility",
            "equipment",
            "Auto/Equipment",
            "insurance"
        );

        //imap credentials
        $inbox = imap_open("{imap.gmail.com:993/imap/ssl/novalidate-cert}","sci.temp@creditmarketintelligence.com","CMI20132013");

        //if found error in connection
        if(imap_last_error()){
            $e = 'ec101:[ '.$inbox['account_title'].' ][ Error ] : ' . imap_last_error();
            $data['errors'] = $e;
            exit();
        }

        /* grab emails */
        $emails = imap_search($inbox,'ALL');

        $latest_email = file_get_contents('emails/latest_email.txt');

        $counter = 0; //email counter to make email array for print and to remove duplication
        $emails_array = array(); //will contain all read emails information
        rsort($emails);

        $latest_email_overview = imap_fetch_overview($inbox,$emails[0],0);
        $latest_email_uid = $latest_email_overview[0]->uid;
        //creating "emails_array.txt" file and puting $emails_array in it to use it in export_report function
        $fp = fopen('emails/latest_email.txt', 'w');
        fwrite($fp, $latest_email_uid);
        fclose($fp);

        foreach($emails as $email_number) {
            //get information specific to this email like subject, date, sender etc
            $overview = imap_fetch_overview($inbox,$email_number,0);
//                echo $overview[0]->uid."<br />";
//                if($counter == 5) {break;}
            if($overview[0]->uid == $latest_email) {
                break;
            }
            //Get email structure like Email encoding, subtype etc
            $structure = imap_fetchstructure ( $inbox , $email_number);

            //converting Email date to standard date/time
            $email_date = new DateTime($overview[0]->date);

//                $current_date = new DateTime(date());

            //checking if email exist
            if(!empty($overview)) {
                $message = ""; //initializing $message variable as empty

                //checking email if in MULTIPART/ALTERNATIVE format
                $message = imap_fetchbody($inbox,$email_number,1);

                //converting suject to proper UTF-8 format (due to 2½ cases)
                $overview[0]->subject = iconv_mime_decode($overview[0]->subject,0,"UTF-8");

                //checking email subtype
                //1. if subtype is MIXED
                if($structure->subtype == "MIXED") {
                    $found = 0; //for stoping (unknown) loop checking again and again
                    if (isset($structure->parts)) { //if found parts
                        $parts = $structure->parts;

                        //1.1: if subtype is PLAIN
                        if ($parts[0]->subtype == 'PLAIN') {
                            $message2 = imap_fetchbody($inbox, $email_number, 1); //read email in MULTIPART/ALTERNATIVE format
                            $message = quoted_printable_decode(base64_decode($message2)); //decode email from base64 and make it normal printable (encoding = 3)

                            if ($parts[0]->encoding == 4) { //if encoding = 4, not required to decode it
                                $message = quoted_printable_decode($message2);
                            }

                            $found = 1; //email read successfully (stop unkonwn loop)
                        }

                        //1.2: if subtype is ALTERNATIVE and $found != 1
                        if ($parts[0]->subtype == 'ALTERNATIVE' && $found != 1) {
                            $message2 = imap_fetchbody($inbox, $email_number, 1.1); //read email in TEXT/PLAIN format

                            if ($message2 == null) { //if found empty
                                $message2 = imap_fetchbody($inbox, $email_number, 1); //read email in MULTIPART/ALTERNATIVE format
                            }

                            $message = quoted_printable_decode($message2); //convert to printable form

                            if(isset($parts[0]->parts)) { //email has inner parts
                                $inner_parts = $parts[0]->parts; //get inner parts

                                if ($inner_parts[0]->subtype == 'PLAIN') { //if inner parts subtype is PLAIN
                                    $message2 = imap_fetchbody($inbox, $email_number, 1.1); //read email in TEXT/PLAIN format
                                    $message = base64_decode($message2); //decode email (encoding = 3)

                                    if ($inner_parts[0]->encoding == 4) { //if inner parts encoding = 4
                                        $message2 = imap_fetchbody($inbox, $email_number, 1); //read email in MULTIPART/ALTERNATIVE format
                                        $message = quoted_printable_decode($message2); //convert it to printable form
                                    }
                                }
                            }
                            $found = 1; //email read successfully (stop unkonwn loop)
                        }

                        //1.3: if subtype is RELATED and $found != 1
                        if ($parts[0]->subtype == 'RELATED' && $found != 1) {
                            if (isset($parts[0]->parts)) { //if found inner parts (it should found inner parts these type of emails contains images, attachments etc)
                                $related_inner_parts = $parts[0]->parts; //get the inner parts

                                //1.3.1: if inner parts subtype is ALTERNATIVE
                                if ($related_inner_parts->subtype == 'ALTERNATIVE') {
                                    $message2 = imap_fetchbody($inbox, $email_number, 1.1); //read email in TEXT/PLAIN format

                                    if ($message2 == null) { //if found empty
                                        $message2 = imap_fetchbody($inbox, $email_number, 1); //read email in MULTIPART/ALTERNATIVE format
                                    }
                                    $message = base64_decode($message2); //decode email
                                }
                            }
                            $found = 1; //email read successfully (stop unkonwn loop)
                        }
                    }
                }

                //2. if subtype is ALTERNATIVE
                if ($structure->subtype == 'ALTERNATIVE') {

                    if (isset($structure->parts)) { //if found parts
                        $parts = $structure->parts; //get these parts
                        $message2 = imap_fetchbody($inbox, $email_number, 1.1); //read email in TEXT/PLAIN format
                        if ($message2 == null) { //if found empty
                            $message2 = imap_fetchbody($inbox, $email_number, 1); //read email in MULTIPART/ALTERNATIVE format
                        }
                        $message = quoted_printable_decode($message2); //convert to printable form

                        if($parts[0]->encoding == 3) { //if parts encoing = 3
                            $message = quoted_printable_decode(base64_decode($message2)); //decode it an make it in printable form
                        }
                    }
                }

                //3. if subtype is PLAIN
                if ($structure->subtype == 'PLAIN') {
                    $message2 = imap_fetchbody($inbox, $email_number, 1); //read email in MULTIPART/ALTERNATIVE format
                    $message = quoted_printable_decode(base64_decode($message2));  //decode it and convert to printable form

                    if ($structure->encoding == 4) { //if encoding = 4
                        $message = quoted_printable_decode($message2); //just convert it to printable form
                    }
                }

                //4. if subtype is RELATED
                if ($structure->subtype == 'RELATED') {
                    if (isset($structure->parts)) { //if email has parts (it should have)
                        $parts = $structure->parts; //get these parts

                        //4.1: if parts subtype is ALTERNATIVE
                        if ($parts[0]->subtype == 'ALTERNATIVE') {
                            $message2 = imap_fetchbody($inbox, $email_number, 1.1); //read email in TEXT/PLAIN format

                            if ($message2 == null) { //if email got empty
                                $message2 = imap_fetchbody($inbox, $email_number, 1); //read email in MULTIPART/ALTERNATIVE format
                            }

                            $message = base64_decode($message2); //decode it

                            if(isset($parts[0]->parts)) {
                                $sub_parts = $parts[0]->parts;
                                if($sub_parts[0]->subtype == "PLAIN") {
                                    //for encoding = 4
                                    $message2 = imap_fetchbody($inbox, $email_number, 1.1);
                                    $message = $message2;

                                    if($sub_parts[0]->encoding == 3) {
                                        $message = base64_decode($message2); //decode it
                                    }
                                }
                            }

                        }
                    }
                }

                /* Filtering Logic */

                //1. Remove Duplicate Emails
                $duplicate = FALSE; //initially false
                $pass_fail = "Fail"; //initially false
                $pass_rule = "";
                $filter_data = "";
                foreach ($emails_array as $erkey => $ervalue) {
                    $diff = date_diff(new DateTime($ervalue->date),$email_date); //find difference in email date and email array date
                    $minutes = $diff->days * 24 * 60; //for perfect daylight saving issue
                    $minutes += $diff->h * 60;
                    $minutes += $diff->i;
                    if(strtolower($overview[0]->subject) == strtolower($ervalue->subject) && $minutes <= 3) {
                        $pass_rule = "Email Duplication Filter";
                        $filter_data = "Duplicate Email Found";
                        $duplicate = TRUE;
                        break;
                    }
                }

                //if no duplicate email found
                if($duplicate == FALSE) {

                    //valid subject keyword(if found) will save here
                    $valid_emails_subject = array(
                        "valid_pricetype" => "", 
                        "valid_dealtype" => "", 
                        "valid_bwic" => "", 
                        "valid_cover" => ""
                    );

                    //valid body keyword(if found) will save here
                    $valid_emails_body = array(
                        "valid_pricetype" => "", 
                        "valid_dealtype" => "", 
                        "valid_bwic" => "", 
                        "valid_cover" => ""
                    );

                    //looping valid email list keywords
                    foreach ($email_keywords as $ekkey => $ekvalues) {
                        $found_keyword_in_subject = FALSE; //initialize as false
                        $found_keyword_in_body = FALSE; //initialize as false
                        foreach ($ekvalues as $ekvalue) { //looping values found in $ekvalues

                            //if keyword found in subject
                            if (strpos(strtolower($overview[0]->subject),strtolower($ekvalue)) !== FALSE && $found_keyword_in_subject != TRUE) {
                                $valid_emails_subject[$ekkey] = $ekvalue; //put that value in $valid_emails_subject array
                                $found_keyword_in_subject = TRUE; //keyword found
                            }

                            //if keyword found in message
                            if (strpos(strtolower($message),strtolower($ekvalue)) !== FALSE && $found_keyword_in_body != TRUE) {
                                $valid_emails_body[$ekkey] = $ekvalue; //put that value in $valid_emails_body array
                                $found_keyword_in_body = TRUE; //keyword found
                            }

                            //if keyword found in both subject and body then no need to loop further, go to next $email_keywords iteration
                            if($found_keyword_in_subject == TRUE && $found_keyword_in_body == TRUE) {
                                break;
                            }
                        }
                    }

                    // 2. Deal-Type and Price-Type Filtering
                    //    *If Subject-Line or Message-Body contains an indication of valid Price-Type
                    //    And 
                    //    *If Subject-Line or Message-Body contains an indication of valid Deal-Type
                    if(($valid_emails_subject['valid_pricetype'] != "" || $valid_emails_body['valid_pricetype'] != "") && ($valid_emails_subject['valid_dealtype'] != "" || $valid_emails_body['valid_dealtype'] != "")) {
                        $pass_fail = "Pass";
                        $pass_rule = "Deal-Type and Price-Type Filtering";
                        $filter_data = "Matched data for price type filtering * ".($valid_emails_subject['valid_pricetype'] ? $valid_emails_subject['valid_pricetype'] : $valid_emails_body['valid_pricetype'])." * Matched data for deal type filtering  *  ".($valid_emails_subject['valid_dealtype'] ? $valid_emails_subject['valid_dealtype'] : $valid_emails_body['valid_dealtype'])." *";
                    }

                    // 3. Cover Filtering
                    //    *Subject-Line includes Cover or derivative of Cover
                    //    And
                    //    *Message-Body includes Cover or derivative of Cover
                    else if($valid_emails_subject['valid_cover'] != "" && $valid_emails_body['valid_cover'] != "") {
                        $pass_fail = "Pass";
                        $pass_rule = "Cover Filtering";
                        $filter_data = "Matched data for Cover Filtering  * ".$valid_emails_subject['valid_cover']." * and Matched data for Cover Body filtering  * ".$valid_emails_body['valid_cover']." *";
                    }

                    // 4. TALK Filtering
                    //    *Subject-Line includes TALK or derivative of Talk
                    //    And
                    //    *Message-Body includes Talk or derivative of Talk
                    else if($valid_emails_subject['valid_pricetype'] != "" && $valid_emails_body['valid_pricetype'] != "") {
                        $pass_fail = "Pass";
                        $pass_rule = "Talk Filtering";
                        $filter_data = "Matched data for TALK Subject Filtering * ".$valid_emails_subject['valid_pricetype']." * and Matched data for Talk Body filtering  * ".$valid_emails_body['valid_pricetype']." *";
                    }

                    // 5. BWIC and Price-Type Filtering
                    //    *If Subject-Line or Message-Body contains an indication of valid Price-Type
                    //    And
                    //    *If Subject-Line or Message-Body contains “BWIC”, “BWICs”
                    else if(($valid_emails_subject['valid_pricetype'] != "" || $valid_emails_body['valid_pricetype'] != "") && ($valid_emails_subject['valid_bwic'] != "" || $valid_emails_body['valid_bwic'] != "")) {
                        $pass_fail = "Pass";
                        $pass_rule = "BWIC and Price-Type Filtering";
                        $filter_data = "Matched data for BWIC and Price-Type Filtering * ".($valid_emails_subject['valid_pricetype'] ? $valid_emails_subject['valid_pricetype'] : $valid_emails_body['valid_pricetype'])." * BWIC or BWICs match *  ".($valid_emails_subject['valid_bwic'] ? $valid_emails_subject['valid_bwic'] : $valid_emails_body['valid_bwic'])." *";
                    }

                    // 6. Price-Type Only Filtering
                    //    *If Subject-Line or Message-Body contains an indication of valid Price-Type
                    else if($valid_emails_subject['valid_pricetype'] != "" || $valid_emails_body['valid_pricetype'] != "") {
                        $pass_fail = "Pass";
                        $pass_rule = "Price-Type Only Filtering";
                        $filter_data = "Matched data for Price-Type Only Filtering  * ".($valid_emails_subject['valid_pricetype'] ? $valid_emails_subject['valid_pricetype'] : $valid_emails_body['valid_pricetype'])." *";
                    }

                    //7. Discard Emails
                    if($pass_fail == "Fail") {
                        $invalid_email_keyword = ""; //initialize
                        //7.1: checking Subject for invalid keyword
                        foreach ($discard_subject_keywords as $dskvalue) { //looping values found in $dskvalue
                            //if keyword found in subject
                            if (strpos(strtolower($overview[0]->subject),strtolower($dskvalue)) !== FALSE) {
                                $invalid_email_keyword = $dskvalue; //put that value in $invalid_email_keyword array
                                $pass_rule = "Subject-Line Filtering";
                                $filter_data = "Invalid Keyword * ".$invalid_email_keyword." * found in Subject";
                                break;
                            }
                        }
                        //7.2: If not found in Subject, checking Message Body for invalid keyword
                        if($invalid_email_keyword == "") {
                            foreach ($discard_message_keywords as $dmkvalue) { //looping values found in $dskvalue
                                //if keyword found in subject
                                if (strpos(strtolower($message),strtolower($dmkvalue)) !== FALSE) {
                                    $invalid_email_keyword = $dmkvalue; //put that value in $invalid_email_keyword array
                                    $pass_rule = "Message-Body Filtering";
                                    $filter_data = "Invalid Keyword * ".$invalid_email_keyword." * found in Message Body";
                                    break;
                                }
                            }
                        }


                        //7.3: No Rule Fired
                        if($invalid_email_keyword == "") {
                            $pass_fail = "No Rule Fired";
                        }
                    }
                }
                /* End of Filtering Logic */

                $emails_array[$counter] = $overview[0]; //adding email overview information in array
                $emails_array[$counter]->email_message = strip_tags($message); //adding Email body to array
                $emails_array[$counter]->pass_fail = $pass_fail;
                $emails_array[$counter]->pass_rule = $pass_rule;
                $emails_array[$counter]->filter_data = $filter_data;
            }

            if($pass_fail == "Pass") {
                $folder = "Parse_Emails";
            }
            else {
                $folder = "Discard_Emails";
            }

            //creating "($counter) email date time.txt" file and puting email in it
            $fp = fopen('emails/'.$folder.'/('.$counter.') '.date("d-M-y h-i A", strtotime($overview[0]->date)).'.txt', 'w');
            fwrite($fp, $message);
            fclose($fp);

            $f = fopen('emails/'.$folder.'/('.$counter.') '.date("d-M-y h-i A", strtotime($overview[0]->date)).'.txt', "rb");
            $dbxClient->uploadFile('/emails/'.$current_date_time.'/'.$folder.'/('.$counter.') '.date("d-M-y h-i A", strtotime($overview[0]->date)).'.txt', Dropbox\WriteMode::add(), $f);
            fclose($f);

            $counter++;
        }

        imap_close($inbox);

//            $data['emails_array'] = $emails_array; //passing email data to print

        //Fetching and storing data from emails/emails_db.json to $emails_db array
        //for maintaining emails db
        $emails_db = array();
        $emails_db = file_get_contents("emails/emails_db.txt");
        $emails_db = unserialize($emails_db);

        if(!empty($emails_db)) { //if not empty
            end($emails_db); //go to last key
            $last_record_key = key($emails_db); //get key
        }
        else { //else 0
            $last_record_key = 0;
        }

        //putting all latest emails in emails_db
        foreach ($emails_array as $ekey => $evalue) {
            $emails_db[$last_record_key] = $evalue;
            $last_record_key++;
        }

        //putting updated $emails_db array in "emails/emails_db.json" file
        $fp = fopen('emails/emails_db.txt', 'w');
        fwrite($fp, serialize($emails_array));
        fclose($fp);

//            export_report($filename);
    }

    //Export Parsing report in excel file
    public function export_report() {

        //get data from emails_array.txt file
        $emails_array_data = file_get_contents('emails/emails_array.txt');
        $emails_array = unserialize($emails_array_data); //unserialize

        //Selecting email_parsed_report file to edit
        $inputFileType = PHPExcel_IOFactory::identify("emails/email_parsed_report.xlsx");

        //Loading email_parsed_report file to edit
        $objPHPExcel = PHPExcel_IOFactory::load("emails/email_parsed_report.xlsx");

//            $objPHPExcel->disconnectWorksheets();
//            $objPHPExcel->createSheet();

        //Selecting sheet 2 in selected email_parsed_report file
        $objPHPExcel->setActiveSheetIndex(1);

        //get the highest row (containing data)
//            $highest_row = $objPHPExcel->setActiveSheetIndex(1)->getHighestRow();

        //remove those rows data
        for($row=2; $row < 5000; $row++) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $row, "");
        }
//            
        $counter = 2; //start from 2nd row
        //Putting data in sheet
        foreach ($emails_array as $k => $v) {
            $objPHPExcel->getActiveSheet()->setCellValue("A".$counter, "".$k.""); //email ID
            $objPHPExcel->getActiveSheet()->setCellValue("B".$counter, "".$v->subject.""); //email subject
            if(strpos($v->email_message,"-") == 0) {
                $v->email_message = "'".$v->email_message;
            }
            $objPHPExcel->getActiveSheet()->setCellValue("C".$counter, "".utf8_encode($v->email_message).""); //email subject
            $objPHPExcel->getActiveSheet()->setCellValue("D".$counter, "".date("d-m-Y h:i:s A", strtotime($v->date)).""); //email date
            $objPHPExcel->getActiveSheet()        // Format email date as dd-mm-yyyy hh:mm:ss AM/PM
->getStyle("D".$counter)
->getNumberFormat()
->setFormatCode('dd-mm-yyyy hh:mm:ss AM/PM');
            $objPHPExcel->getActiveSheet()->setCellValue("E".$counter, "".$v->pass_fail.""); //pass or fail
            $objPHPExcel->getActiveSheet()->setCellValue("F".$counter, "".utf8_encode($v->pass_rule).""); //pass rule
            $objPHPExcel->getActiveSheet()->setCellValue("G".$counter, "".$v->filter_data.""); //filter data
            $objPHPExcel->getActiveSheet()->setCellValue("H".$counter, "".utf8_encode($v->from).""); //sender
            $counter++;
        }

        /* put pas emails data in "Assigning sheet" sheet */

        //Selecting sheet 2 in selected email_parsed_report file
        $objPHPExcel->setActiveSheetIndex(2);
//            print_r($assigned_initials);
//            exit();
        //get the highest row (containing data)
//            $highest_row = $objPHPExcel->setActiveSheetIndex(2)->getHighestRow();

        $objPHPExcel->getActiveSheet()->setTitle("Assigning Sheet"); //rename sheet

        //remove those rows data
        for($row=2; $row < 5000; $row++) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $row, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $row, "");
        }

        //putting headers
        $objPHPExcel->getActiveSheet()->setCellValue("A1", "ID");
        $objPHPExcel->getActiveSheet()->setCellValue("B1", "Initials");
        $objPHPExcel->getActiveSheet()->setCellValue("C1", "Remarks");
        $objPHPExcel->getActiveSheet()->setCellValue("D1", "Subject-Line");
        $objPHPExcel->getActiveSheet()->setCellValue("E1", "Date-Received");
        $objPHPExcel->getActiveSheet()->setCellValue("F1", "Pass/Fail flag");
        $objPHPExcel->getActiveSheet()->setCellValue("G1", "Name of rule filed to pass or fail email");
        $objPHPExcel->getActiveSheet()->setCellValue("H1", "Filter Data");
        $objPHPExcel->getActiveSheet()->setCellValue("I1", "Sender");
//            
        $counter = 2; //start from 2nd row

        $assigned_initials = explode(",", $this->input->post('assigned_initials'));
        $assigned_initials_count = count($assigned_initials);
        $assigned_initials_counter = 0;

        //Putting data in sheet
        foreach ($emails_array as $k => $v) {
            if($v->pass_fail == "Pass") { //if email is pass then write otherwise ignore
                $objPHPExcel->getActiveSheet()->setCellValue("A".$counter, "".$k.""); //email ID
                $objPHPExcel->getActiveSheet()->setCellValue("B".$counter, "".$assigned_initials[$assigned_initials_counter].""); //Assigned Initials

                $objPHPExcel->getActiveSheet()->setCellValue("D".$counter, "".$v->subject.""); //email subject

                $objPHPExcel->getActiveSheet()->setCellValue("E".$counter, "".date("d-m-Y h:i:s A", strtotime($v->date)).""); //email date
                $objPHPExcel->getActiveSheet()        // Format email date as dd-mm-yyyy hh:mm:ss AM/PM
    ->getStyle("E".$counter)
    ->getNumberFormat()
    ->setFormatCode('dd-mm-yyyy hh:mm:ss AM/PM');
                $objPHPExcel->getActiveSheet()->setCellValue("F".$counter, "".$v->pass_fail.""); //pass or fail
                $objPHPExcel->getActiveSheet()->setCellValue("G".$counter, "".utf8_encode($v->pass_rule).""); //pass rule
                $objPHPExcel->getActiveSheet()->setCellValue("H".$counter, "".$v->filter_data.""); //filter data
                $objPHPExcel->getActiveSheet()->setCellValue("I".$counter, "".utf8_encode($v->from).""); //sender

                $counter++;
                $assigned_initials_counter++;
                if($assigned_initials_counter == $assigned_initials_count) { //if counter = total count
                    $assigned_initials_counter = 0;
                }
            }
        }

        //Generating File
        ob_end_clean();
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=email_parsed_report.xlsx");
        header("Cache-Control: max-age=0");
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $inputFileType);
        $objWriter->save(str_replace(__FILE__,'emails/email_parsed_report.xlsx',__FILE__));
        $objWriter->setPreCalculateFormulas(false);
        $objWriter->save("php://output");
        exit();
   }
}