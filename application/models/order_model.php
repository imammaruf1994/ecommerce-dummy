<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Order_model extends CI_Model {

  function getByQuery($sql){
 
    // $this->db->select('*', FALSE);
    // $this->db->from('order_header a');
    // $this->db->join('order_detail b', 'a.order_id = b.order_id');
    // $this->db->where('a.customer_id', $idCustomer);
    // $response = $this->db->get();

    $query = $this->db->query($sql);
    return $query;
  }

}