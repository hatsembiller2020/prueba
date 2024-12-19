<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sales extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->lang->admin_load('sales', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('sales_model');
        $this->load->admin_model('pos_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
        $this->load->library('attachments', [
            'path'     => $this->digital_upload_path,
            'types'    => $this->digital_file_types,
            'max_size' => $this->allowed_file_size,
        ]);
    }




public function actualizarsaldos(){


$ruta =  getcwd();
require $ruta."/app/config/database.php";

$customer_id = $this->input->get('idusuario');


// averiguar si el usario tiene grupo de precios 


if($customer_id ==0)
{
 $sql_saldos = " SELECT *,(s.grand_total-s.paid) as saldodeudor from sma_sales s WHERE (s.payment_status='pending' or s.payment_status='due') ORDER BY s.date asc" ; 

} 
else
{
 $sql_saldos = " SELECT *,(s.grand_total-s.paid) as saldodeudor from sma_sales s WHERE s.customer_id=".$customer_id." AND (s.payment_status='pending' or s.payment_status='due') ORDER BY s.date asc" ; 


}


//echo $sql_saldos."<br>" ; 




// a) averiguo las ventas a saldar 
 $resultado1p33 = $conn->query($sql_saldos);
                 while($tipodocp33= $resultado1p33->fetch_assoc() )
                    { // (1) 
                         
                          $idventa = $tipodocp33['id'];
                          $customer_id_tabla = $tipodocp33["customer_id"];


// b) averigo los productos que tiene esa venta 


                          $sql_productos  = "SELECT * FROM sma_sale_items s WHERE s.sale_id=".$idventa." AND s.product_type ='standard'";
                          // echo $sql_productos."<br><br>";



                           $resultado_productos = $conn->query($sql_productos);
                             while($produc= $resultado_productos->fetch_assoc() )
                                { // (2) 


                                  $idproducto = $produc["product_id"];
                                   $tax_rate_id = $produc["tax_rate_id"];
                                   $unit_quantity = $produc["unit_quantity"];
                                   



                                   if($tax_rate_id == 5) { $impuesto = 0.21 ; }
                                   if($tax_rate_id == 6) { $impuesto = 0.105 ; }



                                    if($customer_id ==0)
                                    { 

                                      // hay que hacer de toda la tabla 

                                         $sql_usuario_grupos ="select COUNT(c.price_group_id) AS cantidad,c.price_group_id from sma_companies c where c.id = ".$customer_id_tabla;
                                    }
                                    else
                                    {

                                         //   hay que averiguar  de ese usuario en su grupo de precio
                                          $sql_usuario_grupos ="select COUNT(c.price_group_id) AS cantidad,c.price_group_id from sma_companies c where c.id = ".$customer_id;

                                    }

                        //   echo $sql_usuario_grupos."<br><br>";

                                     $resultado_usuarios_grupos = $conn->query($sql_usuario_grupos);
                                   while($usugru= $resultado_usuarios_grupos->fetch_assoc() )
                                      { 
                                         $price_group_id = $usugru["price_group_id"];
                                         $cuenta_grupos = $usugru["cantidad"];
                               
                                      } 

                                 if($cuenta_grupos > 0)
                                            {
                                                // el usuario si tiene grupo de precios

                                                  $sql_nuevos_precios ="SELECT * FROM sma_product_prices p WHERE p.product_id =".$idproducto."  AND p.price_group_id=".$price_group_id;
                                                    



                                            }

                                            else 
                                            {
                                            //   el usuario no tien egrupo de precios

                                                 $sql_nuevos_precios ="SELECT * FROM sma_products p where p.id=".$idproducto;
                                                                  


                                            }


    // echo $sql_nuevos_precios."<br><br>";
                                             $resultado_nuevos_precios = $conn->query($sql_nuevos_precios);
                                               while($nuevogrupop= $resultado_nuevos_precios->fetch_assoc() )
                                                       { // (3) 
                                                            $price_product = $nuevogrupop["price"];
                                                         } //  (3)



// d) ahora actualizar en sale_item el nuevo precio del producto 

                                $impuestodelproducto = $price_product * $impuesto ; 
                                $restoimpuestodelproducto = $price_product - $impuestodelproducto ; 

                                $subtotal = $price_product * $unit_quantity ; 

                                $sql_update_sale_items  =" update sma_sale_items s set s.net_unit_price=".$restoimpuestodelproducto.", s.unit_price=".$price_product." , s.item_tax= ".$impuestodelproducto.",s.subtotal=".$subtotal.", s.real_unit_price=".$price_product."  where s.sale_id=".$idventa."  and s.product_id = ".$idproducto ;

                                  $update_tmp=mysqli_query($conn, $sql_update_sale_items);

 //echo $sql_update_sale_items."<br><br>";

                                } //  (2)


// e) ahora sumar el nuevo monto de la venta y actualizarlo en la venta original 

                                    $sql_saldorestante = "select sum(s.subtotal) as saldo1, SUM(s.item_tax) AS impuestos   from sma_sale_items s where s.sale_id=".$idventa ; 
                                    $resultado_saldorestante = $conn->query($sql_saldorestante);
                                         while($rest= $resultado_saldorestante->fetch_assoc() )
                                            { // (4) 


                                            
                                              $saldo_restante = $rest["saldo1"];
                                              $impuestos = $rest["impuestos"];

                                            } //  (4)
// echo $sql_saldorestante."<br><br>";

// f) actualizo esa venta 


                                    $sql_actualiza_venta = "update sma_sales s set s.grand_total = ".$saldo_restante.", s.product_tax=".$impuestos.",s.total_tax=".$impuestos." , s.total =".($saldo_restante - $impuestos)."  where s.id = ".$idventa;

                                    $update_tmp3=mysqli_query($conn, $sql_actualiza_venta);


 //echo $sql_actualiza_venta."<br><br>";



                    } //  (1)



echo "1" ; 



}







public function actualizarsaldopresupuesto(){


$ruta =  getcwd();
require $ruta."/app/config/database.php";

$customer_id = $this->input->get('idusuario');


// averiguar si el usario tiene grupo de precios 



if($customer_id ==0)
{

 $sql_saldos = "  SELECT *  from sma_quotes s   WHERE  (s.status='pending' ) ORDER BY s.date asc" ; 

} 
else
{

 $sql_saldos = "  SELECT *  from sma_quotes s   WHERE s.customer_id=".$customer_id."   AND (s.status='pending' ) ORDER BY s.date asc" ; 

}




//echo $sql_saldos."<br>" ; 




// a) averiguo las ventas a saldar 
 $resultado1p33 = $conn->query($sql_saldos);
                 while($tipodocp33= $resultado1p33->fetch_assoc() )
                    { // (1) 
                         
                          $idquote = $tipodocp33['id'];
                          $customer_id_tabla = $tipodocp33["customer_id"];


// b) averigo los productos que tiene esa venta 


                          $sql_productos  = "SELECT * FROM sma_quote_items s WHERE s.quote_id=".$idquote." AND s.product_type ='standard'";
                          // echo $sql_productos."<br><br>";



                           $resultado_productos = $conn->query($sql_productos);
                             while($produc= $resultado_productos->fetch_assoc() )
                                { // (2) 


                                  $idproducto = $produc["product_id"];
                                   $tax_rate_id = $produc["tax_rate_id"];
                                   $unit_quantity = $produc["unit_quantity"];
                                   



                                   if($tax_rate_id == 5) { $impuesto = 0.21 ; }
                                   if($tax_rate_id == 6) { $impuesto = 0.105 ; }



                                    if($customer_id ==0)
                                    { 

                                      // hay que hacer de toda la tabla 

                                         $sql_usuario_grupos ="select COUNT(c.price_group_id) AS cantidad,c.price_group_id from sma_companies c where c.id = ".$customer_id_tabla;
                                    }
                                    else
                                    {

                                         //   hay que averiguar  de ese usuario en su grupo de precio
                                          $sql_usuario_grupos ="select COUNT(c.price_group_id) AS cantidad,c.price_group_id from sma_companies c where c.id = ".$customer_id;

                                    }

                        //   echo $sql_usuario_grupos."<br><br>";

                                     $resultado_usuarios_grupos = $conn->query($sql_usuario_grupos);
                                   while($usugru= $resultado_usuarios_grupos->fetch_assoc() )
                                      { 
                                         $price_group_id = $usugru["price_group_id"];
                                         $cuenta_grupos = $usugru["cantidad"];
                               
                                      } 

                                 if($cuenta_grupos > 0)
                                            {
                                                // el usuario si tiene grupo de precios

                                                  $sql_nuevos_precios ="SELECT * FROM sma_product_prices p WHERE p.product_id =".$idproducto."  AND p.price_group_id=".$price_group_id;

                                            }

                                            else 
                                            {
                                            //   el usuario no tien egrupo de precios

                                                 $sql_nuevos_precios ="SELECT * FROM sma_products p where p.id=".$idproducto;
                                                                  
                                            }


    // echo $sql_nuevos_precios."<br><br>";
                                             $resultado_nuevos_precios = $conn->query($sql_nuevos_precios);
                                               while($nuevogrupop= $resultado_nuevos_precios->fetch_assoc() )
                                                       { // (3) 
                                                            $price_product = $nuevogrupop["price"];
                                                         } //  (3)



// d) ahora actualizar en sale_item el nuevo precio del producto 

                                $impuestodelproducto = $price_product * $impuesto ; 
                                $restoimpuestodelproducto = $price_product - $impuestodelproducto ; 

                                $subtotal = $price_product * $unit_quantity ; 

                                $sql_update_sale_items  =" update sma_quote_items s set s.net_unit_price=".$restoimpuestodelproducto.", s.unit_price=".$price_product." , s.item_tax= ".$impuestodelproducto.",s.subtotal=".$subtotal.", s.real_unit_price=".$price_product."  where s.quote_id=".$idquote."  and s.product_id = ".$idproducto ;

                                  $update_tmp=mysqli_query($conn, $sql_update_sale_items);

 //echo $sql_update_sale_items."<br><br>";

                                } //  (2)


// e) ahora sumar el nuevo monto de la venta y actualizarlo en la venta original 

                                    $sql_saldorestante = "select sum(s.subtotal) as saldo1, SUM(s.item_tax) AS impuestos   from sma_quote_items s where s.quote_id=".$idquote ; 
                                    $resultado_saldorestante = $conn->query($sql_saldorestante);
                                         while($rest= $resultado_saldorestante->fetch_assoc() )
                                            { // (4) 


                                            
                                              $saldo_restante = $rest["saldo1"];
                                              $impuestos = $rest["impuestos"];

                                            } //  (4)
// echo $sql_saldorestante."<br><br>";

// f) actualizo esa venta 


                                    $sql_actualiza_venta = "update sma_quotes s set s.grand_total = ".$saldo_restante.", s.product_tax=".$impuestos.",s.total_tax=".$impuestos." , s.total =".($saldo_restante - $impuestos)."  where s.id = ".$idquote;

                                    $update_tmp3=mysqli_query($conn, $sql_actualiza_venta);


 //echo $sql_actualiza_venta."<br><br>";



                    } //  (1)



echo "1" ; 



}







public function pagarctacte(){

//date_default_timezone_set("America/Argentina/Buenos_Aires");
$ruta =  getcwd();
require $ruta."/app/config/database.php";

$customer_id = $this->input->get('idusuario');
$monto = $this->input->get('mimonto');
$fecha = $this->input->get('fecha');
$tipopago = $this->input->get('tipopago');
$montoinicial = $monto ; 

$sqlPP33 = "  SELECT *,(s.grand_total-s.paid) as saldodeudor from sma_sales s WHERE s.customer_id=".$customer_id." AND (s.payment_status='pending' or s.payment_status='due') ORDER BY s.date asc";

//$date = date('Y-m-d H:i:s');
$horaActual = date("h:i:s");





$resultado1p33 = $conn->query($sqlPP33);
                 while($tipodocp33= $resultado1p33->fetch_assoc() )
                    { 
                          $grand_total = $tipodocp33['grand_total'];
                          $id = $tipodocp33['id'];
                          $saldodeudor = $tipodocp33['saldodeudor'];

                   
  if($monto>=$saldodeudor) 
                         { 

                            //// eje la factura es de 300 y di 300 debo empezar por la ultima

                            // a) insertar pago en tabla
                            // b) actualizar tabla sale

                                 $payment = [
                                'date'         => $fecha." ".$horaActual,
                                'sale_id'      => $id,
                                'reference_no' => $this->site->getReference('pay'),
                                'amount'       =>  $saldodeudor,
                                 'paid_by'      => $tipopago,
                                'cheque_no'    => '',
                                'cc_no'        => '',
                                'cc_holder'    => '',
                                'cc_month'     => '',
                                'cc_year'      => '',
                                'cc_type'      => '',
                                'note'         => '',
                                'recargototal'   => 0,
                                'recargotarjeta'   => 0,
                                'created_by'   => $this->session->userdata('user_id'),
                                'type'         =>'received',
                            ];


                          $this->sales_model->addPayment($payment, $customer_id);

                          // poner la venta como pagada 

                          $monto -= $saldodeudor;


                           $ruta =  getcwd();
                          require $ruta."/app/config/database.php";
                           $sql_ultimoid = "SELECT MAX(s.id) AS id FROM sma_payments s";
                             $resultado = $conn->query($sql_ultimoid);
                               while($sigo= $resultado->fetch_assoc() )
                               { $ultimoid=$sigo['id']; } 

                             $sql_update ="update sma_payments s set s.recargototal = ".$montoinicial.",pos_paid=".$monto." where s.id=".$ultimoid ;
                               $update_tmp=mysqli_query($conn, $sql_update);



                          // poner la venta como pagada 

$sql_update_venta ="update sma_sales s set s.payment_status = 'paid' where s.id=".$ultimoid ;
$update_tmp_venta=mysqli_query($conn, $sql_update_venta);







                         }


                        else  
                         {
                            // eje la factura es de 300 y solo di 55 debo dejar el saldo de esa factura , eje 245 de saldo

                              $payment = [
                                'date'         => $fecha." ".$horaActual,
                                'sale_id'      => $id,
                                'reference_no' => $this->site->getReference('pay'),
                                'amount'       =>  $monto,
                                'paid_by'      => $tipopago,
                                'cheque_no'    => '',
                                'cc_no'        => '',
                                'cc_holder'    => '',
                                'cc_month'     => '',
                                'cc_year'      => '',
                                'cc_type'      => '',
                                'note'         => '',
                                'recargototal'   => 0,
                                'recargotarjeta'   => 0,
                                'created_by'   => $this->session->userdata('user_id'),
                                'type'         =>'received',
                            ];






                            $this->sales_model->addPayment($payment, $customer_id);
                            $ruta =  getcwd();
                          require $ruta."/app/config/database.php";
                           $sql_ultimoid = "SELECT MAX(s.id) AS id FROM sma_payments s";
                             $resultado = $conn->query($sql_ultimoid);
                               while($sigo= $resultado->fetch_assoc() )
                               { $ultimoid=$sigo['id']; } 

                             $sql_update ="update sma_payments s set s.recargototal = ".$montoinicial.",pos_paid=".$monto." where s.id=".$ultimoid ;
                               $update_tmp=mysqli_query($conn, $sql_update);



                                $monto = 0;


                        break;





                         }

                        




                    }

                  echo  $ultimoid ;


                   



}






        public function facturab()
    {
         $ruta =  getcwd();
          require $ruta."/app/config/database.php";
           $sql_montofacturaB = "SELECT s.version FROM sma_settings s" ; 
            $resultado_montofacturaB = $conn->query($sql_montofacturaB);
                 while($montoB= $resultado_montofacturaB->fetch_assoc() )
                    { 
                         $montoversion = $montoB['version'];

                        
                    }

                     echo $montoversion ;

     }



   public function coeficientes()
    {

  
        
    // $tarjeta = $this->input->get('tarjeta');

         $ruta =  getcwd();
            require $ruta."/app/config/database.php";

            // averiguo que soy yo 


     $idcuota = $this->input->get('idcuota');
     $monto = $this->input->get('monto');



            $sqlPP = "SELECT ct.id AS idcuota,p.title,ct.porcentaje_recargo,ct.cuota  FROM sma_printers2 p LEFT JOIN sma_cuotas_tarjeta ct ON ct.code=p.title WHERE ct.porcentaje_recargo  IS NOT NULL AND ct.id=".$idcuota;



            $resultado1p = $conn->query($sqlPP);
                 while($tipodocp= $resultado1p->fetch_assoc() )
                    { 
                         $porcentaje_recargo = $tipodocp['porcentaje_recargo'];
                    }




     $total = ( ($porcentaje_recargo/100) * $monto ) ;
     echo $total;
     
    }





    
    /* ------------------------------------------------------------------ */

     public function buscacustomer()
    {

/*

M 13 a M 13 =  C 
M 13 a RI 1   =C
M 13 a CF 5  = C 

RI 1 a M 13  = A
RI 1 a RI 1  = A 
RI 1 a CF 5  = B
RI 1 a RM 6  = A 


RM 6 a RM 6 = C
RM 6 a RI 1  =C
RM a 6 CF 5   =C


RM 6 a M 13 = C




M = monotributista
RI = Responsable Inscripto
CF = Consumidor Final
RM = Responsable Monotributo

*/



          $idcustomer = $this->input->get('idcustomer');
          $tiporesponsable = $this->input->get('tiporesponsable');
          
            $ruta =  getcwd();
            require $ruta."/app/config/database.php";

            // averiguo que soy yo 



            $sqlPP = "SELECT p.cf4  FROM sma_companies p WHERE p.id=".$idcustomer;
            $resultado1p = $conn->query($sqlPP);
                 while($tipodocp= $resultado1p->fetch_assoc() )
                    { 
                         $cf4 = $tipodocp['cf4'];
                    }



                   if(($tiporesponsable==13) && ($cf4==13) )

                    {
                            echo 3;
                    }

                   else  if(($tiporesponsable==13) && ($cf4==1) )

                    {
                            echo 3;
                    }


                   else if(($tiporesponsable==13) && ($cf4==5) )

                    {
                            echo 3;
                    }


                  else if(($tiporesponsable==1) && ($cf4==13) )

                    {
                            echo 1;
                    }

                   else if(($tiporesponsable==1) && ($cf4==1) )

                    {
                            echo 1;
                    }



          


                    else if(($tiporesponsable==1) && ($cf4==5) )

                    {
                            echo 2;
                    }

                  else if(($tiporesponsable==6) && ($cf4=6) )

                    {
                            echo 3;
                    }

                     else if(($tiporesponsable==6) && ($cf4==1) )

                    {
                            echo 3;
                    }

                      else if(($tiporesponsable==1) && ($cf4==6) )

                    {
                            echo 1;
                    }



                       else if(($tiporesponsable==6) && ($cf4==13) )

                    {
                            echo 3;
                    }


                     else if(($tiporesponsable==6) && ($cf4==5) )

                    {
                            echo 3;
                    }






                    else {
                        echo 0 ;
                    }









                    
    }




    public function add($quote_id = null)
    {


 if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) 

 {
       $register_data = ['register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date];
            $this->session->set_userdata($register_data);
 } else 

 {
  
             $this->session->set_flashdata('error', lang('register_not_open'));
            admin_redirect('pos/open_register');
 }






         $ruta =  getcwd();
                                                require $ruta."/app/config/database.php";

                                                $sql_vendedor_post = "SELECT c.id ,c.name,c.cf4 FROM sma_companies c WHERE  c.group_name='biller'";
                   

                                                   $resultado_post = $conn->query($sql_vendedor_post);
                                                   while($vp= $resultado_post->fetch_assoc() )
                                                      {
                                                          
                                                        
                                                            $biller_tiporesponsable =trim($vp['cf4']);
                                                      }
                                                            




        $this->sma->checkPermissions();
        $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : null;
        $duplicate = $this->input->get('duplicate') ? $this->input->get('duplicate') : null;

       // $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');

        if ($this->form_validation->run() == true) {

      
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

             $amount_paid = $this->sma->formatDecimal($this->input->post('amount-paid'));


            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_method   = $this->input->post('payment_method');
            $payment_term     = $this->input->post('payment_term');
            $nuevocuit        = $this->input->post('nuevocuit');


                                                



                if($payment_method =="")
                    {
                        $this->session->set_flashdata('error', lang('Debe elegir Tipo de factura'));
                          admin_redirect('sales/add');
                    }


            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->sma->clear_tags($this->input->post('note'));
            $staff_note       = $this->sma->clear_tags($this->input->post('staff_note'));
            $quote_id         = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $digital          = false;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = $_POST['serial'][$r]           ?? '';
                $item_tax_rate      = $_POST['product_tax'][$r]      ?? null;
                $item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    if ($item_type == 'digital') {
                        $digital = true;
                    }
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->sma->formatDecimal($unit_price - $pr_discount);



                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);

                             if($biller_tiporesponsable==1)
                                        {
                                            // responsable inscripto
                                           

                                             //  $tax         = $ctax['tax'];
                                                $iva         = $ctax['iva'];
                                                $neto         = $ctax['neto'];
                                                $item_tax = $iva;




                                        }
                                        else
                                        {
                                                
                                              $item_tax    = $this->sma->formatDecimal($ctax['amount']);
                                               $tax         = $ctax['tax'];;

                                            
                                          

                                        }




                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {

                                     // averiguo si es responsable par asaca rel iva o no 

                                        if($biller_tiporesponsable==1)
                                        {
                                            // responsable inscripto
                                              $item_net_price = $neto ;
                                        }
                                        else
                                        {
                                                

                                              $item_net_price = $unit_price - $item_tax;

                                        }



                      


                        }
                        //$pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);

                        $pr_item_tax = ($item_tax * $item_unit_quantity);



                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->sma->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->sma->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'real_unit_price'   => $real_unit_price,
                    ];

                    $products[] = ($product + $gst_data);
                    $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax), true);
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            // $grand_total    = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);

             

           $recargo_tarjeta  =   $this->input->post('cc_year') ;


           
           if($recargo_tarjeta >= 0 )
           {
              

               
                     $ivarecargotarjeta = round($recargo_tarjeta * 0.173554,2)  ;  // 2,1
                     $recargotarjetasiniva = $recargo_tarjeta - round($ivarecargotarjeta,2) ; // 10 - 2,1 = 7,90



           }
           else
           {
                $recargo_tarjeta  = 0 ;

           }
       

/*si el vendedor es resp insc. ventas a exentos debe ser factura B

si el vendedor es resp insc. ventas a monotributistas debe ser factura A
*/

  $ruta =  getcwd();
require $ruta."/app/config/database.php";
                          


                           $sql_tiporesponsablecuatomer = "SELECT * FROM sma_companies c WHERE c.id=".$customer_id;
                             $resultado_responsablecustomer = $conn->query($sql_tiporesponsablecuatomer);
                               while($respc= $resultado_responsablecustomer->fetch_assoc() )
                               { $tiporesponsablecustomer_id=$respc['cf4']; } 



                           $sql_tiporesponsablebiller = "SELECT * FROM sma_companies c WHERE c.id=".$biller_id;
                             $resultado_responsablebiller = $conn->query($sql_tiporesponsablebiller);
                               while($respb= $resultado_responsablebiller->fetch_assoc() )
                               { $tiporesponsablebiller_id=$respb['cf4']; } 


