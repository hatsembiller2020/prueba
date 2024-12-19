<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->load->admin_model('pos_model');
        $this->load->helper('text');
        $this->pos_settings           = $this->pos_model->getSetting();
        $this->pos_settings->pin_code = $this->pos_settings->pin_code ? md5($this->pos_settings->pin_code) : null;
        $this->data['pos_settings']   = $this->pos_settings;
        $this->session->set_userdata('last_activity', now());
        $this->lang->admin_load('pos', $this->Settings->user_language);
        $this->load->library('form_validation');
    }

    public function active()
    {
        $this->session->set_userdata('last_activity', now());
        if ((now() - $this->session->userdata('last_activity')) <= 20) {
            die('Successfully updated the last activity.');
        }
        die('Failed to update last activity.');
    }







       /* ---------------------------------------------------------------------------------------------------- */

public function llenartabla($id)
    {

         $ruta =  getcwd();
                                                require $ruta."/app/config/database.php";

                                            //    $sql_vendedor_post = "SELECT c.id ,c.name FROM sma_companies c WHERE  c.group_name='biller' and c.id=".$biller_id;

$usuariocaja = $id;


$sql_usuario = "SELECT s.group_id FROM sma_users s WHERE s.id=".$usuariocaja;


     $resultado_usuario = $conn->query($sql_usuario);
                                                   while($TTT= $resultado_usuario->fetch_assoc() )
                                                      {
                                                         $perfilogueado  = $TTT["group_id"] ; 
                                                      }



if($perfilogueado <> 1 )
{
                $sql_vendedor_post = "SELECT s.id,u.username, s.date,s.reference_no,s.biller,s.created_by,s.customer,s.grand_total,s.paid,s.sale_status,s.payment_status,s.CAE FROM sma_sales s LEFT JOIN sma_users u ON u.id=s.created_by WHERE s.pos=1 and s.created_by=".$usuariocaja." ORDER BY s.date DESC LIMIT 5";

}
else
{

                $sql_vendedor_post = "SELECT s.id,u.username, s.date,s.reference_no,s.biller,s.created_by,s.customer,s.grand_total,s.paid,s.sale_status,s.payment_status,s.CAE FROM sma_sales s LEFT JOIN sma_users u ON u.id=s.created_by WHERE s.pos=1  ORDER BY s.date DESC LIMIT 5";

}

    

                   echo "

                   <style>

     .mibotoncito{
        padding: 7px;
    font-size: 13px;
    line-height: 1.5;
    color: #fff;
    background-color: #428bca;
    border-color: snow;

     }
     </style>


     
                   <label>Ultimas 5 ventas de pos </label><br><table  class='table table-bordered table-hover table-striped'>
                        <thead>
                        <tr>
                    
                            <th>Fecha</th>
                            <th>Referencia</th>
                            <th>Vendedor</th>
                            <th>Creado por</th>
                            <th>Comprador</th>
                            <th>Total</th>
                            <th>Pagado</th>
                  
                            <th>Estado venta</th>
                            <th>Estado pago</th>
                            <th></th>
                     
                        </tr>
                        </thead>
                        <tbody> ";
                  
                    
             

                                                   $resultado_post = $conn->query($sql_vendedor_post);
                                                   while($vp= $resultado_post->fetch_assoc() )
                                                      {
                                                          
                                                            $id =$vp['id']; 
                                                            $date =$vp['date']; 
                                                            $reference_no =$vp['reference_no']; 
                                                            $biller =$vp['biller']; 
                                                            $created_by =$vp['created_by']; 
                                                            $customer =$vp['customer']; 
                                                            $grand_total =$vp['grand_total']; 
                                                            $paid =$vp['paid']; 
                                                            $sale_status =$vp['sale_status']; 
                                                            $payment_status =$vp['payment_status'];
                                                            $username =$vp['username'];
                                                            $CAE =$vp['CAE'];

                                                            echo "

<tr>

<td>".$date."</td>
<td>".$reference_no."</td>
<td>".$biller."</td>
<td>".$username."</td>
<td>".$customer."</td>
<td>".number_format($grand_total, 2, ',', '.')."</td>
<td>".number_format($paid, 2, ',', '.')."</td>
<td>".$sale_status."</td>
<td>".$payment_status;

if($CAE >0) { echo " (ARCA)" ;}


echo "</td>
<td>

<button type='button' class='btn btn-primary' data-toggle='modal' data-target='#exampleModal".$id."' onclick='apagarmodal(".$id.")'>
<i class='fa fa-print' aria-hidden='true'></i>
</button>
";

if($CAE >0) {}
else
{



echo "<a href='admin/sales/afip?sale_id=".$id."&tipo=post2'>

<input type='button' class='mibotoncito' name='' id='' value='Enviar ARCA'>


</a> ";


}


echo "</td>";





echo "</tr>" ;

echo '
<div class="modal fade" id="exampleModal'.$id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Imprimir</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">';
  
 echo "  <a href='admin/pos/viewsale/".$id."' target='_blank'><button type='button' class='btn btn-danger' >Factura</button></a>

              <a href='admin/pos/view/".$id."' target='_blank'> <button type='button' class='btn btn-info' >Ticket</button></a>

              <a href='admin/pos/viewsalecc/".$id."' target='_blank'> <button type='button' class='btn btn-warning' >Factura CC</button></a>

              <a href='admin/pos/viewcc/".$id."' target='_blank'> <button type='button' class='btn btn-danger' >Ticket CC</button></a>



";


    




     echo ' </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

      </div>
    </div>
  </div>
</div>
      ';                                                }
          echo " </tbody>
            
                    </table>";

                    ?>


                    <script>
                        
                        function apagarmodal($id)
                        {

                            var idmodal = $id;
                          //  alert(idmodal) ; 

              // Cierro modal y vuelvo a activar form
                              setTimeout(function() {

                                $('#exampleModal'+idmodal).modal('hide');
                             

                              }, 5000);



                        }
                    </script> 


                     <div class="row">
                                        <div class="col-xs-4" style="padding: 0;">
                                            <div class="btn-group-vertical btn-block">
                                                <button type="button" class="btn btn-warning btn-block btn-flat"
                                                id="suspend">
                                                    <?=lang('suspend'); ?>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-block btn-flat"
                                                id="reset">
                                                    <?= lang('cancel'); ?>
                                                </button>
                                            </div>

                                        </div>
                                        <div class="col-xs-4" style="padding: 0;">
                                            <div class="btn-group-vertical btn-block">
                                                <button type="button" class="btn btn-info btn-block" id="print_order">
                                                    <?=lang('order');?>
                                                </button>
                                               

                                                <button type="button" id="btnEscanear2" class="btn btn-primary " onclick="buscarcuentacorriente();">
                                                    <?=lang('Cuenta Corriente');?>
                                                </button> 


                                            </div>
                                        </div>
                                        <div class="col-xs-4" style="padding: 0;">
                                            <button type="button" class="btn btn-success btn-block" id="payment" style="height:67px;">
                                                <i class="fa fa-money" style="margin-right: 5px;"></i><?=lang('payment');?>
                                            </button>
                                        </div>
                                    </div>

<script>
    
   function buscarcuentacorriente() {

    /*
    const $btnEscanear2 = document.querySelector("#btnEscanear2"),
        $input = document.querySelector("#add_item");

    $btnEscanear2.addEventListener("click", () => {
        window.open('<?= admin_url('pos_offline/leer_codigo_pos'); ?>');
    });

    window.onCodigoLeido = datosCodigo => {
        console.log("Oh sí, código leído: ");
        console.log(datosCodigo);

        // Establecer el valor en el cuadro de entrada
        $input.value = datosCodigo.codeResult.code;

        // Simular la pulsación de "Enter" después de 1000 milisegundos (1 segundo)
        setTimeout(() => {
            simularEnter();
        }, 500);
    }

    // Función para simular la pulsación de "Enter"
    function simularEnter() {
        const evento = new Event("keydown");
        evento.key = "Enter";
        $input.dispatchEvent(evento);
    }

    */

    $('#cargando').show();

     $('#payment').click();

      selecOp3("si");
     $("#cuentacorriente").val("si");

     setTimeout(function(){
     $('#submit-sale_frente').click();
        }, 1500);



   
     
}





</script>

                                    <?php 
                   
    }




      public function viewsale($sale_id = null, $modal = null)
    {
       
        $this->load->library('inv_qrcode');
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('pos');
        $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv                   = $this->pos_model->getInvoiceByID($sale_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['rows']            = $this->pos_model->getAllInvoiceItems($sale_id);
        $biller_id                     = $inv->biller_id;
        $customer_id                   = $inv->customer_id;
        $this->data['biller']          = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer']        = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments']        = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos']             = $this->pos_model->getSetting();
        $this->data['barcode']         = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale']     = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : null;
        $this->data['inv']             = $inv;
        $this->data['sid']             = $sale_id;
        $this->data['modal']           = $modal;
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['printer']         = $this->pos_model->getPrinterByID($this->pos_settings->printer);
        $this->data['page_title']      = $this->lang->line('invoice');
        $this->load->view($this->theme . 'pos/viewsale', $this->data);
    }


    public function viewsalecc($sale_id = null, $modal = null)
    {
       
        $this->load->library('inv_qrcode');
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('pos');
        $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv                   = $this->pos_model->getInvoiceByID($sale_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['rows']            = $this->pos_model->getAllInvoiceItems($sale_id);
        $biller_id                     = $inv->biller_id;
        $customer_id                   = $inv->customer_id;
        $this->data['biller']          = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer']        = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments']        = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos']             = $this->pos_model->getSetting();
        $this->data['barcode']         = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale']     = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : null;
        $this->data['inv']             = $inv;
        $this->data['sid']             = $sale_id;
        $this->data['modal']           = $modal;
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['printer']         = $this->pos_model->getPrinterByID($this->pos_settings->printer);
        $this->data['page_title']      = $this->lang->line('invoice');
        $this->load->view($this->theme . 'pos/viewsalecc', $this->data);
    }




    

      public function add_printer2()
    {
        

        $this->form_validation->set_rules('title', $this->lang->line('title'), 'required');
      


        if ($this->form_validation->run() == true) {
            $data = ['title'    => $this->input->post('title'),
             
            ];
        }

        if ($this->form_validation->run() == true && $cid = $this->pos_model->addPrinter2($data)) {
            $this->session->set_flashdata('message', $this->lang->line('Tarjeta agregada'));
            admin_redirect('pos/printers2');
        } else {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['status' => 'failed', 'msg' => validation_errors()]);
                die();
            }

            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('add_printer');
            $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => admin_url('pos/printers2'), 'page' => lang('Tarjetas')], ['link' => '#', 'page' => lang('agregar tarjeta')]];
            $meta                     = ['page_title' => lang('add_printer'), 'bc' => $bc];
            $this->page_construct('pos/add_printer2', $meta, $this->data);
        }
    }




    public function add_payment($id = null)
    {
        $this->sma->checkPermissions('payments', true, 'sales');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $sale = $this->pos_model->getInvoiceByID($this->input->post('sale_id'));
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




            $payment = [
                'date'         => $date,
                'sale_id'      => $this->input->post('sale_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount'       => $this->input->post('amount-paid') + $recargo_tarjeta,
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'cc_cvv2'      => $this->input->post('pcc_ccv'),
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

        if ($this->form_validation->run() == true && $msg = $this->pos_model->addPayment($payment, $customer_id)) {
            if ($msg) {
                if ($msg['status'] == 0) {
                    unset($msg['status']);
                    $error = '';
                    foreach ($msg as $m) {
                        if (is_array($m)) {
                            foreach ($m as $e) {
                                $error .= '<br>' . $e;
                            }
                        } else {
                            $error .= '<br>' . $m;
                        }
                    }
                    $this->session->set_flashdata('error', '<pre>' . $error . '</pre>');
                } else {
                    $this->session->set_flashdata('message', lang('payment_added'));
                }
            } else {
                $this->session->set_flashdata('error', lang('payment_failed'));
            }
            admin_redirect('pos/sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $sale                      = $this->pos_model->getInvoiceByID($id);
            $this->data['inv']         = $sale;
            $this->data['payment_ref'] = $this->site->getReference('pay');
            $this->data['modal_js']    = $this->site->modal_js();

            $this->load->view($this->theme . 'pos/add_payment', $this->data);
        }
    }

    public function add_printer()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('pos');
        }

        $this->form_validation->set_rules('title', $this->lang->line('title'), 'required');
        $this->form_validation->set_rules('type', $this->lang->line('type'), 'required');
        $this->form_validation->set_rules('profile', $this->lang->line('profile'), 'required');
        $this->form_validation->set_rules('char_per_line', $this->lang->line('char_per_line'), 'required');
        if ($this->input->post('type') == 'network') {
            $this->form_validation->set_rules('ip_address', $this->lang->line('ip_address'), 'required|is_unique[printers.ip_address]');
            $this->form_validation->set_rules('port', $this->lang->line('port'), 'required');
        } else {
            $this->form_validation->set_rules('path', $this->lang->line('path'), 'required|is_unique[printers.path]');
        }

        if ($this->form_validation->run() == true) {
            $data = ['title'    => $this->input->post('title'),
                'type'          => $this->input->post('type'),
                'profile'       => $this->input->post('profile'),
                'char_per_line' => $this->input->post('char_per_line'),
                'path'          => $this->input->post('path'),
                'ip_address'    => $this->input->post('ip_address'),
                'port'          => ($this->input->post('type') == 'network') ? $this->input->post('port') : null,
            ];
        }

        if ($this->form_validation->run() == true && $cid = $this->pos_model->addPrinter($data)) {
            $this->session->set_flashdata('message', $this->lang->line('printer_added'));
            admin_redirect('pos/printers');
        } else {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['status' => 'failed', 'msg' => validation_errors()]);
                die();
            }

            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('add_printer');
            $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => admin_url('pos/printers'), 'page' => lang('printers')], ['link' => '#', 'page' => lang('add_printer')]];
            $meta                     = ['page_title' => lang('add_printer'), 'bc' => $bc];
            $this->page_construct('pos/add_printer', $meta, $this->data);
        }
    }

    public function ajaxbranddata($brand_id = null)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('brand_id')) {
            $brand_id = $this->input->get('brand_id');
        }

        $products = $this->ajaxproducts(false, $brand_id);

        if (!($tcp = $this->pos_model->products_count(false, false, $brand_id))) {
            $tcp = 0;
        }

        $this->sma->send_json(['products' => $products, 'tcp' => $tcp]);
    }

    public function ajaxcategorydata($category_id = null)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } else {
            $category_id = $this->pos_settings->default_category;
        }

        $subcategories = $this->site->getSubCategories($category_id);
        $scats         = '';
        if ($subcategories) {
            foreach ($subcategories as $category) {
                $scats .= '<button id="subcategory-' . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni subcategory\" ><img src=\"" . base_url() . 'assets/uploads/thumbs/' . ($category->image ? $category->image : 'no_image.png') . "\" class='img-rounded img-thumbnail' /><span>" . $category->name . '</span></button>';
            }
        }

        $products = $this->ajaxproducts($category_id);

        if (!($tcp = $this->pos_model->products_count($category_id))) {
            $tcp = 0;
        }

        $this->sma->send_json(['products' => $products, 'subcategories' => $scats, 'tcp' => $tcp]);
    }

    public function ajaxproducts($category_id = null, $brand_id = null)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('brand_id')) {
            $brand_id = $this->input->get('brand_id');
        }
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } else {
            $category_id = $this->pos_settings->default_category;
        }
        if ($this->input->get('subcategory_id')) {
            $subcategory_id = $this->input->get('subcategory_id');
        } else {
            $subcategory_id = null;
        }
        if (empty($this->input->get('per_page')) || $this->input->get('per_page') == 'n') {
            $page = 0;
        } else {
            $page = $this->input->get('per_page');
        }

        $this->load->library('pagination');

        $config                  = [];
        $config['base_url']      = base_url() . 'pos/ajaxproducts';
        $config['total_rows']    = $this->pos_model->products_count($category_id, $subcategory_id, $brand_id);
        $config['per_page']      = $this->pos_settings->pro_limit;
        $config['prev_link']     = false;
        $config['next_link']     = false;
        $config['display_pages'] = false;
        $config['first_link']    = false;
        $config['last_link']     = false;

        $this->pagination->initialize($config);

        $products = $this->pos_model->fetch_products($category_id, $config['per_page'], $page, $subcategory_id, $brand_id);
        $pro      = 1;
        $prods    = '<div>';
        if (!empty($products)) {
            foreach ($products as $product) {
                $count = $product->id;
                if ($count < 10) {
                    $count = '0' . ($count / 100) * 100;
                }
                if ($category_id < 10) {
                    $category_id = '0' . ($category_id / 100) * 100;
                }

                $prods .= '<button id="product-' . $category_id . $count . "\" type=\"button\" value='" . $product->code . "' title=\"" . $product->name . '" class="btn-prni btn-' . $this->pos_settings->product_button_color . ' product pos-tip" data-container="body"><img src="' . base_url() . 'assets/uploads/thumbs/' . $product->image . '" alt="' . $product->name . "\" class='img-rounded' /><span>" . character_limiter($product->name, 40) . '</span></button>';

                $pro++;
            }
        }
        $prods .= '</div>';

        if ($this->input->get('per_page')) {
            echo $prods;
        } else {
            return $prods;
        }
    }

    public function barcode($text = null, $bcs = 'code128', $height = 50)
    {
        return admin_url('products/gen_barcode/' . $text . '/' . $bcs . '/' . $height);
    }

    public function check_pin()
    {
        $pin = $this->input->post('pw', true);
        if ($pin == $this->pos_pin) {
            $this->sma->send_json(['res' => 1]);
        }
        $this->sma->send_json(['res' => 0]);
    }




   public function ver_register($id = null)
    {

        $ruta =  getcwd();
         require $ruta."/app/config/database.php";

      $query = "SELECT  p.date,p.closed_at,p.user_id FROM sma_pos_register p WHERE p.id = ".$id ; 

     


          $resultado_cajacerrada = $conn->query($query);
               while($sigocajacerrada= $resultado_cajacerrada->fetch_assoc() )
               { 

                $date_inicio=$sigocajacerrada['date'];
                $closed_at=$sigocajacerrada['closed_at'];
                $user_id=$sigocajacerrada['user_id'];

               }



        /*
        if (!$this->Owner && !$this->Admin) {
            $user_id = $this->session->userdata('user_id');
         
        }
*/
  

        $this->form_validation->set_rules('total_cash', lang('total_cash'), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cheques', lang('total_cheques'), 'trim|numeric');
        $this->form_validation->set_rules('total_cc_slips', lang('total_cc_slips'), 'trim|numeric');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : null;
                $rid           = $user_register ? $user_register->id : $this->session->userdata('register_id');
                $user_id       = $user_register ? $user_register->user_id : $this->session->userdata('user_id');
            } else {
                $rid     = $this->session->userdata('register_id');
                $user_id = $this->session->userdata('user_id');
            }
            $data = [
                'closed_at'                => date('Y-m-d H:i:s'),
                'total_cash'               => $this->input->post('total_cash'),
                'total_cheques'            => $this->input->post('total_cheques'),
                'total_cc_slips'           => $this->input->post('total_cc_slips'),
                'total_cash_submitted'     => $this->input->post('total_cash_submitted'),
                'total_cheques_submitted'  => $this->input->post('total_cheques_submitted'),
                'total_cc_slips_submitted' => $this->input->post('total_cc_slips_submitted'),
                'note'                     => $this->input->post('note'),
                'status'                   => 'close',
                'transfer_opened_bills'    => $this->input->post('transfer_opened_bills'),
                'closed_by'                => $this->session->userdata('user_id'),
            ];
        } elseif ($this->input->post('ver_register')) {
            $this->session->set_flashdata('error', (validation_errors() ? validation_errors() : $this->session->flashdata('error')));
            admin_redirect('pos');
        }


