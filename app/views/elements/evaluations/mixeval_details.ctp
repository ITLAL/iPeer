<?php
$addOn = '';
if (!$gradeReleased && !$commentReleased && $details) {
    $addOn = ' - <font color="red">'._t(' Comments/Grades Not Released Yet').'</font>';
} else if (!$gradeReleased) {
    $addOn = ' - <font color="red">'._t(' Grades Not Released Yet').'</font>';
} else if (!$commentReleased && $details) {
    $addOn = ' - <font color="red">'._t(' Comments Not Released Yet').'</font>';
}
$header = _t($title).$addOn;

echo $html->tag('h2', $header);
$qnum = 1;
if ($details) {
    foreach ($questions as $ques) {
        if ($ques['self_eval'] == $peer_eval) {
            continue; // skip questions not in the desired section
        }
        $required = (!$ques['required']) ? '' :
            $html->tag('span', '*', array('class' => 'required orangered'));
        echo $html->tag('h3', "$qnum. $ques[title] $required");
        $type = $ques['mixeval_question_type_id'];
        $multiplier = $ques['multiplier'];
        $scale = count($ques['MixevalQuestionDesc']);
        $descriptors = Set::combine($ques['MixevalQuestionDesc'], '{n}.scale_level', '{n}.descriptor');
        if (isset($ques['Submissions'])) {
            echo '<ul>';
            foreach ($ques['Submissions'] as $num => $sub) {
                if (in_array($type, array(1, 4)) && !$gradeReleased) {
                    echo '<li>'._t('Grades Not Released Yet').'</li>';
                    break;
                } else if (in_array($type, array(2, 3)) && !$commentReleased) {
                    echo '<li>'._t('Comments Not Released Yet').'</li>';
                    break;
                }
                $name = '';
                if (isset($names)) {
                    $class = in_array($sub['evaluator'], $notInGroup) ? 'blue' : 'name';
                    $name = '<label class='.$class.'>'.$names[$sub['evaluator']].':</label>';
                }
                if ($type == '1') {
                    $step = $multiplier / ($scale - $zero_mark);
                    $start = $zero_mark ? 0 : $step;
                    $marks = array_map('number_format', range($start, $multiplier, $step),
                        array_fill(0, $scale, 2));
                    $options = array_combine($marks, $marks);
                    $grade = '<label class="grade">'._t('Grade: ');
                    $grade .= $sub['grade'].' / '.$multiplier.'</label>';
                    $grade .= (empty($descriptors[$sub['selected_lom']])) ? '' :
                        '<label class="desc">('.$descriptors[$sub['selected_lom']].')</label>';
                    echo $form->input('ques_'.$qnum.'_'.$num.'_'.$evaluatee, array(
                        'type' => 'radio',
                        'options' => $options,
                        'disabled' => true,
                        'default' => $sub['grade'],
                        'before' => '<li>'.$name,
                        'after' => $grade.'</li>'
                    ));
                } else if ($type == '4') {
                    echo '<li>'.$name.$sub['grade'].'</li>';
                } else {
                    echo '<li>'.$name.$sub['question_comment'].'</li>';
                }
            }
            echo '</ul>';
        } else {
            echo '<ul><li>N/A</li></ul>';
        }
        $qnum++;
    }
    echo '<br>';
} else {
    foreach ($questions as $qnum => $ques) {
        $typeId = $ques['mixeval_question_type_id'];
        if ($typeId == '1' || $typeId == '4') {
            $required = (!$ques['required']) ? '' :
                $html->tag('span', '*', array('class' => 'required orangered'));
            echo $html->tag('h3', "$qnum. $ques[title] $required");
            if (isset($ques['Submissions'])) {
                echo '<ul>';
                if (!$gradeReleased) {
                    echo '<li>'._t('Grades Not Released Yet').'</li></ul>';
                    continue;
                }
                $grades = Set::extract('/grade', $ques['Submissions']);
                $average = array_sum($grades) / count($grades);
                $text = $ques['self_eval'] ?  'Grade: ' : 'Average: ';
                echo '<li>'.$text.number_format($average, 2).' / '.$ques['multiplier'].'</li></ul>';
            } else {
                echo '<ul><li>N/A</li></ul>';
            }
        }
        $qnum++; // increments whether the question has a grade or not - to keep the question numbers constant
    }
    echo '<br>';
}
?>
