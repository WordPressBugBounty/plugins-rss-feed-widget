<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
	global $rfw_pro, $rfw_data, $rfw_chameleon_installed, $rfw_chameleon_activated, $rfw_premium_link, $rfw_url;

	$rfw_rss_image_size = get_option('rfw_rss_image_size', 'thumbnail');	
	$rsfw_options = get_option('rsfw_options', array());
	$rfw_mutes = get_option('rfw_mutes', '');
	$rfw_sc_ids = get_option('rfw_sc_ids', '');
	$rfw_sc_google_api_key = get_option('rfw_sc_google_api_key', '');
	
	
?>	
<style type="text/css">
.form-table.noborder td, .form-table.noborder th{ border:none;}
.notice-warning, .update-nag{ display:none; }
.rfw-settings{
	
}
.rfw-settings a.nav-tab{
	cursor:pointer;
}
.rfw-settings > .ecolumns{
	width:50%;
	float:left;
	
}
.rfw-settings > .ecolumns th{
	text-align:center;
}
.rfw-settings > .ecolumns th h5{
	text-align:left;
	margin:0 0 10px 0;
	font-size:14px;
}
.rfw-settings > .ecolumns th pre {
	
	margin: 0;
	background-color: rgba(255,255,0,0.4);
	padding: 6px 20px;
	border-radius: 12px;
	white-space: pre-wrap;
}
</style>
<div class="wrap rfw-settings">
<h2><?php echo $rfw_data['Name'].' ('.$rfw_data['Version'].($rfw_pro?') Pro':')').''; ?> - <?php _e('Settings', 'rss-feed-widget'); ?></h2><br/>





<h2 class="nav-tab-wrapper">
    <a class="nav-tab nav-tab-active rfw-premium-tab"><?php _e("Shortcodes","rss-feed-widget"); ?></a>
    <a class="nav-tab"><?php _e("Skins","rss-feed-widget"); ?></a>
    <a class="nav-tab"><?php _e("Censorship","rss-feed-widget"); ?></a>
    <a class="nav-tab"><?php _e("Image Settings","rss-feed-widget"); ?></a>
    <a class="nav-tab"><?php _e("Youtube Videos","rss-feed-widget"); ?></a>
    <a class="nav-tab"><?php _e("Developer Mode","rss-feed-widget"); ?></a>
</h2>


