<?php global $user_level;

if ($user_level >= 10) {

?>

<link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">

<style>

.row {
	font-family: superclarendon-n7, superclarendon, serif;
	color: rgb(0, 63, 136);
	margin-left: 0px;
	margin-right: 0px;
}

.button {
	padding:10px 20px;
	display:inline-block;
	margin:5px;
	border-radius:5px;
	color:#fff;
	background:rgb(0, 63, 136);
	border:0;
}

.unmark {
	background: #c20404;
}

.art-submissions-title {
	color: color: rgb(0, 63, 136);
	margin-top: 0px;
	display: inline;
	font-size: 14px;
}

.art-submissions-category {
	text-transform: capitalize;
}

.art-submissions-image {
min-height: 200px;
min-width: 200px;
height: 60rem;
max-height: 600px;
background-size: contain;
background-repeat: no-repeat;
background-position: center center;
margin-top:10px;
}

#numberofsubmissions, .art-submissions-index-number {
	display:none;
}

.art-submissions-index-number { margin-top:10px; }

#next-submission:hover, #prev-submission:hover, #next-set:hover, #prev-set:hover {
	cursor:pointer;
}

.col-md-1 img {
margin-top: 255px;
}
.art-submission {
	border:5px solid #c20404;
	padding:10px;
}
.art-submission label {
margin: 5px 5px 5px 0;
color: #c20404 !important;
}

.filter {
padding-top: 30px;
}

/* Hides single submission view when you're in grid view, and hides grid view when you're in single view */
.grid-view .singleton, .single-view .griddleton {
	display:none;
}

.grid-view-submission {
display: inline-block;
margin: 25px 25px 0px 25px;
}

.grid-image-container img {
	height:150px;
	width:150px;
}

.grid-view-submission-buttons {
border: 1px solid #ddd;
padding: 4px 0;
}

.switchview {
	display:none;
	position:absolute;
	top:5px;
	right:5px;
	z-index:2;
}

.grid-image-overlay {
	display:none;
	background:rgba(0,0,0,0.4);
	position:absolute;
	top:0px;
	left:0px;
	height:100%;
	width:100%;
}

.grid-image-overlay h5 {
	color:#fff;
}

.grid-image-container {
	display:block;
	position:relative;
}

.grid-image-container:hover > .grid-image-overlay {
	display:block;
}

.comments {
display: block;
width: 100%;
height: 100px;
}

</style>

<?php

$whichview = '';

if ( $_SERVER['QUERY_STRING'] != "" ){
	parse_str( $_SERVER['QUERY_STRING'] );
	if ( isset($filter) && $filter != "all" ) {
		$taxqueryarray = array(
			array(
				'taxonomy' => 'art_status',
				'field'    => 'slug',
				'terms'    => $filter,
			),
		);
	}
	if ( isset($category) ) {
		if( $category != 'all' && $category != 'Photo / Digital Media' ){
			$catsearcharray = array(
				'key'     => 'art_type',
				'value'   => $category,
				'compare' => 'IN',
				'relation' => 'OR'
			);
		}
	}
	if ( isset($view) ) {
		$whichview = $view;
	}
	if ( isset($artYear) ) {
		$datequeryarray = array(
			array(
				'after'     => array(
					'year'  => $artYear,
					'month' => 1,
					'day'   => 0,
				),
				'before'    => array(
					'year'  => $artYear,
					'month' => 12,
					'day'   => 32,
				),
				'inclusive' => true,
			),
		);
	}
} else {
	$taxqueryarray = array(
		array(
			'taxonomy' => 'art_status',
			'field'    => 'slug',
			'terms'    => array('new-submission','semifinalists','finalists'),
		),
	);
}

$args = array(
	'post_type' => 'pbrart',
	'post_status' => array('publish','draft'),
	'posts_per_page' => -1,
  	'orderby' => 'date',
  	'order' => 'DESC',
	'date_query' => array(
		array(
			'after'     => array(
				'year'  => 2015,
				'month' => 1,
				'day'   => 0,
			),
			'before'    => array(
				'year'  => 2015,
				'month' => 12,
				'day'   => 32,
			),
			'inclusive' => true,
		),
	),
);


