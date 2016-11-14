<?php
/* Short and sweet */
define('WP_USE_THEMES', false);
		// @obsolete require('../../../wp-blog-header.php');
    require_once("../../../wp-config.php");
    $wp->init(); $wp->parse_request(); $wp->query_posts();
    $wp->register_globals(); $wp->send_headers();

header("HTTP/1.1 200 OK");

// create an object called 'postresponse'
class postResponse {

}

$postresponse = new postResponse();

if ( $_POST['form_action'] == "delete" && $_POST['post_id'] != "" ) {
	$post_id = intval( $_POST['post_id'] );
	wp_trash_post( $post_id );
	if( get_post_status( $_POST['post_id'] ) == 'trash'){
		$postresponse->success = "true";
		$postresponse->message = 'Submission deleted.';
	} else {
		$postresponse->success = "false";
		$postresponse->message = 'There was an unexpected error. You must make changes to this submission in the Wordpress Control Panel.';
	}
} else if ( $_POST['form_action'] == "publish" && $_POST['post_id'] != "" ) {
	$post_id = intval( $_POST['post_id'] );
	$status = 'publish';
	$post = array( 'ID' => $post_id, 'post_status' => $status );
	wp_update_post($post);
	$postresponse->success = "true";
	$postresponse->message = 'Submission is now published.';
} else if ( $_POST['form_action'] == "unpublish" && $_POST['post_id'] != "" ) {
	$post_id = intval( $_POST['post_id'] );
	$status = 'draft';
	$post = array( 'ID' => $post_id, 'post_status' => $status );
	wp_update_post($post);
	$postresponse->success = "true";
	$postresponse->message = 'Submission marked as unpublished draft.';
} else if ( $_POST['form_action'] == "modify" && $_POST['new_value'] != "" && $_POST['post_id'] != "" ) {
	// Change post status to finalist, semifinalist
	wp_set_object_terms( $_POST['post_id'], $_POST['new_value'], "art_status", FALSE );
	$postresponse->newcats = $_POST['new_value'];
	$postresponse->post_id = $_POST['post_id'];
	$postresponse->success = "true";
	$postresponse->message = 'Submission marked as one of the '.$_POST['new_value'].'.';
} else if ( $_POST['form_action'] == "remove_art_status" && $_POST['new_value'] != "" && $_POST['post_id'] != "" ) {
	// Change post status back to New Submission
	wp_set_object_terms( $_POST['post_id'], $_POST['new_value'], "art_status", FALSE );
	$postresponse->newcats = $_POST['new_value'];
	$postresponse->post_id = $_POST['post_id'];
	$postresponse->success = "true";
	$postresponse->message = 'Submission no longer marked as one of the '.$_POST['target_field'].'.';
} else if ( $_POST['form_action'] == "update_comments" && $_POST['post_id'] != "" ) {
	// Update comments
	if( update_post_meta( $_POST['post_id'], "comments", $_POST['new_value'] ) ) {
		$postresponse->newcomments = $_POST['new_value'];
		$postresponse->post_id = $_POST['post_id'];
		$postresponse->success = "true";
		$postresponse->message = 'Comments successfully updated.';
	} else if ( get_post_meta( $_POST['post_id'], "comments", TRUE ) == $_POST['new_value'] ) {
		$postresponse->newcomments = $_POST['new_value'];
		$postresponse->post_id = $_POST['post_id'];
		$postresponse->success = "true";
		$postresponse->message = 'Comments successfully updated.';
	} else {
		$postresponse->success = "false";
		$postresponse->message = 'Error updating comments.';
	}
}

// Prints the post response into JSON
echo json_encode($postresponse);

?>