<div class="nav-tab-content rsfw_in_action settings_section mt-2">

    <div class="modal rfw_load_modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="ajax_load_modalLabel" >
        <div class="modal-dialog" role="document" style="max-width: 50px;">
            <div class="modal-content" style="margin-top: 45vh; width: max-content">

                <img src="<?php echo esc_url($rfw_url.'images/loader.gif'); ?>" style="width: 50px; height: 50px"/>

            </div>
        </div>
    </div>




    <!-- Main Section -->
    <div class="row rfw_url_input_dummy hide mt-3">
        <div class="col-md-8 ">

            <input  placeholder="<?php _e("Facebook Page ID or RSS Feed URL here...", 'rss-feed-widget'); ?>" class="form-control" id="" name="rfw_short_code[rss_url][]" type="text" value="" required>
            <span class="text-danger rfw_remove_input">x</span>

        </div>
    </div>



    <div class="rsfw_folders">

        <div class="row mt-4">
            <div class="col-md-12">
                <button class="button button-secondary rfw-create-shortcode"><?php _e("Create new shortcode", 'rss-feed-widget'); ?></button>
                <button class="button button-secondary rfw-show-shortcodes hide"><?php _e("Show shortcodes", 'rss-feed-widget'); ?></button>
            </div>

            <div class="col-md-12 mt-3 pr-4">
                <div class="alert alert-success hide rfw_created">
                    <?php _e("Shortcode created successfully.", 'rss-feed-widget'); ?>
                </div>
                <div class="alert alert-warning hide rfw_exist">
                    <?php _e("Shortcode already exists with these parameters.", 'rss-feed-widget'); ?>
                </div>
            </div>
        </div>


        <form id="rfw_shortcode_form"  method="post" style="display:none">

        <div class="row">


                <?php wp_nonce_field('rfw_shortcode_nonce_action', 'rfw_shortcode_nonce')?>
                <input type="hidden" name="action" value="rfw_shortcode_form_save">

                <div class="col-md-12">

                        <div class="row rfw_required_row">

                            <div class="col-md-12">

                                <div class="row rfw_shortcode_row hide mt-3">

                                    <div class="col-md-12">

                                        <strong class="w-100">
                                            <?php _e('Shortcode', 'rss-feed-widget'); ?>
                                        </strong>

                                        <ul class="list-group pr-4">
                                            <li class="rfw_shortcode_li list-group-item">


                                            </li>

                                        </ul>

                                    </div>

                                </div>


                                <div class="row mt-3">

                                    <div class="col-md-12">

                                        <strong class="w-100">
                                            <?php _e('Required', 'rss-feed-widget'); ?>
                                        </strong>

                                    </div>

                                </div>

                                <div class="row mt-3">

                                    <div class="col-md-12">
                                        <label for="rfw_short_code_rss_url clearfix">
                                            <?php _e('Enter the RSS feed URL or Facebook Page ID here:', 'rss-feed-widget'); ?>

                                            <a title="<?php _e('Click here for examples', 'rss-feed-widget'); ?>" target="_blank" href="http://www.tutorsloop.net/app/live.php?id=4330772">
                                                <?php _e("(What's this?)", 'rss-feed-widget'); ?>
                                            </a>&nbsp;|&nbsp;
                                            <a target="_blank" href="https://plugins.svn.wordpress.org/rss-feed-widget/assets/styling-guide.pdf" title="<?php _e("Click here for styling guide", 'rss-feed-widget'); ?>">
                                                <?php _e("Styling", 'rss-feed-widget'); ?>
                                            </a> &nbsp;|
                                            &nbsp;<a target="_blank" href="https://plugins.svn.wordpress.org/rss-feed-widget/assets/basic-guide.pdf" title="<?php _e("Click here for guide", 'rss-feed-widget'); ?>">
                                                <?php _e("Help", 'rss-feed-widget'); ?>
                                            </a>

                                        </label>


                                    </div>

                                </div>

                                <div class="row rfw_url_input">
                                    <div class="col-md-8 ">

                                        <input  placeholder="<?php _e("Facebook Page ID or RSS Feed URL here...", 'rss-feed-widget'); ?>" class="form-control" id="rfw_short_code_rss_url" name="rfw_short_code[rss_url][]" type="text" value="" required>

                                    </div>
                                </div>

                                <div class="row mt-3">

                                    <div class="col-md-12">

                                        <a class="btn btn-info btn-sm text-white rfw_url_add_more">
                                            <?php _e("Add More", 'rss-feed-widget'); ?>
                                        </a>

                                        <button type="submit" class="btn btn-danger btn-sm text-white rfw_generate_shortcode">
                                            <?php _e("Generate Shortcode", 'rss-feed-widget'); ?>
                                        </button>

                                    </div>

                                </div>


                            </div>

                        </div>

                        <div class="row rfw_optional_row mt-3 ">

                            <div class="col-md-12">

                                <strong class="w-100">
                                    <?php _e("Optional", 'rss-feed-widget'); ?>
                                </strong>

                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <label for="rfw_short_code_title"><?php _e("Title (optional)", 'rss-feed-widget'); ?></label>
                                    </div>

                                    <div class="col-md-5">
                                        <input class="form-control" id="rfw_short_code_title" name="rfw_short_code[title]" type="text" value="">
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <label for="rfw_short_code_content_display">
                                            <?php _e("Display type", 'rss-feed-widget'); ?>
                                        </label>
                                    </div>

                                    <div class="col-md-5">
                                        <select id="rfw_short_code_content_display" class="form-control" name="rfw_short_code[content_display]">
                                            <option value="default" selected="selected"><?php _e("Default", 'rss-feed-widget'); ?></option>
                                            <option value="title_only"><?php _e("Title Only", 'rss-feed-widget'); ?></option>
                                            <option value="text_only"><?php _e("Text Only", 'rss-feed-widget'); ?></option>
                                            <option value="image_only"><?php _e("Image Only", 'rss-feed-widget'); ?></option>
                                        </select>
                                    </div>
                                </div>
                                

                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <label for="rfw_short_code_text_to_display">
                                            <?php _e("Text to display", 'rss-feed-widget'); ?>
                                        </label>
                                    </div>

                                    <div class="col-md-5">
                                        <select id="rfw_short_code_text_to_display" class="form-control" name="rfw_short_code[text_to_display]">
                                            <option value="default" selected="selected"><?php _e("Default", 'rss-feed-widget'); ?></option>
                                            <option value="content"><?php _e("Content", 'rss-feed-widget'); ?></option>
                                            <option value="description"><?php _e("Description", 'rss-feed-widget'); ?></option>
                                        </select>
                                    </div>
                                </div>
                                

                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <label for="rfw_short_code_feed_words"><?php _e("No. of feed words to show", 'rss-feed-widget'); ?></label>
                                    </div>

                                    <div class="col-md-5">
                                        <input id="rfw_short_code_feed_words" class="form-control" name="rfw_short_code[feed_words]" type="text" value="60" size="3">
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <label for="rfw_short_code_content_height"><?php _e("Widget Height", 'rss-feed-widget'); ?></label>
                                    </div>

                                    <div class="col-md-5">
                                        <input id="rfw_short_code_content_height" class="form-control" name="rfw_short_code[content_height]" type="text" value="" size="6">
                                        <small><?php _e("e.g. 400px Leave empty for auto.", 'rss-feed-widget'); ?></small>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <label for="rfw_short_code_content_order"><?php _e("Sort order", 'rss-feed-widget'); ?></label>
                                    </div>

                                    <div class="col-md-5">
                                        <select id="rfw_short_code_content_order" class="form-control" name="rfw_short_code[content_order]">
                                            <option value="default" selected="selected"><?php _e("Default", 'rss-feed-widget'); ?></option>
                                            <option value="aa"><?php _e("Alphabetical Ascending", 'rss-feed-widget'); ?></option>
                                            <option value="ad"><?php _e("Alphabetical Descending", 'rss-feed-widget'); ?></option>
                                            <option value="date_aa"><?php _e("Date Ascending", 'rss-feed-widget'); ?></option>
                                            <option value="date_ad"><?php _e("Date Descending", 'rss-feed-widget'); ?></option>
                                            <option value="rand"><?php _e("Random", 'rss-feed-widget'); ?></option>
                                        </select>
                                    </div>
                                </div>


                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <label for="rfw_short_code_number"><?php _e("Number of feeds to show", 'rss-feed-widget'); ?></label>
                                    </div>

                                    <div class="col-md-5">
                                        <input id="rfw_short_code_number" class="form-control" name="rfw_short_code[number]" type="number" value="6" size="3"></p>

                                    </div>
                                </div>

                            </div>







    <!--                        <p><label for="rfw_short_code_show_feed_title">Display feed title (Yes/No):</label>-->
    <!--                            <input id="rfw_short_code_show_feed_title" name="rfw_short_code[show_feed_title]" type="checkbox" value="true" checked="checked"></p>-->
    <!---->
    <!--                        <p><label for="rfw_short_code_keep_feed_link">Keep feed link (Yes/No):</label>-->
    <!--                            <input id="rfw_short_code_keep_feed_link" name="rfw_short_code[keep_feed_link]" type="checkbox" value="true" checked="checked"></p>-->


                        </div>

                        <div class="row rfw_advance_row mt-3">

                            <div class="col-md-12">

                                <strong class="w-100">
                                    <?php _e("Advanced", 'rss-feed-widget'); ?>
                                </strong>

                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <label for="rfw_short_code_speed"><?php _e("Transition Speed", 'rss-feed-widget'); ?></label>
                                    </div>

                                    <div class="col-md-5">
                                        <input id="rfw_short_code_speed" class="form-control" name="rfw_short_code[speed]" type="text" value="0" size="4">

                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <label for="rfw_short_code_list_type"><?php _e("List Type", 'rss-feed-widget'); ?></label>
                                    </div>

                                    <div class="col-md-5">
                                        <select id="rfw_short_code_list_type" class="form-control" name="rfw_short_code[list_type]">
                                            <option value="slider" selected="selected"><?php _e("Slider", 'rss-feed-widget'); ?></option>
                                            <option value="list"><?php _e("List Only", 'rss-feed-widget'); ?></option>
                                        </select>
                                    </div>
                                </div>


                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <label for="rfw_short_code_img_size"><?php _e("Image Pick", 'rss-feed-widget'); ?></label>
                                    </div>

                                    <div class="col-md-5">
                                        <select id="rfw_short_code_img_size" class="form-control" name="rfw_short_code[img_size]">
                                            <option value="small" selected="selected"><?php _e("Default", 'rss-feed-widget'); ?></option>
                                            <option value="1"><?php _e("First Image", 'rss-feed-widget'); ?></option>
                                            <option value="2"><?php _e("Second Image", 'rss-feed-widget'); ?></option>

                                            <option value="large"><?php _e("Large Image (Slow Loading)", 'rss-feed-widget'); ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <label for="rfw_short_code_rfw_cache"><?php _e("Cache Period", 'rss-feed-widget'); ?></label>
                                    </div>

                                    <div class="col-md-5">
                                        <input id="rfw_short_code_rfw_cache" class="form-control" name="rfw_short_code[rfw_cache]" type="number" min="0" value="" placeholder="<?php _e('Enter in seconds', 'rss-feed-widget'); ?>" size="20">
                                        <small><?php _e("e.g. 7200 seconds = 2 hours | Leave empty or 0 for no cache.", 'rss-feed-widget'); ?></small>
                                    </div>
                                </div>


                                <div class="row mt-5">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary btn-sm text-white rfw_generate_shortcode">
                                            <?php _e("Generate Shortcode", 'rss-feed-widget'); ?>
                                        </button>
                                    </div>
                                </div>



                            </div>

                        </div>


                    </div>


        </div>

        </form>

        <div class="row mt-3" id="rfw_shortcode_table">

            <div class="col-md-12 pr-4">
            	<?php if(!$rfw_pro): ?>
            	<div class="alert alert-secondary fade in alert-dismissible show">
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true" style="font-size:20px">×</span>
                  </button>    <strong><?php _e("Alert!", 'rss-feed-widget'); ?></strong> <?php _e("Shortcodes will work in premium version only.", 'rss-feed-widget'); ?> <a class="btn btn-warning btn-sm float-right" href="<?php echo esc_url($rfw_premium_link); ?>" target="_blank" title="<?php _e('Click here for Premium Version', 'rss-feed-widget'); ?>"><?php _e("Click here to get the premium version.", 'rss-feed-widget'); ?></a>
                </div>
                <?php endif; ?>

                <table class="table table-striped">
                    <thead class="thead-dark">
                    <tr>
                        <th scope="col" class="text-center" style="width: 14%">#</th>
                        <th scope="col" class="text-center"><?php _e("Shortcode", 'rss-feed-widget'); ?></th>
                        <th scope="col" class="text-center"><?php _e("Action", 'rss-feed-widget'); ?></th>
                    </tr>
                    </thead>
                    <tbody>

                        <?php

                            $rfw_short_code_list = get_option('rfw_short_code_list', array());

                            if(function_exists('rfw_shortcode_list_table')){

                                rfw_shortcode_list_table($rfw_short_code_list);

                            }


                        ?>

                    </tbody>
                </table>



            </div>

        </div>


    </div>

    <!--        Sidebar section-->
    <div class="rsfw_log">
        <div class="row">

            <div class="col-12 text-center">
                <?php _e('Optional', 'rss-feed-widget'); ?>
            </div>


        </div>



        <hr class="bg-warning"/>

        <div class="row nopadding rsfw-options">
            <?php if(!$rfw_pro): ?>
                <a class="btn btn-warning btn-sm mx-auto" href="<?php echo esc_url($rfw_premium_link); ?>" target="_blank" title="<?php _e('Click here for Premium Version', 'rss-feed-widget'); ?>"><?php _e('Go Premium', 'rss-feed-widget'); ?></a>
            <?php endif; ?>


            <div class="alert alert-secondary fade in alert-dismissible d-none mx-auto mt-4" style="width: 90%">
                <button type="button" class="close" data-dismiss="alert" aria-label="<?php _e('Close', 'rss-feed-widget'); ?>">
                    <span aria-hidden="true" style="font-size:20px">×</span>
                </button>    <strong><?php _e('Success!', 'rss-feed-widget'); ?></strong> <?php _e('Options are updated successfully.', 'rss-feed-widget'); ?>
            </div>

            <ul class="col col-md-12 mt-4">



