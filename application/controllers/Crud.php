<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crud extends CI_Controller {

	/**
     * Users constructor.
     */
    public function __construct()
    {
		parent::__construct();
		
		//Load Helper
		$this->load->helper('url');

		$this->load->database();
	}
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$tableName = $this->input->get('tableName');

		$query = "SELECT f.table_name, t.parent, t.api_url_value, t.insert, t.edit, t.delete, f.name, f.type, f.type_value, f.label, f.length, f.inlineHelpText, f.nillable, f.insert_display_order, f.view_display_order, f.items_page_order, f.items_page_display_proportion FROM fields f, tables t WHERE t.name = f.table_name and t.name = '" . $tableName ."__c' ORDER BY f.table_name";
					  
		$result = $this->db->query($query)->result_array();

		$this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
	}
	public function orgs_by_email()
	{
		$email = $this->input->get('email');

		$query = "SELECT org_id, org_name__c, full_name__c  from hipaa_contact__c WHERE Email_Address__c = '".$email."'";
					  
		$result = $this->db->query($query)->result_array();

		$this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
	}
	public function dashboard_data()
	{
		$type = $this->input->get('type');
		$filter = $this->input->get('filter');
		$org_id = $this->input->get('org_id');				
	
		$query = "";
		if( $type == 'change_request__c' && $filter == 'open_change_request')
		{
			$query 	= " Select Id, Name, Description__c, Date_submitted__c, Date_required__c, Priority__c, Submitter_full_name__c, Submitter_full_name__c, Date_time_change_completed__c 
				  		FROM change_request__c 
						WHERE YEAR(Date_submitted__c)  = YEAR(CURDATE())-1 and  Change_Done__c = false and Organization__c = '".$org_id."'";
		} 
					  
		$result = $this->db->query($query)->result_array();

		$this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
	}

}
