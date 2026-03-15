<?php
// admin/wait_staff_management.php
session_start();
require_once '../config/database.php';
require_once '../models/StaffModel.php';

$database = new Database();
$db = $database->getConnection();
$staff = new StaffModel($db);

// Get initial data
$stats = $staff->getStatistics();
$staff_members = $staff->getStaffMembers(['status' => 'active']);
$roles = $staff->getRoles();
$shifts = $staff->getShifts();
$today_schedule = $staff->getSchedule(date('Y-m-d'));
$assignments = $staff->getTableAssignments(date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Wait Staff Management</title>
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
        .staff-row:hover { background-color: #f8fafc; }
    </style>
</head>
<body class="bg-white font-sans antialiased">
    <div id="toastContainer"></div>

    <!-- Add Staff Modal -->
    <div id="addStaffModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-lg modal-enter">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Add New Staff</h2>
                <button id="closeModalBtn" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-times text-2xl"></i></button>
            </div>
            <form id="addStaffForm">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">First Name</label>
                        <input type="text" id="firstName" class="w-full border rounded-lg p-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Last Name</label>
                        <input type="text" id="lastName" class="w-full border rounded-lg p-2" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Role</label>
                    <select id="roleId" class="w-full border rounded-lg p-2" required>
                        <option value="">Select role</option>
                        <?php foreach($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" id="email" class="w-full border rounded-lg p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input type="text" id="phone" class="w-full border rounded-lg p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Hire Date</label>
                    <input type="date" id="hireDate" class="w-full border rounded-lg p-2" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" id="cancelModalBtn" class="px-4 py-2 border rounded-lg hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700">Add Staff</button>
                </div>
            </form>
        </div>
    </div>

    <!-- APP CONTAINER -->
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Sidebar -->
        <aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm lg:min-h-screen shrink-0 overflow-y-auto">
            <!-- ... sidebar content ... -->
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Wait Staff Management</h1>
                    <p class="text-sm text-slate-500 mt-0.5">manage schedules, assignments, and performance</p>
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
                    <p class="text-xs text-slate-500">Total staff</p>
                    <p class="text-2xl font-semibold"><?php echo $stats['total_staff']; ?></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">On duty</p>
                    <p class="text-2xl font-semibold text-green-600"><?php echo $stats['on_duty']; ?></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Break</p>
                    <p class="text-2xl font-semibold text-amber-600"><?php echo $stats['on_break']; ?></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Off duty</p>
                    <p class="text-2xl font-semibold text-slate-400"><?php echo $stats['off_duty']; ?></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Tables assigned</p>
                    <p class="text-2xl font-semibold"><?php echo $stats['assigned_tables']; ?>/<?php echo $stats['total_tables'] ?? 24; ?></p>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex justify-between">
                <div class="flex gap-2">
                    <button id="addStaffBtn" class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700">
                        <i class="fa-solid fa-plus mr-1"></i> add staff
                    </button>
                    <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50">
                        <i class="fa-regular fa-calendar mr-1"></i> create schedule
                    </button>
                </div>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="searchInput" placeholder="search staff..." class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
                </div>
            </div>

            <!-- Staff Table -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
                <div class="p-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="font-semibold flex items-center gap-2"><i class="fa-regular fa-user text-amber-600"></i> wait staff roster</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-slate-500 text-xs border-b">
                            <tr>
                                <td class="p-3">Name</td>
                                <td class="p-3">Position</td>
                                <td class="p-3">Shift</td>
                                <td class="p-3">Status</td>
                                <td class="p-3">Assigned tables</td>
                                <td class="p-3">Performance</td>
                                <td class="p-3">Actions</td>
                            </tr>
                        </thead>
                        <tbody id="staffTableBody" class="divide-y">
                            <?php foreach($staff_members as $member): ?>
                            <tr class="staff-row">
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-xs">
                                            <?php echo strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)); ?>
                                        </div>
                                        <span class="font-medium"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></span>
                                    </div>
                                </td>
                                <td class="p-3"><?php echo htmlspecialchars($member['role_name']); ?></td>
                                <td class="p-3">
                                    <?php 
                                    $schedule = array_filter($today_schedule, fn($s) => $s['staff_id'] == $member['id']);
                                    echo !empty($schedule) ? reset($schedule)['shift_name'] ?? 'Scheduled' : 'Off';
                                    ?>
                                </td>
                                <td class="p-3">
                                    <span class="bg-<?php echo $member['status'] == 'active' ? 'green' : 'slate'; ?>-100 text-<?php echo $member['status'] == 'active' ? 'green' : 'slate'; ?>-700 px-2 py-0.5 rounded-full text-xs">
                                        <?php echo $member['status']; ?>
                                    </span>
                                </td>
                                <td class="p-3">
                                    <?php 
                                    $staff_assignments = array_filter($assignments, fn($a) => $a['staff_id'] == $member['id']);
                                    echo count($staff_assignments) . ' tables';
                                    ?>
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center gap-1">
                                        <span class="text-yellow-400">★★★★★</span>
                                        <span class="text-xs">5.0</span>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <button class="edit-staff text-amber-700 text-xs hover:underline mr-2" data-id="<?php echo $member['id']; ?>">edit</button>
                                    <button class="schedule-staff text-blue-600 text-xs hover:underline">schedule</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Today's Schedule -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
                    <h2 class="font-semibold text-lg flex items-center gap-2 mb-3">
                        <i class="fa-regular fa-calendar text-amber-600"></i> today's shift schedule
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <?php
                        $shifts_by_time = [
                            'Morning' => array_filter($today_schedule, fn($s) => strpos($s['shift_name'] ?? '', 'Morning') !== false),
                            'Afternoon' => array_filter($today_schedule, fn($s) => strpos($s['shift_name'] ?? '', 'Afternoon') !== false),
                            'Evening' => array_filter($today_schedule, fn($s) => strpos($s['shift_name'] ?? '', 'Evening') !== false)
                        ];
                        foreach($shifts_by_time as $shift_name => $shift_staff): 
                        ?>
                        <div class="border rounded-xl p-3">
                            <p class="font-medium text-sm"><?php echo $shift_name; ?></p>
                            <p class="text-lg font-semibold mt-1"><?php echo count($shift_staff); ?> staff</p>
                            <p class="text-xs text-green-600">
                                <?php 
                                $names = array_slice(array_map(fn($s) => $s['first_name'], $shift_staff), 0, 3);
                                echo implode(', ', $names) . (count($shift_staff) > 3 ? ', etc.' : '');
                                ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Top Performers -->
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                    <h3 class="font-semibold flex items-center gap-2 mb-3">
                        <i class="fa-regular fa-star text-amber-600"></i> top performers
                    </h3>
                    <ul class="space-y-2">
                        <?php foreach(array_slice($staff_members, 0, 4) as $member): ?>
                        <li class="flex justify-between items-center">
                            <span><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></span>
                            <span class="text-yellow-400 text-sm">★★★★★</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </main>
    </div>

    <script>
        const API_BASE = '../api/staff_api.php';

        function showToast(message, type = 'success') {
            const toast = $(`<div class="toast ${type === 'error' ? 'error' : ''}"><i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>${message}</div>`);
            $('#toastContainer').append(toast);
            setTimeout(() => toast.fadeOut(300, function() { $(this).remove(); }), 3000);
        }

        function updateDate() {
            $('#currentDate').text(new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }));
        }

        $(document).ready(function() {
            updateDate();

            $('#addStaffBtn').click(function() {
                $('#addStaffModal').removeClass('hidden').addClass('flex');
            });

            $('#addStaffForm').submit(function(e) {
                e.preventDefault();
                
                const data = {
                    first_name: $('#firstName').val(),
                    last_name: $('#lastName').val(),
                    role_id: $('#roleId').val(),
                    email: $('#email').val(),
                    phone: $('#phone').val(),
                    hire_date: $('#hireDate').val(),
                    status: 'active',
                    employment_type: 'full_time'
                };

                $.ajax({
                    url: API_BASE + '?action=create_staff',
                    method: 'POST',
                    data: JSON.stringify(data),
                    contentType: 'application/json',
                    success: function(response) {
                        if(response.success) {
                            showToast('Staff member added');
                            $('#addStaffModal').addClass('hidden').removeClass('flex');
                            $('#addStaffForm')[0].reset();
                            setTimeout(() => location.reload(), 1000);
                        }
                    }
                });
            });

            $('#searchInput').on('input', function() {
                const search = $(this).val().toLowerCase();
                $('#staffTableBody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(search) > -1);
                });
            });

            $('#closeModalBtn, #cancelModalBtn').click(function() {
                $('#addStaffModal').addClass('hidden').removeClass('flex');
            });
        });
    </script>
</body>
</html>