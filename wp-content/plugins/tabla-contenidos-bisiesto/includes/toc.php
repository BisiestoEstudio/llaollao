<?php
/**
 * Lógica de la tabla de contenidos: parseo de encabezados, generación
 * de IDs (anclas) y construcción del HTML de la lista.
 *
 * Tanto la tabla como los IDs de los encabezados se calculan con el MISMO
 * algoritmo de slug + desambiguación, en el mismo orden de documento, de modo
 * que las anclas de la tabla siempre coinciden con los IDs reales del post.
 */

defined( 'ABSPATH' ) || exit;

/** Niveles de encabezado que entran en la tabla. */
const TCB_LEVELS = array( 1, 2, 3, 4 );

/**
 * Genera un ID único a partir de un texto, registrándolo en $used.
 *
 * @param string $text Texto del encabezado.
 * @param array  $used Mapa de IDs ya usados (por referencia).
 * @return string
 */
function tcb_make_id( $text, array &$used ) {
	$base = sanitize_title( $text );
	if ( '' === $base ) {
		$base = 'seccion';
	}
	$id = $base;
	$n  = 2;
	while ( isset( $used[ $id ] ) ) {
		$id = $base . '-' . $n;
		$n++;
	}
	$used[ $id ] = true;
	return $id;
}

/**
 * Carga un fragmento HTML en DOMDocument (UTF-8, sin html/body implícitos).
 *
 * @param string $html
 * @return DOMDocument
 */
function tcb_load_dom( $html ) {
	$dom = new DOMDocument();
	libxml_use_internal_errors( true );
	$dom->loadHTML(
		'<?xml encoding="utf-8" ?><div id="tcb-wrap">' . $html . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);
	libxml_clear_errors();
	return $dom;
}

/**
 * Devuelve el HTML interno del wrapper (sin el <div id="tcb-wrap">).
 *
 * @param DOMDocument $dom
 * @return string
 */
function tcb_dom_inner_html( DOMDocument $dom ) {
	$wrap  = $dom->getElementsByTagName( 'div' )->item( 0 );
	$inner = '';
	if ( $wrap ) {
		foreach ( $wrap->childNodes as $child ) {
			$inner .= $dom->saveHTML( $child );
		}
	}
	return $inner;
}

/**
 * Construye el árbol plano de items de la tabla a partir del contenido.
 *
 * @param string $content Contenido del post (markup de bloques o HTML).
 * @return array Lista de [ 'level' => int, 'text' => string, 'id' => string ].
 */
function tcb_build_map( $content ) {
	$items = array();
	if ( '' === trim( (string) $content ) ) {
		return $items;
	}

	$dom   = tcb_load_dom( $content );
	$xpath = new DOMXPath( $dom );
	$query = implode( '|', array_map( function ( $l ) { return '//h' . $l; }, TCB_LEVELS ) );
	$nodes = $xpath->query( $query );

	if ( ! $nodes ) {
		return $items;
	}

	$used = array();
	foreach ( $nodes as $node ) {
		$text = trim( preg_replace( '/\s+/', ' ', $node->textContent ) );
		if ( '' === $text ) {
			continue;
		}
		$existing = $node->getAttribute( 'id' );
		if ( '' !== $existing ) {
			$used[ $existing ] = true;
			$id                = $existing;
		} else {
			$id = tcb_make_id( $text, $used );
		}
		$items[] = array(
			'level' => (int) substr( $node->nodeName, 1 ),
			'text'  => $text,
			'id'    => $id,
		);
	}

	return $items;
}

/**
 * Añade IDs a los encabezados de un HTML ya renderizado, con el mismo
 * algoritmo que tcb_build_map().
 *
 * @param string $content
 * @return string
 */
function tcb_inject_ids( $content ) {
	if ( '' === trim( (string) $content ) ) {
		return $content;
	}

	$dom   = tcb_load_dom( $content );
	$xpath = new DOMXPath( $dom );
	$query = implode( '|', array_map( function ( $l ) { return '//h' . $l; }, TCB_LEVELS ) );
	$nodes = $xpath->query( $query );

	if ( ! $nodes || 0 === $nodes->length ) {
		return $content;
	}

	$used = array();
	foreach ( $nodes as $node ) {
		$existing = $node->getAttribute( 'id' );
		if ( '' !== $existing ) {
			$used[ $existing ] = true;
			continue;
		}
		$text = trim( preg_replace( '/\s+/', ' ', $node->textContent ) );
		if ( '' === $text ) {
			continue;
		}
		$node->setAttribute( 'id', tcb_make_id( $text, $used ) );
	}

	return tcb_dom_inner_html( $dom );
}

/**
 * Pinta la lista anidada (<ol>) a partir del árbol plano de items.
 *
 * @param array $items
 * @return string
 */
function tcb_render_list( array $items ) {
	if ( empty( $items ) ) {
		return '';
	}

	$base = min( array_map( function ( $i ) { return $i['level']; }, $items ) );
	$out  = '';
	$cur  = $base - 1;

	foreach ( $items as $it ) {
		$lvl = $it['level'];
		if ( $lvl > $cur ) {
			$out .= str_repeat( '<ul class="tcb-list">', $lvl - $cur );
		} else {
			$out .= '</li>';
			$out .= str_repeat( '</ul></li>', $cur - $lvl );
		}
		$cur  = $lvl;
		$out .= sprintf(
			'<li class="tcb-item tcb-level-%1$d"><a href="#%2$s">%3$s</a>',
			$lvl,
			esc_attr( $it['id'] ),
			esc_html( $it['text'] )
		);
	}

	$out .= '</li>';
	$out .= str_repeat( '</ul></li>', $cur - $base );
	$out .= '</ul>';

	return $out;
}

/**
 * Items de ejemplo para la vista previa en el editor (cuando no hay post real).
 *
 * @return array
 */
function tcb_demo_items() {
	return array(
		array( 'level' => 2, 'text' => __( 'Introducción', 'tabla-contenidos-bisiesto' ), 'id' => 'demo-intro' ),
		array( 'level' => 2, 'text' => __( 'Primer apartado', 'tabla-contenidos-bisiesto' ), 'id' => 'demo-1' ),
		array( 'level' => 3, 'text' => __( 'Subapartado', 'tabla-contenidos-bisiesto' ), 'id' => 'demo-1-1' ),
		array( 'level' => 2, 'text' => __( 'Conclusión', 'tabla-contenidos-bisiesto' ), 'id' => 'demo-fin' ),
	);
}

/**
 * Añade los IDs a los encabezados del contenido del post (bloque core/post-content).
 */
add_filter( 'render_block_core/post-content', function ( $content, $block ) {
	unset( $block );
	if ( ! is_singular( 'post' ) ) {
		return $content;
	}
	return tcb_inject_ids( $content );
}, 10, 2 );