// Adds the category filter if the user has selected a single category
if ( isset($catsearcharray) ) {
	$args['meta_query'][] = $catsearcharray;
}

if ( isset($taxqueryarray) ) {
	$args['tax_query'] = $taxqueryarray;
}

if ( isset($datequeryarray) ) {
	$args['date_query'] = $datequeryarray;
}

$the_query = new WP_Query( $args );

if ( $the_query->have_posts() ) {
	$json_string = '<script>var artsubmissions = {"submissions":[';
	//Separate loop for Photo / Digital Media to make it work because it's retarded
	if( $category == "Photo / Digital Media" ){
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$image_array = wp_get_attachment_image_src ( get_field('_thumbnail_id'), 'large' );
			$thumbnail_array = wp_get_attachment_image_src ( get_field('_thumbnail_id'), 'thumbnail' );
			if( get_field('art_type') == "Photo / Digital Media" ){
				if( $thumbnail_array[0] != "" && $image_array[0] != "" ){
					$json_string .= '{';
					$json_string .= '"title":"'.get_the_title().'",';
					$json_string .= '"art_type":"'.get_field('art_type').'",';
					$json_string .= '"post_id":"'.$post->ID.'",';
					$json_string .= '"post_date":"'.get_the_date('F j, Y').'",';
					$terms = get_the_terms( $post->ID, "art_status" );
					if ( $terms && ! is_wp_error( $terms ) ) :
					$cats = array();
					foreach ( $terms as $term ) {
						$cats[] = $term->name;
					}
					$catstring = join( ", ", $cats );
					$json_string .= '"category":"'.$catstring.'",';
					endif;
					$json_string .= '"image_url":"'. $image_array[0] .'",';
					$json_string .= '"thumb_image_url":"'. $thumbnail_array[0] .'",';
					$json_string .= '"postmeta":'.json_encode( get_post_custom() );
					$json_string .= '},';
				}
			}
		}
	} else {
		// Regular old WP loop!
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$image_array = wp_get_attachment_image_src ( get_field('_thumbnail_id'), 'large' );
			$thumbnail_array = wp_get_attachment_image_src ( get_field('_thumbnail_id'), 'thumbnail' );
			if( $thumbnail_array[0] != "" && $image_array[0] != "" ){
				$json_string .= '{';
				$json_string .= '"title":"'.get_the_title().'",';
				$json_string .= '"art_type":"'.get_field('art_type').'",';
				$json_string .= '"post_id":"'.$post->ID.'",';
				$json_string .= '"post_date":"'.get_the_date('F j, Y').'",';
				$terms = get_the_terms( $post->ID, "art_status" );
				if ( $terms && ! is_wp_error( $terms ) ) :
				$cats = array();
				foreach ( $terms as $term ) {
					$cats[] = $term->name;
				}
				$catstring = join( ", ", $cats );
				$json_string .= '"category":"'.$catstring.'",';
				endif;
				$json_string .= '"image_url":"'. $image_array[0] .'",';
				$json_string .= '"thumb_image_url":"'. $thumbnail_array[0] .'",';
				$json_string .= '"status":"'.get_post_status( $post->ID ) .'",';
				$json_string .= '"postmeta":'.json_encode( get_post_custom() );
				$json_string .= '},';
			}
		}
	}
	if( $json_string != ""){
		$json_string = rtrim($json_string, ",");
		$json_string .= "]}</script>";
		echo $json_string;
	}
} else {
	// no posts found
	$json_string = '<script>var artsubmissions = {"submissions":[{"title":"No posts found","art_type":"","image_url":"",}]}</script>';
	echo $json_string;
}
/* Restore original Post Data */
wp_reset_postdata();