$rid = "" ; 
        if ($this->form_validation->run() == true && $this->pos_model->closeRegister($rid, $user_id, $data)) {
            $this->session->set_flashdata('message', lang('register_closed'));
            admin_redirect('welcome');
        } else {
          


        if ($this->Owner || $this->Admin) {
                        $user_register                    = $user_id ? $this->pos_model->registerData($user_id) : null;
                        $register_open_time               = $user_register ? $user_register->date : null;
                      
                        $this->data['register_open_time'] = $user_register ? $register_open_time : null;
                    } else {
                        $register_open_time               = $this->session->userdata('register_open_time');
                     
                        $this->data['register_open_time'] = null;
                    }



           


          $this->data['cash_in_hand'] = $this->pos_model->efectivoenmanoVer($date_inicio,$closed_at ,$user_id);


          $this->data['totaventacomunefectivo']      = $this->pos_model->getRegisterSalesnoposefectivoVer($date_inicio,$closed_at,$user_id);

               
           $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
 

// tarjeta de credito
  
           $this->data['ccsales']         = $this->pos_model->getRegisterCCSalesVer($date_inicio,$closed_at ,$user_id);
       
          $this->data['ccsales2']       = $this->pos_model->getRegisterCCSales2Ver($date_inicio,$closed_at ,$user_id);

            $this->data['ccsales3']       = $this->pos_model->getRegisterCCSales3Ver($date_inicio,$closed_at ,$user_id);



// tarjeta de credito

            $this->data['deposito']       = $this->pos_model->getRegisterdepositoSales2Ver($date_inicio,$closed_at ,$user_id);


             $this->data['cashsales']       = $this->pos_model->getRegisterCashSalesVer($date_inicio,$closed_at ,$user_id);

            $this->data['chsales']         = $this->pos_model->getRegisterChSalesVer($date_inicio,$closed_at ,$user_id);
      
        
            $this->data['othersales']      = $this->pos_model->getRegisterOtherSalesVer($date_inicio,$closed_at ,$user_id);
 
          

            $this->data['totalsales']      = $this->pos_model->getRegisterSalesVer($date_inicio,$closed_at ,$user_id);
           
             $this->data['totaltransferencia']      = $this->pos_model->getRegisterTransferenciaSalesVer($date_inicio,$closed_at ,$user_id);
     
 

             $this->data['totalnc']      = $this->pos_model->getRegisterSalesNCVer($date_inicio,$closed_at ,$user_id);
         

         /*   $this->data['refunds']         = $this->pos_model->getRegisterRefundsVer($register_open_time, $id);
            $this->data['returns']         = $this->pos_model->getRegisterReturnsVer($register_open_time, $id);
            $this->data['cashrefunds']     = $this->pos_model->getRegisterCashRefundsVer($register_open_time, $id);
         */
            $this->data['expenses']        = $this->pos_model->getRegisterExpensesVer($date_inicio,$closed_at ,$user_id);
  


           // $this->data['users']           = $this->pos_model->getUsers($user_id);
          //  $this->data['suspended_bills'] = $this->pos_model->getSuspendedsales($user_id);
            $this->data['user_id']         = $user_id;
            $this->data['modal_js']        = $this->site->modal_js();
            $this->load->view($this->theme . 'pos/ver_register', $this->data);
        }
    }




   public function close_register($user_id = null)
    {

      
        
        if (!$this->Owner && !$this->Admin) {
            $user_id = $this->session->userdata('user_id');
         
        }

  

        $this->form_validation->set_rules('total_cash', lang('total_cash'), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cheques', lang('total_cheques'), 'trim|numeric');
        $this->form_validation->set_rules('total_cc_slips', lang('total_cc_slips'), 'trim|numeric');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : null;
                $rid           = $user_register ? $user_register->id : $this->session->userdata('register_id');
                $user_id       = $user_register ? $user_register->user_id : $this->session->userdata('user_id');
            } else {
                $rid     = $this->session->userdata('register_id');
                $user_id = $this->session->userdata('user_id');
            }
            $data = [
                'closed_at'                => date('Y-m-d H:i:s'),
                'total_cash'               => $this->input->post('total_cash'),
                'total_cheques'            => $this->input->post('total_cheques'),
                'total_cc_slips'           => $this->input->post('total_cc_slips'),
                'total_cash_submitted'     => $this->input->post('total_cash_submitted'),
                'total_cheques_submitted'  => $this->input->post('total_cheques_submitted'),
                'total_cc_slips_submitted' => $this->input->post('total_cc_slips_submitted'),
                'note'                     => $this->input->post('note'),
                'status'                   => 'close',
                'transfer_opened_bills'    => $this->input->post('transfer_opened_bills'),
                'closed_by'                => $this->session->userdata('user_id'),
            ];
        } elseif ($this->input->post('close_register')) {
            $this->session->set_flashdata('error', (validation_errors() ? validation_errors() : $this->session->flashdata('error')));
            admin_redirect('pos');
        }

        if ($this->form_validation->run() == true && $this->pos_model->closeRegister($rid, $user_id, $data)) {
            $this->session->set_flashdata('message', lang('register_closed'));
            admin_redirect('welcome');
        } else {
          


        if ($this->Owner || $this->Admin) {
                        $user_register                    = $user_id ? $this->pos_model->registerData($user_id) : null;
                        $register_open_time               = $user_register ? $user_register->date : null;
                      
                        $this->data['register_open_time'] = $user_register ? $register_open_time : null;
                    } else {
                        $register_open_time               = $this->session->userdata('register_open_time');
                     
                        $this->data['register_open_time'] = null;
                    }



            


          $this->data['cash_in_hand'] = $this->pos_model->efectivoenmano($register_open_time, $user_id);


          $this->data['totaventacomunefectivo']      = $this->pos_model->getRegisterSalesnoposefectivo($register_open_time, $user_id);


               
           $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
       

// tarjeta de credito

           $this->data['ccsales']         = $this->pos_model->getRegisterCCSales($register_open_time, $user_id);
           $this->data['ccsales2']       = $this->pos_model->getRegisterCCSales2($register_open_time, $user_id);

            $this->data['ccsales3']       = $this->pos_model->getRegisterCCSales3($register_open_time, $user_id);

// tarjeta de credito




            $this->data['deposito']       = $this->pos_model->getRegisterdepositoSales2($register_open_time, $user_id);






             $this->data['cashsales']       = $this->pos_model->getRegisterCashSales($register_open_time, $user_id);

            $this->data['chsales']         = $this->pos_model->getRegisterChSales($register_open_time, $user_id);
            $this->data['gcsales']         = $this->pos_model->getRegisterGCSales($register_open_time);
            $this->data['pppsales']        = $this->pos_model->getRegisterPPPSales($register_open_time, $user_id);
            $this->data['stripesales']     = $this->pos_model->getRegisterStripeSales($register_open_time, $user_id);
            $this->data['othersales']      = $this->pos_model->getRegisterOtherSales($register_open_time);
            $this->data['authorizesales']  = $this->pos_model->getRegisterAuthorizeSales($register_open_time, $user_id);
            $this->data['totalsales']      = $this->pos_model->getRegisterSales($register_open_time, $user_id);
             $this->data['totaltransferencia']      = $this->pos_model->getRegisterTransferenciaSales($register_open_time, $user_id);
             $this->data['totalnc']      = $this->pos_model->getRegisterSalesNC($register_open_time, $user_id);
            $this->data['refunds']         = $this->pos_model->getRegisterRefunds($register_open_time, $user_id);
            $this->data['returns']         = $this->pos_model->getRegisterReturns($register_open_time, $user_id);
            $this->data['cashrefunds']     = $this->pos_model->getRegisterCashRefunds($register_open_time, $user_id);
            $this->data['expenses']        = $this->pos_model->getRegisterExpenses($register_open_time, $user_id);
  


            $this->data['users']           = $this->pos_model->getUsers($user_id);
            $this->data['suspended_bills'] = $this->pos_model->getSuspendedsales($user_id);
            $this->data['user_id']         = $user_id;
            $this->data['modal_js']        = $this->site->modal_js();
            $this->load->view($this->theme . 'pos/close_register', $this->data);
        }
    }

      public function delete_printer2($id = null)
    {
        if (DEMO) {
            $this->session->set_flashdata('error', $this->lang->line('disabled_in_demo'));
            $this->sma->md();
        }
     

        if ($this->input->get('id')) {
            $id = $this->input->get('id', true);
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->pos_model->deletePrinter2($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('tarjeta borrada, actualice la pagina...')]);
       
        }
    }


       public function edit_printer2($id = null)
    {
        
        if ($this->input->get('id')) {
            $id = $this->input->get('id', true);
        }

 

        $printer = $this->pos_model->getPrinterByID2($id);
        $this->form_validation->set_rules('title', $this->lang->line('title'), 'required');
      
    

        if ($this->form_validation->run() == true) {
            $data = ['title'    => $this->input->post('title')
                
            ];
        }

        if ($this->form_validation->run() == true && $this->pos_model->updatePrinter2($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line('tarjeta modificada'));
            admin_redirect('pos/printers2');
        } else {
            $this->data['printer']    = $printer;
            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('editar tarjeta');
            $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => admin_url('pos/printers2'), 'page' => lang('editar tarjeta')], ['link' => '#', 'page' => lang('editar tarjeta')]];
            $meta                     = ['page_title' => lang('editar tarjeta'), 'bc' => $bc];
            $this->page_construct('pos/edit_printer2', $meta, $this->data);
        }
    }

      public function get_printers2()
    {
        

        $this->load->library('datatables');
        $this->datatables
        ->select('id, title')
        ->from('printers2')
        ->add_column('Actions', "<div class='text-center'> <a href='" . admin_url('pos/edit_printer2/$1') . "' class='btn-warning btn-xs tip' title='" . lang('editar tarjeta') . "'><i class='fa fa-edit'></i></a> <a href='#' class='btn-danger btn-xs tip po' title='<b>" . lang('borrar tarjeta') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('pos/delete_printer2/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id')
        ->unset_column('id');
        echo $this->datatables->generate();
    }



    public function delete($id = null)
    {
        $this->sma->checkPermissions('index');
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->pos_model->deleteBill($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('suspended_sale_deleted')]);
        }
    }


      public function deletepos($id = null)
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

                     $consulta = "delete from sma_sales where id =".$id;
                     $insert_tmp=mysqli_query($conn, $consulta);


                     // borrar los pagos de esa sventas tambien 

                      $consulta2 = "delete from sma_payments where sale_id =".$id;
                      $insert_tmp2=mysqli_query($conn, $consulta2);





                     $this->sma->send_json([ 'msg' => lang('venta borrada con exito')]);
                     admin_redirect('sales');
                     redirect($_SERVER['HTTP_REFERER']);
            }







    }




    public function delete_printer($id = null)
    {
        if (DEMO) {
            $this->session->set_flashdata('error', $this->lang->line('disabled_in_demo'));
            $this->sma->md();
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }

        if ($this->input->get('id')) {
            $id = $this->input->get('id', true);
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->pos_model->deletePrinter($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('printer_deleted')]);
        }
    }

    public function edit_printer($id = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('pos');
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id', true);
        }

        $printer = $this->pos_model->getPrinterByID($id);
        $this->form_validation->set_rules('title', $this->lang->line('title'), 'required');
        $this->form_validation->set_rules('type', $this->lang->line('type'), 'required');
        $this->form_validation->set_rules('profile', $this->lang->line('profile'), 'required');
        $this->form_validation->set_rules('char_per_line', $this->lang->line('char_per_line'), 'required');
        if ($this->input->post('type') == 'network') {
            $this->form_validation->set_rules('ip_address', $this->lang->line('ip_address'), 'required');
            if ($this->input->post('ip_address') != $printer->ip_address) {
                $this->form_validation->set_rules('ip_address', $this->lang->line('ip_address'), 'is_unique[printers.ip_address]');
            }
            $this->form_validation->set_rules('port', $this->lang->line('port'), 'required');
        } else {
            $this->form_validation->set_rules('path', $this->lang->line('path'), 'required');
            if ($this->input->post('path') != $printer->path) {
                $this->form_validation->set_rules('path', $this->lang->line('path'), 'is_unique[printers.path]');
            }
        }

        if ($this->form_validation->run() == true) {
            $data = ['title'    => $this->input->post('title'),
                'type'          => $this->input->post('type'),
                'profile'       => $this->input->post('profile'),
                'char_per_line' => $this->input->post('char_per_line'),
                'path'          => $this->input->post('path'),
                'ip_address'    => $this->input->post('ip_address'),
                'port'          => ($this->input->post('type') == 'network') ? $this->input->post('port') : null,
            ];
        }

        if ($this->form_validation->run() == true && $this->pos_model->updatePrinter($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line('printer_updated'));
            admin_redirect('pos/printers');
        } else {
            $this->data['printer']    = $printer;
            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('edit_printer');
            $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => admin_url('pos/printers'), 'page' => lang('printers')], ['link' => '#', 'page' => lang('edit_printer')]];
            $meta                     = ['page_title' => lang('edit_printer'), 'bc' => $bc];
            $this->page_construct('pos/edit_printer', $meta, $this->data);
        }
    }

    public function email_receipt($sale_id = null, $view = null)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->post('id')) {
            $sale_id = $this->input->post('id');
        }
        if (!$sale_id) {
            die('No sale selected.');
        }
        if ($this->input->post('email')) {
            $to = $this->input->post('email');
        }
        $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');

        $this->data['rows']            = $this->pos_model->getAllInvoiceItems($sale_id);
        $inv                           = $this->pos_model->getInvoiceByID($sale_id);
        $biller_id                     = $inv->biller_id;
        $customer_id                   = $inv->customer_id;
        $this->data['biller']          = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer']        = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments']        = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos']             = $this->pos_model->getSetting();
        $this->data['barcode']         = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale']     = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : null;
        $this->data['inv']             = $inv;
        $this->data['sid']             = $sale_id;
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['page_title']      = $this->lang->line('invoice');

        $receipt = $this->load->view($this->theme . 'pos/email_receipt', $this->data, true);
        if ($view) {
            echo $receipt;
            die();
        }

        if (!$to) {
            $to = $this->data['customer']->email;
        }
        if (!$to) {
            $this->sma->send_json(['msg' => $this->lang->line('no_meil_provided')]);
        }

        try {
            if ($this->sma->send_email($to, lang('receipt_from') . ' ' . $this->data['biller']->company, $receipt)) {
                $this->sma->send_json(['msg' => $this->lang->line('email_sent')]);
            } else {
                $this->sma->send_json(['msg' => $this->lang->line('email_failed')]);
            }
        } catch (Exception $e) {
            $this->sma->send_json(['msg' => $e->getMessage()]);
        }
    }

    public function get_printers()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }

        $this->load->library('datatables');
        $this->datatables
        ->select('id, title, type, profile, path, ip_address, port')
        ->from('printers')
        ->add_column('Actions', "<div class='text-center'> <a href='" . admin_url('pos/edit_printer/$1') . "' class='btn-warning btn-xs tip' title='" . lang('edit_printer') . "'><i class='fa fa-edit'></i></a> <a href='#' class='btn-danger btn-xs tip po' title='<b>" . lang('delete_printer') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('pos/delete_printer/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id')
        ->unset_column('id');
        echo $this->datatables->generate();
    }

    public function getProductDataByCode($code = null, $warehouse_id = null)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('code')) {
            $code = $this->input->get('code', true);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', true);
        }
        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id', true);
        }
        if (!$code) {
            echo null;
            die();
        }
        $warehouse      = $this->site->getWarehouseByID($warehouse_id);
        $customer       = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $row            = $this->pos_model->getWHProduct($code, $warehouse_id);
        $option         = false;
        if ($row) {
            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
            $row->item_tax_method = $row->tax_method;
            $row->qty             = 1;
            $row->discount        = '0';
            $row->serial          = '';
            $options              = $this->pos_model->getProductOptions($row->id, $warehouse_id);
            if ($options) {
                $opt = current($options);
                if (!$option) {
                    $option = $opt->id;
                }
            } else {
                $opt        = json_decode('{}');
                $opt->price = 0;
            }
            $row->option   = $option;
            $row->quantity = 0;
            $pis           = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
            if ($pis) {
                foreach ($pis as $pi) {
                    $row->quantity += $pi->quantity_balance;
                }
            }
            if ($row->type == 'standard' && (!$this->Settings->overselling && $row->quantity < 1)) {
                echo null;
                die();
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
            if ($customer_group) {
                if ($customer_group->discount && $customer_group->percent < 0) {
                    $row->discount = (0 - $customer_group->percent) . '%';
                } else {
                    $row->price = $row->price + (($row->price * $customer_group->percent) / 100);
                }
            }
            $row->real_unit_price = $row->price;
            $row->base_quantity   = 1;
            $row->base_unit       = $row->unit;
            $row->base_unit_price = $row->price;
            $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
            $row->comment         = '';
            $combo_items          = false;
            if ($row->type == 'combo') {
                $combo_items = $this->pos_model->getProductComboItems($row->id, $warehouse_id);
            }
            $units    = $this->site->getUnitsByBUID($row->base_unit);
            $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

            $pr = ['id' => sha1(uniqid(mt_rand(), true)), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options];

            $this->sma->send_json($pr);
        } else {
            echo null;
        }
    }

    public function getProductPromo($pId = null, $warehouse_id = null)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('product_id')) {
            $pId = $this->input->get('product_id', true);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', true);
        }
        $this->load->admin_model('promos_model');
        $promos = $this->promos_model->getPromosByProduct($pId);

        if ($promos) {
            foreach ($promos as $promo) {
                $warehouse = $this->site->getWarehouseByID($warehouse_id);
                $row       = $this->pos_model->getWHProductById($promo->product2get, $warehouse_id);
                $option    = false;
                if ($row) {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    $row->item_tax_method = $row->tax_method;
                    $row->qty             = 1;
                    $row->price           = 0;
                    $row->discount        = '0';
                    $row->serial          = '';
                    $options              = $this->pos_model->getProductOptions($row->id, $warehouse_id);
                    if ($options) {
                        $opt = current($options);
                        if (!$option) {
                            $option = $opt->id;
                        }
                    }
                    $row->option          = $option;
                    $row->real_unit_price = $row->price;
                    $row->base_quantity   = 1;
                    $row->base_unit       = $row->unit;
                    $row->base_unit_price = $row->price;
                    $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
                    $row->comment         = '';
                    $combo_items          = false;
                    // if ($row->type == 'combo') {
                    //     $combo_items = $this->pos_model->getProductComboItems($row->id, $warehouse_id);
                    // }
                    $units    = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = false; // $this->site->getTaxRateByID($row->tax_rate);

                    $pr = ['id' => sha1(uniqid(mt_rand(), true)), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options];

                    $this->sma->send_json($pr);
                } else {
                    echo null;
                }
            }
        } else {
            echo null;
        }
    }




     public function getSales($warehouse_id = null)
    {
        


  $detail_link_afip       = anchor('admin/sales/afip?sale_id=$1&tipo=post', '  <img src="https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png" style="width: 50px"> ' . lang('Enviar a ARCA'));

         $detail_link22       = anchor('admin/sales/notacredito/$1', '  <img src="https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png" style="width: 50px"> ' . lang('Nota de Crédito'));


      
        $duplicate_link    = anchor('admin/pos/?duplicate=$1', '<i class="fa fa-plus-square"></i> ' . lang('duplicate_sale'), 'class="duplicate_pos"');

        $duplicate_new_link    = anchor('admin/pos/?duplicate=$1&duplicate_new=$1', '<i class="fa fa-plus-square"></i> ' . lang('duplicar con precio actual'), 'class="duplicate_pos"');

        $detail_link       = anchor('admin/pos/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Ver ticket'));

        $detail_remito       = anchor('admin/sales/viewremito/$1', '  <i class="fa fa-file-text-o"></i> ' . lang('Ver Remito'));


            $detail_link_factura       = anchor('admin/pos/viewsale/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Ver factura'));


              $detail_link_factura_cc       = anchor('admin/pos/viewsalecc/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Ver factura CC'));
              

              $detail_link_cc       = anchor('admin/pos/viewcc/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Ver Ticket CC'));


         $detail_link_afip       = anchor('admin/sales/afip?sale_id=$1&tipo=post', '  <img src="https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png" style="width: 50px"> ' . lang('Enviar a ARCA'));

         $detail_link22       = anchor('admin/sales/notacredito/$1', '  <img src="https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png" style="width: 50px"> ' . lang('Nota de Crédito'));
     

        $detail_link2      = anchor('admin/sales/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details_modal'), 'data-toggle="modal" data-target="#myModal"');
        $detail_link3      = anchor('admin/sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $payments_link     = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link  = anchor('admin/pos/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $packagink_link    = anchor('admin/sales/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('admin/sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link        = anchor('admin/#', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'class="email_receipt" data-id="$1" data-email-address="$2"');
        $edit_link         = anchor('admin/sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link          = anchor('admin/sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));

        $return_link       = anchor('admin/sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('pos/deletepos/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_sale') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                
             
                <li>' . $detail_link_afip . '</li>
                <li>' . $detail_link22 . '</li>
                <li>' . $duplicate_link . '</li>
                <li>' . $duplicate_new_link . '</li>
                <li>' . $detail_link . '</li>
                 <li>' . $detail_link_factura. '</li>
                 <li>' . $detail_remito. '</li>

                 <li>' . $detail_link_factura_cc. '</li>
                  <li>' . $detail_link_cc. '</li>


          

                <li>' . $detail_link2 . '</li>
                <li>' . $detail_link3 . '</li>
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

        $this->load->library('datatables');

      


        if ($warehouse_id) {



            $this->datatables
                    ->select($this->db->dbprefix('sales') . ".id as id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, biller,username, customer, (grand_total+COALESCE(rounding, 0)), paid, CONCAT(grand_total, '__', rounding, '__', paid) as balance,   concat(if(CAE <>'' , 'ARCA', 'No Emitido'),if(nota_credito <>'nc' , '', ' (nc)')), payment_status, companies.email as cemail")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->join('users', 'sales.created_by=users.id', 'left')
                ->where('sales.warehouse_id', $warehouse_id)
                ->group_by('sales.id');
        } else {

        

            $this->datatables
                ->select($this->db->dbprefix('sales') . ".id as id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, biller,username, customer, (grand_total+COALESCE(rounding, 0)), paid, CONCAT(grand_total, '__', rounding, '__', paid) as balance,  concat(if(CAE <>'' , 'ARCA', 'No Emitido'),if(nota_credito <>'nc' , '', ' (nc)')), payment_status, companies.email as cemail")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->join('users', 'sales.created_by=users.id', 'left')
                ->group_by('sales.id');
        }
        $this->datatables->where('pos', 1);
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column('Actions', $action, 'id, cemail')->unset_column('cemail');
        echo $this->datatables->generate();
    }

    /* ---------------------------------------------------------------------------------------------------- */





    public function index($sid = null)
    {

      

        if (!$this->pos_settings->default_biller || !$this->pos_settings->default_customer || !$this->pos_settings->default_category) {
            $this->session->set_flashdata('warning', lang('please_update_settings'));
            admin_redirect('pos/settings');
        }
        if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) {
            $register_data = ['register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date];
            $this->session->set_userdata($register_data);
        } else {
            $this->session->set_flashdata('error', lang('register_not_open'));
            admin_redirect('pos/open_register');
        }

        $this->data['sid'] = $this->input->get('suspend_id') ? $this->input->get('suspend_id') : $sid;
        $did               = $this->input->post('delete_id') ? $this->input->post('delete_id') : null;
        $suspend           = $this->input->post('suspend') ? true : false;
        $count             = $this->input->post('count') ? $this->input->post('count') : null;

        $duplicate_sale = $this->input->get('duplicate') ? $this->input->get('duplicate') : null;
         $duplicate_new = $this->input->get('duplicate_new') ? $this->input->get('duplicate_new') : null;

        //validate form input
        $this->form_validation->set_rules('customer', $this->lang->line('customer'), 'trim|required');
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required');
        $this->form_validation->set_rules('biller', $this->lang->line('biller'), 'required');

        if ($this->form_validation->run() == true) {
            $date             = date('Y-m-d H:i:s');
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');

         
        //    $nuevocuit        = $this->input->post('nuevocuit');

            
           $nuevocuit        = $this->input->post('nuevocuit');






            $ruta =  getcwd();
                                                require $ruta."/app/config/database.php";

                                                $sql_vendedor_post = "SELECT c.id ,c.name,c.cf4 FROM sma_companies c WHERE  c.group_name='biller'";
                   

                                                   $resultado_post = $conn->query($sql_vendedor_post);
                                                   while($vp= $resultado_post->fetch_assoc() )
                                                      {
                                                          
                                                            $biller =trim($vp['name']);
                                                            $biller_id =trim($vp['id']);
                                                            $biller_tiporesponsable =trim($vp['cf4']);
                                                      }
                                                            



            $total_items      = $this->input->post('total_items');
            $payment_method      = $this->input->post('tipocomp');  // tipo de comprobante

         
            $enviaaafip      = $this->input->post('enviaaafip');//  es si va a afip o no


        

            $cuentacorriente      = $this->input->post('cuentacorriente');//  es si va a ctacte o no

           

            $sale_status      = 'completed';
            $payment_term     = 0;
            $due_date         = date('Y-m-d', strtotime('+' . $payment_term . ' days'));
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = $customer_details->company && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = $biller_details->company && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->sma->clear_tags($this->input->post('pos_note'));
            $staff_note       = $this->sma->clear_tags($this->input->post('staff_note'));

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
                $item_comment       = $_POST['product_comment'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                //$unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);

                $unit_price         = ($_POST['unit_price'][$r]);


                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = $_POST['serial'][$r]           ?? '';
                $item_tax_rate      = $_POST['product_tax'][$r]      ?? null;
                $item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $reference        = $this->site->getReference('pos');
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->pos_model->getProductByCode($item_code) : null;
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
                       // $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);

                          $pr_item_tax = (($item_tax * $item_unit_quantity));


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
                        'comment'           => $item_comment,
                    ];

                       



                    $products[] = ($product + $gst_data);
                   // $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);

                    $total += $item_net_price * $item_unit_quantity ;
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } elseif ($this->pos_settings->item_order == 0) {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax), true);
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            // $grand_total    = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            
          
                $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
          

 



            $rounding    = 0;
            if ($this->pos_settings->rounding) {
                $round_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
                $rounding    = $this->sma->formatMoney($round_total - $grand_total);
            }



         
            $recargotarjeta = $this->input->post('recargotarjeta') ;




                $data = ['date'         => $date,
                'reference_no'      => $reference,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $total,
                'manual_payment'    => $nuevocuit,
                'recargo_tarjeta'   =>  $recargotarjeta,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->sma->formatDecimal($shipping),
                'grand_total'       => ($grand_total + $recargotarjeta),
                'total_items'       => $total_items,
                'sale_status'       => $sale_status,
                'payment_status'    => $grand_total > 0 ? 'paid' : 'pending',
                //'payment_status'    => $grand_total ,
                'payment_term'      => $payment_term,
                'payment_method'    => $payment_method,
                'rounding'          => $rounding,
                'suspend_note'      => $this->input->post('suspend_note'),
                'pos'               => 1,
                'paid'              =>  $_POST['amount'][$r],
                'created_by'        => $this->session->userdata('user_id'),
                'hash'              => hash('sha256', microtime() . mt_rand()),
            ];

        





          
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            if (!$suspend) {
                $p    = isset($_POST['amount']) ? sizeof($_POST['amount']) : 0;
                $paid = 0;



                for ($r = 0; $r < $p; $r++) {
                    if (isset($_POST['amount'][$r]) && !empty($_POST['amount'][$r]) && isset($_POST['paid_by'][$r]) && !empty($_POST['paid_by'][$r])) {
                        $amount = $this->sma->formatDecimal($_POST['balance_amount'][$r] > 0 ? $_POST['amount'][$r] - $_POST['balance_amount'][$r] : $_POST['amount'][$r]);



                        if ($_POST['paid_by'][$r] == 'deposit') {
                            if (!$this->site->check_customer_deposit($customer_id, $amount)) {
                                $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                                redirect($_SERVER['HTTP_REFERER']);
                            }
                        }

                       
                                    $pago = $amount;



              


                            foreach ($_POST['amount2'] as $val) {
                            $idtipotarjeta = $val;
                            }



            if($cuentacorriente=="no")
            {
     

                        if ($_POST['paid_by'][$r] == 'gift_card') 

                        {
                            $gc            = $this->site->getGiftCardByNO($_POST['paying_gift_card_no'][$r]);
                            $amount_paying = $_POST['amount'][$r] >= $gc->balance ? $gc->balance : $_POST['amount'][$r];
                            $gc_balance    = $gc->balance - $amount_paying;
                            $payment[]     = [
                                'date' => $date,
                                'reference_no' => $this->site->getReference('pay'),
                                'amount'      => $pago,
                                'paid_by'     => $_POST['paid_by'][$r],
                                'cheque_no'   => $_POST['cheque_no'][$r],
                                'cc_no'       => $_POST['paying_gift_card_no'][$r],
                                'cc_holder'   => $_POST['cc_holder'][$r],
                                'cc_month'    => $_POST['cc_month'][$r],
                                'cc_year'     => $_POST['cc_year'][$r],
                                'cc_type'     => $_POST['cc_type'][$r],
                                'cc_cvv2'     => $_POST['cc_cvv2'][$r],
                                'created_by'  => $this->session->userdata('user_id'),
                                'type'        => 'received',
                                'note'        => $_POST['payment_note'][$r],
                                'cantidad_cuota'   => 0,
                                'recargototal'   => 0,
                                'recargotarjeta'   => 0,
                                'pos_paid'    => $_POST['amount'][$r],
                                'pos_balance' => $_POST['balance_amount'][$r],
                                'gc_balance'  => $gc_balance,
                            ];
                        } else {

                           

                                    
                                       //cc_type  tipo de tarjeta

                         
                                     if($_POST['paid_by'][$r]=="CC")
                                     {
                                             $payment[] = [
                                            'date' => $date,
                                             'reference_no' => $this->site->getReference('pay'),
                                            'amount'      => $pago,
                                            'paid_by'     => $_POST['paid_by'][$r],
                                            'cheque_no'   => $_POST['cheque_no'][$r],
                                            'cc_no'       => $_POST['cc_no'],
                                            'cc_holder'   => $_POST['rpaidby'][$r],
                                            'cc_month'    => $_POST['cc_month'][$r],
                                            'cc_year'     => $_POST['cc_year'][$r],
                                            'cc_type'     => $idtipotarjeta,
                                            'cc_cvv2'     => $_POST['cc_cvv2'][$r],
                                            'created_by'  => $this->session->userdata('user_id'),
                                            'type'        => 'received',
                                            'note'        => $_POST['payment_note'][$r],
                                            'cantidad_cuota'   => $this->input->post('cantidad_cuota'),
                                            'recargototal'   => $this->input->post('recargototal'),
                                            'recargotarjeta'   => $recargotarjeta,
                                            'pos_paid'    => $_POST['amount'][$r],
                                            'pos_balance' => $_POST['balance_amount'][$r],
                                        ];
                                     }
                                     else
                                     {
                                            $payment[] = [
                                            'date' => $date,
                                             'reference_no' => $this->site->getReference('pay'),
                                            'amount'      => $pago,
                                            'paid_by'     => $_POST['paid_by'][$r],
                                            'cheque_no'   => $_POST['cheque_no'][$r],
                                            'cc_no'       => $_POST['cc_no'],
                                            'cc_holder'   => $_POST['rpaidby'][$r],
                                            'cc_month'    => $_POST['cc_month'][$r],
                                            'cc_year'     => $_POST['cc_year'][$r],
                                            'cc_type'     => $_POST['cc_type'][$r],
                                            'cc_cvv2'     => $_POST['cc_cvv2'][$r],
                                            'created_by'  => $this->session->userdata('user_id'),
                                            'type'        => 'received',
                                            'note'        => $_POST['payment_note'][$r],
                                            'cantidad_cuota'   => 0,
                                            'recargototal'   => 0,
                                            'recargotarjeta'   => 0,
                                            'pos_paid'    => $_POST['amount'][$r],
                                            'pos_balance' => $_POST['balance_amount'][$r],
                                        ];

                                     }
                            



                                }

            } // si no es cuenta corriente 
            else
            {
              $payment = [];  
            }




                    }
                }
            }
            if (!isset($payment) || empty($payment)) {
                $payment = [];

               // echo "aqui sin payment" ; 
            }




      // $this->sma->print_arrays($data, $products, $payment);
        

        }

        if ($this->form_validation->run() == true && !empty($products) && !empty($data)) {
            if ($suspend) {
                if ($this->pos_model->suspendSale($data, $products, $did)) {
                    $this->session->set_userdata('remove_posls', 1);
                    $this->session->set_flashdata('message', $this->lang->line('sale_suspended'));
                    admin_redirect('pos');
                }
            } else {
                if ($sale = $this->pos_model->addSale($data, $products, $payment, $did)) {
                  


                  



                    $this->session->set_userdata('remove_posls', 1);
                    $msg = $this->lang->line('sale_added');
                    if (!empty($sale['message'])) {
                        foreach ($sale['message'] as $m) {
                            $msg .= '<br>' . $m;
                        }
                    }

                     // agrego producto nuevo con el recargo que hice de tarjeta
                          $ruta =  getcwd();
                          require $ruta."/app/config/database.php";
                           $sql_ultimoid = "SELECT MAX(s.id) AS id,s.customer_id  FROM sma_sales s";
                           $resultado = $conn->query($sql_ultimoid);
                           while($sigo= $resultado->fetch_assoc() )
                           { $ultimoid=$sigo['id'];$customer_id_gif=$sigo['customer_id']; } 


      

                  //update tarjeta de regalo 
$paying_gift_card_no = $_POST['paying_gift_card_no'] ;

foreach ($paying_gift_card_no as $color) {
 //   echo $color . "<br>";
    if($color >0 ) {  $mitarjeta  = $color; }

}






if($mitarjeta <> '')
{
     $sql_gif =" SELECT p.amount  FROM sma_payments p WHERE p.sale_id =".$ultimoid." AND p.paid_by = 'gift_card' "; 

    $resultado_gif  = $conn->query($sql_gif);
                           while($sigo_gif= $resultado_gif->fetch_assoc() )
                           { $amount_gif=$sigo_gif['amount']; } 




 $sql_gif2 =" SELECT g.balance  FROM sma_sales p
LEFT JOIN sma_gift_cards g ON g.customer_id = p.customer_id
 WHERE p.id = ".$ultimoid."  and p.customer_id = ".$customer_id_gif." AND g.card_no =".$mitarjeta ; 



   $resultado_gif2  = $conn->query($sql_gif2);
                           while($sigo_gif2= $resultado_gif2->fetch_assoc() )
                           { $balance_gif=$sigo_gif2['balance']; } 

  

$nuevobalance = $balance_gif  - $amount_gif ; 

$sql_update_gif = "update sma_gift_cards g set g.balance = ".$nuevobalance." where g.card_no = ".$mitarjeta ; 
$upd_tmp_gif=mysqli_query($conn, $sql_update_gif);
        
}








  if($recargotarjeta>=0)
           {


                   
                    $ivarecargotarjeta = round($recargotarjeta * 0.173554,2) ;  // 2,1
                    $recargotarjetasiniva = $recargotarjeta - round($ivarecargotarjeta,2) ; // 10 - 2,1 = 7,90

                    $product_id = "1010101010";
                    $product_code = "OT";
                    $product_name = "Otros servicios/productos";
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


                  // update el precio pagado si llego tarjeta


                      $sql_venta = "SELECT sum(s.total+s.recargo_tarjeta+s.product_tax) AS total FROM sma_sales s WHERE s.id =".$ultimoid;
                           $resultado_venta = $conn->query($sql_venta);
                           while($vent= $resultado_venta->fetch_assoc() )
                           { $ventatotal=$vent['total']; } 

                       $sql_update = " update sma_sales s set s.paid=".$ventatotal.",s.payment_status='paid' where id=".$ultimoid;

                       $update_tmp=mysqli_query($conn, $sql_update);


                       // actualizo el pago 
                        

                       



                  



           }
           else
           {
             $recargotarjeta = -1;
           }
       

           if($cuentacorriente=="si")
            {

                 $sql_update2 = " update sma_sales s set s.payment_status='pending',paid=0 where id=".$ultimoid;

                       $update_tmp2=mysqli_query($conn, $sql_update2);
            }




           if($enviaaafip=="si")
                    {


                        
/*  ***************************************INICIO PARTE AFIP********************************/

 // obtenemos el CAE


//averiguo el ultimo id ingresado en post 


 $ruta =  getcwd();
                          require $ruta."/app/config/database.php";
                           $sql_ultimoid = "SELECT MAX(s.id) AS id FROM sma_sales s WHERE s.pos=1";
                             $resultado = $conn->query($sql_ultimoid);
                               while($sigo= $resultado->fetch_assoc() )
                               { $ultimoid=$sigo['id']; } 



                        $ruta =  getcwd();


                        require $ruta."/app/config/database.php";
                        $sale_id = $ultimoid;

                      $sql_sales = "SELECT * FROM sma_sales s WHERE s.id=".$sale_id." and  s.sale_status ='completed' and s.payment_status = 'paid'";

                  


                  $resultado_sigo = $conn->query($sql_sales);
                    $resultado_sale = $conn->query($sql_sales);
                           while($sal= $resultado_sale->fetch_assoc() )
                           {

                            // grand total es el monto final de la venta 

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
                                       $sale_status =$sal['sale_status'];
                                       $CbteTipo=$sal['payment_method'];
                                       $fechacarga=$sal['date'];
                                       $nuevocuitentabla = $sal['manual_payment'];


                           }

                           $sql_comprador = "SELECT * FROM sma_companies c WHERE c.id=".$customer_id;
                               $resultado_comprador = $conn->query($sql_comprador);
                               while($comp= $resultado_comprador->fetch_assoc() )
                                  {

                                        $nombre_comprador =$comp['name'];
                                        $razon_social =$comp['company'];

                                        if(($nuevocuitentabla <>'') && ($total >=30000))
                                        {
                                         $numdoc_comprador = $nuevocuitentabla ;
                                        }
                                        else
                                        {
                                         $numdoc_comprador =str_replace("-","",$comp['vat_no']);
                                        }
                                                                               
                                        


                                        $direccion_comprador =$comp['address'];
                                        $ciudad_comprador =$comp['city'];
                                        $estado_comprador =$comp['state'];
                                        $cp_comprador =$comp['postal_code'];
                                        $email_comprador =$comp['email'];
                                        $tipodoc_comprador =$comp['cf1'];
                                        $tipoiva_comprador =$comp['cf3'];
                                        $tiporesponsable_comprador =$comp['cf4'];

                                  }



                                  // parte del vendedor 


                   $sql_vendedor = "SELECT * FROM sma_companies c WHERE c.id=".$biller_id." and group_name='biller'";
                 //  echo $sql_vendedor."<br>";

                               $resultado_vendedor = $conn->query($sql_vendedor);
                               while($vend= $resultado_vendedor->fetch_assoc() )
                                  {

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
                                        $empresaAlias = $vend['gst_no']; //alias de produccion 
                                        
                                        $PtoVta =$vend['cf6'];


                                  }


/***********************************************PArte de afipo***************************/



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



    
   
    


    /**
     * Aca se puede hacer una comparacion del Ultimo Comprobante Autorizado
     * y el ultimo comprobante que se registro en la base de datos.
     */
     //$total


  

    //Armando el array para el Request
    //La estructura de este array esta diseñada de acuerdo al registro XML del WebService y utiliza las variables antes declaradas.
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

    
     echo "Tratamiento de errores<br>";
     

        if (!$FeCAEResponse){
            /* Procesando ERRORES */

            echo '<h2 class="err">NO SE HA GENERADO EL CAE</h2>
                <h3 class="err">ERRORES DETECTADOS</h3>';

            $errores = $wsfe->getErrLog();
            if (isset($errores))
            {
                foreach ($errores as $v)
                {
                    pr($v);

                }


                             




            }
           echo "<hr/><h3>Response</h3>";
            

        }elseif (!$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE']){
           
           echo '<h2 class="msg">NO SE HA GENERADO EL CAE</h2>
                 <h3 class="msg">OBSERVACIONES INFORMADAS</h3>';

            if (isset($FeCAEResponse['FeDetResp']['FECAEDetResponse']['Observaciones']))
            {
                foreach ($FeCAEResponse['FeDetResp']['FECAEDetResponse']['Observaciones'] as $v)
                {
                    pr($v);
                }
            }
            echo "<hr/><h3>Response</h3>";
            
        }else if($FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE'])


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

                        echo " si obtuvo el cae" ;


                     $mibarcode = $empresaCuit . sprintf('%03d', $CbteTipo) . sprintf('%05d', $PtoVta) .$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE'].$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAEFchVto']."1";




                    //04018098650060000  5   72315468022663   20220812   1



                        // Generate imagen del codigo de barra de afip
                        $gen = new \Code39\Generator;
                        $image = $gen->generate($mibarcode);
                        $nombre_imagen = $sale_id.'_'.$empresaCuit.'.png' ;
                        // Save image to file
                        
                    //   echo $nombre_imagen ;

                        imagepng($image, './assets/uploads/logos/afip/'.$nombre_imagen);


                    /*
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
                    */



                    $puntoventa = str_pad($PtoVta, 6, "0", STR_PAD_LEFT);  
                    $comprobante = str_pad($CbteDesde, 8, "0", STR_PAD_LEFT);  
                    $numero_comprobante = $puntoventa."-".$comprobante;


                    $consulta = "update sma_sales sm set sm.CAE = '".$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAE']."',sm.CAEFchVto = '".$FeCAEResponse['FeDetResp']['FECAEDetResponse']['CAEFchVto']."',sm.imagen_cae='".$nombre_imagen."',sm.numero_comprobante='".$numero_comprobante."',sm.imagen_qr = '".$codeFile."' where sm.id =".$sale_id;


                   
                            $insert_tmp=mysqli_query($conn, $consulta);

                           

                    }
                    else
                    {

                    echo "no se obtuvo el cae";

                    }





      }

  // pr($FeCAEResponse);

echo "
            </td>
        </tr>
    </table>
   ";  
}
else
{
  echo '
    <hr/>
    <h3>Errores detectados al generar el Ticket de Acceso</h3>';
    pr($wsaa->getErrLog());  
}


/*  ***************************************FIN PARTE AFIP********************************/




                    }



