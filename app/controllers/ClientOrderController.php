<?php 

require base_path('app/repositories/OrderRepository.php');
require base_path('app/repositories/PaymentRepository.php');


class ClientOrderController {
    private object $order_repo; 
    private object $payment_repo; 

    public function __construct()
    {
        $this->order_repo = new OrderRepository();
        $this->payment_repo = new PaymentRepository();
    }

    public function index() {
        $orders = $this->order_repo->getAll();
        view('client/orders/index', ['orders' => $orders]);
        
    }

    public function show() {
        $order_id = (int)trim($_GET['id']);
        if(!$order_id) {
            abort(404);
        }
        $this->checkOwnership($order_id);

        $items = $this->order_repo->getOrderItems($order_id);
        if(empty($items)) {
            abort(404);
        }
        view('client/orders/show', ['items' => $items]);
    }

    public function increment() {
        $item_id = (int)trim($_POST['item_id']);
        $order_id = (int)trim($_POST['order_id']);

        $this->checkOwnership($order_id);

        $this->checkPendingStatus($order_id);

        $incremented = $this->order_repo->incrementItem($item_id);
        if(!$incremented) {
            flash('errors', 'unable to increment item');
            redirect('/orders/show?id=' . $order_id);
        }
        flash('success', 'item incremented');
        redirect('/orders/show?id=' . $order_id);
    }

    public function decrement() {
        $item_id = (int)trim($_POST['item_id']);
        $order_id = (int)trim($_POST['order_id']);

        $this->checkOwnership($order_id);

        $this->checkPendingStatus($order_id);

        $decremented = $this->order_repo->decrementItem($item_id);
        if(!$decremented) {
            abort(500);
        }

        flash('success', 'item decremented');
        redirect('/orders/show?id=' . $order_id);
    }

    public function remove() {
        $item_id = (int)trim($_POST['item_id']);
        $order_id = (int)trim($_POST['order_id']);

        $this->checkOwnership($order_id);

        $this->checkPendingStatus($order_id);

        $removed = $this->order_repo->removeItem($item_id, $order_id);
        if(!$removed) {
            abort(500);
        }

        flash('success', 'item removed');

        $order = $this->order_repo->getOrderById($order_id);
        if(!$order) {
            redirect('/orders');
        }
        redirect('/orders/show?id=' . $order_id);
    }

    public function updateNotes() {
        $order_id = (int)trim($_POST['order_id']);
        $new_notes = trim($_POST['notes']);
        
        $this->checkOwnership($order_id);

        $this->checkPendingStatus($order_id);

        $updated = $this->order_repo->updateOrderNotes($order_id, $new_notes);
        if(!$updated) {
            abort(404);
        }
        flash('success', 'notes updated');
        redirect('/orders/show?id=' . $order_id);
    }

    public function payForm() {
        $order_id = (int)trim($_GET['id']);
        if(!$order_id) {
            abort(404);
        }
        $this->checkOwnership($order_id);
        $this->checkCancelledStatus($order_id);
        $this->checkPaymentStatus($order_id);
        if($this->checkPaymentMethod($order_id) === 'cash'){
            flash('errors', 'this order is marked to pay cash');
            redirect('/orders');
        }
        $order = $this->order_repo->getOrderById($order_id);
        $total_price = $order['total_price'];
        view('client/orders/paymentForm', [
            'order_id' => $order_id, 
            'total_price' => $total_price
        ]);
    }

    public function pay() {
        $order_id = (int)trim($_POST['order_id']);
        $this->checkOwnership($order_id);
        $this->checkCancelledStatus($order_id);
        $this->checkPaymentStatus($order_id);
        if($this->checkPaymentMethod($order_id) === 'cash'){
            flash('errors', 'this order is marked to pay cash');
            redirect('/orders');
        }
        $old_payment_status =  $this->payment_repo->getPaymentStatusByOrderId($order_id);
        extract($this->canUPdatePaymentStatus($old_payment_status, 'paid'));
        if($can_update === false){
            flash('errors', $message);
            redirect('/admin/orders');
        }
        $paid = $this->payment_repo->payOrderByOrderId($order_id);
        if(!$paid){
            flash('errors', 'failed to paid for this order, try again');
            redirect('/orders');
        }
        flash('success', 'order paid successfully');
        redirect('/orders');
    }

    public function checkOwnership(int $order_id){
        $order_owner = $this->order_repo->getOrderOwner($order_id);
        if(user()['id'] !== $order_owner) {
            flash('errors', 'not your order');
            redirect('/orders');
        }
    }

    public function checkPendingStatus(int $order_id) {
        $order_status = $this->order_repo->getOrderStatus($order_id);
        if(!$order_status) {
            abort(404);
        }
        if($order_status !== 'pending') {
            flash('errors', 'you can not update this order');
            redirect('/orders');
        }
    }
    public function checkCancelledStatus(int $order_id) {
        $order_status = $this->order_repo->getOrderStatus($order_id);
        if(!$order_status) {
            abort(404);
        }
        if($order_status === 'cancelled') {
            flash('errors', 'this order has already been cancelled, you can not pay for it');
            redirect('/orders');
        }
    }

    public function checkPaymentStatus(int $order_id){
        $payment_status = $this->payment_repo->getPaymentStatusByOrderId($order_id);
        if(!$payment_status) {
            abort(404);
        }
        if($payment_status === 'paid') {
            flash('errors', 'this orders has already been paid');
            redirect('/orders');
        }
    }

    public function checkPaymentMethod(int $order_id){
        $payment = $this->payment_repo->getPaymentByOrderId($order_id);
        if(!$payment){
            abort(404);
        }
        return $payment['payment_method'];
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