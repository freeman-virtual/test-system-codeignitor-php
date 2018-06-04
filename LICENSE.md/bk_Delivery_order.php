<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Delivery_order extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->helper('hv_helper.php');
        $this->load->model('Deliverymodel');
        $this->load->model('Customermodel');
        sessionset();
    }
	public function index()
	{
		$script     = '';
        $customerList  = $this->Customermodel->customerInfoLists();
        $data1[ 'customerList' ]               = $customerList;
		$content    = $this->load->view( 'delivery_order/delivery_order', $data1, true );
        $data[ 'navigation' ]                   = '';
        $data[ 'Emessage' ]                     = '';
        $data[ 'Smessage' ]                     = '';
        $data[ 'header' ][ 'title' ]            = 'Delivery Order';
        $data[ 'header' ][ 'metakeyword' ]      = 'Delivery Order';
        $data[ 'header' ][ 'metadescription' ]  = 'Delivery Order';
        $data[ 'footer' ][ 'script' ]           = $script;
        $data[ 'content' ]                      = $content;
        $data[ 'breadcrumb' ] = '';
        $this->template( $data );
	}

    public  function ExcelDataAdd() {
        error_reporting(E_ALL);
        //echo $userfile = $this->input->post( 'userfile' );
        if(isset($_POST["import"]))
        {
              $filename=$_FILES["file"]["tmp_name"];
              $ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
              if($_FILES["file"]["size"] > 0 && $ext == 'csv')
                {
                  $file = fopen($filename, "r");
                  $flag = true;
                   while (($importdata = fgetcsv($file, 10000, ",")) !== FALSE)
                   {
                    if($flag) { $flag = false; continue; }
                    $recipient_name     = $importdata[0];
                    $recipient_address  = $importdata[1];
                    $recipient_contact  = $importdata[2];
                    $recipient_postcode = $importdata[3];
                    $good_qty           = $importdata[4];
                    $amount             = $importdata[5];
                    $do_date            = $importdata[6];
                    $do_id              = $importdata[7];

                          $data = array(
                              'recipient_name'    => $recipient_name,
                              'recipient_address' => $recipient_address,
                              'recipient_contact' => $recipient_contact,
                              'recipient_postcode'=> $recipient_postcode,
                              'good_qty'          => $good_qty,
                              'amount'            => $amount,
                              'do_date'           => $do_date,
                              'do_type'           => 'Do',
                              'do_id'             => $do_id,
                              'createdon'         => date("Y-m-d H:i:s")
                              );
                   $insert = $this->Deliverymodel->Add_Delivery($data, $do_id);
                }                    
            fclose($file);
                $this->session->set_flashdata('successMSG', 'Data are imported successfully..');
                redirect(base_url().'delivery_order');
            }else{
                $this->session->set_flashdata('errorMSG', 'Something went wrong..');
                redirect(base_url().'delivery_order');
            }
        }
             //redirect(base_url() . "put link were you want to redirect");
     }
     public function add()
    {
        $script     = '';
        $content    = $this->load->view( 'delivery_order/add_pickup', '', true );
        $data[ 'navigation' ]                   = '';
        $data[ 'Emessage' ]                     = '';
        $data[ 'Smessage' ]                     = '';
        $data[ 'header' ][ 'title' ]            = 'Add Pickup job';
        $data[ 'header' ][ 'metakeyword' ]      = 'Add Pickup job';
        $data[ 'header' ][ 'metadescription' ]  = 'Add Pickup job';
        $data[ 'footer' ][ 'script' ]           = $script;
        $data[ 'content' ]                      = $content;
        $data[ 'breadcrumb' ] = '<i class="icon-home"></i>
                                <a href="'.$this->config->item( 'admin_url' ).'delivery_order">Delivery Order</a>
                                <i class="fa fa-angle-right"></i>
                                <i class="icon-home"></i>Add Pickup job';
        $this->template( $data );
    }
    function pickupJobValidate(){
        //error_reporting(E_ALL);

        $this->form_validation->set_rules( 'recipient_name', 'Recipient Name', 'required|min_length[4]' );
        $this->form_validation->set_rules( 'recipient_address', 'Recipient Address', 'required|min_length[4]' );
        $this->form_validation->set_rules( 'recipient_contact', 'Recipient number', 'required|min_length[7]' );
        $this->form_validation->set_rules('recipient_postcode', 'Recipient postcode', 'required');
        $this->form_validation->set_rules('good_qty', 'Good qty', 'required');
        if( $this->form_validation->run() == FALSE ) {
            echo validation_errors();
        } else {
            $recipient_name     = $this->input->post( 'recipient_name' );
            $recipient_address     = $this->input->post( 'recipient_address' );
            $recipient_contact     = $this->input->post( 'recipient_contact' );
            $recipient_postcode     = $this->input->post( 'recipient_postcode' );
            $good_qty     = $this->input->post( 'good_qty' );
            $amount     = $this->input->post( 'amount' );
            $do_date     = $this->input->post( 'do_date' );
            $do_id     = $this->input->post( 'do_id' );
            $weight     = $this->input->post( 'weight' );
            $tracking_no     = $this->input->post( 'tracking_no' );
            $data = array(
                              'recipient_name'    => $recipient_name,
                              'recipient_address' => $recipient_address,
                              'recipient_contact' => $recipient_contact,
                              'recipient_postcode'=> $recipient_postcode,
                              'good_qty'          => $good_qty,
                              'amount'            => $amount,
                              'do_date'           => $do_date,
                              'do_type'           => 'Pickup',
                              'do_id'             => $do_id,
                              'weight'            => $weight,
                              'tracking_no'       => $tracking_no,
                              'createdon'         => date("Y-m-d H:i:s")
                              );
            echo $this->Deliverymodel->addPickupJob($data);
        }
    }

	function template( $data ){
        $this->load->view( 'common/templatecontent', $data );
    }
}
