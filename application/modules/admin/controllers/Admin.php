<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Admin Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Admin
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Admin extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('settings_model');
    }
    /** default function of the controller */
    public function index(){
        redirect('admin/login');
    }
    /**
    * Login page for application
    */
    public function login(){
        
		if($this->session->userdata('admin_login') === TRUE || $this->session->userdata('user_login') === TRUE )
            redirect('admin/dashboard');
        $this->form_validation->set_rules('username', 'Username/Email', 'trim|required|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
        if(!$this->form_validation->run()){
			$data['page_title'] = 'Login';
            $data['app_settings'] = $this->settings_model->get_app_settings();
			
			// var_dump($data);
			
            $this->load->view('admin/login', $data);
        }
		else{
            // $this->load->model('users_model');
            // $user = $this->users_model->validate_user();
            // if(isset($user->user_id) && $user->user_status == USER_STATUS_ACTIVE){
                // if(!($this->input->post('remember', TRUE) == 1)){
                    // $this->session->sess_expiration = 7200;
                    // $this->session->sess_expire_on_close = TRUE;
                // }
                // if($user->user_role == USER_TYPE_ADMIN)
                    // $this->session->set_userdata('admin_login', TRUE);
                // else
                    // $this->session->set_userdata('user_login', TRUE);
                // $this->session->set_userdata('user_id', $user->user_id);
                // $this->session->set_userdata('user_name', $user->user_name);
                // $this->session->set_userdata('user_email', $user->user_email);
                // $setting = $this->users_model->get_settings($user->user_id);
                // $this->session->set_userdata('user_timezone', $setting->time_zone);
                // redirect('admin/dashboard');
            // }
			// else if(isset($user->user_id) && $user->user_status == USER_STATUS_INACTIVE){
                // $this->session->set_flashdata('login_error', get_alert_html(ERROR_LOGIN_DISABLED, ALERT_TYPE_ERROR));
                // redirect('admin/login', 'location', 301);
            // }
			// else{
                // $this->session->set_flashdata('login_error', get_alert_html(ERROR_LOGIN_ERROR, ALERT_TYPE_ERROR));
                // redirect('admin/login', 'location', 301);
            // }
        }
    }
    /**
    * Signup page for application
    */
    public function signup(){
        if($this->session->userdata('admin_login') === TRUE || $this->session->userdata('user_login') === TRUE )
            redirect('admin/dashboard');
        $app_settings = $this->settings_model->get_app_settings();
        if($app_settings->signup == SIGNUP_DISABLED) 
            redirect('admin/login');
        $this->form_validation->set_rules('username', 'User Name', 'trim|required|xss_clean|callback_username_check');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email|callback_email_check');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean|callback_password_check');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|xss_clean');
        $this->form_validation->set_message('required','%s is required');
        $this->form_validation->set_message('valid_email','invalid email address');
        if(!$this->form_validation->run()){
            $data['page_title'] = 'Signup';
            $this->load->view('signup', $data);
        }else{
            $this->load->model('users_model');
            $user_id = $this->users_model->signup();
            if($user_id){
                $this->session->set_userdata('user_login', TRUE);
                $this->session->set_userdata('user_id', $user_id);
                $this->session->set_userdata('user_name', $this->input->post('username', TRUE));
                $this->session->set_userdata('user_email', $this->input->post('email', TRUE));
                $setting = $this->users_model->get_settings($user_id);
                $this->session->set_userdata('user_timezone', $setting->time_zone);
                redirect('admin/dashboard');
            }else{
                $this->session->set_flashdata('signup_error', get_alert_html(ERROR_LOGIN_ERROR, ALERT_TYPE_ERROR));
                redirect('admin/signup', 'location', 301);
            }
        }
    }
    /**
    * Dashboard - List of campaigns, all compaign if admin logged id
    * @param $offset integer, The param for pagination
    */
    public function dashboard($offset = 0){
        session_check();
        $this->load->model('post_model');
        $user_id = $this->session->userdata('admin_login') === TRUE ? 0 : $this->session->userdata('user_id');
        $data['total_posts'] = $this->post_model->get_dashboard_list($user_id, GET_COUNT);
        $data['posts'] = $this->post_model->get_dashboard_list($user_id, GET_RECORDS, $offset);
        $data['pagination'] =     create_admin_pagination_links('admin/dashboard', $data['total_posts']);
        $data['page_title'] = 'Dashboard';
        $data['view'] = 'dashboard';
        $this->load->view('template', $data);
    }
    /**
    * Logout from application link
    */
    public function logoff(){
        $this->session->sess_destroy();;
        redirect('admin/login');
    }
    /**
    * Forgot password page to enter email address
    */
    public function forgot_password(){
        if($this->session->userdata('admin_login') === TRUE || $this->session->userdata('user_login') === TRUE)
            redirect('admin/dashboard');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean');
        if(!$this->form_validation->run()){
             $data['page_title'] = 'Forgot Password';
             $this->load->view('forgot_password', $data);
        }else{
            $config = array(
                'mailtype' => 'html',
                'charset' => 'utf-8',
                'newline' => '\r\n'
            );
            $this->load->model('users_model');
            $user = $this->users_model->get_user($this->input->post('email', TRUE));
            if(!isset($user->user_id)){
                $this->session->set_flashdata('email_not_exist', get_alert_html(ERROR_EMAIL_NOT_EXIST, ALERT_TYPE_ERROR));
                redirect('admin/forgot_password', 'location', 301);
            }else if($user->user_status == USER_STATUS_INACTIVE){
                $this->session->set_flashdata('email_not_exist', get_alert_html(ERROR_LOGIN_DISABLED, ALERT_TYPE_ERROR));
                redirect('admin/forgot_password', 'location', 301);
            }
            $hash = md5($user->user_password);
            $this->users_model->save_reset_password_request($user->user_id, $hash);
            $reset_url = base_url().'index.php/admin/reset_password/'.$hash;
            $message = 'Follow this link to reset your Password.<br> 
                        <a href="'.$reset_url.'">Reset Password</a>';
            $this->load->library('email', $config);
            $this->email->from(ADMIN_EMAIL, 'ADMIN');
            $this->email->to($this->input->post('email', TRUE)); 
            $this->email->subject('Reset Password - PHP FMT');
            $this->email->message($message);    
            if($this->email->send())
                $this->session->set_flashdata('email_sent', get_alert_html(SUCCESS_RESET_PASSWORD_MAIL_SENT, ALERT_TYPE_SUCCESS));
            else
                $this->session->set_flashdata('email_sent', get_alert_html(ERROR_SEND_EMAIL, ALERT_TYPE_ERROR));
            redirect('admin/forgot_password','location', 301);    
        }
    }
    /**
    * Reset password link sent in email
    * @param $hash string, The hash string used to validate a user
    */
    public function reset_password($hash = ''){
        if($this->session->userdata('admin_login') === TRUE || $this->session->userdata('user_login') === TRUE)
            redirect('admin/dashboard');
        if(empty($hash))
            redirect('admin/login');
        $this->load->model('users_model');
        $request = $this->users_model->validate_reset_password_request($hash);
        if(!isset($request->request_id))
            redirect('admin/login', 'location', 301);
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
        $this->form_validation->set_rules('confirm_password', 'Password', 'trim|required|xss_clean');
         if(!$this->form_validation->run()){
            $data['page_title'] = 'Reset Password';
            $data['hash'] = $hash;
            $this->load->view('reset_password', $data);
         }else{
            $this->users_model->update_password($request->user_id, $this->input->post('password', TRUE));
            $this->users_model->delete_reset_password_requests($request->user_id);
            $this->session->set_flashdata('password_reset', get_alert_html(SUCCESS_PASSWORD_RESET, ALERT_TYPE_SUCCESS));
            redirect('admin/login','location', 301);
         }
    }

    /**
    * custom function for form_validation library to check if email address already exsist in database
    * @param $email string, email address of the user
    */
    public function email_check($email){
        $user_id = $this->uri->segment(3);
        $this->load->model('users_model');
        $user_id = empty($user_id) ? $this->session->userdata('user_id') : $user_id;
        if($this->users_model->isemailexist($email, $user_id)){
            $this->form_validation->set_message('email_check', 'The email address already exists');
            return FALSE;
        }
        return TRUE; 
    }

    /**
    * custom function for form_validation library to check if username already exsist in database
    * @param $user_name string, username of the user to be checked
    */
    public function username_check($user_name){
        $user_id = $this->uri->segment(3);
        $this->load->model('users_model');
        $user_id = empty($user_id) ? $this->session->userdata('user_id') : $user_id;
        if($this->users_model->isusernameexist($user_name, $user_id)){
            $this->form_validation->set_message('username_check', 'The username already exists');
            return FALSE;
        }else{
            return TRUE;
        }
    }
}

/* End of file admin.php */
/* Location: ./application/controllers/admin.php */