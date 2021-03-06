<?php
/*
 * "n2" - Forum Software - a nBBS v0.6 + wtcBB remix.
 * Copyright (C) 2009 Chris F. Ravenscroft
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 * 
 * Questions? We can be reached at http://www.nextbbs.com
 */
// ************************************************** \\
## ************************************************** ##
## ************************************************** ##
## ******************* POST REPLY ******************* ##
## ************************************************** ##
## ************************************************** ##
// ************************************************** \\


// Define AREA
define('AREA', 'USER-POSTREPLY');
require_once('./includes/sessions.php');

$Thread = new Thread($_GET['t']);

// easy to access IDs
$postid = 0; $quoted = '';
$threadid = $Thread->getThreadId();
$forumid = $Thread->getForumId();

// create a hash for attachments
if(empty($_POST['myHash'])) {
	$myHash = md5(time() . $Thread->getThreadId() . $User->info['userid'] . microtime() . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
}

else {
	$myHash = $_POST['myHash'];
}

// initiate forum info
Forum::init();

// make sure forum exists
if(!isset($forums[$forumid])) {
	new WtcBBException($lang['error_forum_invalidForum']);
}

// create forum obj
$FORUM = $forums[$forumid]; // easy to access object
$error = ''; // sanity check

ForumPerm::init(); // initiates forum permissions array
Moderator::init(); // initiates moderators array

// this checks forum permissions, usergroups, and user forum access
if(!$User->canViewForum($forumid) OR !$Thread->canView() OR !$Thread->canReply()) {
	new WtcBBException('perm');
}

// forum is a link?
$FORUM->goToLink();

// thread is deleted?
if($Thread->isDeleted()) {
	new WtcBBException($lang['error_forum_threadDel']);
}

// get me quotes
if(is_array($_GET['p'])) {
	$quoted = '';
	$count = 1;

	foreach($_GET['p'] as $postid) {
		// hmm... over the limit?
		if($count > $bboptions['maxQuote']) {
			break;
		}

		$count++;

		$Post = new Post($postid);
		$temp = BBCode::stripMe($Post->getMessageTextArea(), 'quote');
		$quoted .= '[quote=' . $Post->getStarterName() . ']' . $temp . '[/quote]' . "\n\n";
	}

	// picky picky... remove one "\n" from end
	$quoted = preg_replace('/(\n\n)$/', "\n", $quoted);
}

else if($_GET['p']) {
	$Post = new Post($_GET['p']);
	$temp = BBCode::stripMe($Post->getMessageTextArea(), 'quote');
	$quoted = '[quote=' . $Post->getStarterName() . ']' . $temp . '[/quote]' . "\n";
}

// we are good to go... for posting...
if($_POST) {
	// initiate the message parser
	$MessageParser = new Message();
	$MessageParser->autoOptions($User, new Post('', $_POST['postreply']));

	if($_POST['selecteditor']) {
		// Are we switching favourite editor?
		if($User->info['editor'] != $_POST['selecteditor']) {
			$User->update(array('editor' => $_POST['selecteditor']));
			$User->info['editor'] = $_POST['selecteditor'];
		}
	}
	
	// preview?
	if($_POST['preview']) {
		$preview = $MessageParser->parse($_POST['message'], $User->info['username']);
	}

	// either error or post message!
	else {
		// start checking
		$checking = $MessageParser->check($_POST['message'], $_POST['postreply']['title']);

		// uh oh...
		if($checking instanceof WtcBBException) {
			$error = $checking->dump();
		}

		// flood
		else if($User->flood()) {
			$error = new WtcBBException($lang['error_flood'], false);
			$error = $error->dump();
		}

		else if(!$User->info['userid'] AND $_POST['postreply']['postUsername'] != $User->info['username']) {
			// check unique username...
			$check = new Query($query['user']['checkUniqueName'], Array(1 => $_POST['postreply']['postUsername']));
			$check = $wtcDB->fetchArray($check);

			if($check['checking']) {
				$error = new WtcBBException($lang['error_message_uniqueName'], false);
				$error = $error->dump();
			}

			// what about length of username?
			if($_POST['postreply']['postUsername'] < $bboptions['usernameMin']) {
				$error = new WtcBBException($lang['error_message_nameLengthMin'], false);
				$error = $error->dump();
			}

			else if($_POST['postreply']['postUsername'] > $bboptions['usernameMax']) {
				$error = new WtcBBException($lang['error_message_nameLengthMax'], false);
				$error = $error->dump();
			}
		}

		if(empty($error)) {
			// initiate
			$insert = Array();

			// okay we're good... get message
			$_POST['message'] = $checking;

			// sanity
			$_POST['postreply'] = array_map('wtcspecialchars', $_POST['postreply']);

			// now form our post insert
			$insert['threadid'] = $threadid;
			$insert['message'] = $_POST['message'];
			$insert['forumid'] = $forumid;
			$insert['postby'] = $User->info['userid'];
			$insert['title'] = (empty($_POST['postreply']['title']) ? 'Re: ' . $Thread->getName() : $_POST['postreply']['title']);
			$insert['ip_address'] = $_SERVER['REMOTE_ADDR'];
			$insert['posts_timeline'] = NOW;
			$insert['posticon'] = wtcspecialchars($_POST['posticon']);
			$insert['deleted'] = 0; $insert['edited_by'] = 0; $insert['edited_show'] = 1;

			// username
			if(!$User->info['userid']) {
				$insert['postUsername'] = $_POST['postreply']['postUsername'];
			}

			else {
				$insert['postUsername'] = $User->info['username'];
			}

			// options
			$insert['sig'] = $_POST['postreply']['sig'];
			$insert['smilies'] = $_POST['postreply']['smilies'];
			$insert['bbcode'] = $_POST['postreply']['bbcode'];
			$insert['defBBCode'] = 0;

			// now insert the post
			$postid = Post::insert($insert);

			// update last reply for thread
			$threadUpdate = Array(
								'last_reply_username' => $insert['postUsername'],
								'last_reply_userid' => $User->info['userid'],
								'last_reply_date' => NOW,
								'last_reply_postid' => $postid,
								'replies' => ($Thread->getReplies() + 1)
							);

			// now update our thread...
			$Thread->update($threadUpdate);

			// update forum...
			$forumUpdate = Array(
								'last_reply_username' => $insert['postUsername'],
								'last_reply_userid' => $User->info['userid'],
								'last_reply_date' => NOW,
								'last_reply_threadid' => $threadid,
								'last_reply_threadtitle' => $Thread->getName()
							);

			$FORUM->updateLastReplyAndCounts($forumUpdate);

			// update user post counts
			$User->update(
				Array(
					'posts' => (($FORUM->info['countPosts']) ? $User->info['posts'] + 1 : $User->info['posts']),
					'lastpost' => NOW,
					'lastpostid' => $postid
				)
			);

			// update any attachments?
			new Query($query['attachments']['update_hash'],
				Array(
					1 => $Thread->getThreadId(),
					2 => $postid,
					3 => $myHash
				)
			);

			// open close thread?
			if($_POST['closedStatus'] AND $Thread->canClose()) {
				$Thread->openClose();
			}

			// stick unstick thread?
			if($_POST['stickyStatus'] AND $Thread->canSticky()) {
				$Thread->stickUnstick();
			}

			// subscribe?
			if(LOGIN AND $_POST['sub']) {
				$Thread->subUnsub();
			}

			// mark read
			if(LOGIN) {
				new Query($query['read_threads']['insert'], Array(
																1 => $User->info['userid'],
																2 => $Thread->getThreadId(),
																3 => NOW
															));
			}

			// cache forums
			new Cache('Forums');

			// For WP Filters
			$wp_comment->comment_post_ID = $postid;
			$wp_comment->comment_approved = 1;
			$wp_comment->user_id = $User->info['userid'];			
			$wp_comment->comment_author = $User->info['username'];
			$wp_comment->comment_author_email = $User->info['email'];
			$wp_comment->comment_author_url = '';			
			$wp_comment->comment_content = $_POST['message'];
			//
			new WtcBBThanks($lang['thanks_postReply'], apply_filters('comment_post_redirect', './index.php?file=thread&amp;t=' . $Thread->getThreadId() . '&amp;p=' . $postid . $SESSURL, $wp_comment));
		}
	}
}

// uh oh...
if($_POST) {
	$_POST = array_map_recursive('wtcspecialchars', $_POST);
}

$postIcon = PostIcon::constructPostIcons();

$attachments = new Query($query['attachments']['get_hash'], Array(1 => $myHash));
$attachBits = '';

while($attach = $attachments->fetchArray()) {
	$Attachment = new Attachment('', $attach);

	$temp = new StyleFragment('message_attachBit');
	$attachBits .= $temp->dump();
}

$toolBar = Message::buildToolBar();

$editorBits = '';
$editors = getTextEditors();
foreach($editors as $name => $editor) {
	if($name == 'default')
		continue;
	$select = '';

	if($User->info['editor'] == $editor['name']) {
		$select = ' selected="selected"';
	}

	$editorBits .= "<option value='{$editor['name']}'{$select}>{$editor['long_name']}</option>\n";
}

// create navigation
$Nav = new Navigation(
			Array(
				$Thread->getName() => './index.php?file=thread&amp;t=' . $Thread->getThreadId(),
				$lang['user_thread_postReply'] => ''
			),
			'forum'
		);

$header = new StyleFragment('header');
$content = new StyleFragment('message_postreply');
$footer = new StyleFragment('footer');

$header->output();
$content->output();
$footer->output();

?>