<!--                <li class="premium-features"></li>-->



                <li>
                    <label for="rsfw_options_show_feed_title">
                        <input type="checkbox" name="rsfw_options[show_feed_title]" value="show_feed_title" id="rsfw_options_show_feed_title" <?php echo array_key_exists('show_feed_title', $rsfw_options) ? 'checked' : '' ?> />
                        <?php _e("Display feed title (Yes/No)", 'rss-feed-widget'); ?>
                    </label>

                </li>


                <li>

                    <label for="rsfw_options_keep_feed_link">
                        <input type="checkbox" name="rsfw_options[keep_feed_link]" value="keep_feed_link" id="rsfw_options_keep_feed_link" <?php echo array_key_exists('keep_feed_link', $rsfw_options) ? 'checked' : '' ?> />
                        <?php _e('Keep feed link (Yes/No)', 'rss-feed-widget'); ?>
                    </label>

                </li>




            </ul>

            </li>

            </ul>





            <ul class="col col-md-12 mt-4">
                <li class="promotions"></li>
                <li style="text-align:center;">
                    <a href="https://wordpress.org/plugins/gulri-slider" style="float: unset;" target="_blank" title="<?php _e('Image Slider', 'rss-feed-widget'); ?>"><img src="<?php echo esc_url($rfw_url.'images/gslider.gif'); ?>" /></a>
                </li>
            </ul>


        </div>

    </div>


