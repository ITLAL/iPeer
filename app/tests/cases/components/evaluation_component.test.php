<?php
App::import('Component', 'Evaluation');
App::import('Component', 'Auth');
class FakeEvaluationController extends Controller {
    public $name = 'FakeEvaluationController';
    public $components = array('Evaluation', 'Auth');
    public $uses = null;
    public $params = array('action' => 'test');
}

class EvaluationTestCase extends CakeTestCase
{
    public $fixtures = array('app.course', 'app.role', 'app.user', 'app.group', 'app.penalty', 'app.user_grade_penalty',
        'app.roles_user', 'app.event', 'app.event_template_type',
        'app.group_event', 'app.evaluation_submission','app.evaluation_mixeval',
        'app.survey_group_set', 'app.survey_group', 'app.groups_member',
        'app.survey_group_member', 'app.question', 'app.survey_input', 'app.rubrics_lom', 'app.evaluation_rubric', 'app.evaluation_rubric_detail',
        'app.response', 'app.survey_question', 'app.user_course', 'app.rubric', 'app.rubrics_criteria', 'app.rubrics_criteria_comment',
        'app.user_enrol', 'app.groups_member', 'app.survey', 'app.mixeval', 'app.mixevals_question', 'app.mixevals_question_desc',
        'app.evaluation_mixeval', 'app.evaluation_mixeval_detail',
        'app.evaluation_simple', 'app.faculty', 'app.user_faculty',
        'app.department', 'app.course_department', 'app.oauth_token', 'app.sys_parameter',
        'app.user_tutor'
    );

    function startCase()
    {
        $this->EvaluationComponentTest = new EvaluationComponent();
        $this->EvaluationSimple = ClassRegistry::init('EvaluationSimple');
        $this->SurveyInput = ClassRegistry::init('SurveyInput');
        $this->SurveyQuestion = ClassRegistry::init('SurveyQuestion');
        $this->EvaluationSubmission = ClassRegistry::init('EvaluationSubmission');
        $this->EvaluationMixeval = ClassRegistry::init('EvaluationMixeval');
        $this->EvaluationRubric = ClassRegistry::init('EvaluationRubric');
        $this->EvaluationRubricDetail   = ClassRegistry::init('EvaluationRubricDetail');
        $this->Event = ClassRegistry::init('Event');

        $this->EvaluationMixevalDetail = ClassRegistry::init('EvaluationMixevalDetail');
        $admin = array('User' => array('username' => 'root',
            'password' => 'ipeer'));
        $this->controller = new FakeEvaluationController();

        $this->controller->constructClasses();
        $this->controller->startupProcess();
        $this->controller->Component->startup($this->controller);
        $this->controller->Auth->startup($this->controller);
        ClassRegistry::addObject('view', new View($this->Controller));
        ClassRegistry::addObject('Auth', $this->controller->Auth);
        $admin = array('User' => array('username' => 'Admin',
            'password' => 'passwordA'));
        $this->controller->Auth->login($admin);
    }

    function testDaysLate()
    {
        $result = $this->EvaluationComponentTest->daysLate(1, '2011-06-10 00:00:05');
        $this->assertEqual($result, 1);
        $result = $this->EvaluationComponentTest->daysLate(1, '2011-06-12 00:00:05');
        $this->assertEqual($result, 3);
        $result = $this->EvaluationComponentTest->daysLate(1, '2011-06-01 00:00:00');
        $this->assertEqual($result, 0);
    }

