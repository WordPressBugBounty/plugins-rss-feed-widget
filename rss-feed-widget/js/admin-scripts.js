jQuery(document).ready(function($){
	$('body').on('click', '.rwf-required strong, .rwf-optional strong, .rwf-layout strong, .rwf-settings strong, .rwf-advance strong, .rwf-styling strong', function(){
		$(this).parent().toggleClass('rwf-collapsed');
		//$(this).parent().find('p').toggle();
	});
	$('.wp-list-table.styles ul li').on('click', function(){
		$(this).siblings().removeClass('selected');
		$(this).addClass('selected');
		$('input[name="rfw_style"]').val($(this).data('id'));
	});
	
	
	function parse_query_string(query) {
	  var vars = query.split("&");
	  var query_string = {};
	  for (var i = 0; i < vars.length; i++) {
		var pair = vars[i].split("=");
		// If first entry with this name
		if (typeof query_string[pair[0]] === "undefined") {
		  query_string[pair[0]] = decodeURIComponent(pair[1]);
		  // If second entry with this name
		} else if (typeof query_string[pair[0]] === "string") {
		  var arr = [query_string[pair[0]], decodeURIComponent(pair[1])];
		  query_string[pair[0]] = arr;
		  // If third or later entry with this name
		} else {
		  query_string[pair[0]].push(decodeURIComponent(pair[1]));
		}
	  }
	  return query_string;
	}		
	
	$('.rfw-settings a.nav-tab').click(function(){
		$(this).siblings().removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		$('.nav-tab-content, form:not(.wrap.rfw-settings .nav-tab-content)').hide();
		$('.nav-tab-content').eq($(this).index()).show();
		window.history.replaceState('', '', rfw_obj.this_url+'&t='+$(this).index());	
		$('form input[name="rfw_tn"]').val($(this).index());
		rfw_obj.rfw_tab = $(this).index();
		
	});		
	
	var query = window.location.search.substring(1);
	var qs = parse_query_string(query);	
	if(typeof(qs.t)!='undefined'){
		$('.rfw-settings a.nav-tab').eq(qs.t).click();
		
	}


    $('input[name^="rsfw_options"]').on('change', function(){
        //console.log($(this));
        //console.log($(this).parents().eq(1));


        $(this).parents().eq(1).find('ul').toggleClass('d-none');

        $('.rsfw_log ul li.selected').removeClass('selected');


        var rsfw_option_checked = $('input[name^="rsfw_options"][type="checkbox"]:checked');
        var rsfw_option_text = $('input[name^="rsfw_options"][type="text"]');
        var rsfw_option_select = $('select[name^="rsfw_options"]');

        var rsfw_options_post = {};

        if(rfw_obj.empty_settings){

            rsfw_options_post['rsfw_options_update'] = true;

        }

        if(rsfw_option_select.length > 0 ){
            $.each(rsfw_option_select, function () {

                rsfw_options_post[$(this).data('name')] = $(this).val();

            });
        }


        if(rsfw_option_text.length > 0 ){
            $.each(rsfw_option_text, function () {

                rsfw_options_post[$(this).data('name')] = $(this).val();

            });
        }

        if(rsfw_option_checked.length > 0 ){
            $.each(rsfw_option_checked, function () {

                rsfw_options_post[$(this).val()] = true;

                $(this).parents().eq(1).addClass('selected');

            });
        }



        var data = {

            action : 'rsfw_update_option',
            rsfw_update_option_nonce : rfw_obj.nonce,
            rsfw_options : rsfw_options_post,

        }

        $.post(ajaxurl, data, function(code, response){

            //console.log(response);

            if(response == 'success'){

                $('.rsfw-options .alert').removeClass('d-none').addClass('show');
                setTimeout(function(){
                    $('.rsfw-options .alert').addClass('d-none');
                }, 10000);

            }



        });


    });

    if(rfw_obj.empty_settings){

        $('input[name^="rsfw_options"]').change();

    }


    var exist_alert = $('.alert.rfw_exist');
    var create_alert = $('.alert.rfw_created');
    var rfw_shortcode_table = $('#rfw_shortcode_table');
    var rfw_shortcode_form = $('#rfw_shortcode_form');
    var create_shortcode_btn = $('.rfw-create-shortcode');
    var show_shortcode_btn = $('.rfw-show-shortcodes');
    var rfw_load_modal = $('.rfw_load_modal');

    var form_submit_option = {

        'url' : ajaxurl,

        beforeSubmit : function(form_data){

            var required_for_short_code = ['show_feed_title', 'keep_feed_link'];

            $('.rfw_shortcode_row').hide();

            var error = [];

            $.each(form_data, function(f_key, f_value){

                if(f_value.value == "" && f_value.required == true){

                    error.push(true);

                }

            });

            if($.inArray(true, error) != -1){

            alert(rfw_obj.empty_alert);

            return false;

            }




            $.each(required_for_short_code, function(key, value){

                var rsfw_option_checked = $('input[name^="rsfw_options"][value="'+value+'"]:checked');

                if(rsfw_option_checked.length > 0){

                var form_field = {'name' : 'rfw_short_code['+value+']', 'value' : true};
                form_data.push(form_field);

            }

            });

            rfw_load_modal.show();



        },
        success : function(response, code){

                    rfw_load_modal.hide();


//            $('.rfw_shortcode_li').html(response.shortcode);
//            $('.rfw_shortcode_row').show();

            if(response.new_entry){


                create_alert.show();
                rfw_shortcode_table.find('table tbody').remove();
                rfw_shortcode_table.find('table').append(response.shortcode);
                show_shortcode_btn.click();


                setTimeout(function(){

                create_alert.hide();

                }, 5000);

            }else{

                exist_alert.show();

                setTimeout(function(){

                exist_alert.hide();

                }, 5000);

            }




        },

        error : function(e){

           rfw_load_modal.hide();


        }


    };

    $('.rfw_generate_shortcode').on('click', function(e){

        e.preventDefault();

        $('form#rfw_shortcode_form').ajaxSubmit(form_submit_option);

    });

    $('.rfw_url_add_more').on('click', function(){

        var url_input = $('.rfw_url_input_dummy').clone();
        url_input.removeClass('rfw_url_input_dummy hide');
        url_input.addClass('rfw_url_input');

        $('.rfw_url_input:last').after(url_input);



    });

    $('body').on('click','.rfw_remove_input', function(){

        $(this).parents('.row:first').remove();

    })

    function rfw_set_inputs(){


        var skip_array = ['show_feed_title', 'keep_feed_link']

        $.each(rfw_obj.rfw_short_code_settings, function(key, value){

            if($.inArray(key, skip_array) == -1){


                var input = $('form [name^="rfw_short_code['+key+']"]');

                if($.isArray(value)){

                   $.each(value, function(index, url){

                       if(index == 0){
                           input.val(url);
                       }else{

                           $('.rfw_url_add_more').click();
                           console.log($('.rfw_url_input:last'));
                           $('.rfw_url_input:last input').val(url);

                       }

                   })


                }else{

                    input.val(value);

                }

            }


        });

//        $('.rfw_generate_shortcode').click();
    }

    rfw_set_inputs();

    create_shortcode_btn.on('click', function(){

        $(this).hide();
        show_shortcode_btn.show()
        rfw_shortcode_form.show();
        rfw_shortcode_table.hide();

    })

    show_shortcode_btn.on('click', function(){

        $(this).hide();
        create_shortcode_btn.show();
        rfw_shortcode_form.hide();
        rfw_shortcode_table.show();

    })

    $('body').on('click', '#rfw_shortcode_table tbody tr a.btn-danger', function(e){

        e.preventDefault();

        var confirm_del = confirm(rfw_obj.del_confirmation);

        if(!confirm_del){return;}

        rfw_load_modal.show();

        var shortcode = $(this).data('code');

        var data = {

                action : 'rfw_delete_short_code',
                nonce : rfw_obj.nonce,
                rfw_shortcode_del : shortcode,

        }


        $.post(ajaxurl, data, function(response, code){

            rfw_load_modal.hide();
            if(code == 'success' && response.status){

                rfw_shortcode_table.find('table tbody').remove();
                rfw_shortcode_table.find('table').append(response.shortcode);

            }



        });


    })




});