<?php  

require base_path('app/repositories/AdminOrderRepository.php');
require base_path('app/repositories/OrderRepository.php');
require base_path('app/repositories/PaymentRepository.php');



class AdminOrderController{
    private object $admin_order_repo;
    private object $order_repo; 
    private object $payment_repo; 

    public function __construct(){
        $this->admin_order_repo = new AdminOrderRepository();
        $this->order_repo = new OrderRepository();
        $this->payment_repo = new PaymentRepository();

    }
    
    public function index(){
        $filter = $this->get('filter');
        if(in_array($filter, ['pending', 'preparing', 'delivered', 'cancelled'])) {
            $orders = $this->admin_order_repo->getAllOrdersByStatus($filter);
        }else {
            $orders = $this->admin_order_repo->getAllOrders();
        }
        view('admin/order/index', ['orders' => $orders]);
    }

    public function show() {
        $order_id = (int)$this->get('id');
        if(!$order_id) {
            abort(404);
        }
        $order = $this->admin_order_repo->getOrderById($order_id);
        if(empty($order)){
            abort(404);
        }
        view('admin/order/show', ['items' => $order]);
    }

    public function update() {
        $order_id = (int)$this->post('order_id');
        $new_status = $this->post('new_status');
        if($new_status === ''){
            redirect('/admin/orders/show?id=' . $order_id);
        }
        if(!$order_id) {
            abort(404);
        }
        $status = $this->admin_order_repo->getOrderStatus($order_id);
        if(!$status) {
            abort(404);
        }

        $can_update = $this->canUpdateStatus($status, $new_status);
        if(!$can_update['can_update']) {
            flash('errors', $can_update['message']);
            redirect('/admin/orders/show?id=' . $order_id);
        }

        $updated = $this->admin_order_repo->updateOrderStatus($order_id, $new_status);
        if($updated['status'] === false){
            flash('errors', $updated['message']);
            redirect('/admin/orders/show?id=' . $order_id);
        }
        flash('success', 'status updated successfully');
        redirect('/admin/orders/show?id=' . $order_id);

    }

    public function payForm() {
        $order_id = $this->get('id');
        if(!$order_id) {
            abort(404);
        }
        $this->checkCancelledStatus($order_id);
        $this->checkPaymentStatus($order_id);
        if($this->checkPaymentMethod($order_id) === 'cash'){
            flash('errors', 'this order is marked to pay cash');
            redirect('/admin/orders');
        }
        $order = $this->order_repo->getOrderById($order_id);
        $total_price = $order['total_price'];
        view('admin/order/paymentForm', ['order_id' => $order_id, 'total_price' => $total_price]);
    }

    public function pay() {
        $order_id = (int)$this->post('id');
        if(!$order_id) {
            abort(404);
        }
        $this->checkCancelledStatus($order_id);
        $this->checkPaymentStatus($order_id);
        $old_payment_status =  $this->payment_repo->getPaymentStatusByOrderId($order_id);
        extract($this->canUPdatePaymentStatus($old_payment_status, 'paid'));
        if($can_update === false){
            flash('errors', $message);
            redirect('/admin/orders');
        }
        $paid = $this->payment_repo->payOrderByOrderId($order_id);
        if(!$paid){
            flash('errors', 'failed to paid for this order, try again');
            redirect('/admin/orders');
        }
        flash('success', 'order paid successfully');
        redirect('/admin/orders');
    }

    public function updatePaymentStatus() {
        $order_id = (int)$this->post('id');
        if(!$order_id) {
            abort(404);
        }
        $this->checkCancelledStatus($order_id);
        $this->checkPaymentStatus($order_id);
        $updated = $this->payment_repo->payOrderByOrderId($order_id);
        if(!$updated) {
            flash('errors', 'failed to update payment status');
            redirect('/admin/orders');
        }
        flash('success', 'payment status updated successfully');
        redirect('/admin/orders');
    }

    public function get(string $key) {
        return isset($_GET[$key]) ? trim($_GET[$key]) : '';
    }

    public function post(string $key) {
        return isset($_POST[$key]) ? trim($_POST[$key]) : '';
    }

    public function checkCancelledStatus(int $order_id) {
        $order_status = $this->order_repo->getOrderStatus($order_id);
        if(!$order_status) {
            abort(404);
        }
        if($order_status === 'cancelled') {
            flash('errors', 'this order has already been cancelled, you can not pay for it');
            redirect('/admin/orders');
        }
    }

    public function checkPaymentStatus(int $order_id){
        $payment_status = $this->payment_repo->getPaymentStatusByOrderId($order_id);
        if(!$payment_status) {
            abort(404);
        }
        if($payment_status === 'paid') {
            flash('errors', 'this orders has already been paid');
            redirect('/admin/orders');
        }

    }

    public function checkPaymentMethod(int $order_id){
        $payment = $this->payment_repo->getPaymentByOrderId($order_id);
        if(!$payment){
            abort(404);
        }
        return $payment['payment_method'];
    }


    public function canUpdateStatus(string $status, string $new_status): array{
        switch ($status) {
            case 'pending': 
                if($new_status === 'delivered') {
                    return [
                        'can_update' => false, 
                        'message' => 'order should prepared first before delivering'
                    ];
                }
                break;
            case 'preparing': 
                if($new_status === 'cancelled') {
                    return [
                        'can_update' => false, 
                        'message' => 'order already being preparing , you cannot cancel now'
                    ];
                }
                if($new_status === 'pending') {
                    return [
                        'can_update' => false, 
                        'message' => 'order is being preparing, you can start delivering it later'
                    ];
                }
                break;
            case 'delivered': 
                if($new_status === 'pending' || $new_status === 'preparing' || $new_status === 'cancelled'){
                    return [
                        'can_update' => false, 
                        'message' => 'order has already been delivered'
                    ];
                }
                break;
            case 'cancelled': 
                if($new_status === 'pending' || $new_status === 'preparing' || $new_status === 'delivered'){
                    return [
                        'can_update' => false, 
                        'message' => 'order has already been cancelled'
                    ];
                }
                break;

        }
        return [
            'can_update' => true, 
            'message' => ''
        ];
    }

    public function canUPdatePaymentStatus(string $old_status, string $new_status) {
        switch ($old_status){
            case 'pending': 
                if($new_status === 'refunded') {
                    return [
                        'can_update' => false, 
                        'message' => 'the order has not been paid yet. you can not refund'
                    ];
                }
                break;
            
            case 'paid': 
                if($new_status === 'pending' || $new_status === 'failed') {
                    return [
                        'can_update' => false, 
                        'message' => 'this order has been successfully paid earlier, you can not mark it pending or failed again.'
                    ];
                }
                break;

            case 'failed': 
                if($new_status === 'pending' || $new_status === 'refunded') {
                    return [
                        'can_update' => false, 
                        'message' => 'you can not mark a failed payment as pending or refunded'
                    ];
                }
                break;

            case 'refunded': 
                if($new_status === 'pending' || $new_status === 'paid' || $new_status === 'failed'){
                    return [
                        'can_update' => false, 
                        'message' => 'this payment has been refunded. you can not update it'
                    ];
                }
                break;
        }
        return [
            'can_update' => true, 
            'message' => 'payment update successfully'
        ];
    }


}