if (($tiporesponsablebiller_id==1) && ($tiporesponsablecustomer_id==4) )
{
    $metodo = 6;
}

else 

if (($tiporesponsablebiller_id==1) && ($tiporesponsablecustomer_id==13) )
{
    $metodo = 1;
}


else
{
    $metodo = $payment_method ; 
}



            $data        = ['date'  => $date,
                'reference_no'      => $reference,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $total,
                'recargo_tarjeta'   =>  $recargo_tarjeta,
                'product_discount'  => $product_discount,
                'manual_payment'    => $nuevocuit,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->sma->formatDecimal($shipping),
                'grand_total'       => ($grand_total + $recargo_tarjeta),
                'total_items'       => $total_items,
                'sale_status'       => $sale_status,
                'payment_method'      => $metodo,
                'payment_status'    => $payment_status,
                'payment_term'      => $payment_term,
                'due_date'          => $due_date,
                'paid'              => 0,
                'created_by'        => $this->session->userdata('user_id'),
                'hash'              => hash('sha256', microtime() . mt_rand()),
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            if ($payment_status == 'partial' || $payment_status == 'paid') {
                if ($this->input->post('paid_by') == 'deposit') {
                    if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                        $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
                if ($this->input->post('paid_by') == 'gift_card') {
                    $gc            = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance    = $gc->balance - $amount_paying;
                    $payment       = [
                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->sma->formatDecimal($amount_paying),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('gift_card_no'),
                        'cc_holder'    => $this->input->post('pcc_holder'),
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('pcc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                        'type'         => 'received',
                        'gc_balance'   => $gc_balance,
                    ];


                } else {
                 

                 $comopago = $this->input->post('paid_by');
                 if($comopago=="CC")
                 {
                    $holder ="";
                 }
                 else
                 {
                    $holder = "efectivo";
                 }

                    $payment = [

                        

                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->sma->formatDecimal($this->input->post('amount-paid')),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('pcc_no'),
                        'cc_holder'    => $holder,
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('cc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                     

                       'cantidad_cuota'   => $this->input->post('cantidad_cuota') ? $this->input->post('cantidad_cuota') : 0 ,


                        'recargototal'   => $recargo_tarjeta,
                        'recargotarjeta'   => $recargo_tarjeta,
                        'type'         => 'received',
                    ];
                }
            } else {
                $payment = [];
            }

            $attachments        = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
          


          //  $this->sma->print_arrays($data, $products, $payment, $attachments);
        

        }

        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment, [], $attachments)) {
            $this->session->set_userdata('remove_slls', 1);
            if ($quote_id) {
                $this->db->update('quotes', ['status' => 'completed'], ['id' => $quote_id]);
            }



                      $ruta =  getcwd();
                          require $ruta."/app/config/database.php";
                           $sql_ultimoid = "SELECT MAX(s.id) AS id FROM sma_sales s";
                             $resultado = $conn->query($sql_ultimoid);
                               while($sigo= $resultado->fetch_assoc() )
                               { $ultimoid=$sigo['id']; } 



  if(($recargo_tarjeta =="") || ($recargo_tarjeta ==0) )
           {
           }
           else
           {
             
                    $recargotarjeta = $recargo_tarjeta ; // $10
                    $ivarecargotarjeta = round($recargotarjeta * 0.173554,2) ;  // 2,1
                    $recargotarjetasiniva = $recargotarjeta - round($ivarecargotarjeta,2) ; // 10 - 2,1 = 7,90

                    $product_id = "1010101010";
                    $product_code = "OT";
                    $product_name = "Otros servicio/producto";
                    $product_type = "manual";
                    $net_unit_price =$recargotarjetasiniva;
                    $unit_price = $recargotarjeta;
                    $quantity = 1;
                    $warehouse_id = 3;
                    $item_tax = $ivarecargotarjeta ; 
                    $tax_rate_id=5;
                    $tax = "21 %";
                    $subtotal = $recargotarjeta;
                    $real_unit_price = $recargotarjeta;
                    $unit_quantity=1;


                 $consulta = "insert into sma_sale_items (sale_id,product_id,product_code,product_name,product_type,net_unit_price,unit_price,quantity,warehouse_id,item_tax,tax_rate_id,tax,subtotal,real_unit_price,unit_quantity) values ('$ultimoid','$product_id','$product_code','$product_name','$product_type','$net_unit_price','$unit_price','$quantity','$warehouse_id','$item_tax','$tax_rate_id','$tax','$subtotal','$real_unit_price','$unit_quantity')";

                                        $insert_tmp=mysqli_query($conn, $consulta);

                                         // update el precio pagado si lelgo tarjeta


                      $sql_venta = "SELECT sum(s.total+s.recargo_tarjeta+s.product_tax) AS total FROM sma_sales s WHERE s.id =".$ultimoid;
                           $resultado_venta = $conn->query($sql_venta);
                           while($vent= $resultado_venta->fetch_assoc() )
                           { $ventatotal=$vent['total']; } 

                       $sql_update = " update sma_sales s set s.paid=".$ventatotal.",s.payment_status='paid' where id=".$ultimoid;

                       $update_tmp=mysqli_query($conn, $sql_update);


                       
           }

           



                if (($enviaafip=="si") &&  ($payment_status == 'paid') )
                {
                   // echo "envia a fip" function afip ;
                    
                       admin_redirect("sales/afip?sale_id=".$ultimoid."&tipo=sales");
                }
                else   {  }

                          $ruta =  getcwd();
                          require $ruta."/app/config/database.php";




            $this->session->set_flashdata('message', lang('sale_added'));
            admin_redirect('sales');
        } else {


         
            if ($quote_id || $sale_id) {
                if ($quote_id) {
                    $this->data['quote'] = $this->sales_model->getQuoteByID($quote_id);
                    $items               = $this->sales_model->getAllQuoteItems($quote_id);
                } elseif ($sale_id) {
                    $this->data['quote'] = $this->sales_model->getInvoiceByID($sale_id);
                    $items               = $this->sales_model->getAllInvoiceItems($sale_id);
                }
                krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row             = json_decode('{}');
                        $row->tax_method = 0;
                    } else {
                        unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    }
                    $row->quantity = 0;
                    $pis           = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $row->quantity += $pi->quantity_balance;
                        }
                    }
                    $row->id              = $item->product_id;
                    $row->code            = $item->product_code;
                    $row->name            = $item->product_name;
                    $row->type            = $item->product_type;
                    $row->qty             = $item->quantity;
                    $row->base_quantity   = $item->quantity;
                    $row->base_unit       = $row->unit  ?? $item->product_unit_id;
                    $row->base_unit_price = $row->price ?? $item->unit_price;
                    $row->unit            = $item->product_unit_id;
                    $row->qty             = $item->unit_quantity;
                    $row->discount        = $item->discount ? $item->discount : '0';
                    $row->item_tax        = $item->item_tax      > 0 ? $item->item_tax      / $item->quantity : 0;
                    $row->item_discount   = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
                    $row->price           = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($row->item_discount));
                    $row->unit_price      = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($row->item_discount) + $this->sma->formatDecimal($row->item_tax) : $item->unit_price + ($row->item_discount);
                
                    if($duplicate=="")
                    {
                     $row->real_unit_price = $item->real_unit_price;
                    }
                    else
                    {

                                             $ruta =  getcwd();
                                             require $ruta."/app/config/database.php";

                                             $sql_sales_precionuevo = "SELECT p.price FROM sma_products p WHERE p.id=".$row->id;

                                               $resultado_precionuevo = $conn->query($sql_sales_precionuevo);
                                               while($pn= $resultado_precionuevo->fetch_assoc() )
                                               {$precionuevo=$pn['price'];}

                                               $row->real_unit_price = $precionuevo ;
                    }

                   
                


                    $row->tax_rate        = $item->tax_rate_id;
                    $row->serial          = '';
                    $row->option          = $item->option_id;
                    $options              = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
                    if ($options) {
                        $option_quantity = 0;
                        foreach ($options as $option) {
                            $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                            if ($pis) {
                                foreach ($pis as $pi) {
                                    $option_quantity += $pi->quantity_balance;
                                }
                            }
                            if ($option->quantity > $option_quantity) {
                                $option->quantity = $option_quantity;
                            }
                        }
                    }
                    $combo_items = false;
                    if ($row->type == 'combo') {
                        $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                    }
                    $units    = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $ri       = $this->Settings->item_addition ? $row->id : $c;

                    $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                        'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, ];
                    $c++;
                }
                $this->data['quote_items'] = json_encode($pr);
            }

            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id']   = $quote_id ? $quote_id : $sale_id;
            $this->data['billers']    = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['units']      = $this->site->getAllBaseUnits();
            //$this->data['currencies'] = $this->sales_model->getAllCurrencies();
            $this->data['slnumber']    = ''; //$this->site->getReference('so');
            $this->data['payment_ref'] = ''; //$this->site->getReference('pay');
            $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('add_sale')]];
            $meta                      = ['page_title' => lang('add_sale'), 'bc' => $bc];
            $this->page_construct('sales/add', $meta, $this->data);
        }
    }

    public function add_delivery($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
        if ($sale->sale_status != 'completed') {
            $this->session->set_flashdata('error', lang('status_is_x_completed'));
            $this->sma->md();
        }

        if ($delivery = $this->sales_model->getDeliveryBySaleID($id)) {
            $this->edit_delivery($delivery->id);
        } else {
            $this->form_validation->set_rules('sale_reference_no', lang('sale_reference_no'), 'required');
            $this->form_validation->set_rules('customer', lang('customer'), 'required');
            $this->form_validation->set_rules('address', lang('address'), 'required');

            if ($this->form_validation->run() == true) {
                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                } else {
                    $date = date('Y-m-d H:i:s');
                }
                $dlDetails = [
                    'date'              => $date,
                    'sale_id'           => $this->input->post('sale_id'),
                    'do_reference_no'   => $this->input->post('do_reference_no') ? $this->input->post('do_reference_no') : $this->site->getReference('do'),
                    'sale_reference_no' => $this->input->post('sale_reference_no'),
                    'customer'          => $this->input->post('customer'),
                    'address'           => $this->input->post('address'),
                    'status'            => $this->input->post('status'),
                    'delivered_by'      => $this->input->post('delivered_by'),
                    'received_by'       => $this->input->post('received_by'),
                    'note'              => $this->sma->clear_tags($this->input->post('note')),
                    'created_by'        => $this->session->userdata('user_id'),
                ];
                if ($_FILES['document']['size'] > 0) {
                    $this->load->library('upload');
                    $config['upload_path']   = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size']      = $this->allowed_file_size;
                    $config['overwrite']     = false;
                    $config['encrypt_name']  = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('document')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    $photo                   = $this->upload->file_name;
                    $dlDetails['attachment'] = $photo;
                }
            } elseif ($this->input->post('add_delivery')) {
                if ($sale->shop) {
                    $this->load->library('sms');
                    $this->sms->delivering($sale->id, $dlDetails['do_reference_no']);
                }
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER['HTTP_REFERER']);
            }

            if ($this->form_validation->run() == true && $this->sales_model->addDelivery($dlDetails)) {
                $this->session->set_flashdata('message', lang('delivery_added'));
                admin_redirect('sales/deliveries');
            } else {
                $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['customer']        = $this->site->getCompanyByID($sale->customer_id);
                $this->data['address']         = $this->site->getAddressByID($sale->address_id);
                $this->data['inv']             = $sale;
                $this->data['do_reference_no'] = ''; //$this->site->getReference('do');
                $this->data['modal_js']        = $this->site->modal_js();

                $this->load->view($this->theme . 'sales/add_delivery', $this->data);
            }
        }
    }

    public function add_gift_card()
    {
        $this->sma->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang('card_no'), 'trim|is_unique[gift_cards.card_no]|required');
        $this->form_validation->set_rules('value', lang('value'), 'required');

        if ($this->form_validation->run() == true) {
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer         = $customer_details ? $customer_details->company : null;
            $data             = ['card_no' => $this->input->post('card_no'),
                'value'                    => $this->input->post('value'),
                'customer_id'              => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer'                 => $customer,
                'balance'                  => $this->input->post('value'),
                'expiry'                   => $this->input->post('expiry') ? $this->sma->fsd($this->input->post('expiry')) : null,
                'created_by'               => $this->session->userdata('user_id'),
            ];
            $sa_data = [];
            $ca_data = [];
            if ($this->input->post('staff_points')) {
                $sa_points = $this->input->post('sa_points');
                $user      = $this->site->getUser($this->input->post('user'));
                if ($user->award_points < $sa_points) {
                    $this->session->set_flashdata('error', lang('award_points_wrong'));
                    admin_redirect('sales/gift_cards');
                }
                $sa_data = ['user' => $user->id, 'points' => ($user->award_points - $sa_points)];
            } elseif ($customer_details && $this->input->post('use_points')) {
                $ca_points = $this->input->post('ca_points');
                if ($customer_details->award_points < $ca_points) {
                    $this->session->set_flashdata('error', lang('award_points_wrong'));
                    admin_redirect('sales/gift_cards');
                }
                $ca_data = ['customer' => $this->input->post('customer'), 'points' => ($customer_details->award_points - $ca_points)];
            }
            // $this->sma->print_arrays($data, $ca_data, $sa_data);
        } elseif ($this->input->post('add_gift_card')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('sales/gift_cards');
        }

        if ($this->form_validation->run() == true && $this->sales_model->addGiftCard($data, $ca_data, $sa_data)) {
            $this->session->set_flashdata('message', lang('gift_card_added'));
            admin_redirect('sales/gift_cards');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['users']      = $this->sales_model->getStaff();
            $this->data['page_title'] = lang('new_gift_card');
            $this->load->view($this->theme . 'sales/add_gift_card', $this->data);
        }
    }

    public function add_payment($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
        if ($sale->payment_status == 'paid' && $sale->grand_total == $sale->paid) {
            $this->session->set_flashdata('error', lang('sale_already_paid'));
            $this->sma->md();
        }

         $recargo_tarjeta  =   $this->input->post('cc_year') ;

          if($recargo_tarjeta > 0 )
           {
              

               
                     $ivarecargotarjeta = round($recargo_tarjeta * 0.173554,2)  ;  // 2,1
                     $recargotarjetasiniva = $recargo_tarjeta - round($ivarecargotarjeta,2) ; // 10 - 2,1 = 7,90



           }
           else
           {
                $recargo_tarjeta  = 0 ;

           }



        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
            if ($this->input->post('paid_by') == 'deposit') {
                $customer_id = $sale->customer_id;
                if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = [
                'date'         => $date,
                'sale_id'      => $this->input->post('sale_id'),
                'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay'),
                'amount'       => $this->input->post('amount-paid') + $recargo_tarjeta,
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'note'         => $this->input->post('note'),
                'recargototal'   => $recargo_tarjeta,
                'recargotarjeta'   => $recargo_tarjeta,
                'created_by'   => $this->session->userdata('user_id'),
                'type'         => $sale->sale_status == 'returned' ? 'returned' : 'received',
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo                 = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->sma->print_arrays($payment);
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addPayment($payment, $customer_id)) {
                    if ($sale->shop) {
                        $this->load->library('sms');
                        $this->sms->paymentReceived($sale->id, $payment['reference_no'], $payment['amount']);
                    }






            $this->session->set_flashdata('message', lang('payment_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if ($sale->sale_status == 'returned' && $sale->paid == $sale->grand_total) {
                $this->session->set_flashdata('warning', lang('payment_was_returned'));
                $this->sma->md();
            }
            $this->data['inv']         = $sale;
            $this->data['payment_ref'] = ''; //$this->site->getReference('pay');
            $this->data['modal_js']    = $this->site->modal_js();

            $this->load->view($this->theme . 'sales/add_payment', $this->data);
        }
    }

    public function combine_pdf($sales_id)
    {
        $this->sma->checkPermissions('pdf');

        foreach ($sales_id as $id) {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv                 = $this->sales_model->getInvoiceByID($id);
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
            $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
            $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
            $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user']        = $this->site->getUser($inv->created_by);
            $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv']         = $inv;
            $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
            $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
            $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
            $html_data                 = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
            if (!$this->Settings->barcode_img) {
                $html_data = preg_replace("'\<\?xml(.*)\?\>'", '', $html_data);
            }

            $html[] = [
                'content' => $html_data,
                'footer'  => $this->data['biller']->invoice_footer,
            ];
        }

        $name = lang('sales') . '.pdf';
        $this->sma->generate_pdf($html, $name);
    }

    /* ------------------------------- */

    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
       
         $ruta =  getcwd();
         require $ruta."/app/config/database.php";

          $sql_sales_borrar = "SELECT * FROM sma_sales s WHERE s.id=".$id;

          $resultado_sigo_borrar = $conn->query($sql_sales_borrar);
               while($sigob= $resultado_sigo_borrar->fetch_assoc() )
               {$numero_comprobante=$sigob['numero_comprobante'];}

            if($numero_comprobante <>"")
            {
             //echo "<script> alert('Debe seleccionar tipo de comprobante');document.location.href='".$ruta."/admin/sales/';</script>";

                     $this->sma->send_json([ 'msg' => lang('venta no puede ser borrada ')]);
                     admin_redirect('sales');
                     redirect($_SERVER['HTTP_REFERER']);
            }
            else
            {

                    // $consulta = "delete from sma_sales where id =".$id;
                     //$insert_tmp=mysqli_query($conn, $consulta);



                      $this->sales_model->deleteSale($id);



                     // borrar los pagos de esa sventas tambien 

                      $consulta2 = "delete from sma_payments where sale_id =".$id;
                      $insert_tmp2=mysqli_query($conn, $consulta2);





                     $this->sma->send_json([ 'msg' => lang('venta borrada con exito')]);
                     admin_redirect('sales');
                     redirect($_SERVER['HTTP_REFERER']);
            }

         



    }

    public function delete_delivery($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->sales_model->deleteDelivery($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('delivery_deleted')]);
        }
    }

    public function delete_gift_card($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->sales_model->deleteGiftCard($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('gift_card_deleted')]);
        }
    }

    public function delete_payment($id = null)
    {
        $this->sma->checkPermissions('delete');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->sales_model->deletePayment($id)) {
            //echo lang("payment_deleted");
            $this->session->set_flashdata('message', lang('payment_deleted'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_return($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->sales_model->deleteReturn($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(['error' => 0, 'msg' => lang('return_sale_deleted')]);
            }
            $this->session->set_flashdata('message', lang('return_sale_deleted'));
            admin_redirect('welcome');
        }
    }

    /* ------------------------------- */

    public function deliveries()
    {
        $this->sma->checkPermissions();

        $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc            = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('deliveries')]];
        $meta          = ['page_title' => lang('deliveries'), 'bc' => $bc];
        $this->page_construct('sales/deliveries', $meta, $this->data);
    }

    public function delivery_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete_delivery');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteDelivery($id);
                    }
                    $this->session->set_flashdata('message', lang('deliveries_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('deliveries'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('do_reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $delivery = $this->sales_model->getDeliveryByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($delivery->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $delivery->do_reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $delivery->sale_reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $delivery->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $delivery->address);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($delivery->status));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(35);

                    $filename = 'deliveries_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_delivery_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    /* ------------------------------------------------------------------------ */

    
    public function edit($id = null)
    {


   $ruta =  getcwd();
                         require $ruta."/app/config/database.php";

                         $sql_sales_borrar = "SELECT * FROM sma_sales s WHERE s.id=".$id;

                           $resultado_sigo_borrar = $conn->query($sql_sales_borrar);
                           while($sigob= $resultado_sigo_borrar->fetch_assoc() )
                           {$numero_comprobante=$sigob['numero_comprobante'];}

   if($numero_comprobante =="")

         {// del editar factura




        /*----------------------------------------------------*/
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getInvoiceByID($id);
        if ($inv->sale_status == 'returned' || $inv->return_id || $inv->return_sale_ref) {
            $this->session->set_flashdata('error', lang('sale_x_action'));
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->sma->clear_tags($this->input->post('note'));
            $staff_note       = $this->sma->clear_tags($this->input->post('staff_note'));

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = $_POST['serial'][$r]           ?? '';
                $item_tax_rate      = $_POST['product_tax'][$r]      ?? null;
                $item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;

                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->sma->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax    = $this->sma->formatDecimal($ctax['iva']);
                        $tax         = $ctax['iva'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->sma->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->sma->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'real_unit_price'   => $real_unit_price,
                    ];

                    $products[] = ($product + $gst_data);
                    $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax), true);
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            // $grand_total    = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $data        = ['date'  => $date,
                'reference_no'      => $reference,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->sma->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'total_items'       => $total_items,
                'sale_status'       => $sale_status,
                'payment_status'    => $payment_status,
                'payment_term'      => $payment_term,
                'due_date'          => $due_date,
                'updated_by'        => $this->session->userdata('user_id'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            $attachments        = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateSale($id, $data, $products, $attachments)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('sale_updated'));
            admin_redirect($inv->pos ? 'pos/sales' : 'sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang('sale_x_edited_older_than_x_days'), $this->Settings->disable_editing));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            $inv_items = $this->sales_model->getAllInvoiceItems($id);
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                // $row = $this->site->getProductByID($item->product_id);
                $row = $this->sales_model->getWarehouseProduct($item->product_id, $item->warehouse_id);
                if (!$row) {
                    $row             = json_decode('{}');
                    $row->tax_method = 0;
                    $row->quantity   = 0;
                } else {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
                $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) {
                    $row->quantity = 0;
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                $row->id              = $item->product_id;
                $row->code            = $item->product_code;
                $row->name            = $item->product_name;
                $row->type            = $item->product_type;
                $row->base_quantity   = $item->quantity;
                $row->base_unit       = !empty($row->unit) ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = !empty($row->price) ? $row->price : $item->unit_price;
                $row->unit            = $item->product_unit_id;
                $row->qty             = $item->unit_quantity;
                $row->quantity += $item->quantity;
                $row->discount        = $item->discount ? $item->discount : '0';
                $row->item_tax        = $item->item_tax      > 0 ? $item->item_tax      / $item->quantity : 0;
                $row->item_discount   = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
                $row->price           = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($row->item_discount));
                $row->unit_price      = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($row->item_discount) + $this->sma->formatDecimal($row->item_tax) : $item->unit_price + ($row->item_discount);
                $row->real_unit_price = $item->real_unit_price;
                $row->tax_rate        = $item->tax_rate_id;
                $row->serial          = $item->serial_no;
                $row->option          = $item->option_id;
                $options              = $this->sales_model->getProductOptions($row->id, $item->warehouse_id, true);
                if ($options) {
                    foreach ($options as $option) {
                        $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                        if ($pis) {
                            $option->quantity = 0;
                            foreach ($pis as $pi) {
                                $option->quantity += $pi->quantity_balance;
                            }
                        }
                        if ($row->option == $option->id) {
                            $option->quantity += $item->quantity;
                        }
                    }
                }

                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                    $te          = $combo_items;
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }
                $units    = !empty($row->base_unit) ? $this->site->getUnitsByBUID($row->base_unit) : null;
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri       = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, ];
                $c++;
            }

            $this->data['inv_items'] = json_encode($pr);
            $this->data['id']        = $id;
            //$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['billers']    = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['units']      = $this->site->getAllBaseUnits();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('edit_sale')]];
            $meta = ['page_title' => lang('edit_sale'), 'bc' => $bc];
            $this->page_construct('sales/edit', $meta, $this->data);
        }

        /*---------------------------------------------------*/

 } // del editar factura  

        else
        {
           $this->session->set_flashdata('error', lang('No se puede editar venta ya facturada en AFIP'));
                    admin_redirect('sales');


        }


    }






    public function edit_delivery($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('do_reference_no', lang('do_reference_no'), 'required');
        $this->form_validation->set_rules('sale_reference_no', lang('sale_reference_no'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('address', lang('address'), 'required');

        if ($this->form_validation->run() == true) {
            $dlDetails = [
                'sale_id'           => $this->input->post('sale_id'),
                'do_reference_no'   => $this->input->post('do_reference_no'),
                'sale_reference_no' => $this->input->post('sale_reference_no'),
                'customer'          => $this->input->post('customer'),
                'address'           => $this->input->post('address'),
                'status'            => $this->input->post('status'),
                'delivered_by'      => $this->input->post('delivered_by'),
                'received_by'       => $this->input->post('received_by'),
                'note'              => $this->sma->clear_tags($this->input->post('note')),
                'created_by'        => $this->session->userdata('user_id'),
            ];

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo                   = $this->upload->file_name;
                $dlDetails['attachment'] = $photo;
            }

            if ($this->Owner || $this->Admin) {
                $date              = $this->sma->fld(trim($this->input->post('date')));
                $dlDetails['date'] = $date;
            }
        } elseif ($this->input->post('edit_delivery')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateDelivery($id, $dlDetails)) {
            $this->session->set_flashdata('message', lang('delivery_updated'));
            admin_redirect('sales/deliveries');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['delivery'] = $this->sales_model->getDeliveryByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_delivery', $this->data);
        }
    }

    public function edit_gift_card($id = null)
    {
        $this->sma->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang('card_no'), 'trim|required');
        $gc_details = $this->site->getGiftCardByID($id);
        if ($this->input->post('card_no') != $gc_details->card_no) {
            $this->form_validation->set_rules('card_no', lang('card_no'), 'is_unique[gift_cards.card_no]');
        }
        $this->form_validation->set_rules('value', lang('value'), 'required');
        //$this->form_validation->set_rules('customer', lang("customer"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $gift_card        = $this->site->getGiftCardByID($id);
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer         = $customer_details ? $customer_details->company : null;
            $data             = ['card_no' => $this->input->post('card_no'),
                'value'                    => $this->input->post('value'),
                'customer_id'              => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer'                 => $customer,
                'balance'                  => ($this->input->post('value') - $gift_card->value) + $gift_card->balance,
                'expiry'                   => $this->input->post('expiry') ? $this->sma->fsd($this->input->post('expiry')) : null,
            ];
        } elseif ($this->input->post('edit_gift_card')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('sales/gift_cards');
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateGiftCard($id, $data)) {
            $this->session->set_flashdata('message', lang('gift_card_updated'));
            admin_redirect('sales/gift_cards');
        } else {
            $this->data['error']     = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['gift_card'] = $this->site->getGiftCardByID($id);
            $this->data['id']        = $id;
            $this->data['modal_js']  = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_gift_card', $this->data);
        }
    }

    public function edit_payment($id = null)
    {
        $this->sma->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment = $this->sales_model->getPaymentByID($id);
        if ($payment->paid_by == 'ppp' || $payment->paid_by == 'stripe' || $payment->paid_by == 'paypal' || $payment->paid_by == 'skrill') {
            $this->session->set_flashdata('error', lang('x_edit_payment'));
            $this->sma->md();
        }
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->input->post('paid_by') == 'deposit') {
                $sale        = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
                $customer_id = $sale->customer_id;
                $amount      = $this->input->post('amount-paid') - $payment->amount;
                if (!$this->site->check_customer_deposit($customer_id, $amount)) {
                    $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $payment->date;
            }
            $payment = [
                'date'         => $date,
                'sale_id'      => $this->input->post('sale_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount'       => $this->input->post('amount-paid'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'note'         => $this->input->post('note'),
                'created_by'   => $this->session->userdata('user_id'),
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo                 = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->sma->print_arrays($payment);
        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updatePayment($id, $payment, $customer_id)) {
            $this->session->set_flashdata('message', lang('payment_updated'));
            admin_redirect('sales');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment']  = $payment;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_payment', $this->data);
        }
    }

    public function email($id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getInvoiceByID($id);
        $this->form_validation->set_rules('to', lang('to') . ' ' . lang('email'), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', lang('subject'), 'trim|required');
        $this->form_validation->set_rules('cc', lang('cc'), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', lang('bcc'), 'trim|valid_emails');
        $this->form_validation->set_rules('note', lang('message'), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $to      = $this->input->post('to');
            $subject = $this->input->post('subject');
            if ($this->input->post('cc')) {
                $cc = $this->input->post('cc');
            } else {
                $cc = null;
            }
            if ($this->input->post('bcc')) {
                $bcc = $this->input->post('bcc');
            } else {
                $bcc = null;
            }
            $customer = $this->site->getCompanyByID($inv->customer_id);
            $biller   = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = [
                'reference_number' => $inv->reference_no,
                'contact_person'   => "",
                'company'          => $customer->company && $customer->company != '-' ? '(' . $customer->company . ')' : '',
                'order_link'       => $inv->shop ? shop_url('orders/' . $inv->id . '/' . ($this->loggedIn ? '' : $inv->hash)) : base_url(),
                'site_link'        => base_url(),
                'site_name'        => $this->Settings->site_name,
                'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company && $biller->company != '-' ? $biller->company : $biller->name) . '"/>',
            ];
            $msg      = $this->input->post('note');
            $message  = $this->parser->parse_string($msg, $parse_data);
            $paypal   = $this->sales_model->getPaypalSettings();
            $skrill   = $this->sales_model->getSkrillSettings();
            $btn_code = '<div id="payment_buttons" class="text-center margin010">';
            if ($paypal->active == '1' && $inv->grand_total != '0.00') {
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_my / 100);
                } else {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_other / 100);
                }
                $btn_code .= '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=' . $paypal->account_email . '&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&image_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $paypal_fee) . '&no_shipping=1&no_note=1&currency_code=' . $this->default_currency->code . '&bn=FC-BuyNow&rm=2&return=' . admin_url('sales/view/' . $inv->id) . '&cancel_return=' . admin_url('sales/view/' . $inv->id) . '&notify_url=' . admin_url('payments/paypalipn') . '&custom=' . $inv->reference_no . '__' . ($inv->grand_total - $inv->paid) . '__' . $paypal_fee . '"><img src="' . base_url('assets/images/btn-paypal.png') . '" alt="Pay by PayPal"></a> ';
            }
            if ($skrill->active == '1' && $inv->grand_total != '0.00') {
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_my / 100);
                } else {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_other / 100);
                }
                $btn_code .= ' <a href="https://www.moneybookers.com/app/payment.pl?method=get&pay_to_email=' . $skrill->account_email . '&language=EN&merchant_fields=item_name,item_number&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&logo_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $skrill_fee) . '&return_url=' . admin_url('sales/view/' . $inv->id) . '&cancel_url=' . admin_url('sales/view/' . $inv->id) . '&detail1_description=' . $inv->reference_no . '&detail1_text=Payment for the sale invoice ' . $inv->reference_no . ': ' . $inv->grand_total . '(+ fee: ' . $skrill_fee . ') = ' . $this->sma->formatMoney($inv->grand_total + $skrill_fee) . '&currency=' . $this->default_currency->code . '&status_url=' . admin_url('payments/skrillipn') . '"><img src="' . base_url('assets/images/btn-skrill.png') . '" alt="Pay by Skrill"></a>';
            }

            $btn_code .= '<div class="clearfix"></div></div>';
            $message    = $message . $btn_code;
            $attachment = $this->pdf($id, null, 'S');

            try {
                if ($this->sma->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
                    delete_files($attachment);
                    $this->session->set_flashdata('message', lang('email_sent'));
                    admin_redirect('sales');
                }
            } catch (Exception $e) {
                $this->session->set_flashdata('error', $e->getMessage());
                redirect($_SERVER['HTTP_REFERER']);
            }
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            if (file_exists('./themes/' . $this->Settings->theme . '/admin/views/email_templates/sale.html')) {
                $sale_temp = file_get_contents('themes/' . $this->Settings->theme . '/admin/views/email_templates/sale.html');
            } else {
                $sale_temp = file_get_contents('./themes/default/admin/views/email_templates/sale.html');
            }

            $this->data['subject'] = ['name' => 'subject',
                'id'                         => 'subject',
                'type'                       => 'text',
                'value'                      => $this->form_validation->set_value('subject', lang('invoice') . ' (' . $inv->reference_no . ') ' . lang('from') . ' ' . $this->Settings->site_name),
            ];
            $this->data['note'] = ['name' => 'note',
                'id'                      => 'note',
                'type'                    => 'text',
                'value'                   => $this->form_validation->set_value('note', $sale_temp),
            ];
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);

            $this->data['id']       = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/email', $this->data);
        }
    }

    public function email_payment($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $payment              = $this->sales_model->getPaymentByID($id);
        $inv                  = $this->sales_model->getInvoiceByID($payment->sale_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $customer             = $this->site->getCompanyByID($inv->customer_id);
        if (!$customer->email) {
            $this->sma->send_json(['msg' => lang('update_customer_email')]);
        }
        $this->data['inv']        = $inv;
        $this->data['payment']    = $payment;
        $this->data['customer']   = $customer;
        $this->data['page_title'] = lang('payment_note');
        $html                     = $this->load->view($this->theme . 'sales/payment_note', $this->data, true);

        $html = str_replace(['<i class="fa fa-2x">&times;</i>', 'modal-', '<p>&nbsp;</p>', '<p style="border-bottom: 1px solid #666;">&nbsp;</p>', '<p>' . lang('stamp_sign') . '</p>'], '', $html);
        $html = preg_replace("/<img[^>]+\>/i", '', $html);
        // $html = '<div style="border:1px solid #DDD; padding:10px; margin:10px 0;">'.$html.'</div>';

        $this->load->library('parser');
        $parse_data = [
            'stylesheet' => '<link href="' . $this->data['assets'] . 'styles/helpers/bootstrap.min.css" rel="stylesheet"/>',
            'name'       => $customer->company && $customer->company != '-' ? $customer->company : $customer->name,
            'email'      => $customer->email,
            'heading'    => lang('payment_note') . '<hr>',
            'msg'        => $html,
            'site_link'  => base_url(),
            'site_name'  => $this->Settings->site_name,
            'logo'       => '<img src="' . base_url('assets/uploads/logos/' . $this->Settings->logo) . '" alt="' . $this->Settings->site_name . '"/>',
        ];
        $msg     = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/email_con.html');
        $message = $this->parser->parse_string($msg, $parse_data);
        $subject = lang('payment_note') . ' - ' . $this->Settings->site_name;

        if ($this->sma->send_email($customer->email, $subject, $message)) {
            $this->sma->send_json(['msg' => lang('email_sent')]);
        } else {
            $this->sma->send_json(['msg' => lang('email_failed')]);
        }
    }

    public function get_award_points($id = null)
    {
        $this->sma->checkPermissions('index');

        $row = $this->site->getUser($id);
        $this->sma->send_json(['sa_points' => $row->award_points]);
    }

    public function getDeliveries()
    {
        $this->sma->checkPermissions('deliveries');

        $detail_link = anchor('admin/sales/view_delivery/$1', '<i class="fa fa-file-text-o"></i> ' . lang('delivery_details'), 'data-toggle="modal" data-target="#myModal"');
        $email_link  = anchor('admin/sales/email_delivery/$1', '<i class="fa fa-envelope"></i> ' . lang('email_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link   = anchor('admin/sales/edit_delivery/$1', '<i class="fa fa-edit"></i> ' . lang('edit_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $pdf_link    = anchor('admin/sales/pdf_delivery/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang('delete_delivery') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete_delivery/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_delivery') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
    <ul class="dropdown-menu pull-right" role="menu">
        <li>' . $detail_link . '</li>
        <li>' . $edit_link . '</li>
        <li>' . $pdf_link . '</li>
        <li>' . $delete_link . '</li>
    </ul>
</div></div>';

        $this->load->library('datatables');
        //GROUP_CONCAT(CONCAT('Name: ', sale_items.product_name, ' Qty: ', sale_items.quantity ) SEPARATOR '<br>')
        $this->datatables
            ->select('deliveries.id as id, date, do_reference_no, sale_reference_no, customer, address, status, attachment')
            ->from('deliveries')
            ->join('sale_items', 'sale_items.sale_id=deliveries.sale_id', 'left')
            ->group_by('deliveries.id');
        $this->datatables->add_column('Actions', $action, 'id');

        echo $this->datatables->generate();
    }

    public function getGiftCards()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('gift_cards') . '.id as id, card_no, value, balance, CONCAT(' . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . '.last_name) as created_by, customer, expiry', false)
            ->join('users', 'users.id=gift_cards.created_by', 'left')
            ->from('gift_cards')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('sales/view_gift_card/$1') . "' class='tip' title='" . lang('view_gift_card') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-eye\"></i></a> <a href='" . admin_url('sales/topup_gift_card/$1') . "' class='tip' title='" . lang('topup_gift_card') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-dollar\"></i></a> <a href='" . admin_url('sales/edit_gift_card/$1') . "' class='tip' title='" . lang('edit_gift_card') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_gift_card') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete_gift_card/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    public function getSales($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
         $detail_link       = anchor('admin/sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));

       $detail_link2       = anchor('admin/sales/afip?sale_id=$1&tipo=sales', '  <img src="https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png" style="width: 50px"> ' . lang('Enviar venta a ARCA'));
       $detail_link22       = anchor('admin/sales/notacredito/$1', '  <img src="https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png" style="width: 50px"> ' . lang('Nota de Crédito'));
     
      $detail_link33       = anchor('admin/sales/viewsale/$1', '  <i class="fa fa-file-text-o"></i> ' . lang('Ver factura'));
     

      $detail_link44       = anchor('admin/sales/viewTicket/$1', '  <i class="fa fa-file-text-o"></i> ' . lang('Ver Ticket'));
     


    $detail_link_factura_cc       = anchor('admin/sales/viewsalecc/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Ver factura CC'));
              

              $detail_link_cc       = anchor('admin/sales/viewTicketcc/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Ver Ticket CC'));




 $detail_link55       = anchor('admin/sales/viewremito/$1', '  <i class="fa fa-file-text-o"></i> ' . lang('Ver Remito'));

        $duplicate_link    = anchor('admin/sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));

        $duplicate__new_link    = anchor('admin/sales/add?sale_id=$1&duplicate=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicar con precio actual'));



        $payments_link     = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link  = anchor('admin/sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
       $packagink_link    = anchor('admin/sales/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('admin/sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link        = anchor('admin/sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link         = anchor('admin/sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link          = anchor('admin/sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link       = anchor('admin/sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $detail_link2 . '</li>
            <li>' . $detail_link22 . '</li>
            <li>' . $detail_link33 . '</li>
            <li>' . $detail_link44 . '</li>
            <li>' . $detail_link_factura_cc . '</li>
            <li>' . $detail_link_cc . '</li>



            <li>' . $detail_link55 . '</li>
            <li>' . $duplicate_link . '</li>
            <li>' . $duplicate__new_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $packagink_link . '</li>
            <li>' . $add_delivery_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $return_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

       $this->load->library('datatables');
     

      if ($warehouse_id) {

          


            $this->datatables
               ->select("{$this->db->dbprefix('sales')}.id as id, DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, reference_no, if(shop = 1, 'Tienda Shop', username), concat( {$this->db->dbprefix('sales')}.customer ,'hola'),  sale_status, grand_total, paid, (grand_total-paid) as balance,numero_comprobante, payment_status,  concat(if(CAE <>'' , 'ARCA', 'No Emitido'),if(nota_credito <>'nc' , '', ' (nc)')), return_id")


                ->from('sales')
                ->join('users', 'sales.created_by=users.id', 'left')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->where('sales.warehouse_id', $warehouse_id);
        } else {
         

       


            $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id as id, DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, reference_no, if(shop = 1, 'Tienda Shop', username), concat( {$this->db->dbprefix('companies')}.name ,'-',  {$this->db->dbprefix('sales')}.customer ), sale_status, grand_total, paid, (grand_total-paid) as balance, numero_comprobante,payment_status, concat(if(CAE <>'' , 'ARCA', 'No Emitido'),if(nota_credito <>'nc' , '', ' (nc)')), return_id")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                 ->join('users', 'sales.created_by=users.id', 'left');;
        }









        if ($this->input->get('shop') == 'yes') {
            $this->datatables->where('shop', 1);
        } elseif ($this->input->get('shop') == 'no') {
            $this->datatables->where('shop !=', 1);
        }
        if ($this->input->get('delivery') == 'no') {
            $this->datatables->join('deliveries', 'deliveries.sale_id=sales.id', 'left')
            ->where('sales.sale_status', 'completed')->where('sales.payment_status', 'paid')
            ->where("({$this->db->dbprefix('deliveries')}.status != 'delivered' OR {$this->db->dbprefix('deliveries')}.status IS NULL)", null);
        }
        if ($this->input->get('attachment') == 'yes') {
            $this->datatables->where('payment_status !=', 'paid')->where('attachment !=', null);
        }
        $this->datatables->where('pos !=', 1); // ->where('sale_status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }




  public function viewsale($id = null)
    {
        $this->sma->checkPermissions('index');
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['paypal']      = $this->sales_model->getPaypalSettings();
        $this->data['skrill']      = $this->sales_model->getSkrillSettings();
        $this->data['attachments'] = $this->site->getAttachments($id, 'sale');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_sales_details'), 'bc' => $bc];
        $this->page_construct('sales/viewsale', $meta, $this->data);
    }



  public function viewsalecc($id = null)
    {
        $this->sma->checkPermissions('index');
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['paypal']      = $this->sales_model->getPaypalSettings();
        $this->data['skrill']      = $this->sales_model->getSkrillSettings();
        $this->data['attachments'] = $this->site->getAttachments($id, 'sale');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_sales_details'), 'bc' => $bc];
        $this->page_construct('sales/viewsalecc', $meta, $this->data);
    }

 public function viewremito($id = null)
    {
        $this->sma->checkPermissions('index');
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['paypal']      = $this->sales_model->getPaypalSettings();
        $this->data['skrill']      = $this->sales_model->getSkrillSettings();
        $this->data['attachments'] = $this->site->getAttachments($id, 'sale');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_sales_details'), 'bc' => $bc];
        $this->page_construct('sales/viewremito', $meta, $this->data);
    }


     public function viewx($id = null)
    {
        $this->sma->checkPermissions('index');
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['paypal']      = $this->sales_model->getPaypalSettings();
        $this->data['skrill']      = $this->sales_model->getSkrillSettings();
        $this->data['attachments'] = $this->site->getAttachments($id, 'sale');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_sales_details'), 'bc' => $bc];
        $this->page_construct('sales/viewx', $meta, $this->data);
    }




  public function viewTicket($id = null)
    {
        $this->sma->checkPermissions('index');
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['paypal']      = $this->sales_model->getPaypalSettings();
        $this->data['skrill']      = $this->sales_model->getSkrillSettings();
        $this->data['attachments'] = $this->site->getAttachments($id, 'sale');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_sales_details'), 'bc' => $bc];
        $this->page_construct('sales/viewTicket', $meta, $this->data);
    }



  public function viewTicketcc($id = null)
    {
        $this->sma->checkPermissions('index');
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['paypal']      = $this->sales_model->getPaypalSettings();
        $this->data['skrill']      = $this->sales_model->getSkrillSettings();
        $this->data['attachments'] = $this->site->getAttachments($id, 'sale');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_sales_details'), 'bc' => $bc];
        $this->page_construct('sales/viewTicketcc', $meta, $this->data);
    }






    public function gift_card_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete_gift_card');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteGiftCard($id);
                    }
                    $this->session->set_flashdata('message', lang('gift_cards_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('gift_cards'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('card_no'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('value'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->site->getGiftCardByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->card_no);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->value);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->customer);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'gift_cards_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_gift_card_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    /* ------------------------------------ Gift Cards ---------------------------------- */

    public function gift_cards()
    {
        $this->sma->checkPermissions();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('gift_cards')]];
        $meta = ['page_title' => lang('gift_cards'), 'bc' => $bc];
        $this->page_construct('sales/gift_cards', $meta, $this->data);
    }

    public function index($warehouse_id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses']   = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse']    = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('sales')]];
        $meta = ['page_title' => lang('sales'), 'bc' => $bc];
        $this->page_construct('sales/index', $meta, $this->data);
    }

    public function modal_view($id = null)
    {
        $this->sma->checkPermissions('index', true);
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['attachments'] = $this->site->getAttachments($id, 'sale');

        $this->load->view($this->theme . 'sales/modal_view', $this->data);
    }

    public function packaging($id)
    {
        $sale                   = $this->sales_model->getInvoiceByID($id);
        $this->data['returned'] = false;
        if ($sale->sale_status == 'returned' || $sale->return_id) {
            $this->data['returned'] = true;
        }
        $this->data['warehouse'] = $this->site->getWarehouseByID($sale->warehouse_id);
        $items                   = $this->sales_model->getAllInvoiceItems($sale->id);
        foreach ($items as $item) {
            $packaging[] = [
                'name'     => $item->product_code . ' - ' . $item->product_name,
                'quantity' => $item->quantity,
                'unit'     => $item->product_unit_code,
                'rack'     => $this->sales_model->getItemRack($item->product_id, $sale->warehouse_id),
            ];
        }
        $this->data['packaging'] = $packaging;
        $this->data['sale']      = $sale;

        $this->load->view($this->theme . 'sales/packaging', $this->data);
    }

    public function payment_note($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $payment                  = $this->sales_model->getPaymentByID($id);
        $inv                      = $this->sales_model->getInvoiceByID($payment->sale_id);
        $this->data['biller']     = $this->site->getCompanyByID($inv->biller_id);
        $this->data['customer']   = $this->site->getCompanyByID($inv->customer_id);
        $this->data['inv']        = $inv;
        $this->data['payment']    = $payment;
        $this->data['page_title'] = lang('payment_note');

        $this->load->view($this->theme . 'sales/payment_note', $this->data);
    }


     public function payment_note2($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $payment                  = $this->sales_model->getPaymentByID($id);
        $inv                      = $this->sales_model->getInvoiceByID($payment->sale_id);
        $this->data['biller']     = $this->site->getCompanyByID($inv->biller_id);
        $this->data['customer']   = $this->site->getCompanyByID($inv->customer_id);
        $this->data['inv']        = $inv;
        $this->data['payment']    = $payment;
        $this->data['page_title'] = lang('payment_note');

        $this->load->view($this->theme . 'sales/payment_note2', $this->data);
    }




    /* -------------------------------------------------------------------------------- */

    public function payments($id = null)
    {
        $this->sma->checkPermissions(false, true);
        $this->data['payments'] = $this->sales_model->getInvoicePayments($id);
        $this->data['inv']      = $this->sales_model->getInvoiceByID($id);
        $this->load->view($this->theme . 'sales/payments', $this->data);
    }

    public function pdf($id = null, $view = null, $save_bufffer = null)
    {
        $this->sma->checkPermissions();
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user']        = $this->site->getUser($inv->created_by);
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        //$this->data['paypal'] = $this->sales_model->getPaypalSettings();
        //$this->data['skrill'] = $this->sales_model->getSkrillSettings();

        $name = lang('sale') . '_' . str_replace('/', '_', $inv->reference_no) . '.pdf';
        $html = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }

        if ($view) {
            $this->load->view($this->theme . 'sales/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer, $this->data['biller']->invoice_footer);
        } else {
            $this->sma->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
        }
    }


     public function pdf_remito($id = null, $view = null, $save_bufffer = null)
    {
        $this->sma->checkPermissions();
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user']        = $this->site->getUser($inv->created_by);
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        //$this->data['paypal'] = $this->sales_model->getPaypalSettings();
        //$this->data['skrill'] = $this->sales_model->getSkrillSettings();

        $name = lang('Remito') . '_' . str_replace('/', '_', $inv->reference_no) . '.pdf';
        $html = $this->load->view($this->theme . 'sales/pdf_remito', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }

        if ($view) {
            $this->load->view($this->theme . 'sales/pdf_remito', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer, $this->data['biller']->invoice_footer);
        } else {
            $this->sma->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
        }
    }

    public function pdf_delivery($id = null, $view = null, $save_bufffer = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli                = $this->sales_model->getDeliveryByID($id);

        $this->data['delivery'] = $deli;
        $sale                   = $this->sales_model->getInvoiceByID($deli->sale_id);
        $this->data['biller']   = $this->site->getCompanyByID($sale->biller_id);
        $this->data['rows']     = $this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
        $this->data['user']     = $this->site->getUser($deli->created_by);

        $name = lang('delivery') . '_' . str_replace('/', '_', $deli->do_reference_no) . '.pdf';
        $html = $this->load->view($this->theme . 'sales/pdf_delivery', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'sales/pdf_delivery', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->sma->generate_pdf($html, $name);
        }
    }

    /* ------------------------------- */

    public function return_sale($id = null)
    {
        $this->sma->checkPermissions('return_sales');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
        if ($sale->return_id) {
            $this->session->set_flashdata('error', lang('sale_already_returned'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('return_surcharge', lang('return_surcharge'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $note             = $this->sma->clear_tags($this->input->post('note'));
            $customer_details = $this->site->getCompanyByID($sale->customer_id);
            $biller_details   = $this->site->getCompanyByID($sale->biller_id);

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $sale_item_id       = $_POST['sale_item_id'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = (0 - $_POST['quantity'][$r]);
                $item_serial        = $_POST['serial'][$r]           ?? '';
                $item_tax_rate      = $_POST['product_tax'][$r]      ?? null;
                $item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = (0 - $_POST['product_base_quantity'][$r]);

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->sma->formatDecimal(($unit_price - $pr_discount), 4);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity, 4);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax    = $this->sma->formatDecimal($ctax['amount']);
                        $tax         = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = $this->sma->formatDecimal((($item_net_price * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit     = $item_unit ? $this->site->getUnitByID($item_unit) : false;

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->sma->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $sale->warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->sma->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'real_unit_price'   => $real_unit_price,
                        'sale_item_id'      => $sale_item_id,
                    ];

                    $si_return[] = [
                        'id'           => $sale_item_id,
                        'sale_id'      => $id,
                        'product_id'   => $item_id,
                        'option_id'    => $item_option,
                        'quantity'     => (0 - $item_quantity),
                        'warehouse_id' => $sale->warehouse_id,
                    ];

                    $products[] = ($product + $gst_data);
                    $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $order_discount = (0 - $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax)));
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            // $grand_total    = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($return_surcharge) + (0 - $shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $return_surcharge + (0 - $shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $data        = [
                'date'              => $date,
                'sale_id'           => $id,
                'reference_no'      => $sale->reference_no,
                'customer_id'       => $sale->customer_id,
                'customer'          => $sale->customer,
                'biller_id'         => $sale->biller_id,
                'biller'            => $sale->biller,
                'warehouse_id'      => $sale->warehouse_id,
                'note'              => $note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('discount') ? $this->input->post('order_discount') : null,
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'surcharge'         => $this->sma->formatDecimal($return_surcharge),
                'grand_total'       => $grand_total,
                'created_by'        => $this->session->userdata('user_id'),
                'return_sale_ref'   => $reference,
                'shipping'          => $shipping,
                'sale_status'       => 'returned',
                'pos'               => $sale->pos,
                'payment_status'    => $sale->payment_status == 'paid' ? 'due' : 'pending',
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }


                $comopago = $this->input->post('paid_by');
                 if($comopago=="CC")
                 {
                    $holder ="";
                 }
                 else
                 {
                    $holder = "efectivo";
                 }




            if ($this->input->post('amount-paid') && $this->input->post('amount-paid') > 0) {
                $pay_ref = $this->input->post('payment_reference_no') ? $this->input->post('payment_reference_no') : $this->site->getReference('pay');
                $payment = [
                    'date'         => $date,
                    'reference_no' => $pay_ref,
                    'amount'       => (0 - $this->input->post('amount-paid')),
                    'paid_by'      => $this->input->post('paid_by'),
                    'cheque_no'    => $this->input->post('cheque_no'),
                    'cc_no'        => $this->input->post('pcc_no'),
                    'cc_holder'    => $holder,
                    'cc_month'     => $this->input->post('pcc_month'),
                    'cc_year'      => $this->input->post('pcc_year'),
                    'cc_type'      => $this->input->post('pcc_type'),
                    'created_by'   => $this->session->userdata('user_id'),
                    'type'         => 'returned',
                ];
                $data['payment_status'] = $grand_total == $this->input->post('amount-paid') ? 'paid' : 'partial';
            } else {
                $payment = [];
            }

            $attachments        = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            // $this->sma->print_arrays($data, $products, $si_return, $payment);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment, $si_return, $attachments)) {
            $this->session->set_flashdata('message', lang('return_sale_added'));
            admin_redirect($sale->pos ? 'pos/sales' : 'sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $sale;
            if ($this->data['inv']->sale_status != 'completed') {
                $this->session->set_flashdata('error', lang('sale_status_x_competed'));
                redirect($_SERVER['HTTP_REFERER']);
            }
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang('sale_x_edited_older_than_x_days'), $this->Settings->disable_editing));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            $inv_items = $this->sales_model->getAllInvoiceItems($id);
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row             = json_decode('{}');
                    $row->tax_method = 0;
                    $row->quantity   = 0;
                } else {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
                $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) {
                    $row->quantity = 0;
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                $row->id              = $item->product_id;
                $row->sale_item_id    = $item->id;
                $row->code            = $item->product_code;
                $row->name            = $item->product_name;
                $row->type            = $item->product_type;
                $row->unit            = $item->product_unit_id;
                $row->qty             = $item->unit_quantity;
                $row->oqty            = $item->unit_quantity;
                $row->discount        = $item->discount ? $item->discount : '0';
                $row->item_tax        = $item->item_tax      > 0 ? $item->item_tax      / $item->quantity : 0;
                $row->item_discount   = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
                $row->price           = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->unit_quantity));
                $row->unit_price      = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->unit_quantity) - $this->sma->formatDecimal($item->item_tax / $item->unit_quantity) : $item->unit_price + ($item->item_discount / $item->unit_quantity);
                $row->real_unit_price = $item->real_unit_price;
                $row->base_quantity   = $item->quantity;
                $row->base_unit       = $row->unit       ?? $item->product_unit_id;
                $row->base_unit_price = $row->unit_price ?? $item->unit_price;
                $row->tax_rate        = $item->tax_rate_id;
                $row->serial          = $item->serial_no;
                $row->option          = $item->option_id;
                $options              = $this->sales_model->getProductOptions($row->id, $item->warehouse_id, true);
                $units                = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate             = $this->site->getTaxRateByID($row->tax_rate);
                $ri                   = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'row' => $row, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options];
                $c++;
            }
            $this->data['inv_items']   = json_encode($pr);
            $this->data['id']          = $id;
            $this->data['payment_ref'] = '';
            $this->data['reference']   = ''; // $this->site->getReference('re');
            $this->data['tax_rates']   = $this->site->getAllTaxRates();
            $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('return_sale')]];
            $meta                      = ['page_title' => lang('return_sale'), 'bc' => $bc];
            $this->page_construct('sales/return_sale', $meta, $this->data);
        }
    }

  


    public function sale_actions()
    {
        


        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) 
                    {


                         $ruta =  getcwd();
                         require $ruta."/app/config/database.php";

                         $sql_sales_borrar = "SELECT * FROM sma_sales s WHERE s.id=".$id;

                           $resultado_sigo_borrar = $conn->query($sql_sales_borrar);
                           while($sigob= $resultado_sigo_borrar->fetch_assoc() )
                           {$numero_comprobante=$sigob['numero_comprobante'];}

                                if($numero_comprobante <>"")
                                {
                                        
                                }




                                else
                                {
                                          $this->sales_model->deleteSale($id);
                                }



                       
                    }
                    $this->session->set_flashdata('message', lang('sales_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf($_POST['val']);
                } 


                elseif ($this->input->post('form_action') == 'enviarAfip') {

              

                   foreach ($_POST['val'] as $id) {$idventa=$id; }

                  admin_redirect('sales/afip?sale_id='.$idventa.'&tipo=sales');
                } 


 


                


               elseif ($this->input->post('form_action') == 'export_excel2') {

             


                              foreach ($_POST['val'] as $id) {
                                $sale_id=$id;



/*------------------------------------Afip---------------------------------------------*/





        $ruta =  getcwd();
        require $ruta."/app/config/database.php";
        $sql_sales = "SELECT * FROM sma_sales s WHERE s.id=".$sale_id." and  s.sale_status ='completed' and s.payment_status = 'paid'";


             $resultado_sigo = $conn->query($sql_sales);
             while($sigo= $resultado_sigo->fetch_assoc() )
              { 
                $CbteTipoSigo=$sigo['payment_method'];
                $numero_comprobante=$sigo['numero_comprobante'];
                $reference_no=$sigo['reference_no'];
                $date=$sigo['date']; 
                $total =$sigo['grand_total'];
             }



if($numero_comprobante <>"")
{
 //echo "<script> alert('Debe seleccionar tipo de comprobante');document.location.href='".$ruta."/admin/sales/';</script>";

}
else if($CbteTipoSigo =="")
{


}



               $resultado_sale = $conn->query($sql_sales);
               while($sal= $resultado_sale->fetch_assoc() )
               {
                   $customer_id =$sal['customer_id'];
                   $biller_id =$sal['biller_id'];
                   $recargo_tarjeta =$sal['recargo_tarjeta'];
                   
                   if($recargo_tarjeta > 0) 
                   {
                         $recargotarjeta = $recargo_tarjeta; // $10
                         $ivarecargotarjeta = $recargotarjeta * 0.173554 ;  // 2,1
                         $recargotarjetasiniva = $recargotarjeta - round($ivarecargotarjeta,3) ; // 10 - 2,1 = 7,90
                         $subtot =$sal['total'];
                         $subtotal =$subtot + $recargotarjetasiniva;
                         $mo_iva =$sal['total_tax'];
                         $monto_iva = $mo_iva + $ivarecargotarjeta;
                   }
                   else
                   {    
                             $subtotal =$sal['total'];
                             $monto_iva =$sal['total_tax'];


                   }
                   
                   $total =$sal['grand_total'];
                   $sale_status =$sal['sale_status'];
                   $CbteTipo=$sal['payment_method'];
                   $fechacarga=$sal['date'];



                               $sql_comprador = "SELECT * FROM sma_companies c WHERE c.id=".$customer_id;
                               $resultado_comprador = $conn->query($sql_comprador);
                               while($comp= $resultado_comprador->fetch_assoc() )
                                  {

                                        $nombre_comprador =$comp['name'];
                                        $razon_social =$comp['company'];
                                        
                                        $direccion_comprador =$comp['address'];
                                        $ciudad_comprador =$comp['city'];
                                        $estado_comprador =$comp['state'];
                                        $cp_comprador =$comp['postal_code'];
                                        $email_comprador =$comp['email'];

                                            if($comp['cf1']==99)
                                            {


                                              $tipodoc_comprador =$comp['cf1'];
                                                              if($nuevocuit <>'')
                                                                    {
                                                                     $numdoc_comprador = $nuevocuit ;
                                                                    }
                                                                    else
                                                                    {
                                                                     $  $numdoc_comprador =0;
                                                                    }




                                            }
                                            else
                                            {
                                              $tipodoc_comprador =$comp['cf1'];
                                            

                                               if($nuevocuit <>'')
                                                    {
                                                     $numdoc_comprador = $nuevocuit ;
                                                    }
                                                    else
                                                    {
                                                     $numdoc_comprador =str_replace("-","",$comp['vat_no']);
                                                    }
                                                            


                                            }
                                      
                                        

                                        $tipoiva_comprador =$comp['cf3'];
                                        $tiporesponsable_comprador =$comp['cf4'];

                                  }


                             $sql_vendedor = "SELECT *  FROM sma_companies  s WHERE s.group_name='biller' and s.gst_no<>''";

                               $resultado_vendedor = $conn->query($sql_vendedor);
                               while($vend= $resultado_vendedor->fetch_assoc() )
                                  {

                                        $nombre_vendedor =$vend['name'];
                                        $razon_social_vendedor =$vend['company'];
                                       

                                         $empresaCuit =str_replace("-","",$vend['vat_no']);



                                        $direccion_vendedor =$vend['address'];
                                        $ciudad_vendedor =$vend['city'];
                                        $estado_vendedor =$vend['state'];
                                        $cp_vendedor =$vend['postal_code'];
                                        $email_vendedor =$vend['email'];
                                        $tipodoc_vendedor =$vend['cf1'];
                                        $tipoiva_vendedor =$vend['cf3'];
                                        $tiporesponsable_vendedor =$vend['cf4'];
                                        $logo =$vend['logo'];
                                        $modoAfip =$vend['cf2'];
                                        $empresaAlias = $vend['gst_no'];  //alias de produccion 
                                        $PtoVta =$vend['cf6'];

                                        
                                        

                                  }



include_once  $ruta."/afip/config_test.php";
include_once  $ruta."/afip/test/functions.php";

//Cargando modelos de conexion a WebService
include_once  $ruta."/afip/AfipWsaa_test.php";
include_once  $ruta."/afip/AfipWsfev1.php";
include_once  $ruta."/afip/phpqrcode/qrlib.php";






  // Load classes
include_once $ruta.'/afip/src/Code39/Bar.php';
include_once $ruta.'/afip/src/Code39/Character.php';
include_once $ruta.'/afip/src/Code39/CharacterSequence.php';
include_once $ruta.'/afip/src/Code39/Generator.php';
include_once $ruta.'/afip/src/Code39/Parameters.php';



$Concepto = 3; //Productos y Servicios
$CbteFch = intval(date('Ymd'));
$FchServDesde = intval(date('Ymd'));
$FchServHasta = intval(date('Ymd'));
$FchVtoPago = intval(date('Ymd'));
$MonId = 'PES'; // Pesos (AR) - Ver - AfipWsfev1::FEParamGetTiposMonedas()
$MonCotiz = 1.00;




// facturacion afip

include_once  $ruta."/afip/config_test.php";
include_once  $ruta."/afip/test/functions.php";

//Cargando modelos de conexion a WebService
include_once  $ruta."/afip/AfipWsaa_test.php";
include_once  $ruta."/afip/AfipWsfev1.php";
include_once  $ruta."/afip/phpqrcode/qrlib.php";






  // Load classes
include_once $ruta.'/afip/src/Code39/Bar.php';
include_once $ruta.'/afip/src/Code39/Character.php';
include_once $ruta.'/afip/src/Code39/CharacterSequence.php';
include_once $ruta.'/afip/src/Code39/Generator.php';
include_once $ruta.'/afip/src/Code39/Parameters.php';



$Concepto = 3; //Productos y Servicios
$CbteFch = intval(date('Ymd'));
$FchServDesde = intval(date('Ymd'));
$FchServHasta = intval(date('Ymd'));
$FchVtoPago = intval(date('Ymd'));
$MonId = 'PES'; // Pesos (AR) - Ver - AfipWsfev1::FEParamGetTiposMonedas()
$MonCotiz = 1.00;


    //Informacion para agregar al array Tributos
    /** 
     * Esto aplica si las facturas tienen tributos agregados
     */




$stream_opts = [
    'ssl' => [
        'ciphers' => 'AES256-SHA',
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
];
$context = stream_context_create($stream_opts);
//WebService que utilizara la autenticacion
$webService   = 'wsfe';

//Creando el objeto WSAA (Web Service de Autenticación y Autorización)
//$wsaa = new AfipWsaa($webService,$empresaAlias);


$wsaa = new AfipWsaa($webService, $empresaAlias, $context);

//Creando el TA (Ticket de acceso)
if ($ta = $wsaa->loginCms())
{
    $token      = $ta['token'];
    $sign       = $ta['sign'];
    $expiration = $ta['expiration'];
    $uniqueid   = $ta['uniqueid'];
    // Conectando al WebService de Factura Electrónica (WsFev1)
    $wsfe = new AfipWsfev1($empresaCuit, $token, $sign, $context);
    //Conectando al WebService de Factura electronica (WsFev1)
 //   $wsfe = new AfipWsfev1($empresaCuit,$token,$sign);
    //Obteniendo el ultimo numero de comprobante autorizado
    $CompUltimoAutorizado = $wsfe->FECompUltimoAutorizado($PtoVta,$CbteTipo);
    // $CompUltimoAutorizado2 = $wsfe->FEParamGetTiposIva();
     //   pr($CompUltimoAutorizado);
   //    pr($CompUltimoAutorizado2);





                    $CbteDesde = $CompUltimoAutorizado['CbteNro'] + 1;
                    $CbteHasta = $CbteDesde;


            

                    
                      if($tiporesponsable_vendedor==1)

                      {



                          $montoIva=$monto_iva;
                          $ImpIVA = $montoIva;
                          $ImpTotal = $total;
                          $totalSinIVA = $subtotal;



                          $ImpNeto = $totalSinIVA;
                   

                      }
                      else
                      {


                        //monotributo 
                        $ImpIVA = 0.00;
                        $ImpTotal = $total;
                        $ImpNeto = $total;

                      }

                    $tributoId = null; // Ver - AfipWsfev1::FEParamGetTiposTributos()
                    $tributoDesc = null;
                    $tributoBaseImp = null;
                    $tributoAlic = null;
                    $tributoImporte = null;
                    $ImpTotConc = 0.00;
                    $ImpOpEx = 0.00;
                    $ImpTrib = 0.00;
                    $IvaAlicuotaId= 0.00;
                    $IvaAlicuotaBaseImp= 0.00;
                    $IvaAlicuotaImporte= 0.00;



    
 
        $FeCAEReq = array (
            'FeCAEReq' => array (
                'FeCabReq' => array (
                    'CantReg' => 1,
                    'CbteTipo' => $CbteTipo,
                    'PtoVta' => $PtoVta
                    ),
                'FeDetReq' => array (

                        'FECAEDetRequest' => array(
                        'Concepto' => $Concepto,
                        'DocTipo' => $tipodoc_comprador,
                        'DocNro' => $numdoc_comprador,
                        'CbteDesde' => $CbteDesde,
                        'CbteHasta' => $CbteHasta,
                        'CbteFch' => intval(date('Ymd')),
                        'FchServDesde' => intval(date('Ymd')), // Fechas desde cuando
                        'FchServHasta' => intval(date('Ymd')), // Fecha hasta cuando
                        'FchVtoPago' => intval(date('Ymd')), //  Fecha de pago
                        'ImpTotal' => number_format(abs($ImpTotal),2,'.',''), //total a cobrar
                        'ImpTotConc' => number_format(abs($ImpTotConc),2,'.',''), //
                        'ImpNeto' => number_format(abs($ImpNeto),2,'.',''), // importe neto no grabado
                        'ImpOpEx' => number_format(abs($ImpOpEx),2,'.',''), // importe exento
                        'ImpIVA' => number_format(abs($ImpIVA),2,'.',''), //importe con iva
                        'ImpTrib' => number_format(abs($ImpTrib),2,'.',''), // importe tributario
                        'MonId' => $MonId, // Moneda Argentina
                        'MonCotiz' => $MonCotiz // Cotización de la moneda
                        )
                    )
                ),
            );



        if (isset($Tributos) || isset($tributoBaseImp) || isset($tributoImporte))
        {
            if (empty($Tributos))
            {
                $Tributos = array(
                    'Tributo' => array (
                        'Id' => $tributoId,
                        'Desc' => $tributoDesc,
                        'BaseImp' => number_format(abs($tributoBaseImp),2,'.',''),
                        'Alic' => number_format(abs($tributoAlic),2,'.',''),
                        'Importe' => number_format(abs($tributoImporte),2,'.','')
                        )
                );
            }
            $FeCAEReq['FeCAEReq']['FeDetReq']['FECAEDetRequest']['Tributos'] = $Tributos;
        }

        // si es monotributo no informa iva 

     
        // si es monotributo no informa iva 

      if($tiporesponsable_vendedor==1)  // responsable inscripto
        {

         

         
              //    echo " es factura a o B";


         
/*-----------------------------Fact A o B -----------------------------------------*/
           


           // echo $sql_totalsiniva."<br>";


$sql_existe = "SELECT COUNT(s.id) AS cuenta  FROM sma_sales s WHERE s.id=".$sale_id;



 $resultado_cuenta = $conn->query($sql_existe); while($cuent= $resultado_cuenta->fetch_assoc() ){$cuenta =$cuent['cuenta'];}

if($cuenta >0)
{


    $sql_adelanto =" SELECT SUM(s.net_unit_price * s.unit_quantity) AS adelanto FROM sma_sale_items s WHERE s.sale_id=".$sale_id." AND s.net_unit_price * s.unit_quantity < 0";



     $resultado_adelanto = $conn->query($sql_adelanto); while($adel= $resultado_adelanto->fetch_assoc() ){$adelanto =$adel['adelanto'];}





      


             $sql_21="SELECT SUM(s.item_tax) AS iva21 FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=21" ;



          

            $resultado_21 = $conn->query($sql_21); while($vend21= $resultado_21->fetch_assoc() ){$IvaAlicuotaImporte21 =$vend21['iva21'];}



            $sql_21totalsiniva="SELECT SUM(s.net_unit_price * s.unit_quantity ) AS totalsinivaiva21  FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=21" ;




            $resultado_21totalsiniva = $conn->query($sql_21totalsiniva); while($vend21totalsiniva= $resultado_21totalsiniva->fetch_assoc() )
            {
                $IvaAlicuotaBaseImp21 =$vend21totalsiniva['totalsinivaiva21'] ;
            }





}
else
{

          

              $sql_21="SELECT SUM(s.item_tax) AS iva21 FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=21" ;



          //  echo $sql_21."<br>";

            $resultado_21 = $conn->query($sql_21); while($vend21= $resultado_21->fetch_assoc() ){$IvaAlicuotaImporte21 =$vend21['iva21'];}


            $sql_21totalsiniva="SELECT SUM(s.net_unit_price * s.unit_quantity ) AS totalsinivaiva21  FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=21" ;



              // echo $sql_21totalsiniva."<br>";


            $resultado_21totalsiniva = $conn->query($sql_21totalsiniva); while($vend21totalsiniva= $resultado_21totalsiniva->fetch_assoc() ){$IvaAlicuotaBaseImp21 =$vend21totalsiniva['totalsinivaiva21'];}




}


           



          




        /*--------------------------------------------------------------------------------------*/

            $sql_10="SELECT SUM(s.item_tax) AS iva10 FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=10.5" ;


           //    echo $sql_10."<br>";   

            $resultado_10 = $conn->query($sql_10); while($vend10= $resultado_10->fetch_assoc() ){$IvaAlicuotaImporte10 =$vend10['iva10'];}

              $sql_10totalsiniva="SELECT SUM(s.net_unit_price * s.unit_quantity ) AS totalsinivaiva10 FROM sma_sale_items s LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id  WHERE s.sale_id=".$sale_id." AND t.rate=10.5";



          //  echo $sql_10totalsiniva."<br>";   


            $resultado_10totalsiniva = $conn->query($sql_10totalsiniva); while($vend10totalsiniva= $resultado_10totalsiniva->fetch_assoc() ){$IvaAlicuotaBaseImp10 =$vend10totalsiniva['totalsinivaiva10'];}







        /*--------------------------------------------------------------------------------------*/



             $sql_exe="SELECT SUM(s.item_tax) AS exento FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=0" ;




            // echo $sql_exe."<br>";   


             $resultado_exe = $conn->query($sql_exe); while($vendexe= $resultado_exe->fetch_assoc() ){$IvaAlicuotaImporteexe =$vendexe['exento'];}



                  $sql_exetotalsiniva="SELECT SUM(s.net_unit_price * s.unit_quantity ) AS totalsinivaivaexe FROM sma_sale_items s LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id  WHERE s.sale_id=".$sale_id." AND t.rate=0";



          //  echo $sql_exetotalsiniva."<br>"; 
            $resultado_exetotalsiniva = $conn->query($sql_exetotalsiniva); while($vendexetotalsiniva= $resultado_exetotalsiniva->fetch_assoc() ) { $IvaAlicuotaBaseImpexe =$vendexetotalsiniva['totalsinivaivaexe'];}




        /*--------------------------------------------------------------------------------------*/





              


            $IvaAlicuotaBaseImpsuma = $IvaAlicuotaBaseImpexe  ;
            $IvaAlicuotaImportesuma =  (int)$IvaAlicuotaImporteexe ;

            

           //  echo "sin iva".$IvaAlicuotaBaseImpsuma;
           //  echo "iva de sin iva".$IvaAlicuotaImportesuma;

             // $IvaAlicuotaId = 5; // 21% Ver - AfipWsfev1::FEParamGetTiposIva()
                  

                    //$IvaAlicuotaBaseImp = $totalSinIVA;
                   // $IvaAlicuotaImporte = $montoIva;

                //    if (isset($Iva) || isset($IvaAlicuotaBaseImp21) || isset($IvaAlicuotaImporte21))
                  //  {



 if(($IvaAlicuotaImporte21>0) &&  ($IvaAlicuotaImporte10==0))
 {
// hay iva 21 pero no 10,5


                                               $AlicIva[0] =array(
                                                    "Id"=>5,
                                                    "BaseImp"=>number_format(abs($IvaAlicuotaBaseImp21),2,'.',''),
                                                    "Importe"=>number_format(abs($IvaAlicuotaImporte21),2,'.','')
                                                ); 
                                                    if($IvaAlicuotaBaseImpsuma>0)
                                                        {
                                                                  $AlicIva[1] =array(
                                                                                        "Id"=>3,
                                                                                        "BaseImp"=>number_format(abs($IvaAlicuotaBaseImpsuma),2,'.',''),
                                                                                        "Importe"=>number_format(abs($IvaAlicuotaImportesuma),2,'.','')
                                                                                    ); 
                                                        }




 }



 if(($IvaAlicuotaImporte21==0) &&  ($IvaAlicuotaImporte10>0))
 {
// no hay iva 21 y si hay 10,5

                                        $AlicIva[0] =array(
                                                    "Id"=>4,
                                                    "BaseImp"=>number_format(abs($IvaAlicuotaBaseImp10),2,'.',''),
                                                    "Importe"=>number_format(abs($IvaAlicuotaImporte10),2,'.','')
                                                ); 

                                             if($IvaAlicuotaBaseImpsuma>0)
                                                    {
                                                       
                                                              $AlicIva[1] =array(
                                                                                    "Id"=>3,
                                                                                    "BaseImp"=>number_format(abs($IvaAlicuotaBaseImpsuma),2,'.',''),
                                                                                    "Importe"=>number_format(abs($IvaAlicuotaImportesuma),2,'.','')
                                                                                ); 
                                                    }




    
 }


 if(($IvaAlicuotaImporte21>0) &&  ($IvaAlicuotaImporte10>0))
 {
 //  hay iva 21 y  hay 10,5

                                               $AlicIva[0] =array(
                                                    "Id"=>5,
                                                    "BaseImp"=>number_format(abs($IvaAlicuotaBaseImp21),2,'.',''),
                                                    "Importe"=>number_format(abs($IvaAlicuotaImporte21),2,'.','')
                                                ); 
                                               $AlicIva[1] =array(
                                                    "Id"=>4,
                                                    "BaseImp"=>number_format(abs($IvaAlicuotaBaseImp10),2,'.',''),
                                                    "Importe"=>number_format(abs($IvaAlicuotaImporte10),2,'.','')
                                                ); 

                                                    if($IvaAlicuotaBaseImpsuma>0)
                                                            {
                                                               
                                                                      $AlicIva[2] =array(
                                                                                            "Id"=>3,
                                                                                            "BaseImp"=>number_format(abs($IvaAlicuotaBaseImpsuma),2,'.',''),
                                                                                            "Importe"=>number_format(abs($IvaAlicuotaImportesuma),2,'.','')
                                                                                        ); 
                                                            }






 }



 if  ( ($IvaAlicuotaImporte21==0) &&  ($IvaAlicuotaImporte10==0) && ($IvaAlicuotaImportesuma==0) )
 {
 
      $AlicIva[0] =array(
                       "Id"=>3,
                             "BaseImp"=>number_format(abs($IvaAlicuotaBaseImpsuma),2,'.',''),
                             "Importe"=>number_format(abs($IvaAlicuotaImportesuma),2,'.','')
                         ); 

 }




                
             

/*-----------------------------Fact A -----------------------------------------*/

                

                  $Iva = array("AlicIva"=>$AlicIva);


                  $FeCAEReq['FeCAEReq']['FeDetReq']['FECAEDetRequest']['Iva'] = $Iva;



           



                  //  }


        } // fin si es responsable 1
/*
echo '
    <table>
        <caption>wsfe->FECAESolicitar(Request)</caption>
        <tr>
            <th >Request</th>
            <th >Response</th>
        </tr>
        <tr>
            <td>
    '; 
    pr($FeCAEReq);

    echo " 
            </td>
            <td> 
  ";     */
            //Registrando la factura electronica
            $FeCAEResponse = $wsfe->FECAESolicitar($FeCAEReq);

            /**/
           //     echo "tratamiento de errores<br>";
             

                if (!$FeCAEResponse)
                {
                    /* Procesando ERRORES */
                  

                //    echo '<h2 class="err">NO SE HA GENERADO EL CAE</h2>
                //         <h3 class="err">ERRORES DETECTADOS</h3>'; 

                    $errores = $wsfe->getErrLog();
                    if (isset($errores))
                    {
                        foreach ($errores as $v)
                        {
                             pr($v);

                        }
                    }
                   //   echo "<hr/><h3>Response</h3>";
                    

                }elseif (!$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE'])
                {
                    //   echo 'Procesando OBSERVACIONES <br>
                   //  <h2 class="msg">NO SE HA GENERADO EL CAE</h2>
                   //  <h3 class="msg">OBSERVACIONES INFORMADAS</h3>';  

                       

                    if (isset($FeCAEResponse['FeDetResp']['FECAEDetResponse']['Observaciones']))
                    {
                        foreach ($FeCAEResponse['FeDetResp']['FECAEDetResponse']['Observaciones'] as $v)
                        {
                              pr($v);

                          foreach ($v as $row) {
                                $code =  $row['Code'];
                                $msg = $row['Msg'];
                                
                            }



                        



                        }
                    }



                     echo "<hr/><h3>Response</h3>";
                    
                }
        else if($FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE'])

        { 
        
          $array_Concepto=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['Concepto'];
          $array_DocTipo=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['DocTipo'];
          $array_DocNro=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['DocNro'];
          $array_cbteDesde=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CbteDesde'];
          $array_cbteHasta=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CbteHasta'];
          $array_cbteFch=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CbteFch'];
          $array_resultado=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['Resultado'];
          $array_cae=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE'];
          $array_fechacaeven=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAEFchVto'];
         




                if($FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE']<>"")
                {


                 $mibarcode = $empresaCuit . sprintf('%03d', $CbteTipo) . sprintf('%05d', $PtoVta) .$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE'].$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAEFchVto']."1";


               /*     // Generate imagen del codigo de barra de afip
                    $gen = new \Code39\Generator;
                    $image = $gen->generate($mibarcode);
                    $nombre_imagen = $sale_id.'_'.$empresaCuit.'.png' ;
                    // Save image to file
                    
die();

                    imagepng($image, './assets/uploads/logos/afip/'.$nombre_imagen);

*/
                // genera imagen del codigo QR

                $url="https://www.afip.gob.ar/fe/qr/?p=";
                $arr = array('ver' =>1 ,'fecha' =>$fechacarga, 'cuit' =>$empresaCuit, 'ptoVta' =>$PtoVta, 'tipoCmp' =>$CbteTipo, 'nroCmp' =>$numero_comprobante, 'importe' =>$total, 'moneda' => 'PES',  'ctz' => 1,  'tipoDocRec' => $tipodoc_comprador, 'nroDocRec' =>$numdoc_comprador, 'tipoCodAut' => 'E','codAut' => $array_cae  );

                //echo json_encode($arr);
                $codigo = base64_encode(json_encode($arr));
                $codigo_para_qr= $url.$codigo;
                $codesDir = "./assets/uploads/logos/afip/";   
                $codeFile =  $sale_id.'_'.$array_cae.'.png';
                QRcode::png($codigo_para_qr, $codesDir.$codeFile, "H", 5); 
                  //  echo '<img class="img-thumbnail" src="'.$codesDir.$codeFile.'" style="width:150px;" />';


                $puntoventa = str_pad($PtoVta, 6, "0", STR_PAD_LEFT);  
                $comprobante = str_pad($CbteDesde, 8, "0", STR_PAD_LEFT);  
                $numero_comprobante = $puntoventa."-".$comprobante;


                $consulta = "update sma_sales sm set sm.CAE = '".$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE']."',sm.CAEFchVto = '".$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAEFchVto']."',sm.imagen_cae='".$nombre_imagen."',sm.numero_comprobante='".$numero_comprobante."',sm.imagen_qr = '".$codeFile."' where sm.id =".$sale_id;



                        $insert_tmp=mysqli_query($conn, $consulta);

                      

                }
             





         }

//  pr($FeCAEResponse);

 //   echo "
 //          </td>
 //    </tr>
 //  </table>
 //  ";   
}
else
{
/* echo '
    <hr/>
    <h3>Errores detectados al generar el Ticket de Acceso</h3>';
    pr($wsaa->getErrLog());    */ 
}





                } // fin del ciclo while de la sventas


/*------------------------------------Fin Afip---------------------------------------------*/


                               }  // del for de los id de venta



                        $this->session->set_flashdata('message', lang('Venta/s enviada/s con exito'));
                            admin_redirect('pos/sales');


              
                }  // fin del procedimiento







                elseif ($this->input->post('form_action') == 'export_excel') {



                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sales'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('payment_status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sale = $this->sales_model->getInvoiceByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($sale->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sale->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sale->biller);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sale->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sale->grand_total);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($sale->paid));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($sale->payment_status));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'sales_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }


             elseif ($this->input->post('form_action') == 'export_libro') {



$fechaini = $this->input->post('fechaini') ; 

$fechafin = $this->input->post('fechafin') ;




                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sales'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('Comprobante'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('Razon social'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('CI'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('Doc/Cuit'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('Neto gral.'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('Iva gral.'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('Neto red.'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('Iva red.'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('Iva otr.'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('Neto exen.'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('Neto N.Grav.'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('Iva N.Insc.'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('TOTAL'));

                    $row = 2;
              
        $ruta =  getcwd();
        require $ruta."/app/config/database.php";
        $sql_sales = "SELECT * FROM sma_sales s WHERE s.CAE >0 ";

         if(($fechaini <>"") && ($fechafin <>"") )

         {
            $sql_sales.=" and  s.date >='".$fechaini."' and s.date <='".$fechafin."'";
         }


        $sql_sales .= " ORDER BY s.date desc";



// echo $sql_sales."<br>";

             $resultado_sigo = $conn->query($sql_sales);
             while($roww= $resultado_sigo->fetch_assoc() )
              { 

                       $nota_credito = $roww['nota_credito'] ; 



                       $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($roww['date']));


                        $payment_method = $roww['payment_method'];

                        if($payment_method==1)   { $factura = "Fact A" ; }
                    
                         if($payment_method==6) { $factura = "Fact B" ; }
                         if($payment_method==11)  { $factura = "Fact C" ; } 
                        
                     
                         if($nota_credito=="nc")
                         {

                          if($payment_method==1)   { $facturanc = "Nota Cred A" ; }
                    
                         if($payment_method==6) { $facturanc = "Nota Cred B" ; }
                         if($payment_method==11)  { $facturanc = "Nota Cred C" ; } 



                            $this->excel->getActiveSheet()->SetCellValue('B' . $row, $facturanc." ".$roww['numero_comprobante']);
                         }
                         else
                         {
                             $this->excel->getActiveSheet()->SetCellValue('B' . $row, $factura." ".$roww['numero_comprobante']);

                         }
                       


                           $this->excel->getActiveSheet()->SetCellValue('C' . $row, $roww['customer']);

                        $customer_id = $roww['customer_id'];

                        $sql_sales_busqueda = "SELECT c.cf4 ,c.vat_no FROM sma_companies c WHERE c.id =".$customer_id;


                         $resultado_busqueda = $conn->query($sql_sales_busqueda);
                             while($busqueda= $resultado_busqueda->fetch_assoc() )
                                       { $cf4=$busqueda['cf4'] ;

                                        $vat_no =str_replace("-","",$busqueda['vat_no']);


                                         }

                        if($cf4==1) {  $tipo="RI" ;}

                        if($cf4==2) {  $tipo="RNI" ;}

                        if($cf4==4) {  $tipo="ISE" ;}


                        if($cf4==5) {  $tipo="CF" ;}


                        if($cf4==6) {  $tipo="RM" ;}

                        $sale_id = $roww['id']; 

                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $tipo);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $vat_no);

                          /*--------------------------------------------------------------------------------------*/

                            $sql_10="SELECT SUM(s.item_tax) AS iva10 FROM sma_sale_items s 
                        LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=10.5" ;


              //  echo $sql_10."<br>";   

                       $resultado_10 = $conn->query($sql_10); while($vend10= $resultado_10->fetch_assoc() ){$IvaAlicuotaImporte10 =$vend10['iva10'];}

                      $sql_10totalsiniva="SELECT SUM(s.net_unit_price * s.unit_quantity ) AS totalsinivaiva10 FROM sma_sale_items s LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id  WHERE s.sale_id=".$sale_id." AND t.rate=10.5";

                        $resultado_10totalsiniva = $conn->query($sql_10totalsiniva); while($vend10totalsiniva= $resultado_10totalsiniva->fetch_assoc() ){$IvaAlicuotaBaseImp10 =$vend10totalsiniva['totalsinivaiva10'];}
                          $this->excel->getActiveSheet()->SetCellValue('F' . $row, $IvaAlicuotaBaseImp10);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $IvaAlicuotaImporte10);



                         /*--------------------------------------------------------------------------------------*/
                  $sql_21="SELECT SUM(s.item_tax) AS iva21 FROM sma_sale_items s 
                   LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=21" ;



          //  echo $sql_21."<br>";

                  $resultado_21 = $conn->query($sql_21); while($vend21= $resultado_21->fetch_assoc() ){$IvaAlicuotaImporte21 =$vend21['iva21'];}


                  $sql_21totalsiniva="SELECT SUM(s.net_unit_price * s.unit_quantity ) AS totalsinivaiva21  FROM sma_sale_items s   LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=21" ;



              //  echo $sql_21totalsiniva."<br>";


            $resultado_21totalsiniva = $conn->query($sql_21totalsiniva); while($vend21totalsiniva= $resultado_21totalsiniva->fetch_assoc() ){$IvaAlicuotaBaseImp21 =$vend21totalsiniva['totalsinivaiva21'];}



        /*--------------------------------------------------------------------------------------*/       

                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $IvaAlicuotaBaseImp21);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $IvaAlicuotaImporte21);

                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, "");
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, "");
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, "");
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, "");
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, $IvaAlicuotaBaseImp10 +  $IvaAlicuotaImporte10 + $IvaAlicuotaBaseImp21 + $IvaAlicuotaImporte21 );



                   $row++;



                
              }
                    
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'libroiva_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
             



                }  //  si es libro

                





            } else {
                $this->session->set_flashdata('error', lang('no_sale_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    /* -------------------------------------------------------------------------------------- */




    public function sale_by_csv()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days')) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = $customer_details->company && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = $biller_details->company && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->sma->clear_tags($this->input->post('note'));
            $staff_note       = $this->sma->clear_tags($this->input->post('staff_note'));

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('sales/sale_by_csv');
                }
                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen($this->digital_upload_path . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $arr_length = count($arrResult);
                if ($arr_length > 499) {
                    $this->session->set_flashdata('error', lang('too_many_products'));
                    redirect($_SERVER['HTTP_REFERER']);
                    exit();
                }
                $titles = array_shift($arrResult);
                $keys   = ['code', 'net_unit_price', 'quantity', 'variant', 'item_tax_rate', 'discount', 'serial'];
                $final  = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if (isset($csv_pr['code']) && isset($csv_pr['net_unit_price']) && isset($csv_pr['quantity'])) {
                        if ($product_details = $this->sales_model->getProductByCode($csv_pr['code'])) {
                            if ($csv_pr['variant']) {
                                $item_option = $this->sales_model->getProductVariantByName($csv_pr['variant'], $product_details->id);
                                if (!$item_option) {
                                    $this->session->set_flashdata('error', lang('pr_not_found') . ' ( ' . $product_details->name . ' - ' . $csv_pr['variant'] . ' ). ' . lang('line_no') . ' ' . $rw);
                                    redirect($_SERVER['HTTP_REFERER']);
                                }
                            } else {
                                $item_option     = json_decode('{}');
                                $item_option->id = null;
                            }

                            $item_id        = $product_details->id;
                            $item_type      = $product_details->type;
                            $item_code      = $product_details->code;
                            $item_name      = $product_details->name;
                            $item_net_price = $this->sma->formatDecimal($csv_pr['net_unit_price']);
                            $item_quantity  = $csv_pr['quantity'];
                            $item_tax_rate  = $csv_pr['item_tax_rate'];
                            $item_discount  = $csv_pr['discount'];
                            $item_serial    = $csv_pr['serial'];

                            if (isset($item_code) && isset($item_net_price) && isset($item_quantity)) {
                                $product_details  = $this->sales_model->getProductByCode($item_code);
                                $pr_discount      = $this->site->calculateDiscount($item_discount, $item_net_price);
                                $item_net_price   = $this->sma->formatDecimal(($item_net_price - $pr_discount), 4);
                                $pr_item_discount = $this->sma->formatDecimal(($pr_discount * $item_quantity), 4);
                                $product_discount += $pr_item_discount;

                                $tax         = '';
                                $pr_item_tax = 0;
                                $unit_price  = $item_net_price;
                                $tax_details = ((isset($item_tax_rate) && !empty($item_tax_rate)) ? $this->sales_model->getTaxRateByName($item_tax_rate) : $this->site->getTaxRateByID($product_details->tax_rate));
                                if ($tax_details) {
                                    $ctax     = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                                    $item_tax = $this->sma->formatDecimal($ctax['amount']);
                                    $tax      = $ctax['tax'];
                                    // $this->sma->print_arrays($product_details);
                                    // if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                                    $unit_price = $unit_price + $item_tax;
                                    // }
                                    $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_quantity, 4);
                                    if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                                        $total_cgst += $gst_data['cgst'];
                                        $total_sgst += $gst_data['sgst'];
                                        $total_igst += $gst_data['igst'];
                                    }
                                }

                                $product_tax += $pr_item_tax;
                                $subtotal = $this->sma->formatDecimal(($unit_price * $item_quantity), 4);
                                $unit     = $this->site->getUnitByID($product_details->unit);

                                $product = [
                                    'product_id'        => $product_details->id,
                                    'product_code'      => $item_code,
                                    'product_name'      => $item_name,
                                    'product_type'      => $item_type,
                                    'option_id'         => $item_option->id,
                                    'net_unit_price'    => $item_net_price,
                                    'quantity'          => $item_quantity,
                                    'product_unit_id'   => $product_details->unit,
                                    'product_unit_code' => $unit->code,
                                    'unit_quantity'     => $item_quantity,
                                    'warehouse_id'      => $warehouse_id,
                                    'item_tax'          => $pr_item_tax,
                                    'tax_rate_id'       => $tax_details ? $tax_details->id : null,
                                    'tax'               => $tax,
                                    'discount'          => $item_discount,
                                    'item_discount'     => $pr_item_discount,
                                    'subtotal'          => $subtotal,
                                    'serial_no'         => $item_serial,
                                    'unit_price'        => $this->sma->formatDecimal($unit_price, 4),
                                    'real_unit_price'   => $this->sma->formatDecimal(($unit_price + $pr_discount), 4),
                                ];

                                $products[] = ($product + $gst_data);
                                $total += $this->sma->formatDecimal(($item_net_price * $item_quantity), 4);
                            }
                        } else {
                            $this->session->set_flashdata('error', lang('pr_not_found') . ' ( ' . $csv_pr['code'] . ' ). ' . lang('line_no') . ' ' . $rw);
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                        $rw++;
                    }
                }
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax), true);
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            // $grand_total    = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $data        = ['date'  => $date,
                'reference_no'      => $reference,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->sma->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'total_items'       => $total_items,
                'sale_status'       => $sale_status,
                'payment_status'    => $payment_status,
                'payment_term'      => $payment_term,
                'due_date'          => $due_date,
                'paid'              => 0,
                'created_by'        => $this->session->userdata('user_id'),
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            if ($payment_status == 'paid') {
                $payment = [
                    'date'         => $date,
                    'reference_no' => $this->site->getReference('pay'),
                    'amount'       => $grand_total,
                    'paid_by'      => 'cash',
                    'cheque_no'    => '',
                    'cc_no'        => '',
                    'cc_holder'    => '',
                    'cc_month'     => '',
                    'cc_year'      => '',
                    'cc_type'      => '',
                    'created_by'   => $this->session->userdata('user_id'),
                    'note'         => lang('auto_added_for_sale_by_csv') . ' (' . lang('sale_reference_no') . ' ' . $reference . ')',
                    'type'         => 'received',
                ];
            } else {
                $payment = [];
            }

            $attachments        = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            // $this->sma->print_arrays($data, $products, $payment);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment, [], $attachments)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('sale_added'));
            admin_redirect('sales');
        } else {
            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['billers']    = $this->site->getAllCompanies('biller');
            $this->data['slnumber']   = $this->site->getReference('so');

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('add_sale_by_csv')]];
            $meta = ['page_title' => lang('add_sale_by_csv'), 'bc' => $bc];
            $this->page_construct('sales/sale_by_csv', $meta, $this->data);
        }
    }

    public function sell_gift_card()
    {
        $this->sma->checkPermissions('gift_cards', true);
        $error  = null;
        $gcData = $this->input->get('gcdata');
        if (empty($gcData[0])) {
            $error = lang('value') . ' ' . lang('is_required');
        }
        if (empty($gcData[1])) {
            $error = lang('card_no') . ' ' . lang('is_required');
        }

        $customer_details = (!empty($gcData[2])) ? $this->site->getCompanyByID($gcData[2]) : null;
        $customer         = $customer_details ? $customer_details->company : null;
        $data             = ['card_no' => $gcData[0],
            'value'                    => $gcData[1],
            'customer_id'              => (!empty($gcData[2])) ? $gcData[2] : null,
            'customer'                 => $customer,
            'balance'                  => $gcData[1],
            'expiry'                   => (!empty($gcData[3])) ? $this->sma->fsd($gcData[3]) : null,
            'created_by'               => $this->session->userdata('user_id'),
        ];

        if (!$error) {
            if ($this->sales_model->addGiftCard($data)) {
                $this->sma->send_json(['result' => 'success', 'message' => lang('gift_card_added')]);
            }
        } else {
            $this->sma->send_json(['result' => 'failed', 'message' => $error]);
        }
    }

    /* --------------------------------------------------------------------------------------------- */

    public function suggestions($pos = 0)
    {
        

        $cadena         = $this->input->get('term', true);




        if (strpos($cadena, '*') !== false) {

        $arrayPalabras = explode("*",$cadena);
        //echo $arrayPalabras[0]."<br>";
        $term= $arrayPalabras[1];


                if (strlen($term) < 1 || !$term) {
                    die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
                }

                $analyzed  = $this->sma->analyze_term($term);
                $sr        = $analyzed['term'];
                $option_id = $analyzed['option_id'];
                $sr        = addslashes($sr);
                $strict    = $analyzed['strict']                    ?? false;
              //  $qty       = $strict ? null : $analyzed['quantity'] ?? null;

                $qty       = $arrayPalabras[0];


           
        }
        else

        {

               $term = $this->input->get('term', true);

                if (strlen($term) < 1 || !$term) {
                    die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
                }

                $analyzed  = $this->sma->analyze_term($term);
                $sr        = $analyzed['term'];
                $option_id = $analyzed['option_id'];
                $sr        = addslashes($sr);
                $strict    = $analyzed['strict']                    ?? false;
                $qty       = $strict ? null : $analyzed['quantity'] ?? null;

            



        }




        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id  = $this->input->get('customer_id', true)    ;







        $bprice    = $strict ? null : $analyzed['price']    ?? null;

        $warehouse      = $this->site->getWarehouseByID($warehouse_id);
        $customer       = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows           = $this->sales_model->getProductNames($sr, $warehouse_id, $pos);
        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $c = uniqid(mt_rand(), true);
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option               = false;
                $row->quantity        = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty             = 1;
                $row->discount        = '0';
                $row->serial          = '';
                $options              = $this->sales_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->sales_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt        = json_decode('{}');
                    $opt->price = 0;
                    $option_id  = false;
                }
                $row->option = $option_id;
                $pis         = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                if ($pis) {
                    $row->quantity = 0;
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
                        if ($option->quantity > $option_quantity) {
                            $option->quantity = $option_quantity;
                        }
                    }
                }

                // aqui toma el precio del customer
                /*------------------------------------------*/

                if ($this->sma->isPromo($row)) {
                    $row->price = $row->promo_price;
                } elseif ($customer->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                } elseif ($warehouse->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                }


                /*------------------------------------------*/



                if ($customer_group->discount && $customer_group->percent < 0) {
                    $row->discount = (0 - $customer_group->percent) . '%';
                } else {
                    $row->price = $row->price + (($row->price * $customer_group->percent) / 100);
                }
                $row->real_unit_price = $row->price;
                $row->base_quantity   = 1;
                $row->base_unit       = $row->unit;
                $row->base_unit_price = $row->price;
                $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->comment         = '';
                $combo_items          = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $warehouse_id);
                }
                if ($qty) {
                    $row->qty           = $qty;
                    $row->base_quantity = $qty;
                } else {
                    $row->qty = ($bprice ? $bprice / $row->price : 1);
                }
                $units    = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

              
                             $pr[] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (#code:' . $row->code . ') - (#Stock:'.$row->quantity.') - (#Precio:'.$row->price.')'      , 'category' => $row->category_id,
                    'row'     => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, ];
                                  
                         
                 
                
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function topup_gift_card($card_id)
    {
        $this->sma->checkPermissions('add_gift_card', true);
        $card = $this->site->getGiftCardByID($card_id);
        $this->form_validation->set_rules('amount', lang('amount'), 'trim|integer|required');

        if ($this->form_validation->run() == true) {
            $data = ['card_id' => $card_id,
                'amount'       => $this->input->post('amount'),
                'date'         => date('Y-m-d H:i:s'),
                'created_by'   => $this->session->userdata('user_id'),
            ];
            $card_data['balance'] = ($this->input->post('amount') + $card->balance);
            // $card_data['value'] = ($this->input->post('amount')+$card->value);
            if ($this->input->post('expiry')) {
                $card_data['expiry'] = $this->sma->fld(trim($this->input->post('expiry')));
            }
        } elseif ($this->input->post('topup')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('sales/gift_cards');
        }

        if ($this->form_validation->run() == true && $this->sales_model->topupGiftCard($data, $card_data)) {
            $this->session->set_flashdata('message', lang('topup_added'));
            admin_redirect('sales/gift_cards');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['card']       = $card;
            $this->data['page_title'] = lang('topup_gift_card');
            $this->load->view($this->theme . 'sales/topup_gift_card', $this->data);
        }
    }

    public function update_status($id)
    {
        $this->sma->checkPermissions('edit', true);
        $this->form_validation->set_rules('status', lang('sale_status'), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note   = $this->sma->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'sales');
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'sales');
        } else {
            $this->data['inv']      = $this->sales_model->getInvoiceByID($id);
            $this->data['returned'] = false;
            if ($this->data['inv']->sale_status == 'returned' || $this->data['inv']->return_id) {
                $this->data['returned'] = true;
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/update_status', $this->data);
        }
    }

    public function validate_gift_card($no)
    {
        //$this->sma->checkPermissions();
        if ($gc = $this->site->getGiftCardByNO($no)) {
            if ($gc->expiry) {
                if ($gc->expiry >= date('Y-m-d')) {
                    $this->sma->send_json($gc);
                } else {
                    $this->sma->send_json(false);
                }
            } else {
                $this->sma->send_json($gc);
            }
        } else {
            $this->sma->send_json(false);
        }
    }

    public function view($id = null)
    {
        $this->sma->checkPermissions('index');
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['paypal']      = $this->sales_model->getPaypalSettings();
        $this->data['skrill']      = $this->sales_model->getSkrillSettings();
        $this->data['attachments'] = $this->site->getAttachments($id, 'sale');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_sales_details'), 'bc' => $bc];
        $this->page_construct('sales/view', $meta, $this->data);
    }

    public function view_delivery($id = null)
    {
        $this->sma->checkPermissions('deliveries');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli                = $this->sales_model->getDeliveryByID($id);
        $sale                = $this->sales_model->getInvoiceByID($deli->sale_id);
        if (!$sale) {
            $this->session->set_flashdata('error', lang('sale_not_found'));
            $this->sma->md();
        }
        $this->data['delivery']   = $deli;
        $this->data['biller']     = $this->site->getCompanyByID($sale->biller_id);
        $this->data['rows']       = $this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
        $this->data['user']       = $this->site->getUser($deli->created_by);
        $this->data['page_title'] = lang('delivery_order');

        $this->load->view($this->theme . 'sales/view_delivery', $this->data);
    }

    public function view_gift_card($id = null)
    {
        $this->data['page_title'] = lang('gift_card');
        $gift_card                = $this->site->getGiftCardByID($id);
        $this->data['gift_card']  = $this->site->getGiftCardByID($id);
        $this->data['customer']   = $this->site->getCompanyByID($gift_card->customer_id);
        $this->data['topups']     = $this->sales_model->getAllGCTopups($id);
        $this->load->view($this->theme . 'sales/view_gift_card', $this->data);
    }




