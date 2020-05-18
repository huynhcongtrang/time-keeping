<?php

/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class LeaveStaffController extends My_Controller {

    public function _exportTotalLeave($data) {
        
        if (count($data) > 1000) {
            echo "<pre>";
            print_r('File vượt quá giới hạn cho phép !');
            echo "</pre>";
            exit();
        }
        // no limit time
        set_time_limit(0);
        ini_set('memory_limit', -1);
        error_reporting(~E_ALL);
        ini_set('display_error', 0);
        $heads = array(
            'CODE',
            'NAME',
            'EMAIL',
            'DEPARTMENT',
            'TEAM',
            'TITLE',
            'FROM DATE',
            'TO DATE',
            'COUNT DAY',
            'GROUP LEAVE',
            'LEAVE TYPE',
            'NOTE',
            'STATUS'
        );
        $filename = "List_leave_" . date('d-m-Y H-i-s') . ".xls"; //trang edit
        header('Content-Encoding: UTF-8');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Content-type: text/csv; charset=UTF-8');


        $csv = '';
        $csv .= implode("\t", array_values($heads)) . "\r\n";

        try {
            foreach ($data as $item) {
                $rowData = array();
                $rowData[] = "=\"" . $item['code'] . "\"";
                $rowData[] = $item['full_name'];
                $rowData[] = $item['email'];
                $rowData[] = $item['deparment'];
                $rowData[] = $item['team'];
                $rowData[] = $item['title'];
                $rowData[] = $item['from_date'];
                $rowData[] = $item['to_date'];
                $rowData[] = "=\"" .  $item['count_day']. "\"";
                $rowData[] = $item['group_type'];
                $rowData[] = $item['leave_type'];
                $rowData[] = $item['reason'];
                $rowData[] = ($item['status']==1)?'APPROVED':'REJECTED';
                        
                array_walk($rowData, __NAMESPACE__ . '\cleanData');
                $csv .= implode("\t", array_values($rowData)) . "\r\n";
            }
        } catch (Exception $exc) {
            echo 'Lỗi export';
            exit();
        }



        $csv = chr(255) . chr(254) . mb_convert_encoding($csv, "UTF-16LE", "UTF-8");
        echo $csv;
        exit;
    }

    public function indexAction() {
        $params = array();
        $QLeaveDetail = new \Application\Model\LeaveDetail();
        $QStaff = new \Application\Model\Staff();
        $QTeam = new \Application\Model\Team();
        $export_leave = $this->params()->fromPost('export-leave', 0);

        if ($user = $this->identity()) {
            
        } else {
            header('Location: ' . HOST . 'user/login');
            exit();
        }

        $info_user_team = $QStaff->getTeamStaff($user->staff_id);
        $list_department = $QTeam->getDepartment($user->staff_id);

        $year = date("Y");
        $month = date("m");
        $params['month'] = $month;
        $params['year'] = $year;
        $params['date'] = $month . '-' . $year;

        if ($this->getRequest()->isPost()) {
            $full_name = $this->params()->fromPost('full_name', null);
            $email = $this->params()->fromPost('email', null);
            $code = $this->params()->fromPost('code', null);
            $department = $this->params()->fromPost('department', 0);
            $team = $this->params()->fromPost('team', 0);
            $title = $this->params()->fromPost('title', 0);
            $date = $this->params()->fromPost('date', 0);
            $status_leave = $this->params()->fromPost('status-leave', 0);
            $params = [
                'full_name' => $full_name,
                'email' => $email,
                'code' => $code,
                'department' => $department,
                'team' => $team,
                'title' => $title,
                'date' => $date,
                'status' => $status_leave
            ];
        }
        $list_leave_detail = $QLeaveDetail->getListLeave($user->staff_id, $params);
        if ($export_leave == 1) {
            $this->_exportTotalLeave($list_leave_detail);
            exit();
        }

        $this->layout()->setVariable('title', 'List Staff Leave');
        return new ViewModel([
            'list_leave_detail' => $list_leave_detail,
            'info_user_team' => $info_user_team,
            'list_department' => $list_department,
            'param' => $params
        ]);
    }

    public function delLeaveAction() {
        $QLeaveDetail = new \Application\Model\LeaveDetail();
        if ($this->getRequest()->isPost()) {
            $id_leave = $this->params()->fromPost('id_leave');
            $QLeaveDetail->delLeaveDetail($id_leave);
            echo 1;
            exit();
        }
    }

    public function showLeaveAction() {// 1 la approved , 2 , reject
        $QLeaveDetail = new \Application\Model\LeaveDetail();
        if ($this->getRequest()->isPost()) {
            $id_leave = $this->params()->fromPost('id_leave');
            echo json_encode($QLeaveDetail->getDetailAppOrReLeave($id_leave));
            exit();
        }
    }

}
