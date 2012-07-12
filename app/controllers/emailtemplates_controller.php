<?php
/**
 * EmailtemplatesController
 *
 * @uses AppController
 * @package   CTLT.iPeer
 * @author    Pan Luo <pan.luo@ubc.ca>
 * @copyright 2012 All rights reserved.
 * @license   MIT {@link http://www.opensource.org/licenses/MIT}
 */
class EmailtemplatesController extends AppController
{
    public $name = 'EmailTemplates';
    public $uses = array('GroupsMembers', 'UserEnrol', 'User', 'EmailTemplate',
        'EmailMerge', 'EmailSchedule', 'Personalize', 'SysParameter', 'SysFunction');
    public $components = array('AjaxList', 'Session', 'RequestHandler', 'Email');
    public $helpers = array('Html', 'Ajax', 'Javascript', 'Time', 'Js' => array('Prototype'));
    public $show;
    public $sortBy;
    public $direction;
    public $page;
    public $order;
    public $Sanitize;

    /**
     * __construct
     *
     * @access protected
     * @return void
     */
    function __construct()
    {
        $this->Sanitize = new Sanitize;
        $this->show = empty($_GET['show'])? 'null': $this->Sanitize->paranoid($_GET['show']);
        if ($this->show == 'all') {
            $this->show = 99999999;
        }
        $this->sortBy = empty($_GET['sort'])? 'EmailTemplate.description': $this->Sanitize->paranoid($_GET['sort']);
        $this->direction = empty($_GET['direction'])? 'asc': $this->Sanitize->paranoid($_GET['direction']);
        $this->page = empty($_GET['page'])? '1': $this->Sanitize->paranoid($_GET['page']);
        $this->order = $this->sortBy.' '.strtoupper($this->direction);
        $this->set('title_for_layout', __('Email', true));
        parent::__construct();
    }

    /**
     * setUpAjaxList
     *
     * @access public
     * @return void
     */
    function setUpAjaxList()
    {
        $myID = $this->Auth->user('id');

        // Set up Columns
        $columns = array(
            array("EmailTemplate.id",   "",       "",        "hidden"),
            array("EmailTemplate.name", __("Name", true),   "12em",    "action",   "View Email Template"),
            array("EmailTemplate.subject", __("Subject", true),   "12em",    "string"),
            array("EmailTemplate.description", __("Description", true), "auto",  "string"),
            array("EmailTemplate.creator_id",           "",            "",     "hidden"),
            array("EmailTemplate.creator",     __("Creator", true),  "10em", "action", "View Creator"),
            array("EmailTemplate.created", __("Creation Date", true), "10em", "date"));

        $userList = array($myID => "My Email Template");

        // Join with Users
        $jointTableCreator =
            array("id"         => "Creator_id",
                "localKey"   => "creator_id",
                "description" => __("Email Template to show:", true),
                "default" => $myID,
                "list" => $userList,
                "joinTable"  => "users",
                "joinModel"  => "Creator");
        //put all the joins together
        $joinTables = array($jointTableCreator);

        $extraFilters = "";

        // Restriction for Instructor
        $restrictions = "";
        if (!User::hasRole('superadmin') && !User::hasRole('admin')) {
            $restrictions = array(
                "EmailTemplate.creator_id" => array($myID => true, "!default" => false)
            );
            $extraFilters = "(EmailTemplate.creator_id=$myID or EmailTemplate.availability='1')";
        }

        // Set up actions
        $warning = __("Are you sure you want to delete this email template permanently?", true);

        $actions = array(
            array(__("View Email Template", true), "", "", "", "view", "EmailTemplate.id"),
            array(__("Edit Email Template", true), "", $restrictions, "", "edit", "EmailTemplate.id"),
            array(__("Delete Email Template", true), $warning, $restrictions, "", "delete", "EmailTemplate.id"),
            array(__("View Creator", true), "",    "", "users", "view", "EmailTemplate.creator_id"));

        // Set up the list itself
        $this->AjaxList->setUp($this->EmailTemplate, $columns, $actions,
            "EmailTemplate.id", "EmailTemplate.subject", $joinTables, $extraFilters);
    }


    /**
     * ajaxList
     *
     *
     * @access public
     * @return void
     */
    function ajaxList()
    {
        // Set up the list
        $this->setUpAjaxList();
        // Process the request for data
        $this->AjaxList->asyncGet();
    }

    /**
     * index
     *
     *
     * @access public
     * @return void
     */
    function index()
    {
        if (!User::hasPermission('controllers/emailtemplates')) {
            $this->Session->setFlash(__('You do not have permission to access email templates.', true));
            $this->redirect('/home');
        }
        
        // Set up the basic static ajax list variables
        $this->setUpAjaxList();
        // Set the display list
        $this->set('paramsForList', $this->AjaxList->getParamsForList());
    }

    /**
     * add
     *
     * Add an email template
     *
     * @access public
     * @return void
     */
    function add()
    {
        if (!User::hasPermission('controllers/emailtemplates')) {
            $this->Session->setFlash(__('You do not have permission to add email templates.', true));
            $this->redirect('index');
        }
        
        $this->set('title_for_layout', __('Add Email Template', true));
        $this->layout = 'pop_up';
        // Set up user info
        $currentUser = $this->User->getCurrentLoggedInUser();
        $this->set('currentUser', $currentUser);
        $this->set('mergeList', $this->EmailMerge->getMergeList());
        if (empty($this->params['data'])) {

        } else {
            //Save Data
            if ($this->EmailTemplate->save($this->params['data'])) {
                $this->Session->setFlash(__('Save Successful!', true), 'good');
            } else {
                $this->Session->setFlash(__('Save failed.', true));
            }
        }
    }


