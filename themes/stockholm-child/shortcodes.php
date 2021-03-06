<?php

/* Portfolio list shortcode */

if (!function_exists('portfolio_list')) {

	function portfolio_list($atts, $content = null) {

		global $wp_query;
		global $qode_options;
		$portfolio_qode_like = "on";
		if (isset($qode_options['portfolio_qode_like'])) {
			$portfolio_qode_like = $qode_options['portfolio_qode_like'];
		}

		$portfolio_list_hide_category = false;
		if (isset($qode_options['portfolio_list_hide_category']) && $qode_options['portfolio_list_hide_category'] == "yes") {
			$portfolio_list_hide_category = true;
		}

		$portfolio_filter_class = "";
		if (isset($qode_options['portfolio_filter_disable_separator']) && !empty($qode_options['portfolio_filter_disable_separator'])) {
			if($qode_options['portfolio_filter_disable_separator'] == "yes"){
				$portfolio_filter_class = "without_separator";
			} else {
				$portfolio_filter_class = "";
			}
		}

		$args = array(
			"type"                  	=> "standard",
			"hover_type"            	=> "default_hover",
			"box_border"            	=> "",
			"box_background_color" 		=> "",		
			"box_border_color"      	=> "",
			"box_border_width"      	=> "",
			"columns"               	=> "3",
			"image_size"            	=> "",
			"order_by"              	=> "date",
			"order"                 	=> "ASC",
			"number"                	=> "-1",
			"filter"                	=> "no",
			"filter_order_by"           => "name",
			"disable_filter_title"      => "no",
			"filter_align"          	=> "left_align",
			"disable_link"          	=> "no",
			"lightbox"             		=> "yes",
			"show_like"             	=> "yes",
			"category"              	=> "",
			"selected_projects"     	=> "",
			"show_load_more"        	=> "yes",
			"title_tag"             	=> "h4",
			"title_font_size"       	=> "",
			"text_align"            	=> ""
		);

		extract(shortcode_atts($args, $atts));

		$headings_array = array('h2', 'h3', 'h4', 'h5', 'h6');

		//get correct heading value. If provided heading isn't valid get the default one
		$title_tag = (in_array($title_tag, $headings_array)) ? $title_tag : $args['title_tag'];

		$html = "";

		$_type_class = '';
		$_portfolio_space_class = '';
		$_portfolio_masonry_with_space_class = '';
		if ($type == "hover_text") {
			$_type_class = " hover_text";
			$_portfolio_space_class = "portfolio_with_space portfolio_with_hover_text";
		} elseif ($type == "standard" || $type == "masonry_with_space"){
			$_type_class = " standard";
			$_portfolio_space_class = "portfolio_with_space portfolio_standard";
			if($type == "masonry_with_space"){
				$_portfolio_masonry_with_space_class = ' masonry_with_space';
			}
		} elseif ($type == "standard_no_space"){
			$_type_class = " standard_no_space";
			$_portfolio_space_class = "portfolio_no_space portfolio_standard";
		} elseif ($type == "hover_text_no_space"){
			$_type_class = " hover_text no_space";
			$_portfolio_space_class = "portfolio_no_space portfolio_with_hover_text";
		}

		$portfolio_box_style = "";
		$portfolio_description_class = "";

		if($box_border == "yes" || $box_background_color != ""){

			$portfolio_box_style .= "style=";
			if($box_border == "yes"){
				$portfolio_box_style .= "border-style:solid;";
				if($box_border_color != "" ){
					$portfolio_box_style .= "border-color:" . $box_border_color . ";";
				}
				if($box_border_width != "" ){
					$portfolio_box_style .= "border-width:" . $box_border_width . "px;";
				} else {
					$portfolio_box_style .= "border-width: 1px;";
				}
			}
			if($box_background_color != ""){
				$portfolio_box_style .= "background-color:" . $box_background_color . ";";
			}
			$portfolio_box_style .= "'";

			$portfolio_description_class .= 'with_padding';

			$_portfolio_space_class = ' with_description_background';

		}

		if($text_align !== '') {
			$portfolio_description_class .= ' text_align_'.$text_align;
		}

		if($type != 'masonry') {
			$html .= "<div class='projects_holder_outer v$columns $_portfolio_space_class $_portfolio_masonry_with_space_class'>";
			if ($filter == "yes") {
				$html .= "<div class='filter_outer ".$filter_align."'>";
					$html .= "<div class='filter_holder ".$portfolio_filter_class."'><ul>";
						if($disable_filter_title != "yes"){
							$html .= "<li class='filter_title'><span>".__('FILTER PUBLICATIONS:', 'qode')."</span></li>";
						}
						if($type == 'masonry_with_space'){
							$html .= "<li class='filter' data-filter='*'><span>" . __('All', 'qode') . "</span></li>";
						} else {
							$html .= "<li class='filter' data-filter='all'><span>" . __('All', 'qode') . "</span></li>";
						}
						
					if ($category == "") {
						$args = array(
							'parent' => 0,
							'orderby' => $filter_order_by
						);
						$portfolio_categories = get_terms('portfolio_category', $args);
					} else {
						$top_category = get_term_by('slug', $category, 'portfolio_category');
						$term_id = '';
						if (isset($top_category->term_id))
							$term_id = $top_category->term_id;
						$args = array(
							'parent' => $term_id,
							'orderby' => $filter_order_by
						);
						$portfolio_categories = get_terms('portfolio_category', $args);
					}
					foreach ($portfolio_categories as $portfolio_category) {
						if($type == 'masonry_with_space'){
							$html .= "<li class='filter' data-filter='.portfolio_category_$portfolio_category->term_id'><span>$portfolio_category->name</span>";
						} else {
							$html .= "<li class='filter' data-filter='portfolio_category_$portfolio_category->term_id'><span>$portfolio_category->name</span>";
						}
						$args = array(
							'child_of' => $portfolio_category->term_id
						);
						$html .= '</li>';
					}
					$html .= "</ul></div>";
				$html .= "</div>";
			}

			$thumb_size_class = "";
			//get proper image size
			switch($image_size) {
				case 'landscape':
					$thumb_size_class = 'portfolio_landscape_image';
					break;
				case 'portrait':
					$thumb_size_class = 'portfolio_portrait_image';
					break;
				case 'square':
					$thumb_size_class = 'portfolio_square_image';
					break;
				case 'full':
					$thumb_size_class = 'portfolio_full_image';
					break;
				default:
					$thumb_size_class = 'portfolio_default_image';
					break;
			}

			$html .= "<div class='projects_holder clearfix v$columns$_type_class $thumb_size_class'>\n";
			if (get_query_var('paged')) {
				$paged = get_query_var('paged');
			} elseif (get_query_var('page')) {
				$paged = get_query_var('page');
			} else {
				$paged = 1;
			}
			if ($category == "") {
				$args = array(
					'post_type' => 'portfolio_page',
					'orderby' => $order_by,
					'order' => $order,
					'posts_per_page' => $number,
					'paged' => $paged
				);
			} else {
				$args = array(
					'post_type' => 'portfolio_page',
					'portfolio_category' => $category,
					'orderby' => $order_by,
					'order' => $order,
					'posts_per_page' => $number,
					'paged' => $paged
				);
			}
			$project_ids = null;
			if ($selected_projects != "") {
				$project_ids = explode(",", $selected_projects);
				$args['post__in'] = $project_ids;
			}
			query_posts($args);
			if (have_posts()) : while (have_posts()) : the_post();
				$terms = wp_get_post_terms(get_the_ID(), 'portfolio_category');
				//Get subtitle 
	    		$subtitle = get_post_meta( get_the_ID(), '_subtitle', true );

				$html .= "<article class='mix ";
				foreach ($terms as $term) {
					$html .= "portfolio_category_$term->term_id ";
				}

				$title = get_the_title();
				$featured_image_array = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); //original size

				if(get_post_meta(get_the_ID(), 'qode_portfolio-lightbox-link', true) != ""){
					$large_image = get_post_meta(get_the_ID(), 'qode_portfolio-lightbox-link', true);
				} else {
					$large_image = $featured_image_array[0];
				}

				$slug_list_ = "pretty_photo_gallery";

				//get proper image size
				switch($image_size) {
					case 'landscape':
						$thumb_size = 'portfolio-landscape';
						break;
					case 'portrait':
						$thumb_size = 'portfolio-portrait';
						break;
					case 'square':
						$thumb_size = 'portfolio-square';
						break;
					case 'full':
						$thumb_size = 'full';
						break;
					default:
						$thumb_size = 'portfolio-default';
						break;
				}

				if($type == "masonry_with_space"){
					$thumb_size = 'portfolio_masonry_with_space';
				}

				$custom_portfolio_link = get_post_meta(get_the_ID(), 'qode_portfolio-external-link', true);
				$portfolio_link = $custom_portfolio_link != "" ? $custom_portfolio_link : get_permalink();

				if(get_post_meta(get_the_ID(), 'qode_portfolio-external-link-target', true) != ""){
					$custom_portfolio_link_target = get_post_meta(get_the_ID(), 'qode_portfolio-external-link-target', true);
				} else {
					$custom_portfolio_link_target = '_blank';
				}

				$target = $custom_portfolio_link != "" ? $custom_portfolio_link_target : '_self';

				$html .="'>";

				$html .= "<div class='image_holder ".$hover_type."'>";
					// IMAGE
					//$html .= "<span class='image'>";
					//$html .= get_the_post_thumbnail(get_the_ID(), $thumb_size);
					//$html .= "</span>";
				 	// END IMAGE

					if ($type == "standard" || $type == "standard_no_space" || $type == "masonry_with_space") {

						if($disable_link != "yes"){
							$html .= "<a class='portfolio_link_class' href='" . $portfolio_link . "' target='".$target."'></a>";
						}

						$html .= '<div class="portfolio_shader"></div>';

						$html .= '<div class="icons_holder"><div class="icons_holder_inner">';
							if ($lightbox == "yes") {
								$html .= "<a class='portfolio_lightbox' title='" . $title . "' href='" . $large_image . "' data-rel='prettyPhoto[" . $slug_list_ . "]'></a>";
							}

							if ($portfolio_qode_like == "on" && $show_like == "yes") {
								if (function_exists('qode_like_portfolio_list')) {
									$html .= qode_like_portfolio_list(get_the_ID());
								}
							}
						$html .= "</div></div>";

					} 


				$html .= "</div>";

				if ($type == "standard" || $type == "standard_no_space" || $type == "masonry_with_space") {
					$html .= "<div class='portfolio_description ".$portfolio_description_class."'". $portfolio_box_style .">";
						
						$title_style = '';
						if($title_font_size != ""){
							$title_style = 'style="font-size: '.$title_font_size.'px;"';
						}

						if($disable_link != "yes"){
							$html .= '<'.$title_tag.' class="portfolio_title" '.$title_style.'><a href="' . $portfolio_link . '" target="'.$target.'">' . get_the_title() . '</a></'.$title_tag.'>';
						} else {
							$html .= '<'.$title_tag.' class="portfolio_title" '.$title_style.'>' . get_the_title() . '</'.$title_tag.'>';
						}
					
						$subtitle = (strlen($subtitle) >= 70) ? substr($subtitle,0,69) . "..."  : $subtitle;
						$html .= "<span class='portfolio_subtitle'><span>" . $subtitle . "</span></span>";

						if(!$portfolio_list_hide_category){
							$html .= '<span class="project_category">';
								$html .= '<span>'. __('In ', 'qode') .'</span>';
								$k = 1;
								foreach ($terms as $term) {
									$html .= "$term->name";
									if (count($terms) != $k) {
										$html .= ', ';
									}
									$k++;
								}
							$html .= '</span>';
						}
					$html .= '</div>';
				}

				$html .= "</article>\n";

			endwhile;

				$i = 1;
				while ($i <= $columns) {
					$i++;
					if ($columns != 1) {
						$html .= "<div class='filler'></div>\n";
					}
				}

			else:
				?>
				<p><?php _e('Sorry, no posts matched your criteria.', 'qode'); ?></p>
			<?php
			endif;


			$html .= "</div>";
			if (get_next_posts_link()) {
				if ($show_load_more == "yes" || $show_load_more == "") {
					$html .= '<div class="portfolio_paging"><span rel="' . $wp_query->max_num_pages . '" class="load_more">' . get_next_posts_link(__('Show more', 'qode')) . '</span></div>';
					$html .= '<div class="portfolio_paging_loading"><a href="javascript: void(0)" class="qbutton">'.__('Loading...', 'qode').'</a></div>';
				}
			}
			$html .= "</div>";
			wp_reset_query();
		} else {
			if ($filter == "yes") {

				$html .= "<div class='filter_outer ".$filter_align."'>";
				$html .= "<div class='filter_holder ".$portfolio_filter_class."'><ul>";
				if($disable_filter_title != "yes"){		
					$html .= "<li class='filter_title'><span>".__('Sort Portfolio:', 'qode')."</span></li>";
				}	
				$html .= "<li class='filter' data-filter='*'><span>" . __('All', 'qode') . "</span></li>";
				if ($category == "") {
					$args = array(
						'parent' => 0,
						'orderby' => $filter_order_by
					);
					$portfolio_categories = get_terms('portfolio_category', $args);
				} else {
					$top_category = get_term_by('slug', $category, 'portfolio_category');
					$term_id = '';
					if (isset($top_category->term_id))
						$term_id = $top_category->term_id;
					$args = array(
						'parent' => $term_id,
						'orderby' => $filter_order_by
					);
					$portfolio_categories = get_terms('portfolio_category', $args);
				}
				foreach ($portfolio_categories as $portfolio_category) {
					$html .= "<li class='filter' data-filter='.portfolio_category_$portfolio_category->term_id'><span>$portfolio_category->name</span>";
					$args = array(
						'child_of' => $portfolio_category->term_id
					);
					$html .= '</li>';
				}
				$html .= "</ul></div>";
				$html .= "</div>";


			}
			$html .= "<div class='projects_masonry_holder'>";
			if (get_query_var('paged')) {
				$paged = get_query_var('paged');
			} elseif (get_query_var('page')) {
				$paged = get_query_var('page');
			} else {
				$paged = 1;
			}
			if ($category == "") {
				$args = array(
					'post_type' => 'portfolio_page',
					'orderby' => $order_by,
					'order' => $order,
					'posts_per_page' => $number,
					'paged' => $paged
				);
			} else {
				$args = array(
					'post_type' => 'portfolio_page',
					'portfolio_category' => $category,
					'orderby' => $order_by,
					'order' => $order,
					'posts_per_page' => $number,
					'paged' => $paged
				);
			}
			$project_ids = null;
			if ($selected_projects != "") {
				$project_ids = explode(",", $selected_projects);
				$args['post__in'] = $project_ids;
			}
			query_posts($args);
			if (have_posts()) : while (have_posts()) : the_post();
				$terms = wp_get_post_terms(get_the_ID(), 'portfolio_category');
				$featured_image_array = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); //original size

				if(get_post_meta(get_the_ID(), 'qode_portfolio-lightbox-link', true) != ""){
					$large_image = get_post_meta(get_the_ID(), 'qode_portfolio-lightbox-link', true);
				} else {
					$large_image = $featured_image_array[0];
				}

				$custom_portfolio_link = get_post_meta(get_the_ID(), 'qode_portfolio-external-link', true);
				$portfolio_link = $custom_portfolio_link != "" ? $custom_portfolio_link : get_permalink();
				if(get_post_meta(get_the_ID(), 'qode_portfolio-external-link-target', true) != ""){
					$custom_portfolio_link_target = get_post_meta(get_the_ID(), 'qode_portfolio-external-link-target', true);
				} else {
					$custom_portfolio_link_target = '_blank';
				}

				$target = $custom_portfolio_link != "" ? $custom_portfolio_link_target : '_self';

				$masonry_size = "default";
				$masonry_size =  get_post_meta(get_the_ID(), "qode_portfolio_type_masonry_style", true);

				$image_size = "";
				if($masonry_size == "large_width"){
					$image_size = "portfolio_masonry_wide";
				}elseif($masonry_size == "large_height"){
					$image_size = "portfolio_masonry_tall";
				}elseif($masonry_size == "large_width_height"){
					$image_size = "portfolio_masonry_large";
				} else{
					$image_size = "portfolio_masonry_regular";
				}

				if($type == "masonry_with_space"){
					$image_size = "portfolio_masonry_with_space";
				}

				$slug_list_ = "pretty_photo_gallery";
				$title = get_the_title();
				$html .= "<article class='portfolio_masonry_item ";

				foreach ($terms as $term) {
					$html .= "portfolio_category_$term->term_id ";
				}

				$html .= " " . $masonry_size;
				$html .= "'>";

					// IMAGE 
					$html .= "<div class='image_holder ".$hover_type."'>";
						//$html .= "<span class='image'>";
						//$html .= get_the_post_thumbnail(get_the_ID(), $image_size);
						//$html .= "</span>"; //close span.image
					// END IMAGE

						if($disable_link != "yes"){
							$html .= "<a class='portfolio_link_class' href='" . $portfolio_link . "' target='".$target."'></a>";
						}
						
						$html .= '<div class="portfolio_shader"></div>';

						$html .= '<div class="text_holder">';
							if($hover_type == "elegant_hover"){
								$html .= '<div class="text_holder_inner"><div class="text_holder_inner2">';
							}

							if($hover_type == "default_hover" && !$portfolio_list_hide_category){
								$html .= '<span class="project_category">';
									$html .= '<span>'. __('In ', 'qode') .'</span>';
									$k = 1;
									foreach ($terms as $term) {
										$html .= "$term->name";
										if (count($terms) != $k) {
											$html .= ' / ';
										}
										$k++;
									}
								$html .= '</span>';
							}

							$title_style = '';
							if($title_font_size != ""){
								$title_style = 'style="font-size: '.$title_font_size.'px;"';
							}
							
							$html .= '<'.$title_tag.' class="portfolio_title" '.$title_style.'>' . get_the_title() . '</'.$title_tag.'>';

							if($hover_type != "default_hover" && !$portfolio_list_hide_category){
								$html .= '<span class="project_category">';
									$html .= '<span>'. __('In ', 'qode') .'</span>';
									$k = 1;
									foreach ($terms as $term) {
										$html .= "$term->name";
										if (count($terms) != $k) {
											$html .= ' / ';
										}
										$k++;
									}
								$html .= '</span>';
							}

							if($hover_type == "elegant_hover"){
								$html .= '</div></div>';
							}
						$html .= "</div>";

						if($hover_type != "elegant_hover"){
							$html .= '<div class="icons_holder"><div class="icons_holder_inner">';
								if ($lightbox == "yes") {
									$html .= "<a class='portfolio_lightbox' title='" . $title . "' href='" . $large_image . "' data-rel='prettyPhoto[" . $slug_list_ . "]'></a>";
								}

								if ($portfolio_qode_like == "on" && $show_like == "yes") {
									if (function_exists('qode_like_portfolio_list')) {
										$html .= qode_like_portfolio_list(get_the_ID());
									}
								}
							$html .= "</div></div>";
						}
					$html .= "</div>"; //close div.image_holder
				$html .= "</article>";

			endwhile;
			else:
				?>
				<p><?php _e('Sorry, no posts matched your criteria.', 'qode'); ?></p>
			<?php
			endif;
			wp_reset_query();
			$html .= "</div>";
		}
		return $html;
	}

}
add_shortcode('portfolio_list', 'portfolio_list');