</div>



<form class="nav-tab-content hide" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
<input type="hidden" name="rfw_tn" value="<?php echo (isset($_GET['t'])?esc_attr($_GET['t']):'0'); ?>" />
<?php wp_nonce_field( 'rfw_styles_act', 'rfw_styles' ); ?>
<table width="100%">
	<tr>
    	<td valign="top">          
        
        <table class="wp-list-table widefat fixed styles">
            	<thead>
                <tr>
                	<th><h5><?php _e('Styles', 'rss-feed-widget'); ?></h5></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                	<td>
                    
                    <?php 
						if($rfw_chameleon_installed){
							if($rfw_chameleon_activated){
								
								global $wpc_assets_loaded, $wpc_dir, $wpc_url;
								$styles = array();
								
								
						
					?>								
                    	
                        
                        
                        <?php
						if(!empty($wpc_assets_loaded) && array_key_exists('rfw', $wpc_assets_loaded) && !empty($wpc_assets_loaded['rfw'])){
							$rfw_style = get_option('rfw_style');
						?>
                        <input type="hidden" name="rfw_style" value="<?php echo esc_attr($rfw_style); ?>" />
                        <ul>
                        <?php
							foreach($wpc_assets_loaded['rfw'] as $name=>$data){
						?>
                        	<li <?php echo ($rfw_style==$name?'class="selected"':''); ?> title="<?php echo esc_attr($name); ?>" data-id="<?php echo esc_attr($name); ?>"><img src="<?php echo esc_url(str_replace($wpc_dir, $wpc_url, $data['images']['screenshot'])); ?>" alt="<?php echo esc_attr($name); ?>" /><span><?php echo esc_attr(ucwords($name)); ?></span></li>
								
						<?php
                            }
						?>
                        </ul>
                        <div style="float:left; width:100%;">
                        <input type="submit" value="Apply Style" class="button-primary" />
                        </div>
                        
                        <?php
						}else{
						?>
                        <?php _e('No styles found.', 'rss-feed-widget'); ?>
						<?php							
						}
						?>
                    	
                        	
                            
                       
					<?php
							}else{
					?>
                    		Wow, you have installed <a href="https://downloads.wordpress.org/plugin/chameleon.zip" target="_blank">Chameleon</a> already. <a href="plugins.php?s=chameleon&plugin_status=inactive" target="_blank">Click here</a> to activate styles for <?php echo esc_attr($rfw_data['Name']); ?>.
                    <?php								
							}
						}else{
					?>
                    		Good news, now you can install <a href="https://downloads.wordpress.org/plugin/chameleon.zip" target="_blank">Chameleon</a> to get awesome styles for for <?php echo esc_attr($rfw_data['Name']); ?>.
                    <?php								
						}
					?>						
                   
                        
                    </td>
                </tr>
                </tbody>
            </table>
            <br/>
            
            <br/>
        
        </td>
    </tr>
