<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	global $rfw_pro, $rfw_pro_file;

	if(!function_exists('pre')){
	function pre($data){
			if(isset($_GET['debug'])){
				pree($data);
			}
		}	 
	} 	

	if(!function_exists('pree')){
	function pree($data){
				echo '<pre>';
				print_r($data);
				echo '</pre>';	
		}	 
	} 
		

/*
	if(!function_exists('rfw_cache')){
		function rfw_cache(){
			
		}
	}*/


add_action('wp_feed_options', 'force_feed', 10, 1);
function force_feed($feed) {
	$feed->force_feed(true);
}
	
class rfw_dock extends WP_Widget {
	
	public $rfw_cache_val, $list_type;

	public function __construct() {
		global $rfw_data;
		// widget actual processes
		parent::__construct(
			'rfw_dock', // Base ID
			$rfw_data['Name'].' ('.$rfw_data['Version'].')', // Name
			array( 'description' => __( 'A feed widget with sliding effect.', 'rss-feed-widget' ), ) // Args
		);
		
	}
	

	function clean_xhtml($string)
	{
		/*if(function_exists('ereg_replace'))
		$string = ereg_replace("<[^>]*>", "", $string);
		
		if(function_exists('preg_replace'))
		$string = preg_replace("@<p[^>]*?>.*?@siu", '',$string);
		
		$string = str_replace(array('</p>'), '', $string);*/
		
		if(function_exists('strip_tags'))
		$string = strip_tags($string);
		
		return $string;
	}

	public function rfw_cache(){
		return $this->rfw_cache_val;
	}
	
	public function list_type(){
		return $this->list_type;
	}	
	public function img_size(){
		return $this->img_size;
	}	
	
