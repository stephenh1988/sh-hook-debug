		jQuery(document).ready(function($) {
			$('body').append("<div id='hookdialog'></div>");
			$( "#hooksearch" ).autocomplete({
				source: hooks,
				select: function (event, ui){
					tag = ui.item.value;
					instance = callbacks[tag];
					title = 'Callbacks for <code>'+tag+'</code>';
					html = '<ul>';
					for (j=0; j<instance.length; j++){
						html += '<ul> Call Instance: '+(j+1);
							hooked = instance[j];
							for (i=0; i<hooked.length; i++){
								html += '<li><strong>'+hooked[i].name+'</strong>     <small>prioity:'+hooked[i].priority+' args '+hooked[i].args+' </small></li>';
							}
						html += '</ul>';
					}
					html += '</ul>';
					$('#hookdialog').html(html);
					$('#hookdialog').dialog({ minWidth: 450, zIndex: 9999, title:title });
				}
			});
		});