?>
<button class="switchview button">RETURN TO GRID VIEW</button>
<div class="row<?php if( $view == "single" ) { ?> single-view<?php } else { ?> grid-view<?php } ?>">
	<div class="col-md-1 aligncenter singleton">
		<img id="prev-submission" src="http://pabstblueribbon.com/wp-content/themes/brewery/assets/img/icons/arrow-left.png">
	</div>
	<div class="col-md-6 singleton">
		<div class="art-submissions-image"></div>
		<div class="art-submissions-index-number aligncenter">Image <span></span> of <span></span></div>
	</div>
	<div class="col-md-1 aligncenter singleton">
		<img id="next-submission" src="http://pabstblueribbon.com/wp-content/themes/brewery/assets/img/icons/arrow-right.png">
	</div>
	<div class="col-md-1 aligncenter griddleton">
		<img id="prev-set" src="http://pabstblueribbon.com/wp-content/themes/brewery/assets/img/icons/arrow-left.png">
	</div>
	<div class="col-md-6 aligncenter griddleton grid-container">
	</div>
	<div class="col-md-1 aligncenter griddleton">
		<img id="next-set" src="http://pabstblueribbon.com/wp-content/themes/brewery/assets/img/icons/arrow-right.png">
	</div>
	<div class="col-md-4">
		<div class="row">
			<h2>PBR Art Review</h2>
		</div>
		<div class="row">
			<label for="year">YEAR</label>
			<select id="year">
<?php
date_default_timezone_set('America/Los_Angeles');
$thisYear = intval( date("Y") );
for ( $y=$thisYear;$y>2010;$y-- ) {
	echo '
				<option value="'.$y.'">'.$y.'</option>';
}
?>
			</select>
		</div>
		<div class="row">
			<label for="category">CATEGORY</label>
			<select id="category">
				<option value="all">ALL</option>
				<option value="2D">2D</option>
				<option value="3D">3D</option>
				<option value="art-can">Art Can</option>
				<!-- <option value="Photo / Digital Media">Photo / Digital Media</option> -->
			</select>
		</div>
		<div class="row">
			<label for="filter">FILTER BY: </label>
			<select id="filterby">
				<option value="new-submission">New Submissions</option>
				<option value="semifinalists">Semi-Finalists</option>
				<option value="finalists">Finalists</option>
				<option value="all">All Submissions</option>
			</select>
		</div>
		<div class="row">
			<button class="button" id="search">SEARCH SUBMISSIONS</button>
		</div>
		<div class="row">
			<h4 id="numberofsubmissions"><span></span> <span></span></h4>
		</div>
		<div class="row singleton button-container">
			<button class="button" id="delete">DELETE</button>
			<button class="button" id="semifinalist">MARK AS SEMI-FINALIST</button>
			<button class="button" id="finalist">MARK AS FINALIST</button>
			<button class="button unmark" id="unmarksemifinalist">UNMARK AS SEMI-FINALIST</button>
			<button class="button unmark" id="unmarkfinalist">UNMARK AS FINALIST</button>
		</div>
		<div class="row">
			<div id="message"></div>
		</div>
		<div class="art-submission singleton">
			<div><label>Title:</label> <h2 class="art-submissions-title"></h2></div>
			<div><label>Category:</label> <span class="art-submissions-type"></span></div>
			<div><label>Marked As:</label> <span class="art-submissions-category"></span></div>
			<div><label>Artist Name and Address:</label></div>
			<div class="art-submissions-artist-name"></div>
			<div class="art-submissions-artist-address"></div>
			<div><span class="art-submissions-artist-city"></span> <span class="art-submissions-artist-zip"></span></div>
			<div><label>Submitted on: </label><span class="art-submissions-date"></span></div>
			<div><label>Description:</label></div>
			<div class="art-submissions-description"></div>
			<div><label>Comments:</label></div>
			<textarea class="comments"></textarea>
			<button class="button update-comments">SAVE COMMENTS</button>
		</div>
	</div>
</div>

<script>

var appIndex, gridIndex;

var numberOfItemsInGrid = 16;

function emptySubmissionData() {
	$('.art-submissions-title, .art-submissions-type, .art-submissions-artist-name, .art-submissions-artist-address, .art-submissions-artist-city, .art-submissions-artist-zip, .art-submissions-date, .art-submissions-description, .art-submissions-category').empty();
	$('.art-submissions-image').css('background-image','none');
}

