<?php

/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Db\Sql\Sql; //note
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class MyLeaveController extends My_Controller {

    public function solvedFromToMDM($list_leave_mdm) {
        $QLeaveDetail = new \Application\Model\LeaveDetail();
        $return_data = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        foreach ($list_leave_mdm as $item) {
            //$lastday = date('t',strtotime('today'));
            $stm1 = explode('-', $item['from_date']);
            $stm2 = explode('-', $item['to_date']);
            $temp = $stm1[1];
            while ($temp <= $stm2[1]) {
                if ($temp == $stm1[1]) {
                    $lastday = date('t', strtotime('today'));
                    $from_temp = $stm1[0] . '-' . $temp . '-' . $lastday;
                    $data_spe = $QLeaveDetail->getArrayDaysSpe($item['from_date'], $from_temp);
                    $total_date = date_diff(date_create($from_temp), date_create($item['from_date']))->format("%a") - $data_spe['count_date'] + 1;
                    $return_data[(int) $temp] += $total_date;
                    $temp++;
                } else if ($temp == $stm2[1]) {
                    $from_temp = $stm2[0] . '-' . $temp . '-' . '01';
                    $data_spe = $QLeaveDetail->getArrayDaysSpe($from_temp, $item['to_date']);
                    $total_date = date_diff(date_create($item['to_date']), date_create($from_temp))->format("%a") - $data_spe['count_date'] + 1;
                    $return_data[(int) $temp] += $total_date;
                    $temp++;
                } else {
                    $lastday = date('t', strtotime('today'));
                    $from_temp = $stm1[0] . '-' . $temp . '-' . '01';
                    $to_temp = $stm1[0] . '-' . $temp . '-' . $lastday;
                    $data_spe = $QLeaveDetail->getArrayDaysSpe($from_temp, $to_temp);
                    $total_date = date_diff(date_create($to_temp), date_create($from_temp))->format("%a") - $data_spe['count_date'] + 1;
                    $return_data[(int) $temp] += $total_date;
                    $temp++;
                }
            }
        }
        return $return_data;
    }

    public function indexAction() {
        $QLeaveTime = new \Application\Model\LeaveTime();
        $QContract = new \Application\Model\Contract();
        $QLeaveDetail = new \Application\Model\LeaveDetail();
        $param = array();
        if ($user = $this->identity()) {
            
        } else {
            header('Location: ' . HOST . 'user/login');
            exit();
        }

        $leave_time = $QLeaveTime->getLeaveTime();
        $staff_contract = $QContract->getContractPrevent($user->staff_id);
        $staff_contract_last = $QContract->getContractLast($user->staff_id);
        $param['date'] = date('m-Y');
        if ($this->getRequest()->isPost()) {
            $date = $this->params()->fromPost('date');
            $stm = explode("-", $date);
            $list_my_leave = $QLeaveDetail->getMyLeave($user->staff_id, $stm[0], $stm[1]);
            $param['date'] = $stm[0] . "-" . $stm[1];
        } else
            $list_my_leave = $QLeaveDetail->getMyLeave($user->staff_id, date("m"), date("Y"));

        //get totle leave
        $list_leave_mm = $QLeaveDetail->getLeaveByIdMM($user->staff_id);
        $list_leave_mdm = $QLeaveDetail->getLeaveByIdMDM($user->staff_id);
        $list_leave_mdm = $this->solvedFromToMDM($list_leave_mdm);

        $this->layout()->setVariable('title', 'My Leave');
        return new ViewModel([
            'leave_time' => $leave_time,
            'staff_contract' => $staff_contract,
            'staff_contract_last' => $staff_contract_last,
            'list_my_leave' => $list_my_leave,
            'list_leave_mm' => $list_leave_mm,
            'list_leave_mdm' => $list_leave_mdm,
            'param' => $param
        ]);
    }

    public function testAction() {
        $QLeaveDetail = new \Application\Model\LeaveDetail();
        $check = $QLeaveDetail->checkDateOffHaft(1, '2020-01-30', 2);
        echo "<pre>";
        print_r($check);
        echo "</pre>";
        exit();
    }

    public function checkFromToAction() {
        $QLeaveDetail = new \Application\Model\LeaveDetail();
        $QContract = new \Application\Model\Contract();
        $QStaff = new \Application\Model\Staff();
        if ($this->getRequest()->isPost()) {
            $from_date = $this->params()->fromPost('from_date', null);
            $to_date = $this->params()->fromPost('to_date', null);
            $type = $this->params()->fromPost('type');
            $note = $this->params()->fromPost('note');
            if ($user = $this->identity()) {
                
            }
            if ($type == 2) { // phep nam
                $contract = $QContract->getContractPrevent($user->staff_id);
                if (empty($contract)) {
                    echo json_encode(['status' => 10]);
                    exit();
                }
            }
            $check = $QLeaveDetail->checkExceedTime($user->staff_id, $from_date, $to_date, $type);
            if ($check['status'] == 4) {
                $db = new \Database\Controller\AdapterController();
                $adapter = $db->DbAdapter();
                $sql = new Sql($adapter);
                $info_staff = $QStaff->getTeamStaff($user->staff_id);
                if ($info_staff['department_id'] == DEPARTMENT_ADMIN || $info_staff['department_id'] == DEPARTMENT_HR) {
                    $data_insert = array(
                        'staff_id' => $user->staff_id,
                        'from_date' => $from_date,
                        'to_date' => $to_date,
                        'type' => 1,
                        'type_leave' => $type,
                        'reason' => $note,
                        'status' => 1,
                        'create_at' => date("Y-m-d H:i:s"),
                        'count_day' => $check['total_date_leave']
                    );
                } else if ($info_staff['is_leader'] == 1) {
                    $data_insert = array(
                        'staff_id' => $user->staff_id,
                        'from_date' => $from_date,
                        'to_date' => $to_date,
                        'type' => 1,
                        'approve_by_leader' => $user->staff_id,
                        'approve_at_leader' => date("Y-m-d H:i:s"),
                        'type_leave' => $type,
                        'reason' => $note,
                        'status' => 2,
                        'create_at' => date("Y-m-d H:i:s"),
                        'count_day' => $check['total_date_leave']
                    );
                } else {
                    $data_insert = array(
                        'staff_id' => $user->staff_id,
                        'from_date' => $from_date,
                        'to_date' => $to_date,
                        'type' => 1,
                        'type_leave' => $type,
                        'reason' => $note,
                        'status' => 2,
                        'create_at' => date("Y-m-d H:i:s"),
                        'count_day' => $check['total_date_leave']
                    );
                }
                $insert = $sql->insert('leave_detail');
                $insert->values($data_insert);
                $statement = $sql->prepareStatementForSqlObject($insert);
                $result = $statement->execute();
            }
            echo json_encode($check);
        }
        exit();
    }

    public function checkDateOffAction() {
        $QLeaveDetail = new \Application\Model\LeaveDetail();
        $QContract = new \Application\Model\Contract();
        if ($this->getRequest()->isPost()) {
            $date_off = $this->params()->fromPost('date_off', null);
            $type = $this->params()->fromPost('type');
            $note = $this->params()->fromPost('note');
            if ($user = $this->identity()) {
                
            }
            if ($type == 2) { // phep nam
                $contract = $QContract->getContractPrevent($user->staff_id);
                if (empty($contract)) {
                    echo 10;
                    exit();
                }
            }
            $check = $QLeaveDetail->checkDateOffHaft($user->staff_id, $date_off, $type);
            if ($check == 5) {
                $db = new \Database\Controller\AdapterController();
                $adapter = $db->DbAdapter();
                $sql = new Sql($adapter);
                if ($info_staff['department_id'] == DEPARTMENT_ADMIN || $info_staff['department_id'] == DEPARTMENT_HR) {
                    $data_insert = array(
                        'staff_id' => $user->staff_id,
                        'from_date' => $date_off,
                        'to_date' => $date_off,
                        'type' => 2,
                        'type_leave' => $type,
                        'reason' => $note,
                        'status' => 1,
                        'create_at' => date("Y-m-d H:i:s"),
                        'count_day' => 0.5
                    );
                } else if ($info_staff['is_leader'] == 1) {
                    $data_insert = array(
                        'staff_id' => $user->staff_id,
                        'from_date' => $date_off,
                        'to_date' => $date_off,
                        'type' => 2,
                        'type_leave' => $type,
                        'reason' => $note,
                        'status' => 2,
                        'approve_by_leader' => $user->staff_id,
                        'approve_at_leader' => date("Y-m-d H:i:s"),
                        'create_at' => date("Y-m-d H:i:s"),
                        'count_day' => 0.5
                    );
                } else {
                    $data_insert = array(
                        'staff_id' => $user->staff_id,
                        'from_date' => $date_off,
                        'to_date' => $date_off,
                        'type' => 2,
                        'type_leave' => $type,
                        'reason' => $note,
                        'status' => 2,
                        'create_at' => date("Y-m-d H:i:s"),
                        'count_day' => 0.5
                    );
                }

                $insert = $sql->insert('leave_detail');
                $insert->values($data_insert);
                $statement = $sql->prepareStatementForSqlObject($insert);
                $result = $statement->execute();
            }
            echo $check;
        }
        exit();
    }

    public function createLeaveAction() {
        $success = '';
        $err = '';
        $QLeaveType = new \Application\Model\LeaveType();
        $QContract = new \Application\Model\Contract();
        if ($user = $this->identity()) {
            
        }
        //get group leave
        $list_group_leave = $QLeaveType->getGroupLeave();

        $this->layout()->setVariable('title', 'Create Leave');
        return new ViewModel([
            'list_group_leave' => $list_group_leave,
            'error' => $err,
            'success' => $success
        ]);
    }

    public function checkFromToLeaveAction($from, $to, $leave_type) {//1 : trung  2 : max day , 3 day setting // 4 success
        // kiem tra xe co max day khong
        // kiem tra trung
        // kiem tra date setting holiady
        $QLeaveDetail = new \Application\Model\LeaveDetail();
        // kiem tra trung
        $result = $QLeaveDetail->checkDuplicateLeave(1, '2020-04-20', '2020-05-28');
        if ($result) {
            echo 1;
            exit();
        } else {
            
        }

        echo 4;
        exit();
    }

    public function loadTypeLeaveAction() {
        $QLeaveType = new \Application\Model\LeaveType();
        if ($this->getRequest()->isPost()) {
            $group_type = $this->params()->fromPost('group_type');
            //get type leave
            $list_type_leave = $QLeaveType->getTypeLeave($group_type);
            echo json_encode($list_type_leave);
            exit();
        }
    }

    public function loadTypeLeaveIdAction() {
        $QLeaveType = new \Application\Model\LeaveType();
        if ($this->getRequest()->isPost()) {
            $leave_type = $this->params()->fromPost('leave_type');
            //get type leave
            $list_type_leave = $QLeaveType->getTypeLeaveById($leave_type);
            echo json_encode($list_type_leave);
            exit();
        }
    }

    public function rejectDetailAction() {
        $QReasonReject = new \Application\Model\ReasonReject();
        if ($this->getRequest()->isPost()) {
            $id_leave = $this->params()->fromPost('id_leave');
            if ($user = $this->identity()) {
                
            } else exit();
            $reason_reject = $QReasonReject->getDetailReject($id_leave);
            echo json_encode($reason_reject);
            exit();
        }
    }

}
