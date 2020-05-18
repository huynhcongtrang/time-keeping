<?php

namespace Application\Model;

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

class LeaveDetail {

    private $table = 'leave_detail';

    public function checkDuplicateLeave($staff_id, $from, $to) {
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select()
                ->from(array('l' => $this->table))
                ->columns(['id'])
                ->where(array('l.staff_id' => $staff_id))
                ->where(array('l.status <> 0'))
                ->where("('" . $from . "' BETWEEN l.from_date AND l.to_date OR '" . $to . "' BETWEEN l.from_date AND l.to_date)");
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->current();
    }

    public function getLeaveByIdMM($staff_id) {//lay leave ma month to = from
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select()
                ->from(array('l' => $this->table))
                ->columns(['month_date' => new \Zend\Db\Sql\Expression('MONTH( l.to_date )'), 'total_leave' => new \Zend\Db\Sql\Expression('SUM( l.count_day )')])
                ->where(array('l.staff_id' => $staff_id))
                ->where(array('l.status' => 1))
                ->where(array('l.type_leave' => 2))
                ->where('MONTH ( l.to_date ) = MONTH ( l.from_date ) ')
                ->where('YEAR ( l.to_date ) = YEAR ( NOW( ) ) ')
                ->group(['month_date']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $db->fetch_array_key_value('month_date', 'total_leave', $result);
    }

    public function getLeaveByIdMDM($staff_id) {
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select()
                ->from(array('l' => $this->table))
                ->columns(['from_date', 'to_date', 'count_day'])
                ->where(array('l.staff_id' => $staff_id))
                ->where(array('l.type_leave' => 2))
                ->where(array('l.status' => 1))
                ->where('MONTH ( l.to_date ) <> MONTH ( l.from_date ) ')
                ->where('YEAR ( l.to_date ) = YEAR ( NOW( ) ) ')
                ->order('l.to_date');
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $db->fetch_array($result);
    }

    public function getMyLeave($id_staff, $month, $year) {
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $sql = "SELECT l.id ,l.from_date, l.to_date, l.count_day, lt.title AS leave_type, lt2.title AS group_type, l.reason,CASE  WHEN l.`status` = 0 THEN 'REJECT' WHEN l.`status` = 1 THEN 'APPROVED' WHEN l.`status` = 2 THEN 'PEDDING' END  AS status_leave FROM leave_detail l INNER JOIN leave_type lt ON l.type_leave = lt.id INNER JOIN leave_type lt2 ON lt.parent = lt2.id  WHERE (MONTH ( l.to_date ) = ? OR MONTH ( l.from_date ) = ?) AND YEAR(l.to_date) = ?  AND l.staff_id = ? AND l.del = 0";
        $statement = $adapter->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute(array(
            'month' => $month,
            'month2' => $month,
            'year' => $year,
            'id' => $id_staff
        ));
        return $db->fetch_array($result);
        //$result = $statement->execute();
    }

    //check ngay chu nhat
    function isWeekend($date) {
        $weekDay = date('w', strtotime($date));
        return ($weekDay == 0);
    }

    public function getArrayDaysSpe($from, $to) { // typeof : string ,, format : '2020-04-26'
        $data = array();
        $date1 = date_create($from);
        $date2 = date_create($to);
        $day_current = $date1;
        //get sunday
        $cout_date_special = 0;
        while ($day_current <= $date2) {
            if ($this->isWeekend($day_current->format('Y-m-d'))) {
                $cout_date_special++;
                $data[] = ['data' => $day_current->format('Y-m-d'), 'count' => 1];
            } else {
                $db = new \Database\Controller\AdapterController();
                $adapter = $db->DbAdapter();
                $sql = new Sql($adapter);
                $select = $sql->select()
                        ->from(array('sd' => 'setting_date'))
                        ->columns(['count_day_off' => new \Zend\Db\Sql\Expression("SUM(IF(sd.haft_day <> 0,0.5,1))")])
                        ->where("'" . $day_current->format('Y-m-d') . "' BETWEEN sd.`from` AND sd.`to`")
                        ->where('sd.off_date = 1');
                $statement = $sql->prepareStatementForSqlObject($select);
                $result = $statement->execute();
                if (!empty($result->current()['count_day_off'])) {
                    $cout_date_special += $result->current()['count_day_off'];
                    $data[] = ['data' => $day_current->format('Y-m-d'), 'count' => $result->current()['count_day_off']];
                }
                unset($db);
            }
            $time = strtotime($day_current->format('Y-m-d'));
            $final = date("Y-m-d", strtotime("+1 day", $time));
            $day_current = date_create($final);
        }


        return [
            'date' => $data,
            'count_date' => $cout_date_special
        ];
    }

    public function checkExceedTime($staff_id, $from, $to, $leave_type) { // 1 max per time ; 2 // max per year //3 : ngay nghi  4 cuccess 5 duplicate leave
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $sql = new Sql($adapter);
        //get type leave
        $QLeaveType = new LeaveType();
        $type_leave = $QLeaveType->getTypeLeaveById($leave_type);
        $check_duplicate = $this->checkDuplicateLeave($staff_id, $from, $to);
        if (!empty($check_duplicate)) {
            return ['status' => 5];
            exit();
        }
        //check max day
        $date1 = date_create($from);
        $date2 = date_create($to);
        $total_date_leave = date_diff($date1, $date2)->days + 1;
        $special_day = $this->getArrayDaysSpe($from, $to);
        if ($special_day['count_date'] == 0) {
            if ($type_leave['max_day_per_time'] > 0 && $total_date_leave > $type_leave['max_day_per_time']) {
                return ['status' => 1];
            }

            if ($type_leave['max_day_per_year'] > 0) {
                $select = $sql->select()
                        ->from(array('l' => $this->table))
                        ->columns(['days_leave' => new \Zend\Db\Sql\Expression("SUM(count_day)")])
                        ->where(array('l.staff_id' => $staff_id))
                        ->where(array('l.status' => 1))
                        ->group('staff_id');
                $statement = $sql->prepareStatementForSqlObject($select);
                $result = $statement->execute();
                $days_leave = $result->current()['days_leave'];
                if (($days_leave + $total_date_leave) > $type_leave['max_day_per_year']) {
                    return ['status' => 2, 'date_remain' => ($type_leave['max_day_per_year'] - $days_leave)];
                }
            }
        } else {

            //check xem phai ngay nghi hay chu nhat hk nhe.
            // check xem co trung voi ngay chu nhat hay ngay le khong. neu trung thi v
            if ($total_date_leave == $special_day['count_date']) {
                return ['status' => 3];
            } else {
                $total_date_leave = $total_date_leave - $special_day['count_date'];
                if ($type_leave['max_day_per_time'] > 0 && $total_date_leave > $type_leave['max_day_per_time']) {
                    return [
                        'total_date_leave' => $total_date_leave,
                        'date_special' => $special_day,
                        'status' => 1
                    ];
                }

                if ($type_leave['max_day_per_year'] > 0) {
                    $select = $sql->select()
                            ->from(array('l' => $this->table))
                            ->columns(['days_leave' => new \Zend\Db\Sql\Expression("SUM(count_day)")])
                            ->where(array('l.staff_id' => $staff_id))
                            ->where(array('l.status' => 1))
                            ->group('staff_id');
                    $statement = $sql->prepareStatementForSqlObject($select);
                    $result = $statement->execute();
                    $days_leave = $result->current()['days_leave'];
                    if (($days_leave + $total_date_leave) > $type_leave['max_day_per_year']) {
                        return [
                            'total_date_leave' => $total_date_leave,
                            'date_special' => $special_day,
                            'date_remain' => ($type_leave['max_day_per_year'] - $days_leave),
                            'status' => 2
                        ];
                    }
                }
                return [
                    'total_date_leave' => $total_date_leave,
                    'date_special' => $special_day,
                    'status' => 4
                ];
            }
        }
        return [
            'total_date_leave' => $total_date_leave,
            'status' => 4
        ];
    }

    public function checkDateOffHaft($staff_id, $date_off, $type) {// 1 ngay chu nhat , 2 ngay le 3 qua 1 lan dk, 4 qua 1 nam dk
        $check_duplicate = $this->checkDuplicateLeave($staff_id, $date_off, $date_off);
        if (!empty($check_duplicate)) {
            return 5;
            exit();
        }
        if ($this->isWeekend($date_off)) {
            return 1;
        } else {
            $db = new \Database\Controller\AdapterController();
            $adapter = $db->DbAdapter();
            $sql = new Sql($adapter);
            $select = $sql->select()
                    ->from(array('sd' => 'setting_date'))
                    ->columns(['count_day_off' => new \Zend\Db\Sql\Expression("SUM(IF(sd.haft_day <> 0,0.5,1))")])
                    ->where("'" . $date_off . "' BETWEEN sd.`from` AND sd.`to`")
                    ->where('sd.off_date = 1');
            $statement = $sql->prepareStatementForSqlObject($select);
            $result = $statement->execute();
            if (!empty($result->current()['count_day_off'])) {
                return 2;
            } else {
                //get type leave
                $QLeaveType = new LeaveType();
                $type_leave = $QLeaveType->getTypeLeaveById($type);
                if ($type_leave['max_day_per_time'] > 0 && 0.5 > $type_leave['max_day_per_time']) {
                    return 3;
                }
                if ($type_leave['max_day_per_year'] > 0) {
                    $db = new \Database\Controller\AdapterController();
                    $adapter = $db->DbAdapter();
                    $sql = new Sql($adapter);
                    $select = $sql->select()
                            ->from(array('l' => $this->table))
                            ->columns(['cout_date' => new \Zend\Db\Sql\Expression("COUNT(l.count_day)")])
                            ->where(array('l.staff_id' => $staff_id))
                            ->where('YEAR(l.from_date) = YEAR(NOW())');
                    $statement = $sql->prepareStatementForSqlObject($select);
                    $result2 = $statement->execute();
                    if (($result2->current()['cout_date'] + 0.5) > $type_leave['max_day_per_year']) {
                        return 4;
                    }
                }
            }
        }
        return 5;
    }
    public function getLocationStaff($staff_id){
         //get local staff1
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $sql = "SELECT CASE WHEN s.department_id = " . DEPARTMENT_ADMIN . " THEN 1  WHEN s.department_id = " . DEPARTMENT_HR . " THEN 2  WHEN s.is_leader = 1  AND s.department_id<> " . DEPARTMENT_ADMIN . " THEN 3 ELSE 0  END AS `loca_staff`, s.department_id  FROM staff s  WHERE s.disible = 0  AND s.id = ?";
        $statement = $adapter->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute(array(
            'staff_id' => $staff_id
        ));
        return $result->current();
    }
    public function getListLeaveStaff($staff_id, $param = null) { //1:admin , 2: hr , 3 : leader
        //get local staff1
        $location_staff  = $this->getLocationStaff($staff_id);

        $sql2 = "SELECT l.id,s.`code`, s.full_name, t.`name` AS deparment, t2.`name` team, t3.`name` title, l.from_date, l.to_date, l.count_day, lt.title AS leave_type, lt2.title group_type, l.reason, s2.full_name AS approve_by_hr , l.approve_at_hr,s1.full_name approve_by_leader,l.approve_at_leader,CASE  WHEN l.`status` = 2 THEN 'PENDING' END  AS `status` FROM leave_detail l INNER JOIN staff s ON s.id = l.staff_id LEFT JOIN `user` u on u.staff_id = s.id LEFT JOIN team t ON t.id = s.department_id LEFT JOIN team t2 ON t2.id = s.team_id LEFT JOIN team t3 ON t3.id = s.title_id INNER JOIN leave_type lt ON lt.id = l.type_leave INNER JOIN leave_type lt2 ON lt2.id = lt.parent LEFT JOIN staff s1 ON s1.id = l.approve_by_leader LEFT JOIN staff s2 ON s2.id = l.approve_by_hr  WHERE l.`status` = 2  AND l.del = 0  AND s.disible = 0 ";
        if ($location_staff['loca_staff'] == 1) {
            
        } else if ($location_staff['loca_staff'] == 2) {
            $sql2 = $sql2 . " AND S.department_id <> " . DEPARTMENT_ADMIN;
        } else if ($location_staff['loca_staff'] == 3) {
            $sql2 = $sql2 . " AND s.department_id = " . $location_staff['department_id'] . " AND s.id <> " . $staff_id;
        } else
            return null;
        if (isset($param['full_name']) && !empty($param['full_name'])) {
            $sql2 = $sql2 . " AND s.full_name LIKE '%" . $param['full_name'] . "%'";
        }

        if (isset($param['email']) && !empty($param['email'])) {
            $sql2 = $sql2 . " AND u.email LIKE '" . $param['email'] . "%'";
        }

        if (isset($param['code']) && !empty($param['code'])) {
            $sql2 = $sql2 . " AND s.`code` = '" . $param['code'] . "'";
        }

        if (isset($param['department']) && !empty($param['department'])) {
            $sql2 = $sql2 . " AND s.department_id = " . $param['department'];
        }

        if (isset($param['team']) && !empty($param['team'])) {
            $sql2 = $sql2 . " AND s.team_id = " . $param['team'];
        }

        if (isset($param['title']) && !empty($param['title'])) {
            $sql2 = $sql2 . " AND s.title_id = " . $param['title'];
        }

        if (isset($param['date']) && !empty($param['date'])) {
            $arr = explode("-", $param['date']);
            $sql2 = $sql2 . " AND MONTH ( l.to_date ) = " . $arr[0] . "  AND YEAR ( l.to_date ) = " . $arr[1];
        } else {
            $sql2 = $sql2 . " AND MONTH ( l.to_date ) = MONTH ( NOW( ) )  AND YEAR ( l.to_date ) = YEAR ( NOW( ) )";
        }

        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $statement = $adapter->createStatement($sql2);
        $statement->prepare();
        $result = $statement->execute();
        $list_array_leave = $db->fetch_array($result);
        unset($db);
        unset($adapter);
        unset($result);
        return $list_array_leave;
    }

    public function checkArpoved($type, $id_leave) { //11 : approve by leader , approve by hr
        if ($type == 1) {
            $sql = "SELECT COUNT(l.id) as `count` FROM leave_detail l WHERE l.approve_by_leader IS NOT NULL AND l.id = " . $id_leave;
        } else if ($type == 2) {
            $sql = "SELECT COUNT(l.id) as `count` FROM leave_detail l WHERE l.approve_by_hr IS NOT NULL AND l.id = " . $id_leave;
        }
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $statement = $adapter->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute();
        return $result->current()['count'];
    }

    public function rejectLeave($staff_id, $leave_id, $reason_id, $note) {
        // update status leave
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $sql = new Sql($adapter);

        $data_insert = array(
            'status' => 0
        );
        $where = array('id' => $leave_id);
        $update = $sql->update('leave_detail');
        $update->set($data_insert);
        $update->where($where);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();

        // insert reason reject
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $sql = new Sql($adapter);

        $data_insert = array(
            'id_leave' => $leave_id,
            'id_reason_reject' => $reason_id,
            'reason' => $note,
            'create_by' => $staff_id,
            'create_at' => date("Y-m-d H:i:s")
        );
        $insert = $sql->insert('reason_reject_leave');
        $insert->values($data_insert);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();
    }

    public function getListLeave($staff_id , $param = array()) {
        $location_staff  = $this->getLocationStaff($staff_id);
        
        $sql = "SELECT	l.id , s.`code`,s.full_name,u.email,t.`name` AS deparment,t2.`name` team,t3.`name` title,l.from_date,l.to_date,l.count_day,lt.title AS leave_type,lt2.title group_type,l.reason,s1.full_name AS approve_by_leader,l.approve_at_leader,s2.full_name AS approve_by_hr , l.approve_at_hr , l.`status` FROM leave_detail l INNER JOIN staff s ON s.id = l.staff_id INNER JOIN `user` u ON u.staff_id = s.id	LEFT JOIN team t ON t.id = s.department_id	LEFT JOIN team t2 ON t2.id = s.team_id	LEFT JOIN team t3 ON t3.id = s.title_id	INNER JOIN leave_type lt ON lt.id = l.type_leave	INNER JOIN leave_type lt2 ON lt2.id = lt.parent 	LEFT JOIN staff s1 ON s1.id = l.approve_by_leader 	LEFT JOIN staff s2 ON s2.id = l.approve_by_hr WHERE	l.del = 0 AND s.disible = 0 AND l.`status` <> 2";
        if ($location_staff['loca_staff'] == 1) {
            
        } else if ($location_staff['loca_staff'] == 2) {
            $sql = $sql . " AND s.department_id <> " . DEPARTMENT_ADMIN;
        } else if ($location_staff['loca_staff'] == 3) {
            $sql = $sql . " AND s.department_id = " . $location_staff['department_id'] . " AND s.id <> " . $staff_id;
        } else
            return null;
        if (isset($param['full_name']) && !empty($param['full_name'])) {
            $sql = $sql . " AND s.full_name LIKE '%" . $param['full_name'] . "%'";
        }

        if (isset($param['email']) && !empty($param['email'])) {
            $sql = $sql . " AND u.email LIKE '" . $param['email'] . "%'";
        }

        if (isset($param['code']) && !empty($param['code'])) {
            $sql = $sql . " AND s.`code` = '" . $param['code'] . "'";
        }

        if (isset($param['department']) && !empty($param['department'])) {
            $sql = $sql . " AND s.department_id = " . $param['department'];
        }

        if (isset($param['team']) && !empty($param['team'])) {
            $sql = $sql . " AND s.team_id = " . $param['team'];
        }

        if (isset($param['title']) && !empty($param['title'])) {
            $sql = $sql . " AND s.title_id = " . $param['title'];
        }

        if (isset($param['date']) && !empty($param['date'])) {
            $arr = explode("-", $param['date']);
            $sql = $sql . " AND MONTH ( l.to_date ) = " . $arr[0] . "  AND YEAR ( l.to_date ) = " . $arr[1];
        } else {
            $sql = $sql . " AND MONTH ( l.to_date ) = MONTH ( NOW( ) )  AND YEAR ( l.to_date ) = YEAR ( NOW( ) )";
        }
       
        if (isset($param['status']) && $param['status'] != '') {
            $sql = $sql . " AND l.status = " . $param['status'];
        }
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $statement = $adapter->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute();
        return $db->fetch_array($result);
    }

    public function delLeaveDetail($id_leave) {
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $sql = new Sql($adapter);
        $update = $sql->update('leave_detail');
        $update->set(array('del' => 1));
        $update->where(array('id' => $id_leave));

        $statement = $sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();
        return 1;
    }

    //check status leave detail
    public function checkStatusLeave($id_leave) {
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select()
                ->from(array('l' => $this->table))
                ->columns(['status'])
                ->where(array('l.id' => $id_leave));
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->current()['status'];
    }

    public function getDetailLeaveByIdLeave($id_leave) {
        $sql = "SELECT l.id, s.`code` codea_staff, s.full_name full_name_staff, l.from_date, l.to_date, l.count_day, lt.title AS leave_type, lt2.title group_type, l.reason, s1.full_name AS approve_by_leader, t.`name` `department_leader`, l.approve_at_leader, s2.full_name AS approve_by_hr, t2.`name` `department_hr`, l.approve_at_hr, l.`status`  FROM leave_detail l INNER JOIN staff s ON s.id = l.staff_id INNER JOIN leave_type lt ON lt.id = l.type_leave INNER JOIN leave_type lt2 ON lt2.id = lt.parent LEFT JOIN staff s1 ON s1.id = l.approve_by_leader LEFT JOIN team t ON t.id = s1.department_id LEFT JOIN staff s2 ON s2.id = l.approve_by_hr  LEFT JOIN team t2 ON t2.id = s2.department_id WHERE l.del = 0  AND s.disible = 0  AND l.id = ?";
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $statement = $adapter->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute(array(
            'id_leave' => $id_leave,
        ));
        return $result->current(); 
    }

    public function getDetailAppOrReLeave($id_leave) {
        $QStaffReason = new ReasonReject();
        $status = $this->checkStatusLeave($id_leave);
        if ($status == 0) {
            $data = $QStaffReason->getDetailRejectDeltail($id_leave);
        } else if ($status == 1) {
            $data = $this->getDetailLeaveByIdLeave($id_leave);
        }
        return [
          'status' => $status,
           'data' => $data
        ];
    }

}