</table>

 </form>


<form class="nav-tab-content hide" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
<input type="hidden" name="rfw_tn" value="<?php echo (isset($_GET['t'])?esc_attr($_GET['t']):'0'); ?>" />
<?php wp_nonce_field( 'rfw_mutes_action', 'rfw_mutes_field' ); ?>
<table width="100%">
	<tr>
    	<td valign="top">          
        
        <table class="wp-list-table widefat fixed bookmarks">
            	<thead>
                <tr>
                	<th><h5><?php _e('Filter RSS Feeds', 'rss-feed-widget'); ?></h5></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                	<td>
                    
                    	
                    	<textarea name="rfw_mutes" style="width:100%; height:200px"><?php echo esc_attr($rfw_mutes); ?></textarea>
                        <br />
						<p><?php _e('Enter text/words/sentences which you want to filter or mute. One per line.', 'rss-feed-widget'); ?></p>
                    
					

                        <input type="submit" name="submit-bpu" class="button-primary" value="<?php _e('Save Changes', 'rss-feed-widget') ?>" />
                    
                        
                    </td>
                </tr>
                </tbody>
            </table>
            <br/>
            
            <br/>
        
        </td>
    </tr>
</table>

</form>


<form class="nav-tab-content hide" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
<input type="hidden" name="rfw_tn" value="<?php echo (isset($_GET['t'])?esc_attr($_GET['t']):'0'); ?>" />
<?php wp_nonce_field( 'rfw_settings_action', 'rfw_settings_field' ); ?>
<table width="100%">
	<tr>
    	<td valign="top">          
