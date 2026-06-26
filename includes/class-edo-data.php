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
				'tag_type'  => '',
				'day'       => $dm['day'],
				'mon'       => $dm['mon'],
				'is_sample' => false,
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
				'date'      => human_time_diff( get_post_time( 'U', true, $p ), current_time( 'timestamp', true ) ) . ' ' . __( 'geleden', 'setg-edo-portal' ),
				'important' => (bool) get_post_meta( $p->ID, 'important', true ),
				'is_sample' => false,
			);
		}
		return $rows;
	}

	/**
	 * Quick dashboard counts.
	 *
	 * @return array{assignments:int,trainings:int,documents:int}
	 */
	public static function counts() {
		return array(
			'assignments' => self::count_or( 'edo_assignment', 3 ),
			'trainings'   => self::count_or( 'edo_training', 2 ),
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
				'date'      => $r[2],
				'important' => $r[3],
				'is_sample' => true,
			);
		}
		return $rows;
	}
}
