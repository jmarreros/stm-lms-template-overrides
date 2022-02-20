
<?php

// Funciones para el reseteo de la unidad

function update_course_user($user_id, $course_id, $firts_lesson_unit){
	global $wpdb;
	$sql = "UPDATE {$wpdb->prefix}stm_lms_user_courses
			SET current_lesson_id = {$firts_lesson_unit}
			WHERE user_id = {$user_id} AND course_id = {$course_id}";
	$wpdb->query($sql);

	$sql = "DELETE FROM {$wpdb->prefix}usermeta
			WHERE user_id = {$user_id} AND meta_key like 'stm_lms_course_started_{$course_id}%'";
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


