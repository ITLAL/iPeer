<script type="text/javascript">
var numQues = <?php echo count($data['RubricsCriteria']) ?>;
function saveButtonVal(userId) {
    var complete = true;
    for (var i=1; i <= numQues; i++) {
        var value = jQuery('input[name='+userId+'criteria_points_'+i+']:checked').val();
        if (value == null) {
            jQuery('#'+userId+'criteria'+i).attr('color', 'red');
            complete = false;
        } else {
            jQuery('#'+userId+'criteria'+i).removeAttr('color');
        }
    }
    if (complete) {
        jQuery('#'+userId+'likert').hide();
    } else {
        jQuery('#'+userId+'likert').show();
    }
    return complete;
}
</script>
<?php echo $html->script('ricobase')?>
<?php echo $html->script('ricoeffects')?>
<?php echo $html->script('ricoanimation')?>
<?php echo $html->script('ricopanelcontainer')?>
<?php echo $html->script('ricoaccordion')?>
<form name="evalForm" id="evalForm" method="POST" action="<?php echo $html->url('makeEvaluation/'.$event['Event']['id'].'/'.$event['Group']['id']) ?>">
<?php echo empty($params['data']['Evaluation']['id']) ? null : $html->hidden('Evaluation/id'); ?>
<input type="hidden" name="event_id" value="<?php echo $event['Event']['id']?>"/>
<input type="hidden" name="group_id" value="<?php echo $event['Group']['id']?>"/>
<input type="hidden" name="group_event_id" value="<?php echo $event['GroupEvent']['id']?>"/>
<input type="hidden" name="course_id" value="<?php echo $event['Event']['course_id']?>"/>
<input type="hidden" name="rubric_id" value="<?php echo $viewData['id']?>"/>
<input type="hidden" name="data[Evaluation][evaluator_id]" value="<?php echo $userId ?>"/>
<input type="hidden" name="evaluateeCount" value="<?php echo $evaluateeCount?>"/>

<table class="standardtable">
    <tr>
        <th colspan="4" align="center"><?php __('Evaluation Event Detail')?></th>
    </tr>
    <tr>
        <td width="10%"><?php __('Evaluator')?>:</td>
        <td width="25%"><?php echo $fullName ?></td>
        <td width="10%"><?php __('Evaluating')?>:</td>
        <td width="25%"><?php echo $event['Group']['group_name'] ?></td>
    </tr>
    <tr>
        <td><?php __('Event Name')?>:</td>
        <td><?php echo $event['Event']['title'] ?></td>
        <td><?php __('Due Date')?>:</td>
        <td><?php echo Toolkit::formatDate($event['Event']['due_date']) ?></td>
    </tr>
    <tr>
        <td><?php __('Description')?>:</td>
        <td colspan="3"><?php echo $event['Event']['description'] ?></td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: left;">
            <span class="instruction-icon"><?php __(' Instructions')?>:</span>
            <ul class="instructions">
            <li><?php __('Click <font color ="#FF6666"><i>EACH</i></font> of your peer\'s name to rate his/her performance.')?></li>
            <li><?php __('Enter Comments')?> (<?php echo $event['Event']['com_req']? '<font color="red">'.__('Required', true).'</font>' : __('Optional', true) ;?>).</li>
            <li><?php __('Press "Save This Section" to save the evaluation for each group member.')?></li>
            <li><?php __('Press "Submit to Complete the Evaluation" to submit your evaluation to all peers.')?> </li>
            <li><?php __('<i>NOTE:</i> You can click the "Submit to Complete the Evaluation" button only <font color ="#FF6666">AFTER</font> all evaluations are completed.')?></li>
            <?php $releaseEnd = !isset($event['Event']['release_date_end']) ? '<i>'._t("Evaluation's release end date").'</i>' : Toolkit::formatDate($event['Event']['release_date_end']); ?>
            <li><?php echo _t('The evaluation can be repeatedly submitted until ').$releaseEnd.'.'?></li>
            </ul>

            <div style="text-align:left; margin-left:3em;"><a href="#" onClick="javascript:$('penalty').toggle();return false;">( <?php __('Show/Hide late penalty policy')?> )</a></div>
            <div id ="penalty" style ="border:1px solid red; margin: 0.5em 0 0 3em; width: 450px; padding:0.5em; color:darkred; display:none">
                <?php if (!empty($penalty)) {
                    foreach ($penalty as $day) {
                        $mult = ($day['Penalty']['days_late']>1)?'s':'';
                        echo $day['Penalty']['days_late'].' day'.$mult.' late: '.$day['Penalty']['percent_penalty'].'% deduction. </br>';
                    }
                    echo $penaltyFinal['Penalty']['percent_penalty'].'% is deducted afterwards.';
                } else {
                    echo 'No penalty is specified for this evaluation.';
                }
                ?>
            </div>
        </td>
    </tr>
