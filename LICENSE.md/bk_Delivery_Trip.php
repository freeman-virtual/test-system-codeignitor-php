<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Delivery_Trip extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->helper('hv_helper.php');
        $this->load->model('Delivery_Trip_Model');
        sessionset();
    }

    public function create()
  	{

       $script     = '';
       $data1      = array();
       $content    = $this->load->view( 'delivery_trip/create', $data1, true );

       $data[ 'navigation' ]                   = '';
       $data[ 'Emessage' ]                     = '';
       $data[ 'Smessage' ]                     = '';
       $data[ 'header' ][ 'head_title' ]       = 'Assign Delivery Trip';
       $data[ 'header' ][ 'title' ]            = '<i class="fa fa-plus"></i>&nbsp;&nbsp;Assign Delivery Trip <span class="badge badge-success">Only show initialed DO</span>';
       $data[ 'header' ][ 'metakeyword' ]      = 'Assign Delivery Trip';
       $data[ 'header' ][ 'metadescription' ]  = 'Assign Delivery Trip';
       $data[ 'footer' ][ 'script' ]           = $script;
       $data[ 'content' ]                      = $content;
       $data[ 'breadcrumb' ]                   = '<li class="active">Delivery Management</li>';
       $this->template( $data );

  	}

    /*public function confirm()
  	{

       $script     = '';
       $data1      = array();
       $content    = $this->load->view( 'delivery_trip/confirm', $data1, true );

       $data[ 'navigation' ]                   = '';
       $data[ 'Emessage' ]                     = '';
       $data[ 'Smessage' ]                     = '';
       $data[ 'header' ][ 'head_title' ]       = 'Confirmation Assign New Delivery Trip';
       $data[ 'header' ][ 'title' ]            = '<i class="fa fa-plus"></i>&nbsp;&nbsp;Confirmation Assign New Delivery Trip';
       $data[ 'header' ][ 'metakeyword' ]      = 'Confirmation Assign New Delivery Trip';
       $data[ 'header' ][ 'metadescription' ]  = 'Confirmation Assign New Delivery Trip';
       $data[ 'footer' ][ 'script' ]           = $script;
       $data[ 'content' ]                      = $content;
       $data[ 'breadcrumb' ]                   = '<li class="active">Delivery Management</li>';
       $this->template( $data );

  	}*/


          function trash_trip(){
              $id  = $this->input->post( 'id' );
              $delete     = $this->Delivery_Trip_Model->trash_delivery_trip( $id );
              if($delete){
                  $this->session->set_flashdata('successMSG', 'Selected delivery trip has been removed. [ID:'.$id.']');
              }else{
                  $this->session->set_flashdata('errorMSG', 'Operation failed.');
              }
          }


        public function getTableResponse(){

            $data           = array();
            $results        = array();
            $where          = array();
            $resultLists    = $this->Delivery_Trip_Model->get_datatables($where);

            if($_POST['start']){
                $si_no  = $_POST['start']+1;
            }else{
                $si_no  = 1;
            }
            $data       = array();
            if($resultLists){
                foreach ($resultLists as $node) {

                    $editBtn        = '<a href="'.$this->config->item( 'admin_url' ).'vehicle/edit/'.$node->vehicle_id.'"
                    class="btn btn-primary btn-sm"><i class="glyphicon glyphicon-edit"></i></a>';
                    $deleteBtn      = '<a href="javascript:;" onClick="return trash_vehicle(\''.$node->vehicle_id.'\');"
                    class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-remove"></i></a>';

                    $row    = array();

                    $row[]  = $node->vehicle_id;
                    $row[]  = $node->plate_number;
                    $row[]  = $node->vehicle_model;
                    $row[]  = $node->brand;
                    $row[]  = $node->vehicle_remarks;
                    $row[]  = $editBtn.$deleteBtn;

                    $data[] = $row;
                    $si_no++;
                }
            }
            $results = array(
                            "draw"              => $_POST['draw'],
                            "recordsTotal"      => $this->Delivery_Trip_Model->count_all($where),
                            "recordsFiltered"   => $this->Delivery_Trip_Model->count_filtered($where),
                            "data"              => $data
                    );
            echo json_encode($results);
        }

    public function edit()
    {
        $script     = '';
        $vehicle_id    = $this->uri->segment(3);
        $vehicleInfo   = $this->Vehiclemodel->vehicleInfo($vehicle_id);
        if(!$vehicleInfo){ redirect( $this->config->item( 'admin_url' ) . 'vehicle' ); }
        $data1[ 'vehicleInfo' ]                 = $vehicleInfo;

        $content    = $this->load->view( 'vehicle/edit_vehicle', $data1, true );
        $data[ 'navigation' ]                   = '';
        $data[ 'Emessage' ]                     = '';
        $data[ 'Smessage' ]                     = '';
        $data[ 'header' ][ 'title' ]            = 'Edit Vehicle';
        $data[ 'header' ][ 'metakeyword' ]      = 'Edit Vehicle';
        $data[ 'header' ][ 'metadescription' ]  = 'Edit Vehicle';
        $data[ 'footer' ][ 'script' ]           = $script;
        $data[ 'content' ]                      = $content;
        $data[ 'breadcrumb' ]                   = '
                                              <li><a href="'.$this->config->item( 'admin_url' ).'vehicle">Vehicle lists</a></li>
                                              <li class="active">Edit Vehicle</li>';
        $this->template( $data );
    }
    function vehicleValidate(){

        $plate_number = $this->input->post( 'plate_number' );
        $vehicle_id    = $this->input->post( 'vehicle_id' );
        $this->form_validation->set_rules( 'plate_number', 'Plate Number', 'required' );
        $this->form_validation->set_rules( 'vehicle_model', 'Vehicle Model', 'required' );
        $this->form_validation->set_rules( 'brand', 'Brand', 'required' );

        if( $this->form_validation->run() == FALSE ) {
            echo validation_errors();
        } else {
            $vehicleInfo = $this->Vehiclemodel->vehicleInfo( $vehicle_id );
            if($vehicleInfo){
                echo $result = $vehicle_id = $this->Vehiclemodel->updateVehicle();
                if($result){
                    $this->session->set_flashdata('successMSG', 'Vehicle Updated successfully.');
                }else{
                    $this->session->set_flashdata('errorMSG', 'Operation failed.');
                }
            }else{
                echo $result1 = $vehicle_id = $this->Vehiclemodel->addVehicle();
                if($result1){
                    $this->session->set_flashdata('successMSG', 'Vehicle Added successfully.');
                }else{
                    $this->session->set_flashdata('errorMSG', 'Operation failed.');
                }
            }
        }
    }

    function deletevehicle(){
        $vehicle_id = $this->input->post( 'vehicle_id' );
        $delete     = $this->Vehiclemodel->deleteVehicle( $vehicle_id );
        if($delete){
            $this->session->set_flashdata('successMSG', 'Vehicle deleted successfully.');
        }else{
            $this->session->set_flashdata('errorMSG', 'Operation failed.');
        }
    }
    public function set_view_type(){
        $json = array();

        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
          $view_vehicle  = $this->input->post('view_vehicle');
          $array      = array('view_vehicle' => $view_vehicle);
          $this->session->set_userdata($array);
        }
        echo json_encode($json);
    }

	function template( $data ){
        $this->load->view( 'common/templatecontent', $data );
    }
}