if (!function_exists('appearance_list')) {

	function appearance_list($atts, $content = null) {

		global $wp_query;
		global $qode_options;
		$portfolio_qode_like = "on";
		if (isset($qode_options['portfolio_qode_like'])) {
			$portfolio_qode_like = $qode_options['portfolio_qode_like'];
		}

		$portfolio_list_hide_category = false;
		if (isset($qode_options['portfolio_list_hide_category']) && $qode_options['portfolio_list_hide_category'] == "yes") {
			$portfolio_list_hide_category = true;
		}

		$portfolio_filter_class = "";
		if (isset($qode_options['portfolio_filter_disable_separator']) && !empty($qode_options['portfolio_filter_disable_separator'])) {
			if($qode_options['portfolio_filter_disable_separator'] == "yes"){
				$portfolio_filter_class = "without_separator";
			} else {
				$portfolio_filter_class = "";
			}
		}

		$args = array(
			"type"                  	=> "standard",
			"hover_type"            	=> "default_hover",
			"box_border"            	=> "",
			"box_background_color" 		=> "",		
			"box_border_color"      	=> "",
			"box_border_width"      	=> "",
			"columns"               	=> "3",
			"image_size"            	=> "",
			"order_by"              	=> "date",
			"order"                 	=> "ASC",
			"number"                	=> "-1",
			"filter"                	=> "no",
			"filter_order_by"           => "name",
			"disable_filter_title"      => "no",
			"filter_align"          	=> "left_align",
			"disable_link"          	=> "no",
			"lightbox"             		=> "yes",
			"show_like"             	=> "yes",
			"category"              	=> "",
			"selected_projects"     	=> "",
			"show_load_more"        	=> "yes",
			"title_tag"             	=> "h4",
			"title_font_size"       	=> "",
			"text_align"            	=> ""
		);

		extract(shortcode_atts($args, $atts));

		$headings_array = array('h2', 'h3', 'h4', 'h5', 'h6');

		//get correct heading value. If provided heading isn't valid get the default one
		$title_tag = (in_array($title_tag, $headings_array)) ? $title_tag : $args['title_tag'];

		$html = "";

		$_type_class = '';
		$_portfolio_space_class = '';
		$_portfolio_masonry_with_space_class = '';
		if ($type == "hover_text") {
			$_type_class = " hover_text";
			$_portfolio_space_class = "portfolio_with_space portfolio_with_hover_text";
		} elseif ($type == "standard" || $type == "masonry_with_space"){
			$_type_class = " standard";
			$_portfolio_space_class = "portfolio_with_space portfolio_standard";
			if($type == "masonry_with_space"){
				$_portfolio_masonry_with_space_class = ' masonry_with_space';
			}
		} elseif ($type == "standard_no_space"){
			$_type_class = " standard_no_space";
			$_portfolio_space_class = "portfolio_no_space portfolio_standard";
		} elseif ($type == "hover_text_no_space"){
			$_type_class = " hover_text no_space";
			$_portfolio_space_class = "portfolio_no_space portfolio_with_hover_text";
		}

		$portfolio_box_style = "";
		$portfolio_description_class = "";

		if($box_border == "yes" || $box_background_color != ""){

			$portfolio_box_style .= "style=";
			if($box_border == "yes"){
				$portfolio_box_style .= "border-style:solid;";
				if($box_border_color != "" ){
					$portfolio_box_style .= "border-color:" . $box_border_color . ";";
				}
				if($box_border_width != "" ){
					$portfolio_box_style .= "border-width:" . $box_border_width . "px;";
				} else {
					$portfolio_box_style .= "border-width: 1px;";
				}
			}
			if($box_background_color != ""){
				$portfolio_box_style .= "background-color:" . $box_background_color . ";";
			}
			$portfolio_box_style .= "'";

			$portfolio_description_class .= 'with_padding';

			$_portfolio_space_class = ' with_description_background';

		}

		if($text_align !== '') {
			$portfolio_description_class .= ' text_align_'.$text_align;
		}

		if($type != 'masonry') {
			$html .= "<div class='projects_holder_outer v$columns $_portfolio_space_class $_portfolio_masonry_with_space_class'>";
			if ($filter == "yes") {
				$html .= "<div class='filter_outer ".$filter_align."'>";
					$html .= "<div class='filter_holder ".$portfolio_filter_class."'><ul>";
						if($disable_filter_title != "yes"){
							$html .= "<li class='filter_title'><span>".__('FILTER APPEARANCES:', 'qode')."</span></li>";
						}
						if($type == 'masonry_with_space'){
							$html .= "<li class='filter' data-filter='*'><span>" . __('All', 'qode') . "</span></li>";
						} else {
							$html .= "<li class='filter' data-filter='all'><span>" . __('All', 'qode') . "</span></li>";
						}
						
					if ($category == "") {
						$args = array(
							'parent' => 0,
							'orderby' => $filter_order_by
						);
						$portfolio_categories = get_terms('appearance_category', $args);
					} else {
						$top_category = get_term_by('slug', $category, 'appearance_category');
						$term_id = '';
						if (isset($top_category->term_id))
							$term_id = $top_category->term_id;
						$args = array(
							'parent' => $term_id,
							'orderby' => $filter_order_by
						);
						$portfolio_categories = get_terms('appearance_category', $args);
					}
					foreach ($portfolio_categories as $portfolio_category) {
						if($type == 'masonry_with_space'){
							$html .= "<li class='filter' data-filter='.portfolio_category_$portfolio_category->term_id'><span>$portfolio_category->name</span>";
						} else {
							$html .= "<li class='filter' data-filter='portfolio_category_$portfolio_category->term_id'><span>$portfolio_category->name</span>";
						}
						$args = array(
							'child_of' => $portfolio_category->term_id
						);
						$html .= '</li>';
					}
					$html .= "</ul></div>";
				$html .= "</div>";
			}

			$thumb_size_class = "";
			//get proper image size
			switch($image_size) {
				case 'landscape':
					$thumb_size_class = 'portfolio_landscape_image';
					break;
				case 'portrait':
					$thumb_size_class = 'portfolio_portrait_image';
					break;
				case 'square':
					$thumb_size_class = 'portfolio_square_image';
					break;
				case 'full':
					$thumb_size_class = 'portfolio_full_image';
					break;
				default:
					$thumb_size_class = 'portfolio_default_image';
					break;
			}

			$html .= "<div class='projects_holder clearfix v$columns$_type_class $thumb_size_class'>\n";
			if (get_query_var('paged')) {
				$paged = get_query_var('paged');
			} elseif (get_query_var('page')) {
				$paged = get_query_var('page');
			} else {
				$paged = 1;
			}
			if ($category == "") {
				$args = array(
					'post_type' => 'appearances',
					'orderby' => $order_by,
					'order' => $order,
					'posts_per_page' => $number,
					'paged' => $paged
				);
			} else {
				$args = array(
					'post_type' => 'appearances',
					'portfolio_category' => $category,
					'orderby' => $order_by,
					'order' => $order,
					'posts_per_page' => $number,
					'paged' => $paged
				);
			}
			$project_ids = null;
			if ($selected_projects != "") {
				$project_ids = explode(",", $selected_projects);
				$args['post__in'] = $project_ids;
			}
			query_posts($args);
			if (have_posts()) : while (have_posts()) : the_post();
				$terms = wp_get_post_terms(get_the_ID(), 'appearance_category');
				$html .= "<article class='mix ";
				foreach ($terms as $term) {
					$html .= "portfolio_category_$term->term_id ";
				}

				$title = get_the_title();
				$featured_image_array = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); //original size

				if(get_post_meta(get_the_ID(), 'qode_portfolio-lightbox-link', true) != ""){
					$large_image = get_post_meta(get_the_ID(), 'qode_portfolio-lightbox-link', true);
				} else {
					$large_image = $featured_image_array[0];
				}

				$slug_list_ = "pretty_photo_gallery";

				//get proper image size
				switch($image_size) {
					case 'landscape':
						$thumb_size = 'portfolio-landscape';
						break;
					case 'portrait':
						$thumb_size = 'portfolio-portrait';
						break;
					case 'square':
						$thumb_size = 'portfolio-square';
						break;
					case 'full':
						$thumb_size = 'full';
						break;
					default:
						$thumb_size = 'portfolio-default';
						break;
				}

				if($type == "masonry_with_space"){
					$thumb_size = 'portfolio_masonry_with_space';
				}

				$custom_portfolio_link = get_post_meta(get_the_ID(), 'qode_portfolio-external-link', true);
				$portfolio_link = $custom_portfolio_link != "" ? $custom_portfolio_link : get_permalink();

				if(get_post_meta(get_the_ID(), 'qode_portfolio-external-link-target', true) != ""){
					$custom_portfolio_link_target = get_post_meta(get_the_ID(), 'qode_portfolio-external-link-target', true);
				} else {
					$custom_portfolio_link_target = '_blank';
				}

				$target = $custom_portfolfo_link != "" ? $custom_portfolio_link_target : '_self';

				$html .="'>";

				$hover_type = "";
				//echo $thumb_size;
				//exit;
				$html .= "<div class='image_holder ".$hover_type."'>";
					$html .= "<span class='image'>";
				    $html .= '<div class="post_image">';

				    $html .= getAppearanceMediaHTML( get_the_ID(), $thumb_size );
					
					$html .= '</div>';
					
					$html .= "</span>";

					/*
					if ($type == "standard" || $type == "standard_no_space" || $type == "masonry_with_space") {

						if($disable_link != "yes"){
							$html .= "<a class='portfolio_link_class' href='" . $portfolio_link . "' target='".$target."'></a>";
						}

						$html .= '<div class="portfolio_shader"></div>';

						$html .= '<div class="icons_holder"><div class="icons_holder_inner">';
							if ($lightbox == "yes") {
								$html .= "<a class='portfolio_lightbox' title='" . $title . "' href='" . $large_image . "' data-rel='prettyPhoto[" . $slug_list_ . "]'></a>";
							}

							if ($portfolio_qode_like == "on" && $show_like == "yes") {
								if (function_exists('qode_like_portfolio_list')) {
									$html .= qode_like_portfolio_list(get_the_ID());
								}
							}
						$html .= "</div></div>";

					} else if ($type == "hover_text" || $type == "hover_text_no_space") {

						if($disable_link != "yes"){
							$html .= "<a class='portfolio_link_class' href='" . $portfolio_link . "' target='".$target."'></a>";
						}
						
						$html .= '<div class="portfolio_shader"></div>';

						$html .= '<div class="text_holder">';
							if($hover_type == "elegant_hover"){
								$html .= '<div class="text_holder_inner"><div class="text_holder_inner2">';
							}

							if($hover_type == "default_hover" && !$portfolio_list_hide_category){
								$html .= '<span class="project_category">';
									$html .= '<span>'. __('In ', 'qode') .'</span>';
									$k = 1;
									foreach ($terms as $term) {
										$html .= "$term->name";
										if (count($terms) != $k) {
											$html .= ' / ';
										}
										$k++;
									}
								$html .= '</span>';
							}	

							$title_style = '';
							if($title_font_size != ""){
								$title_style = 'style="font-size: '.$title_font_size.'px;"';
							}
							
							$html .= '<'.$title_tag.' class="portfolio_title" '.$title_style.'>' . get_the_title() . '</'.$title_tag.'>';

							if($hover_type != "default_hover" && !$portfolio_list_hide_category){
								$html .= '<span class="project_category">';
									$html .= '<span>'. __('In ', 'qode') .'</span>';
									$k = 1;
									foreach ($terms as $term) {
										$html .= "$term->name";
										if (count($terms) != $k) {
											$html .= ' / ';
										}
										$k++;
									}
								$html .= '</span>';
							}

							if($hover_type == "elegant_hover"){
								$html .= '</div></div>';
							}
						$html .= "</div>";

						if($hover_type != "elegant_hover"){
							$html .= '<div class="icons_holder"><div class="icons_holder_inner">';
								if ($lightbox == "yes") {
									$html .= "<a class='portfolio_lightbox' title='" . $title . "' href='" . $large_image . "' data-rel='prettyPhoto[" . $slug_list_ . "]'></a>";
								}

								if ($portfolio_qode_like == "on" && $show_like == "yes") {
									if (function_exists('qode_like_portfolio_list')) {
										$html .= qode_like_portfolio_list(get_the_ID());
									}
								}
							$html .= "</div></div>";
						}
					}*/

				$html .= "</div>";

				if ($type == "standard" || $type == "standard_no_space" || $type == "masonry_with_space") {
					$html .= "<div class='portfolio_description ".$portfolio_description_class."'". $portfolio_box_style .">";
						
						$title_style = '';
						if($title_font_size != ""){
							$title_style = 'style="font-size: '.$title_font_size.'px;"';
						}

						if($disable_link != "yes"){
							$html .= '<'.$title_tag.' class="portfolio_title" '.$title_style.'><a href="' . $portfolio_link . '" target="'.$target.'">' . get_the_title() . '</a></'.$title_tag.'>';
						} else {
							$html .= '<'.$title_tag.' class="portfolio_title" '.$title_style.'>' . get_the_title() . '</'.$title_tag.'>';
						}
					
						if(!$portfolio_list_hide_category){
							$html .= '<span class="project_category">';
								$html .= '<span>'. __('In ', 'qode') .'</span>';
								$k = 1;
								foreach ($terms as $term) {
									$html .= "$term->name";
									if (count($terms) != $k) {
										$html .= ', ';
									}
									$k++;
								}
							$html .= '</span>';
						}
					$html .= '</div>';
				}

				$html .= "</article>\n";

			endwhile;

				$i = 1;
				while ($i <= $columns) {
					$i++;
					if ($columns != 1) {
						$html .= "<div class='filler'></div>\n";
					}
				}

			else:
				?>
				<p><?php _e('Sorry, no posts matched your criteria.', 'qode'); ?></p>
			<?php
			endif;


			$html .= "</div>";
			if (get_next_posts_link()) {
				if ($show_load_more == "yes" || $show_load_more == "") {
					$html .= '<div class="portfolio_paging"><span rel="' . $wp_query->max_num_pages . '" class="load_more">' . get_next_posts_link(__('Show more', 'qode')) . '</span></div>';
					$html .= '<div class="portfolio_paging_loading"><a href="javascript: void(0)" class="qbutton">'.__('Loading...', 'qode').'</a></div>';
				}
			}
			$html .= "</div>";
			wp_reset_query();
		} else {
			if ($filter == "yes") {

				$html .= "<div class='filter_outer ".$filter_align."'>";
				$html .= "<div class='filter_holder ".$portfolio_filter_class."'><ul>";
				if($disable_filter_title != "yes"){		
					$html .= "<li class='filter_title'><span>".__('Sort Appearances:', 'qode')."</span></li>";
				}	
				$html .= "<li class='filter' data-filter='*'><span>" . __('All', 'qode') . "</span></li>";
				if ($category == "") {
					$args = array(
						'parent' => 0,
						'orderby' => $filter_order_by
					);
					$portfolio_categories = get_terms('appearance_category', $args);
				} else {
					$top_category = get_term_by('slug', $category, 'appearance_category');
					$term_id = '';
					if (isset($top_category->term_id))
						$term_id = $top_category->term_id;
					$args = array(
						'parent' => $term_id,
						'orderby' => $filter_order_by
					);
					$portfolio_categories = get_terms('appearance_category', $args);
				}
				foreach ($portfolio_categories as $portfolio_category) {
					$html .= "<li class='filter' data-filter='.portfolio_category_$portfolio_category->term_id'><span>$portfolio_category->name</span>";
					$args = array(
						'child_of' => $portfolio_category->term_id
					);
					$html .= '</li>';
				}
				$html .= "</ul></div>";
				$html .= "</div>";


			}
			$html .= "<div class='projects_masonry_holder'>";
			if (get_query_var('paged')) {
				$paged = get_query_var('paged');
			} elseif (get_query_var('page')) {
				$paged = get_query_var('page');
			} else {
				$paged = 1;
			}
			if ($category == "") {
				$args = array(
					'post_type' => 'appearances',
					'orderby' => $order_by,
					'order' => $order,
					'posts_per_page' => $number,
					'paged' => $paged
				);
			} else {
				$args = array(
					'post_type' => 'appearances',
					'portfolio_category' => $category,
					'orderby' => $order_by,
					'order' => $order,
					'posts_per_page' => $number,
					'paged' => $paged
				);
			}
			$project_ids = null;
			if ($selected_projects != "") {
				$project_ids = explode(",", $selected_projects);
				$args['post__in'] = $project_ids;
			}
			query_posts($args);
			if (have_posts()) : while (have_posts()) : the_post();
				$terms = wp_get_post_terms(get_the_ID(), 'appearance_category');
				$featured_image_array = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); //original size

				if(get_post_meta(get_the_ID(), 'qode_portfolio-lightbox-link', true) != ""){
					$large_image = get_post_meta(get_the_ID(), 'qode_portfolio-lightbox-link', true);
				} else {
					$large_image = $featured_image_array[0];
				}

				$custom_portfolio_link = get_post_meta(get_the_ID(), 'qode_portfolio-external-link', true);
				$portfolio_link = $custom_portfolio_link != "" ? $custom_portfolio_link : get_permalink();
				if(get_post_meta(get_the_ID(), 'qode_portfolio-external-link-target', true) != ""){
					$custom_portfolio_link_target = get_post_meta(get_the_ID(), 'qode_portfolio-external-link-target', true);
				} else {
					$custom_portfolio_link_target = '_blank';
				}

				$target = $custom_portfolio_link != "" ? $custom_portfolio_link_target : '_self';

				$masonry_size = "default";
				$masonry_size =  get_post_meta(get_the_ID(), "qode_portfolio_type_masonry_style", true);

				$image_size = "";
				if($masonry_size == "large_width"){
					$image_size = "portfolio_masonry_wide";
				}elseif($masonry_size == "large_height"){
					$image_size = "portfolio_masonry_tall";
				}elseif($masonry_size == "large_width_height"){
					$image_size = "portfolio_masonry_large";
				} else{
					$image_size = "portfolio_masonry_regular";
				}

				if($type == "masonry_with_space"){
					$image_size = "portfolio_masonry_with_space";
				}

				$slug_list_ = "pretty_photo_gallery";
				$title = get_the_title();
				$html .= "<article class='portfolio_masonry_item ";

				foreach ($terms as $term) {
					$html .= "portfolio_category_$term->term_id ";
				}

				$html .= " " . $masonry_size;
				$html .= "'>";

					$html .= "<div class='image_holder ".$hover_type."'>";
						$html .= "<span class='image'>";
							$html .= get_the_post_thumbnail(get_the_ID(), $image_size);
						$html .= "</span>"; //close span.image

						if($disable_link != "yes"){
							$html .= "<a class='portfolio_link_class' href='" . $portfolio_link . "' target='".$target."'></a>";
						}
						
						$html .= '<div class="portfolio_shader"></div>';

						$html .= '<div class="text_holder">';
							if($hover_type == "elegant_hover"){
								$html .= '<div class="text_holder_inner"><div class="text_holder_inner2">';
							}

							if($hover_type == "default_hover" && !$portfolio_list_hide_category){
								$html .= '<span class="project_category">';
									$html .= '<span>'. __('In ', 'qode') .'</span>';
									$k = 1;
									foreach ($terms as $term) {
										$html .= "$term->name";
										if (count($terms) != $k) {
											$html .= ' / ';
										}
										$k++;
									}
								$html .= '</span>';
							}

							$title_style = '';
							if($title_font_size != ""){
								$title_style = 'style="font-size: '.$title_font_size.'px;"';
							}
							
							$html .= '<'.$title_tag.' class="portfolio_title" '.$title_style.'>' . get_the_title() . '</'.$title_tag.'>';

							if($hover_type != "default_hover" && !$portfolio_list_hide_category){
								$html .= '<span class="project_category">';
									$html .= '<span>'. __('In ', 'qode') .'</span>';
									$k = 1;
									foreach ($terms as $term) {
										$html .= "$term->name";
										if (count($terms) != $k) {
											$html .= ' / ';
										}
										$k++;
									}
								$html .= '</span>';
							}

							if($hover_type == "elegant_hover"){
								$html .= '</div></div>';
							}
						$html .= "</div>";

						if($hover_type != "elegant_hover"){
							$html .= '<div class="icons_holder"><div class="icons_holder_inner">';
								if ($lightbox == "yes") {
									$html .= "<a class='portfolio_lightbox' title='" . $title . "' href='" . $large_image . "' data-rel='prettyPhoto[" . $slug_list_ . "]'></a>";
								}

								if ($portfolio_qode_like == "on" && $show_like == "yes") {
									if (function_exists('qode_like_portfolio_list')) {
										$html .= qode_like_portfolio_list(get_the_ID());
									}
								}
							$html .= "</div></div>";
						}
					$html .= "</div>"; //close div.image_holder
				$html .= "</article>";

			endwhile;
			else:
				?>
				<p><?php _e('Sorry, no posts matched your criteria.', 'qode'); ?></p>
			<?php
			endif;
			wp_reset_query();
			$html .= "</div>";
		}
		return $html;
	}

}
add_shortcode('appearance_list', 'appearance_list');