	public function is_url($url){
		$ret = true;
		if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
			$ret = false;
		}
		return $ret;
	}
	
	public function widget( $args, $instance ) {



		// outputs the content of the widget
		// pree($args); pree($instance);//exit;
		global $wpdb, $rfw_pro, $rfw_chameleon_installed, $rfw_chameleon_activated, $rfw_censorship_array, $rfw_url;
		
		//pree($instance);
		$limit_feeds = ($instance['number']>0?$instance['number']:1);
		
		$rfw_style = '';
		if($rfw_chameleon_installed && $rfw_chameleon_activated)
		$rfw_style = get_option('rfw_style', '');
		
		
		$title = $instance['title'];
		//pree($title);
		$rss_url = $instance['rss_url'];
		
		
			
		//pree($rss_url);//exit;
		
		//pree($fb_page_id);
		$show_feed_title = $instance['show_feed_title'];
		
		$keep_feed_link = $instance['keep_feed_link'];
		
		
		
		$feed_words = ($instance['feed_words']>0?$instance['feed_words']:60);
		
		$display_type = array_key_exists('content_display', $instance)?$instance['content_display']:'';
		$text_to_display = array_key_exists('text_to_display', $instance)?$instance['text_to_display']:'';
		//pree($display_type);		
		$content_order = array_key_exists('content_order', $instance)?$instance['content_order']:'';
		
		$content_height = $instance['content_height']; 
		
		$rfw_censorship = (isset($instance['rfw_censorship'])?$instance['rfw_censorship']:''); 
		
		$rfw_censorship = trim($rfw_censorship);
		$rfw_censorship_array = ($rfw_censorship!=''?explode(',', $rfw_censorship):array());
		$rfw_censorship_array = array_filter($rfw_censorship_array, 'strlen');	
		$rfw_censorship_array = array_map('strtolower', $rfw_censorship_array);		
		$rfw_censorship_array = array_map('trim', $rfw_censorship_array);		
		
		$speed    = isset( $instance['speed'] ) ? absint( $instance['speed'] ) : 500;
		
		$this->rfw_cache_val = isset($instance['rfw_cache'])?$instance['rfw_cache']:''; 
		$this->list_type = isset($instance['list_type'])?$instance['list_type']:'slider'; 
		
		$this->img_size = isset($instance['img_size'])?$instance['img_size']:'small'; 
		
		$content_sort = (substr($content_order, 0, 4)=='date');
		//pree($content_sort);
		
		//pree($this->img_size);
		$widget_sub_array = array(
			'limit_feeds' => $limit_feeds, 
			'content_height' => $content_height, 
			'feed_words' => $feed_words, 
			'display_type' => $display_type, 
			'text_to_display' => $text_to_display,
			'keep_feed_link' => $keep_feed_link, 
			'show_feed_title' => $show_feed_title, 
			'content_order' => $content_order,
			'content_sort' => $content_sort
		);
		
	
		$html = '';
		$html_arr = array();
		$scripts = array();


		if($rfw_pro){
			
			$rss_links = (is_array(maybe_unserialize($instance['rss_url']))?unserialize($instance['rss_url']):$rss_url);

			if(is_array($rss_links) && !empty($rss_links)){
				$rss_urls = $rss_links;
				foreach($rss_urls as $ri=>$rss_url){
					
					$slider_obj = str_replace('-', '_', $args['widget_id'].$ri);
					
					$rfw_style = apply_filters('rfw_class_filter', $rfw_style, $args['widget_id']);

					$html_arr[] = '<aside id="'.$args['widget_id'].$ri.'" data-class="'.$args['widget_id'].'" class="rfw-class '.$rfw_style.' '.$args['widget_id'].' '.(count($rss_urls)>1?'rfw_multiple '.($ri==0?'rfw_first':'').' '.($ri==(count($rss_urls)-1)?'rfw_last':'').' '.($ri>=1?'rfw_more':''):'').'" data-rfw-feed-url="'.$rss_url.'" data-rfw-slider="'.$slider_obj.'">';
					
					
					$widget_sub_output = $this->widget_sub($ri, $rss_url, $widget_sub_array);
					extract($widget_sub_output);
					
					
					if(count($rss_urls)>1){
						$html_arr[] = '<div class="rfw_feed_sets"><a class="rfw_fsl"></a><h4>'.$feed_title.'</h4><a class="rfw_fsr"></a></div>';
					}
					$html_arr[] = ($title!='')?'<h3 class="widget-title">'.$title.'</h3>':'';
					
					$html_arr[] = '<nav class="add-nav widget_dock" id="rfw-widget-'.$ri.'">';
	
					switch($this->list_type){
						case 'slider':
							wp_enqueue_style( 'rfw-slider-style',  plugins_url('css/jquery.bxslider.css', dirname(__FILE__)), array(), date('Ymhi'));			
							wp_enqueue_script(
								'rfw-slider-script',
								plugins_url('js/jquery.bxslider.js', dirname(__FILE__)),
								array( 'jquery' ),
								date('Ymhi'),
								true
							);						
							$scripts[] = "<script type=\"text/javascript\" language=\"javascript\">
											var ".$slider_obj.";
											jQuery(document).ready(function($){	
											".$slider_obj." = $('#".$args['widget_id']."$ri .rfw_dock.rfw_slider').bxSlider({
												  auto: ".($ri==0?'true':'false').",
												  adaptiveHeight: true,
												  pager: true,
												  controls: false,
												  infiniteLoop: true,
												  speed: $speed,
												  mode: 'horizontal',
												  pause: 10000,
												  ticker: false,
												  pagerType: 'full',
												  randomStart: true,
												  hideControlOnEnd: true,
												  easing: 'linear',
												  captions: false,
												  video: true,
												  responsive: true,
												  useCSS: true,
												  preloadImages: 'visible',
												  touchEnabled: true
											});
										});
									</script>";		
						break;
					}			
					
					
					$html_arr[] = $html_output;
					
					$html_arr[] = '</nav></aside>';
				}
				
				//pree($html_arr);exit;
				$html = implode('', $html_arr);
			}
		}else{
				$rss_links = (isset($instance['rss_url'])?(is_array(@unserialize($instance['rss_url']))?@unserialize($instance['rss_url']):array()):array());
				//pree($instance);
				$rss_url = (!empty($rss_links)?current($rss_links):$rss_url);
				//pree($rss_url);
			
				$ri = 0;
				
				$widget_sub_output = $this->widget_sub($ri, $rss_url, $widget_sub_array);
//				pree($widget_sub_output);
				extract($widget_sub_output);
				
				$rfw_style = apply_filters('rfw_class_filter', $rfw_style, $args['widget_id']);
					
				$html .= '<aside id="'.$args['widget_id'].$ri.'" data-class="'.$args['widget_id'].'" class="rfw-class '.$rfw_style.' '.$args['widget_id'].'">';
				$html.= ($title!='')?'<h3 class="widget-title">'.$title.'</h3>':'';
				$html.= '<nav class="add-nav widget_dock" id="rfw-widget-'.$ri.'">';

			
				switch($this->list_type){
					case 'slider':
						wp_enqueue_style( 'rfw-slider-style',  plugins_url('css/jquery.bxslider.css', dirname(__FILE__)), array(), date('Ymhi'));			
						wp_enqueue_script(
							'rfw-slider-script',
							plugins_url('js/jquery.bxslider.js', dirname(__FILE__)),
							array( 'jquery' ),
							date('Ymhi'),
							true
						);						
						$scripts[] = "<script type=\"text/javascript\" language=\"javascript\">jQuery(document).ready(function($){	
										$('#".$args['widget_id'].$ri." .rfw_dock.rfw_slider').bxSlider({
											  auto: true,
											  adaptiveHeight: true,
											  pager: true,
											  controls: false,
											  infiniteLoop: true,
											  speed: $speed,
											  mode: 'horizontal',
											  pause: 10000,
											  ticker: false,
											  pagerType: 'full',
											  randomStart: true,
											  hideControlOnEnd: true,
											  easing: 'linear',
											  captions: false,
											  video: true,
											  responsive: true,
											  useCSS: true,
											  preloadImages: 'visible',
											  touchEnabled: true
										});
									});
								</script>";		
					break;
				}			
				//pree($rss_url);
				$html_ul = $html_output;
				$html .= $html_ul.'</nav></aside>';
			}
			
			
			$css = '<style type="text/css">
			.'.$args['widget_id'].'.rfw_more{
				display:none;
			}
			
			</style>';
			
			echo $css.$html.implode('', $scripts);

	}
		

	
	public function widget_sub($index=0, $rss_url='', $widget_sub_array=array()){
		
		//pree($widget_sub_array);
		
		extract($widget_sub_array);
		
		$return_array = array('feed_title'=>'', 'html_output'=>'');
		
		$content_str = '';
			//pree($rss_url);
		$html_arr = array();
		
		$fb_page_id = !$this->is_url($rss_url);
		//pree($fb_page_id);
				
		$html_ul = '<ul class="rfw_dock rfw_'.$this->list_type.'" style="'.($content_height!=''?'height:'.$content_height:'').'">';
		
			if($fb_page_id){
				$access_token_array = array(
					'1489500477999288|KFys5ppNi3sreihdreqPkU2ChIE',
					'859332767418162|BR-YU8zjzvonNrszlll_1a4y_xE',
					'360558880785446|4jyruti_VkxxK7gS7JeyX-EuSXs',
					'1487072591579718|0KQzP-O2E4mvFCPxTLWP1b87I4Q',
					'640861236031365|2rENQzxtWtG12DtlZwqfZ6Vu6BE',
					'334487440086538|hI_NNy1NvxQiQxm-TtXsrmoCVaE',
					'755471677869105|Jxv8xVDad7vUUTauk8K2o71wG2w',
					'518353204973067|dA7YTe-k8eSvgZ8lqa51xSm16DA',
					'444286039063163|5qkYu2qxpERWO3gcs2f3nxeqhpg',
					'944793728885704|XJ6QqKK8Ldsssr4n5Qrs2tVr7rs',
					'1444667452511509|wU7tzWiuj6NadfpHfgkIGLGO86o',
					'1574171666165548|ZL9tXNXxpnCdAvdUjCX5HtRnsR8'
				);
				$access_token = $access_token_array[rand(0, 11)];	
				
				$json_resp = 'https://graph.facebook.com/'.$rss_url.'/posts?fields=object_id,message,story,link,type&access_token='.$access_token.'&limit='.$limit_feeds.'&locale=en_US';
				//pree($json_resp);
				//$response = wp_remote_get( $url );
				//pree($json_resp);
				$response = wp_remote_get($json_resp);
				
				
				$img_url = 'https://graph.facebook.com/ITEM-ID?fields=object_id';
				$img_path = 'https://graph.facebook.com/ITEM-ID/picture';
				

				if( is_wp_error( $response ) ) {
				   $error_message = $response->get_error_message();
				   //echo "Something went wrong: $error_message";
				} else {
					if( is_array($response) ) {
						$header = $response['headers']; // array of http header lines
						$body = wp_remote_retrieve_body($response);
						
						$body = json_decode($body);
						$body = (isset($body->data)?$body->data:$body);
						$body_arr = (array)$body;
					 	
					 				  
					  if(!empty($body) && !array_key_exists('error', $body_arr)){
						  
						  foreach($body as $items){
							  
							//pree($items);exit;
							
							$items->message = (isset($items->message)?$items->message:'');
							$items->story = (isset($items->story)?$items->story:$items->message);
							
							switch($content_sort){
								default:
								
									$unique_key = strtolower(substr($items->story, 0, 1));
							
								break;
								
								case 'date':
									$unique_key = strtolower(substr($items->story, 0, 1));
								break;
							}
							
							$content = '';
							
							$img = '';
							$story_img = str_replace('ITEM-ID', $items->id, $img_url);
						
														
							if($keep_feed_link)
							$link = '<h3 class="rfw1"><a title="'.addslashes($items->story).'" href="'.esc_url( $items->link ).'" target="_blank" rel="nofollow">_LINK_TEXT_</a></h3>';
							else
							$link = '<h3 class="rfw1"><a title="'.$items->story.'">_LINK_TEXT_</a></h3>';
							
							$content .= $show_feed_title?str_replace('_LINK_TEXT_', esc_html( $items->story ), $link):'';
							
							
							$description = $items->story;	
							
							//pree($items);exit;
							
							$atts = array();
							
							//pree($items->type);
							switch($items->type){
							
								case 'video':
									$attachments = wp_remote_get( 'https://graph.facebook.com/v2.2/'.$items->id.'?fields=attachments&fields=attachments{url,subattachments}&access_token='.$access_token );
									
											
									$attachments = wp_remote_retrieve_body($attachments);
									
									$attachments = json_decode($attachments);
									$attachments = (isset($attachments->data)?$attachments->data:$attachments);
							  
									
									if(!empty($attachments) && isset($attachments->attachments) && isset($attachments->attachments->data) && !empty($attachments->attachments->data)){
										foreach($attachments->attachments->data as $urls){
											$atts[] = $urls->url;	
										}
									}
								break;							
						 	}
							
							//pree($atts);exit;
							//pree($display_type);//exit;
														  
							switch($display_type){
								
								case 'title_only':						
										
									$content .= $items->get_title();
								break;
								
								case 'text_only':						
										
									$content .= ($description!=''?'<div class="text_div">'.$this->string_limit_words($this->clean_xhtml($description), $feed_words).'</div>':'');													
								break;
								
								default:
									
									$media = '';
									switch($items->type){
										case 'photo':
											$object_id = $items->object_id;
											if(!isset($items->object_id)){
												//pree($story_img);
												$story_img = wp_remote_get($story_img);
												$story_img = wp_remote_retrieve_body($story_img);
												$story_img = json_decode($story_img);
												
												if(isset($story_img->object_id))
												$object_id = $story_img->object_id;
											}
											
											$media = '<img alt="'.$items->story.'" src="'.str_replace('ITEM-ID', $object_id, $img_path).'" />';
										break;
										case 'video':
											$media = '<iframe src="https://web.facebook.com/plugins/video.php?href='.$atts[0].'&show_text=0&height=315" width="auto" height="315" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>';
										break;
										
									}
									
									//pree($media);
									
									
									$temp_desc = $this->clean_xhtml($description);
									$temp_desc = substr($temp_desc, 0, 10);
									//pree($temp_desc);
									$desc_final = $this->string_limit_words($description, $feed_words);
									$desc_final = ($desc_final!=''?$desc_final.'':'');
									//pree($desc_final);
									$description = '<div class="feed_img">'.str_replace($temp_desc, ($media!=''?'_IMG_URL_':'').'</div><div class="text_div">'.$temp_desc, $desc_final).'</div>';
									
									//pree($description);
									$content = ((!$this->is_url($description) && $description!='')?str_replace('_IMG_URL_', $media, $description):'');	
									
									
								break;
								
								case 'video_only':	
									if($items->type=='video')
									$content .= '<iframe src="https://web.facebook.com/plugins/video.php?href='.$atts[0].'&show_text=0&width=560" width="560" height="315" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>';
									
								break;
								
								case 'image_only':	
									$media = '';
									switch($items->type){
										case 'photo':
											$object_id = $items->object_id;
											
											if(!isset($items->object_id)){
												//pree($story_img);
												$story_img = wp_remote_get($story_img);
												$story_img = wp_remote_retrieve_body($story_img);
												$story_img = json_decode($story_img);
												
												if(isset($story_img->object_id))
												$object_id = $story_img->object_id;
											}
											
											$media = '<img alt="'.$items->story.'" src="'.str_replace('ITEM-ID', $object_id, $img_path).'" />';
										break;
										case 'video':
											$media = '<iframe src="https://web.facebook.com/plugins/video.php?href='.$atts[0].'&show_text=0&height=315" width="auto" height="315" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>';
										break;
										
									}
									
									
									$content .= $media;
									
								break;
							}
							
							
							if($img!=''){			
										   
								$content .= str_replace('_LINK_TEXT_', $img, $link);						
							}
						   
							
							$html_arr[$unique_key][] = ($content!=''?'<li>'.$content.'</li>':'');

						  }
					  }else{
						  $html_ul = '';
					  }
					}
				}				
							
			}else{
				
					
				if($this->rfw_cache()>0)
				add_filter( 'wp_feed_cache_transient_lifetime' , array($this, 'rfw_cache') );
				else{
					if(!in_array(substr($rss_url, -3, 3), array('xml')))
					$rss_url = $rss_url.(strpos($rss_url, '?')?'':'?').'&t='.time();
				}
				
				//pree($rss_url);exit;
				
				$rss = fetch_feed($rss_url);
				
				if (is_wp_error($rss)) {
					//echo $rss->get_error_message();
					
					$rss_url = esc_url($rss_url);
					$rss = fetch_feed($rss_url);
					
					//pree($rss);
				} else {
				}				
				
				//pree($rss);
				//pree($rss->get_title());
				if(method_exists($rss, 'get_title'))
				$return_array['feed_title'] = $rss->get_title();
				//pree($limit_feeds);
			 
			 	if($this->rfw_cache()>0)
				remove_filter( 'wp_feed_cache_transient_lifetime' , array($this, 'rfw_cache') );
					
					
				 if ( ! is_wp_error( $rss ) ){
					
					$maxitems = $rss->get_item_quantity( $limit_feeds );  
					switch($content_order){
						default:
							$rss_items = $rss->get_items( 0, $maxitems );
						break;
						case 'rand':
							$rss_items = $rss->get_items( 0, 0 );
							shuffle($rss_items);
							$rss_items = array_slice($rss_items, 0, $maxitems); 
							//pree(array_keys($rss_items));
						break;
					}
					
					//pree($content_order);
					
					
					$rfw_mutes = get_option('rfw_mutes', '');
					
					//pree($rss_items);
					
					if ( !empty($rss_items) ){
						
						
						
				   		//pree($rss_items);
						foreach ( $rss_items as $item ){
							$get_feed = ($item->get_feed());
							
							//pree($get_feed);exit;
							
							if(isset($_GET['rfw-debug'])){
								pree($item);//exit;
							}
							
							$expected_custom_image = '';
							
							if(function_exists('rfw_expected_custom_image'))
							$expected_custom_image =  rfw_expected_custom_image($item->data);
							
							
							
							//$child = current($child);
							if(isset($_GET['rfw-debug'])){
								pree($expected_custom_image);
								//pree(array_keys($child));
								exit;
							}
							
							
							switch($content_sort){
								default:
								
									$unique_key = strtolower(substr($item->get_title(), 0, 1));
							
								break;
								
								case 'date':
									$unique_key = strtotime($item->get_date());
								break;
							}
							//pree($item->get_title());
							//pree($unique_key);
														
							
							$html_arr[$unique_key][]='<li>';
							
							$img = '';
							
														
							if($keep_feed_link)	
							$link = '<h3 class="rfw2" title="'.addslashes($item->get_title()).'" data-href="'.esc_url( $item->get_permalink() ).'"><a href="'.esc_url( $item->get_permalink() ).'" target="_blank">_LINK_TEXT_</a></h3>';
							else
							$link = '<h3 class="rfw2"><a title="'.$item->get_title().'">_LINK_TEXT_</a></h3>';
							
							$html_arr[$unique_key][]= $show_feed_title?str_replace('_LINK_TEXT_', esc_html( $item->get_title() ), $link):'';
							
							
							$description = $item->get_description();
							$content = $item->get_content();
							

							switch($text_to_display){

                                case 'default':

                                    $description = (strlen($description)<strlen($content)?$content:$description);

								break;

                                case 'content':

                                    $description = $content;


								break;


                            }
							
							if($rfw_mutes!=''){
								$mute_arr = nl2br($rfw_mutes);
								$mute_arr = explode('<br />', $mute_arr);
								$mute_arr = array_filter($mute_arr, 'trim');
								$mute_arr = array_filter($mute_arr, 'strlen');
								if(!empty($mute_arr)){		
									$ma = array();
									foreach($mute_arr as $mute_str){
										$ma[] = trim($mute_str);
									}
									$description = str_replace($ma, '', $description);		
								}
							}
													
							$enclosure_images = array();
							
							$enclosure = $item->get_enclosure();
							
							//pree($enclosure);
							//exit;
												
							if ($enclosure)
							{	
								//pree($enclosure->type);							
								if(isset($enclosure->type) && substr($enclosure->type, 0, strlen('image'))=='image'){
									$enclosure_images[] = $enclosure->link;
								}elseif(isset($enclosure->thumbnails) && !empty($enclosure->thumbnails)){
									$enclosure_images = $enclosure->thumbnails;
								}elseif(
								
										(isset($enclosure->link) && in_array(substr($enclosure->link, -3, 3), array('png', 'jpg', 'gif', 'peg', 'bmp')))
									||
										(isset($enclosure->medium) && $enclosure->medium=='image')
								
								){
									$enclosure_images[] = $enclosure->link;								
								}
							}	
							//pree($enclosure_images);
							//pree($item->get_enclosure());
							//exit;						
							//pree($display_type);
							
							$feed_post_link = $item->get_permalink();
							
							switch($display_type){
								
								case 'image_only':						
								
									
									//preg_match_all('/<img[^>]+>/i', $description, $img); 
									//
									//preg_match_all('/src="([^"]*)"/', $description, $img); 
									preg_match_all('@<img.*src="([^"]*)"[^>/]*/?>@Ui', $description, $img); 
									
									
									if(!empty($img)){
										
										$img = end($img);
										
										if(isset($img[0]) && $img[0]!=''){
											$img = $img[0];
											
											$img = current(explode('?', $img));
											
											
											
										}else{
											$img = '';
										}
										
									}
									if($img == '' && !empty($enclosure_images)){
										$img = current($enclosure_images);
									}
									
									
								break;		
			
								case 'title_only':	
									//pree($item);
									$content_str = $item->get_title();
														
									//$html_arr[$unique_key][]= $content_str;							
								break;
								
								case 'linked_title':	
								
								
									$content_str = '<p><a target="_blank" href="' . esc_url($feed_post_link) . '" target="_blank">' . esc_html($item->get_title()) . '</a></p>';
									
								break;
								
								case 'text_only':	
									$content_str = (!$this->is_url($description)?($description!=''?'<div class="text_div">'.$this->string_limit_words($this->clean_xhtml($description), $feed_words).'</div>':''):'');
														
									//$html_arr[$unique_key][]= $content_str;							
								break;
								
								default:
									preg_match_all('@<img.*src="([^"]*)"[^>/]*/?>@Ui', $description, $chk_img); 
									$chk_img = $this->rfw_filter_recursive($chk_img);
									//pree($chk_img);exit;
									
									$temp_description = $this->clean_xhtml($description);
									//pree($temp_description);
									$temp_desc = substr($temp_description, 0, 10);
									
									$description = '<div class="feed_img">'.str_replace($temp_desc, '</div><div class="text_div" data-type="default">'.$temp_desc, $this->string_limit_words($temp_description, $feed_words)).'</div>';
									
									$content_str = (!$this->is_url($description)?($description!=''?$description:''):'');	
									
									if($img==''){
									
										if(!empty($chk_img)){											
											$img = $this->rfw_image_size($chk_img);											
										}
										if(!empty($enclosure_images)){
											$img = current($enclosure_images);											
										}
									}
									
									
								break;
								
							}
						   
						   
							//pree($img);
							if($img=='' && $expected_custom_image!=''){
								$img = $expected_custom_image;
							}
						   
							if(!is_array($img) && $img!=''){
								//pree($img);
							   
								$html_arr[$unique_key][]= str_replace('_LINK_TEXT_', '<div class="image_only imgn"><img src="'.$img.'" /></div>', $link);
							
							}
							
							$html_arr[$unique_key][]= $content_str;	
							$html_arr[$unique_key][]= '</li>';
						  
						}
					}
				 }			
			}


		 
		 if(is_array($html_arr) && !empty($html_arr)){
			switch($content_order){
			
				 case 'aa':
				 case 'date_aa':
					ksort($html_arr); 
				 break;
				
				 case 'ad':
				 case 'date_ad':
					krsort($html_arr); 
				 break;
				 
				 case 'rand':
				 	shuffle($html_arr);
				 break;
			
			}
			 
			//pree($html_arr);exit;
			 
			foreach($html_arr as $alphabet=>$feeds){
		 		$html_ul .= implode(' ', $feeds);
			}
		 
		 }
		
		$html_ul .= '</ul>';
		$return_array['html_output'] = $html_ul;
		
		return $return_array;	
	}
	
	private function rfw_iterate_object($data){
		foreach($data as $key => $value)
		{
			if (is_object($value) || is_array($value)){
				$this->rfw_iterate_object($value); // no need to concat
			}elseif($key=='seagal.bmb.se'){ 
				$html = '
					<div class="row">
						<div class="col-xs-3">'.$key.'</div>
						<div class="col-xs-9">'.$value.'</div>
					</div>';
				
				pree($html);
			}
		}
		
	}
	
	private function rfw_image_size($chk_img){
		
		$ret = '';
		$img_size = (is_numeric($this->img_size)?$this->img_size-1:$this->img_size);
		//pree($img_size);
		switch($img_size){
			
			case ($img_size>=1):
				
				$ret = (isset($chk_img[1]) && !empty($chk_img[1]) && isset($chk_img[1][$img_size]))?$chk_img[1][$img_size]:current($chk_img[1]);
			break;
			
			default:
			
				$ret = (isset($chk_img[1]) && !empty($chk_img[1]))?current($chk_img[1]):'';
			
			break;
			
			case 'large':
				//pree($this->rfw_img_size);
				$ret = $this->rfw_image_size_inner($chk_img[1]);
				
			break;
		}
		
		return $ret;
	}
	
	private function rfw_image_size_inner($chk_img){
		$ret = end($chk_img);
		if(function_exists('getimagesize')){			
			$larger = 0;
			foreach($chk_img as $ci){
				list($originalWidth, $originalHeight) = getimagesize($ci);
				//$ratio = ($originalWidth / $originalHeight);
				//$ret[$ratio] = $ci;										
				if(($originalWidth*$originalHeight)>$larger){
					$larger = $originalWidth*$originalHeight;
					$ret = $ci;
				}else{
					
				}
			}	
		}
		return $ret;
	}	
	private function rfw_filter_recursive($array) {
	   foreach ($array as $key => &$value) {
		  if (empty($value)) {
			 unset($array[$key]);
		  }
		  else {
			 if (is_array($value)) {
				$value = $this->rfw_filter_recursive($value);
				if (empty($value)) {
				   unset($array[$key]);
				}
			 }
		  }
	   }
	
	   return $array;
	}

	private function string_limit_words($string, $word_limit)
	{
		global $rfw_censorship_array;
		
		//pree($rfw_censorship_array);
		
		$string = str_replace(array('Read More'), '', trim($string));
		//$string = str_replace($rfw_censorship_array, '', $string);
		
		$words = explode(' ', $string);
		
		//pree($word_limit);
		
		//pree(count($words));
		
		//pree($words);
		
		if(!empty($words)){
			
			//$elimination_array = array();
			if(!empty($rfw_censorship_array)){
				foreach($words as $k=>$word){
					foreach($rfw_censorship_array as $cword){
						
						$eword = strtolower(substr($word, 0, strlen($cword)));
						//pree($eword.' - '.$cword);
						if($eword==$cword){
							//$elimination_array[] = $word;
							unset($words[$k]);
						}
					}
				}
			}
			//pree($elimination_array);
			//pree($words);
			$words = array_values($words);
			
			$string = implode(' ', $words);
			
			$words = explode(' ', $string, ($word_limit + 1));
		}
		
		//pree($words);
		
		//pree(count($words));
		
		$dots = '';
		
		if(count($words) > $word_limit){
			array_pop($words);
			$dots = '...';
		}
		
		return implode(' ', $words).$dots;
	}
	
 	public function form( $instance ) {
		// outputs the options form on admin
		global $rfw_pro, $rfw_chameleon_installed, $rfw_chameleon_activated, $rfw_premium_link;
		
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$rss_url     = isset( $instance['rss_url'] ) ? esc_attr( $instance['rss_url'] ) : '';
		
		$rss_url = isset($instance['rss_url'])?(is_array(maybe_unserialize($instance['rss_url']))?unserialize($instance['rss_url']):$rss_url):'';
		
		
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 6;
		$speed    = isset( $instance['speed'] ) ? absint( $instance['speed'] ) : '';
		
		$show_feed_title = isset( $instance['show_feed_title'] ) ? esc_attr( $instance['show_feed_title'] ) : true;
		$keep_feed_link = isset( $instance['keep_feed_link'] ) ? esc_attr( $instance['keep_feed_link'] ) : true;
		$feed_words    = isset( $instance['feed_words'] ) ? absint( $instance['feed_words'] ) : 60;
		$content_display    = isset( $instance['content_display'] ) ? $instance['content_display'] : 'default';
		$content_order    = isset( $instance['content_order'] ) ? $instance['content_order'] : 'default';
		$rfw_censorship = isset( $instance['rfw_censorship'] ) ? $instance['rfw_censorship'] : ''; 
		$content_height    = isset( $instance['content_height'] ) ? $instance['content_height'] : '';
		$rfw_cache    = isset( $instance['rfw_cache'] ) ? $instance['rfw_cache'] : '';		
		$list_type    = isset( $instance['list_type'] ) ? $instance['list_type'] : '';
		$img_size    = isset( $instance['img_size'] ) ? $instance['img_size'] : '';
		$text_to_display    = isset( $instance['text_to_display'] ) ? $instance['text_to_display'] : 'default';
		
		//pree($content_display);
		
		//pree($rss_url);
?>
        
        <div class="rwf-required">        
        <strong><?php _e('Required'); ?></strong>
        <p class="rss_url_area"><label for="<?php echo esc_attr($this->get_field_id( 'rss_url' )); ?>"><?php echo __('Enter the RSS feed URL or Facebook Page ID here:', 'rss-feed-widget').' <a title="'.__('Click here for examples', 'rss-feed-widget').'" target="_blank" href="http://www.tutorsloop.net/app/live.php?id=4330772">('.__("What's this?", 'rss-feed-widget').')</a>&nbsp;|&nbsp;<a target="_blank" href="https://plugins.svn.wordpress.org/rss-feed-widget/assets/styling-guide.pdf" title="'.__('Click here for styling guide', 'rss-feed-widget').'">'.__('Styling', 'rss-feed-widget').'</a>&nbsp;|&nbsp;<a target="_blank" href="https://plugins.svn.wordpress.org/rss-feed-widget/assets/basic-guide.pdf" title="'.__('Click here for guide', 'rss-feed-widget').'">'.__('Help', 'rss-feed-widget').'</a>'.($rfw_pro?'':'&nbsp;|&nbsp;<a target="_blank" href="'.esc_url($rfw_premium_link).'" title="'.__('Go Premium', 'rss-feed-widget').'">'.__('Multiple Feed URLs?', 'rss-feed-widget').'</a>'); ?></label>
        
<?php if($rfw_pro){ ?>
<a class="rfw_add_more"><?php _e('Add More', 'rss-feed-widget'); ?></a>
<?php } ?>


<?php if(is_array($rss_url) && !empty($rss_url)){ ?>
<?php foreach($rss_url as $rss_link){ ?>
	<input placeholder="Facebook Page ID or RSS Feed URL here..." class="widefat" id="<?php echo esc_attr($this->get_field_id( 'rss_url' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'rss_url' )).($rfw_pro?'[]':''); ?>" type="text" value="<?php echo esc_attr( $rss_link ); ?>" />
<?php } ?>
<?php }else{ //pree($this->get_field_id( 'rss_url' )); 
//pree($rss_url);
?>
	<input placeholder="Facebook Page ID or RSS Feed URL here..." class="widefat" id="<?php echo esc_attr($this->get_field_id( 'rss_url' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'rss_url' )).($rfw_pro?'[]':''); ?>" type="text" value="<?php echo (is_array($rss_url)?esc_url(current($rss_url)):esc_url( $rss_url )); ?>" />
<?php } ?>
    	<?php
		$url = 'plugin-install.php?tab=search&s=chameleon';
		$tip = 'Use WordPress Plugin Chameleon for Styles';
		if($rfw_chameleon_installed){
			$url = 'plugins.php?s=chameleon&plugin_status=inactive';
			$tip = 'Activate WordPress Plugin Chameleon';
			if($rfw_chameleon_activated){				
				$url = 'options-general.php?page=rfw_options';
				$tip = 'Select Styles from Chameleon';
			}
		}
		?>
    	</p>
    	</div>
    	
		<div class="rwf-layout rwf-collapsed">   
        
        <strong><?php _e('Optional', 'rss-feed-widget'); ?></strong> 
  		<p>
		<label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php _e( 'Title (optional):', 'rss-feed-widget' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p><label for="<?php echo esc_attr($this->get_field_id( 'content_display' )); ?>"><?php _e( 'Display type:', 'rss-feed-widget' ); ?></label>
        <select id="<?php echo esc_attr($this->get_field_id( 'content_display' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'content_display' )); ?>">
        	<option value="default" <?php echo $content_display=='default'?'selected="selected"':''; ?>><?php _e('Default', 'rss-feed-widget'); ?></option>
            <option value="title_only" <?php echo $content_display=='title_only'?'selected="selected"':''; ?>><?php _e('Title Only', 'rss-feed-widget'); ?></option>
            <option value="text_only" <?php echo $content_display=='text_only'?'selected="selected"':''; ?>><?php _e('Text Only', 'rss-feed-widget'); ?></option>
            <option value="image_only" <?php echo $content_display=='image_only'?'selected="selected"':''; ?>><?php _e('Image Only', 'rss-feed-widget'); ?></option>
        </select></p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'text_to_display' )); ?>"><?php _e( 'Use Text From:', 'rss-feed-widget' ); ?></label>
            <select id="<?php echo esc_attr($this->get_field_id( 'text_to_display' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'text_to_display' )); ?>">
                <option value="default" <?php echo $text_to_display=='default'?'selected="selected"':''; ?>><?php _e('Default', 'rss-feed-widget'); ?></option>
                <option value="content" <?php echo $text_to_display=='content'?'selected="selected"':''; ?>><?php _e('Content', 'rss-feed-widget'); ?></option>
                <option value="description" <?php echo $text_to_display=='description'?'selected="selected"':''; ?>><?php _e('Description', 'rss-feed-widget'); ?></option>
            </select>
        </p>
                
		<p><label for="<?php echo esc_attr($this->get_field_id( 'show_feed_title' )); ?>"><?php _e( 'Display feed title (Yes/No):', 'rss-feed-widget' ); ?></label>
		<input id="<?php echo esc_attr($this->get_field_id( 'show_feed_title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'show_feed_title' )); ?>" type="checkbox" value="true" <?php echo $show_feed_title?'checked="checked"':''; ?>  /></p>
        
        <p><label for="<?php echo esc_attr($this->get_field_id( 'keep_feed_link' )); ?>"><?php _e( 'Keep feed link (Yes/No):', 'rss-feed-widget' ); ?></label>
		<input id="<?php echo esc_attr($this->get_field_id( 'keep_feed_link' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'keep_feed_link' )); ?>" type="checkbox" value="true" <?php echo $keep_feed_link?'checked="checked"':''; ?>  /></p>
        
        <p><label for="<?php echo esc_attr($this->get_field_id( 'feed_words' )); ?>"><?php _e( 'No. of feed words to show:', 'rss-feed-widget' ); ?></label>
		<input id="<?php echo esc_attr($this->get_field_id( 'feed_words' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'feed_words' )); ?>" type="text" value="<?php echo esc_attr(sanitize_text_field($feed_words)); ?>" size="3" /></p>
        
        <p><label for="<?php echo esc_attr($this->get_field_id( 'content_height' )); ?>"><?php _e( 'Widget Height:', 'rss-feed-widget' ); ?></label>
		<input id="<?php echo esc_attr($this->get_field_id( 'content_height' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'content_height' )); ?>" type="text" value="<?php echo esc_attr(sanitize_text_field($content_height)); ?>" size="6" /><small><br />