function initializeArtReview(index,type,gridoffset){
	if(type == "single"){
		emptySubmissionData();
		if( artsubmissions['submissions'].length > 0 ){
			$('.art-submissions-title').html(artsubmissions['submissions'][index]['title']);
			$('.art-submissions-type').html(artsubmissions['submissions'][index]['art_type']);
			if( $('.art-submissions-type').html() == "art-can" ){
				$('.art-submissions-type').html("Art Can");
			}
			$('.art-submissions-category').html(artsubmissions['submissions'][index]['category']);
			if(typeof artsubmissions['submissions'][index]['postmeta'] != 'undefined'){
				$('.art-submissions-artist-name').html(artsubmissions['submissions'][index]['postmeta']['artist_name']);
				$('.art-submissions-artist-address').html(artsubmissions['submissions'][index]['postmeta']['artist_address']);
				$('.art-submissions-artist-city').html(artsubmissions['submissions'][index]['postmeta']['artist_city']);
				$('.art-submissions-artist-zip').html(artsubmissions['submissions'][index]['postmeta']['artist_zip']);
				$('.art-submissions-date').html(artsubmissions['submissions'][index]['post_date']);
				$('.art-submissions-description').html(artsubmissions['submissions'][index]['postmeta']['art_description']);
				$('.comments').val( artsubmissions['submissions'][index]['postmeta']['comments'] );
			}
			$('.art-submissions-image').css('background-image','url('+artsubmissions['submissions'][index]['image_url']+')');
			setAppState(index);
			$('.art-submissions-index-number span:first-child').html(index+1);
			if(typeof artsubmissions['submissions'][index]['category'] != 'undefined'){
				if( artsubmissions['submissions'][index]['category'].indexOf("emifinalists") != -1 ){
					$('#semifinalist').hide()
					$('#unmarksemifinalist').show()
				} else {
					$('#unmarksemifinalist').hide()
					$('#semifinalist').show()
				}
				if( artsubmissions['submissions'][index]['category'].indexOf("inalists") != -1 && artsubmissions['submissions'][index]['category'].indexOf("emifinalists") == -1 ){
					$('#finalist').hide()
					$('#unmarkfinalist').show()
				} else {
					$('#unmarkfinalist').hide()
					$('#finalist').show()
				}
			}
			$('#numberofsubmissions span:first-child').html(artsubmissions['submissions'].length);
		}
	} else {
		$('.grid-container').empty();
		if(artsubmissions['submissions'].length > 0){
			if( artsubmissions['submissions'][0]['title'] != "No posts found" ){
				if (typeof gridoffset != "undefinied") {
					gridIndex = gridoffset;
				} else {
					gridIndex = 0;
				}
				for(i=0;i<numberOfItemsInGrid;i++){
					var singlegridbuttons = '<div class="grid-view-submission-buttons">';
					if(typeof artsubmissions['submissions'][i+gridIndex] != 'undefined'){
						if( artsubmissions['submissions'][(i+gridIndex)]['status'] === 'draft' ){
							singlegridbuttons += '<a title="Publish this art" href="javascript:publishSubmissionByID('+artsubmissions['submissions'][(i+gridIndex)]['post_id']+')"><i class="fa fa-flag fa-lg" style="color:#ff0000;margin-right:10px;"></i></a>';
						} else {
							singlegridbuttons += '<a title="Draft this art" href="javascript:draftSubmissionByID('+artsubmissions['submissions'][(i+gridIndex)]['post_id']+')"><i class="fa fa-flag fa-lg" style="margin-right:10px;"></i></a>';
						}
						if(typeof artsubmissions['submissions'][(i+gridIndex)]['category'] != 'undefined'){
							if( artsubmissions['submissions'][(i+gridIndex)]['category'].indexOf("emifinalists") != -1 ){
								singlegridbuttons += '<a href="javascript:unmarkAsSemifinalistByID('+artsubmissions['submissions'][(i+gridIndex)]['post_id']+')"><i class="fa fa-check"></i>SF</a> ';
							} else {
								singlegridbuttons += '<a href="javascript:markAsSemifinalistByID('+artsubmissions['submissions'][(i+gridIndex)]['post_id']+')">SF</a> ';
							}
							if( artsubmissions['submissions'][(i+gridIndex)]['category'].indexOf("inalists") != -1 && artsubmissions['submissions'][(i+gridIndex)]['category'].indexOf("emifinalists") == -1 ){
								singlegridbuttons += '<a href="javascript:unmarkAsFinalistByID('+artsubmissions['submissions'][(i+gridIndex)]['post_id']+')"><i class="fa fa-check"></i>F</a> ';
							} else {
								singlegridbuttons += '<a href="javascript:markAsFinalistByID('+artsubmissions['submissions'][(i+gridIndex)]['post_id']+')">F</a> ';
							}
						}
						singlegridbuttons += '<a class="thumbDelete" data-postid="'+artsubmissions['submissions'][(i+gridIndex)]['post_id']+'"><i class="fa fa-trash-o fa-lg"></i></a></div>';
						$('.grid-container').append('<div class="grid-view-submission"><a href="javascript:switchView('+(i+gridIndex)+')" class="grid-image-container"><img src="'+artsubmissions['submissions'][(i+gridIndex)]['thumb_image_url']+'"><div class="grid-image-overlay"><h5>'+artsubmissions['submissions'][(i+gridIndex)]['title']+'</h5></div></a>'+singlegridbuttons+'</div>');
					}
				}
				$('#numberofsubmissions span:first-child').html( "Items "+(gridIndex+1)+" - "+(gridIndex+numberOfItemsInGrid)+" of "+(artsubmissions['submissions'].length-1) );
			}
		}
	}
}

