"use strict";!function(a,t){t.behaviors.auth0Lock={attach:function(a,t){var e=t.auth0;if(e){var o={};if(e.lockExtraSettings)try{o=JSON.parse(e.lockExtraSettings)}catch(a){console.error(e.jsonErrorMsg)}o.container=o.container||"auth0-login-form",o.allowSignUp=!(!o.allowSignUp&&!e.showSignup),o.auth=o.auth||{},o.auth.container=o.auth.container||"auth0-login-form",o.auth.redirectUrl=o.auth.redirectUrl||e.callbackURL,o.auth.responseType=o.auth.responseType||"code",o.auth.params=o.auth.params||{},o.auth.params.scope=o.auth.params.scope||e.scopes,o.auth.params.state=e.state,o.languageDictionary=o.languageDictionary||{},o.languageDictionary.title=o.languageDictionary.title||e.formTitle,o.configurationBaseUrl=o.configurationBaseUrl||e.configurationBaseUrl,"TRUE"===e.offlineAccess&&o.auth.params.scope.indexOf("offline_access")<0&&(o.auth.params.scope+=" offline_access"),new Auth0Lock(e.clientId,e.domain,o).show()}}}}(window.jQuery,Drupal);