e.g. 400px<br />
        <?php _e( 'Leave empty for auto.', 'rss-feed-widget' ); ?></small>
        </p>
        <p><label for="<?php echo esc_attr($this->get_field_id( 'content_order' )); ?>"><?php _e( 'Sort order:', 'rss-feed-widget' ); ?></label>
        <select id="<?php echo esc_attr($this->get_field_id( 'content_order' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'content_order' )); ?>">
        	<option value="default" <?php echo $content_order=='default'?'selected="selected"':''; ?>><?php _e('Default', 'rss-feed-widget'); ?></option>
            <option value="aa" <?php echo $content_order=='aa'?'selected="selected"':''; ?>><?php _e('Alphabetical Ascending', 'rss-feed-widget'); ?></option>
            <option value="ad" <?php echo $content_order=='ad'?'selected="selected"':''; ?>><?php _e('Alphabetical Descending', 'rss-feed-widget'); ?></option>
            <option value="date_aa" <?php echo $content_order=='date_aa'?'selected="selected"':''; ?>><?php _e('Date Ascending', 'rss-feed-widget'); ?></option>
            <option value="date_ad" <?php echo $content_order=='date_ad'?'selected="selected"':''; ?>><?php _e('Date Descending', 'rss-feed-widget'); ?></option>
            <option value="rand" <?php echo $content_order=='rand'?'selected="selected"':''; ?>><?php _e('Random', 'rss-feed-widget'); ?></option>
        </select></p>
        
        <p><label for="<?php echo esc_attr($this->get_field_id( 'number' )); ?>"><?php _e( 'Number of feeds to show:', 'rss-feed-widget' ); ?></label>
		<input id="<?php echo esc_attr($this->get_field_id( 'number' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'number' )); ?>" type="text" value="<?php echo esc_attr(sanitize_text_field($number)); ?>" size="3" /></p>
        
		</div>
        
        
        <div class="rwf-advance rwf-collapsed">
        <strong><?php _e('Advanced', 'rss-feed-widget'); ?></strong>
        <p><label for="<?php echo esc_attr($this->get_field_id( 'speed' )); ?>"><?php _e( 'Transition Speed:', 'rss-feed-widget' ); ?></label>
		<input id="<?php echo esc_attr($this->get_field_id( 'speed' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'speed' )); ?>" type="text" value="<?php echo esc_attr(sanitize_text_field($speed)); ?>" size="4" /></p>
        
        <p><label for="<?php echo esc_attr($this->get_field_id( 'list_type' )); ?>"><?php _e( 'List Type:', 'rss-feed-widget' ); ?></label>
		 <select id="<?php echo esc_attr($this->get_field_id( 'list_type' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'list_type' )); ?>">
        	<option value="slider" <?php echo $list_type=='slider'?'selected="selected"':''; ?>>Slider</option>
            <option value="list" <?php echo $list_type=='list'?'selected="selected"':''; ?>>List Only</option>
        </select>        
       </p>
        
		<p><label for="<?php echo esc_attr($this->get_field_id( 'img_size' )); ?>"><?php _e( 'Image Pick:', 'rss-feed-widget' ); ?></label>
		 <select id="<?php echo esc_attr($this->get_field_id( 'img_size' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'img_size' )); ?>">
        	<option value="small" <?php echo $img_size=='small'?'selected="selected"':''; ?>>Default</option>
            <option value="1" <?php echo $img_size==1?'selected="selected"':''; ?>>First Image</option>
           	<option value="2" <?php echo $img_size==2?'selected="selected"':''; ?>>Second Image</option>
            
            <option value="large" <?php echo $img_size=='large'?'selected="selected"':''; ?>>Large Image (Slow Loading)</option>
        </select>        
       </p>
                
        <p><label for="<?php echo esc_attr($this->get_field_id( 'rfw_cache' )); ?>"><?php _e( 'Cache Period:', 'rss-feed-widget' ); ?></label>
		<input id="<?php echo esc_attr($this->get_field_id( 'rfw_cache' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'rfw_cache' )); ?>" type="number" min="0" value="<?php echo esc_attr(sanitize_text_field($rfw_cache)); ?>" placeholder="<?php _e('Enter in seconds', 'rss-feed-widget'); ?>" size="20" /><small><br />