    function testFormatGradeReleaseStatus()
    {
        // Set up test data
        $groupEventNone = array();
        $groupEventNone['GroupEvent']['grade_release_status'] = 'None';
        $groupEventSome = array();
        $groupEventSome['GroupEvent']['grade_release_status'] = 'Some';
        $groupEventAll = array();
        $groupEventAll['GroupEvent']['grade_release_status'] = 'All';

        // Case one: "grade_release_status" changed from None => Some
        $result = $this->EvaluationComponentTest->formatGradeReleaseStatus($groupEventNone, true, 3);
        $gradeReleaseStatus = $result['GroupEvent']['grade_release_status'];
        $this->assertEqual($gradeReleaseStatus, 'Some');

        // Case two: "grade_release_status" changed from Some => All
        $result = $this->EvaluationComponentTest->formatGradeReleaseStatus($groupEventSome, true, 0);
        $gradeReleaseStatus = $result['GroupEvent']['grade_release_status'];
        $this->assertEqual($gradeReleaseStatus, 'All');

        // Case three: "grade_release_status" changed from Some => None
        $result = $this->EvaluationComponentTest->formatGradeReleaseStatus($groupEventSome, false, 0);
        $gradeReleaseStatus = $result['GroupEvent']['grade_release_status'];
        $this->assertEqual($gradeReleaseStatus, 'None');

        // Case four: "grade_release_status" changed from All => Some
        $result = $this->EvaluationComponentTest->formatGradeReleaseStatus($groupEventAll, false, 0);
        $gradeReleaseStatus = $result['GroupEvent']['grade_release_status'];
        $this->assertEqual($gradeReleaseStatus, 'Some');

        // Case five: "grade_release_status" stays the same
        $result = $this->EvaluationComponentTest->formatGradeReleaseStatus($groupEventAll, true, 0);
        $gradeReleaseStatus = $result['GroupEvent']['grade_release_status'];
        $this->assertEqual($gradeReleaseStatus, 'All');

        $result = $this->EvaluationComponentTest->formatGradeReleaseStatus($groupEventNone, false, 0);
        $gradeReleaseStatus = $result['GroupEvent']['grade_release_status'];
        $this->assertEqual($gradeReleaseStatus, 'None');
    }

    function testGetGroupReleaseStatus()
    {
        // Set up test data
        $groupEvent = array();
        $groupEvent['GroupEvent']['grade_release_status'] = 'Some';
        $groupEvent['GroupEvent']['comment_release_status'] = 'Some';
        $expect = array('grade_release_status' => 'Some',
            'comment_release_status' => 'Some');
        // Run tests
        $result = $this->EvaluationComponentTest->GetGroupReleaseStatus($groupEvent);
        $this->assertEqual($result, $expect);
    }

    function testFilterString()
    {
        $testString = "HELLO THIS IS A TEST";
        $result = $this->EvaluationComponentTest->filterString($testString);
        $this->assertEqual($testString, $result);

        $testString2 = "HELLO232_32";
        $result = $this->EvaluationComponentTest->filterString($testString2);
        $expect = "HELLO";
        $this->assertEqual($result, $expect);
    }

    function testSaveSimpleEvaluation()
    {
        // Assert data was not saved prior to running function
        $search1 = $this->EvaluationSimple->find('first', array('conditions' => array('comment' => 'Kevin Luk was smart')));
        $search2 = $this->EvaluationSimple->find('first', array('conditions' => array('comment' => 'Zion Au was also smart')));
        $searchEvalSubmission = $this->EvaluationSubmission->find('all', array('conditions' => array('grp_event_id' => 999)));
        $this->assertFalse($search1);
        $this->assertFalse($search2);
        $this->assertFalse($searchEvalSubmission);

        // Set up test data
        $input = $this->setUpSimpleEvaluationTestData();
        $params = $input[0];
        $groupEvent = $input[1];
        $result1 = $this->EvaluationComponentTest->saveSimpleEvaluation($params, $groupEvent, null);
        $search1 = $this->EvaluationSimple->find('first', array('conditions' => array('comment' => 'Kevin Luk was smart')));
        $search2 = $this->EvaluationSimple->find('first', array('conditions' => array('comment' => 'Zion Au was also smart')));
        $searchEvalSubmission = $this->EvaluationSubmission->find('all', array('conditions' => array('grp_event_id' => 999)));

        // Run tests
        $this->assertTrue($search1);
        $this->assertTrue($search2);
        $this->assertTrue($searchEvalSubmission);
        $this->assertEqual($search1['EvaluationSimple']['comment'], 'Kevin Luk was smart');
        $this->assertEqual($search1['EvaluationSimple']['score'], 25);
        $this->assertEqual($search1['EvaluationSimple']['grp_event_id'], 999);
        $this->assertEqual($search2['EvaluationSimple']['comment'], 'Zion Au was also smart');
        $this->assertEqual($search2['EvaluationSimple']['score'], 50);
        $this->assertEqual($search2['EvaluationSimple']['grp_event_id'], 999);
        $this->assertEqual($searchEvalSubmission[0]['EvaluationSubmission']['event_id'], 999);
        $this->assertEqual($searchEvalSubmission[0]['EvaluationSubmission']['grp_event_id'], 999);
    }

