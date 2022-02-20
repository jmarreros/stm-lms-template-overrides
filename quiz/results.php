<?php
/**
 * @var $quiz_meta
 * @var $last_quiz
 * @var $progress
 * @var $passing_grade
 * @var $passed
 * @var $item_id
 *
 * @var $count_retake
 * @var $course_id
 * @var $quizz_id
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
    <?php if ( $count_retake >= 0 && $count_retake < 2): ?>
        <h5>Veces: <?= $count_retake + 1 ?>/2</h5>
    <?php endif; ?>

    <div class="stm-lms-quiz__result_actions">
        <?php if ( $progress < $passing_grade ) { ?>

            <?php if ( $count_retake == 0 ): ?>
                <div class="another-chance"><span>Tienes otra oportunidad<span></div>
                <a href="<?= $current_url ?>?retake=1" class="btn btn-default btn-retake">
                    <?php esc_html_e('Re-take Quiz', 'masterstudy-lms-learning-management-system'); ?>
                </a>
            <?php endif; ?>

            <?php if ( $count_retake >= 1 ): ?>
                <a href="#" class="btn btn-default btn-retake btn-retake-unit">
                    Tienes que retornar al inicio de la unidad
                </a>
            <?php endif; ?>


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

<script>

(function( $ ) {
	'use strict';
    $( document ).ajaxComplete(function() {

        if ( $('.btn-close-quiz-modal-results').length ){
            $('.another-chance').hide();
            $('.btn-retake').hide();
        }
    });


    $('.btn-retake-unit').click(function(e){
        e.preventDefault();

		$.ajax({
			url : stm_lms_ajaxurl,
			type: 'post',
			data: {
				action : 'dcms_reset_unit',
                course_id: <?= $course_id ?>,
                quizz_id: <?= $quizz_id ?>,
                current_url: "<?= $current_url ?>"
			},
			success: function(url_redirect){
                window.location.replace(url_redirect);
			}
		});
    });


})( jQuery );

</script>