/* Icon with text shortcode */

if(!function_exists('icon_text')) {
	function icon_text($atts, $content = null) {
		$default_atts = array(
			"icon_size"             		=> "",
			"custom_icon_size"      		=> "20",
			"text_left_padding"     		=> "86",
			"icon_pack"             		=> "",
			"fa_icon"               		=> "",
			"fe_icon"               		=> "",
			"icon_animation"        		=> "",
			"icon_animation_delay"  	 	=> "",
			"icon_type"             	 	=> "",
			"icon_border_width"       	 	=> "",
			"without_double_border_icon" 	=> "",
			"icon_position"         		=> "",
			"icon_border_color"     		=> "",
			"icon_margin"           		=> "",
			"icon_color"            		=> "",
			"icon_background_color" 		=> "",
			"box_type"              		=> "",
			"box_border"            		=> "",
			"box_border_color"      		=> "",
			"box_background_color"  		=> "",
			"title"                 		=> "",
			"title_tag"             		=> "h4",
			"title_color"           		=> "",
			"title_padding"         		=> "",
			"text"                  		=> "",
			"text_color"            		=> "",
			"link"                  		=> "",
			"link_text"             		=> "",
			"link_color"            		=> "",
			"target"                		=> ""
		);

		extract(shortcode_atts($default_atts, $atts));

		$headings_array = array('h2', 'h3', 'h4', 'h5', 'h6');

		//get correct heading value. If provided heading isn't valid get the default one
		$title_tag = (in_array($title_tag, $headings_array)) ? $title_tag : $args['title_tag'];

		//init icon styles
		$style = '';
		$icon_stack_classes = '';

		//init icon stack styles
		$icon_margin_style       	= '';
		$icon_stack_square_style 	= '';
		$icon_stack_base_style   	= '';
		$icon_stack_style        	= '';
		$icon_stack_font_size       = '';
		$icon_holder_style          = '';
		$animation_delay_style   	= '';

		//generate inline icon styles
		if($custom_icon_size != "" && $fe_icon != "" && $icon_pack == 'font_elegant') {
			$icon_stack_style		.= 'font-size: '.$custom_icon_size.'px;';
			$icon_stack_font_size	.= 'font-size: '.$custom_icon_size.'px;';
		}

		if($icon_color != "") {
			$style .= 'color: '.$icon_color.';';
			$icon_stack_style .= 'color: '.$icon_color.';';
		}

		//generate icon stack styles
		if($icon_background_color != "") {
			$icon_stack_base_style .= 'background-color: '.$icon_background_color.';';
			$icon_stack_square_style .= 'background-color: '.$icon_background_color.';';
		}

		if($icon_border_width !== '') {
			$icon_stack_base_style .= 'border-width: '.$icon_border_width.'px;';
			$icon_holder_style .= 'border-width: '.$icon_border_width.'px;';
			$icon_stack_square_style .= 'border-width: '.$icon_border_width.'px;';
		}

		if($icon_border_color != "") {
			$icon_stack_style .= 'border-color: '.$icon_border_color.';';
			$icon_holder_style .= 'border-color: '.$icon_border_color.';';
		}

		if($icon_margin != "") {
			$icon_margin_style .= "margin: ".$icon_margin.";";
		}

		if($icon_animation_delay != "" && $icon_animation == "q_icon_animation"){
			$animation_delay_style .= 'transition-delay: '.$icon_animation_delay.'ms; -webkit-transition-delay: '.$icon_animation_delay.'ms; -moz-transition-delay: '.$icon_animation_delay.'ms; -o-transition-delay: '.$icon_animation_delay.'ms;';
		}

		$box_size = '';
		//generate icon text holder styles and classes

		//map value of the field to the actual class value

		if($icon_pack == 'font_awesome' && $fa_icon != ''){

			switch ($icon_size) {
				case 'large': //smallest icon size
					$box_size = 'tiny';
					break;
				case 'fa-2x':
					$box_size = 'small';
					break;
				case 'fa-3x':
					$box_size = 'medium';
					break;
				case 'fa-4x':
					$box_size = 'large';
					break;
				case 'fa-5x':
					$box_size = 'very_large';
					break;
				default:
					$box_size = 'tiny';
			}
		}

		$box_icon_type = '';
		switch ($icon_type) {
			case 'normal':
				$box_icon_type = 'normal_icon';
				break;
			case 'square':
				$box_icon_type = 'square';
				break;
			case 'circle':
				$box_icon_type = 'circle';
				break;
		}

		$html = "";
		$html_icon = "";

		//genererate icon html
		switch ($icon_type) {
			case 'circle':
				//if custom icon size is set and if it is larger than large icon size
				if($custom_icon_size != "") {
					//add custom font class that has smaller inner icon font
					$icon_stack_classes .= ' custom-font';
				}

				if($icon_pack == 'font_awesome' && $fa_icon != ''){
					$html_icon .= '<span class="fa-stack '.$icon_size.' '.$icon_stack_classes.'" style="'.$icon_stack_style . $icon_stack_base_style .'">';
					$html_icon .= '<i class="icon_text_icon fa '.$fa_icon.' fa-stack-1x"></i>';
					$html_icon .= '</span>';
				}elseif($icon_pack == 'font_elegant' && $fe_icon != ''){
					$html_icon .= '<span class="q_font_elegant_holder '.$icon_type.' '.$icon_stack_classes.'" style="'.$icon_stack_style.$icon_stack_base_style.'">';
					$html_icon .= '<span class="icon_text_icon q_font_elegant_icon '.$fe_icon.'" aria-hidden="true" style="'.$icon_stack_font_size.'"></span>';
					$html_icon .= '</span>';
				}

				break;
			case 'square':
				//if custom icon size is set and if it is larget than large icon size
				if($custom_icon_size != "") {
					//add custom font class that has smaller inner icon font
					$icon_stack_classes .= ' custom-font';
				}

				if($icon_pack == 'font_awesome' && $fa_icon != ''){
					$html_icon .= '<span class="fa-stack '.$icon_size.' '.$icon_stack_classes.'" style="'.$icon_stack_style.$icon_stack_square_style.'">';
					$html_icon .= '<i class="icon_text_icon fa '.$fa_icon.' fa-stack-1x"></i>';
					$html_icon .= '</span>';
				} elseif($icon_pack == 'font_elegant' && $fe_icon != ''){
					$html_icon .= '<span class="q_font_elegant_holder '.$icon_type.' '.$icon_stack_classes.'" style="'.$icon_stack_style.$icon_stack_square_style.'">';
					$html_icon .= '<span class="icon_text_icon q_font_elegant_icon '.$fe_icon.'" aria-hidden="true" style="'.$icon_stack_font_size.'" ></span>';
					$html_icon .= '</span>';
				}

				break;
			default:

				if($icon_pack == 'font_awesome' && $fa_icon != ''){
					$html_icon .= '<span style="'.$icon_stack_style.'" class="q_font_awsome_icon '.$icon_size.' '.$icon_stack_classes.'">';
					$html_icon .= '<i class="icon_text_icon fa '.$fa_icon.'"></i>';
					$html_icon .= '</span>';
				} elseif($icon_pack == 'font_elegant' && $fe_icon != ''){
					$html_icon .= '<span class="q_font_elegant_holder '.$icon_type.' '.$icon_stack_classes.'" style="'.$icon_stack_style.'">';
					$html_icon .= '<span class="icon_text_icon q_font_elegant_icon '.$fe_icon.'" aria-hidden="true" style="'.$icon_stack_font_size.'"></span>';
					$html_icon .= '</span>';
				}

				break;
		}

		$title_style = "";
		if($title_color != "") {
			$title_style .= "color: ".$title_color;
		}

		$text_style = "";
		if($text_color != "") {
			$text_style .= "color: ".$text_color;
		}

		$link_style = "";

		if($link_color != "") {
			$link_style .= "color: ".$link_color.";";
		}

		//generate normal type of a box html
		if($box_type == "normal") {

			//init icon text wrapper styles
			$icon_with_text_clasess = '';
			$icon_with_text_style   = '';
			$icon_text_inner_style  = '';
			$icon_text_holder_style = '';

			$icon_with_text_clasess .= $box_size;
			$icon_with_text_clasess .= ' '.$box_icon_type;

			if($box_border == "yes") {
				$icon_with_text_clasess .= ' with_border_line';
			}

			if($without_double_border_icon == 'yes') {
				$icon_with_text_clasess .= ' without_double_border';
			}

			if($text_left_padding != "" && $icon_pack == 'font_elegant' && $icon_position == "left"){
				$icon_text_holder_style .= 'padding-left: '.$text_left_padding.'px';
			}

			if($box_border == "yes" && $box_border_color != "") {
				$icon_text_inner_style .= 'border-color: '.$box_border_color;
			}

			if($icon_position == "" || $icon_position == "top") {
				$icon_with_text_clasess .= " center";
			}
			if($icon_position == "left_from_title"){
				$icon_with_text_clasess .= " left_from_title";
			}

			$html .= "<div class='q_icon_with_title ".$icon_with_text_clasess."'>";
			if($icon_position != "left_from_title") {
				//generate icon holder html part with icon
				$html .= '<div class="icon_holder '.$icon_animation.'" style="'.$icon_margin_style.' '.$animation_delay_style.'">';
				$html .= '<div class="icon_holder_inner">';
				$html .= $html_icon;
				$html .= '</div>'; // close icon_holder_inner
				$html .= '</div>'; //close icon_holder
			}

			//generate text html
			$html .= '<div class="icon_text_holder" style="'.$icon_text_holder_style.'">';
			$html .= '<div class="icon_text_inner" style="'.$icon_text_inner_style.'">';
			if($icon_position == "left_from_title") {
				$html .= '<div class="icon_title_holder">'; //generate icon_title holder for icon from title
				//generate icon holder html part with icon
				$html .= '<div class="icon_holder '.$icon_animation.'" style="'.$icon_margin_style.' '.$animation_delay_style.'">';
				$html .= '<div class="icon_holder_inner">';
				$html .= $html_icon;
				$html .= '</div>'; //close icon_holder_inner
				$html .= '</div>'; //close icon_holder
			}
			$html .= '<'.$title_tag.' class="icon_title" style="'.$title_style.'">'.$title.'</'.$title_tag.'>';
			if($icon_position == "left_from_title") {
				$html .= '</div>'; //close icon_title holder for icon from title
			}

			
			$linkButton = ($link != "") ?  "<a href='$link' >$text</a>" : "$text";
			$html .= "<p  style='".$text_style."'>".$linkButton."</p>";

			if($link != ""){
				if($target == ""){
					$target = "_self";
				}

				if($link_text == ""){
					$link_text = "READ MORE";
				}

				//$html .= "<a class='icon_with_title_link' href='".$link."' target='".$target."' style='".$link_style."'>".$link_text."</a>";
			}
			$html .= '</div>';  //close icon_text_inner
			$html .= '</div>'; //close icon_text_holder

			$html.= '</div>'; //close icon_with_title
		} else {
			//init icon text wrapper styles
			$icon_with_text_clasess = '';
			$box_holder_styles = '';

			if($box_border_color != "") {
				$box_holder_styles .= 'border-color: '.$box_border_color.';';
			}

			if($box_background_color != "") {
				$box_holder_styles .= 'background-color: '.$box_background_color.';';
			}

			if($title_padding != ""){
				$valid_title_padding = (strstr($title_padding, 'px', true)) ? $title_padding : $title_padding.'px';
				$title_style .= 'padding-top: '.$valid_title_padding.';';
			}

			$icon_with_text_clasess .= $box_size;
			$icon_with_text_clasess .= ' '.$box_icon_type;

			if($without_double_border_icon == 'yes') {
				$icon_with_text_clasess .= ' without_double_border';
			}

			$html .= '<div class="q_box_holder with_icon" style="'.$box_holder_styles.'">';

			$html .= '<div class="box_holder_icon">';
			$html .= '<div class="box_holder_icon_inner '.$icon_with_text_clasess.' '.$icon_animation.'" style="'.$animation_delay_style.'">';
			$html .= '<div class="icon_holder_inner">';
			$html .= $html_icon;
			$html .= '</div>'; //close icon_holder_inner
			$html .= '</div>'; //close box_holder_icon_inner
			$html .= '</div>'; //close box_holder_icon

			//generate text html
			$html .= '<div class="box_holder_inner '.$box_size.' center">';
			$html .= '<'.$title_tag.' class="icon_title" style="'.$title_style.'">'.$title.'</'.$title_tag.'>';
			$html .= '<p style="'.$text_style.'">'.$text.'</p>';
			$html .= '</div>'; //close box_holder_inner

			$html .= '</div>'; //close box_holder
		}

		return $html;

	}
}
add_shortcode('icon_text', 'icon_text');