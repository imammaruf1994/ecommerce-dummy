<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_model extends CI_Model {

  function getProduct($id){
 
    $this->db->select('*', FALSE);
    $this->db->from('product');
    $this->db->where('pcode', $id);
    $response = $this->db->get()->row();

    return $response;
  }

}