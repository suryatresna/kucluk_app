<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * settings_model Class
 * 
 * @package   PHP_FMT
 * @subpackage  Models
 * @category  settings_model
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class settings_model extends CI_Model{
    
    function __construct(){
        parent :: __construct();
    }
    /**
    * get setting of user
    * @param $user_id integer, The id of user
    * @return object 
    */
    public function get_settings($user_id){
        // $this->db->where('user_id', $user_id);
        // return $this->db->get('user_settings')->row();
    }
    /**
    * get setting of whole application
    * @return object 
    */
    public function get_app_settings(){
        // return $this->db->get('app_settings')->row();
    }
    /**
    * update setting of user
    * @param $user_id integer, The id of user
    * @return void 
    */
    public function update_user_setting($user_id){
        $setting = array(
          'canvas_width' => $this->input->post('canvas_width', TRUE),
          'canvas_height' => $this->input->post('canvas_height', TRUE),
          'time_zone' => $this->input->post('time_zone', TRUE),
        );
        $this->db->where('user_id', $user_id);
        $this->db->update('user_settings', $setting);
        $this->session->set_userdata('user_timezone', $this->input->post('time_zone', TRUE));
    }
    /**
    * update application setting
    * @return void 
    */
    public function update_app_setting(){
        $setting = array(
          'page_limit' => $this->input->post('page_limit', TRUE),
          'group_limit' => $this->input->post('group_limit', TRUE),
          'event_limit' => $this->input->post('event_limit', TRUE),
          'signup' => $this->input->post('signup', TRUE),
          'spintax' => $this->input->post('spintax', TRUE)
        );
        if($setting['page_limit'] < DEFAULT_GROUP_LIMT)
          $setting['page_limit'] = DEFAULT_GROUP_LIMT;
        if($setting['group_limit'] < DEFAULT_PAGE_LIMIT)
          $setting['group_limit'] = DEFAULT_PAGE_LIMIT;
        if($setting['event_limit'] < DEFAULT_EVENT_LIMIT)
          $setting['event_limit'] = DEFAULT_EVENT_LIMIT;
        $this->db->update('app_settings', $setting);
    }
}


/* End of file settings_model.php */
/* Location: ./application/models/settings_model.php */