<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} //Exit if accessed directly

// Modificado
// ==========
$count_retake = 0;
$retake_unit = false;
$firts_lesson_unit = 0;

$user_id = get_current_user_id();
$course_id = $post_id;
$quizz_id = $item_id;

$meka_key = 'stm_custom_retake_'.$course_id.'_'.$quizz_id;
$count_retake = intval(get_user_meta($user_id, $meka_key, true));

if (isset($_GET['retake'])){

	// Always delete current quiz
	delete_quizz($user_id, $course_id, $quizz_id);

	if ( $count_retake < 2 ) {
		$count_retake++;
	} else {
		$ids_content = get_content_section($course_id, $quizz_id);

		foreach($ids_content as $id){
			switch ( get_post_type($id) ) {
				case 'stm-quizzes':
					delete_quizz($user_id, $course_id, $id);
					break;
				case 'stm-lessons':
					delete_lesson($user_id, $course_id, $id);
					break;
			}
		}

		$firts_lesson_unit = $ids_content[count($ids_content)-1];

		$count_retake = 0;

		update_course($user_id, $course_id, $firts_lesson_unit);
	}

	$retake_unit = (intval($_GET['retake']) === 3);

	update_user_meta($user_id, $meka_key, $count_retake);
}

function update_course($user_id, $course_id, $firts_lesson_unit){
	global $wpdb;
	$sql = "UPDATE {$wpdb->prefix}stm_lms_user_courses
			SET current_lesson_id = {$firts_lesson_unit}
			WHERE user_id = {$user_id} AND course_id = {$course_id}";
	$wpdb->query($sql);

}

function delete_quizz($user_id, $course_id, $quizz_id){
	global $wpdb;
	// Remove answers
	$sql = "DELETE FROM {$wpdb->prefix}stm_lms_user_answers
			WHERE user_id = {$user_id} AND course_id = {$course_id} AND quiz_id = {$quizz_id}";
	$wpdb->query($sql);

	// Remove Quizz
	$sql = "DELETE FROM {$wpdb->prefix}stm_lms_user_quizzes
			WHERE user_id = {$user_id} AND course_id = {$course_id} AND quiz_id = {$quizz_id}";
	$wpdb->query($sql);
}

function delete_lesson($user_id, $course_id, $lesson_id){
	global $wpdb;
	// Remove Lesson
	$sql = "DELETE FROM {$wpdb->prefix}stm_lms_user_lessons
			WHERE user_id = {$user_id} AND course_id = {$course_id} AND lesson_id = {$lesson_id}";

	$wpdb->query($sql);
}

// Get lessons and quizzes for a section (unit)
function get_content_section( $course_id, $quizz_id ){

	$curriculum = get_post_meta($course_id, 'curriculum', true);
	$curriculum = explode(',' , $curriculum);
	$key = array_search($quizz_id, $curriculum);

	$content = [];
	for ($i = $key + 1 ; $i < count($curriculum) ; $i++) {
		if ( ! is_numeric( $curriculum[$i] ) ) break;
		$content[] = $curriculum[$i];
	}

	for ($i = $key - 1 ; $i > 0 ; $i--) {
		if ( ! is_numeric( $curriculum[$i] ) ) break;
		$content[] = $curriculum[$i];
	}

	return $content;
}

// ==========

?>

<?php
/**
 * @var $post_id
 * @var $item_id
 */

stm_lms_register_style('quiz');
stm_lms_register_script('quiz');
wp_localize_script('stm-lms-quiz', 'quiz_data_vars', compact('post_id', 'item_id'));
wp_add_inline_script( 'stm-lms-quiz',
    "var stm_lms_lesson_id = {$item_id}; var stm_lms_course_id = {$post_id}" );
wp_localize_script( 'stm-lms-quiz', 'stm_lms_quiz_vars', array(
	'prevent_submit' => apply_filters('stm_lms_prevent_quiz_submit', 0),
	'confirmation' => esc_html__( 'Once you submit, you will no longer be able to change your answers. Are you sure you want to submit the quiz?', 'masterstudy-lms-learning-management-system' )
) );

$current_screen        = get_queried_object();
$source                = ( ! empty( $current_screen ) ) ? $current_screen->ID : '';
if ( empty( $post_id ) ) {
	$post_id = $source;
}

if ( ! empty( $item_id ) ):

	/*Start Quiz*/

	$q = new WP_Query( array(
		'posts_per_page' => 1,
		'post_type' => 'stm-quizzes',
		'post__in' => array( $item_id )
	) );

	if ( $q->have_posts() ):
		$quiz_meta = STM_LMS_Helpers::parse_meta_field( $item_id );
		$user          = apply_filters( 'user_answers__user_id', STM_LMS_User::get_current_user(), $source );
		$last_quiz     = STM_LMS_Helpers::simplify_db_array( stm_lms_get_user_last_quiz( $user['id'], $item_id, array( 'progress' ) ) );
		$progress      = ( ! empty( $last_quiz['progress'] ) ) ? $last_quiz['progress'] : 0;
		$passing_grade = ( ! empty( $quiz_meta['passing_grade'] ) ) ? $quiz_meta['passing_grade'] : 0;
		$passed        = $progress >= $passing_grade && ! empty( $progress );

		$classes = array();
		if ( $passed ) {
			$classes[] = 'passed';
		}
		if ( ! empty( $last_quiz ) and ! $passed ) {
			$classes[] = 'not-passed';
		}
		if ( STM_LMS_Quiz::show_answers( $item_id ) ) {
			$classes[] = 'show_answers';
		}
		if ( ! empty( $last_quiz ) ) {
			$classes[] = 'retaking';
		}

		$duration  = STM_LMS_Quiz::get_quiz_duration( $item_id );
		$classes[] = ( empty( $duration ) ) ? 'no-timer' : 'has-timer';

		?>
		<div class="stm-lms-course__lesson-content <?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php if ( ! $retake_unit ) : ?>
				<?php while ( $q->have_posts() ): $q->the_post();
					$meta_quiz = STM_LMS_Helpers::parse_meta_field( get_the_ID() ); ?>


					<div class="stm-lms-course__lesson-html_content">
						<?php
						ob_start();
						the_content();
						$content = ob_get_clean();
						$content = str_replace( '../../', site_url() . '/', $content );
						echo stm_lms_filtered_output( $content );
						?>
					</div>

					<?php if ( ! empty( $meta_quiz['questions'] ) ) {
						STM_LMS_Templates::show_lms_template(
							'quiz/quiz',
							array_merge( $meta_quiz, compact( 'post_id', 'item_id', 'last_quiz', 'source' ) )
						);
					} ?>

					<?php
						// Modificado
						STM_LMS_Templates::show_lms_template(
						'quiz/results',
						compact( 'quiz_meta', 'last_quiz', 'progress', 'passing_grade', 'passed', 'item_id', 'count_retake' )
					);
					?>

				<?php endwhile; ?>
			<?php else: ?>
				<?php
					global $wp;
					$current_url = home_url( $wp->request );
					$new_url = str_replace($item_id, '', $current_url);
				?>
				<a href="<?= $new_url ?>" class="btn btn-default">Retomar Unidad</a>
			<?php endif; ?>
		</div>

	<?php endif; ?>
	<?php wp_reset_postdata(); ?>
<?php endif;