// redirecciona a la pantalla de impresion

                    $this->session->set_flashdata('message', $msg);
                    $redirect_to = $this->pos_settings->after_sale_page ? 'pos' : 'pos/view/' . $sale['sale_id'];
                    if ($this->pos_settings->auto_print) {
                        if ($this->Settings->remote_printing != 1) {
                            $redirect_to .= '?print=' . $sale_id;
                        }
                    }
                    admin_redirect($redirect_to);
                }
            }
        } else {
            $this->data['old_sale'] = null;
            $this->data['oid']      = null;
            if ($duplicate_sale) {
                if ($old_sale = $this->pos_model->getInvoiceByID($duplicate_sale)) {
                    $inv_items              = $this->pos_model->getSaleItems($duplicate_sale);
                    $this->data['oid']      = $duplicate_sale;
                    $this->data['old_sale'] = $old_sale;
                    $this->data['message']  = lang('old_sale_loaded');
                    $this->data['customer'] = $this->pos_model->getCompanyByID($old_sale->customer_id);
                } else {
                    $this->session->set_flashdata('error', lang('bill_x_found'));
                    admin_redirect('pos');
                }
            }
            $this->data['suspend_sale'] = null;
            if ($sid) {
                if ($suspended_sale = $this->pos_model->getOpenBillByID($sid)) {
                    $inv_items                    = $this->pos_model->getSuspendedSaleItems($sid);
                    $this->data['sid']            = $sid;
                    $this->data['suspend_sale']   = $suspended_sale;
                    $this->data['message']        = lang('suspended_sale_loaded');
                    $this->data['customer']       = $this->pos_model->getCompanyByID($suspended_sale->customer_id);
                    $this->data['reference_note'] = $suspended_sale->suspend_note;
                } else {
                    $this->session->set_flashdata('error', lang('bill_x_found'));
                    admin_redirect('pos');
                }
            }

         

            if (($sid || $duplicate_sale) && $inv_items) {
                // krsort($inv_items);
                $c = rand(100000, 9999999);
                foreach ($inv_items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row             = json_decode('{}');
                        $row->tax_method = 0;
                        $row->quantity   = 0;
                    } else {
                        $category           = $this->site->getCategoryByID($row->category_id);
                        $row->category_name = $category->name;
                        unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    }
                    $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $row->quantity += $pi->quantity_balance;
                        }
                    }
                    $row->id   = $item->product_id;
                    $row->code = $item->product_code;
                    $row->name = $item->product_name;
                    $row->type = $item->product_type;
                    $row->quantity += $item->quantity;
                    $row->discount        = $item->discount ? $item->discount : '0';
              