function switchView(index){
	if( $('main > div.row').hasClass('grid-view') ){
		$('main > div.row').removeClass('grid-view single-view');
		$('main > div.row').addClass('single-view');
		initializeArtReview(index,"single");
		appIndex = index;
		$('.switchview').show();
	} else {
		$('main > div.row').removeClass('grid-view single-view');
		$('main > div.row').addClass('grid-view');
		if(index > numberOfItemsInGrid){
			initializeArtReview( index, "grid", ( Math.floor( ( index / numberOfItemsInGrid ) ) * numberOfItemsInGrid) );
		} else {
			initializeArtReview( index, "grid", 0 );
		}
		appIndex = index;
	}
}

function nextSet(){
	// Ensure that you're not trying to show items beginning at an index that is past the end of the art submissions array
	if( (gridIndex + numberOfItemsInGrid ) < ( ( artsubmissions['submissions'].length-1 ) - numberOfItemsInGrid ) ) {
		initializeArtReview(appIndex,"grid",gridIndex+numberOfItemsInGrid);
	// Show last 16 if you're trying to view items beginning at an index that's in the last 16
	} else if ( (gridIndex + numberOfItemsInGrid ) < ( artsubmissions['submissions'].length -1 ) ){
		initializeArtReview(appIndex,"grid",artsubmissions['submissions'].length-1-numberOfItemsInGrid);
	} else {
		initializeArtReview(appIndex,"grid",0);
	}
}

function prevSet(){
	// Ensure that you're not trying to show items beginning at an index that is before the beginning of the art submissions array
	if( (gridIndex - numberOfItemsInGrid ) > -1 ) {
		initializeArtReview(appIndex,"grid",gridIndex-numberOfItemsInGrid);
	} else {
		initializeArtReview(appIndex,"grid",artsubmissions['submissions'].length-1-numberOfItemsInGrid);
	}
}

function setAppState(index){
	appIndex = index;
}

function nextSubmission(){
  	if( appIndex < artsubmissions['submissions'].length-1 ){
  		initializeArtReview(appIndex+1,"single");
  	} else {
  		initializeArtReview(0,"single");
  	}
}
function prevSubmission(){
  	if(appIndex!=0){
  		initializeArtReview(appIndex-1,"single");
  	} else {
  		initializeArtReview( artsubmissions['submissions'].length-1,"single" );
  	}
}

