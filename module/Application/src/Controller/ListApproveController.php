<?php

/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Plugin\Identity;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;
use Zend\Authentication\Storage\Session;
use Zend\Session\Storage\SessionStorage;
use Zend\View\Helper\Placeholder\Registry;
use Zend\Db\Sql\Sql; //note
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\Apc;
use Zend\Cache\Storage\Plugin\ExceptionHandler;

class ListApproveController extends My_Controller {

    public function indexAction() {
        $QLeaveDetail = new \Application\Model\LeaveDetail();
        $QStaff = new \Application\Model\Staff();
        $QReasonReject = new \Application\Model\ReasonReject();
        $QTeam = new \Application\Model\Team();
        
        if ($user = $this->identity()) {
            
        } else {
            header('Location: ' . HOST . 'user/login');
            exit();
        }
        $param = array();
        if ($this->getRequest()->isPost()) {
            $full_name = $this->params()->fromPost('full_name', null);
            $email = $this->params()->fromPost('email', null);
            $code = $this->params()->fromPost('code', null);
            $department = $this->params()->fromPost('department', 0);
            $team = $this->params()->fromPost('team', 0);
            $title = $this->params()->fromPost('title', 0);
            $date = $this->params()->fromPost('date', 0);
            $param = [
                'full_name' => $full_name , 
                'email' => $email,
                'code' => $code,
                'department' => $department,
                'team' => $team,
                'title' => $title,
                'date' => $date
            ];
           
        }
        $list_staff_leave = $QLeaveDetail->getListLeaveStaff($user->staff_id , $param);
        $info_user_team = $QStaff->getTeamStaff($user->staff_id);
        $list_department = $QTeam->getDepartment($user->staff_id);
        $list_all_team_title = $QTeam->getTeamOrDepartment2();
        $list_reason_reject = $QReasonReject->getReasonReject(1);
        $this->layout()->setVariable('title', 'Approve Time/Leave');
        return new ViewModel([
            'list_staff_leave' => $list_staff_leave,
            'info_user_team' => $info_user_team,
            'list_reason_reject' => $list_reason_reject,
            'param' => $param,
            'list_department' => $list_department,
            'list_all_team_title' => $list_all_team_title
        ]);
    }

    public function approveByLeaderAction() {
        $QLeaveDetail = new \Application\Model\LeaveDetail();
        if ($this->getRequest()->isPost()) {
            $id_leave = $this->params()->fromPost('id_leave', null);
            $check_approved = $QLeaveDetail->checkArpoved(1, $id_leave);
            if ($check_approved == 1) {
                echo 1;
                exit();
            } else {
                if ($user = $this->identity()) {
                    
                } else {
                    echo 1;
                    exit();
                }
                $db = new \Database\Controller\AdapterController();
                $adapter = $db->DbAdapter();
                $sql = new Sql($adapter);

                $data_insert = array(
                    'approve_by_leader' => $user->staff_id,
                    'approve_at_leader' => date("Y-m-d H:i:s")
                );
                $where = array('id' => $id_leave);
                $update = $sql->update('leave_detail');
                $update->set($data_insert);
                $update->where($where);
                $statement = $sql->prepareStatementForSqlObject($update);
                $result = $statement->execute();
                echo 2;
                exit();
            }
        }
        exit();
    }

    public function approveByHrAction() {//1:da duoc appro trước đó  , 2: chua duoc leader approve nen hr k approve
        $QLeaveDetail = new \Application\Model\LeaveDetail();
        if ($this->getRequest()->isPost()) {
            $id_leave = $this->params()->fromPost('id_leave', null);
            $check_approved = $QLeaveDetail->checkArpoved(2, $id_leave);
            if ($check_approved == 1) {
                echo 1;
                exit();
            }
            $check_approved_leader = $QLeaveDetail->checkArpoved(1, $id_leave);
            if ($check_approved_leader != 1) {
                echo 2;
                exit();
            }

            if ($user = $this->identity()) {
                
            } else {
                echo 1;
                exit();
            }

            $db = new \Database\Controller\AdapterController();
            $adapter = $db->DbAdapter();
            $sql = new Sql($adapter);
            $data_insert = array(
                'approve_by_hr' => $user->staff_id,
                'approve_at_hr' => date("Y-m-d H:i:s"),
                'status' => 1
            );
            $where = array('id' => $id_leave);
            $update = $sql->update('leave_detail');
            $update->set($data_insert);
            $update->where($where);
            $statement = $sql->prepareStatementForSqlObject($update);
            $result = $statement->execute();
            echo 3;
            exit();
        }
    }

    public function approveByAdAction() {
        $QLeaveDetail = new \Application\Model\LeaveDetail();
        if ($this->getRequest()->isPost()) {
            $id_leave = $this->params()->fromPost('id_leave', null);
            $db = new \Database\Controller\AdapterController();
            $adapter = $db->DbAdapter();
            $sql = new Sql($adapter);
            $data_insert = array(
                'status' => 1
            );
            $where = array('id' => $id_leave);
            $update = $sql->update('leave_detail');
            $update->set($data_insert);
            $update->where($where);
            $statement = $sql->prepareStatementForSqlObject($update);
            $result = $statement->execute();
            exit();
        }
    }

    public function rejectAction() {
        $QLeaveDetail = new \Application\Model\LeaveDetail();
        if ($this->getRequest()->isPost()) {
            $id_leave = $this->params()->fromPost('id_leave2', null);
            $reason_reject = $this->params()->fromPost('reason-reject', null);
            $reason_leave = $this->params()->fromPost('reason_leave', null);

            if ($user = $this->identity()) {
                
            } else {
                echo 1;
                exit();
            }

            $QLeaveDetail->rejectLeave($user->staff_id, $id_leave, $reason_reject, $reason_leave);
            header('Location: ' . HOST . 'list-approve');
            exit();
        }
    }
    
    public function getTeamOrTitleAction(){
        $QTeam = new \Application\Model\Team();
        if ($this->getRequest()->isPost()) {
            $id =  $this->params()->fromPost('parent_id', null);
            echo json_encode($QTeam->getTeamOrDepartment($id));
            exit();
        }
    }

}
