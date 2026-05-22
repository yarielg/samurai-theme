<?php
/**
 * Mega Menu — Walker that builds a full-width panel from the primary nav structure.
 *
 * Menu setup in WP Admin → Appearance → Menus:
 *   Level 0 — Shop (or any item with CSS class "sf-mega-menu")
 *   Level 1 — Column headers (e.g. "Category", "Effect") → link to taxonomy archive
 *   Level 2 — Term links under each column
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Mobile nav — builds accordion HTML from the primary nav menu tree.
// Mirrors the 3-level structure used by the desktop mega menu:
//   depth 0 → accordion trigger (or plain link if no children)
//   depth 1 → link inside accordion (or section header if it has children)
//   depth 2 → link under a section header
// ---------------------------------------------------------------------------
function samurai_mobile_nav_html(): string {
	$locations = get_nav_menu_locations();
	if ( empty( $locations['primary'] ) ) {
		return '';
	}

	$raw_items = wp_get_nav_menu_items( $locations['primary'] );
	if ( ! $raw_items ) {
		return '';
	}

	// Build parent → children map (parent_id => [items])
	$children_map = [];
	foreach ( $raw_items as $item ) {
		$children_map[ (int) $item->menu_item_parent ][] = $item;
	}

	// SVG chevron icon (same as existing mobile accordion)
	$icon_chevron = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"'
	                . ' fill="none" stroke="currentColor" stroke-width="2.5"'
	                . ' stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
	                . '<polyline points="6 9 12 15 18 9"/></svg>';

	ob_start();

	foreach ( $children_map[0] ?? [] as $item ) {
		$item_children = $children_map[ $item->ID ] ?? [];
		$url           = $item->url ?? '#';
		$title         = esc_html( $item->title );
		$has_link      = ! empty( $url ) && '#' !== trim( $url );
		$item_classes  = (array) $item->classes;
		$fire_icon     = in_array( 'fire-deals-item', $item_classes, true )
			? '<span class="sf-fire-icon" aria-hidden="true">&#x1F525;</span>'
			: '';

		if ( empty( $item_children ) ) {
			// Plain nav link
			if ( $has_link ) {
				echo '<a href="' . esc_url( $url ) . '" class="sf-mobile-nav-link">'
				     . $fire_icon . $title . '</a>';
			}
			continue;
		}

		// Accordion wrapper
		echo '<div class="sf-mobile-accordion">';
		echo '<button type="button" class="sf-mobile-accordion__trigger js-accordion-trigger" aria-expanded="false">';
		echo $fire_icon . $title;
		echo '<span class="sf-mobile-accordion__icon">' . $icon_chevron . '</span>';
		echo '</button>';
		echo '<div class="sf-mobile-accordion__panel" aria-hidden="true">';

		foreach ( $item_children as $child ) {
			$child_children = $children_map[ $child->ID ] ?? [];
			$child_url      = $child->url ?? '#';
			$child_title    = esc_html( $child->title );
			$child_has_link = ! empty( $child_url ) && '#' !== trim( $child_url );

			if ( empty( $child_children ) ) {
				// Regular sub-link
				if ( $child_has_link ) {
					echo '<a href="' . esc_url( $child_url ) . '" class="sf-mobile-nav-link">' . $child_title . '</a>';
				}
			} else {
				// Section header (column header from the mega menu at depth 1)
				echo '<div class="sf-mobile-nav-section">';
				if ( $child_has_link ) {
					echo '<a href="' . esc_url( $child_url ) . '" class="sf-mobile-nav-section-label">' . $child_title . '</a>';
				} else {
					echo '<span class="sf-mobile-nav-section-label">' . $child_title . '</span>';
				}
				foreach ( $child_children as $grandchild ) {
					$gc_url = $grandchild->url ?? '#';
					if ( ! empty( $gc_url ) && '#' !== trim( $gc_url ) ) {
						echo '<a href="' . esc_url( $gc_url ) . '" class="sf-mobile-nav-link">'
						     . esc_html( $grandchild->title )
						     . '</a>';
					}
				}
				echo '</div>'; // .sf-mobile-nav-section
			}
		}

		// "See all" link at the bottom of the accordion panel
		if ( $has_link ) {
			echo '<a href="' . esc_url( $url ) . '" class="sf-mobile-nav-link sf-mobile-nav-link--all">'
			     . sprintf( esc_html__( 'All %s →', 'samurai' ), $title )
			     . '</a>';
		}

		echo '</div>'; // .sf-mobile-accordion__panel
		echo '</div>'; // .sf-mobile-accordion
	}

	return ob_get_clean();
}

// ---------------------------------------------------------------------------
// Desktop mega menu Walker
// ---------------------------------------------------------------------------
class SF_Primary_Nav_Walker extends Walker_Nav_Menu {

	/** True while processing descendants of the mega trigger item. */
	private bool $in_mega = false;

	/**
	 * Detect the mega trigger: an item with CSS class "sf-mega-menu"
	 * OR the WooCommerce Shop page item.
	 */
	private function is_mega_item( object $item ): bool {
		if ( in_array( 'sf-mega-menu', (array) $item->classes, true ) ) {
			return true;
		}
		if ( function_exists( 'wc_get_page_id' ) ) {
			$shop_id = (int) wc_get_page_id( 'shop' );
			if ( $shop_id > 0 && (int) $item->object_id === $shop_id ) {
				return true;
			}
		}
		return false;
	}

	private function has_real_url( string $url ): bool {
		$url = trim( $url );
		return '' !== $url && '#' !== $url;
	}

	// ── start_el ──────────────────────────────────────────────────────────────

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ): void {
		// Tag the mega trigger at depth 0 and flip state.
		if ( $depth === 0 && $this->is_mega_item( $item ) ) {
			$item->classes[] = 'menu-item--mega';
			$this->in_mega   = true;
		}

		// Inside the mega panel, render columns (depth 1) and term links (depth 2).
		if ( $this->in_mega && $depth > 0 ) {
			if ( $depth === 1 ) {
				$url = $item->url ?? '#';
				$output .= '<div class="sf-mega-panel__col">';
				if ( $this->has_real_url( $url ) ) {
					$output .= '<a href="' . esc_url( $url ) . '" class="sf-mega-panel__col-label">'
					           . esc_html( $item->title ) . '</a>';
				} else {
					$output .= '<span class="sf-mega-panel__col-label">'
					           . esc_html( $item->title ) . '</span>';
				}
			} elseif ( $depth === 2 ) {
				$img_html = '';
				if ( function_exists( 'get_field' ) && 'taxonomy' === $item->type && ! empty( $item->object_id ) ) {
					$acf_img = get_field( 'image', 'term_' . (int) $item->object_id );
					if ( $acf_img ) {
						$img_src = is_array( $acf_img ) ? ( $acf_img['url'] ?? '' ) : (string) $acf_img;
						$img_alt = is_array( $acf_img ) ? ( $acf_img['alt'] ?? '' ) : '';
						if ( $img_src ) {
							$img_html = '<img src="' . esc_url( $img_src ) . '" alt="' . esc_attr( $img_alt ) . '"'
							            . ' class="sf-mega-panel__term-img" width="22" height="22" loading="lazy">';
						}
					}
				}
				$link_class = $img_html ? 'sf-mega-panel__link sf-mega-panel__link--has-img' : 'sf-mega-panel__link';
				$output .= '<li>'
				           . '<a href="' . esc_url( $item->url ) . '" class="' . $link_class . '">'
				           . $img_html
				           . '<span>' . esc_html( $item->title ) . '</span>'
				           . '</a>'
				           . '</li>';
			}
			// depth >= 3 ignored (depth param in wp_nav_menu limits this anyway)
			return;
		}

		// Normal items (including the Shop trigger itself at depth 0).
		parent::start_el( $output, $item, $depth, $args, $id );
	}

	// ── end_el ────────────────────────────────────────────────────────────────

	public function end_el( &$output, $item, $depth = 0, $args = null ): void {
		if ( $this->in_mega ) {
			if ( $depth === 1 ) {
				// Optionally add a "see all" link then close the column.
				$url = $item->url ?? '#';
				if ( $this->has_real_url( $url ) ) {
					$output .= '<a href="' . esc_url( $url ) . '" class="sf-mega-panel__see-all">'
					           . sprintf(
						           /* translators: %s: label name */
						           esc_html__( 'All %s →', 'samurai' ),
						           esc_html( $item->title )
					           )
					           . '</a>';
				}
				$output .= '</div>'; // .sf-mega-panel__col
				return;
			}
			if ( $depth === 2 ) {
				return; // <li> self-closed in start_el
			}
		}
		parent::end_el( $output, $item, $depth, $args );
	}

	// ── start_lvl ─────────────────────────────────────────────────────────────

	public function start_lvl( &$output, $depth = 0, $args = null ): void {
		if ( $this->in_mega ) {
			if ( $depth === 0 ) {
				// Sub-tree of the mega trigger: open the panel shell.
				$output .= '<div class="sf-mega-panel" id="sf-mega-panel" role="region"'
				           . ' aria-label="' . esc_attr__( 'Shop categories', 'samurai' ) . '">';
				$output .= '<div class="sf-mega-panel__inner sf-container">';
				$output .= '<div class="sf-mega-panel__grid">';
				return;
			}
			if ( $depth === 1 ) {
				// Sub-tree of a column header: open the term list.
				$output .= '<ul class="sf-mega-panel__list" role="list">';
				return;
			}
			return; // depth >= 2: suppress
		}
		parent::start_lvl( $output, $depth, $args );
	}

	// ── end_lvl ───────────────────────────────────────────────────────────────

	public function end_lvl( &$output, $depth = 0, $args = null ): void {
		if ( $this->in_mega ) {
			if ( $depth === 1 ) {
				$output .= '</ul>'; // .sf-mega-panel__list
				return;
			}
			if ( $depth === 0 ) {
				// All columns rendered — close grid, add footer, close panel.
				$shop_url = function_exists( 'wc_get_page_permalink' )
					? wc_get_page_permalink( 'shop' )
					: home_url( '/shop/' );

				$output .= '</div>'; // .sf-mega-panel__grid

				$output .= '<div class="sf-mega-panel__footer">'
				           . '<a href="' . esc_url( $shop_url ) . '" class="sf-mega-panel__footer-link">'
				           . '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
				           . ' stroke-width="2.5" stroke-linecap="round" aria-hidden="true">'
				           . '<path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>'
				           . '<line x1="3" y1="6" x2="21" y2="6"/>'
				           . '<path d="M16 10a4 4 0 01-8 0"/>'
				           . '</svg>'
				           . esc_html__( 'Browse All Products', 'samurai' )
				           . '</a>'
				           . '</div>'; // .sf-mega-panel__footer

				$output .= '</div>'; // .sf-mega-panel__inner
				$output .= '</div>'; // .sf-mega-panel

				$this->in_mega = false;
				return;
			}
			return; // depth >= 2: suppress
		}
		parent::end_lvl( $output, $depth, $args );
	}
}