function postAjax(action,id,target,value){
	$.post(
	"<?php echo get_template_directory_uri(); ?>/artreviewactions.php",
	{
	  "form_action":action,
	  "post_id":id,
	  "target_field":target,
	  "new_value":value
	}, function( data ) {
		var myData = $.parseJSON(data);
		if( myData['success'] == "true" && value == "publish" ){
			index = findPostIndexByID(id);
			artsubmissions['submissions'][index]['status'] = "publish";
			initializeArtReview(appIndex,"grid",gridIndex);
		} else if( myData['success'] == "true" && value == "draft" ){
			index = findPostIndexByID(id);
			artsubmissions['submissions'][index]['status'] = "draft";
			initializeArtReview(appIndex,"grid",gridIndex);
		}
		var message = myData['message'];
		var newcats = myData['newcats'];
		var thepostid = myData['post_id'];
		var newcomments = myData['newcomments'];
		$('#message').html(message);
		$('#message').show();
		window.setTimeout("$('#message').fadeOut(1000)",6000);
		// Set new category if category was changed
		if( typeof newcats != 'undefined' ){
			for(i=0;i<artsubmissions['submissions'].length;i++){
				if ( artsubmissions['submissions'][i]['post_id'] == thepostid ) {
					// artsubmissions['submissions'][i]['category'] = newcats;
					artsubmissions['submissions'].splice(i,1);
					if($('main > .row').hasClass('single-view')){
						initializeArtReview(i,"single");
					} else {
						if( i > numberOfItemsInGrid){
							initializeArtReview( i, "grid", ( Math.floor( ( i / numberOfItemsInGrid ) ) * numberOfItemsInGrid) );
						} else {
							initializeArtReview( i, "grid", 0 );
						}
					}
					break
				}
			}
		}
		// Set new comments if comments were changed
		if( typeof newcomments != 'undefined'){
			for(i=0;i<artsubmissions['submissions'].length;i++){
				if ( artsubmissions['submissions'][i]['post_id'] == thepostid ) {
					artsubmissions['submissions'][i]['postmeta']['comments'] = newcomments;
					initializeArtReview(i,"single");
					break
				}
			}
		}
	});
}

function findPostIndexByID(id){
	var index;
	for(i=0;i<artsubmissions['submissions'].length;i++){
		if ( artsubmissions['submissions'][i]['post_id'] == id ) {
			index = i;
			break
		}
	}
	return index;
}

function deleteCurrentSubmission(){
	postAjax("delete", artsubmissions['submissions'][appIndex]['post_id'], "", "");
	artsubmissions['submissions'].splice(appIndex,1);
	if(appIndex>0){
		appIndex -= 1;
	} else {
		appIndex = artsubmissions['submissions'].length-1;
	}
	nextSubmission();
	$('#numberofsubmissions span:first-child').html(artsubmissions['submissions'].length);
	if(artsubmissions['submissions'].length == 1){ $('#numberofsubmissions span:last-child').html('match'); } else { $('#numberofsubmissions span:last-child').html('total matches'); }
	$('.art-submissions-index-number span:last-child').html(artsubmissions['submissions'].length);
}

function markAsSemifinalist(){
	postAjax("modify", artsubmissions['submissions'][appIndex]['post_id'], "new-submission", "semifinalists");
}

function markAsFinalist(){
	postAjax("modify", artsubmissions['submissions'][appIndex]['post_id'], "new-submission", "finalists");
}

function unmarkAsSemifinalist(){
	postAjax("remove_art_status", artsubmissions['submissions'][appIndex]['post_id'], "semifinalists", "new-submission");
}

function unmarkAsFinalist(){
	postAjax("remove_art_status", artsubmissions['submissions'][appIndex]['post_id'], "finalists", "new-submission");
}

function markAsSemifinalistByID(id){
	postAjax("modify", id, "", "semifinalists");
	initializeArtReview(appIndex,"grid",gridIndex);
}

function markAsFinalistByID(id){
	postAjax("modify", id, "", "finalists");
	initializeArtReview(appIndex,"grid",gridIndex);
}

function unmarkAsSemifinalistByID(id){
	postAjax("remove_art_status", id, "semifinalists", "new-submission");
	initializeArtReview(appIndex,"grid",gridIndex);
}

function unmarkAsFinalistByID(id){
	postAjax("remove_art_status", id, "finalists", "new-submission");
	initializeArtReview(appIndex,"grid",gridIndex);
}