</table>

<table class="standardtable">
    <tr>
        <td>
        <div id="accordion">
        <?php foreach($groupMembers as $row): $user = $row['User'];?>
            <input type="hidden" name="memberIDs[]" value="<?php echo $user['id']?>"/>
            <div id="panel<?php echo $user['id']?>" class="panelName">
                <div id="panel<?php echo $user['id']?>Header" class="panelheader">
                <?php echo $user['first_name'].' '.$user['last_name'];?>
                <?php if (isset($row['User']['Evaluation'])): ?>
                    <font color="#259500"> ( Saved )</font>
                <?php else: ?>
                    <blink><font color="#FF6666"> - </font></blink><?php __('(click to expand)')?>
                <?php endif; ?>
                </div>
                <div style="height: 200px;" id="panel1Content" class="panelContent">
                    <br>
                    <?php
                    $params = array('controller'=>'rubrics', $viewData , 'evaluate'=>1, 'user'=>$user, 'event'=>$event);
                    echo $this->element('rubrics/ajax_rubric_view', $params);
                    ?>
                    <table align="center" width=100% >
                    <tr>
                        <td align="center">
                            <?php if (!isset($preview)): ?>
                                <?php echo $form->submit(__('Save This Section', true), array('name' => $user['id'], 'div' => 'saveThisSection')); ?>
                            <?php else: ?>
                                <?php echo $form->submit(__('Save This Section', true), array('disabled' => true, 'div' => 'saveThisSection')); ?>
                                <div style='color: red'><?php __('This is a preview. All submissions are disabled.')?></div>
                            <?php endif; ?>
                            <div style='color: red' id='<?php echo $user['id']?>likert'><?php __('Please complete all the questions marked red before saving.')?></div>
                            <div style='color: red'><?php __('Make sure you save this section before moving on to the other ones!')?></div>
                        </td>
                    </tr>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        </td>
    </tr>
</table>
</form>
<table class="standardtable">
    <tr><td colspan="4" align="center">
<form name="submitForm" id="submitForm" method="POST" action="<?php echo $html->url('completeEvaluationRubric') ?>">
    <input type="hidden" name="event_id" value="<?php echo $event['Event']['id']?>"/>
    <input type="hidden" name="group_id" value="<?php echo $event['Group']['id']?>"/>
    <input type="hidden" name="group_event_id" value="<?php echo $event['GroupEvent']['id']?>"/>
    <input type="hidden" name="course_id" value="<?php echo $event['Event']['course_id']?>"/>
    <input type="hidden" name="rubric_id" value="<?php echo $viewData['id']?>"/>
    <input type="hidden" name="data[Evaluation][evaluator_id]" value="<?php echo User::get('id')?>"/>
    <input type="hidden" name="evaluateeCount" value="<?php echo $evaluateeCount?>"/>
    <?php
    if ($allDone && !$comReq && !isset($preview)) {
        echo $form->submit(__('Submit to Complete the Evaluation', true), array('div'=>'submitComplete'));
    } else {
        echo $form->submit(__('Submit to Complete the Evaluation', true), array('disabled'=>'true','div'=>'submitComplete')); echo "<br />";
        echo isset($preview) ? "<div style='color: red'>".__('This is a preview. All submissions are disabled.', true).'</div>' : "";
        echo !$allDone ? "<div style='color: red'>".__("Please complete the questions for all group members, pressing 'Save This Section' button for each one.", true).'</div>' : "";
        echo $comReq ? "<div style='color: red'>".__('Please enter all the comments for all the group members before submitting.', true).'</div>' : "";
    }
    ?>
</form></td></tr>
</table>
<script type="text/javascript">
new Rico.Accordion( 'accordion',
    {panelHeight: 600,
    hoverClass: 'mdHover',
    selectedClass: 'mdSelected',
    clickedClass: 'mdClicked',
    unselectedClass: 'panelheader'});
var userIds = [<?php echo $userIds ?>];
jQuery.each(userIds, function(index, userId) {
    jQuery('#'+userId+'likert').hide();
});
</script>