    /**
     * edit
     * Edit an email template
     *
     * @param mixed $id template id
     *
     * @access public
     * @return void
     */
    function edit ($id)
    {
        if (!User::hasPermission('controllers/emailtemplates/edit')) {
            $this->Session->setFlash(__('You do not have permission to edit email templates.', true));
            $this->redirect('index');
        }
        if ($this->data) {
            //Save Data
            if ($this->EmailTemplate->save($this->data)) {
                $this->Session->setFlash(__('Save Successful', true), 'good');
                $this->redirect('index');
            } else {
                $this->Session->setFlash(__('Failed to save', true));
            }
            return;
        }
        
        // retrieving the requested email template
        $template = $this->EmailTemplate->find('first', array('conditions' => array('id' => $id)));
        
        $this->set('title_for_layout', __('Edit Email Template', true));
        // check to see if $id is valid - is an email template
        if (empty($template)) {
            $this->Session->setFlash(__('Invalid ID.', true));
            $this->redirect('index');
        }
        // check to see if the user is the creator, admin, or superadmin
        if (!($template['EmailTemplate']['creator_id'] == $this->Auth->user('id') ||
            User::hasPermission('functions/emailtemplate'))) {
            $this->Session->setFlash(__('You do not have permission to edit this email template.', true));
            $this->redirect('index');
        }

        //Set up user info
        $currentUser = $this->User->getCurrentLoggedInUser();
        $this->set('currentUser', $currentUser);
        $this->set('mergeList', $this->EmailMerge->getMergeList());
        
        $this->data = $template;
    }


    /**
     * Delete an email template
     * @param <type> $id template id
     */
    function delete ($id)
    {
        if (!User::hasPermission('controllers/emailtemplates')) {
            $this->Session->setFlash(__('You do not have permission to delete email templates.', true));
            $this->redirect('/home');
        }

        // retrieving the requested email template
        $template = $this->EmailTemplate->find(
            'first', 
            array(
                'conditions' => array('id' => $id), 
            )
        );
        
        // check to see if $id is valid - numeric & is a email template
        if (empty($template)) {
            $this->Session->setFlash(__('Invalid ID.', true));
            $this->redirect('index');
        }
        
        // check to see if the user is the creator, admin, or superadmin
        if (!($template['EmailTemplate']['creator_id'] == $this->Auth->user('id') ||
            User::hasPermission('functions/emailtemplate'))) {
            $this->Session->setFlash(__('You do not have permission to delete this email template.', true));
            $this->redirect('index');
        }
        
        $creator_id = $this->EmailTemplate->getCreatorId($id);
        $user_id = $this->Auth->user('id');
        if ($creator_id == $user_id) {
            if ($this->EmailTemplate->delete($id)) {
                $this->Session->setFlash(__('The Email Template was deleted successfully.', true), 'good');
            } else {
                $this->Session->setFlash(__('Failed to delete the Email Template.', true));
            }
            $this->redirect('index');
        } else {
            $this->Session->setFlash(__('No Permission', true));
            $this->redirect('index');
        }
    }

    /**
     * View an email template
     * @param <type> $id template id
     */
    function view($id)
    {
        if (!User::hasPermission('controllers/emailtemplates')) {
            $this->Session->setFlash(__('You do not have permission to view email templates.', true));
            $this->redirect('/home');
        }
        
        $this->set('title_for_layout', __('View Email Template', true));   //title for view
        
        // retrieving the requested email template
        $template = $this->EmailTemplate->find('first', array('conditions' => array('id' => $id)));
        
        // check to see if $id is valid - numeric & is a email template
        if (!is_numeric($id) || empty($template)) {
            $this->Session->setFlash(__('Invalid ID.', true));
            $this->redirect('index');
        }
        
        // check to see if the user is the creator, admin, or superadmin
        if (!($template['EmailTemplate']['creator_id'] == $this->Auth->user('id') ||
            User::hasPermission('functions/emailtemplate'))) {
            $this->Session->setFlash(__('You do not have permission to view this email template.', true));
            $this->redirect('index');
        }
        
        $this->data = $this->EmailTemplate->find('first', array('conditions' => array('EmailTemplate.id' => $id)));
        $this->set('readonly', true);
    }

    /**
     * display template content for updating field by selecting a template
     * @param <type> $templateId template id
     */
    function displayTemplateContent($templateId = null)
    {
        $this->layout = 'ajax';
        $template = $this->EmailTemplate->find('first', array(
            'conditions' => array('EmailTemplate.id' => $templateId)
        ));
        $this->set('template', $template);
    }

    /**
     * displayTemplateSubject
     * display template subject for updating field by selecting a template
     *
     * @param int $templateId template id
     *
     * @access public
     * @return void
     */
    function displayTemplateSubject($templateId = null)
    {
        $this->layout = 'ajax';
        $template = $this->EmailTemplate->find('first', array(
            'conditions' => array('EmailTemplate.id' => $templateId)
        ));
        $this->set('template', $template);
    }

}
