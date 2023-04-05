<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product extends CI_Controller {
	
	function __construct(){ 
       parent::__construct(); 
       $this->load->library('encrypt'); 
       $this->load->model('product_model');
       $this->load->model('order_model');
       date_default_timezone_set("Asia/Bangkok");
  	} 

	public function index()
	{
		$key = 'blablabla';
		$idNumber = '010001';
		$id = $this->encrypt->encode($idNumber, $key);

		redirect('/product/detail_product/'.$id);
	}

	public function detail_product($id)
	{
		$key = 'blablabla';
		$data['pcode'] = $id;
		$id = $this->encrypt->decode($id , $key);

		$data['data'] = $this->product_model->getProduct($id);

		$datas = $this->order_model->getByQuery("
			SELECT * FROM cart a left join product b on a.pcode = b.pcode WHERE a.customer_id = 1	
		")->result();

		$data['jumlahOrder'] = count($datas);
		$data['cart'] = $datas;
		

		$this->load->view('product_detail', $data);
	}

	public function save($id)
    {
    	
    	
    	$price = $this->input->post('price');  
    	$qty = $this->input->post('qty');  
    	$ppn = 1900;
    	$discount = 1000;
    	$net = $qty * $price;  
        
        $total = $net - $discount + $ppn;

        $data = array(
            "pcode" => $this->input->post('pcode'),
            "qty" => $qty,
            "price" => $price,
            "customer_id" => 1,
            "subtotal" => $net
        );

        $this->db->insert('cart', $data);
    	
    	redirect('/product/detail_product/'.$id);
    }

    public function delete($id, $pcode)
    {
        $this->db->delete('cart', array("order_detail_id" => $id));
        
        redirect('/product/detail_product/'.$pcode);
    }

    public function delete_detail($id, $pcode)
    {
        $this->db->delete('cart', array("order_detail_id" => $id));
        
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function cart_detail($id)
    {
    	$customer_id = $this->encrypt->decode($id , 'blablabla');
		
		$data['datas'] = $this->order_model->getByQuery("
			SELECT * FROM cart a left join product b on a.pcode = b.pcode WHERE a.customer_id = ".$customer_id."
		")->result();

		$data['pcode'] = '010001';
		$data['customer_id'] = $id;

		$this->load->view('cart_detail', $data);    	
    }

    public function checkout($id, $pcode)
    {
    	$customer_id = $this->encrypt->decode($id , 'blablabla');
    	$pcode = $this->encrypt->decode($pcode , 'blablabla');

		$getCode = $this->order_model->getByQuery("
    			select * from order_header order by order_id desc limit 1
    		")->row();
		// echo $getCode->order_id . ' - '. ((int) substr($getCode->order_id, -5,5) + 1);
		// exit();
    	if(empty($getCode)){
    		$order_id = "INV/".date('m')."/".date('Y')."/00001";
    	}else{
    		$num = ((int) substr($getCode->order_id, -5,5) + 1);

    		if($num < 9){
	    		$order_id = "INV/".date('m')."/".date('Y')."/0000".$num;
    		}else if($num < 99){
	    		$order_id = "INV/".date('m')."/".date('Y')."/000".$num;
    		}else if($num < 999){
	    		$order_id = "INV/".date('m')."/".date('Y')."/00".$num;
    		}else if($num < 9999){
	    		$order_id = "INV/".date('m')."/".date('Y')."/0".$num;
    		}else if($num < 99999){
	    		$order_id = "INV/".date('m')."/".date('Y')."/".$num;
    		}
    	}

    	$dataCart = $this->order_model->getByQuery("
    			select * from cart where customer_id = ".$customer_id."
    	")->result();

    	$net = 0;
    	foreach ($dataCart as $key) {
			$data = array(
	            "pcode" => $key->pcode,
	            "order_id" => $order_id,
	            "qty" => $key->qty,
	            "price" => $key->price,
	            "subtotal" => ($key->price * $key->qty)
	        );

	        $this->db->insert('order_detail', $data);    	
    		
    		$net += $key->subtotal;
    	}

    	// inisert order_header
    	$discount = 1000;
    	$ppn = 1900;
    	$total = $net - $discount + $ppn;

    	$data = array(
            "order_id" => $order_id,
            "order_date" => date('Y-m-d'),
            "customer_id" => $customer_id,
            "promo_code" => 'pmo-001',
            "amount_discount" => 1000,
            "net" => $net,
            "ppn" => $ppn,
            "total" => $total,
        );

        $this->db->insert('order_header', $data);

        //delete from chart
        $this->db->delete('cart', array("customer_id" => $customer_id));

        echo "success checkout!!!";
    }
}
