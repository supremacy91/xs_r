//require(['jquery', 'jquery/ui'], function ($) {
//    'use strict';
//    var dialog = null;
//
//
//    function ParcelProKiezerUrl() {
//        var postcode = null;
//        var street = null;
//
//        if(window.isCustomerLoggedIn) {
//            if (typeof quote.getShippingAddressFromData() !== "undefined"
//                && quote.getShippingAddressFromData() !== null
//                && quote.getSelectedShippingAddress() == 'new-customer-address'
//            ) {
//                postcode = quote.getShippingAddressFromData().postcode;
//                street = quote.getShippingAddressFromData().street;
//            } else {
//                if(quote.getSelectedShippingAddress() != null){
//                    var parts = quote.getSelectedShippingAddress().split('customer-address');
//                    postcode = window.customerData.addresses[ ( parts[1] -1 ) ].postcode;
//                    street = window.customerData.addresses[ ( parts[1] -1 ) ].street[0];
//                }else{
//                    postcode = window.customerData.addresses[0].postcode;
//                    street = window.customerData.addresses[0].street[0];
//                }
//            }
//        }else{
//            postcode = jQuery('input[name=postcode]').val();
//            street = jQuery('input[name^=street]').first().val();
//        }
//
//        var url = "https://login.parcelpro.nl/plugin/afhaalpunt/parcelpro-kiezer.html";
//        url += "?";
//        url += "id=" + window.checkoutConfig.config.gebruikerID;
//        url += "&postcode=" + jQuery('input[name=postcode]').val();
//        url += "&adres=" + jQuery('input[name^=street]').first().val();
//        url += "&origin=" + window.location.protocol + "//" + window.location.hostname;
//        return url;
//    }
//
//    window.addEventListener("message", function (event) {
//        if (event.origin === "https://login.parcelpro.nl") {
//            var msg = event.data;
//            if (msg == "closewindow") {
//                popup_close();
//            } else {
//                AddressIsParcelshop(msg);
//                popup_close();
//            }
//        } else {
//            console.log(event.origin + "!== https://login.parcelpro.nl");
//        }
//    }, false);
//
//    jQuery(document).on('click', "#s_method_parcelpro_dhl_parcelshop", function () {
//        jQuery('#modal').show();
//        jQuery('#afhaalpunt_frame').attr('src', ParcelProKiezerUrl() + '&carrier=DHL');
//    });
//
//    jQuery(document).on('click', "#s_method_parcelpro_postnl_pakjegemak", function () {
//        jQuery('#modal').show();
//        jQuery('#afhaalpunt_frame').attr('src', ParcelProKiezerUrl() + '&carrier=PostNL');
//    });
//
//});
