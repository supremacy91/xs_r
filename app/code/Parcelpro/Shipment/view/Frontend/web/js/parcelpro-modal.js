function popup_close() {
    jQuery('#modal').hide();
}

function popup_submit(data) {
    AddressIsParcelshop(data);
}

function AddressIsParcelshop(data) {
    if (data) {
        jQuery("#shipping_method\\:company").val(data.Id);
        jQuery("#shipping_method\\:firstname").val(data.LocationType);
        jQuery("#shipping_method\\:lastname").val(data.Name);
        jQuery("#shipping_method\\:street1").val(data.Street);
        jQuery("#shipping_method\\:street2").val(data.Housenumber + data.HousenumberAdditional);
        jQuery("#shipping_method\\:postcode").val(data.Postalcode);
        jQuery("#shipping_method\\:city").val(data.City);
        //match = /^(.*)\-(\d+)$/.exec(data.LocationTypeId);
        jQuery("#shipping_method\\:country_id").val('NL');
    }
    var firstname = jQuery("#shipping_method\\:firstname").val();
    var lastname = jQuery("#shipping_method\\:lastname").val();

    if (firstname == "DHL ParcelShop") {
        var label = jQuery('label[for="s_method_parcelpro_dhl_parcelshop"]');
        var price = jQuery('span', label);
        var priceHtml = jQuery('<div>').append(price.clone()).html();
        jQuery(label).html(firstname + " " + lastname + " <strong>" + priceHtml + "<strong>");

        return true;
    }
    if (firstname == "PostNL Pakketpunt") {
        var label = jQuery('label[for="s_method_parcelpro_postnl_pakjegemak"]');
        var price = jQuery('span', label);
        var priceHtml = jQuery('<div>').append(price.clone()).html();
        jQuery(label).html(firstname + " " + lastname + " <strong>" + priceHtml + "<strong>");
        return true;
    }
    return false;
}

