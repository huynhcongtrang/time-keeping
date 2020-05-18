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

class ReasonReject {

    private $table = 'reason_reject';

    public function getReasonReject($type) { // type 1 : leave
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select()
                ->from(array('l' => $this->table))
                ->where(array('l.type' => $type));
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $db->fetch_array($result);
    }
    
    public function getDetailReject($id_leave){
        $sql = "SELECT  s.`code` codea_staff , s.full_name AS `full_name_staff` ,  s2.`code` `code_reject`, s2.full_name `full_name_reject` , r1.reason_reject , r.reason,r.create_at FROM reason_reject_leave r INNER JOIN reason_reject r1 ON r1.id = r.id_reason_reject INNER JOIN leave_detail l ON l.id  = r.id_leave INNER JOIN staff s ON s.id = l.staff_id INNER JOIN staff s2 ON s2.id = r.create_by WHERE r.id_leave = ?";
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $statement = $adapter->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute(array(
            'id_leave' => $id_leave,
            //'staff_id' => $staff_id
        ));
        return $result->current();       
    }
    
    public function getDetailRejectDeltail($id_leave){
        $sql = "SELECT s.`code` codea_staff, s.full_name AS `full_name_staff`, s2.`code` `code_reject`, s2.full_name `full_name_reject`, r1.reason_reject, r.reason, r.create_at, t.`name` `department_reject`, s3.full_name `approve_by_leader` ,  t1.`name` `department_leader`, l.approve_at_leader ,  s4.full_name `approve_by_hr` ,  t2.`name` `department_hr`, l.approve_at_hr FROM reason_reject_leave r INNER JOIN reason_reject r1 ON r1.id = r.id_reason_reject INNER JOIN leave_detail l ON l.id = r.id_leave	INNER JOIN staff s ON s.id = l.staff_id INNER JOIN staff s2 ON s2.id = r.create_by LEFT JOIN team t ON t.id = s2.department_id LEFT JOIN staff s3 ON s3.id = l.approve_by_leader  LEFT JOIN team t1 ON t1.id = s3.department_id LEFT JOIN staff s4 ON s4.id = l.approve_by_hr LEFT JOIN team t2 ON t2.id = s4.department_id WHERE r.id_leave = ?";
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $statement = $adapter->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute(array(
            'id_leave' => $id_leave,
            //'staff_id' => $staff_id
        ));
        return $result->current();       
    }

}
