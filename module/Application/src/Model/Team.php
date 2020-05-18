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

class Team {

    private $table = 'team';

    public function getDepartment($staff_id) {
        $QStaff = new Staff();
        $info_der = $QStaff->getTeamStaff($staff_id);
        if ($info_der['department_id'] == DEPARTMENT_ADMIN || $info_der['department_id'] == DEPARTMENT_HR) {
            $db = new \Database\Controller\AdapterController();
            $adapter = $db->DbAdapter();
            $sql = new Sql($adapter);
            $select = $sql->select()
                    ->from(array('s' => $this->table))
                    ->columns(['id', 'name'])
                    ->where(array('s.parent_id' => 0));
            $statement = $sql->prepareStatementForSqlObject($select);
            $result = $statement->execute();
            return $db->fetch_array($result);
        } else if ($info_der['is_leader'] == 1) {
            $db = new \Database\Controller\AdapterController();
            $adapter = $db->DbAdapter();
            $sql = new Sql($adapter);
            $select = $sql->select()
                    ->from(array('s' => $this->table))
                    ->columns(['id', 'name'])
                    ->where(array('s.parent_id' => 0))
                    ->where(array('s.id' => $info_der['department_id']));
            $statement = $sql->prepareStatementForSqlObject($select);
            $result = $statement->execute();
            return $db->fetch_array($result);
        }
    }

    
    public function getTeamOrDepartment2() {// not parent_id
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select()
                ->from(array('s' => $this->table))
                ->columns(['id', 'name'])
                ->where(array('s.parent_id <> 0'));
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $db->fetch_array_key_value('id', 'name', $result);
    }
    
    public function getTeamOrDepartment($parent_id) {
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select()
                ->from(array('s' => $this->table))
                ->columns(['id', 'name'])
                ->where(array('s.parent_id' => $parent_id));
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $db->fetch_array($result);
    }

}
