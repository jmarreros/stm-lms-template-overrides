<?php
/**
 * @var $quiz_meta
 * @var $last_quiz
 * @var $progress
 * @var $passing_grade
 * @var $passed
 * @var $item_id
 */

// Modificado
global $wp;
$current_url = home_url( $wp->request );
?>

<div class="stm-lms-quiz__result__overlay"></div>
<div class="stm-lms-quiz__result">
    <h2><?php esc_html_e('Result', 'masterstudy-lms-learning-management-system'); ?></h2>
    <h4 class="stm-lms-quiz__result_number">
        <span><?php echo round($progress, 1); ?>%</span>
    </h4>
    <div class="stm-lms-quiz__result_actions">
        <?php if ( $progress < $passing_grade ) { ?>

        <div class="another-chance"><strong>Tienes otra oportunidad</strong></div>
        <a href="<?= $current_url ?>?retake=1" class="btn btn-default btn-retake">
            <?php esc_html_e('Re-take Quiz', 'masterstudy-lms-learning-management-system'); ?>
        </a>
        <?php } ?>

        <?php if(STM_LMS_Quiz::can_watch_answers($item_id)): ?>
            <a href="<?php echo esc_url(STM_LMS_Quiz::answers_url()); ?>" class="btn btn-default">
                <?php esc_html_e('Show answers', 'masterstudy-lms-learning-management-system'); ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="stm-lms-quiz__result_passing_grade">
        <?php printf(esc_html__('Passing grade - %s%%', 'masterstudy-lms-learning-management-system'), $passing_grade); ?>
    </div>
</div>