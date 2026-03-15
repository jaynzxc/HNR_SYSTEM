<?php
// admin/orders_pos.php
session_start();
require_once '../config/database.php';
require_once '../models/OrderModel.php';
require_once '../models/MenuModel.php';

$database = new Database();
$db = $database->getConnection();
$order = new OrderModel($db);
$menu = new MenuModel($db);

// Get initial data
$stats = $order->getStatistics();
$order_types = $order->getOrderTypes();
$menu_items = $menu->getMenuItems(['is_available' => 1]);
$active_orders = $order->getOrders(['status' => 'pending,confirmed,preparing']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Orders / POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .transition-side { transition: all 0.2s ease; }
        .dropdown-arrow { transition: transform 0.2s; }
        details[open] .dropdown-arrow { transform: rotate(90deg); }
        details > summary { list-style: none; }
        .toast {
            position: fixed; bottom: 20px; right: 20px;
            background: #10b981; color: white; padding: 12px 24px;
            border-radius: 8px; z-index: 1000; animation: slideIn 0.3s ease;
        }
        .toast.error { background: #ef4444; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .modal-enter { animation: modalFadeIn 0.3s ease; }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body class="bg-white font-sans antialiased">
    <div id="toastContainer"></div>

    <!-- New Order Modal -->
    <div id="newOrderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto modal-enter">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Create New Order</h2>
                <button id="closeModalBtn" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-times text-2xl"></i></button>
            </div>
            <form id="newOrderForm">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Order Type</label>
                        <select name="order_type_id" class="w-full border rounded-lg p-2" required>
                            <?php foreach($order_types as $type): ?>
                            <option value="<?php echo $type['id']; ?>"><?php echo ucfirst($type['type_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Table</label>
                        <input type="text" name="table_number" class="w-full border rounded-lg p-2" placeholder="Table # or Takeaway">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Customer Name</label>
                    <input type="text" name="guest_name" class="w-full border rounded-lg p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Special Instructions</label>
                    <textarea name="special_instructions" class="w-full border rounded-lg p-2" rows="2"></textarea>
                </div>
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium">Order Items</label>
                        <button type="button" id="addItemBtn" class="text-amber-600 text-sm hover:text-amber-700">
                            <i class="fa-solid fa-plus mr-1"></i> Add Item
                        </button>
                    </div>
                    <div id="orderItemsContainer" class="space-y-2"></div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" id="cancelModalBtn" class="px-4 py-2 border rounded-lg hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700">Create Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md modal-enter">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Process Payment</h2>
                <button id="closePaymentModal" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-times text-2xl"></i></button>
            </div>
            <form id="paymentForm">
                <input type="hidden" id="paymentOrderId">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Order #</label>
                    <input type="text" id="paymentOrderNumber" class="w-full border rounded-lg p-2 bg-slate-50" readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Total Amount</label>
                    <input type="text" id="paymentTotal" class="w-full border rounded-lg p-2 bg-slate-50" readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Payment Method</label>
                    <select id="paymentMethod" class="w-full border rounded-lg p-2" required>
                        <option value="1">Cash</option>
                        <option value="2">Credit Card</option>
                        <option value="3">Debit Card</option>
                        <option value="4">GCash</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Amount Paid</label>
                    <input type="number" id="paymentAmount" class="w-full border rounded-lg p-2" required step="0.01">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" id="cancelPayment" class="px-4 py-2 border rounded-lg hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Process Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- APP CONTAINER -->
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Sidebar (same as menu_management.php) -->
        <aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm lg:min-h-screen shrink-0 overflow-y-auto">
            <!-- ... sidebar content same as menu_management.php ... -->
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Orders / POS</h1>
                    <p class="text-sm text-slate-500 mt-0.5">manage dine-in, takeaway, and delivery orders</p>
                </div>
                <div class="flex gap-3 text-sm">
                    <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
                        <i class="fa-regular fa-calendar text-slate-400"></i> <span id="currentDate"></span>
                    </span>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Active orders</p>
                    <p class="text-2xl font-semibold" id="activeOrders"><?php echo $stats['active_orders']; ?></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Dine-in</p>
                    <p class="text-2xl font-semibold" id="dineInCount"><?php echo $stats['dine_in'] ?? 0; ?></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Takeaway</p>
                    <p class="text-2xl font-semibold" id="takeawayCount"><?php echo $stats['takeaway'] ?? 0; ?></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Delivery</p>
                    <p class="text-2xl font-semibold" id="deliveryCount"><?php echo $stats['delivery'] ?? 0; ?></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Today's revenue</p>
                    <p class="text-2xl font-semibold" id="todayRevenue">₱<?php echo number_format($stats['today_revenue'] ?? 0, 2); ?></p>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex justify-between">
                <button id="newOrderBtn" class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700">
                    <i class="fa-solid fa-plus mr-1"></i> new order
                </button>
                <div class="flex gap-2">
                    <button id="refreshBtn" class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50">
                        <i class="fa-solid fa-rotate-right mr-1"></i> refresh
                    </button>
                </div>
            </div>

            <!-- Order Type Tabs -->
            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6">
                <button class="filter-btn px-4 py-2 bg-amber-600 text-white rounded-full text-sm" data-filter="all">all orders</button>
                <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm" data-filter="1">dine-in</button>
                <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm" data-filter="2">takeaway</button>
                <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm" data-filter="3">delivery</button>
                <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm" data-filter="completed">completed</button>
            </div>

            <!-- Orders Table -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
                <div class="p-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="font-semibold flex items-center gap-2"><i class="fa-solid fa-cash-register text-amber-600"></i> active orders</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-slate-500 text-xs border-b">
                            <tr>
                                <td class="p-3">Order #</td>
                                <td class="p-3">Time</td>
                                <td class="p-3">Type</td>
                                <td class="p-3">Table / Guest</td>
                                <td class="p-3">Items</td>
                                <td class="p-3">Total</td>
                                <td class="p-3">Status</td>
                                <td class="p-3">Actions</td>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody" class="divide-y">
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        const API_BASE = '../api/orders_api.php';
        let currentFilter = 'all';

        function showToast(message, type = 'success') {
            const toast = $(`<div class="toast ${type === 'error' ? 'error' : ''}"><i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>${message}</div>`);
            $('#toastContainer').append(toast);
            setTimeout(() => toast.fadeOut(300, function() { $(this).remove(); }), 3000);
        }

        function updateDate() {
            $('#currentDate').text(new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }));
        }

        function loadOrders() {
            let url = API_BASE + '?action=orders';
            if(currentFilter !== 'all' && currentFilter !== 'completed') {
                url += '&type=' + currentFilter;
            } else if(currentFilter === 'completed') {
                url += '&status=completed';
            }
            
            $.get(url, function(response) {
                if(response.success) {
                    renderOrders(response.data);
                }
            });
        }

        function renderOrders(orders) {
            let html = '';
            orders.forEach(order => {
                let typeClass = {
                    '1': 'bg-blue-100 text-blue-700',
                    '2': 'bg-green-100 text-green-700',
                    '3': 'bg-purple-100 text-purple-700'
                }[order.order_type_id] || 'bg-slate-100';
                
                let typeText = {
                    '1': 'dine-in',
                    '2': 'takeaway',
                    '3': 'delivery'
                }[order.order_type_id] || 'unknown';
                
                let statusClass = {
                    'pending': 'bg-amber-100 text-amber-700',
                    'confirmed': 'bg-blue-100 text-blue-700',
                    'preparing': 'bg-amber-100 text-amber-700',
                    'ready': 'bg-green-100 text-green-700',
                    'served': 'bg-slate-100 text-slate-700',
                    'completed': 'bg-slate-100 text-slate-700'
                }[order.order_status] || 'bg-slate-100';
                
                html += `
                    <tr>
                        <td class="p-3 font-medium">${order.order_number}</td>
                        <td class="p-3">${new Date(order.created_at).toLocaleTimeString()}</td>
                        <td class="p-3"><span class="${typeClass} px-2 py-0.5 rounded-full text-xs">${typeText}</span></td>
                        <td class="p-3">${order.table_number || order.guest_name}</td>
                        <td class="p-3">${order.item_count || 0} items</td>
                        <td class="p-3 font-medium">₱${parseFloat(order.total_amount || 0).toFixed(2)}</td>
                        <td class="p-3"><span class="${statusClass} px-2 py-0.5 rounded-full text-xs">${order.order_status}</span></td>
                        <td class="p-3">
                            <button class="view-order text-amber-700 text-xs hover:underline mr-2" data-id="${order.id}">view</button>
                            ${order.order_status !== 'completed' ? 
                                `<button class="payment-btn text-green-600 text-xs hover:underline" data-id="${order.id}" data-number="${order.order_number}" data-total="${order.total_amount}">payment</button>` : ''}
                        </td>
                    </tr>
                `;
            });
            
            $('#ordersTableBody').html(html || '<tr><td colspan="8" class="p-8 text-center text-slate-500">No orders found</td></tr>');
        }

        $(document).ready(function() {
            updateDate();
            loadOrders();

            $('#newOrderBtn').click(function() {
                $('#newOrderModal').removeClass('hidden').addClass('flex');
                $('#orderItemsContainer').empty();
                addItemRow();
            });

            function addItemRow() {
                const rowHtml = `
                    <div class="item-row flex gap-2 items-start">
                        <select class="item-name flex-1 border rounded-lg p-2" required>
                            <option value="">Select item</option>
                            <?php foreach($menu_items as $item): ?>
                            <option value="<?php echo $item['id']; ?>" data-price="<?php echo $item['price']; ?>">
                                <?php echo htmlspecialchars($item['item_name']); ?> - ₱<?php echo $item['price']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" class="item-qty w-20 border rounded-lg p-2" value="1" min="1" required>
                        <button type="button" class="remove-item text-red-500 px-2 hover:text-red-700"><i class="fa-solid fa-times"></i></button>
                    </div>
                `;
                $('#orderItemsContainer').append(rowHtml);
            }

            $('#addItemBtn').click(addItemRow);

            $(document).on('click', '.remove-item', function() {
                if($('.item-row').length > 1) {
                    $(this).closest('.item-row').remove();
                }
            });

            $('#newOrderForm').submit(function(e) {
                e.preventDefault();
                
                let items = [];
                let subtotal = 0;
                
                $('.item-row').each(function() {
                    const select = $(this).find('.item-name');
                    const qty = parseInt($(this).find('.item-qty').val());
                    const price = parseFloat(select.find('option:selected').data('price'));
                    
                    if(select.val() && qty > 0) {
                        items.push({
                            menu_item_id: select.val(),
                            quantity: qty,
                            unit_price: price,
                            subtotal: price * qty
                        });
                        subtotal += price * qty;
                    }
                });

                const orderData = {
                    order_type_id: $('select[name="order_type_id"]').val(),
                    guest_name: $('input[name="guest_name"]').val(),
                    special_instructions: $('textarea[name="special_instructions"]').val(),
                    subtotal: subtotal,
                    tax_amount: subtotal * 0.12,
                    total_amount: subtotal * 1.12,
                    items: items
                };

                $.ajax({
                    url: API_BASE + '?action=create',
                    method: 'POST',
                    data: JSON.stringify(orderData),
                    contentType: 'application/json',
                    success: function(response) {
                        if(response.success) {
                            showToast('Order created successfully');
                            $('#newOrderModal').addClass('hidden').removeClass('flex');
                            $('#newOrderForm')[0].reset();
                            $('#orderItemsContainer').empty();
                            loadOrders();
                        }
                    }
                });
            });

            $(document).on('click', '.payment-btn', function() {
                const id = $(this).data('id');
                const number = $(this).data('number');
                const total = $(this).data('total');
                
                $('#paymentOrderId').val(id);
                $('#paymentOrderNumber').val(number);
                $('#paymentTotal').val('₱' + parseFloat(total).toFixed(2));
                $('#paymentAmount').val(total);
                $('#paymentModal').removeClass('hidden').addClass('flex');
            });

            $('#paymentForm').submit(function(e) {
                e.preventDefault();
                
                const paymentData = {
                    order_id: $('#paymentOrderId').val(),
                    amount: $('#paymentAmount').val(),
                    payment_method_id: $('#paymentMethod').val(),
                    status: 'completed'
                };

                $.ajax({
                    url: API_BASE + '?action=process_payment',
                    method: 'POST',
                    data: JSON.stringify(paymentData),
                    contentType: 'application/json',
                    success: function(response) {
                        if(response.success) {
                            showToast('Payment processed');
                            $('#paymentModal').addClass('hidden').removeClass('flex');
                            loadOrders();
                        }
                    }
                });
            });

            $('.filter-btn').click(function() {
                $('.filter-btn').removeClass('bg-amber-600 text-white').addClass('bg-white border');
                $(this).removeClass('bg-white border').addClass('bg-amber-600 text-white');
                currentFilter = $(this).data('filter');
                loadOrders();
            });

            $('#refreshBtn').click(function() {
                loadOrders();
                showToast('Data refreshed');
            });

            // Close modal handlers
            $('#closeModalBtn, #cancelModalBtn').click(function() {
                $('#newOrderModal').addClass('hidden').removeClass('flex');
            });
            $('#closePaymentModal, #cancelPayment').click(function() {
                $('#paymentModal').addClass('hidden').removeClass('flex');
            });

            setInterval(loadOrders, 30000);
        });
    </script>
</body>
</html>