e.g. 7200 seconds = 2 hours<br />
        <?php _e( 'Leave empty or 0 for no cache.', 'rss-feed-widget'); ?></small>
        </p>
        </div>
        
 		<div class="rwf-layout rwf-collapsed">   
        
        <strong><?php _e('Censorship', 'rss-feed-widget'); ?></strong>        
        
        <p><textarea id="<?php echo esc_attr($this->get_field_id( 'rfw_censorship' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'rfw_censorship' )); ?>" placeholder="Enter the words or just initials which you don't want to display from the feeds. Separate them with commas."><?php echo esc_attr(sanitize_textarea_field($rfw_censorship)); ?></textarea><br /><small>Examples: Hate, Harassment or harrass only as initials.</small></p>
        </div>
        
        <div class="rwf-styling rwf-collapsed">
         <strong><?php _e('Styling', 'rss-feed-widget'); ?></strong>
         <p>
        <a class="rwf-styling-link" title="<?php echo esc_attr($tip); ?>" href="<?php echo esc_url($url); ?>" target="_blank"><?php echo esc_attr($tip); ?></a></p>
        </div>

		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		
		global $rfw_pro;
		
		// processes widget options to be saved
		//pree($_POST);
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['rss_url'] = trim(strip_tags($new_instance['rss_url']));
		$instance['number'] = (int) $new_instance['number'];
		$instance['speed'] = (int) $new_instance['speed'];
		$instance['rfw_cache'] = (int) $new_instance['rfw_cache'];
		
		$instance['show_feed_title'] = ($new_instance['show_feed_title']==true);
		$instance['keep_feed_link'] = ($new_instance['keep_feed_link']==true);
		
		$instance['feed_words'] = (int) $new_instance['feed_words'];
		$instance['content_display'] = strip_tags($new_instance['content_display']);
		$instance['content_order'] = strip_tags($new_instance['content_order']);
		$instance['content_height'] = strip_tags($new_instance['content_height']);
		$instance['list_type'] = strip_tags($new_instance['list_type']);
		$instance['img_size'] = strip_tags($new_instance['img_size']);
		$instance['rfw_censorship'] = strip_tags($new_instance['rfw_censorship']);
		$instance['text_to_display'] = strip_tags($new_instance['text_to_display']);
		
		if($rfw_pro)
		$instance = rfw_pro_update( $instance, $new_instance );
		
		return $instance;
		
	}
}
if(!function_exists('rfw_init')){
	function rfw_init(){
		 register_widget( 'rfw_dock' );
		}
	}
	
	function rfw_settings(){
		

		global $rfw_data;		
		//pree($rfw_data);exit;
		add_options_page($rfw_data['Name'].' - Settings', $rfw_data['Name'], 'activate_plugins', 'rfw_options', 'rfw_options_page');
		

	}
		
	function register_rfwsettings() {
		register_setting('rfw_settings_group', 'rfw_rss_image_size');
	}	
	
	function rfw_options_page(){
		include('settings.php');
	}
		
	function rfw_featuredtoRSS($content) {
		$fir_rss_image_size = get_option('rfw_rss_image_size');
		global $post;
		if ( has_post_thumbnail( $post->ID ) ){
			$content = '' . get_the_post_thumbnail( $post->ID, $fir_rss_image_size, array( 'style' => 'float:left; margin:0 15px 15px 0;' ) ) . '' . $content;
		}
		return $content;
	}
	
	function rfw_styles_selection(){
		
		if(current_user_can( 'administrator') && isset($_GET['page']) && $_GET['page']=='rfw_options' && !empty($_POST)){
		

			if (isset($_POST['rfw_style'])){
				if ( 
					! isset( $_POST['rfw_styles'] ) 
					|| ! wp_verify_nonce( $_POST['rfw_styles'], 'rfw_styles_act' ) 
				) {
				
				   _e('Sorry, your nonce did not verify.', 'rss-feed-widget');
				   exit;
				
				} else {
					
					
					update_option('rfw_style', sanitize_text_field($_POST['rfw_style']));
				   // process form data
				}
			
			}
			
				
			if (isset($_POST['rfw_mutes'])){
				//pree($_POST);
				if(
						! isset( $_POST['rfw_mutes_field'] ) 
					|| 
						! wp_verify_nonce( $_POST['rfw_mutes_field'], 'rfw_mutes_action' ) 
				
				) {
				
				   _e('Sorry, your nonce did not verify.', 'rss-feed-widget');
				   exit;
				
				} else {
				
				   update_option('rfw_mutes', sanitize_textarea_field($_POST['rfw_mutes']));
				}	
			}	
			
			
			if (isset($_POST['rfw_sc_ids'])){
				//pree($_POST);
				if(
						! isset( $_POST['rfw_sc_field'] ) 
					|| 
						! wp_verify_nonce( $_POST['rfw_sc_field'], 'rfw_sc_action' ) 
				
				) {
				
				   _e('Sorry, your nonce did not verify.', 'rss-feed-widget');
				   exit;
				
				} else {
					$rfw_sc_ids = sanitize_textarea_field($_POST['rfw_sc_ids']);
					/*$rfw_sc_id_arr = nl2br($rfw_sc_ids);
					$rfw_sc_id_arr = explode('<br />', $rfw_sc_id_arr);
					$rfw_sc_id_arr = array_map('sanitize_key', $rfw_sc_id_arr);
					
					$rfw_sc_ids = implode('-newline-', $rfw_sc_id_arr);
					
					//pree($rfw_sc_ids);
					//exit;
					
					$rfw_sc_ids = sanitize_key($rfw_sc_ids);
					
					$rfw_sc_ids = str_replace('-newline-', '\r\n', $rfw_sc_ids);*/
					
					//pree($rfw_sc_ids);exit;
					
					update_option('rfw_sc_ids', $rfw_sc_ids);
					update_option('rfw_sc_google_api_key', sanitize_rsfw_data($_POST['rfw_sc_google_api_key']));
				   
				}	
			}	
			
			
			if (isset($_POST['rfw_custom_tag_patterns'])){
				//pree($_POST);
				if(
						! isset( $_POST['rfw_settings_field'] ) 
					|| 
						! wp_verify_nonce( $_POST['rfw_settings_field'], 'rfw_settings_action' ) 
				
				) {
				
				   _e('Sorry, your nonce did not verify.', 'rss-feed-widget');
				   exit;
				
				} else {
				
				   update_option('rfw_custom_tag_patterns', sanitize_textarea_field($_POST['rfw_custom_tag_patterns']));
				}	
			}									
			
			if (isset($_POST['rfw_rss_image_size'])){
				//pree($_POST);
				if(
						! isset( $_POST['rfw_settings_field'] ) 
					|| 
						! wp_verify_nonce( $_POST['rfw_settings_field'], 'rfw_settings_action' ) 
				
				) {
				
				   _e('Sorry, your nonce did not verify.', 'rss-feed-widget');
				   exit;
				
				} else {
				
				   update_option('rfw_rss_image_size', sanitize_rsfw_data($_POST['rfw_rss_image_size']));
				}	
			}				
			
		}
		

			
	}
	add_action('admin_init', 'rfw_styles_selection');
	
	function rfw_plugin_links($links) { 
		global $rfw_premium_link, $rfw_pro;
		
		$settings_link = '<a href="options-general.php?page=rfw_options">'.__('Settings', 'rss-feed-widget').'</a>';
		$guide_link_1 = '<a target="_blank" href="https://plugins.svn.wordpress.org/rss-feed-widget/assets/basic-guide.pdf">'.__('Help', 'rss-feed-widget').'</a>';
		$guide_link_2 = '<a target="_blank" href="https://plugins.svn.wordpress.org/rss-feed-widget/assets/styling-guide.pdf">'.__('Styling', 'rss-feed-widget').'</a>';
		
		if($rfw_pro){
			array_unshift($links, $settings_link, $guide_link_2, $guide_link_1); 
		}else{
			 
			$rfw_premium_link = '<a href="'.$rfw_premium_link.'" title="'.__('Go Premium', 'rss-feed-widget').'" target=_blank>'.__('Go Premium', 'rss-feed-widget').'</a>'; 
			array_unshift($links, $settings_link, $rfw_premium_link); 
		
		}
		
		
		return $links; 
	}	
	
	function rfw_clean_yt_links($str){
		
		global $rfw_youtube_videos_number;
		
		$rfw_sc_google_api_key = get_option('rfw_sc_google_api_key', '');
		
		$output = parse_url($str, PHP_URL_QUERY);
		
		if(!empty($output)){
			parse_str($output, $output_2);
			if(!empty($output_2) && isset($output_2['v'])){
				$str = $output_2['v'];
			}
		}
		if(stristr($str, 'https://www.youtube.com/channel/')){
			$str_arr = explode('/channel/', $str);
			if(isset($str_arr[1])){
				$str = $str_arr[1];
			}
		}
		
		if (filter_var($str, FILTER_VALIDATE_URL) === FALSE && $rfw_youtube_videos_number>0) {
			
			
			
			try {
				$youtube_content_uri = 'https://www.googleapis.com/youtube/v3/search?key='.$rfw_sc_google_api_key.'&channelId='.$str.'&part=snippet,id&order=date&maxResults='.$rfw_youtube_videos_number;
			
				//pree($youtube_content_uri);
				
				$youtube_content = @file_get_contents($youtube_content_uri);
				
				$str_arr_updated = array();
				
				if($youtube_content!=''){
					$youtube_content_obj = json_decode($youtube_content);
					if(is_object($youtube_content_obj)){
						$youtube_content_items = $youtube_content_obj->items;
						if(is_array($youtube_content_items)){
							foreach($youtube_content_items as $youtube_content_item){
								$videoId = $youtube_content_item->id->videoId;
								$str_arr_updated[] = $videoId;
							}
						}
					}				
				}
				
				if(!empty($str_arr_updated)){
					$str = implode(',', $str_arr_updated);
				}
				
			}
			catch (Exception $e) {
				//echo $e->getMessage();
			}

			
			//pree($str);
			
			//exit;
			
		}
		
		return $str;
	}
	
	function rfw_youtube_valid_id($id) {
		return preg_match('/^[a-zA-Z0-9_-]{11}$/', $id) > 0;
	}
	
	add_shortcode( 'rfw-youtube-videos', 'rfw_youtube_videos' );
	function rfw_youtube_videos( $atts ) {
		
		$atts['number'] = (isset($atts['number']) && is_numeric($atts['number']) && $atts['number']>0?$atts['number']:3);
		
		global $rfw_footer_scripts, $rfw_youtube_videos_number;
		
		$rfw_youtube_videos_number = $atts['number'];
		
		$common_style = '<style type="text/css">';
		if(isset($atts['fullscreen']) && $atts['fullscreen']=='true'){
			$common_style .= 'body *:not(iframe) {
				width: 100% !important;
				padding: 0 !important;
				margin: 0 !important;
				max-width: 100% !important;
			}';
			$common_style .= 'header, footer, #wpadminbar, .wp_breadcrumb{ display:none !important; } html{ margin:0 !important; }';
		}
		$common_style .= '</style>';
		
		$rfw_footer_scripts['styles'] = $common_style;
		
		$ret = array();
		
		$rfw_sc_ids = get_option('rfw_sc_ids', '');
		$rfw_sc_arr = array();
		if($rfw_sc_ids!=''){
			$rfw_sc_ids = nl2br($rfw_sc_ids);
			//pree($rfw_sc_ids);
			$rfw_sc_arr = explode('<br />', $rfw_sc_ids);
			$rfw_sc_arr = array_filter(array_map('trim', $rfw_sc_arr));
			$rfw_sc_arr = array_map('rfw_clean_yt_links', $rfw_sc_arr);
			//pree($rfw_sc_arr);
			if(!empty($rfw_sc_arr)){
				$item_id_to_remove = array();
				foreach($rfw_sc_arr as $item_id=>$rfw_sc_item){
					$rfw_sc_item_arr = explode(',', $rfw_sc_item);
					if(is_array($rfw_sc_item_arr) && count($rfw_sc_item_arr)>1){				
						//pree($rfw_sc_item_arr);		
						$rfw_sc_arr = array_merge($rfw_sc_arr, $rfw_sc_item_arr);
						$item_id_to_remove[] = $item_id;
						
					}
				}
				if(!empty($item_id_to_remove)){
					foreach($item_id_to_remove as $item_id){
						unset($rfw_sc_arr[$item_id]);
					}
				}
			}
			$rfw_sc_arr = array_filter($rfw_sc_arr, 'strlen');
			//pree($rfw_sc_arr);exit;
			
			$styles = array();
			
			$atts = array_map('esc_attr', $atts);
			
			$styles['wrapper'][] = 'style="';
			$styles['wrapper'][] = (isset($atts['bgcolor'])?'background-color:'.$atts['bgcolor'].';':'');
			$styles['wrapper'][] = '"';
						
			$styles['inner'][] = 'style="';
			$styles['inner'][] = (isset($atts['width'])?'width:'.$atts['width'].';':'');
			$styles['inner'][] = (isset($atts['height'])?'height:'.$atts['height'].';':'');
			$styles['inner'][] = (isset($atts['margin'])?'margin:'.$atts['margin'].';':'');
			$styles['inner'][] = '"';
			
			if(!empty($rfw_sc_arr)){
				//pree($rfw_sc_arr);exit;
				$ret[] = '<div class="rfw-yt-items" '.implode('', $styles['wrapper']).'>';
				foreach($rfw_sc_arr as $sc_item){
					$valid_id = false;
					if(rfw_youtube_valid_id($sc_item) && !is_numeric($sc_item)){
						$valid_id = true;
					}
					if($valid_id){
						$ret[] = '<iframe '.implode('', $styles['inner']).' src="https://www.youtube.com/embed/'.esc_attr($sc_item).'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
					}
				}
				$ret[] = '</div>';
			}
		}
		
		
	 
		return $rfw_footer_scripts['styles'].implode('', $ret);
	}	
	
	function rfw_footer_scripts(){
		global $rfw_footer_scripts;
		if(!empty($rfw_footer_scripts)){
			foreach($rfw_footer_scripts as $scripts){
				echo $scripts;
			}
		}
	}

    function sanitize_rsfw_data( $input ) {

        if(is_array($input)){

            $new_input = array();

            foreach ( $input as $key => $val ) {
                $new_input[ $key ] = (is_array($val)?sanitize_rsfw_data($val):sanitize_text_field( $val ));
            }

        }else{
            $new_input = sanitize_text_field($input);
        }

        if(!is_array($new_input)){

            if(stripos($new_input, '@') && is_email($new_input)){
                $new_input = sanitize_email($new_input);
            }

            if(stripos($new_input, 'http') || wp_http_validate_url($new_input)){
                $new_input = sanitize_url($new_input);
            }

        }


        return $new_input;
    }


    add_action('wp_ajax_rsfw_update_option', 'rsfw_update_option');

        if(!function_exists('rsfw_update_option')){
            function rsfw_update_option(){





                if(isset($_POST['rsfw_update_option_nonce'])){

                    $nonce = $_POST['rsfw_update_option_nonce'];

                    $return = array(
                            'status' => false,

                    );

                    if ( ! wp_verify_nonce( $nonce, 'rsfw_update_options_nonce_action' ) )
                        die (__("Nonce did not veriy.", 'rss-feed-widget'));


                        $rsfw_options = isset($_POST['rsfw_options']) ? $_POST['rsfw_options'] : array();
                        $sanitized_option = sanitize_rsfw_data($rsfw_options);
                        $return['status'] = update_option('rsfw_options', $sanitized_option);





                    echo  json_encode($return);

                }

                wp_die();

            }
        }




    add_action('wp_ajax_rfw_shortcode_form_save', 'rfw_shortcode_form_save');
    add_action('wp_ajax_rfw_delete_short_code', 'rfw_delete_short_code');

    function rfw_shortcode_form_save(){

            $result = array(
                    'status' => false,
                    'shortcode' => '',
            );


            if(!empty($_POST) && isset($_POST['rfw_short_code'])){


                if(!isset($_POST['rfw_shortcode_nonce']) || !wp_verify_nonce($_POST['rfw_shortcode_nonce'], 'rfw_shortcode_nonce_action')){


                    wp_die(__('Sorry, your nonce did not verify.', 'rss-feed-widget'));


                }else{





                    $rfw_short_code = sanitize_rsfw_data($_POST['rfw_short_code']);

                    $rfw_short_code_list = get_option('rfw_short_code_list', array());
                    $short_code = '';



                    if(!empty($rfw_short_code)){

                        $short_code .= '[rfw-feed-widget ';



                        $short_code_part = '';


                        foreach ($rfw_short_code as $name => $value){


                            if(is_array($value)){

                                $value = implode(',', $value);
                            }

                            $short_code_part .= $name.'="'.$value.'" ';
                        }

                        $short_code_part = trim($short_code_part);

                        $short_code .= $short_code_part.']';
                        $result['shortcode'] = $short_code;

                        if(!in_array($short_code, $rfw_short_code_list)){

                            $rfw_short_code_list[] = $short_code;

                        }

                        ob_start();

                        rfw_shortcode_list_table($rfw_short_code_list, $short_code);
                        $short_code_table = ob_get_clean();
                        $result['shortcode'] = $short_code_table;

                    }


                    $result['status'] = update_option('rfw_short_code_settings', $rfw_short_code);
                    $result['new_entry'] = update_option('rfw_short_code_list', $rfw_short_code_list);

                    $short_code = $result['new_entry'] ? $short_code : '';

                    rfw_shortcode_list_table($rfw_short_code_list, $short_code);
                    $short_code_table = ob_get_clean();
                    $result['shortcode'] = $short_code_table;

                }



            }

            wp_send_json($result);

    }

    function rfw_delete_short_code(){

        $result = array(
            'status' => false,
            'shortcode' => '',
        );


        if(!empty($_POST) && isset($_POST['rfw_shortcode_del'])){


            if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rsfw_update_options_nonce_action')){


                wp_die(__('Sorry, your nonce did not verify.', 'rss-feed-widget'));


            }else{


    //                    $rfw_short_code = sanitize_rsfw_data($_POST['rfw_short_code']);
                $rfw_short_code = sanitize_rsfw_data($_POST['rfw_shortcode_del']);
                $rfw_short_code = base64_decode($rfw_short_code);

                $rfw_short_code_list = get_option('rfw_short_code_list', array());
                $key = array_search($rfw_short_code, $rfw_short_code_list) ;

                if ($key !== false) {



                    unset($rfw_short_code_list[$key]);

                }

                $result['status'] = update_option('rfw_short_code_list', $rfw_short_code_list);
                ob_start();

                rfw_shortcode_list_table($rfw_short_code_list);
                $short_code_table = ob_get_clean();
                $result['shortcode'] = $short_code_table;

            }



        }

        wp_send_json($result);

    }

    function rfw_shortcode_list_table($rfw_short_code_list, $new = ''){






        ?>

            <tbody>

        <?php


        if(!empty($rfw_short_code_list)){



            $rfw_short_code_list = array_reverse($rfw_short_code_list);


            foreach ($rfw_short_code_list as $index => $shortcode){

                $badge = $shortcode == $new ? __('New', 'rss-feed-widget') : '';
                $del = __('Remove', 'rss-feed-widget');
                $counter = $index+1;
                $code = base64_encode($shortcode);

                echo "<tr>
                        <td class='text-center' style='vertical-align: middle;'>  <span class='badge badge-primary'>$badge</span> <span class='d-inline-block'>$counter</span></td>
                        <td>$shortcode</td>
                        <td style='vertical-align: middle;'><a href='' data-code='$code' class='btn btn-danger btn-sm'>$del</a></td>                  
                    
                    </tr>";


            }

        }else{

            $alert_no_found = __('No shortcode found', 'rss-feed-widget');


            echo "<tr>
                        <td colspan='3'><div class='alert alert-info text-center'>$alert_no_found</div></td>
                    
                    </tr>";

        }

        ?>


            </tbody>

        <?php


    }

	//add_action('wp_footer', 'rfw_footer_scripts');


	
	if($rfw_pro)
	include_once($rfw_pro_file);	