<?php
// api/staff_api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/StaffModel.php';
require_once '../includes/helpers.php';

$database = new Database();
$db = $database->getConnection();
$staff = new StaffModel($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch($method) {
        case 'GET':
            if($action === 'stats') {
                $data = $staff->getStatistics();
                sendResponse(true, $data, 'Staff statistics retrieved');
            }
            elseif($action === 'roles') {
                $data = $staff->getRoles();
                sendResponse(true, $data, 'Staff roles retrieved');
            }
            elseif($action === 'staff') {
                $filters = [
                    'status' => $_GET['status'] ?? null,
                    'role' => $_GET['role'] ?? null,
                    'search' => $_GET['search'] ?? null
                ];
                $data = $staff->getStaffMembers(array_filter($filters));
                sendResponse(true, $data, 'Staff members retrieved');
            }
            elseif($action === 'staff_member' && isset($_GET['id'])) {
                $data = $staff->getStaffMember($_GET['id']);
                if($data) {
                    sendResponse(true, $data, 'Staff member retrieved');
                } else {
                    sendResponse(false, null, 'Staff member not found', 404);
                }
            }
            elseif($action === 'shifts') {
                $data = $staff->getShifts();
                sendResponse(true, $data, 'Shifts retrieved');
            }
            elseif($action === 'schedule') {
                $date = $_GET['date'] ?? date('Y-m-d');
                $data = $staff->getSchedule($date);
                sendResponse(true, $data, 'Schedule retrieved');
            }
            elseif($action === 'assignments') {
                $date = $_GET['date'] ?? date('Y-m-d');
                $data = $staff->getTableAssignments($date);
                sendResponse(true, $data, 'Table assignments retrieved');
            }
            elseif($action === 'performance') {
                $staff_id = $_GET['staff_id'] ?? null;
                $period = $_GET['period'] ?? 'week';
                $data = $staff->getStaffPerformance($staff_id, $period);
                sendResponse(true, $data, 'Performance ratings retrieved');
            }
            elseif($action === 'attendance') {
                $date = $_GET['date'] ?? date('Y-m-d');
                $data = $staff->getAttendance($date);
                sendResponse(true, $data, 'Attendance retrieved');
            }
            else {
                sendResponse(false, null, 'Invalid action', 400);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if($action === 'create_staff') {
                $missing = validateRequired($input, ['first_name', 'last_name', 'role_id']);
                if(!empty($missing)) {
                    sendResponse(false, null, 'Missing fields: ' . implode(', ', $missing), 400);
                }
                $result = $staff->createStaffMember($input);
                if($result) {
                    sendResponse(true, ['staff_id' => $result], 'Staff member created');
                } else {
                    sendResponse(false, null, 'Failed to create staff member', 500);
                }
            }
            elseif($action === 'create_schedule') {
                $missing = validateRequired($input, ['staff_id', 'schedule_date']);
                if(!empty($missing)) {
                    sendResponse(false, null, 'Missing fields: ' . implode(', ', $missing), 400);
                }
                $result = $staff->createSchedule($input);
                if($result) {
                    sendResponse(true, null, 'Schedule created');
                } else {
                    sendResponse(false, null, 'Failed to create schedule', 500);
                }
            }
            elseif($action === 'assign_table') {
                $missing = validateRequired($input, ['staff_id', 'table_id', 'assignment_date']);
                if(!empty($missing)) {
                    sendResponse(false, null, 'Missing fields: ' . implode(', ', $missing), 400);
                }
                $result = $staff->assignTable($input);
                if($result) {
                    sendResponse(true, null, 'Table assigned');
                } else {
                    sendResponse(false, null, 'Failed to assign table', 500);
                }
            }
            elseif($action === 'add_performance') {
                $missing = validateRequired($input, ['staff_id']);
                if(!empty($missing)) {
                    sendResponse(false, null, 'Missing fields: ' . implode(', ', $missing), 400);
                }
                $result = $staff->addPerformanceRating($input);
                if($result) {
                    sendResponse(true, null, 'Performance rating added');
                } else {
                    sendResponse(false, null, 'Failed to add rating', 500);
                }
            }
            elseif($action === 'record_attendance') {
                $missing = validateRequired($input, ['staff_id', 'status']);
                if(!empty($missing)) {
                    sendResponse(false, null, 'Missing fields: ' . implode(', ', $missing), 400);
                }
                $result = $staff->recordAttendance($input);
                if($result) {
                    sendResponse(true, null, 'Attendance recorded');
                } else {
                    sendResponse(false, null, 'Failed to record attendance', 500);
                }
            }
            else {
                sendResponse(false, null, 'Invalid action', 400);
            }
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if($action === 'update_staff' && isset($_GET['id'])) {
                $result = $staff->updateStaffMember($_GET['id'], $input);
                if($result) {
                    sendResponse(true, null, 'Staff member updated');
                } else {
                    sendResponse(false, null, 'Failed to update staff member', 500);
                }
            }
            elseif($action === 'update_schedule_status' && isset($_GET['id'])) {
                if(!isset($input['status'])) {
                    sendResponse(false, null, 'Status required', 400);
                }
                $result = $staff->updateScheduleStatus($_GET['id'], $input['status']);
                if($result) {
                    sendResponse(true, null, 'Schedule status updated');
                } else {
                    sendResponse(false, null, 'Failed to update schedule status', 500);
                }
            }
            else {
                sendResponse(false, null, 'Invalid action', 400);
            }
            break;

        case 'DELETE':
            if($action === 'staff' && isset($_GET['id'])) {
                $result = $staff->deleteStaffMember($_GET['id']);
                if($result) {
                    sendResponse(true, null, 'Staff member deleted');
                } else {
                    sendResponse(false, null, 'Failed to delete staff member', 500);
                }
            }
            elseif($action === 'assignment' && isset($_GET['id'])) {
                $result = $staff->removeTableAssignment($_GET['id']);
                if($result) {
                    sendResponse(true, null, 'Table assignment removed');
                } else {
                    sendResponse(false, null, 'Failed to remove assignment', 500);
                }
            }
            else {
                sendResponse(false, null, 'Invalid action', 400);
            }
            break;

        default:
            sendResponse(false, null, 'Method not allowed', 405);
    }
} catch(Exception $e) {
    sendResponse(false, null, 'Server error: ' . $e->getMessage(), 500);
}
?>