    function testSaveRubricEvaluation()
    {

    }


    function saveNGetEvalutionRubricDetail()
    {

    }

    function testGetStudentViewRubricResultDetailReview()
    {

        $event = array('group_event_id' => 1);
        $result = $this->EvaluationComponentTest->getStudentViewRubricResultDetailReview($event, 3);

        $this->assertEqual($result[3][0]['EvaluationRubric']['id'], 1);
        $this->assertEqual($result[3][0]['EvaluationRubric']['evaluator'], 3);
        $this->assertEqual($result[3][0]['EvaluationRubric']['evaluatee'], 4);
        $this->assertEqual($result[3][0]['EvaluationRubric']['comment'], 'general comment1');
        $this->assertEqual($result[3][0]['EvaluationRubric']['score'], 15.00);
        $this->assertEqual($result[3][0]['EvaluationRubric']['details'][0]['EvaluationRubricDetail']['id'], 3);
        $this->assertEqual($result[3][0]['EvaluationRubric']['details'][0]['EvaluationRubricDetail']['evaluation_rubric_id'], 1);
        $this->assertEqual($result[3][0]['EvaluationRubric']['details'][0]['EvaluationRubricDetail']['grade'], 10.00);

        $result = $this->EvaluationComponentTest->getStudentViewRubricResultDetailReview(null, 3);
        $this->assertFalse($result);
        $result = $this->EvaluationComponentTest->getStudentViewRubricResultDetailReview($event, null);
        $this->assertFalse($result);
        $result = $this->EvaluationComponentTest->getStudentViewRubricResultDetailReview(null, null);
        $this->assertFalse($result);
    }

    function testFormatRubricEvaluationResultsMatrix()
    {

        $groupMembers = array(array('id' => 1), array('id' => 2));
        $evalResult = array(1 => array( array('EvaluationRubric' =>
            array('grade_release' => 1, 'comment_release' => 1, 'evaluatee' => 1,
                'details' => array(array('EvaluationRubricDetail' => array('criteria_number' => 1, 'grade' => 10)))))),
        2 => array( array('EvaluationRubric' =>
        array('grade_release' => 1, 'comment_release' => 1, 'evaluatee' => 2,
            'details' => array(array('EvaluationRubricDetail' => array('criteria_number' => 1, 'grade' => 20)))))));
        $result = $this->EvaluationComponentTest->formatRubricEvaluationResultsMatrix($groupMembers, $evalResult);
        $expected = array(1=>array("grade_released"=> 1, "comment_released"=>1, "rubric_criteria_ave"=>array(1=>10)),
            2=>array ("grade_released"=>1,"comment_released"=>1,"rubric_criteria_ave"=>array(1=>20)),
            "group_criteria_ave"=>array (1=>15));

        $this->assertEqual($expected, $result);

        $result = $this->EvaluationComponentTest->formatRubricEvaluationResultsMatrix(null, null);
        $this->assertFalse($result);

    }

    function testChangeRubricEvaluationGradeRelease()
    {

        $this->EvaluationComponentTest->changeRubricEvaluationGradeRelease(1, 3, 0);
        $result = $this->EvaluationRubric->find('all', array('conditions' => array('id' => 3)));
        $this->assertEqual($result[0]['EvaluationRubric']['grade_release'], 0);

        $this->EvaluationComponentTest->changeRubricEvaluationGradeRelease(1, 3, 1);
        $result = $this->EvaluationRubric->find('all', array('conditions' => array('id' => 3)));
        $this->assertEqual($result[0]['EvaluationRubric']['grade_release'], 1);

        $this->EvaluationComponentTest->changeRubricEvaluationGradeRelease(1, null, 0);
        $result = $this->EvaluationRubric->find('all', array('conditions' => array('id' => 3)));
        $this->assertEqual($result[0]['EvaluationRubric']['grade_release'], 1);
    }

