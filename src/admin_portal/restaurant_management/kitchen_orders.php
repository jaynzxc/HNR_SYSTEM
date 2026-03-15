<?php
// admin/kitchen_orders.php
session_start();
require_once '../config/database.php';
require_once '../models/KitchenModel.php';

$database = new Database();
$db = $database->getConnection();
$kitchen = new KitchenModel($db);

// Get initial data
$stats = $kitchen->getStatistics();
$tickets = $kitchen->getTickets(['status' => 'pending,preparing']);
$stations = $kitchen->getStations();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Orders (KOT) · Restaurant Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .transition-side { transition: all 0.2s ease; }
        .dropdown-arrow { transition: transform 0.2s; }
        details[open] .dropdown-arrow { transform: rotate(90deg); }
        details > summary { list-style: none; }
        details summary::-webkit-details-marker { display: none; }
        .btn-clicked { transform: scale(0.95); transition: transform 0.1s; }
        .toast {
            position: fixed; bottom: 20px; right: 20px;
            background: #10b981; color: white; padding: 12px 24px;
            border-radius: 8px; z-index: 1000; animation: slideIn 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
        .order-card { transition: all 0.2s ease; }
        .order-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #a0aec0; }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div id="toastContainer"></div>

    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Sidebar -->
        <aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm lg:min-h-screen shrink-0 overflow-y-auto">
            <div class="px-5 py-6 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-utensils text-amber-600 text-xl"></i>
                <i class="fa-solid fa-bed text-amber-600 text-xl"></i>
                <span class="font-semibold text-lg tracking-tight text-slate-800">Lùcas<span class="text-amber-600">.admin</span></span>
            </div>
            <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-slate-50/60">
                <div class="h-9 w-9 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold">A</div>
                <div>
                    <p class="font-medium text-sm">Andreo Reyes</p>
                    <p class="text-xs text-slate-500">general manager</p>
                </div>
            </div>
            <nav class="p-4 space-y-2 text-sm">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition">
                    <i class="fa-solid fa-table-cells-large w-5 text-slate-400"></i> <span>Dashboard</span>
                </a>
                <details class="group" open>
                    <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
                        <i class="fa-solid fa-hotel w-5 text-slate-400 group-open:text-amber-600"></i>
                        <span class="font-medium">HOTEL MANAGEMENT</span>
                        <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
                    </summary>
                    <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
                        <a href="#" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-reception w-4 text-slate-400"></i> Front Desk / Reception</a>
                        <a href="#" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-bed w-4 text-slate-400"></i> Room Management</a>
                        <a href="#" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar-check w-4 text-slate-400"></i> Reservations & Booking</a>
                        <a href="#" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-broom w-4 text-slate-400"></i> Housekeeping & Maintenance</a>
                        <a href="#" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar w-4 text-slate-400"></i> Events & Conference</a>
                    </div>
                </details>
                <details class="group" open>
                    <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-amber-800 bg-amber-50 cursor-pointer transition-side">
                        <i class="fa-solid fa-utensils w-5 text-amber-600"></i>
                        <span class="font-medium">RESTAURANT MANAGEMENT</span>
                        <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-amber-600"></i>
                    </summary>
                    <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-200">
                        <a href="table_reservation.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-clock w-4 text-slate-400"></i> Table Reservation</a>
                        <a href="menu_management.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-bars w-4 text-slate-400"></i> Menu Management</a>
                        <a href="orders_pos.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-cash-register w-4 text-slate-400"></i> Orders / POS</a>
                        <a href="kitchen_orders.php" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-100/50 text-amber-700 font-medium"><i class="fa-solid fa-fire w-4 text-amber-600"></i> Kitchen Orders (KOT)</a>
                        <a href="wait_staff_management.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-user w-4 text-slate-400"></i> Wait Staff Management</a>
                    </div>
                </details>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-gray-50">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Kitchen Orders (KOT)</h1>
                    <p class="text-sm text-slate-500 mt-0.5">real-time kitchen display system · manage food preparation</p>
                </div>
                <div class="flex gap-3 text-sm">
                    <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
                        <i class="fa-regular fa-calendar text-slate-400"></i> 
                        <span id="currentDate"></span>
                    </span>
                    <button id="refreshBtn" class="bg-white border rounded-full px-4 py-2 shadow-sm hover:bg-slate-50 transition">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">New orders</p>
                    <p class="text-2xl font-semibold text-blue-600" id="newOrdersCount"><?php echo $stats['new_orders'] ?? 0; ?></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Preparing</p>
                    <p class="text-2xl font-semibold text-amber-600" id="preparingOrdersCount"><?php echo $stats['preparing_orders'] ?? 0; ?></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Ready to serve</p>
                    <p class="text-2xl font-semibold text-green-600" id="readyOrdersCount"><?php echo $stats['ready_orders'] ?? 0; ?></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Completed (today)</p>
                    <p class="text-2xl font-semibold" id="completedOrdersCount"><?php echo $stats['completed_today'] ?? 0; ?></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Avg prep time</p>
                    <p class="text-2xl font-semibold" id="avgPrepTime"><?php echo ($stats['avg_prep_time'] ?? 0) . ' min'; ?></p>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6">
                <button class="filter-btn px-4 py-2 bg-amber-600 text-white rounded-full text-sm" data-filter="all">all orders</button>
                <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50" data-filter="pending">new</button>
                <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50" data-filter="preparing">preparing</button>
                <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50" data-filter="ready">ready</button>
                <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50" data-filter="urgent">urgent</button>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3 mb-6">
                <button id="newOrderBtn" class="bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700 transition flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-plus"></i> New Order
                </button>
                <button id="addNoteBtn" class="bg-white border border-amber-600 text-amber-700 px-4 py-2 rounded-xl hover:bg-amber-50 transition flex items-center gap-2">
                    <i class="fa-regular fa-pen-to-square"></i> Add Special Instruction
                </button>
            </div>

            <!-- Kitchen Orders Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8" id="ordersContainer">
                <!-- Orders will be loaded via AJAX -->
            </div>

            <!-- Bottom Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
                    <h2 class="font-semibold text-lg flex items-center gap-2 mb-3">
                        <i class="fa-regular fa-rectangle-list text-amber-600"></i> Preparation Queue
                    </h2>
                    <div class="space-y-3" id="queueContainer"></div>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                    <h3 class="font-semibold flex items-center gap-2 mb-3">
                        <i class="fa-regular fa-note-sticky text-amber-600"></i> Special Instructions
                    </h3>
                    <ul class="space-y-2 text-sm" id="instructionsList"></ul>
                </div>
            </div>
        </main>
    </div>

    <!-- New Order Modal -->
    <div id="newOrderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto modal-enter">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Create New Order</h2>
                <button id="closeModalBtn" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>
            <form id="newOrderForm">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Order Type</label>
                        <select name="order_type" class="w-full border rounded-lg p-2" required>
                            <option value="dine_in">Dine-in</option>
                            <option value="takeaway">Takeaway</option>
                            <option value="delivery">Delivery</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Table / Takeaway #</label>
                        <select name="table_id" class="w-full border rounded-lg p-2" id="tableSelect"></select>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Customer Name</label>
                    <input type="text" name="customer_name" class="w-full border rounded-lg p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Priority</label>
                    <select name="priority" class="w-full border rounded-lg p-2">
                        <option value="normal">Normal</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Special Instructions</label>
                    <textarea name="special_instructions" class="w-full border rounded-lg p-2" rows="2"></textarea>
                </div>
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium">Order Items</label>
                        <button type="button" id="addItemBtn" class="text-amber-600 text-sm hover:text-amber-700">
                            <i class="fa-solid fa-plus"></i> Add Item
                        </button>
                    </div>
                    <div id="orderItemsContainer" class="space-y-2"></div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" id="cancelModalBtn" class="px-4 py-2 border rounded-lg hover:bg-slate-50 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition">Create Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-lg modal-enter">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold" id="modalOrderTitle">Order Details</h2>
                <button id="closeDetailsModalBtn" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>
            <div id="orderDetailsContent" class="space-y-4"></div>
        </div>
    </div>

    <!-- Add Note Modal -->
    <div id="addNoteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md modal-enter">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Add Special Instruction</h2>
                <button id="closeNoteModalBtn" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>
            <form id="addNoteForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Order Number</label>
                    <select id="noteOrderSelect" class="w-full border rounded-lg p-2" required></select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Instruction</label>
                    <textarea id="noteText" class="w-full border rounded-lg p-2" rows="3" required></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" id="cancelNoteBtn" class="px-4 py-2 border rounded-lg hover:bg-slate-50 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition">Add Note</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const API_BASE = '../api/kitchen_api.php';
        
        function showToast(message, type = 'success') {
            const toast = $(`<div class="toast ${type === 'error' ? 'error' : ''}"><i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>${message}</div>`);
            $('#toastContainer').append(toast);
            setTimeout(() => toast.fadeOut(300, function() { $(this).remove(); }), 3000);
        }

        function updateDate() {
            $('#currentDate').text(new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }));
        }

        function loadTickets() {
            $.get(`${API_BASE}?action=tickets`, function(response) {
                if(response.success) {
                    renderTickets(response.data);
                }
            });
        }

        function renderTickets(tickets) {
            let html = '';
            tickets.forEach(ticket => {
                let borderColor = 'border-slate-200';
                let statusBadge = '';
                
                if(ticket.priority === 'urgent' && (ticket.status === 'pending' || ticket.status === 'preparing')) {
                    borderColor = 'border-red-500';
                    statusBadge = '<span class="status-badge bg-red-100 text-red-600">URGENT</span>';
                } else if(ticket.status === 'pending') {
                    borderColor = 'border-blue-500';
                    statusBadge = '<span class="status-badge bg-blue-100 text-blue-600">NEW</span>';
                } else if(ticket.status === 'preparing') {
                    borderColor = 'border-amber-500';
                    statusBadge = '<span class="status-badge bg-amber-100 text-amber-600">PREPARING</span>';
                } else if(ticket.status === 'ready') {
                    borderColor = 'border-green-500';
                    statusBadge = '<span class="status-badge bg-green-100 text-green-600">READY</span>';
                }
                
                html += `
                    <div class="bg-white rounded-2xl border-l-4 ${borderColor} border shadow-sm order-card" data-id="${ticket.id}">
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <div>${statusBadge}<h3 class="font-semibold text-lg mt-1">${ticket.ticket_number}</h3></div>
                                <span class="text-sm text-slate-500">${new Date(ticket.created_at).toLocaleTimeString()}</span>
                            </div>
                            <div class="mt-2 text-sm">
                                <p class="font-medium">${ticket.table_number || 'Takeaway'} · ${ticket.guest_name || 'Guest'}</p>
                                <p class="text-xs text-slate-400 mt-1">${ticket.item_count || 0} items</p>
                            </div>
                            <div class="flex gap-2 mt-4">
                                ${getActionButtons(ticket)}
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#ordersContainer').html(html || '<div class="col-span-3 text-center py-8 text-slate-500">No orders found</div>');
        }

        function getActionButtons(ticket) {
            if(ticket.status === 'pending') {
                return `
                    <button class="start-prep-btn flex-1 bg-amber-600 text-white py-2 rounded-xl text-sm hover:bg-amber-700 transition" data-id="${ticket.id}">Start Preparing</button>
                    <button class="details-btn border border-slate-200 px-3 rounded-xl text-slate-600 hover:bg-slate-50 transition" data-id="${ticket.id}"><i class="fa-regular fa-clock"></i></button>
                `;
            } else if(ticket.status === 'preparing') {
                return `
                    <button class="mark-ready-btn flex-1 bg-green-600 text-white py-2 rounded-xl text-sm hover:bg-green-700 transition" data-id="${ticket.id}">Mark Ready</button>
                    <button class="details-btn border border-slate-200 px-3 rounded-xl text-slate-600 hover:bg-slate-50 transition" data-id="${ticket.id}"><i class="fa-regular fa-clock"></i></button>
                `;
            } else if(ticket.status === 'ready') {
                return `
                    <button class="serve-btn flex-1 bg-blue-600 text-white py-2 rounded-xl text-sm hover:bg-blue-700 transition" data-id="${ticket.id}">Serve</button>
                    <button class="details-btn border border-slate-200 px-3 rounded-xl text-slate-600 hover:bg-slate-50 transition" data-id="${ticket.id}"><i class="fa-regular fa-receipt"></i></button>
                `;
            }
            return '';
        }

        $(document).ready(function() {
            updateDate();
            loadTickets();

            $('.filter-btn').click(function() {
                $('.filter-btn').removeClass('bg-amber-600 text-white').addClass('bg-white border');
                $(this).removeClass('bg-white border').addClass('bg-amber-600 text-white');
                loadTickets();
            });

            $('#refreshBtn').click(function() {
                $(this).find('i').addClass('fa-spin');
                setTimeout(() => {
                    loadTickets();
                    $(this).find('i').removeClass('fa-spin');
                    showToast('Data refreshed');
                }, 500);
            });

            $(document).on('click', '.start-prep-btn', function() {
                const id = $(this).data('id');
                $.ajax({
                    url: API_BASE + '?action=update_status',
                    method: 'POST',
                    data: JSON.stringify({ ticket_id: id, status: 'preparing' }),
                    contentType: 'application/json',
                    success: function(response) {
                        if(response.success) {
                            showToast('Order status updated');
                            loadTickets();
                        }
                    }
                });
            });

            $(document).on('click', '.mark-ready-btn', function() {
                const id = $(this).data('id');
                $.ajax({
                    url: API_BASE + '?action=update_status',
                    method: 'POST',
                    data: JSON.stringify({ ticket_id: id, status: 'ready' }),
                    contentType: 'application/json',
                    success: function(response) {
                        if(response.success) {
                            showToast('Order marked as ready');
                            loadTickets();
                        }
                    }
                });
            });

            $('#newOrderBtn').click(function() {
                $('#newOrderModal').removeClass('hidden').addClass('flex');
                $('#orderItemsContainer').empty();
                addItemRow();
            });

            $('#addItemBtn').click(addItemRow);

            function addItemRow() {
                const rowHtml = `
                    <div class="item-row flex gap-2 items-start">
                        <select class="item-name flex-1 border rounded-lg p-2" required>
                            <option value="">Select item</option>
                        </select>
                        <input type="number" class="item-qty w-20 border rounded-lg p-2" value="1" min="1" required>
                        <button type="button" class="remove-item text-red-500 px-2 hover:text-red-700"><i class="fa-solid fa-times"></i></button>
                    </div>
                `;
                $('#orderItemsContainer').append(rowHtml);
                loadMenuItems();
            }

            function loadMenuItems() {
                $.get('../api/menu_api.php?action=items', function(response) {
                    if(response.success) {
                        let options = '<option value="">Select item</option>';
                        response.data.forEach(item => {
                            options += `<option value="${item.id}" data-price="${item.price}">${item.item_name} - ₱${item.price}</option>`;
                        });
                        $('.item-name').html(options);
                    }
                });
            }

            $('#newOrderForm').submit(function(e) {
                e.preventDefault();
                let items = [];
                $('.item-row').each(function() {
                    const select = $(this).find('.item-name');
                    if(select.val()) {
                        items.push({
                            menu_item_id: select.val(),
                            quantity: $(this).find('.item-qty').val(),
                            unit_price: select.find('option:selected').data('price')
                        });
                    }
                });

                const orderData = {
                    order_type_id: $('select[name="order_type"]').val() === 'dine_in' ? 1 : 2,
                    guest_name: $('input[name="customer_name"]').val(),
                    special_instructions: $('textarea[name="special_instructions"]').val(),
                    items: items
                };

                $.ajax({
                    url: '../api/orders_api.php?action=create',
                    method: 'POST',
                    data: JSON.stringify(orderData),
                    contentType: 'application/json',
                    success: function(response) {
                        if(response.success) {
                            showToast('Order created successfully');
                            $('#newOrderModal').addClass('hidden').removeClass('flex');
                            loadTickets();
                        }
                    }
                });
            });

            $('#closeModalBtn, #cancelModalBtn').click(function() {
                $('#newOrderModal').addClass('hidden').removeClass('flex');
            });

            $('#closeDetailsModalBtn').click(function() {
                $('#orderDetailsModal').addClass('hidden').removeClass('flex');
            });

            $('#closeNoteModalBtn, #cancelNoteBtn').click(function() {
                $('#addNoteModal').addClass('hidden').removeClass('flex');
            });

            $(window).click(function(e) {
                if($(e.target).hasClass('fixed')) {
                    $('.fixed').addClass('hidden').removeClass('flex');
                }
            });

            setInterval(loadTickets, 30000);
        });
    </script>
</body>
</html>