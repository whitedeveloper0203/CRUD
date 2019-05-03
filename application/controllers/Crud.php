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
		$stage = $this->input->get('stage');

		$current_db = $this->db;
		if($stage == 'staging')
			$current_db = $this->load->database('staging', TRUE);
		else if($stage == 'prod')
			$current_db = $this->load->database('prod', TRUE);
		
		$query = "SELECT f.table_name, t.parent, t.api_url_value, t.insert, t.edit, t.delete, f.name, f.type, f.type_value, f.label, f.length, f.inlineHelpText, f.nillable, f.insert_display_order, f.view_display_order, f.items_page_order, f.items_page_display_proportion FROM fields f, tables t WHERE t.name = f.table_name and t.name = '" . $tableName ."__c' ORDER BY f.table_name";
					  
		$result = $current_db->query($query)->result_array();

		$this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
	}
	public function orgs_by_email()
	{
		$email = $this->input->get('email');

		$query = "SELECT id as contact_id, org_id, org_name__c, full_name__c  from hipaa_contact__c WHERE Email_Address__c = '".$email."'";
					  
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
		$contact_id = $this->input->get('contact_id');				

		$query = "";
		if( $type == 'change_request__c' && $filter == 'open_change_request')
		{
			$query 	= " Select Id, Name, Description__c, Date_submitted__c, Date_required__c, Priority__c, Submitter_full_name__c, Submitter_full_name__c, Date_time_change_completed__c 
				  		FROM change_request__c 
						WHERE YEAR(Date_submitted__c)  = YEAR(CURDATE()) and  Change_Done__c = false and Organization__c = '".$org_id."'";
		} 
		else if( $type == 'security_incident__c' && $filter == 'open_breach_incident')
		{
			$query 	= "SELECT Id, Name, Security_Incident_Description_Summary__c, Reportable_Breach__c, 
					Investigation_Start_Time__c, Breach__c, Number_of_Patients_Affected__c, Date_Notified_HHS_Breach__c, Date_Patients_Notified_Breach__c 
					FROM security_incident__c 
					WHERE  YEAR(Time_of_Breach__c) = YEAR(current_date()) and Breach__c = true and Date_Patients_Notified_Breach__c IS null and  Organization__c= '".$org_id."'";
		}
		else if( $type == 'vendor_review__c' && $filter == 'review_failed')
		{
			$query 	= "Select vr.Id, vc.Name, vr.Last_Reported_Breach__c, vr.Last_Assessment_Date__c, 
					vr.HIPAA_Risk_Assessment_Date__c ,vr.Date_first_sent__c, vr.Date_time_review_completed__c 
					from vendor_review__c as vr
					left join vendors__c as vc ON vr.Vendor__c = vc.id
					where YEAR(vr.Date_first_sent__c) = YEAR(current_date()) and vr.is_survey_filled__c=true and (vr.Date_time_review_completed__c = NULL or vr.Digital_Signature1__c = 						NULL or 
					vr.Digital_Signature2__c = NULL or  vr.Full_Breach_Policy__c = FALSE or vr.Last_assessment_Date__c = NULL) and vc.Organization__c= '".$org_id."'";
		} 
		else if( $type == 'media_sanitization__c' && $filter == 'submitted')
		{
			$query 	= "SELECT media_sanitization_request__c.Id,Media_Sanitization__c, Media_Sanitization__c.Name ,Media_Sanitization__c.Request_Processing_Person_Name__c,Media_Sanitization__c.Status__c,Process__c,media_sanitization_request__c.Name,hipaa_contact__c.Full_Name__c, Date_Time_of_Request__c
						FROM media_sanitization_request__c, media_sanitization__c, hipaa_contact__c  
						WHERE Person_Requesting__c= '".$contact_id."' AND hipaa_contact__c.id = media_sanitization_request__c.Person_Requesting__c
						AND Media_sanitization__c.id = media_sanitization_request__c.Media_Sanitization__c
						ORDER BY Date_Time_of_Request__c desc";
		}  
				  
		$result = $this->db->query($query)->result_array();

		$this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
	}

}
