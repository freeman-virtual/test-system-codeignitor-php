<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_jobs extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->helper('hv_helper.php');
        $this->load->model('Deliverymodel');
        $this->load->model('Customermodel');
        $this->load->model('Drivermodel');
        $this->load->model('PODmodel');
        $this->load->model('api/push_notification_model');
        sessionset();
        //error_reporting(E_ALL);
    }
    public function index(){
      $config = array();
      $config['uri_segment']      = 2;
      $config['num_links']        = 2;
      $config['page_query_string']= true;
      $config['use_page_numbers'] = TRUE;
      $config['full_tag_open']    = "<ul class='pagination pull-right'>";
      $config['full_tag_close']   ="</ul>";
      $config['num_tag_open']     = '<li>';
      $config['num_tag_close']    = '</li>';
      $config['cur_tag_open']     = "<li class='disabled'><li class='active'><a href='#'>";
      $config['cur_tag_close']    = "<span class='sr-only'></span></a></li>";
      $config['next_tag_open']    = "<li>";
      $config['next_tagl_close']  = "</li>";
      $config['prev_tag_open']    = "<li>";
      $config['prev_tagl_close']  = "</li>";
      $config['first_tag_open']   = "<li>";
      $config['first_tagl_close'] = "</li>";
      $config['last_tag_open']    = "<li>";
      $config['last_tagl_close']  = "</li>";
      $status       = $this->input->get( 'status' );
      $order_search = $this->input->get('order_search');
      $order_type   = $this->input->get('order_type');
      $post_from    = $this->input->get('post_from');
      $post_to      = $this->input->get('post_to');
      $sort_by      = $this->input->get('sort_by');
      $delivery_type = $this->input->get('delivery_type');
      $per_page     = $this->input->get('per_page');

      if($status == 'assigned'){
          $job_id     = $this->input->get('order_search');
          $driver_id  = $this->input->get('driver_id');
          $vehicle_id = $this->input->get('vehicle_id');
          if($this->session->userdata('view_asgn_job')){
              $config['per_page']    = $this->session->userdata('view_asgn_job');
              $data1[ 'page_limit' ] = $this->session->userdata('view_asgn_job');
          }else{
              $config['per_page']    = LRGE_PAGINATION_PER_PAGE;
              $data1[ 'page_limit' ] = LRGE_PAGINATION_PER_PAGE;
          }
          if(isset($job_id) || isset($driver_id) || isset($vehicle_id)){
              $page_number     = ($per_page) ? $per_page : 1;
              $offset          = ($page_number  == 1) ? 0 : ($page_number * $config['per_page']) - $config['per_page'];

              $config['base_url'] = base_url() . "manage_jobs?status=assigned&order_search=".$job_id."&driver_id=".$driver_id."&vehicle_id=".$vehicle_id;
              $config['total_rows'] = $this->Deliverymodel->count_managejobLists(0,$job_id,$driver_id,$vehicle_id);
              $jobLists   = $this->Deliverymodel->managejobLists($config["per_page"],$offset,0,$job_id,$driver_id,$vehicle_id);
          }else{
              $page_number = ($per_page) ? $per_page : 1;
              $offset  = ($page_number  == 1) ? 0 : ($page_number * $config['per_page']) - $config['per_page'];
              $config['base_url'] = base_url() . "manage_jobs/?status=assigned";
              $config['total_rows'] = $this->Deliverymodel->count_managejobLists(0);
              $jobLists   = $this->Deliverymodel->managejobLists($config["per_page"], $offset,0);
          }
          $this->pagination->initialize($config);
          $driverList  = $this->Drivermodel->driverListsAll();
          $vehicleList = $this->Drivermodel->vehicleListsAll();
          $data1["links"]         = $this->pagination->create_links();
          $data1['total_rows']    = $config['total_rows'];
          $data1[ 'vehicleList' ] = $vehicleList;
          $data1[ 'driverList' ]  = $driverList;
          $data1['jobLists']      = $jobLists;
          $content    = $this->load->view( 'manage_jobs/assigned_jobs', $data1, true );
      }else if($status == 'completed'){
          $job_id     = $this->input->get('order_search');
          $driver_id  = $this->input->get('driver_id');
          $vehicle_id = $this->input->get('vehicle_id');
          if($this->session->userdata('view_comp_job')){
              $config['per_page']    = $this->session->userdata('view_comp_job');
              $data1[ 'page_limit' ] = $this->session->userdata('view_comp_job');
          }else{
              $config['per_page']    = LRGE_PAGINATION_PER_PAGE;
              $data1[ 'page_limit' ] = LRGE_PAGINATION_PER_PAGE;
          }
          if(isset($job_id) || isset($driver_id) || isset($vehicle_id)){
              $page_number     = ($per_page) ? $per_page : 1;
              $offset          = ($page_number  == 1) ? 0 : ($page_number * $config['per_page']) - $config['per_page'];

              $config['base_url'] = base_url() . "manage_jobs?status=completed&order_search=".$job_id."&driver_id=".$driver_id."&vehicle_id=".$vehicle_id;
              $config['total_rows'] = $this->Deliverymodel->count_managejobLists(1,$job_id,$driver_id,$vehicle_id);
              $jobLists   = $this->Deliverymodel->managejobLists($config["per_page"],$offset,1,$job_id,$driver_id,$vehicle_id);
          }else{
              $page_number = ($per_page) ? $per_page : 1;
              $offset  = ($page_number  == 1) ? 0 : ($page_number * $config['per_page']) - $config['per_page'];
              $config['base_url'] = base_url() . "manage_jobs/?status=completed";
              $config['total_rows'] = $this->Deliverymodel->count_managejobLists(1);
              $jobLists   = $this->Deliverymodel->managejobLists($config["per_page"], $offset,1);
          }
          $this->pagination->initialize($config);
          $driverList  = $this->Drivermodel->driverListsAll();
          $vehicleList = $this->Drivermodel->vehicleListsAll();
          $data1["links"]         = $this->pagination->create_links();
          $data1['total_rows']    = $config['total_rows'];
          $data1[ 'vehicleList' ] = $vehicleList;
          $data1[ 'driverList' ]  = $driverList;
          $data1['jobLists']      = $jobLists;
          $content    = $this->load->view( 'manage_jobs/completed_jobs', $data1, true );
      }else{
          if($this->session->userdata('view_init_job')){
              $config['per_page']    = $this->session->userdata('view_init_job');
              $data1[ 'page_limit' ] = $this->session->userdata('view_init_job');
          }else{
              $config['per_page']    = LRGE_PAGINATION_PER_PAGE;
              $data1[ 'page_limit' ] = LRGE_PAGINATION_PER_PAGE;
          }
          if(isset($order_search) || isset($order_type) || isset($post_from) || isset($post_to) || isset($sort_by)){
              $page_number     = ($per_page) ? $per_page : 1;
              $offset          = ($page_number  == 1) ? 0 : ($page_number * $config['per_page']) - $config['per_page'];

              $config['base_url'] = base_url() . "manage_jobs?order_search=".$order_search."&order_type=".$order_type."&post_from=".$post_from."&post_to=".$post_to."&sort_by=".$sort_by."&delivery_type=".$delivery_type;
              $config['total_rows'] = $this->Deliverymodel->count_unassignedDeliveryLists($order_search,$order_type, $post_from, $post_to, $delivery_type);
              $unassignedDeliveryLists = $this->Deliverymodel->unassignedDeliveryLists($config["per_page"], $offset, $order_search, $order_type, $post_from, $post_to, $sort_by, $delivery_type);
          }else{
              $page_number = ($per_page) ? $per_page : 1;
              $offset  = ($page_number  == 1) ? 0 : ($page_number * $config['per_page']) - $config['per_page'];
              $config['base_url'] = base_url() . "manage_jobs";
              $config['total_rows'] = $this->Deliverymodel->count_unassignedDeliveryLists();
              $unassignedDeliveryLists = $this->Deliverymodel->unassignedDeliveryLists($config["per_page"], $offset);
          }
          $this->pagination->initialize($config);
          $data1["links"]       = $this->pagination->create_links();
          $data1['total_rows']  = $config['total_rows'];
          $data1['unassignedDeliveryLists']   = $unassignedDeliveryLists;
          $content    = $this->load->view( 'manage_jobs/manage_jobs', $data1, true );
      }
      $data[ 'navigation' ]                   = '';
      $data[ 'Emessage' ]                     = '';
      $data[ 'Smessage' ]                     = '';
      $data[ 'header' ][ 'title' ]            = 'Manage Jobs';
      $data[ 'header' ][ 'metakeyword' ]      = 'Manage Jobs';
      $data[ 'header' ][ 'metadescription' ]  = 'Manage Jobs';
      $data[ 'footer' ][ 'script' ]           = '';
      $data[ 'content' ]                      = $content;
      $data[ 'breadcrumb' ]                   = '<li class="active">Manage Jobs</li>';
      $this->template( $data );
    }




    public function add()
    {
        $script     = '';
        $content    = $this->load->view( 'manage_jobs/add_pickup', '', true );
        $data[ 'navigation' ]                   = '';
        $data[ 'Emessage' ]                     = '';
        $data[ 'Smessage' ]                     = '';
        $data[ 'header' ][ 'title' ]            = 'Add Pickup job';
        $data[ 'header' ][ 'metakeyword' ]      = 'Add Pickup job';
        $data[ 'header' ][ 'metadescription' ]  = 'Add Pickup job';
        $data[ 'footer' ][ 'script' ]           = $script;
        $data[ 'content' ]                      = $content;
        $data[ 'breadcrumb' ]                   = '
                                              <li><a href="'.$this->config->item( 'admin_url' ).'manage_jobs">Manage jobs</a></li>
                                              <li class="active">Add Pickup job</li>';
        $this->template( $data );
    }


    function deleteOrderGoods(){
        $goods_id  = $this->input->post( 'goods_id' );
        $delete = $this->Deliverymodel->deleteOrderGoods( $goods_id );
    }
    function deleteorders(){
        $do_id  = $this->input->post( 'do_id' );
        $delete = $this->Deliverymodel->deleteorders( $do_id );
        if($delete){
            $this->session->set_flashdata('successMSG', 'Order info deleted successfully.');
        }else{
            $this->session->set_flashdata('errorMSG', 'Operation failed.');
        }
    }

    function selectCustomer(){

      $id           = $this->input->post( 'job_id' );
      $customer_id  = $this->input->post( 'customer_id' );

      $data         = array('customer_id' => $customer_id);
      echo $this->Deliverymodel->updateDeliverorder($data, $id);
    }



    public function orderlist(){

        $job_id = $this->uri->segment(3);

        if(!count($job_id)){ redirect( $this->config->item( 'admin_url' ) . 'manage_jobs' ); }
        $jobDoPickupList     = $this->Deliverymodel->jobDoPickupList($job_id);
        $data1[ 'jobDoPickupList' ]               = $jobDoPickupList;

        $script     = '';
        $content    = $this->load->view( 'manage_jobs/jobs_orderlist', $data1, true );
        $data[ 'navigation' ]                   = '';
        $data[ 'Emessage' ]                     = '';
        $data[ 'Smessage' ]                     = '';
        $data[ 'header' ][ 'title' ]            = 'job Do Pickup list';
        $data[ 'header' ][ 'metakeyword' ]      = 'job Do Pickup list';
        $data[ 'header' ][ 'metadescription' ]  = 'job Do Pickup list';
        $data[ 'footer' ][ 'script' ]           = $script;
        $data[ 'content' ]                      = $content;
        $data[ 'breadcrumb' ]                   = '
                                              <li><a href="'.$this->config->item( 'admin_url' ).'job_list">Jobs Lists</a></li>
                                              <li class="active">job Do Pickup list</li>';
        $this->template( $data );
    }
    public function order_goods_list(){

      $do_id = $this->uri->segment(3);

      if(!count($do_id)){ redirect( $this->config->item( 'admin_url' ) . 'job_list' ); }
      $doGoodsList     = $this->Deliverymodel->doGoodsList($do_id);
      $data1[ 'doGoodsList' ]                = $doGoodsList;

      $script     = '';
      $content    = $this->load->view( 'manage_jobs/order_goods_list', $data1, true );
      $data[ 'navigation' ]                   = '';
      $data[ 'Emessage' ]                     = '';
      $data[ 'Smessage' ]                     = '';
      $data[ 'header' ][ 'title' ]            = 'Do Goods list';
      $data[ 'header' ][ 'metakeyword' ]      = 'Do Goods list';
      $data[ 'header' ][ 'metadescription' ]  = 'Do Goods list';
      $data[ 'footer' ][ 'script' ]           = $script;
      $data[ 'content' ]                      = $content;
      $data[ 'breadcrumb' ]                   = '
                                              <li><a href="'.$this->config->item( 'admin_url' ).'job_list">Jobs lists</a></li>
                                              <li class="active">Do Goods list</li>';
      $this->template( $data );
  }
  public function order_customer()
    {
        $do_id        = $this->uri->segment(3);
        $orderInfo    = $this->Deliverymodel->orderInfoByDoid($do_id);
        $goodsLists   = $this->Deliverymodel->doGoodsList($do_id);
        $customerList = $this->Customermodel->customerLists();
        if(!$do_id){ redirect( $this->config->item( 'admin_url' ) . 'manage_jobs' ); }
        $data1[ 'goodsLists' ]                  = $goodsLists;
        $data1[ 'orderInfo' ]                   = $orderInfo;
        $data1[ 'customerList' ]                = $customerList;
        $script     = '';
        $content    = $this->load->view( 'manage_jobs/order_customer', $data1, true );
        $data[ 'navigation' ]                   = '';
        $data[ 'Emessage' ]                     = '';
        $data[ 'Smessage' ]                     = '';
        $data[ 'header' ][ 'title' ]            = 'Select Customer';
        $data[ 'header' ][ 'metakeyword' ]      = 'Select Customer';
        $data[ 'header' ][ 'metadescription' ]  = 'Select Customer';
        $data[ 'footer' ][ 'script' ]           = $script;
        $data[ 'content' ]                      = $content;
        $data[ 'breadcrumb' ]                   = '
                                              <li><a href="'.$this->config->item( 'admin_url' ).'manage_jobs">Manage jobs</a></li>
                                              <li class="active">Select Customer</li>';
        $this->template( $data );
    }
    function deleteDO(){

      $do_ids = $this->input->post( 'do_ids' );
      if(count($do_ids) > 0){
        foreach ($do_ids as $id) {
          $deliveryorderInfo = $this->Deliverymodel->deliveryorderInfo($id);
          $do_id        = $deliveryorderInfo->do_id;
          echo $deleteGoods  = $this->Deliverymodel->deleteCheckDoGoods($do_id);
        }
        $deleteDO = $this->Deliverymodel->deleteCheckDO($do_ids);
        $this->session->set_flashdata('successMSG', 'DO Deleted successfully.');
        echo 1;
      }
    }
    function deleteClient(){

      $client_ids = $this->input->post( 'client_ids' );
      if(count($client_ids) > 0){
        foreach ($client_ids as $id) {
          $deliveryorderInfo = $this->Deliverymodel->deleteClientDOInfo($id);
        }
        $delete = $this->Customermodel->deleteCheckClient($client_ids);
        $this->session->set_flashdata('successMSG', 'Customer Deleted successfully.');
        echo 1;
      }
    }
    function revertDO(){
      $do_ids = $this->input->post( 'do_ids' );
      if(count($do_ids) > 0){
        foreach ($do_ids as $id) {
          $deliveryorderInfo = $this->Deliverymodel->deliveryorderInfo($id);
          $do_id        = $deliveryorderInfo->do_id;
          if($deliveryorderInfo->delivery_type){
            $data         = array('status' => 0);
            echo $this->Deliverymodel->updateDeliverorderByDOId($data, $do_id);
          }
        }
        $this->session->set_flashdata('successMSG', 'DO Status updated successfully.');
        echo 1;
      }
    }
    public function set_view_type(){
        $json = array();
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
          $status   = $this->input->post('status');
          $type     = $this->input->post('type');
          if($type == 1){
            $array  = array('view_init_job' => $status);
          }elseif ($type == 2) {
            $array  = array('view_asgn_job' => $status);
          }elseif ($type == 3) {
            $array  = array('view_comp_job' => $status);
          }
          $this->session->set_userdata($array);
        }
        echo json_encode($json);
    }
    public function getLatLong($address=FALSE){
      $address = 'NO.11 & 11A,PUSAT PERDAGANGAN SURI PUTERI,PERSIARAN JUBLI PERAK, SEKSYEN 20,40300 SHAH ALAM, SELANGOR.';
      $results = geocode($address);
    }
    function template( $data ){
      $this->load->view( 'common/templatecontent', $data );
    }
}