if($duplicate_new=="")

{
                     $row->price           = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
              

                    $row->unit_price      = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
              

                    $row->real_unit_price = $item->real_unit_price;


              

}
else
{


                     $row->price           = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
              

                    $row->unit_price      = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
              

                         $ruta =  getcwd();
                         require $ruta."/app/config/database.php";

                         $sql_sales_precionuevo = "SELECT p.price FROM sma_products p WHERE p.id=".$row->id;

                           $resultado_precionuevo = $conn->query($sql_sales_precionuevo);
                           while($pn= $resultado_precionuevo->fetch_assoc() )
                           {$precionuevo=$pn['price'];}

                           $row->real_unit_price = $precionuevo ;

}
                   




 
                    $row->base_quantity   = $item->quantity;
                    $row->base_unit       = $row->unit ? $row->unit : $item->product_unit_id;
                    // $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                    $row->base_unit_price = $item->real_unit_price;
                    $row->unit            = $item->product_unit_id;
                    $row->qty             = $item->unit_quantity;
                    $row->tax_rate        = $item->tax_rate_id;
                    $row->serial          = $item->serial_no;
                    $row->option          = $item->option_id;
                    $options              = $this->pos_model->getProductOptions($row->id, $item->warehouse_id);

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

                    $row->comment = $item->comment ?? '';
                    $row->ordered = 1;
                    $combo_items  = false;
                    if ($row->type == 'combo') {
                        $combo_items = $this->pos_model->getProductComboItems($row->id, $item->warehouse_id);
                    }
                    $units    = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $ri       = $this->Settings->item_addition ? $row->id : $c;

                    $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                        'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, ];
                    $c++;
                }

                $this->data['items'] = json_encode($pr);
            // $this->sma->print_arrays($this->data['items']);
            } else {
                $this->data['customer']       = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
                $this->data['reference_note'] = null;
            }

            $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['message'] = $this->data['message'] ?? $this->session->flashdata('message');

            // $this->data['biller'] = $this->site->getCompanyByID($this->pos_settings->default_biller);
            $this->data['billers']       = $this->site->getAllCompanies('biller');
            $this->data['warehouses']    = $this->site->getAllWarehouses();

           


            $this->data['tax_rates']     = $this->site->getAllTaxRates();
            $this->data['user']          = $this->site->getUser();
            $this->data['tcp']           = $this->pos_model->products_count($this->pos_settings->default_category);
            $this->data['products']      = $this->ajaxproducts($this->pos_settings->default_category);
            $this->data['categories']    = $this->site->getAllCategories();
            $this->data['brands']        = $this->site->getAllBrands();
            $this->data['subcategories'] = $this->site->getSubCategories($this->pos_settings->default_category);
            $this->data['printer']       = $this->pos_model->getPrinterByID($this->pos_settings->printer);
            $order_printers              = json_decode($this->pos_settings->order_printers);
            $printers                    = [];
            if (!empty($order_printers)) {
                foreach ($order_printers as $printer_id) {
                    $printers[] = $this->pos_model->getPrinterByID($printer_id);
                }
            }
            $this->data['order_printers'] = $printers;
            $this->data['pos_settings']   = $this->pos_settings;

            if ($this->pos_settings->after_sale_page && $saleid = $this->input->get('print', true)) {
                if ($inv = $this->pos_model->getInvoiceByID($saleid)) {
                    $this->load->helper('pos');
                    if (!$this->session->userdata('view_right')) {
                        $this->sma->view_rights($inv->created_by, true);
                    }
                    $this->data['rows']            = $this->pos_model->getAllInvoiceItems($inv->id);
                    $this->data['biller']          = $this->pos_model->getCompanyByID($inv->biller_id);
                    $this->data['customer']        = $this->pos_model->getCompanyByID($inv->customer_id);
                    $this->data['payments']        = $this->pos_model->getInvoicePayments($inv->id);
                    $this->data['return_sale']     = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : null;
                    $this->data['return_rows']     = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : null;
                    $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : null;
                    $this->data['inv']             = $inv;
                    $this->data['print']           = $inv->id;
                    $this->data['created_by']      = $this->site->getUser($inv->created_by);
                }
            }



