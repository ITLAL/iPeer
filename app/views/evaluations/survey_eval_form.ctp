<div class='surveyquestions'>
<?php
if (!empty($event['Event']['description'])) { ?>
<h2><?php echo _t('Description') ?></h2>
<div id='description'><?php echo _t($event['Event']['description']) ?></div>
<?php }
echo '<h2>' . $event['Event']['title'] . '</h2>';
echo $form->create('SurveyInput', 
    array('url' => $html->url("makeEvaluation/$eventId")));

foreach ($questions as $i => $q) {
    echo $html->div('prompt', $i + 1 .' '. $q['Question']['prompt']);
    if ($q['Question']['type'] == 'M') {
        echo $form->input(
            "$i.response_id",
            array(
                'type' => 'radio',
                'options' => $q['ResponseOptions'], 
                'separator' => '<br />',
                'legend' => false
            )
        );
    } else if ($q['Question']['type'] == 'C') {
        echo $form->input(
            "$i.response_id",
            array(
                'options' => $q['ResponseOptions'], 
                'multiple' => 'checkbox',
                'label' => false
            )
        );
    } else if ($q['Question']['type'] == 'S') {
        echo $form->text("$i.response_text");
    } else if ($q['Question']['type'] == 'L') {
        echo $form->textarea("$i.response_text");
    }
    echo $form->hidden("$i.event_id", array('value' => $eventId));
    echo $form->hidden("$i.user_id", array('value' => $userId));
    echo $form->hidden("$i.question_id",array('value' => $q['Question']['id']));
}

echo $form->end('Submit');
?>
</div>