    function testChangeRubricEvaluationCommentRelease()
    {

        $this->EvaluationComponentTest->changeRubricEvaluationCommentRelease(1, 3, 0);
        $result = $this->EvaluationRubric->find('all', array('conditions' => array('id' => 3)));
        $this->assertEqual($result[0]['EvaluationRubric']['comment_release'], 0);

        $this->EvaluationComponentTest->changeRubricEvaluationCommentRelease(1, 3, 1);
        $result = $this->EvaluationRubric->find('all', array('conditions' => array('id' => 3)));
        $this->assertEqual($result[0]['EvaluationRubric']['comment_release'], 1);

        $this->EvaluationComponentTest->changeRubricEvaluationCommentRelease(1, null, 0);
        $result = $this->EvaluationRubric->find('all', array('conditions' => array('id' => 3)));
        $this->assertEqual($result[0]['EvaluationRubric']['comment_release'], 1);
    }

    //TODO
    //Skip, uses Auth
    function testFormatRubricEvaluationResult()
    {

        $event= array('Event' => array('template_id' => 1, 'self_eval' => 1), 'group_id' => 1, 'group_event_id' => 1);
        $displayFormat = 'Detail';
        $studentView = 0;
        $currentUser = array ('id' => 1);
        //   $result = $this->EvaluationComponentTest->formatRubricEvaluationResult($event, $displayFormat, $studentView, $currentUser);
    }

    //TODO
    //Uses Auth
    function testLoadMixEvaluationDetail()
    {

    }

    //TODO
    function testSaveMixevalEvaluation()
    {

        $params = array('form'=> array('memberIDs' => array (3,16), 'group_event_id' => 1, 'event_id' => 1, '3'=>'Save This Section'),
            'data' => array('Evaluation' => array ('evaluator_id' => 16)));
        $this->EvaluationComponentTest->saveMixevalEvaluation($params);
        $now  ='hi';
        $search = $this->EvaluationMixeval->find('all', array('conditions' => array('evaluator' => 16)));
        $this->assertEqual($search[0]['EvaluationMixeval']['evaluator'], 16);
        $this->assertEqual($search[0]['EvaluationMixeval']['evaluatee'], 3);
        $this->assertEqual($search[0]['EvaluationMixeval']['comment_release'], 0);
        $this->assertEqual($search[0]['EvaluationMixeval']['grp_event_id'], 1);
        $this->assertEqual($search[0]['EvaluationMixeval']['event_id'], 1);
        $this->assertEqual($search[0]['EvaluationMixeval']['record_status'], 'A');

    }

    function testSaveNGetEvaluationMixevalDetail()
    {

        $evalMixevalId = 1;
        $mixeval = array('Mixeval' => array('total_question' => 2));
        $targetEvaluatee = 2;
        $form = array('data'=> array('Mixeval' => array('mixeval_question_type_id0' => '1', 'mixeval_question_type_id1' => '2')),
            'form' => array('selected_lom_2_0' => 1, '2criteria_points_0' => 30, 'response_text_2_1' => 'text'));
        $grade = $this->EvaluationComponentTest->saveNGetEvaluationMixevalDetail ($evalMixevalId, $mixeval, $targetEvaluatee, $form);
        $this->assertEqual($grade, 30);
        $grade = $this->EvaluationComponentTest->saveNGetEvaluationMixevalDetail (null, null, null, null);
        $this->assertFalse($grade);

    }