function deleteSubmissionByID(id){
	postAjax("delete", id, "", "");
	var index = findPostIndexByID(id);
	artsubmissions['submissions'].splice(index,1);
	$('#numberofsubmissions span:first-child').html(artsubmissions['submissions'].length);
	if(artsubmissions['submissions'].length == 1){ $('#numberofsubmissions span:last-child').html('match'); } else { $('#numberofsubmissions span:last-child').html('total matches'); }
	initializeArtReview(appIndex,"grid",gridIndex);
}

function publishSubmissionByID(id){
	postAjax("publish", id, "", "publish");
}

function draftSubmissionByID(id){
	postAjax("unpublish", id, "", "draft");
}

function saveComments(){
	postAjax("update_comments", artsubmissions['submissions'][appIndex]['post_id'], "comments", $('.comments').val() );
}

$(document).ready(function(){
	$('header, .navbar-fix, footer').hide();
<?php if($view == "single"){ ?>
	initializeArtReview(0,"single");
<?php } else { ?>
	initializeArtReview(0,"grid",0);
<?php } ?>
	if(artsubmissions['submissions'].length == 1){ $('#numberofsubmissions span:last-child').html('match'); } else { $('#numberofsubmissions span:last-child').html('total matches'); }
	$('#numberofsubmissions').show();
	$('.art-submissions-index-number span:last-child').html(artsubmissions['submissions'].length);
	$('.art-submissions-index-number').show();
	$("body").keydown(function(e) {
	  if(e.keyCode == 37) { // left
	  	if( !$(".comments").is(":focus") ){
		  	e.preventDefault();
		  	if( $('main > .row').hasClass('single-view') ){
		  		prevSubmission();
		  	} else {
		  		prevSet();
		  	}
	  	}
	  }
	  else if(e.keyCode == 39) { // right
	  	if( !$(".comments").is(":focus") ){
		  	e.preventDefault();
		  	if( $('main > .row').hasClass('single-view') ){
		  		nextSubmission();
		  	} else {
		  		nextSet();
		  	}
	  	}
	  }
	});
	$('#next-submission').click(function(){
		nextSubmission();
	});
	$('#prev-submission').click(function(){
		prevSubmission();
	});
	$('#next-set').click(function(){
		nextSet();
	});
	$('#prev-set').click(function(){
		prevSet();
	});
	$('#delete').click(function(){
		if ( confirm('Are you sure you want to delete this submission?') ) {
			deleteCurrentSubmission();
		}
	});
	$('.thumbDelete').click(function(){
		$(this).removeData('postid');
		window.console.log('about to delete: '+ $(this).data('postid'));
		if ( confirm('Are you sure you want to delete this submission?') ) {
			// deleteCurrentSubmission();
			deleteSubmissionByID($(this).data('postid'));
		}
	});
	$('#semifinalist').click(function(){
		markAsSemifinalist();
	});
	$('#finalist').click(function(){
		markAsFinalist();
	});
	$('#unmarksemifinalist').click(function(){
		unmarkAsSemifinalist();
	});
	$('#unmarkfinalist').click(function(){
		unmarkAsFinalist();
	});
	$('#search').click(function(){
		var querystring = "/artreview/?artYear="+$('#year').val()+"&category="+encodeURIComponent($('#category').val())+"&filter="+$('#filterby').val()+"&view=grid";
		window.location.href = querystring;
	});
	$('.switchview').click(function(){
		switchView(appIndex);
		$('.switchview').hide();
	});
	$('.update-comments').click(function(){
		saveComments();
	});
<?php
if ( isset($artYear) ) { ?>
	$('#year').val('<?php echo $artYear; ?>');
<?php }
	if ( isset($category) ) { ?>
	$('#category').val('<?php echo $category; ?>');
<?php }
	if ( isset($filter) ) { ?>
	$('#filterby').val('<?php echo $filter; ?>');
<?php } ?>
	if ( artsubmissions['submissions'].length == 1 && artsubmissions['submissions'][0]['title'] == "No posts found" ) {
		$('#delete, #semifinalist, #finalist, #unmarkfinalist, #unmarksemifinalist').hide();
		$('#numberofsubmissions').hide();
	}
});
</script>

<?php

}

?>
