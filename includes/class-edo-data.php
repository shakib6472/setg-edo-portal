<?php
/**
 * Data access layer for the portal views.
 *
 * Each method returns view-ready rows. When real content exists it is used;
 * otherwise the approved design's sample content is returned so the portal looks
 * complete from day one. Sample rows are clearly flagged with 'is_sample'.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read helpers used by the templates.
 */
class EDO_Data {

	/**
	 * Assignments, newest first.
	 *
	 * @param int $limit Max rows (0 = all).
	 * @return array
	 */
	public static function assignments( $limit = 0 ) {
		$posts = get_posts(
			array(
				'post_type'      => 'edo_assignment',
				'post_status'    => 'publish',
				'numberposts'    => $limit > 0 ? $limit : -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			$rows = self::sample_assignments();
			return $limit > 0 ? array_slice( $rows, 0, $limit ) : $rows;
		}

		$rows = array();
		foreach ( $posts as $p ) {
			$date = (string) get_post_meta( $p->ID, 'event_date', true );
			$dm   = self::day_month( $date );
			$rows[] = array(
				'id'        => $p->ID,
				'title'     => get_the_title( $p ),
				'client'    => (string) get_post_meta( $p->ID, 'client', true ),
				'date'      => $date,
				'location'  => (string) get_post_meta( $p->ID, 'location', true ),
				'time'      => (string) get_post_meta( $p->ID, 'time_investment', true ),
				'comp'      => (string) get_post_meta( $p->ID, 'compensation', true ),
				'tag'       => (string) get_post_meta( $p->ID, 'status_tag', true ),
				'tag_type'  => (string) get_post_meta( $p->ID, 'status_type', true ),
				'day'        => $dm['day'],
				'mon'        => $dm['mon'],
				'interested' => EDO_Interest::is_interested( $p->ID, get_current_user_id() ),
				'is_sample'  => false,
			);
		}
		return $rows;
	}

	/**
	 * Announcements, newest first.
	 *
	 * @param int $limit Max rows (0 = all).
	 * @return array
	 */
	public static function announcements( $limit = 0 ) {
		$posts = get_posts(
			array(
				'post_type'   => 'edo_announcement',
				'post_status' => 'publish',
				'numberposts' => $limit > 0 ? $limit : -1,
				'orderby'     => 'date',
				'order'       => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			$rows = self::sample_announcements();
			return $limit > 0 ? array_slice( $rows, 0, $limit ) : $rows;
		}

		$rows = array();
		foreach ( $posts as $p ) {
			$rows[] = array(
				'id'        => $p->ID,
				'title'     => get_the_title( $p ),
				'body'      => wp_strip_all_tags( get_the_excerpt( $p ) ),
				'full'      => get_post_field( 'post_content', $p ),
				'date'      => human_time_diff( get_post_time( 'U', true, $p ), time() ) . ' ' . __( 'geleden', 'setg' ),
				'important' => (bool) get_post_meta( $p->ID, 'important', true ),
				'is_sample' => false,
			);
		}
		return $rows;
	}

	/**
	 * Trainings, newest first.
	 *
	 * @param int $limit Max rows (0 = all).
	 * @return array
	 */
	public static function trainings( $limit = 0 ) {
		$posts = get_posts(
			array(
				'post_type'   => 'edo_training',
				'post_status' => 'publish',
				'numberposts' => $limit > 0 ? $limit : -1,
				'orderby'     => 'date',
				'order'       => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			$rows = self::sample_trainings();
			return $limit > 0 ? array_slice( $rows, 0, $limit ) : $rows;
		}

		$rows = array();
		foreach ( $posts as $p ) {
			$rows[] = array(
				'id'        => $p->ID,
				'title'     => get_the_title( $p ),
				'subject'   => (string) get_post_meta( $p->ID, 'subject', true ),
				'date'      => (string) get_post_meta( $p->ID, 'event_date', true ),
				'place'     => (string) get_post_meta( $p->ID, 'place', true ),
				'online'    => (bool) get_post_meta( $p->ID, 'is_online', true ),
				'spots'     => (string) get_post_meta( $p->ID, 'spots', true ),
				'is_sample' => false,
			);
		}
		return $rows;
	}

	/**
	 * Documents & resources, newest first.
	 *
	 * @return array
	 */
	public static function documents() {
		$posts = get_posts(
			array(
				'post_type'   => 'edo_document',
				'post_status' => 'publish',
				'numberposts' => -1,
				'orderby'     => 'date',
				'order'       => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return self::sample_documents();
		}

		$rows = array();
		foreach ( $posts as $p ) {
			$cat = (string) get_post_meta( $p->ID, 'doc_category', true );
			$cat = $cat ? $cat : 'document';

			$att_id = absint( get_post_meta( $p->ID, 'attachment_id', true ) );
			$url    = $att_id ? wp_get_attachment_url( $att_id ) : '';
			if ( ! $url ) {
				$url = (string) get_post_meta( $p->ID, 'external_url', true );
			}

			$rows[] = array(
				'id'        => $p->ID,
				'title'     => get_the_title( $p ),
				'cat'       => $cat,
				'short'     => self::doc_short( $cat ),
				'meta'      => (string) get_post_meta( $p->ID, 'meta_line', true ),
				'url'       => $url ? $url : '',
				'is_sample' => false,
			);
		}
		return $rows;
	}

	/**
	 * Team members. Real EDO members when present, else sample team.
	 *
	 * @return array
	 */
	public static function members() {
		$users = get_users(
			array(
				'role'    => EDO_Roles::ROLE,
				'orderby' => 'display_name',
			)
		);

		if ( empty( $users ) ) {
			return self::sample_members();
		}

		$rows = array();
		foreach ( $users as $u ) {
			$role  = get_user_meta( $u->ID, 'edo_function', true );
			$avail = get_user_meta( $u->ID, 'edo_availability', true );
			$exp   = (string) get_user_meta( $u->ID, 'edo_expertise', true );
			$tags  = $exp ? array_slice( array_filter( array_map( 'trim', explode( ',', $exp ) ) ), 0, 2 ) : array();

			$rows[] = array(
				'id'        => $u->ID,
				'name'      => $u->display_name,
				'role'      => $role ? $role : __( 'Ervaringsdeskundige', 'setg' ),
				'tags'      => $tags,
				'avail'     => $avail ? $avail : '—',
				'is_sample' => false,
			);
		}
		return $rows;
	}

	/**
	 * TutorLMS courses for the Training view, newest first.
	 *
	 * Returns an empty array when Tutor is not active. Each row links to the
	 * course's own single page (styled later, in the final pass).
	 *
	 * @param int $limit Max rows (0 = all).
	 * @return array
	 */
	public static function courses( $limit = 0 ) {
		if ( ! edo_tutor_active() ) {
			return array();
		}

		$posts = get_posts(
			array(
				'post_type'   => tutor()->course_post_type,
				'post_status' => 'publish',
				'numberposts' => $limit > 0 ? $limit : -1,
				'orderby'     => 'date',
				'order'       => 'DESC',
			)
		);

		$user_id = get_current_user_id();
		$utils   = tutor_utils();

		$rows = array();
		foreach ( $posts as $p ) {
			$enrolled = false;
			if ( $user_id && method_exists( $utils, 'is_enrolled' ) ) {
				$enrolled = (bool) $utils->is_enrolled( $p->ID, $user_id );
			}

			$lessons = 0;
			if ( method_exists( $utils, 'get_lesson_count_by_course' ) ) {
				$lessons = (int) $utils->get_lesson_count_by_course( $p->ID );
			}

			$rows[] = array(
				'id'       => $p->ID,
				'title'    => get_the_title( $p ),
				'url'      => get_permalink( $p ),
				'excerpt'  => wp_trim_words( wp_strip_all_tags( get_the_excerpt( $p ) ), 18 ),
				'thumb'    => (string) get_the_post_thumbnail_url( $p, 'medium_large' ),
				'lessons'  => $lessons,
				'enrolled' => $enrolled,
			);
		}
		return $rows;
	}

	/**
	 * Number of published Tutor courses (0 when Tutor is inactive).
	 *
	 * @return int
	 */
	public static function courses_count() {
		if ( ! edo_tutor_active() ) {
			return 0;
		}
		$counts = wp_count_posts( tutor()->course_post_type );
		return isset( $counts->publish ) ? (int) $counts->publish : 0;
	}

	/**
	 * Short label shown on a document tile, derived from its category.
	 *
	 * @param string $cat Category key.
	 * @return string
	 */
	private static function doc_short( $cat ) {
		$map = array(
			'document' => 'PDF',
			'artikel'  => 'ART',
			'video'    => 'MP4',
			'beleid'   => 'BEL',
		);
		return isset( $map[ $cat ] ) ? $map[ $cat ] : 'DOC';
	}

	/**
	 * Quick dashboard counts.
	 *
	 * @return array{assignments:int,trainings:int,documents:int}
	 */
	public static function counts() {
		$courses = self::courses_count();
		return array(
			'assignments' => self::count_or( 'edo_assignment', 3 ),
			'courses'     => $courses > 0 ? $courses : 2,
			'documents'   => self::count_or( 'edo_document', 5 ),
		);
	}

	/**
	 * Published count for a post type, or a sample fallback when empty.
	 *
	 * @param string $post_type Post type.
	 * @param int    $fallback  Sample number to show when there is no content.
	 * @return int
	 */
	private static function count_or( $post_type, $fallback ) {
		$counts = wp_count_posts( $post_type );
		$n      = isset( $counts->publish ) ? (int) $counts->publish : 0;
		return $n > 0 ? $n : $fallback;
	}

	/**
	 * Derive a two-digit day and short month from a date string.
	 *
	 * @param string $date Free-text or parseable date.
	 * @return array{day:string,mon:string}
	 */
	private static function day_month( $date ) {
		$ts = $date ? strtotime( $date ) : false;
		if ( ! $ts ) {
			return array(
				'day' => '',
				'mon' => '',
			);
		}
		return array(
			'day' => gmdate( 'd', $ts ),
			'mon' => strtolower( gmdate( 'M', $ts ) ),
		);
	}

	/* --------------------------------------------------------------------- *
	 * Sample content (mirrors the approved design).
	 * --------------------------------------------------------------------- */

	/**
	 * Sample assignments.
	 *
	 * @return array
	 */
	private static function sample_assignments() {
		$raw = array(
			array( 'Beoordeling onderzoeksvoorstellen — ZonMw referentenpanel', 'ZonMw', '12 juni 2026', 'Online (Teams)', '± 4 uur', '€75 vergoeding', '12', 'jun', 'Nieuw', 'new' ),
			array( 'Workshop vroegsignalering — sociaal wijkteam', 'GGD Amsterdam', '18 juni 2026', 'Amsterdam-West', '3 uur', '€120 vergoeding', '18', 'jun', 'Bijna vol', 'urgent' ),
			array( 'Klankbordgroep interventieontwikkeling', 'SETG · Amsterdam UMC', '24 juni 2026', 'Marco Polostraat, A’dam', '2 uur', '€50 vergoeding', '24', 'jun', '', '' ),
			array( 'Adviesgesprek gemeente — preventiebeleid gokschade', 'Gemeente Almere', '1 juli 2026', 'Almere · hybride', '2,5 uur', '€90 vergoeding', '01', 'jul', '', '' ),
			array( 'Voorlichting MBO — jouw ervaringsverhaal', 'ROC van Amsterdam', '8 juli 2026', 'Amsterdam-Zuidoost', '2 uur', '€80 vergoeding', '08', 'jul', '', '' ),
			array( 'Meelezen subsidieaanvraag preventie', 'Kansspelautoriteit', 'voor 15 juli 2026', 'Online', '3 uur', '€75 vergoeding', '15', 'jul', 'Flexibel', 'info' ),
		);

		$rows = array();
		foreach ( $raw as $i => $r ) {
			$rows[] = array(
				'id'        => 'sample-a' . ( $i + 1 ),
				'title'     => $r[0],
				'client'    => $r[1],
				'date'      => $r[2],
				'location'  => $r[3],
				'time'      => $r[4],
				'comp'      => $r[5],
				'day'       => $r[6],
				'mon'       => $r[7],
				'tag'       => $r[8],
				'tag_type'  => $r[9],
				'is_sample' => true,
			);
		}
		return $rows;
	}

	/**
	 * Sample announcements.
	 *
	 * @return array
	 */
	private static function sample_announcements() {
		$raw = array(
			array( 'Nieuwe trainingsronde ZonMw-referenten geopend', 'Inschrijving voor de combinatietraining (9 & 30 sept) is open. Beperkt aantal plekken.', '2 dagen geleden', true ),
			array( 'EDO-teamdag — zet 5 juli in je agenda', 'Een middag samen: terugblik, intervisie en plannen voor het najaar. Locatie volgt.', '4 dagen geleden', false ),
			array( 'Vergoedingen geüpdatet per 1 juni', 'De vrijwilligersvergoedingen zijn aangepast. Bekijk de details in Documenten.', '1 week geleden', false ),
			array( 'Welkom aan drie nieuwe teamleden', 'Sanne, Youssef en Linda versterken het EDO-team. Maak kennis via Leden.', '2 weken geleden', false ),
		);

		$rows = array();
		foreach ( $raw as $i => $r ) {
			$rows[] = array(
				'id'        => 'sample-n' . ( $i + 1 ),
				'title'     => $r[0],
				'body'      => $r[1],
				'full'      => $r[1],
				'date'      => $r[2],
				'important' => $r[3],
				'is_sample' => true,
			);
		}
		return $rows;
	}

	/**
	 * Sample trainings.
	 *
	 * @return array
	 */
	private static function sample_trainings() {
		$raw = array(
			array( 'Beoordelen van onderzoeksvoorstellen', 'Referent worden bij ZonMw · via Involv', '9 & 30 sept 2026', 'Amsterdam', false, 'Nog 4 plekken' ),
			array( 'Weerbaar & Bewust — basistraining', 'Ervaringsdeskundigheid inzetten met impact', '25 juni 2026', 'Online (Zoom)', true, 'Plekken vrij' ),
			array( 'Vroegsignalering & herkenning gokschade', 'Signalen herkennen en toeleiden naar zorg', '2 juli 2026', 'Amsterdam', false, 'Nog 6 plekken' ),
			array( 'Storytelling & presenteren met impact', 'Jouw verhaal krachtig én veilig vertellen', '10 juli 2026', 'Online (Zoom)', true, 'Plekken vrij' ),
			array( 'Omgaan met stigma en schaamte', 'Veerkracht en grenzen in je rol', '16 juli 2026', 'Amsterdam', false, 'Nog 3 plekken' ),
		);

		$rows = array();
		foreach ( $raw as $i => $r ) {
			$rows[] = array(
				'id'        => 'sample-t' . ( $i + 1 ),
				'title'     => $r[0],
				'subject'   => $r[1],
				'date'      => $r[2],
				'place'     => $r[3],
				'online'    => $r[4],
				'spots'     => $r[5],
				'is_sample' => true,
			);
		}
		return $rows;
	}

	/**
	 * Sample documents.
	 *
	 * @return array
	 */
	private static function sample_documents() {
		$raw = array(
			array( 'Handboek Ervaringsdeskundigheid', 'document', 'Document · 2,4 MB · PDF' ),
			array( 'Addiction by Design — samenvatting', 'artikel', 'Artikel · 8 min lezen' ),
			array( 'Webinar: vroegsignalering in de wijk', 'video', 'Video · 18 min' ),
			array( 'Weerbaar & Bewust — werkboek', 'document', 'Document · 1,1 MB · PDF' ),
			array( 'ZonMw beoordelingskader referenten', 'beleid', 'Beleid · richtlijn' ),
			array( 'Interview: herstel en gokschade', 'video', 'Video · 24 min' ),
			array( 'Factsheet gokschade & cijfers 2025', 'artikel', 'Artikel · factsheet' ),
			array( 'Vergoedingen & privacy EDO-team', 'document', 'Document · 320 KB · PDF' ),
		);

		$rows = array();
		foreach ( $raw as $i => $r ) {
			$rows[] = array(
				'id'        => 'sample-d' . ( $i + 1 ),
				'title'     => $r[0],
				'cat'       => $r[1],
				'short'     => self::doc_short( $r[1] ),
				'meta'      => $r[2],
				'url'       => '',
				'is_sample' => true,
			);
		}
		return $rows;
	}

	/**
	 * Sample team members.
	 *
	 * @return array
	 */
	private static function sample_members() {
		$raw = array(
			array( 'Raymond Aronds', 'Founder', array( 'Strategie', 'Beleid' ), 'Ma–Vr' ),
			array( 'Roger Stassen', 'Coördinator', array( 'Planning', 'Trainingen' ), 'Ma–Do' ),
			array( 'Mahmudul Hoque', 'Web Manager', array( 'Online', 'Communicatie' ), 'Flexibel' ),
			array( 'Sanne de Vries', 'Ervaringsdeskundige', array( 'Vroegsignalering', 'Jongeren' ), 'Di–Do' ),
			array( 'Youssef El Amrani', 'Ervaringsdeskundige', array( 'Online gokken', 'Storytelling' ), 'Wo–Vr' ),
			array( 'Linda Beumer', 'Ervaringsdeskundige', array( 'Herstel', 'Naasten' ), 'Ma, Di' ),
		);

		$rows = array();
		foreach ( $raw as $i => $r ) {
			$rows[] = array(
				'id'        => 'sample-m' . ( $i + 1 ),
				'name'      => $r[0],
				'role'      => $r[1],
				'tags'      => $r[2],
				'avail'     => $r[3],
				'is_sample' => true,
			);
		}
		return $rows;
	}
}