    function testGetMixevalResultDetail()
    {

        $groupEventId = 1;
        $groupMembers = array( 1=> array('id' => 1),2=> array ('id' =>2));
        $eval = $this->EvaluationComponentTest->getMixevalResultDetail($groupEventId, $groupMembers, array(1, 2));
        $expected = array( "scoreRecords"=>
            array(1=> array ( "grade_released"=> "0", "comment_released"=> "0", "mixeval_question_ave"=> array()),
                2=> array( 1 => "n/a", 2=> "n/a", "mixeval_question_ave"=> array()),
                "group_question_ave"=> array()), "allMembersCompleted"=> false,
                "inCompletedMembers"=> array ( 0=> array("id"=>1), 1=> array ("id"=>2)),
                "memberScoreSummary"=> array ( 1=> array("received_total_score"=> "10.000000", "received_ave_score"=> 10)),
                "evalResult"=> array ( 1=> array(0=>array("EvaluationMixeval"=> array(
                    "id"=>3, "evaluator"=> 1, "evaluatee"=> 1, "score"=> "10.00",
                    "comment_release"=> 0, "grade_release"=> 0, "grp_event_id"=> 1, "event_id"=> 1,
                    "record_status"=> "A", "creator_id"=> 0, "created"=> "0000-00-00 00:00:00",
                    "updater_id"=> NULL, "modified"=> NULL, "creator"=> NULL, "updater"=> NULL,
                    "details"=> array ()), "EvaluationMixevalDetail"=> array (),
                    "CreatorId"=> array (),  "UpdaterId"=> array ())), 2=> array ()));

        $this->assertEqual($eval, $expected);
        //  $eval = $this->EvaluationComponentTest->getMixevalResultDetail(null, $groupMembers);
        //  $expected = array("scoreRecords"=>false, "allMembersCompleted"=>true, "inCompletedMembers"=> array(), "memberScoreSummary"=> array(), "evalResult"=> array( ) );
        //  $this->assertEqual($eval, $expected);
        //  $eval = $this->EvaluationComponentTest->getMixevalResultDetail(null, null);
        //  $this->assertEqual($eval, $expected);
    }

    function testGetStudentViewMixevalResultDetailReview()
    {

        $event = array ('group_event_id' => 1);
        $eval = $this->EvaluationComponentTest->getStudentViewMixevalResultDetailReview($event, 1);

        $expected = array(
            1=>
            array (
                array(
                    "EvaluationMixeval"=> array(
                        "id"=> "3", "evaluator" => "1", "evaluatee" => "1",
                        "score" => "10.00", "comment_release"=> 0, "grade_release"=> 0,
                        "grp_event_id"=> 1, "event_id"=> 1, "record_status"=> "A",
                        "creator_id"=> 0, "created"=> "0000-00-00 00:00:00",
                        "updater_id"=> null, "modified"=> null, "creator"=> null, "updater"=> null,
                        "details"=>array()),

                    "EvaluationMixevalDetail"=> array(), "CreatorId"=> array(), "UpdaterId"=> array()
                )));
        $this->assertEqual($eval, $expected);

        $eval = $eval = $this->EvaluationComponentTest->getStudentViewMixevalResultDetailReview(null, 1);
        $this->assertFalse($eval);
        $eval = $eval = $this->EvaluationComponentTest->getStudentViewMixevalResultDetailReview(1, null);
        $this->assertFalse($eval);
    }
    
    function testFormatMixevalEvaluationResultsMatrix()
    {
    
    }


    function testChangeMixevalEvaluationGradeRelease()
    {



    }

    function testChangeMixevalEvaluationCommentRelease()
    {

        $this->EvaluationComponentTest->changeMixevalEvaluationCommentRelease(1, 1, 1);
        //     $survey =  $this->EvaluationMixeval->find('all', array('conditions' => array('grp_event_id' => 1)));
        //  var_dump($survey);

    }

    //function is not used anywhere

    function testFormatStudentViewOfSurveyEvaluationResult()
    {
        //   $survey = $this->EvaluationComponentTest->formatStudentViewOfSurveyEvaluationResult(1);
    }

    function testFormatSurveyEvaluationResult()
    {

        // $survey = $this->EvaluationComponentTest->formatSurveyEvaluationResult(1,1);
        // var_dump($survey);

    }

