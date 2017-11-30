(function($) {
	$( document ).ready(function() {
		$("#restaurant_list").on("change", function(){
			var restaurant_id = $(this).val();
			if(restaurant_id) {
				setGetParameter("restaurant", restaurant_id);
			}
		});
        $( function() {
            $( "#spidanet-tabs" ).tabs();
        });
        from = $( "#trnx_from_dt" )
        .datepicker({
            defaultDate: "+1w",
            dateFormat: "dd MM yy",
            changeMonth: true,
            numberOfMonths: 3
        })
        .on( "change", function() {
            to.datepicker( "option", "minDate", getDate( this ) );
        }),
        to = $( "#trnx_to_dt" ).datepicker({
            defaultDate: "+1w",
            dateFormat: "dd MM yy",
            changeMonth: true,
            numberOfMonths: 3
        })
        .on( "change", function() {
            from.datepicker( "option", "maxDate", getDate( this ) );
        });
        var dialog, form,
        desc = $("#send_amt_desc"),
        mtxa = $("#mtxa"),
        sec = $("input[name='_wpnonce']"),
        mobj = $("#mid"),
        pay_btn = $( "#spidanet_pay_popup" ),
        loader = pay_btn.next();
        function sendReq() {
            pay_btn.hide();
            loader.css('visibility', 'visible');
            var req_data =  {
                action : 'spidanet_send_merchant_amt',
                tot_amt : mtxa.val(),
                amt_desc : desc.val(),
                mid : mobj.val(), 
                sec_inf : sec.val()
            };
            $.post(ajaxurl, req_data, function(response) {
                var obj  = jQuery.parseJSON( response );
                loader.css('visibility', 'hidden');
                pay_btn.show();                
                if(obj.msg == "Authorized") {
                    dialog.dialog( "close" );
                    location.reload();
                } else {
                    alert(obj.msg);
                }
            });
            return true;
        }
        dialog = $( "#spidanet-pay-form" ).dialog({
            autoOpen: false,
            height: "auto",
            width: 300,
            modal: true,
            buttons: {
                "Send Money": sendReq,
                Cancel: function() {
                    dialog.dialog( "close" );
                }
            },
            close: function() {
                form[ 0 ].reset();
            }
        });
        form = dialog.find( "form" );
        $( "#spidanet_pay_popup" ).button().on( "click", function() {
            dialog.dialog( "open" );
        });
	});
}(jQuery));

function getDate( element ) {
    var dateFormat = "mm/dd/yy";
    var date;
    try {
        date = $.datepicker.parseDate( dateFormat, element.value );
    } catch( error ) {
        date = null;
    }
    return date;
}

function setGetParameter(paramName, paramValue)
{
    var url = window.location.href;
    var hash = location.hash;
    url = url.replace(hash, '');
    if (url.indexOf(paramName + "=") >= 0)
    {
        var prefix = url.substring(0, url.indexOf(paramName));
        var suffix = url.substring(url.indexOf(paramName));
        suffix = suffix.substring(suffix.indexOf("=") + 1);
        suffix = (suffix.indexOf("&") >= 0) ? suffix.substring(suffix.indexOf("&")) : "";
        url = prefix + paramName + "=" + paramValue + suffix;
    }
    else
    {
    if (url.indexOf("?") < 0)
        url += "?" + paramName + "=" + paramValue;
    else
        url += "&" + paramName + "=" + paramValue;
    }
    window.location.href = url + hash;
}