<table class="wp-list-table widefat fixed bookmarks">
    <thead>
        <tr>
            <th><h5><?php _e('Select Image Size For Rss Feed', 'rss-feed-widget'); ?></h5></th>
        </tr>
    </thead>
    <tbody>
    <tr>
        <td>
			<table class="wp-list-table widefat fixed bookmarks">
            	<thead>
                <tr>
                	<th><h5><?php _e('Instructions', 'rss-feed-widget'); ?></h5></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                	<td>
                    	<ol>
                        	<li><?php _e('Select image size which you want to use in your rss feeds.', 'rss-feed-widget'); ?></li>
                            
                            <li><?php _e('Save Changes', 'rss-feed-widget'); ?></li>

							<li><?php _e("That's it.", 'rss-feed-widget'); ?></li>
                            
                            <li>If you still have any query visit my <a href="<?php echo esc_url($rfw_data['PluginURI']); ?>" target="_blank">website</a> and contact me.</li>
                            
                        </ol>
                        
                    </td>
                </tr>
                </tbody>
            </table>
            			
			<?php settings_fields( 'rfw_settings_group' ); ?>
            <table class="form-table noborder">
                <tr valign="top">
                    <th scope="row"><?php _e('Image Size', 'rss-feed-widget'); ?></th>
                    <td>
                        
                        <?php $image_sizes = get_intermediate_image_sizes(); ?>
                        <select name="rfw_rss_image_size">
                          <?php foreach ($image_sizes as $size_name => $size_attrs): //var_dump($size_attrs);?>
                            <option value="<?php echo esc_attr($size_attrs); ?>" <?php echo $rfw_rss_image_size == $size_attrs?'selected="selected"':''; ?>><?php echo esc_attr(ucwords(str_replace(array('-','_'),' ',$size_attrs))); ?></option>                    
                          <?php endforeach; ?>
                          <option value="full" <?php echo $rfw_rss_image_size == 'full'?'selected="selected"':''; ?>><?php _e('Full Size', 'rss-feed-widget'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">&nbsp;</th>
                    <td bordercolor="red">
                        <input type="submit" name="submit-bpu" class="button-primary" value="<?php _e('Save Changes', 'rss-feed-widget') ?>" />
                    </td>
                </tr>
               
            </table><br />
<br />

            <p><?php echo esc_attr($rfw_data['Description']); ?></p>
        <br />

            
</td>

</tr>
</tbody>
</table>
</td>
    </tr>
</table>

</form>



<form class="nav-tab-content hide" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
<input type="hidden" name="rfw_tn" value="<?php echo (isset($_GET['t'])?esc_attr($_GET['t']):'0'); ?>" />
<?php wp_nonce_field( 'rfw_sc_action', 'rfw_sc_field' ); ?>
<table width="100%">
	<tr>
    	<td valign="top">          
        
        <table class="wp-list-table widefat fixed bookmarks">
            	<thead>
                <tr>
                	<th><h5><?php _e('Create Shortcode Based Page', 'rss-feed-widget'); ?></h5> <pre>[rfw-youtube-videos number="20" height=&quot;300px&quot; width=&quot;32%&quot; bgcolor=&quot;black&quot; fullscreen=&quot;false&quot; margin=&quot;1px 1px 1px 1px&quot;]</pre></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                	<td>
                    	<input style="width:300px;" type="text" name="rfw_sc_google_api_key" value="<?php echo esc_attr($rfw_sc_google_api_key); ?>" placeholder="<?php _e('Google API Key', 'rss-feed-widget'); ?>" /> (<?php _e('Google API Key', 'rss-feed-widget'); ?> - <small><?php _e('Required to fetch videos list from the youtube using Google API', 'rss-feed-widget'); ?></small>)
                    </td>
				</tr>                    
                <tr>
                	<td>
                   
                    	
                    	<textarea name="rfw_sc_ids" style="width:100%; height:200px"><?php echo esc_attr($rfw_sc_ids); ?></textarea>
                        <br />
						<p><?php _e('Enter Youtube Video/Channel URL or ID', 'rss-feed-widget'); ?>. <?php _e('One per line', 'rss-feed-widget'); ?>.</p>
                    
					

                        <input type="submit" name="submit-bpu" class="button-primary" value="<?php _e('Save Changes', 'rss-feed-widget') ?>" />
                    
                        
                    </td>
                </tr>
                </tbody>
            </table>
            <br/>
            
            <br/>
        
        </td>
    </tr>
</table>

</form>


<form class="nav-tab-content hide" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
<input type="hidden" name="rfw_tn" value="<?php echo (isset($_GET['t'])?esc_attr($_GET['t']):'0'); ?>" />
<?php wp_nonce_field( 'rfw_settings_action', 'rfw_settings_field' ); ?>
<table width="100%">
	<tr>
    	<td valign="top">          
        
        <table class="wp-list-table widefat fixed bookmarks">
            	<thead>
                <tr>
                	<th><h5><?php _e('Enter XML tags hierarchy to reach custom tag for images:', 'rss-feed-widget'); ?></h5></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                	<td>
                   
                    	
                    	<textarea name="rfw_custom_tag_patterns" style="width:100%; height:200px"><?php echo esc_attr(get_option('rfw_custom_tag_patterns', '')); ?></textarea>
                        <br />
						<p><?php _e('Example:', 'rss-feed-widget'); ?> <strong>$item->data</strong>['<span style="color:blue">child</span>']['<span style="color:red">someCustomTagNode1</span>']['<span style="color:green">someCustomTagNode2</span>'][<span style="color:brown">0</span>]['<span style="color:tomato">someCustomTagFinalNode</span>']</p><br />
                        <p><?php _e('Instructions:', 'rss-feed-widget'); ?><br />
                        <ol>
                            <li><a href="<?php admin_url('widgets.php'); ?>" target="_blank"><?php _e('Add RSS Feed Widget to sidebars', 'rss-feed-widget'); ?></a></li>
                            <li><a href="<?php echo site_url(); ?>/?rfw-debug" target="_blank" title="<?php _e('Click here to debug', 'rss-feed-widget'); ?>"><?php _e('Click here to debug', 'rss-feed-widget'); ?></a></li>
                            <li><?php _e('Follow the XML tags hierarchy and consider it after item tag and syntax should be as follows:', 'rss-feed-widget'); ?><br />
                            	<h4>child|someCustomTagNode1|someCustomTagNode2|0|someCustomTagFinalNode</h4>
							</li>
                            <li><?php _e('Copy/Paste the custom tag hierarchy in textarea above. Save and try to debug again, you will start getting the custom tag value.', 'rss-feed-widget'); ?></li>
                            <li><a href="https://wordpress.org/support/plugin/rss-feed-widget/" target="_blank"><?php _e('Still need help? Click here to reach the development team.', 'rss-feed-widget'); ?></a></li>                            
                        </ol>
                        </p>

                    
					

                        <input type="submit" <?php echo disabled(!$rfw_pro); ?> name="submit-bpu" class="button-primary" value="<?php echo $rfw_pro?__('Save Changes', 'rss-feed-widget'):__('Get Premium Version', 'rss-feed-widget'); ?>" />

						<?php if(!$rfw_pro): ?>
                        <br /><br />

                        <a href="<?php echo esc_url($rfw_premium_link); ?>" target="_blank"><?php _e('Click here to get premium version', 'rss-feed-widget'); ?></a>
                        <?php endif; ?>
                    
                        
                    </td>
                </tr>
                </tbody>
            </table>
            <br/>
            
            <br/>
        
        </td>
    </tr>
</table>

</form>

</div>        

<script type="text/javascript" language="javascript">
jQuery(document).ready(function($) {
	
	<?php if(isset($_POST['rfw_tn'])): ?>
	
		$('.nav-tab-wrapper .nav-tab:nth-child(<?php echo esc_attr($_POST['rfw_tn'])+1; ?>)').click();
	
	<?php endif; ?>

	
});	
</script>

<style type="text/css">
	#wpfooter{
		display:none;
	}
<?php if(!$rfw_pro): ?>

	#adminmenu li.current a.current {
		font-size: 12px !important;
		font-weight: bold !important;
		padding: 6px 0px 6px 12px !important;
	}
	#adminmenu li.current a.current,
	#adminmenu li.current a.current span:hover{
		color:#9B5C8F;
	}
	#adminmenu li.current a.current:hover,
	#adminmenu li.current a.current span{
		color:#fff;
	}	
<?php endif; ?>
	.woocommerce-message,
	.update-nag{
		display:none;
	}

</style>