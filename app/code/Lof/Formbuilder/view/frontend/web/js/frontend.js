var recaptcha = [];

function myCallBackReCaptcha() {
    if (jQuery(".recaptcha-play").length > 0) {
        jQuery(".recaptcha-play").each(function () {
            var captcha_field_id = jQuery(this).attr("id");
            var sitekey = jQuery(this).data("sitekey");
            var recaptcha_theme = jQuery(this).data("theme");
            var tmp_recaptcha = grecaptcha.render(captcha_field_id, {
                'sitekey': sitekey, //Replace this with your Site key
                'theme': recaptcha_theme
            });
            recaptcha.push(tmp_recaptcha);
        })
    }

}