$sql_pos="SELECT p.cf_value2 FROM sma_pos_settings p ";


         $ruta =  getcwd();
          require $ruta."/app/config/database.php";
        $resultado_post = $conn->query($sql_pos);
          while($vp= $resultado_post->fetch_assoc() )
        {
                $cf_value2  = $vp["cf_value2"];

          }



                if ($cf_value2==1)

                {
                        $this->load->view($this->theme . 'pos/add_articulos', $this->data);

                }
                else

                {
                        $this->load->view($this->theme . 'pos/add', $this->data);

                }

    


        }
    }



    public function printers2()
    {



        
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('Tarjetas');
        $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => '#', 'page' => lang('Tarjetas')]];
        $meta                     = ['page_title' => lang('Tarjetas'), 'bc' => $bc];
        $this->page_construct('pos/printers2', $meta, $this->data);
    }




    public function install_update($file, $m_version, $version)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->load->helper('update');
        save_remote_file($file . '.zip');
        $this->sma->unzip('./files/updates/' . $file . '.zip');
        if ($m_version) {
            $this->load->library('migration');
            if (!$this->migration->latest()) {
                $this->session->set_flashdata('error', $this->migration->error_string());
                admin_redirect('pos/updates');
            }
        }
        $this->db->update('pos_settings', ['version' => $version], ['pos_id' => 1]);
        unlink('./files/updates/' . $file . '.zip');
        $this->session->set_flashdata('success', lang('update_done'));
        admin_redirect('pos/updates');
    }

    public function open_drawer()
    {
        $data = json_decode($this->input->get('data'));
        $this->load->library('escpos');
        $this->escpos->load($data->printer);
        $this->escpos->open_drawer();
    }

    public function open_register()
    {
        $this->sma->checkPermissions('index');
        $this->form_validation->set_rules('cash_in_hand', lang('cash_in_hand'), 'trim|required|numeric');
        if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) {
            $register_data = ['register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date];
            $this->session->set_userdata($register_data);
            admin_redirect('pos');
        }

        if ($this->form_validation->run() == true) {
            $data = [
                'date'         => date('Y-m-d H:i:s'),
                'cash_in_hand' => $this->input->post('cash_in_hand'),
                'user_id'      => $this->session->userdata('user_id'),
                'status'       => 'open',
            ];
        }
        if ($this->form_validation->run() == true && $this->pos_model->openRegister($data)) {
            $this->session->set_flashdata('message', lang('welcome_to_pos'));
            admin_redirect('pos');
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('open_register')]];
            $meta                = ['page_title' => lang('open_register'), 'bc' => $bc];
            $this->page_construct('pos/open_register', $meta, $this->data);
        }
    }

    public function opened_bills($per_page = 0)
    {
        $this->load->library('pagination');

        //$this->table->set_heading('Id', 'The Title', 'The Content');
        if ($this->input->get('per_page')) {
            $per_page = $this->input->get('per_page');
        }

        $config['base_url']   = admin_url('pos/opened_bills');
        $config['total_rows'] = $this->pos_model->bills_count();
        $config['per_page']   = 6;
        $config['num_links']  = 3;

        $config['full_tag_open']   = '<ul class="pagination pagination-sm">';
        $config['full_tag_close']  = '</ul>';
        $config['first_tag_open']  = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open']   = '<li>';
        $config['last_tag_close']  = '</li>';
        $config['next_tag_open']   = '<li>';
        $config['next_tag_close']  = '</li>';
        $config['prev_tag_open']   = '<li>';
        $config['prev_tag_close']  = '</li>';
        $config['num_tag_open']    = '<li>';
        $config['num_tag_close']   = '</li>';
        $config['cur_tag_open']    = '<li class="active"><a>';
        $config['cur_tag_close']   = '</a></li>';

        $this->pagination->initialize($config);
        $data['r'] = true;
        $bills     = $this->pos_model->fetch_bills($config['per_page'], $per_page);
        if (!empty($bills)) {
            $html = '';
            $html .= '<ul class="ob">';
            foreach ($bills as $bill) {
                $html .= '<li><button type="button" class="btn btn-info sus_sale" id="' . $bill->id . '"><p>' . $bill->suspend_note . '</p><strong>' . $bill->customer . '</strong><br>' . lang('date') . ': ' . $bill->date . '<br>' . lang('items') . ': ' . $bill->count . '<br>' . lang('total') . ': ' . $this->sma->formatMoney($bill->total) . '</button></li>';
            }
            $html .= '</ul>';
        } else {
            $html      = '<h3>' . lang('no_opeded_bill') . '</h3><p>&nbsp;</p>';
            $data['r'] = false;
        }

        $data['html'] = $html;

        $data['page'] = $this->pagination->create_links();
        echo $this->load->view($this->theme . 'pos/opened', $data, true);
    }

    public function p()
    {
        $data = json_decode($this->input->get('data'));
        $this->load->library('escpos');
        $this->escpos->load($data->printer);
        $this->escpos->print_receipt($data);
    }

    public function paypal_balance()
    {
        if (!$this->Owner) {
            return false;
        }
        $this->load->admin_model('paypal_payments');

        return $this->paypal_payments->get_balance();
    }

    public function printers()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('pos');
        }
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('printers');
        $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => '#', 'page' => lang('printers')]];
        $meta                     = ['page_title' => lang('list_printers'), 'bc' => $bc];
        $this->page_construct('pos/printers', $meta, $this->data);
    }

    public function register_details()
    {
        $this->sma->checkPermissions('index');
        $register_open_time           = $this->session->userdata('register_open_time');
        $this->data['error']          = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales']        = $this->pos_model->getRegisterCCSales($register_open_time);
        $this->data['cashsales']      = $this->pos_model->getRegisterCashSales($register_open_time);
        $this->data['chsales']        = $this->pos_model->getRegisterChSales($register_open_time);
        $this->data['gcsales']        = $this->pos_model->getRegisterGCSales($register_open_time);
        $this->data['pppsales']       = $this->pos_model->getRegisterPPPSales($register_open_time);
        $this->data['stripesales']    = $this->pos_model->getRegisterStripeSales($register_open_time);
        $this->data['othersales']     = $this->pos_model->getRegisterOtherSales($register_open_time);
        $this->data['authorizesales'] = $this->pos_model->getRegisterAuthorizeSales($register_open_time);
        $this->data['totalsales']     = $this->pos_model->getRegisterSales($register_open_time);
        $this->data['refunds']        = $this->pos_model->getRegisterRefunds($register_open_time);
        $this->data['returns']        = $this->pos_model->getRegisterReturns($register_open_time);
        $this->data['expenses']       = $this->pos_model->getRegisterExpenses($register_open_time);
        $this->load->view($this->theme . 'pos/register_details', $this->data);
    }

    public function registers()
    {
        $this->sma->checkPermissions();

        $this->data['error']     = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['registers'] = $this->pos_model->getOpenRegisters();
        $this->data['closeregisters'] = $this->pos_model->getCloseRegisters();
        $bc                      = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => '#', 'page' => lang('open_registers')]];
        $meta                    = ['page_title' => lang('open_registers'), 'bc' => $bc];
        $this->page_construct('pos/registers', $meta, $this->data);
    }

    public function sales($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $user                       = $this->site->getUser();
            $this->data['warehouses']   = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse']    = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => '#', 'page' => lang('pos_sales')]];
        $meta = ['page_title' => lang('pos_sales'), 'bc' => $bc];
        $this->page_construct('pos/sales', $meta, $this->data);
    }

    public function settings()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('pro_limit', $this->lang->line('pro_limit'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('pin_code', $this->lang->line('delete_code'), 'numeric');
        $this->form_validation->set_rules('category', $this->lang->line('default_category'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('customer', $this->lang->line('default_customer'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('biller', $this->lang->line('default_biller'), 'required|is_natural_no_zero');

        if ($this->form_validation->run() == true) {
            $data = [
                'pro_limit'                 => $this->input->post('pro_limit'),
                'pin_code'                  => $this->input->post('pin_code') ? $this->input->post('pin_code') : null,
                'default_category'          => $this->input->post('category'),
                'default_customer'          => $this->input->post('customer'),
                'default_biller'            => $this->input->post('biller'),
                'display_time'              => $this->input->post('display_time'),
                'receipt_printer'           => $this->input->post('receipt_printer'),
                'cash_drawer_codes'         => $this->input->post('cash_drawer_codes'),
                'cf_title1'                 => $this->input->post('cf_title1'),
                'cf_title2'                 => $this->input->post('cf_title2'),
                'cf_value1'                 => $this->input->post('cf_value1'),
                'cf_value2'                 => $this->input->post('cf_value2'),
                'focus_add_item'            => $this->input->post('focus_add_item'),
                'add_manual_product'        => $this->input->post('add_manual_product'),
                'customer_selection'        => $this->input->post('customer_selection'),
                'add_customer'              => $this->input->post('add_customer'),
                'toggle_category_slider'    => $this->input->post('toggle_category_slider'),
                'toggle_subcategory_slider' => $this->input->post('toggle_subcategory_slider'),
                'toggle_brands_slider'      => $this->input->post('toggle_brands_slider'),
                'cancel_sale'               => $this->input->post('cancel_sale'),
                'suspend_sale'              => $this->input->post('suspend_sale'),
                'print_items_list'          => $this->input->post('print_items_list'),
                'finalize_sale'             => $this->input->post('finalize_sale'),
                'today_sale'                => $this->input->post('today_sale'),
                'open_hold_bills'           => $this->input->post('open_hold_bills'),
                'close_register'            => $this->input->post('close_register'),
                'tooltips'                  => $this->input->post('tooltips'),
                'keyboard'                  => $this->input->post('keyboard'),
                'pos_printers'              => $this->input->post('pos_printers'),
                'java_applet'               => $this->input->post('enable_java_applet'),
                'product_button_color'      => $this->input->post('product_button_color'),
                'paypal_pro'                => $this->input->post('paypal_pro'),
                'stripe'                    => $this->input->post('stripe'),
                'authorize'                 => $this->input->post('authorize'),
                'rounding'                  => $this->input->post('rounding'),
                'item_order'                => $this->input->post('item_order'),
                'after_sale_page'           => $this->input->post('after_sale_page'),
                'printer'                   => $this->input->post('receipt_printer'),
                'order_printers'            => json_encode($this->input->post('order_printers')),
                'auto_print'                => $this->input->post('auto_print'),
                'remote_printing'           => DEMO ? 1 : $this->input->post('remote_printing'),
                'customer_details'          => $this->input->post('customer_details'),
                'local_printers'            => $this->input->post('local_printers'),
            ];
            $payment_config = [
                'APIUsername'            => $this->input->post('APIUsername'),
                'APIPassword'            => $this->input->post('APIPassword'),
                'APISignature'           => $this->input->post('APISignature'),
                'stripe_secret_key'      => $this->input->post('stripe_secret_key'),
                'stripe_publishable_key' => $this->input->post('stripe_publishable_key'),
                'api_login_id'           => $this->input->post('api_login_id'),
                'api_transaction_key'    => $this->input->post('api_transaction_key'),
            ];
        } elseif ($this->input->post('update_settings')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('pos/settings');
        }

        if ($this->form_validation->run() == true && $this->pos_model->updateSetting($data)) {
            if (DEMO) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                admin_redirect('pos/settings');
            }
            if ($this->write_payments_config($payment_config)) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                admin_redirect('pos/settings');
            } else {
                $this->session->set_flashdata('error', $this->lang->line('pos_setting_updated_payment_failed'));
                admin_redirect('pos/settings');
            }
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['pos']        = $this->pos_model->getSetting();
            $this->data['categories'] = $this->site->getAllCategories();
            //$this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
            $this->data['billers'] = $this->pos_model->getAllBillerCompanies();
            $this->config->load('payment_gateways');
            $this->data['stripe_secret_key']      = $this->config->item('stripe_secret_key');
            $this->data['stripe_publishable_key'] = $this->config->item('stripe_publishable_key');
            $authorize                            = $this->config->item('authorize');
            $this->data['api_login_id']           = $authorize['api_login_id'];
            $this->data['api_transaction_key']    = $authorize['api_transaction_key'];
            $this->data['APIUsername']            = $this->config->item('APIUsername');
            $this->data['APIPassword']            = $this->config->item('APIPassword');
            $this->data['APISignature']           = $this->config->item('APISignature');
            $this->data['printers']               = $this->pos_model->getAllPrinters();
            $this->data['paypal_balance']         = null; // $this->pos_settings->paypal_pro ? $this->paypal_balance() : NULL;
            $this->data['stripe_balance']         = null; // $this->pos_settings->stripe ? $this->stripe_balance() : NULL;
            $bc                                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('pos_settings')]];
            $meta                                 = ['page_title' => lang('pos_settings'), 'bc' => $bc];
            $this->page_construct('pos/settings', $meta, $this->data);
        }
    }

    public function stripe_balance()
    {
        if (!$this->Owner) {
            return false;
        }
        $this->load->admin_model('stripe_payments');

        return $this->stripe_payments->get_balance();
    }

    public function today_sale()
    {
        if (!$this->Owner && !$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }

        $this->data['error']          = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales']        = $this->pos_model->getTodayCCSales();
        $this->data['cashsales']      = $this->pos_model->getTodayCashSales();
        $this->data['chsales']        = $this->pos_model->getTodayChSales();
        $this->data['pppsales']       = $this->pos_model->getTodayPPPSales();
        $this->data['stripesales']    = $this->pos_model->getTodayStripeSales();
        $this->data['authorizesales'] = $this->pos_model->getTodayAuthorizeSales();
        $this->data['totalsales']     = $this->pos_model->getTodaySales();
        $this->data['refunds']        = $this->pos_model->getTodayRefunds();
        $this->data['returns']        = $this->pos_model->getTodayReturns();
        $this->data['expenses']       = $this->pos_model->getTodayExpenses();
        $this->load->view($this->theme . 'pos/today_sale', $this->data);
    }

    public function updates()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->form_validation->set_rules('purchase_code', lang('purchase_code'), 'required');
        $this->form_validation->set_rules('envato_username', lang('envato_username'), 'required');
        if ($this->form_validation->run() == true) {
            $this->db->update('pos_settings', ['purchase_code' => $this->input->post('purchase_code', true), 'envato_username' => $this->input->post('envato_username', true)], ['pos_id' => 1]);
            admin_redirect('pos/updates');
        } else {
            $fields = ['version' => $this->pos_settings->version, 'code' => $this->pos_settings->purchase_code, 'username' => $this->pos_settings->envato_username, 'site' => base_url()];
            $this->load->helper('update');
            $protocol              = is_https() ? 'https://' : 'http://';
            $updates               = get_remote_contents($protocol . 'api.tecdiary.com/v1/update/', $fields);
            $this->data['updates'] = json_decode($updates);
            $bc                    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('updates')]];
            $meta                  = ['page_title' => lang('updates'), 'bc' => $bc];
            $this->page_construct('pos/updates', $meta, $this->data);
        }
    }

    /* ------------------------------------------------------------------------------------ */

    public function view($sale_id = null, $modal = null)
    {
        $this->sma->checkPermissions('index');
        $this->load->library('inv_qrcode');
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('pos');
        $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv                   = $this->pos_model->getInvoiceByID($sale_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['rows']            = $this->pos_model->getAllInvoiceItems($sale_id);
        $biller_id                     = $inv->biller_id;
        $customer_id                   = $inv->customer_id;
        $this->data['biller']          = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer']        = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments']        = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos']             = $this->pos_model->getSetting();
        $this->data['barcode']         = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale']     = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : null;
        $this->data['inv']             = $inv;
        $this->data['sid']             = $sale_id;
        $this->data['modal']           = $modal;
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['printer']         = $this->pos_model->getPrinterByID($this->pos_settings->printer);
        $this->data['page_title']      = $this->lang->line('invoice');
        $this->load->view($this->theme . 'pos/view', $this->data);
    }


     public function viewcc($sale_id = null, $modal = null)
    {
        $this->sma->checkPermissions('index');
        $this->load->library('inv_qrcode');
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('pos');
        $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv                   = $this->pos_model->getInvoiceByID($sale_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['rows']            = $this->pos_model->getAllInvoiceItems($sale_id);
        $biller_id                     = $inv->biller_id;
        $customer_id                   = $inv->customer_id;
        $this->data['biller']          = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer']        = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments']        = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos']             = $this->pos_model->getSetting();
        $this->data['barcode']         = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale']     = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : null;
        $this->data['inv']             = $inv;
        $this->data['sid']             = $sale_id;
        $this->data['modal']           = $modal;
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['printer']         = $this->pos_model->getPrinterByID($this->pos_settings->printer);
        $this->data['page_title']      = $this->lang->line('invoice');
        $this->load->view($this->theme . 'pos/viewcc', $this->data);
    }


      public function viewx($sale_id = null, $modal = null)
    {
        $this->sma->checkPermissions('index');
        $this->load->library('inv_qrcode');
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('pos');
        $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv                   = $this->pos_model->getInvoiceByID($sale_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['rows']            = $this->pos_model->getAllInvoiceItems($sale_id);
        $biller_id                     = $inv->biller_id;
        $customer_id                   = $inv->customer_id;
        $this->data['biller']          = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer']        = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments']        = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos']             = $this->pos_model->getSetting();
        $this->data['barcode']         = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale']     = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : null;
        $this->data['inv']             = $inv;
        $this->data['sid']             = $sale_id;
        $this->data['modal']           = $modal;
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['printer']         = $this->pos_model->getPrinterByID($this->pos_settings->printer);
        $this->data['page_title']      = $this->lang->line('invoice');
        $this->load->view($this->theme . 'pos/viewx', $this->data);
    }



    public function view_bill()
    {
        $this->sma->checkPermissions('index');
        $this->data['tax_rates'] = $this->site->getAllTaxRates();
        $this->load->view($this->theme . 'pos/view_bill', $this->data);
    }

    public function write_payments_config($config)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        if (DEMO) {
            return true;
        }
        $file_contents = file_get_contents('./assets/config_dumps/payment_gateways.php');
        $output_path   = APPPATH . 'config/payment_gateways.php';
        $this->load->library('parser');
        $parse_data = [
            'APIUsername'            => $config['APIUsername'],
            'APIPassword'            => $config['APIPassword'],
            'APISignature'           => $config['APISignature'],
            'stripe_secret_key'      => $config['stripe_secret_key'],
            'stripe_publishable_key' => $config['stripe_publishable_key'],
            'api_login_id'           => $config['api_login_id'],
            'api_transaction_key'    => $config['api_transaction_key'],
        ];
        $new_config = $this->parser->parse_string($file_contents, $parse_data);

        $handle = fopen($output_path, 'w+');
        @chmod($output_path, 0777);

        if (is_writable($output_path)) {
            if (fwrite($handle, $new_config)) {
                @chmod($output_path, 0644);
                return true;
            }
            @chmod($output_path, 0644);
            return false;
        }
        @chmod($output_path, 0644);
        return false;
    }
}