/*++++++++++++++++++++++++++++++++++AFIP+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/


 public function afipNTC($quote_id = null)
    {
     





$sale_id = $this->input->post('sale_id') ? $this->input->post('sale_id') : null;
$CbteTiponc = $this->input->post('payment_method') ? $this->input->post('payment_method') : null;




$ruta =  getcwd();
require $ruta."/app/config/database.php";


//$sql_sales_busqueda = "SELECT * FROM sma_sales s WHERE s.id=".$sale_id." and  s.sale_status ='completed' and s.payment_status = 'paid'";


$sql_sales_busqueda = "SELECT * FROM sma_sales s WHERE s.id=".$sale_id." and  s.sale_status ='completed' ";




$resultado_busqueda = $conn->query($sql_sales_busqueda);
     while($busqueda= $resultado_busqueda->fetch_assoc() )
               { $notacredito=$busqueda['nota_credito'] ;}




if($notacredito <> "nc")
{ // if nota de credito
    //echo "puede continuar";

    

    $sql_sales = "SELECT * FROM sma_sales s WHERE s.id=".$sale_id." and  s.sale_status ='completed' and s.payment_status = 'paid' and s.nota_credito =0 ";




     $resultado_sale = $conn->query($sql_sales);
               while($sal= $resultado_sale->fetch_assoc() )
               { // (3)
                   $customer_id =$sal['customer_id'];
                   $biller_id =$sal['biller_id'];
                   $reference_no =$sal['reference_no'];
                   $monto_iva =$sal['total_tax'];
                   $subtotal =$sal['total'];
                   $total =$sal['grand_total'];
                   $recargo_tarjeta =$sal['recargo_tarjeta'];

                   
                   
                   if($recargo_tarjeta > 0) 
                   {
                         $recargotarjeta = $recargo_tarjeta; // $10

                         $ivarecargotarjeta = $recargotarjeta * 0.173554 ;  // 2,1

                           $recargotarjetasiniva = $recargotarjeta - round($ivarecargotarjeta,3) ; // 10 - 2,1 = 7,90


                           

                           $subtot =$sal['total'];
                           $subtotal =$subtot + $recargotarjetasiniva;

                            $mo_iva =$sal['total_tax'];
                            $monto_iva = $mo_iva + $ivarecargotarjeta;




                   }
                   else
                   {    
                             $subtotal =$sal['total'];
                             $monto_iva =$sal['total_tax'];


                   }


                   $sale_status =$sal['sale_status'];
                   $CbteTipo=$sal['payment_method'];
                   $fechacarga=$sal['date'];
                   $numero_comprobante=$sal['numero_comprobante'];

                     $sql_comprador = "SELECT * FROM sma_companies c WHERE c.id=".$customer_id;


                               $resultado_comprador = $conn->query($sql_comprador);
                               while($comp= $resultado_comprador->fetch_assoc() )
                                  {  // (2)

                                        $nombre_comprador =$comp['name'];
                                        $razon_social =$comp['company'];
                                       


                                          $numdoc_comprador =str_replace("-","",$comp['vat_no']);


                                        $direccion_comprador =$comp['address'];
                                        $ciudad_comprador =$comp['city'];
                                        $estado_comprador =$comp['state'];
                                        $cp_comprador =$comp['postal_code'];
                                        $email_comprador =$comp['email'];
                                        $tipodoc_comprador =$comp['cf1'];
                                        $tipoiva_comprador =$comp['cf3'];
                                        $tiporesponsable_comprador =$comp['cf4'];

                                  } // (2)



                                  // parte del vendedor 


                 //  $sql_vendedor = "SELECT * FROM sma_companies c WHERE c.id=".$biller_id." and group_name='biller'";
                 //  echo $sql_vendedor."<br>";

                    $sql_vendedor = "SELECT * FROM sma_companies  s WHERE s.group_name='biller'";



                               $resultado_vendedor = $conn->query($sql_vendedor);
                               while($vend= $resultado_vendedor->fetch_assoc() )
                                  {  // (1)

                                        $nombre_vendedor =$vend['name'];
                                        $razon_social =$vend['company'];
                                     

                                          $empresaCuit =str_replace("-","",$vend['vat_no']);


                                        $direccion_vendedor =$vend['address'];
                                        $ciudad_vendedor =$vend['city'];
                                        $estado_vendedor =$vend['state'];
                                        $cp_vendedor =$vend['postal_code'];
                                        $email_vendedor =$vend['email'];
                                        $tipodoc_vendedor =$vend['cf1'];
                                        $tipoiva_vendedor =$vend['cf3'];
                                        $tiporesponsable_vendedor =$vend['cf4'];
                                        $logo =$vend['logo'];
//                                        $empresaAlias =$vend['cf5'];
                                         $empresaAlias = $vend['gst_no']; //alias de produccion 

                                        $PtoVta =$vend['cf6'];
                                        
                                        

                                  } // (1)




/*----------------------------------AFIP-----------------------------*/


