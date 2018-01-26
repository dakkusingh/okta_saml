/**
 * @file
 */

(function ($, Drupal, drupalSettings) {

    "use strict";

    $(document).ready(function () {
        var settings = drupalSettings.okta_saml_login;
        var orgUrl = settings.okta_org_url;
        var oktaSignIn = new OktaSignIn({baseUrl: orgUrl});
        var redirectUrl = settings.redirect_url;

        oktaSignIn.renderEl(
            { el: '#okta-login-container' },
            function (res) {
                if (res.status === 'SUCCESS') { res.session.setCookieAndRedirect(redirectUrl); }
            }
        );
    });

})(jQuery, Drupal, drupalSettings);