<?php
/**
 * Assignment interest ("Ik ben geïnteresseerd").
 *
 * Members toggle interest in an assignment; the list of interested members is
 * stored on the assignment and shown to admins.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interest storage, AJAX toggle and admin display.
 */
class EDO_Interest {

	const META  = '_edo_interested';
	const NONCE = 'edo_interest';

	/**
	 * Hook in. The AJAX handler is always registered (admin-ajax.php runs in the
	 * admin context); the read-only meta box only in wp-admin.
	 */
	public static function init() {
		add_action( 'wp_ajax_edo_toggle_interest', array( __CLASS__, 'ajax_toggle' ) );

		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
			add_action( 'admin_menu', array( __CLASS__, 'register_page' ) );
		}
	}

	/**
	 * Aggregate interest by member across all assignments.
	 *
	 * @return array<int,array{user:WP_User,items:array,count:int}> Sorted by count desc.
	 */
	public static function by_member() {
		$assignments = get_posts(
			array(
				'post_type'   => 'edo_assignment',
				'post_status' => 'publish',
				'numberposts' => -1,
			)
		);

		$map = array();
		foreach ( $assignments as $a ) {
			foreach ( self::get_interested( $a->ID ) as $uid => $when ) {
				$map[ $uid ][] = array(
					'id'    => $a->ID,
					'title' => get_the_title( $a ),
					'date'  => (string) get_post_meta( $a->ID, 'event_date', true ),
					'time'  => (int) $when,
				);
			}
		}

		$rows = array();
		foreach ( $map as $uid => $items ) {
			$user = get_userdata( $uid );
			if ( ! $user ) {
				continue;
			}
			usort(
				$items,
				static function ( $x, $y ) {
					return $y['time'] - $x['time'];
				}
			);
			$rows[] = array(
				'user'  => $user,
				'items' => $items,
				'count' => count( $items ),
			);
		}

		usort(
			$rows,
			static function ( $x, $y ) {
				return $y['count'] - $x['count'];
			}
		);
		return $rows;
	}

	/**
	 * The interested map for an assignment: user_id => unix timestamp.
	 *
	 * @param int $post_id Assignment ID.
	 * @return array<int,int>
	 */
	public static function get_interested( $post_id ) {
		$value = get_post_meta( $post_id, self::META, true );
		return is_array( $value ) ? $value : array();
	}

	/**
	 * Whether a user is interested in an assignment.
	 *
	 * @param int $post_id Assignment ID.
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_interested( $post_id, $user_id ) {
		if ( ! $user_id ) {
			return false;
		}
		return isset( self::get_interested( $post_id )[ $user_id ] );
	}

	/**
	 * Number of interested members.
	 *
	 * @param int $post_id Assignment ID.
	 * @return int
	 */
	public static function count( $post_id ) {
		return count( self::get_interested( $post_id ) );
	}

	/**
	 * Toggle a user's interest. Returns the new state (true = now interested).
	 *
	 * @param int $post_id Assignment ID.
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function toggle( $post_id, $user_id ) {
		$list = self::get_interested( $post_id );
		if ( isset( $list[ $user_id ] ) ) {
			unset( $list[ $user_id ] );
			$now = false;
		} else {
			$list[ $user_id ] = time();
			$now              = true;
		}
		update_post_meta( $post_id, self::META, $list );
		return $now;
	}

	/**
	 * AJAX: toggle the current user's interest in an assignment.
	 */
	public static function ajax_toggle() {
		check_ajax_referer( self::NONCE, 'nonce' );

		if ( ! is_user_logged_in() || ! EDO_Roles::user_can_access() ) {
			wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
		}

		$post_id = isset( $_POST['assignment'] ) ? absint( wp_unslash( $_POST['assignment'] ) ) : 0;
		if ( ! $post_id || 'edo_assignment' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
			wp_send_json_error( array( 'message' => 'invalid' ), 400 );
		}

		$state = self::toggle( $post_id, get_current_user_id() );

		/**
		 * Fires after a member toggles interest — used for admin notifications (Phase 4).
		 *
		 * @param int  $post_id Assignment ID.
		 * @param int  $user_id Member ID.
		 * @param bool $state   New interest state.
		 */
		do_action( 'edo_interest_toggled', $post_id, get_current_user_id(), $state );

		wp_send_json_success(
			array(
				'interested' => $state,
				'count'      => self::count( $post_id ),
			)
		);
	}

	/**
	 * Register the read-only "interested members" meta box.
	 */
	public static function add_meta_box() {
		add_meta_box(
			'edo_interested',
			__( 'Geïnteresseerde leden', 'setg' ),
			array( __CLASS__, 'render_meta_box' ),
			'edo_assignment',
			'side',
			'default'
		);
	}

	/**
	 * Render the list of interested members (read-only).
	 *
	 * @param WP_Post $post Assignment.
	 */
	public static function render_meta_box( $post ) {
		$list = self::get_interested( $post->ID );
		if ( empty( $list ) ) {
			echo '<p>' . esc_html__( 'Nog geen aanmeldingen.', 'setg' ) . '</p>';
			return;
		}

		arsort( $list ); // Most recent first.
		echo '<ul style="margin:0;">';
		foreach ( $list as $user_id => $when ) {
			$user = get_userdata( $user_id );
			if ( ! $user ) {
				continue;
			}
			$subject = sprintf(
				/* translators: %s: assignment title. */
				__( 'EDO-opdracht: %s', 'setg' ),
				get_the_title( $post )
			);
			$mailto  = 'mailto:' . $user->user_email . '?subject=' . rawurlencode( $subject );
			printf(
				'<li style="padding:6px 0;border-bottom:1px solid #f0f0f1;"><a href="%4$s">%1$s</a><br><span style="color:#777;font-size:11px;">%2$s · %3$s</span></li>',
				esc_html( $user->display_name ),
				esc_html( $user->user_email ),
				esc_html( date_i18n( get_option( 'date_format' ), (int) $when ) ),
				esc_url( $mailto )
			);
		}
		echo '</ul>';
		printf(
			'<p style="margin-top:8px;"><a href="%s">%s →</a></p>',
			esc_url( admin_url( 'admin.php?page=edo-interesse' ) ),
			esc_html__( 'Alle interesse bekijken', 'setg' )
		);
	}

	/**
	 * Register the "EDO · Interesse" admin page.
	 */
	public static function register_page() {
		add_menu_page(
			__( 'EDO Interesse', 'setg' ),
			__( 'EDO · Interesse', 'setg' ),
			'edit_posts',
			'edo-interesse',
			array( __CLASS__, 'render_page' ),
			'dashicons-heart',
			27
		);
	}

	/**
	 * Render the interest-by-member admin page: a table of members, with a
	 * pop-up showing which assignments each is interested in, plus an e-mail
	 * action (SETG contacts members manually, per the brief).
	 */
	public static function render_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$rows        = self::by_member();
		$date_format = get_option( 'date_format' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'EDO · Interesse', 'setg' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Leden die interesse hebben getoond in opdrachten. Neem handmatig contact op via e-mail.', 'setg' ); ?></p>

			<?php if ( empty( $rows ) ) : ?>
				<p><?php esc_html_e( 'Nog geen aanmeldingen.', 'setg' ); ?></p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Lid', 'setg' ); ?></th>
							<th><?php esc_html_e( 'E-mail', 'setg' ); ?></th>
							<th style="width:140px;"><?php esc_html_e( 'Opdrachten', 'setg' ); ?></th>
							<th style="width:220px;"><?php esc_html_e( 'Acties', 'setg' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rows as $row ) : ?>
							<?php
							$user    = $row['user'];
							$subject = __( 'EDO Community Portal', 'setg' );
							$mailto  = 'mailto:' . $user->user_email . '?subject=' . rawurlencode( $subject );
							?>
							<tr>
								<td><strong><?php echo esc_html( $user->display_name ); ?></strong></td>
								<td><a href="<?php echo esc_url( $mailto ); ?>"><?php echo esc_html( $user->user_email ); ?></a></td>
								<td><?php echo (int) $row['count']; ?></td>
								<td>
									<button
										type="button"
										class="button edo-idetails"
										data-name="<?php echo esc_attr( $user->display_name ); ?>"
										data-email="<?php echo esc_attr( $user->user_email ); ?>"
										data-target="edo-im-<?php echo (int) $user->ID; ?>"
									><?php esc_html_e( 'Details', 'setg' ); ?></button>
									<a class="button button-primary" href="<?php echo esc_url( $mailto ); ?>"><?php esc_html_e( 'E-mail', 'setg' ); ?></a>

									<div id="edo-im-<?php echo (int) $user->ID; ?>" style="display:none;">
										<ul style="margin:0;list-style:disc;padding-left:18px;">
											<?php foreach ( $row['items'] as $item ) : ?>
												<li style="margin-bottom:6px;">
													<a href="<?php echo esc_url( get_edit_post_link( $item['id'] ) ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
													<?php if ( $item['date'] ) : ?>
														<span style="color:#777;"> — <?php echo esc_html( $item['date'] ); ?></span>
													<?php endif; ?>
													<span style="color:#999;font-size:11px;"> (<?php echo esc_html( date_i18n( $date_format, $item['time'] ) ); ?>)</span>
												</li>
											<?php endforeach; ?>
										</ul>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<!-- detail modal -->
		<div id="edo-imodal" class="edo-imodal" style="display:none;">
			<div class="edo-imodal__overlay"></div>
			<div class="edo-imodal__dialog" role="dialog" aria-modal="true">
				<div class="edo-imodal__head">
					<strong id="edo-imodal-title"></strong>
					<button type="button" class="button-link edo-imodal-close" aria-label="<?php esc_attr_e( 'Sluiten', 'setg' ); ?>">&times;</button>
				</div>
				<div id="edo-imodal-body"></div>
				<p id="edo-imodal-foot" style="margin-top:14px;"></p>
			</div>
		</div>

		<style>
			.edo-imodal { position: fixed; inset: 0; z-index: 100000; }
			.edo-imodal__overlay { position: absolute; inset: 0; background: rgba(0,0,0,.5); }
			.edo-imodal__dialog { position: relative; max-width: 520px; margin: 8vh auto 0; background: #fff; border-radius: 8px; padding: 18px 22px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
			.edo-imodal__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
			.edo-imodal__head strong { font-size: 15px; }
			.edo-imodal-close { font-size: 22px; line-height: 1; text-decoration: none; color: #50575e; }
		</style>

		<script>
		( function () {
			var modal = document.getElementById( 'edo-imodal' );
			var title = document.getElementById( 'edo-imodal-title' );
			var body  = document.getElementById( 'edo-imodal-body' );
			var foot  = document.getElementById( 'edo-imodal-foot' );

			function close() { modal.style.display = 'none'; body.innerHTML = ''; foot.innerHTML = ''; }

			document.addEventListener( 'click', function ( e ) {
				var btn = e.target.closest( '.edo-idetails' );
				if ( btn ) {
					var src = document.getElementById( btn.getAttribute( 'data-target' ) );
					var name = btn.getAttribute( 'data-name' );
					var email = btn.getAttribute( 'data-email' );
					title.textContent = name;
					body.innerHTML = src ? src.innerHTML : '';
					foot.innerHTML = '<a class="button button-primary" href="mailto:' + encodeURIComponent( email ).replace( /%40/g, '@' ) + '?subject=EDO%20Community%20Portal"><?php echo esc_js( __( 'E-mail versturen', 'setg' ) ); ?></a>';
					modal.style.display = 'block';
					return;
				}
				if ( e.target.closest( '.edo-imodal-close' ) || e.target.classList.contains( 'edo-imodal__overlay' ) ) {
					close();
				}
			} );
			document.addEventListener( 'keydown', function ( e ) {
				if ( 'Escape' === e.key && 'block' === modal.style.display ) { close(); }
			} );
		} )();
		</script>
		<?php
	}
}