// facturacion afip

include_once  $ruta."/afip/config_test.php";
include_once  $ruta."/afip/test/functions.php";

//Cargando modelos de conexion a WebService
include_once  $ruta."/afip/AfipWsaa_test.php";
include_once  $ruta."/afip/AfipWsfev1.php";
include_once  $ruta."/afip/phpqrcode/qrlib.php";






  // Load classes
include_once $ruta.'/afip/src/Code39/Bar.php';
include_once $ruta.'/afip/src/Code39/Character.php';
include_once $ruta.'/afip/src/Code39/CharacterSequence.php';
include_once $ruta.'/afip/src/Code39/Generator.php';
include_once $ruta.'/afip/src/Code39/Parameters.php';



$Concepto = 3; //Productos y Servicios
$CbteFch = intval(date('Ymd'));
$FchServDesde = intval(date('Ymd'));
$FchServHasta = intval(date('Ymd'));
$FchVtoPago = intval(date('Ymd'));
$MonId = 'PES'; // Pesos (AR) - Ver - AfipWsfev1::FEParamGetTiposMonedas()
$MonCotiz = 1.00;


    //Informacion para agregar al array Tributos
    /** 
     * Esto aplica si las facturas tienen tributos agregados
     */



$stream_opts = [
    'ssl' => [
        'ciphers' => 'AES256-SHA',
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
];
$context = stream_context_create($stream_opts);
//WebService que utilizara la autenticacion
$webService   = 'wsfe';

//Creando el objeto WSAA (Web Service de Autenticación y Autorización)
//$wsaa = new AfipWsaa($webService,$empresaAlias);


$wsaa = new AfipWsaa($webService, $empresaAlias, $context);

//Creando el TA (Ticket de acceso)
if ($ta = $wsaa->loginCms())
{
    $token      = $ta['token'];
    $sign       = $ta['sign'];
    $expiration = $ta['expiration'];
    $uniqueid   = $ta['uniqueid'];
    // Conectando al WebService de Factura Electrónica (WsFev1)
    $wsfe = new AfipWsfev1($empresaCuit, $token, $sign, $context);
    //Conectando al WebService de Factura electronica (WsFev1)
 //   $wsfe = new AfipWsfev1($empresaCuit,$token,$sign);
    //Obteniendo el ultimo numero de comprobante autorizado
    $CompUltimoAutorizado = $wsfe->FECompUltimoAutorizado($PtoVta,$CbteTiponc);
    // $CompUltimoAutorizado2 = $wsfe->FEParamGetTiposIva();
     //   pr($CompUltimoAutorizado);
   //    pr($CompUltimoAutorizado2);




                    $CbteDesde = $CompUltimoAutorizado['CbteNro'] + 1;
                    $CbteHasta = $CbteDesde;
                    





                      if($tiporesponsable_vendedor==1)

                      {
                          $montoIva=$monto_iva;
                          $ImpIVA = $montoIva;
                          $ImpTotal = $total;
                          $totalSinIVA = $subtotal;
                          $ImpNeto = $totalSinIVA;
                   

                      }
                      else
                      {
                        $ImpIVA = 0.00;
                        $ImpTotal = $total;
                        $ImpNeto = $total;

                      }

                    $tributoId = null; // Ver - AfipWsfev1::FEParamGetTiposTributos()
                    $tributoDesc = null;
                    $tributoBaseImp = null;
                    $tributoAlic = null;
                    $tributoImporte = null;
                    $ImpTotConc = 0.00;
                    $ImpOpEx = 0.00;
                    $ImpTrib = 0.00;
                    $IvaAlicuotaId= 0.00;
                    $IvaAlicuotaBaseImp= 0.00;
                    $IvaAlicuotaImporte= 0.00;



$numero_comprobante_modificado  =  substr($numero_comprobante, 7);

      
    
 
        $FeCAEReq = array (
            'FeCAEReq' => array (
                'FeCabReq' => array (
                    'CantReg' => 1,
                    'CbteTipo' => $CbteTiponc,
                    'PtoVta' => $PtoVta
                    ),
                'FeDetReq' => array (

                        'FECAEDetRequest' => array(
                        'Concepto' => $Concepto,
                        'DocTipo' => $tipodoc_comprador,
                        'DocNro' => $numdoc_comprador,
                        'CbteDesde' => $CbteDesde,
                        'CbteHasta' => $CbteHasta,
                        'CbteFch' => intval(date('Ymd')),
                        'FchServDesde' => intval(date('Ymd')), // Fechas desde cuando
                        'FchServHasta' => intval(date('Ymd')), // Fecha hasta cuando
                        'FchVtoPago' => intval(date('Ymd')), //  Fecha de pago
                        'ImpTotal' => number_format(abs($ImpTotal),2,'.',''), //total a cobrar
                        'ImpTotConc' => number_format(abs($ImpTotConc),2,'.',''), //
                        'ImpNeto' => number_format(abs($ImpNeto),2,'.',''), // importe neto no grabado
                        'ImpOpEx' => number_format(abs($ImpOpEx),2,'.',''), // importe exento
                        'ImpIVA' => number_format(abs($ImpIVA),2,'.',''), //importe con iva
                        'ImpTrib' => number_format(abs($ImpTrib),2,'.',''), // importe tributario
                        'MonId' => $MonId, // Moneda Argentina
                        'MonCotiz' => $MonCotiz ,// Cotización de la moneda

                        'CbtesAsoc'=>array(
                                     'CbteAsoc' => array (
                                                'Tipo' => $CbteTipo,  // es el comprobante de la venta
                                                'PtoVta' => $PtoVta,
                                                'Nro' => $numero_comprobante_modificado)
                                          )
                                          

                        )


                    )
                ),
            );







        if (isset($Tributos) || isset($tributoBaseImp) || isset($tributoImporte))
        {
            if (empty($Tributos))
            {
                $Tributos = array(
                    'Tributo' => array (
                        'Id' => $tributoId,
                        'Desc' => $tributoDesc,
                        'BaseImp' => number_format(abs($tributoBaseImp),2,'.',''),
                        'Alic' => number_format(abs($tributoAlic),2,'.',''),
                        'Importe' => number_format(abs($tributoImporte),2,'.','')
                        )
                );
            }
            $FeCAEReq['FeCAEReq']['FeDetReq']['FECAEDetRequest']['Tributos'] = $Tributos;
        }




        // si es monotributo no informa iva 

     
        // si es monotributo no informa iva 
   if($tiporesponsable_vendedor==1)  // responsable inscripto
        {

         

         
              //    echo " es factura a o B";


         
/*-----------------------------Fact A o B -----------------------------------------*/
           


           // echo $sql_totalsiniva."<br>";


$sql_existe = "SELECT COUNT(s.id) AS cuenta  FROM sma_sales s WHERE s.id=".$sale_id;



 $resultado_cuenta = $conn->query($sql_existe); while($cuent= $resultado_cuenta->fetch_assoc() ){$cuenta =$cuent['cuenta'];}

if($cuenta >0)
{


    $sql_adelanto =" SELECT SUM(s.net_unit_price * s.unit_quantity) AS adelanto FROM sma_sale_items s WHERE s.sale_id=".$sale_id." AND s.net_unit_price * s.unit_quantity < 0";



     $resultado_adelanto = $conn->query($sql_adelanto); while($adel= $resultado_adelanto->fetch_assoc() ){$adelanto =$adel['adelanto'];}





      


             $sql_21="SELECT SUM(s.item_tax) AS iva21 FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=21" ;



          

            $resultado_21 = $conn->query($sql_21); while($vend21= $resultado_21->fetch_assoc() ){$IvaAlicuotaImporte21 =$vend21['iva21'];}



            $sql_21totalsiniva="SELECT SUM(s.net_unit_price * s.unit_quantity ) AS totalsinivaiva21  FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=21" ;




            $resultado_21totalsiniva = $conn->query($sql_21totalsiniva); while($vend21totalsiniva= $resultado_21totalsiniva->fetch_assoc() )
            {
                $IvaAlicuotaBaseImp21 =$vend21totalsiniva['totalsinivaiva21'] ;
            }





}
else
{

          

              $sql_21="SELECT SUM(s.item_tax) AS iva21 FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=21" ;



          //  echo $sql_21."<br>";

            $resultado_21 = $conn->query($sql_21); while($vend21= $resultado_21->fetch_assoc() ){$IvaAlicuotaImporte21 =$vend21['iva21'];}


            $sql_21totalsiniva="SELECT SUM(s.net_unit_price * s.unit_quantity) AS totalsinivaiva21  FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=21" ;



              // echo $sql_21totalsiniva."<br>";


            $resultado_21totalsiniva = $conn->query($sql_21totalsiniva); while($vend21totalsiniva= $resultado_21totalsiniva->fetch_assoc() ){$IvaAlicuotaBaseImp21 =$vend21totalsiniva['totalsinivaiva21'];}




}


           



          




        /*--------------------------------------------------------------------------------------*/

            $sql_10="SELECT SUM(s.item_tax) AS iva10 FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=10.5" ;


           //    echo $sql_10."<br>";   

            $resultado_10 = $conn->query($sql_10); while($vend10= $resultado_10->fetch_assoc() ){$IvaAlicuotaImporte10 =$vend10['iva10'];}

              $sql_10totalsiniva="SELECT SUM(s.net_unit_price * s.unit_quantity ) AS totalsinivaiva10 FROM sma_sale_items s LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id  WHERE s.sale_id=".$sale_id." AND t.rate=10.5";



          //  echo $sql_10totalsiniva."<br>";   


            $resultado_10totalsiniva = $conn->query($sql_10totalsiniva); while($vend10totalsiniva= $resultado_10totalsiniva->fetch_assoc() ){$IvaAlicuotaBaseImp10 =$vend10totalsiniva['totalsinivaiva10'];}







        /*--------------------------------------------------------------------------------------*/



             $sql_exe="SELECT SUM(s.item_tax) AS exento FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=0" ;




            // echo $sql_exe."<br>";   


             $resultado_exe = $conn->query($sql_exe); while($vendexe= $resultado_exe->fetch_assoc() ){$IvaAlicuotaImporteexe =$vendexe['exento'];}



                  $sql_exetotalsiniva="SELECT SUM(s.net_unit_price * s.unit_quantity ) AS totalsinivaivaexe FROM sma_sale_items s LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id  WHERE s.sale_id=".$sale_id." AND t.rate=0";



          //  echo $sql_exetotalsiniva."<br>"; 
            $resultado_exetotalsiniva = $conn->query($sql_exetotalsiniva); while($vendexetotalsiniva= $resultado_exetotalsiniva->fetch_assoc() ) { $IvaAlicuotaBaseImpexe =$vendexetotalsiniva['totalsinivaivaexe'];}




        /*--------------------------------------------------------------------------------------*/





              


            $IvaAlicuotaBaseImpsuma = $IvaAlicuotaBaseImpexe  ;
            $IvaAlicuotaImportesuma =  (int)$IvaAlicuotaImporteexe ;

            

           //  echo "sin iva".$IvaAlicuotaBaseImpsuma;
           //  echo "iva de sin iva".$IvaAlicuotaImportesuma;

             // $IvaAlicuotaId = 5; // 21% Ver - AfipWsfev1::FEParamGetTiposIva()
                  

                    //$IvaAlicuotaBaseImp = $totalSinIVA;
                   // $IvaAlicuotaImporte = $montoIva;

                //    if (isset($Iva) || isset($IvaAlicuotaBaseImp21) || isset($IvaAlicuotaImporte21))
                  //  {



 if(($IvaAlicuotaImporte21>0) &&  ($IvaAlicuotaImporte10==0))
 {
// hay iva 21 pero no 10,5


                                               $AlicIva[0] =array(
                                                    "Id"=>5,
                                                    "BaseImp"=>number_format(abs($IvaAlicuotaBaseImp21),2,'.',''),
                                                    "Importe"=>number_format(abs($IvaAlicuotaImporte21),2,'.','')
                                                ); 
                                                    if($IvaAlicuotaBaseImpsuma>0)
                                                        {
                                                                  $AlicIva[1] =array(
                                                                                        "Id"=>3,
                                                                                        "BaseImp"=>number_format(abs($IvaAlicuotaBaseImpsuma),2,'.',''),
                                                                                        "Importe"=>number_format(abs($IvaAlicuotaImportesuma),2,'.','')
                                                                                    ); 
                                                        }




 }



 if(($IvaAlicuotaImporte21==0) &&  ($IvaAlicuotaImporte10>0))
 {
// no hay iva 21 y si hay 10,5

                                        $AlicIva[0] =array(
                                                    "Id"=>4,
                                                    "BaseImp"=>number_format(abs($IvaAlicuotaBaseImp10),2,'.',''),
                                                    "Importe"=>number_format(abs($IvaAlicuotaImporte10),2,'.','')
                                                ); 

                                             if($IvaAlicuotaBaseImpsuma>0)
                                                    {
                                                       
                                                              $AlicIva[1] =array(
                                                                                    "Id"=>3,
                                                                                    "BaseImp"=>number_format(abs($IvaAlicuotaBaseImpsuma),2,'.',''),
                                                                                    "Importe"=>number_format(abs($IvaAlicuotaImportesuma),2,'.','')
                                                                                ); 
                                                    }




    
 }


 if(($IvaAlicuotaImporte21>0) &&  ($IvaAlicuotaImporte10>0))
 {
 //  hay iva 21 y  hay 10,5

                                               $AlicIva[0] =array(
                                                    "Id"=>5,
                                                    "BaseImp"=>number_format(abs($IvaAlicuotaBaseImp21),2,'.',''),
                                                    "Importe"=>number_format(abs($IvaAlicuotaImporte21),2,'.','')
                                                ); 
                                               $AlicIva[1] =array(
                                                    "Id"=>4,
                                                    "BaseImp"=>number_format(abs($IvaAlicuotaBaseImp10),2,'.',''),
                                                    "Importe"=>number_format(abs($IvaAlicuotaImporte10),2,'.','')
                                                ); 

                                                    if($IvaAlicuotaBaseImpsuma>0)
                                                            {
                                                               
                                                                      $AlicIva[2] =array(
                                                                                            "Id"=>3,
                                                                                            "BaseImp"=>number_format(abs($IvaAlicuotaBaseImpsuma),2,'.',''),
                                                                                            "Importe"=>number_format(abs($IvaAlicuotaImportesuma),2,'.','')
                                                                                        ); 
                                                            }






 }



 if  ( ($IvaAlicuotaImporte21==0) &&  ($IvaAlicuotaImporte10==0) && ($IvaAlicuotaImportesuma==0) )
 {
 
      $AlicIva[0] =array(
                       "Id"=>3,
                             "BaseImp"=>number_format(abs($IvaAlicuotaBaseImpsuma),2,'.',''),
                             "Importe"=>number_format(abs($IvaAlicuotaImportesuma),2,'.','')
                         ); 

 }




                
             

/*-----------------------------Fact A -----------------------------------------*/

                

                  $Iva = array("AlicIva"=>$AlicIva);


                  $FeCAEReq['FeCAEReq']['FeDetReq']['FECAEDetRequest']['Iva'] = $Iva;



           



                  //  }


        } // fin si es responsable 1


   /* echo '
    <table>
        <caption>wsfe->FECAESolicitar(Request)</caption>
        <tr>
            <th >Request</th>
            <th >Response</th>
        </tr>
        <tr>
            <td>
    ';
    pr($FeCAEReq);

    echo "
            </td>
            <td>
  ";  */

  
            //Registrando la factura electronica
            $FeCAEResponse = $wsfe->FECAESolicitar($FeCAEReq);

            /**
             * Tratamiento de errores
             */

                if (!$FeCAEResponse)
                {
                    /* Procesando ERRORES */

                //   echo '<h2 class="err">NO SE HA GENERADO EL CAE</h2>
                //     <h3 class="err">ERRORES DETECTADOS</h3>';

                    $errores = $wsfe->getErrLog();
                    if (isset($errores))
                    {
                        foreach ($errores as $v)
                        {
                     //       pr($v);

                        }
                    }
                 //  echo "<hr/><h3>Response</h3>";
                    

                }elseif (!$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE'])
                {
                    /* Procesando OBSERVACIONES */
                 //    echo '<h2 class="msg">NO SE HA GENERADO EL CAE</h2>
                 //         <h3 class="msg">OBSERVACIONES INFORMADAS</h3>';

                    if (isset($FeCAEResponse['FeDetResp']['FECAEDetResponse']['Observaciones']))
                    {
                        foreach ($FeCAEResponse['FeDetResp']['FECAEDetResponse']['Observaciones'] as $v)
                        {
                      //      pr($v);
                        }
                    }
                   // echo "<hr/><h3>Response</h3>";
                    
                }
        else if($FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE'])

        {
        
          $array_Concepto=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['Concepto'];
          $array_DocTipo=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['DocTipo'];
          $array_DocNro=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['DocNro'];
          $array_cbteDesde=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CbteDesde'];
          $array_cbteHasta=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CbteHasta'];
          $array_cbteFch=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CbteFch'];
          $array_resultado=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['Resultado'];
          $array_cae=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE'];
          $array_fechacaeven=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAEFchVto'];
         



                if($FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE']<>"")
                {


                 $mibarcode = $empresaCuit . sprintf('%03d', $CbteTipo) . sprintf('%05d', $PtoVta) .$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE'].$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAEFchVto']."1";


                    // Generate imagen del codigo de barra de afip
                    $gen = new \Code39\Generator;
                    $image = $gen->generate($mibarcode);
                    $nombre_imagennc = "NC_".$sale_id.'_'.$empresaCuit.'.png' ;
                    // Save image to file
                    


                    imagepng($image, './assets/uploads/logos/afip/'.$nombre_imagennc);



                // genera imagen del codigo QR

                $url="https://www.afip.gob.ar/fe/qr/?p=";
                $arr = array('ver' =>1 ,'fecha' =>$fechacarga, 'cuit' =>$empresaCuit, 'ptoVta' =>$PtoVta, 'tipoCmp' =>$CbteTipo, 'nroCmp' =>$numero_comprobante, 'importe' =>$total, 'moneda' => 'PES',  'ctz' => 1,  'tipoDocRec' => $tipodoc_comprador, 'nroDocRec' =>$numdoc_comprador, 'tipoCodAut' => 'E','codAut' => $array_cae  );

                //echo json_encode($arr);
                $codigo = base64_encode(json_encode($arr));
                $codigo_para_qr= $url.$codigo;
                $codesDir = "./assets/uploads/logos/afip/";   
                $codeFilenc =  "NC_".$sale_id.'_'.$array_cae.'.png';
                QRcode::png($codigo_para_qr, $codesDir.$codeFilenc, "H", 5); 
                  //  echo '<img class="img-thumbnail" src="'.$codesDir.$codeFile.'" style="width:150px;" />';


                $puntoventa = str_pad($PtoVta, 6, "0", STR_PAD_LEFT);  
                $comprobante = str_pad($CbteDesde, 8, "0", STR_PAD_LEFT);  
                $numero_comprobantenc = $puntoventa."-".$comprobante;
                

                $date = date('Y-m-d h:i:s', time());




 $consulta = "insert into sma_notacredito (date,reference_no,sale_id,grand_total,created_by,CAE,CAEFchVto,imagen_cae,imagen_qr,numero_comprobante,tipocomprobante) values ('$date','$reference_no','$sale_id','$total','$biller_id','".$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE']."','".$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAEFchVto']."','$nombre_imagennc','$codeFilenc','$numero_comprobantenc','$CbteTiponc')";


                        $insert_tmp=mysqli_query($conn, $consulta);


                        

                        if($insert_tmp)
                          {
                              $consulta_sales = "update sma_sales sm set sm.nota_credito ='nc' where sm.id =".$sale_id;
                              $update_tmp=mysqli_query($conn, $consulta_sales);

                          
                                 $this->session->set_flashdata('message', lang('Nota de crédito creada con exito'));
                               admin_redirect('sales');

                          
                           
                        }
                        else
                        {

                        //  echo " no se ha podido update" ;
                            $this->session->set_flashdata('message', lang('Nota de crédito no pudo ser creada'));
                               admin_redirect('sales');
                      
                        }

                }
                else
                {


                   $this->session->set_flashdata('error', lang('sale_afip_error'));
                            admin_redirect('sales');

                }






         }


   /*  pr($FeCAEResponse);

   echo "
            </td>
        </tr>
    </table>
   "; 
*/

}
else
{
   /*echo '
    <hr/>
    <h3>Errores detectados al generar el Ticket de Acceso</h3>';
    pr($wsaa->getErrLog());  */
}






/*----------------------------------AFIP-------------------------------*/





               } // (3)






}  // if nota de credito
else
{
   // echo "no puede continuar"; 
    $this->session->set_flashdata('error', lang('Esa venta ya posee una Nota de Crédito '));
                            admin_redirect('sales');

}





