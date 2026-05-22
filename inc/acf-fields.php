<?php
/**
 * ACF local field group definitions.
 * Registered programmatically so they appear without manual UI steps.
 * Fields added here are editable via WP Admin → Theme Settings sub-pages.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'samurai_register_acf_fields' );

function samurai_register_acf_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// =========================================================================
	// Hero Slider — Theme Settings → Header & Promo
	// =========================================================================
	acf_add_local_field_group( [
		'key'      => 'group_samurai_hero_slider',
		'title'    => 'Hero Slider',
		'fields'   => [

			[
				'key'          => 'field_hero_slides',
				'label'        => 'Hero Slides',
				'name'         => 'hero_slides',
				'type'         => 'repeater',
				'min'          => 0,
				'max'          => 8,
				'layout'       => 'row',
				'button_label' => 'Add Slide',
				'sub_fields'   => [

					[
						'key'           => 'field_slide_image',
						'label'         => 'Desktop Image',
						'name'          => 'slide_image',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
						'instructions'  => 'Desktop/tablet background. Recommended: 1920 × 900 px (landscape), JPEG, under 300 KB.',
						'column_width'  => '',
					],

					[
						'key'           => 'field_slide_image_mobile',
						'label'         => 'Mobile Image (optional)',
						'name'          => 'slide_image_mobile',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
						'instructions'  => 'Portrait image for phones. Recommended: 750 × 1200 px, JPEG, under 200 KB. Falls back to desktop image if left blank.',
						'column_width'  => '',
					],

					[
						'key'          => 'field_slide_headline',
						'label'        => 'Headline',
						'name'         => 'slide_headline',
						'type'         => 'text',
						'placeholder'  => 'e.g. Light Up Miami',
						'instructions' => 'Main large heading shown on the slide.',
					],

					[
						'key'          => 'field_slide_subline',
						'label'        => 'Sub-line',
						'name'         => 'slide_subline',
						'type'         => 'text',
						'placeholder'  => 'e.g. Ignite Every Moment. Miami Style.',
						'instructions' => 'Smaller text shown above the headline.',
					],

					[
						'key'         => 'field_slide_cta_label',
						'label'       => 'Button Label',
						'name'        => 'slide_cta_label',
						'type'        => 'text',
						'placeholder' => 'e.g. Shop Now',
					],

					[
						'key'  => 'field_slide_cta_url',
						'label' => 'Button URL',
						'name'  => 'slide_cta_url',
						'type'  => 'url',
					],

					[
						'key'          => 'field_slide_cta_target',
						'label'        => 'Open in New Tab',
						'name'         => 'slide_cta_target',
						'type'         => 'true_false',
						'default_value' => 0,
						'ui'           => 1,
					],

				],
			],

		],
		'location' => [
			[
				[
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'samurai-header-promo',
				],
			],
		],
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
	] );


	// =========================================================================
	// Brand & Colors — Theme Settings → Brand & Colors
	// =========================================================================
	acf_add_local_field_group( [
		'key'    => 'group_samurai_brand_colors',
		'title'  => 'Brand & Colors',
		'fields' => [

			[
				'key'          => 'field_brand_color_primary',
				'label'        => 'Primary Color',
				'name'         => 'brand_color_primary',
				'type'         => 'color_picker',
				'default_value' => '#C0392B',
				'instructions' => 'Main CTA button and badge color.',
			],
			[
				'key'          => 'field_brand_color_secondary',
				'label'        => 'Secondary / Gold Color',
				'name'         => 'brand_color_secondary',
				'type'         => 'color_picker',
				'default_value' => '#F39C12',
				'instructions' => 'Accent highlights, sale badges.',
			],
			[
				'key'          => 'field_brand_color_accent',
				'label'        => 'Accent / Aqua Color',
				'name'         => 'brand_color_accent',
				'type'         => 'color_picker',
				'default_value' => '#1ABC9C',
				'instructions' => 'Miami accent — hover states, links.',
			],
			[
				'key'          => 'field_header_nav_bg_color',
				'label'        => 'Header / Nav Background Color',
				'name'         => 'header_nav_bg_color',
				'type'         => 'color_picker',
				'default_value' => '#C0392B',
				'instructions' => 'Used on non-homepage pages and when scrolled. Defaults to Primary Color.',
			],
			[
				'key'          => 'field_brand_tagline',
				'label'        => 'Brand Tagline',
				'name'         => 'brand_tagline',
				'type'         => 'text',
				'placeholder'  => 'Ignite Every Moment. Miami Style.',
				'instructions' => 'Shown in the footer and used as hero fallback sub-line.',
			],

		],
		'location' => [
			[
				[
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'samurai-brand-colors',
				],
			],
		],
		'menu_order' => 0,
		'position'   => 'normal',
		'active'     => true,
	] );


	// =========================================================================
	// Contact & Hours — Theme Settings → Contact & Hours
	// =========================================================================
	acf_add_local_field_group( [
		'key'    => 'group_samurai_contact',
		'title'  => 'Contact & Hours',
		'fields' => [

			[
				'key'         => 'field_contact_phone',
				'label'       => 'Phone',
				'name'        => 'contact_phone',
				'type'        => 'text',
				'placeholder' => '(305) 555-0100',
			],
			[
				'key'         => 'field_contact_email',
				'label'       => 'Email',
				'name'        => 'contact_email',
				'type'        => 'email',
			],
			[
				'key'         => 'field_contact_address',
				'label'       => 'Store Address',
				'name'        => 'contact_address',
				'type'        => 'text',
				'placeholder' => '1234 Biscayne Blvd, Miami, FL 33137',
			],
			[
				'key'         => 'field_contact_hours',
				'label'       => 'Store Hours',
				'name'        => 'contact_hours',
				'type'        => 'text',
				'placeholder' => 'Mon – Sat 9am – 9pm',
			],
			[
				'key'   => 'field_social_facebook',
				'label' => 'Facebook URL',
				'name'  => 'social_facebook',
				'type'  => 'url',
			],
			[
				'key'   => 'field_social_instagram',
				'label' => 'Instagram URL',
				'name'  => 'social_instagram',
				'type'  => 'url',
			],
			[
				'key'   => 'field_social_youtube',
				'label' => 'YouTube URL',
				'name'  => 'social_youtube',
				'type'  => 'url',
			],

		],
		'location' => [
			[
				[
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'samurai-contact',
				],
			],
		],
		'menu_order' => 0,
		'position'   => 'normal',
		'active'     => true,
	] );


	// =========================================================================
	// Footer — Theme Settings → Footer
	// =========================================================================
	acf_add_local_field_group( [
		'key'    => 'group_samurai_footer',
		'title'  => 'Footer',
		'fields' => [

			[
				'key'         => 'field_footer_description',
				'label'       => 'Footer Description',
				'name'        => 'footer_description',
				'type'        => 'textarea',
				'rows'        => 3,
				'placeholder' => 'Miami\'s favorite fireworks store since…',
			],
			[
				'key'         => 'field_footer_legal_copy',
				'label'       => 'Legal / Copyright Text',
				'name'        => 'footer_legal_copy',
				'type'        => 'text',
				'placeholder' => '© 2025 Samurai Fireworks. All rights reserved.',
				'instructions' => 'Leave blank to use the auto-generated copyright line.',
			],
			[
				'key'         => 'field_trust_item_1',
				'label'       => 'Trust Badge 1',
				'name'        => 'trust_item_1',
				'type'        => 'text',
				'default_value' => 'Secure Checkout',
			],
			[
				'key'         => 'field_trust_item_2',
				'label'       => 'Trust Badge 2',
				'name'        => 'trust_item_2',
				'type'        => 'text',
				'default_value' => 'Fast Local Pickup',
			],
			[
				'key'         => 'field_trust_item_3',
				'label'       => 'Trust Badge 3',
				'name'        => 'trust_item_3',
				'type'        => 'text',
				'default_value' => 'Licensed & Insured',
			],
			[
				'key'         => 'field_trust_item_4',
				'label'       => 'Trust Badge 4',
				'name'        => 'trust_item_4',
				'type'        => 'text',
				'default_value' => "Miami's #1 Store",
			],

		],
		'location' => [
			[
				[
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'samurai-footer',
				],
			],
		],
		'menu_order' => 0,
		'position'   => 'normal',
		'active'     => true,
	] );


	// =========================================================================
	// Homepage — Theme Settings → Homepage
	// =========================================================================
	acf_add_local_field_group( [
		'key'    => 'group_samurai_homepage',
		'title'  => 'Homepage Sections',
		'fields' => [

			// Section: Categories
			[
				'key'          => 'field_home_categories_heading',
				'label'        => 'Categories Section Heading',
				'name'         => 'home_categories_heading',
				'type'         => 'text',
				'default_value' => 'Shop by Category',
				'instructions' => 'Heading above the product category grid.',
			],

			// Section: Featured products
			[
				'key'          => 'field_home_featured_heading',
				'label'        => 'Featured Products Heading',
				'name'         => 'home_featured_heading',
				'type'         => 'text',
				'default_value' => 'Trending Now',
				'instructions' => 'Heading above the featured products grid.',
			],
			[
				'key'          => 'field_home_featured_ids',
				'label'        => 'Featured Products (optional override)',
				'name'         => 'home_featured_ids',
				'type'         => 'post_object',
				'post_type'    => [ 'product' ],
				'return_format' => 'id',
				'multiple'     => 1,
				'allow_null'   => 1,
				'instructions' => 'Leave blank to use WooCommerce "featured" products automatically. Select products here to override.',
			],

			// Section: Fire Deals
			[
				'key'          => 'field_home_deals_heading',
				'label'        => 'Fire Deals Section Heading',
				'name'         => 'home_deals_heading',
				'type'         => 'text',
				'default_value' => '🔥 Fire Deals',
				'instructions' => 'Heading above the Fire Deals section.',
			],
			[
				'key'          => 'field_home_deals_subline',
				'label'        => 'Fire Deals Sub-line',
				'name'         => 'home_deals_subline',
				'type'         => 'text',
				'default_value' => 'Limited-time deals on our best sellers.',
				'instructions' => 'Short tagline under the Fire Deals heading.',
			],

			// Section: Brands
			[
				'key'          => 'field_home_brands_heading',
				'label'        => 'Brands Section Heading',
				'name'         => 'home_brands_heading',
				'type'         => 'text',
				'default_value' => 'Shop by Brand',
			],

			// Section: Effects
			[
				'key'          => 'field_home_effects_heading',
				'label'        => 'Effects Section Heading',
				'name'         => 'home_effects_heading',
				'type'         => 'text',
				'default_value' => 'Shop by Effect',
			],

			// Section: Occasions
			[
				'key'          => 'field_home_occasions_heading',
				'label'        => 'Occasions Section Heading',
				'name'         => 'home_occasions_heading',
				'type'         => 'text',
				'default_value' => 'Shop by Occasion',
			],

			// Section: Category Carousel
			[
				'key'          => 'field_home_carousel_heading',
				'label'        => 'Category Carousel Heading',
				'name'         => 'home_carousel_heading',
				'type'         => 'text',
				'default_value' => 'Shop the Collection',
				'instructions' => 'Heading above the horizontal product carousel.',
			],
			[
				'key'          => 'field_home_carousel_category',
				'label'        => 'Carousel Category',
				'name'         => 'home_carousel_category',
				'type'         => 'taxonomy',
				'taxonomy'     => 'product_cat',
				'field_type'   => 'select',
				'return_format' => 'id',
				'allow_null'   => 1,
				'multiple'     => 0,
				'instructions' => 'Select which product category to display in the carousel. Leave blank for latest products.',
			],
			[
				'key'          => 'field_home_carousel_count',
				'label'        => 'Carousel Product Count',
				'name'         => 'home_carousel_count',
				'type'         => 'number',
				'default_value' => 12,
				'min'          => 4,
				'max'          => 24,
				'instructions' => 'How many products to show (4–24).',
			],

			// Section: Local pickup
			[
				'key'          => 'field_home_pickup_heading',
				'label'        => 'Pickup Section Heading',
				'name'         => 'home_pickup_heading',
				'type'         => 'text',
				'default_value' => 'Local Pickup in Miami, FL',
			],
			[
				'key'          => 'field_home_pickup_subline',
				'label'        => 'Pickup Section Sub-line',
				'name'         => 'home_pickup_subline',
				'type'         => 'text',
				'default_value' => 'Order online and pick up at our warehouse — same day, no shipping fee.',
			],
			[
				'key'          => 'field_home_pickup_cta_label',
				'label'        => 'Pickup CTA Button Label',
				'name'         => 'home_pickup_cta_label',
				'type'         => 'text',
				'default_value' => 'Get Directions',
			],
			[
				'key'          => 'field_home_pickup_cta_url',
				'label'        => 'Pickup CTA URL (map link)',
				'name'         => 'home_pickup_cta_url',
				'type'         => 'url',
				'instructions' => 'Google Maps or directions link for the store.',
			],

		],
		'location' => [
			[
				[
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'samurai-homepage',
				],
			],
		],
		'menu_order' => 0,
		'position'   => 'normal',
		'active'     => true,
	] );


	// =========================================================================
	// Cart & Checkout — Theme Settings → Cart & Checkout
	// =========================================================================
	acf_add_local_field_group( [
		'key'    => 'group_samurai_cart_checkout',
		'title'  => 'Cart & Checkout',
		'fields' => [

			[
				'key'           => 'field_cart_empty_message',
				'label'         => 'Empty Cart Heading',
				'name'          => 'cart_empty_message',
				'type'          => 'text',
				'default_value' => 'Your cart is empty',
				'instructions'  => 'Large heading shown when the cart has no items.',
			],
			[
				'key'           => 'field_cart_empty_subtext',
				'label'         => 'Empty Cart Sub-text',
				'name'          => 'cart_empty_subtext',
				'type'          => 'textarea',
				'rows'          => 2,
				'default_value' => "Looks like you haven't added anything yet. Browse our fireworks and light up your next celebration!",
				'instructions'  => 'Paragraph shown below the empty cart heading.',
			],
			[
				'key'           => 'field_cart_empty_cta_label',
				'label'         => 'Empty Cart CTA Button Label',
				'name'          => 'cart_empty_cta_label',
				'type'          => 'text',
				'default_value' => 'Shop Fireworks',
			],
			[
				'key'          => 'field_cart_empty_cta_url',
				'label'        => 'Empty Cart CTA URL',
				'name'         => 'cart_empty_cta_url',
				'type'         => 'url',
				'instructions' => 'Defaults to the WooCommerce Shop page if left blank.',
			],
			[
				'key'           => 'field_cart_pickup_note',
				'label'         => 'Pickup Note (Order Summary)',
				'name'          => 'cart_pickup_note',
				'type'          => 'text',
				'default_value' => 'Available for local Miami pickup',
				'instructions'  => 'Short note in the cart trust panel. Leave blank to hide the row.',
			],
			[
				'key'          => 'field_cart_support_message',
				'label'        => 'Support Message (Order Summary)',
				'name'         => 'cart_support_message',
				'type'         => 'text',
				'instructions' => 'Optional extra note in the order summary trust panel. Leave blank to hide.',
			],

		],
		'location' => [
			[
				[
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'samurai-cart-checkout',
				],
			],
		],
		'menu_order' => 0,
		'position'   => 'normal',
		'active'     => true,
	] );


	// =========================================================================
	// Header & Promo — search placeholder
	// =========================================================================
	acf_add_local_field_group( [
		'key'    => 'group_samurai_header_promo',
		'title'  => 'Header Settings',
		'fields' => [

			[
				'key'          => 'field_search_placeholder',
				'label'        => 'Search Placeholder Text',
				'name'         => 'search_placeholder',
				'type'         => 'text',
				'placeholder'  => 'Search fireworks, brands, effects…',
				'instructions' => 'Text shown inside the search box.',
			],

		],
		'location' => [
			[
				[
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'samurai-header-promo',
				],
			],
		],
		'menu_order' => 10,
		'position'   => 'normal',
		'active'     => true,
	] );
}
