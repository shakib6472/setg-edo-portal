<?php
/**
 * Comments / questions on assignments.
 *
 * Uses the native WordPress comment system (so admins moderate via the standard
 * Comments screen). Members read and post from the portal via AJAX.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX endpoints and rendering for assignment comments.
 */
class EDO_Comments {

	/**
	 * Hook the AJAX endpoints (logged-in members only).
	 */
	public static function init() {
		add_action( 'wp_ajax_edo_get_comments', array( __CLASS__, 'ajax_get' ) );
		add_action( 'wp_ajax_edo_post_comment', array( __CLASS__, 'ajax_post' ) );
	}

	/**
	 * Validate the request and return the assignment ID, or 0.
	 *
	 * @return int
	 */
	private static function valid_assignment() {
		check_ajax_referer( EDO_Interest::NONCE, 'nonce' );

		if ( ! is_user_logged_in() || ! EDO_Roles::user_can_access() ) {
			wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
		}

		$post_id = isset( $_POST['assignment'] ) ? absint( wp_unslash( $_POST['assignment'] ) ) : 0;
		if ( ! $post_id || 'edo_assignment' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
			wp_send_json_error( array( 'message' => 'invalid' ), 400 );
		}
		return $post_id;
	}

	/**
	 * AJAX: return the rendered comment thread for an assignment.
	 */
	public static function ajax_get() {
		$post_id  = self::valid_assignment();
		$comments = get_comments(
			array(
				'post_id' => $post_id,
				'status'  => 'approve',
				'order'   => 'ASC',
			)
		);

		wp_send_json_success(
			array(
				'html'  => self::render_tree( $comments, 0 ),
				'count' => count( $comments ),
			)
		);
	}

	/**
	 * AJAX: store a new comment (optionally a reply) from the current member.
	 */
	public static function ajax_post() {
		$post_id = self::valid_assignment();

		$content = isset( $_POST['content'] ) ? sanitize_textarea_field( wp_unslash( $_POST['content'] ) ) : '';
		$content = trim( $content );
		if ( '' === $content ) {
			wp_send_json_error( array( 'message' => 'empty' ), 400 );
		}

		// A reply must point at a comment on the same assignment.
		$parent = isset( $_POST['parent'] ) ? absint( wp_unslash( $_POST['parent'] ) ) : 0;
		if ( $parent ) {
			$parent_comment = get_comment( $parent );
			if ( ! $parent_comment || (int) $parent_comment->comment_post_ID !== $post_id ) {
				$parent = 0;
			}
		}

		$user = wp_get_current_user();

		$comment_id = wp_insert_comment(
			array(
				'comment_post_ID'      => $post_id,
				'comment_content'      => $content,
				'comment_parent'       => $parent,
				'user_id'              => $user->ID,
				'comment_author'       => $user->display_name,
				'comment_author_email' => $user->user_email,
				'comment_approved'     => 1,
				'comment_type'         => 'comment',
			)
		);

		if ( ! $comment_id ) {
			wp_send_json_error( array( 'message' => 'failed' ), 500 );
		}

		wp_send_json_success(
			array(
				'html'   => self::render_comment( get_comment( $comment_id ) ),
				'parent' => $parent,
				'count'  => (int) get_comments_number( $post_id ),
			)
		);
	}

	/**
	 * Render comments under a given parent, recursively (threaded).
	 *
	 * @param WP_Comment[] $comments  All comments for the post.
	 * @param int          $parent_id Parent comment ID (0 = top level).
	 * @return string Safe HTML.
	 */
	private static function render_tree( $comments, $parent_id ) {
		$html = '';
		foreach ( $comments as $comment ) {
			if ( (int) $comment->comment_parent === (int) $parent_id ) {
				$html .= self::render_comment( $comment, $comments );
			}
		}
		return $html;
	}

	/**
	 * Render a single comment (with its nested replies, if $all is given).
	 *
	 * @param WP_Comment   $comment Comment.
	 * @param WP_Comment[] $all     All comments (for building children).
	 * @return string Safe HTML.
	 */
	private static function render_comment( $comment, $all = array() ) {
		$author   = $comment->comment_author ? $comment->comment_author : __( 'Lid', 'setg' );
		$when     = mysql2date( get_option( 'date_format' ) . ' H:i', $comment->comment_date );
		$children = empty( $all ) ? '' : self::render_tree( $all, (int) $comment->comment_ID );

		return sprintf(
			'<div class="edo-cmt" data-id="%1$d"><span class="edo-avatar edo-avatar--xs">%2$s</span><div class="edo-cmt__body"><div class="edo-cmt__meta"><strong>%3$s</strong><span>%4$s</span></div><p>%5$s</p><button type="button" class="edo-cmt-reply" data-reply="%1$d" data-name="%3$s">%6$s</button><div class="edo-cmt__children">%7$s</div></div></div>',
			(int) $comment->comment_ID,
			esc_html( edo_initials( $author ) ),
			esc_html( $author ),
			esc_html( $when ),
			esc_html( $comment->comment_content ),
			esc_html__( 'Beantwoorden', 'setg' ),
			$children
		);
	}
}
