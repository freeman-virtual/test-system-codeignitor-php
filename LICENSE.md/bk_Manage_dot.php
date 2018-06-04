<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_dot extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->helper('hv_helper.php');
        $this->load->model('Deliverymodel');
        $this->load->model('Customermodel');
        $this->load->model('Drivermodel');
        $this->load->model('api/push_notification_model');
        sessionset();
        //error_reporting(E_ALL);
    }



        function trash_do(){
            $id  = $this->input->post( 'id' );
            $delete     = $this->Deliverymodel->trash_do( $id );
            if($delete){
                $this->session->set_flashdata('successMSG', 'Selected DO has been removed. [ID:'.$id.']');
            }else{
                $this->session->set_flashdata('errorMSG', 'Operation failed.');
            }
        }






    public function ajaxsearch()
    {

       if(is_null($this->input->get('COMPANYNAME')))
        {

        $this->load->view('manage_dot');


        }
        else
        {

        $data['dotable']=$this->Deliverymodel->dotable($this->input->get('COMPANYNAME'));

        $this->load->view('manage_dot',$data);

        }


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

    function Confirmcreatejob(){
        $do_id_lists = $this->input->post('do_id');

        if(!count($do_id_lists)){ redirect( $this->config->item( 'admin_url' ) . 'manage_jobs' ); }
        $driverList  = $this->Drivermodel->driverLists();
        $vehicleList = $this->Drivermodel->vehicleLists();

        $user_id   = $this->session->userdata['USER_ID'];
        $adminInfo = $this->PODmodel->adminInfo($user_id);
        $latitude  = $adminInfo->latitude;
        $longitude = $adminInfo->longitude;

        if($do_id_lists){
          $i1 = 1;
          foreach ($do_id_lists as $value) {
            $doInfo = $this->Deliverymodel->deliveryorderInfo($value);

            $lat1    = $latitude;
            $long1   = $longitude;
            $lat2    = $doInfo->address_lat;
            $long2   = $doInfo->address_long;
            $aaa     = GetDrivingDistance1($lat1, $lat2, $long1, $long2);

            $mappostsarray[] = array(
                                    'orders'      =>$i1,
                                    'do_id'       =>'DO Id: '.$doInfo->do_id,
                                    'id'          => $value,
                                    'distance'    =>$aaa['distance'],
                                    'distance_val'=>$aaa['distance_val'],
                                    'duration'    =>$aaa['duration'],
                                    'duration_val'=>$aaa['duration_val'],
                                    'address_lat' =>$lat2,
                                    'address_long'=>$long2
                                    );
            $i1++;
          }
        }
        $mappoint = usort($mappostsarray, 'sortByOrder');
        $orderDisVal2  = 0;
        $previouslat2  = $latitude;
        $previouslong2 = $longitude;
        //BEST SEQ LOOP
        if($mappostsarray){
          $i2 = 1;
          /*$bestSequenceList[] = array(
                            'orders'      =>0,
                            'do_id'       =>'Warehouse',
                            'job_do_id'   =>'',
                            'distance'    => 0,
                            'distance_val'=> 0,
                            'duration'    => 0,
                            'duration_val'=> 0,
                            'address_lat' =>$latitude,
                            'address_long'=>$longitude
                            );*/
          $duration_val2  = 0;
          $lat22  = $latitude;
          $long22 = $longitude;

          foreach ($mappostsarray as $value) {

            $lat22   = $value['address_lat'];
            $long22  = $value['address_long'];
            $ccc     = GetDrivingDistance1($previouslat2, $lat22, $previouslong2, $long22);
            $duration_val2  = $ccc['duration_val'];
            $bestSequenceList[] = array(
                                        'orders'        =>'',
                                        'do_id'         =>$value['do_id'],
                                        'id'            =>$value['id'],
                                        'distance'      =>$ccc['distance'],
                                        'distance_val'  =>$ccc['distance_val'],
                                        'duration'      =>$ccc['duration'],
                                        'duration_val'  =>$duration_val2,
                                        'address_lat'   =>$lat22,
                                        'address_long'  =>$long22
                                        );

            $orderDisVal2  += $duration_val2;
            $previouslat2  = $lat22;
            $previouslong2 = $long22;
            $i2++;
          }
        }
        $besteta  = '';
        $hours2   = floor($orderDisVal2 / 3600);
        $mins2    = floor($orderDisVal2 / 60 % 60);
        $secs2    = floor($orderDisVal2 % 60);
        if($hours2){
          if($hours2 == 1){
            $besteta .= $hours2.' Hr';
          }else{
            $besteta .= $hours2.' Hrs';
          }
        }
        if($secs2 > 30){
          $mins2 = $mins2 + 1;
        }
        if($mins2){
          if($mins2 == 1){
            $besteta .= ' '.$mins2.' Min';
          }else{
            $besteta .= ' '.$mins2.' Mins';
          }
        }
        $data1[ 'bestSequenceList' ]            = $bestSequenceList;
        $data1[ 'besteta' ]                     = trim($besteta);

        $data1[ 'do_id_lists' ]               = $do_id_lists;
        $data1[ 'driverList' ]                = $driverList;
        $data1[ 'vehicleList' ]               = $vehicleList;
        $content    = $this->load->view( 'manage_jobs/create_job', $data1, true );
        $data[ 'navigation' ]                   = '';
        $data[ 'Emessage' ]                     = '';
        $data[ 'Smessage' ]                     = '';
        $data[ 'header' ][ 'title' ]            = 'Create Jobs';
        $data[ 'header' ][ 'metakeyword' ]      = 'Create Jobs';
        $data[ 'header' ][ 'metadescription' ]  = 'Create Jobs';
        $data[ 'footer' ][ 'script' ]           = $script;
        $data[ 'content' ]                      = $content;
        $data[ 'breadcrumb' ]                   = '
                                              <li><a href="'.$this->config->item( 'admin_url' ).'manage_jobs">Manage jobs</a></li>
                                              <li class="active">Edit Order</li>';
        $this->template( $data );
    }

    function createJobValidate(){

        $this->form_validation->set_rules( 'driver_id', 'Driver name', 'required' );
        $this->form_validation->set_rules('vehicle_id', 'Vehicle name', 'required');
        $this->form_validation->set_rules('do_ids', 'DO list', 'required');
        if( $this->form_validation->run() == FALSE ) {
            echo validation_errors();
        } else {
            $vehicle_id     = $this->input->post( 'vehicle_id' );
            $driver_id      = $this->input->post( 'driver_id' );
            $do_ids         = $this->input->post( 'do_ids' );

            $data = array(
                          'vehicle_id'  => $vehicle_id,
                          'driver_id'   => $driver_id,
                          'createdon'   => date("Y-m-d H:i:s")
                          );
            $job_id = $this->Deliverymodel->addJob($data);

            $do_id_Array = explode(",",$do_ids);
            if($job_id){
              if($do_id_Array){
                $order = 1;
                foreach ($do_id_Array as $id) {
                  $deliveryorderInfo = $this->Deliverymodel->deliveryorderInfo($id);
                  //add log
                  $logData = array('meta_id' => $deliveryorderInfo->do_id, 'log_type' => 'ASSIGN' );
                  $this->PODmodel->addLog($logData);

                  $data         = array('status' => 3, 'stage' => 2);
                  $this->Deliverymodel->updateDeliverorder($data, $id);
                  $data2 = array(
                              'job_id'    => $job_id,
                              'do_po_id'  => $id,
                              'orders'    => $order
                              );
                  $this->Deliverymodel->addJobDoPickup($data2);
                  $doInfo = $this->Deliverymodel->deliveryorderInfo($id);
                  $do_id  = $doInfo->do_id;
                  if($do_id){
                    $data3 = array('job_id' => $job_id);
                    $this->Deliverymodel->updateDoGoodsList($data3, $do_id);
                  }
                 $order++;
                }
              }
              $user_type = 1;
              $userDeviceTokenList  = $this->Deliverymodel->userDeviceTokenList( $driver_id, $user_type );
              //PUSH NOTIFICATION
              if($userDeviceTokenList){
                $message   = "You have a New Task.";
                $push_data = array( 'title'     => 'CHIN LAI',
                                    'message'   => $message
                                  );
                $device_tokenA = array();
                $device_tokenI = array();
                foreach ($userDeviceTokenList as $tokenList) {
                  $device_token   = trim($tokenList->device_token);
                  $device_type    = trim($tokenList->device_type);
                  if (strtoupper($device_type) == 'ANDROID') {
                    if (strlen($device_token) > 10){
                      $device_tokenA[] = $device_token;
                    }
                  } elseif (strtoupper($device_type) == 'IOS') {
                    if (strlen($device_token) > 10){
                      $device_tokenI[] = $device_token;
                    }
                  }
                }
                if (!empty($device_tokenA)) {
                  $this->push_notification_model->android_push_notification($device_tokenA, $push_data);
                }
                if (!empty($device_tokenI)) {
                  $this->push_notification_model->ios_push_notification($device_tokenI, $message);
                }
              }
              $this->session->set_flashdata('successMSG', 'Job Created successfully.');
              echo 1;
            }
        }
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
    public function set_view_type(){
        $json = array();
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
          $status   = $this->input->post('status');
          $array  = array('view_do_job' => $status);
          $this->session->set_userdata($array);
        }
        echo json_encode($json);
    }
    function changeDeliveryType(){

      $delivery_type  = $this->input->post( 'delivery_type' );
      $do_id          = $this->input->post( 'do_id' );

      $data         = array('delivery_type' => $delivery_type);
      echo $this->Deliverymodel->updateDeliverorderByDOId($data, $do_id);
    }
    function initiateDo(){
      $do_ids = $this->input->post( 'do_ids' );
      if(count($do_ids) > 0){
        foreach ($do_ids as $id) {
          $deliveryorderInfo = $this->Deliverymodel->deliveryorderInfo($id);
          $do_id        = $deliveryorderInfo->do_id;
          if($deliveryorderInfo->delivery_type){
            $data         = array('status' => 2);
            echo $this->Deliverymodel->updateDeliverorderByDOId($data, $do_id);
            //add log
            $logData = array('meta_id' => $do_id, 'log_type' => 'INITIATE' );
            $this->PODmodel->addLog($logData);
          }
        }
        $this->session->set_flashdata('successMSG', 'DO Initiate successfully.');
        echo 1;
      }
    }
    function template( $data ){
      $this->load->view( 'common/templatecontent', $data );
    }
    function deleteimage(){

        $delete = unlink(FCPATH.'uploads/scan_do/'.$deliveryInfo->do_id.'/');
        if($delete){
            $this->session->set_flashdata('successMSG', 'Image deleted successfully.');
        }else{
            $this->session->set_flashdata('errorMSG', 'Operation failed.');
        }
    }
}