    function testFormatSurveyGroupEvaluationResult()
    {

        $survey = $this->EvaluationComponentTest->formatSurveyGroupEvaluationResult(1, 1);
        $expected = array(
            1 => array(
                'Question' => array(
                    'prompt' => 'Did you learn a lot from this course ?',
                    'type' => 'M',
                    'id' => 1,
                    'number' => 1,
                    'sq_id' => 1,
                    'Responses' => array(
                        'response_0' => array(
                            'response' => 'YES FOR Q1',
                            'id' => 1,
                            'count' =>0
                        ),
                        'response_1' => array(
                            'response' => 'NO FOR Q1',
                            'id' => 5,
                            'count' => 0
                        )
                    ),
                    'total_response' => 0
                )
            ),
            2 => array(
                'Question' => array(
                    'prompt' => 'What was the hardest part ?',
                    'type' => 'M',
                    'id' => 2,
                    'number' => 2,
                    'sq_id' => 2,
                    'Responses' => array(
                        'response_0' => array(
                            'response' => 'NO FOR Q2',
                            'id' => 2,
                            'count' =>0
                        )
                    ),
                    'total_response' => 0)),

            3 => array(
                'Question' => array(
                    'prompt' => 'Did u like the prof ?',
                    'type' => 'A',
                    'id' => 6,
                    'number' => 3,
                    'sq_id' => 6,
                    'Responses' => array(),
                )));
        $this->assertEqual($survey, $expected);

        $survey = $this->EvaluationComponentTest->formatSurveyGroupEvaluationResult(null, null);
        //     $this->assertFalse($survey);
        $survey = $this->EvaluationComponentTest->formatSurveyGroupEvaluationResult(999, 999);
        //     $this->assertFalse($survey);

    }

    function testFormatSurveyEvaluationSummary()
    {

        $survey = $this->EvaluationComponentTest->formatSurveyEvaluationSummary(1);
        $expected = $this->setUpSurveyTestData();

        $this->assertEqual($expected, $survey);
        $survey = $this->EvaluationComponentTest->formatSurveyEvaluationSummary(999);
        // $this->assertFalse($survey);
        $survey = $this->EvaluationComponentTest->formatSurveyEvaluationSummary(null);
        // $this->assertFalse($survey);

    }
   /*function testFormatStudentViewOfSimpleEvaluationResult()
{
      $eventInput = $this->Event->find('first', array('conditions' => array('Event.id' => 1)));
      $result = $this->EvaluationComponentTest->formatStudentViewOfSimpleEvaluationResult($eventInput);
      var_dump($return);
   }
    */
    function setUpSimpleEvaluationTestData()
    {
        $params = array();
        $params['form']['memberIDs'][0] = 1;
        $params['form']['memberIDs'][1] = 2;
        $params['form']['points'][0] = 25;
        $params['form']['points'][1] = 50;
        $params['form']['comments'][0] = "Kevin Luk was smart";
        $params['form']['comments'][1] = "Zion Au was also smart";
        $params['data']['Evaluation']['evaluator_id'] = 1;
        $params['data']['Evaluation']['evaluator_id'] = 2;
        $params['form']['evaluateeCount'] = 2;

        $groupEvent = array();
        $groupEvent['GroupEvent']['id'] = 999;
        $groupEvent['GroupEvent']['event_id'] = 999;
        $groupEvent['GroupEvent']['group_id'] = 999;

        $return = array($params, $groupEvent);
        return $return;
    }

    function setUpSurveyTestData()
    {

        $expected = array(
            1 => array(
                'Question' => array(
                    'prompt' => 'Did you learn a lot from this course ?',
                    'type' => 'M',
                    'id' => 1,
                    'number' => 1,
                    'sq_id' => 1,
                    'Responses' => array('response_0' =>
                    array('response' => 'YES FOR Q1', 'id' => 1, 'count' =>1),
                        'response_1' =>
                        array('response' => 'NO FOR Q1', 'id' => 5, 'count' => 0)),
                            'total_response' => 1)),

            2 => array(
                'Question' => array(
                    'prompt' => 'What was the hardest part ?',
                    'type' => 'M',
                    'id' => 2,
                    'number' => 2,
                    'sq_id' => 2,
                    'Responses' => array('response_0' =>
                    array('response' => 'NO FOR Q2', 'id' => 2, 'count' =>1)),
                        'total_response' => 1)),

            3 => array(
                'Question' => array(
                    'prompt' => 'Did u like the prof ?',
                    'type' => 'A',
                    'id' => 6,
                    'number' => 3,
                    'sq_id' => 6,
                    'Responses' => array('response_1' =>
                    array('response_text' => null, 'user_name' => 'lastname, name')),
                    )));

        return $expected;
    }
}
