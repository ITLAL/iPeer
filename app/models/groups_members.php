<?php

class GroupsMembers extends AppModel
{
  var $name = 'GroupsMembers';

  // inserts all members into the groups_members table
  function insertMembers($id=null, $data=null){
  	for( $i=1; $i<=$data['member_count']; $i++ ){
  	  $tmp = array( 'group_id'=>$id, 'user_id'=>$data['member'.$i] );
  	  $this->save($tmp);
  	  //reset the id field
  	  $this->id = null;
    }
  }

  // updates all the group members
  function updateMembers($id=null, $data=null){
//get old userid's
	$tmp = $this->getMembers($id);
	$oldUsers = array();
	$newUsers = array();
	$insertUsers = array();
	$deleteUsers = array();

	for ($i = 1; $i <= $data['member_count']; $i++) array_push($newUsers, $data['member'.$i]);
	for ($i = 0; $i < $tmp['member_count']; $i++) array_push($oldUsers, $tmp[$i]['GroupsMembers']['user_id']);

//compare
	$insertUsers = array_diff($newUsers, $oldUsers);
	$deleteUsers = array_diff($oldUsers, $newUsers);

//insert/delete
	foreach ($insertUsers as $userId) {
		$tmp = array('group_id'=>$id, 'user_id'=>$userId);
		$this->save($tmp);
		$this->id = null;
	}
	foreach ($deleteUsers as $userId) {
		$tmp = $this->find('first',array(
                    'conditions' => array('group_id'=>$id,'user_id'=>$userId),
                    'fields' => array('GroupMember.id')
                    ));
		$this->del($tmp['GroupsMembers']['id']);
		$this->id = null;
	}
 }

  // returns the user_id of all members in a specific group
  function getMembers($id){
    $tmp = $this->find('list',array('conditions' => array('group_id' => $id),
                                   'fields' => array('user_id')));
    //$tmp['member_count'] = count($tmp);
    return $tmp;
  }



  function countMembers($groupID) {
    if (!is_numeric($groupID)) {
      return -1;
    } else {
      return $this->find('count', array('conditions' => array('group_id' => $groupID)));
    }
  }

  function getEventGroupMembers ($groupId, $selfEval, $userId)
  {
  	$sql = "SELECT U . *
			FROM users U, groups_members G
			WHERE U.id = G.user_id";
  	//var_dump($this->query($sql));
  	return $this->query($sql);
  	/*
     if ($selfEval)
     {//Include self on evaluation
<<<<<<< .mine
        $condition = array( 'conditions' => array('GroupsMembers.group_id'=>$groupId));
=======
        $condition = array('GroupsMembers.group_id' => $groupId);
>>>>>>> .r489
     }
     else {
<<<<<<< .mine
        $condition = array('conditions' => array('GroupsMembers.group_id'=>$groupId, 'User.id'=>$userId));
=======
        $condition = array('GroupsMembers.group_id' => $groupId, 'User.id <>' => $userId);
>>>>>>> .r489
     }
<<<<<<< .mine
    $fields = array('fields'=>array('User.id, User.role, User.username, User.first_name, User.last_name, User.student_no, User.title, User.email'));
    $joinTable = array(' RIGHT JOIN users as User ON User.id=GroupsMembers.user_id');

    //return $this->find('all',$condition, $fields, 'User.last_name ASC', null, null, null, $joinTable );
    //var_dump($this->find('all',$condition, $fields));
    return $this->find('all',$condition, $fields);
    */
  }
  
  function getGroupsByUserId($userId){
  	$sql = "SELECT *
  			FROM groups_members
  			WHERE user_id=$userId";
  	return $this->query($sql);
  }
  
  //Checks whether a User belongs to a specific group
  function checkMembershipInGroup($groupId, $userId){
  	$sql = "SELECT id
  			FROM groups_members
  			WHERE group_id=$groupId AND user_id=$userId";
  	$id = $this->query($sql);
  	if($id==null)return false;
  	else return true;
//    $fields = 'User.id, User.role, User.username, User.first_name, User.last_name, User.student_no, User.title, User.email';
//    $joinTable = array(' RIGHT JOIN users as User ON User.id=GroupsMembers.user_id');
//
//    return $this->find('all',$condition, $fields, 'User.last_name ASC', null, null, null, $joinTable );
    return $this->find('all', array(
                    'conditions' => $condition,
                    'fields' => array('User.id', 'User.role', 'User.username', 'User.first_name', 'User.last_name', 'User.student_no', 'User.title', 'User.email'),
                    'joins' => array(
                        array(
                            'table' => 'users',
                            'alias' => 'User',
                            'type' => 'RIGHT',
                            'conditions' => array('User.id = GroupsMember.user_id')
                        )
                    ),
                    'order' => 'User.last_name ASC'
                ));


  }
}

?>