/*$date = $this->input->get('date') ? $this->input->get('date') : null;
$numero_comprobante = $this->input->get('numero_comprobante') ? $this->input->get('numero_comprobante') : null;
$reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
$customer = $this->input->get('customer') ? $this->input->get('customer') : null;
$payment_method = $this->input->get('payment_method') ? $this->input->get('payment_method') : null;

$puntodeventa = $this->input->get('cf6') ? $this->input->get('cf6') : null;

*/













    } // fin del metodo




      public function afip($quote_id = null)
    {

 
     
       $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : null;

       $tipo= $this->input->get('tipo') ;

        $ruta =  getcwd();


        require $ruta."/app/config/database.php";

      $sql_sales = "SELECT * FROM sma_sales s WHERE s.id=".$sale_id." and  s.sale_status ='completed'";


//$sql_sales = "SELECT * FROM sma_sales s WHERE s.id=".$sale_id." and  s.sale_status ='completed' and s.payment_status = 'paid'";



               $resultado_sigo = $conn->query($sql_sales);
               while($sigo= $resultado_sigo->fetch_assoc() )
               { $CbteTipoSigo=$sigo['payment_method'];$numero_comprobante=$sigo['numero_comprobante'];$reference_no=$sigo['reference_no'];$date=$sigo['date']; $total =$sigo['grand_total'];}







if($numero_comprobante <>"")
{
 //echo "<script> alert('Debe seleccionar tipo de comprobante');document.location.href='".$ruta."/admin/sales/';</script>";

 $this->session->set_flashdata('error', lang('sale_ocupada'));
            admin_redirect('sales');

}
else if($CbteTipoSigo =="")
{


                            if($tipo=="sales")
                            {
                                  $this->session->set_flashdata('error', lang('sale_rechazada'));
                                   admin_redirect('sales');

                            }
                            else if($tipo=="post")
                            {
                             $this->session->set_flashdata('error', lang('sale_rechazada'));
                               admin_redirect('pos/sales');
                            }






}
else

{






               
    

                $resultado_sale = $conn->query($sql_sales);
               while($sal= $resultado_sale->fetch_assoc() )
               {
                   $customer_id =$sal['customer_id'];
                   $biller_id =$sal['biller_id'];
                  
                    
                   $recargo_tarjeta =$sal['recargo_tarjeta'];
                   
                   if($recargo_tarjeta > 0) 
                   {
                         $recargotarjeta = $recargo_tarjeta; // $10

                         $ivarecargotarjeta = $recargotarjeta * 0.173554 ;  // 2,1

                           $recargotarjetasiniva = $recargotarjeta - round($ivarecargotarjeta,3) ; // 10 - 2,1 = 7,90




                           $subtot =$sal['total'];
                           $subtotal =$subtot + $recargotarjetasiniva;

                            $mo_iva =$sal['total_tax'];
                            $monto_iva = $mo_iva + $ivarecargotarjeta;




                   }
                   else
                   {    
                             $subtotal =$sal['total'];
                             $monto_iva =$sal['total_tax'];


                   }
                   
                   $total =$sal['grand_total'];
                   $total_discount = $sal['total_discount'];
                   $nuevocuitentabla =$sal['manual_payment'];
                   $sale_status =$sal['sale_status'];
                   $CbteTipo=$sal['payment_method'];
                   $fechacarga=$sal['date'];

/*
echo "parte d la venta <br>";
echo "cusotmer".$customer_id."<br>";
echo "biller i".$biller_id."<br>";
echo "monto iva".$monto_iva."<br>";
echo "subtota".$subtotal."<br>";
echo "totoal".$total."<br>";
echo "sle estatus ".$sale_status."<br>";
echo "cbt tipo".$CbteTipo."<br>";
echo "fecga carga".$fechacarga."<br>";
echo "<br>";
*/

                   // parte del comprador 


                   $sql_comprador = "SELECT * FROM sma_companies c WHERE c.id=".$customer_id;
                               $resultado_comprador = $conn->query($sql_comprador);
                               while($comp= $resultado_comprador->fetch_assoc() )
                                  {

                                        $nombre_comprador =$comp['name'];
                                        $razon_social =$comp['company'];
                                        
                                        $direccion_comprador =$comp['address'];
                                        $ciudad_comprador =$comp['city'];
                                        $estado_comprador =$comp['state'];
                                        $cp_comprador =$comp['postal_code'];
                                        $email_comprador =$comp['email'];

                                            if($comp['cf1']==99)
                                            {
                                              

                                               if(($nuevocuitentabla <>'') && ($total >=30000))
                                                    {
                                                     $numdoc_comprador = $nuevocuitentabla ;
                                                    }
                                                    else
                                                    {
                                                        $tipodoc_comprador =$comp['cf1'];
                                                        $numdoc_comprador =0;
                                                    }
                                                         




                                            }
                                            else
                                            {
                                              $tipodoc_comprador =$comp['cf1'];

                                               if(($nuevocuitentabla <>'') && ($total >=30000))
                                                    {
                                                     $numdoc_comprador = $nuevocuitentabla ;
                                                    }
                                                    else
                                                    {
                                                     $numdoc_comprador =str_replace("-","",$comp['vat_no']);
                                                    }
                                             

                                            }
                                      
                                        

                                        $tipoiva_comprador =$comp['cf3'];
                                        $tiporesponsable_comprador =$comp['cf4'];

                                  }
/*

echo "parte del comprador<br>" ; 

echo " nombre_comprador ".$nombre_comprador."<br>";
echo " razon_social ".$razon_social."<br>";
echo "numdoc_comprador ".$numdoc_comprador."<br>";
echo "direccion_comprador ".$direccion_comprador."<br>";
echo " ciudad_comprador ".$ciudad_comprador."<br>";
echo "estado_comprador ".$estado_comprador."<br>";
echo "cp_comprador ".$cp_comprador."<br>";
echo "email_comprador ".$email_comprador."<br>";

echo "tipodoc_comprador ".$tipodoc_comprador."<br>";
echo "tipoiva_comprador ".$tipoiva_comprador."<br>";
echo "tiporesponsable_comprador ".$tiporesponsable_comprador."<br>";

echo "<br><br>";

*/


                                  // parte del vendedor 


                 //  $sql_vendedor = "SELECT * FROM sma_companies c WHERE c.id=".$biller_id." and group_name='biller'";
                 //  echo $sql_vendedor."<br>";


                    $sql_vendedor = "SELECT *  FROM sma_companies  s WHERE s.group_name='biller' and s.gst_no<>''";




                               $resultado_vendedor = $conn->query($sql_vendedor);
                               while($vend= $resultado_vendedor->fetch_assoc() )
                                  {

                                        $nombre_vendedor =$vend['name'];
                                        $razon_social_vendedor =$vend['company'];
                                     

                                          $empresaCuit =str_replace("-","",$vend['vat_no']);


                                        $direccion_vendedor =$vend['address'];
                                        $ciudad_vendedor =$vend['city'];
                                        $estado_vendedor =$vend['state'];
                                        $cp_vendedor =$vend['postal_code'];
                                        $email_vendedor =$vend['email'];
                                        $tipodoc_vendedor =$vend['cf1'];
                                        $tipoiva_vendedor =$vend['cf3'];
                                        $tiporesponsable_vendedor =$vend['cf4'];
                                        $logo =$vend['logo'];
                                        $modoAfip =$vend['cf2'];
                                        $empresaAlias = $vend['gst_no'];  //alias de produccion 
                                        $PtoVta =$vend['cf6'];

                                        
                                        

                                  }

/*


echo "parte del vendedor<br>" ; 

echo "nombre_vendedor ".$nombre_vendedor."<br>";
echo "razon_social ".$razon_social_vendedor."<br>";
echo "empresaCuit ".$empresaCuit."<br>";
echo "direccion_vendedor ".$direccion_vendedor."<br>";
echo "ciudad_vendedor ".$ciudad_vendedor."<br>";
echo "estado_vendedor ".$estado_vendedor."<br>";
echo "cp_vendedor ".$cp_vendedor."<br>";
echo "email_vendedor ".$email_vendedor."<br>";

echo "<br><br>";
echo "tipodoc_vendedor ".$tipodoc_vendedor."<br>";
echo "tipoiva_vendedor ".$tipoiva_vendedor."<br>";
echo "tiporesponsable_vendedor ".$tiporesponsable_vendedor."<br>";
echo "logo ".$logo."<br>";
echo "empresaAlias ".$empresaAlias."<br>";
echo "PtoVta ".$PtoVta."<br>";

echo "<br><br>";


die();

*/


// facturacion afip

include_once  $ruta."/afip/config_test.php";
include_once  $ruta."/afip/test/functions.php";

//Cargando modelos de conexion a WebService
include_once  $ruta."/afip/AfipWsaa_test.php";
include_once  $ruta."/afip/AfipWsfev1.php";
include_once  $ruta."/afip/phpqrcode/qrlib.php";






  // Load classes
include_once $ruta.'/afip/src/Code39/Bar.php';
include_once $ruta.'/afip/src/Code39/Character.php';
include_once $ruta.'/afip/src/Code39/CharacterSequence.php';
include_once $ruta.'/afip/src/Code39/Generator.php';
include_once $ruta.'/afip/src/Code39/Parameters.php';



$Concepto = 3; //Productos y Servicios
$CbteFch = intval(date('Ymd'));
$FchServDesde = intval(date('Ymd'));
$FchServHasta = intval(date('Ymd'));
$FchVtoPago = intval(date('Ymd'));
$MonId = 'PES'; // Pesos (AR) - Ver - AfipWsfev1::FEParamGetTiposMonedas()
$MonCotiz = 1.00;


    //Informacion para agregar al array Tributos
    /** 
     * Esto aplica si las facturas tienen tributos agregados
     */
$stream_opts = [
    'ssl' => [
        'ciphers' => 'AES256-SHA',
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
];
$context = stream_context_create($stream_opts);
//WebService que utilizara la autenticacion
$webService   = 'wsfe';

//Creando el objeto WSAA (Web Service de Autenticación y Autorización)
//$wsaa = new AfipWsaa($webService,$empresaAlias);


$wsaa = new AfipWsaa($webService, $empresaAlias, $context);

//Creando el TA (Ticket de acceso)
if ($ta = $wsaa->loginCms())
{
    $token      = $ta['token'];
    $sign       = $ta['sign'];
    $expiration = $ta['expiration'];
    $uniqueid   = $ta['uniqueid'];
    // Conectando al WebService de Factura Electrónica (WsFev1)
    $wsfe = new AfipWsfev1($empresaCuit, $token, $sign, $context);
    //Conectando al WebService de Factura electronica (WsFev1)
 //   $wsfe = new AfipWsfev1($empresaCuit,$token,$sign);
    //Obteniendo el ultimo numero de comprobante autorizado
    $CompUltimoAutorizado = $wsfe->FECompUltimoAutorizado($PtoVta,$CbteTipo);
    // $CompUltimoAutorizado2 = $wsfe->FEParamGetTiposIva();
     //   pr($CompUltimoAutorizado);
   //    pr($CompUltimoAutorizado2);




                    $CbteDesde = $CompUltimoAutorizado['CbteNro'] + 1;
                    $CbteHasta = $CbteDesde;


            

                    
                

                  if($tiporesponsable_vendedor==1)

      {


                    if($total_discount >0)
                    {
                         // a)la venta tien edescuento  

                           $monto_iva_deldescuento = round($total * 0.173554,2) ;  // 2,1

                           $subtotal_deldescuento = $total - $monto_iva_deldescuento;

                              $montoIva=$monto_iva_deldescuento;
                              $ImpIVA = $montoIva;
                              $ImpTotal = $total;
                              $totalSinIVA = $subtotal_deldescuento;
                              $ImpNeto = $subtotal_deldescuento;



                    }
                    else
                    {
                          // b)  no tiene descuento

                              $montoIva=$monto_iva;
                              $ImpIVA = $montoIva;
                              $ImpTotal = $total;
                              $totalSinIVA = $subtotal;
                              $ImpNeto = $totalSinIVA;



                    }

        




 


      
   

      }
      else
      {


                        if($total_discount >0)
                        {
                             // a)la venta tien edescuento  


                        }
                        else
                        {
                              // b)  no tiene descuento

                               
                                    $ImpIVA = 0.00;
                                    $ImpTotal = $total;
                                    $ImpNeto = $total;



                        }





      }



                    $tributoId = null; // Ver - AfipWsfev1::FEParamGetTiposTributos()
                    $tributoDesc = null;
                    $tributoBaseImp = null;
                    $tributoAlic = null;
                    $tributoImporte = null;
                    $ImpTotConc = 0.00;
                    $ImpOpEx = 0.00;
                    $ImpTrib = 0.00;
                    $IvaAlicuotaId= 0.00;
                    $IvaAlicuotaBaseImp= 0.00;
                    $IvaAlicuotaImporte= 0.00;



    
 
        $FeCAEReq = array (
            'FeCAEReq' => array (
                'FeCabReq' => array (
                    'CantReg' => 1,
                    'CbteTipo' => $CbteTipo,
                    'PtoVta' => $PtoVta
                    ),
                'FeDetReq' => array (

                        'FECAEDetRequest' => array(
                        'Concepto' => $Concepto,
                        'DocTipo' => $tipodoc_comprador,
                        'DocNro' => $numdoc_comprador,
                        'CbteDesde' => $CbteDesde,
                        'CbteHasta' => $CbteHasta,
                        'CbteFch' => intval(date('Ymd')),
                        'FchServDesde' => intval(date('Ymd')), // Fechas desde cuando
                        'FchServHasta' => intval(date('Ymd')), // Fecha hasta cuando
                        'FchVtoPago' => intval(date('Ymd')), //  Fecha de pago
                        'ImpTotal' => number_format(abs($ImpTotal),2,'.',''), //total a cobrar
                        'ImpTotConc' => number_format(abs($ImpTotConc),2,'.',''), //
                        'ImpNeto' => number_format(abs($ImpNeto),2,'.',''), // importe neto no grabado
                        'ImpOpEx' => number_format(abs($ImpOpEx),2,'.',''), // importe exento
                        'ImpIVA' => number_format(abs($ImpIVA),2,'.',''), //importe con iva
                        'ImpTrib' => number_format(abs($ImpTrib),2,'.',''), // importe tributario
                        'MonId' => $MonId, // Moneda Argentina
                        'MonCotiz' => $MonCotiz // Cotización de la moneda
                        )
                    )
                ),
            );



        if (isset($Tributos) || isset($tributoBaseImp) || isset($tributoImporte))
        {
            if (empty($Tributos))
            {
                $Tributos = array(
                    'Tributo' => array (
                        'Id' => $tributoId,
                        'Desc' => $tributoDesc,
                        'BaseImp' => number_format(abs($tributoBaseImp),2,'.',''),
                        'Alic' => number_format(abs($tributoAlic),2,'.',''),
                        'Importe' => number_format(abs($tributoImporte),2,'.','')
                        )
                );
            }
            $FeCAEReq['FeCAEReq']['FeDetReq']['FECAEDetRequest']['Tributos'] = $Tributos;
        }

        // si es monotributo no informa iva 

     
        // si es monotributo no informa iva 

        if($tiporesponsable_vendedor==1)  // responsable inscripto
        {

         

         
              //    echo " es factura a o B";


         
/*-----------------------------Fact A o B -----------------------------------------*/
           


           // echo $sql_totalsiniva."<br>";


$sql_existe = "SELECT COUNT(s.id) AS cuenta  FROM sma_sales s WHERE s.id=".$sale_id;



 $resultado_cuenta = $conn->query($sql_existe); while($cuent= $resultado_cuenta->fetch_assoc() ){$cuenta =$cuent['cuenta'];}

if($cuenta >0)
{


    $sql_adelanto =" SELECT SUM(s.net_unit_price * s.unit_quantity) AS adelanto FROM sma_sale_items s WHERE s.sale_id=".$sale_id." AND s.net_unit_price * s.unit_quantity < 0";



     $resultado_adelanto = $conn->query($sql_adelanto); while($adel= $resultado_adelanto->fetch_assoc() ){$adelanto =$adel['adelanto'];}





      


             $sql_21="SELECT SUM(s.item_tax) AS iva21 FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=21" ;



          

            $resultado_21 = $conn->query($sql_21); while($vend21= $resultado_21->fetch_assoc() ){$IvaAlicuotaImporte21 =$vend21['iva21'];}



            $sql_21totalsiniva="SELECT SUM(s.net_unit_price * s.unit_quantity ) AS totalsinivaiva21  FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=21" ;




            $resultado_21totalsiniva = $conn->query($sql_21totalsiniva); while($vend21totalsiniva= $resultado_21totalsiniva->fetch_assoc() )
            {
                $IvaAlicuotaBaseImp21 =$vend21totalsiniva['totalsinivaiva21'] ;
            }





}
else
{

          

              $sql_21="SELECT SUM(s.item_tax) AS iva21 FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=21" ;



          //  echo $sql_21."<br>";

            $resultado_21 = $conn->query($sql_21); while($vend21= $resultado_21->fetch_assoc() ){$IvaAlicuotaImporte21 =$vend21['iva21'];}


            $sql_21totalsiniva="SELECT SUM(s.net_unit_price * s.unit_quantity ) AS totalsinivaiva21  FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=21" ;



              // echo $sql_21totalsiniva."<br>";


            $resultado_21totalsiniva = $conn->query($sql_21totalsiniva); while($vend21totalsiniva= $resultado_21totalsiniva->fetch_assoc() ){$IvaAlicuotaBaseImp21 =$vend21totalsiniva['totalsinivaiva21'];}




}


           



          




        /*--------------------------------------------------------------------------------------*/

            $sql_10="SELECT SUM(s.item_tax) AS iva10 FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=10.5" ;


           //    echo $sql_10."<br>";   

            $resultado_10 = $conn->query($sql_10); while($vend10= $resultado_10->fetch_assoc() ){$IvaAlicuotaImporte10 =$vend10['iva10'];}

              $sql_10totalsiniva="SELECT SUM(s.net_unit_price * s.unit_quantity ) AS totalsinivaiva10 FROM sma_sale_items s LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id  WHERE s.sale_id=".$sale_id." AND t.rate=10.5";



          //  echo $sql_10totalsiniva."<br>";   


            $resultado_10totalsiniva = $conn->query($sql_10totalsiniva); while($vend10totalsiniva= $resultado_10totalsiniva->fetch_assoc() ){$IvaAlicuotaBaseImp10 =$vend10totalsiniva['totalsinivaiva10'];}







        /*--------------------------------------------------------------------------------------*/



             $sql_exe="SELECT SUM(s.item_tax) AS exento FROM sma_sale_items s 
            LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id WHERE s.sale_id=".$sale_id." AND t.rate=0" ;




            // echo $sql_exe."<br>";   


             $resultado_exe = $conn->query($sql_exe); while($vendexe= $resultado_exe->fetch_assoc() ){$IvaAlicuotaImporteexe =$vendexe['exento'];}



                  $sql_exetotalsiniva="SELECT SUM(s.net_unit_price * s.unit_quantity) AS totalsinivaivaexe FROM sma_sale_items s LEFT JOIN sma_tax_rates t ON t.id=s.tax_rate_id  WHERE s.sale_id=".$sale_id." AND t.rate=0";



          //  echo $sql_exetotalsiniva."<br>"; 
            $resultado_exetotalsiniva = $conn->query($sql_exetotalsiniva); while($vendexetotalsiniva= $resultado_exetotalsiniva->fetch_assoc() ) { $IvaAlicuotaBaseImpexe =$vendexetotalsiniva['totalsinivaivaexe'];}




        /*--------------------------------------------------------------------------------------*/





              


            $IvaAlicuotaBaseImpsuma = $IvaAlicuotaBaseImpexe  ;
            $IvaAlicuotaImportesuma =  (int)$IvaAlicuotaImporteexe ;

            

           //  echo "sin iva".$IvaAlicuotaBaseImpsuma;
           //  echo "iva de sin iva".$IvaAlicuotaImportesuma;

             // $IvaAlicuotaId = 5; // 21% Ver - AfipWsfev1::FEParamGetTiposIva()
                  

                    //$IvaAlicuotaBaseImp = $totalSinIVA;
                   // $IvaAlicuotaImporte = $montoIva;

                //    if (isset($Iva) || isset($IvaAlicuotaBaseImp21) || isset($IvaAlicuotaImporte21))
                  //  {

/*--------------------------------------------------*/


if($total_discount >0)
                    {
                         // a)la venta tien edescuento  

                           $monto_iva_deldescuento = round($total * 0.173554,2) ;  // 2,1

                           $subtotal_deldescuento = $total - $monto_iva_deldescuento;

                              $montoIva=$monto_iva_deldescuento;
                              $ImpIVA = $montoIva;
                              $ImpTotal = $total;
                              $totalSinIVA = $subtotal_deldescuento;
                              $ImpNeto = $subtotal_deldescuento;


                                               $AlicIva[0] =array(
                                                    "Id"=>5,
                                                    "BaseImp"=>number_format(abs($totalSinIVA),2,'.',''),
                                                    "Importe"=>number_format(abs($ImpIVA),2,'.','')
                                                );


                                               



                    }
                    else
                    {



 if(($IvaAlicuotaImporte21>0) &&  ($IvaAlicuotaImporte10==0))
 {
// hay iva 21 pero no 10,5


                                               $AlicIva[0] =array(
                                                    "Id"=>5,
                                                    "BaseImp"=>number_format(abs($IvaAlicuotaBaseImp21),2,'.',''),
                                                    "Importe"=>number_format(abs($IvaAlicuotaImporte21),2,'.','')
                                                ); 
                                                    if($IvaAlicuotaBaseImpsuma>0)
                                                        {
                                                                  $AlicIva[1] =array(
                                                                                        "Id"=>3,
                                                                                        "BaseImp"=>number_format(abs($IvaAlicuotaBaseImpsuma),2,'.',''),
                                                                                        "Importe"=>number_format(abs($IvaAlicuotaImportesuma),2,'.','')
                                                                                    ); 
                                                        }




 }



 if(($IvaAlicuotaImporte21==0) &&  ($IvaAlicuotaImporte10>0))
 {
// no hay iva 21 y si hay 10,5

                                        $AlicIva[0] =array(
                                                    "Id"=>4,
                                                    "BaseImp"=>number_format(abs($IvaAlicuotaBaseImp10),2,'.',''),
                                                    "Importe"=>number_format(abs($IvaAlicuotaImporte10),2,'.','')
                                                ); 

                                             if($IvaAlicuotaBaseImpsuma>0)
                                                    {
                                                       
                                                              $AlicIva[1] =array(
                                                                                    "Id"=>3,
                                                                                    "BaseImp"=>number_format(abs($IvaAlicuotaBaseImpsuma),2,'.',''),
                                                                                    "Importe"=>number_format(abs($IvaAlicuotaImportesuma),2,'.','')
                                                                                ); 
                                                    }




    
 }


 if(($IvaAlicuotaImporte21>0) &&  ($IvaAlicuotaImporte10>0))
 {
 //  hay iva 21 y  hay 10,5

                                               $AlicIva[0] =array(
                                                    "Id"=>5,
                                                    "BaseImp"=>number_format(abs($IvaAlicuotaBaseImp21),2,'.',''),
                                                    "Importe"=>number_format(abs($IvaAlicuotaImporte21),2,'.','')
                                                ); 
                                               $AlicIva[1] =array(
                                                    "Id"=>4,
                                                    "BaseImp"=>number_format(abs($IvaAlicuotaBaseImp10),2,'.',''),
                                                    "Importe"=>number_format(abs($IvaAlicuotaImporte10),2,'.','')
                                                ); 

                                                    if($IvaAlicuotaBaseImpsuma>0)
                                                            {
                                                               
                                                                      $AlicIva[2] =array(
                                                                                            "Id"=>3,
                                                                                            "BaseImp"=>number_format(abs($IvaAlicuotaBaseImpsuma),2,'.',''),
                                                                                            "Importe"=>number_format(abs($IvaAlicuotaImportesuma),2,'.','')
                                                                                        ); 
                                                            }






 }



 if  ( ($IvaAlicuotaImporte21==0) &&  ($IvaAlicuotaImporte10==0) && ($IvaAlicuotaImportesuma==0) )
 {
 
      $AlicIva[0] =array(
                       "Id"=>3,
                             "BaseImp"=>number_format(abs($IvaAlicuotaBaseImpsuma),2,'.',''),
                             "Importe"=>number_format(abs($IvaAlicuotaImportesuma),2,'.','')
                         ); 

 }



} // fin del else de si hay descuentos
  /*-----------------------------------------------------------------------------*/              
             

/*-----------------------------Fact A -----------------------------------------*/

                

                  $Iva = array("AlicIva"=>$AlicIva);


                  $FeCAEReq['FeCAEReq']['FeDetReq']['FECAEDetRequest']['Iva'] = $Iva;



           



                  //  }


        } // fin si es responsable 1

echo '
    <table>
        <caption>wsfe->FECAESolicitar(Request)</caption>
        <tr>
            <th >Request</th>
            <th >Response</th>
        </tr>
        <tr>
            <td>
    '; 
    pr($FeCAEReq);

    echo " 
            </td>
            <td> 
  ";     
            //Registrando la factura electronica
            $FeCAEResponse = $wsfe->FECAESolicitar($FeCAEReq);

            /**/
               echo "tratamiento de errores<br>";
             

                if (!$FeCAEResponse)
                {
                    /* Procesando ERRORES */
                  

                 //   echo '<h2 class="err">NO SE HA GENERADO EL CAE</h2>
                  //       <h3 class="err">ERRORES DETECTADOS</h3>'; 

                    $errores = $wsfe->getErrLog();
                    if (isset($errores))
                    {
                        foreach ($errores as $v)
                        {
                             pr($v);

                        }
                    }
                      echo "<hr/><h3>Response</h3>";
                    

                }elseif (!$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE'])
                {
                      /* echo 'Procesando OBSERVACIONES <br>
                     <h2 class="msg">NO SE HA GENERADO EL CAE</h2>
                     <h3 class="msg">OBSERVACIONES INFORMADAS</h3>';   */
                       

                    if (isset($FeCAEResponse['FeDetResp']['FECAEDetResponse']['Observaciones']))
                    {
                        foreach ($FeCAEResponse['FeDetResp']['FECAEDetResponse']['Observaciones'] as $v)
                        {
                              pr($v);

                          foreach ($v as $row) {
                                $code =  $row['Code'];
                                $msg = $row['Msg'];
                                
                            }



                        



                        }
                    }



                  //   echo "<hr/><h3>Response</h3>";
                    
                }
        else if($FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE'])

        { 
        
          $array_Concepto=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['Concepto'];
          $array_DocTipo=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['DocTipo'];
          $array_DocNro=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['DocNro'];
          $array_cbteDesde=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CbteDesde'];
          $array_cbteHasta=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CbteHasta'];
          $array_cbteFch=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CbteFch'];
          $array_resultado=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['Resultado'];
          $array_cae=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE'];
          $array_fechacaeven=$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAEFchVto'];
         




                if($FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE']<>"")
                {


                 $mibarcode = $empresaCuit . sprintf('%03d', $CbteTipo) . sprintf('%05d', $PtoVta) .$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE'].$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAEFchVto']."1";


               /*     // Generate imagen del codigo de barra de afip
                    $gen = new \Code39\Generator;
                    $image = $gen->generate($mibarcode);
                    $nombre_imagen = $sale_id.'_'.$empresaCuit.'.png' ;
                    // Save image to file
                    
die();

                    imagepng($image, './assets/uploads/logos/afip/'.$nombre_imagen);

*/
                // genera imagen del codigo QR

                $url="https://www.afip.gob.ar/fe/qr/?p=";
                $arr = array('ver' =>1 ,'fecha' =>$fechacarga, 'cuit' =>$empresaCuit, 'ptoVta' =>$PtoVta, 'tipoCmp' =>$CbteTipo, 'nroCmp' =>$numero_comprobante, 'importe' =>$total, 'moneda' => 'PES',  'ctz' => 1,  'tipoDocRec' => $tipodoc_comprador, 'nroDocRec' =>$numdoc_comprador, 'tipoCodAut' => 'E','codAut' => $array_cae  );

                //echo json_encode($arr);
                $codigo = base64_encode(json_encode($arr));
                $codigo_para_qr= $url.$codigo;
                $codesDir = "./assets/uploads/logos/afip/";   
                $codeFile =  $sale_id.'_'.$array_cae.'.png';
                QRcode::png($codigo_para_qr, $codesDir.$codeFile, "H", 5); 
                  //  echo '<img class="img-thumbnail" src="'.$codesDir.$codeFile.'" style="width:150px;" />';


                $puntoventa = str_pad($PtoVta, 6, "0", STR_PAD_LEFT);  
                $comprobante = str_pad($CbteDesde, 8, "0", STR_PAD_LEFT);  
                $numero_comprobante = $puntoventa."-".$comprobante;


                $consulta = "update sma_sales sm set sm.CAE = '".$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE']."',sm.CAEFchVto = '".$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAEFchVto']."',sm.imagen_cae='".$nombre_imagen."',sm.numero_comprobante='".$numero_comprobante."',sm.imagen_qr = '".$codeFile."' where sm.id =".$sale_id;



                        $insert_tmp=mysqli_query($conn, $consulta);

                        if($insert_tmp)
                        {


                            if($tipo=="sales")
                            {
                                 $this->session->set_flashdata('message', lang('sale_afip'));
                            admin_redirect('sales');

                            }
                            else if($tipo=="post")
                            {
                             $this->session->set_flashdata('message', lang('sale_afip'));
                               admin_redirect('pos/sales');
                            } 

                            else if($tipo=="post2")
                            {
                             $this->session->set_flashdata('message', lang('sale_afip'));
                               admin_redirect('pos/');
                            } 
                           
                        }
                        else
                        {

                        //  echo " no se ha podido update" ;
                      
                        }

                }
                else
                {


                   $this->session->set_flashdata('error', lang('sale_afip_error'));
                            admin_redirect('sales');

                }






         }

/*  pr($FeCAEResponse);

    echo "
           </td>
     </tr>
  </table>
   "; */   
}
else
{
 /* echo '
    <hr/>
    <h3>Errores detectados al generar el Ticket de Acceso</h3>';
    pr($wsaa->getErrLog()); */    
}







               }

}  // fin si selecciono comprobante

    }





    /*++++++++++++++++++++++++++++++++++++++fin afip ++++++++++++++++++++++++++++++++++++++++++++++++++++++*/




    public function notacredito($id = null)
    {



        $ruta =  getcwd();
        require $ruta."/app/config/database.php";


        $sql_sales_busqueda = "SELECT * FROM sma_sales s WHERE s.id=".$id." and  s.sale_status ='completed' and s.payment_status = 'paid'";


        $resultado_busqueda = $conn->query($sql_sales_busqueda);
             while($busqueda= $resultado_busqueda->fetch_assoc() )
                       { $nota_credito=$busqueda['nota_credito'] ;}



if($nota_credito <> "nc")
{ // if nota de credito
    //echo "puede continuar";

    





      //  

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }



        $inv = $this->sales_model->getInvoiceByID($id);
        if ($inv->sale_status == 'returned' || $inv->return_id || $inv->return_sale_ref) {
            $this->session->set_flashdata('error', lang('sale_x_action'));
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_method   = $this->input->post('payment_method');
            $payment_term     = $this->input->post('payment_term');
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->sma->clear_tags($this->input->post('note'));
            $staff_note       = $this->sma->clear_tags($this->input->post('staff_note'));

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = $_POST['serial'][$r]           ?? '';
                $item_tax_rate      = $_POST['product_tax'][$r]      ?? null;
                $item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;

                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->sma->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax    = $this->sma->formatDecimal($ctax['amount']);
                        $tax         = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->sma->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->sma->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'real_unit_price'   => $real_unit_price,
                    ];

                    $products[] = ($product + $gst_data);
                    $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax), true);
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total    = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $data           = ['date' => $date,
                'reference_no'        => $reference,
                'customer_id'         => $customer_id,
                'customer'            => $customer,
                'biller_id'           => $biller_id,
                'biller'              => $biller,
                'warehouse_id'        => $warehouse_id,
                'note'                => $note,
                'staff_note'          => $staff_note,
                'total'               => $total,
                'product_discount'    => $product_discount,
                'order_discount_id'   => $this->input->post('order_discount'),
                'order_discount'      => $order_discount,
                'total_discount'      => $total_discount,
                'product_tax'         => $product_tax,
                'order_tax_id'        => $this->input->post('order_tax'),
                'order_tax'           => $order_tax,
                'total_tax'           => $total_tax,
                'shipping'            => $this->sma->formatDecimal($shipping),
                'grand_total'         => $grand_total,
                'total_items'         => $total_items,
                'sale_status'         => $sale_status,
                'payment_status'      => $payment_status,
                'payment_method'      => $payment_method,
                
                'payment_term'        => $payment_term,
                'due_date'            => $due_date,
                'updated_by'          => $this->session->userdata('user_id'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            $attachments        = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateSale($id, $data, $products, $attachments)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('sale_updated'));
            admin_redirect($inv->pos ? 'pos/sales' : 'sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang('sale_x_edited_older_than_x_days'), $this->Settings->disable_editing));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            $inv_items = $this->sales_model->getAllInvoiceItems($id);
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                // $row = $this->site->getProductByID($item->product_id);
                $row = $this->sales_model->getWarehouseProduct($item->product_id, $item->warehouse_id);
                if (!$row) {
                    $row             = json_decode('{}');
                    $row->tax_method = 0;
                    $row->quantity   = 0;
                } else {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
                $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) {
                    $row->quantity = 0;
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                $row->id              = $item->product_id;
                $row->code            = $item->product_code;
                $row->name            = $item->product_name;
                $row->type            = $item->product_type;
                $row->base_quantity   = $item->quantity;
                $row->base_unit       = !empty($row->unit) ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = !empty($row->price) ? $row->price : $item->unit_price;
                $row->unit            = $item->product_unit_id;
                $row->qty             = $item->unit_quantity;
                $row->quantity += $item->quantity;
                $row->discount        = $item->discount ? $item->discount : '0';
                $row->item_tax        = $item->item_tax      > 0 ? $item->item_tax      / $item->quantity : 0;
                $row->item_discount   = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
                $row->price           = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($row->item_discount));
                $row->unit_price      = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($row->item_discount) + $this->sma->formatDecimal($row->item_tax) : $item->unit_price + ($row->item_discount);
                $row->real_unit_price = $item->real_unit_price;
                $row->tax_rate        = $item->tax_rate_id;
                $row->serial          = $item->serial_no;
                $row->option          = $item->option_id;
                $options              = $this->sales_model->getProductOptions($row->id, $item->warehouse_id, true);
                if ($options) {
                    foreach ($options as $option) {
                        $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                        if ($pis) {
                            $option->quantity = 0;
                            foreach ($pis as $pi) {
                                $option->quantity += $pi->quantity_balance;
                            }
                        }
                        if ($row->option == $option->id) {
                            $option->quantity += $item->quantity;
                        }
                    }
                }

                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                    $te          = $combo_items;
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }
                $units    = !empty($row->base_unit) ? $this->site->getUnitsByBUID($row->base_unit) : null;
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri       = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, ];
                $c++;
            }

            $this->data['inv_items'] = json_encode($pr);
            $this->data['id']        = $id;
            //$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['billers']    = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['units']      = $this->site->getAllBaseUnits();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('nota de credito')]];
            $meta = ['page_title' => lang('nota de credito'), 'bc' => $bc];
            $this->page_construct('sales/notacredito', $meta, $this->data);
        }

    }
    else
    {
         $this->session->set_flashdata('message', lang('Nota de crédito ya existe'));
                               admin_redirect('sales');
    }

}






}
