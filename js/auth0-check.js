let auth0 = null;

const configureClient = async() => {
    auth0 = await createAuth0Client({
        domain: drupalSettings.auth0_domain,
        client_id: drupalSettings.auth0_client_id,
        redirect_uri: window.location.origin
    });
};


// Will run when page finishes loading the DOM.
window.addEventListener('DOMContentLoaded', async() =>  {
    const loginUrl = window.location.origin + "/user/login?returnTo=" + window.location.pathname;
    const logoutUrl = window.location.origin + "/user/logout";

    // Check Drupal login status.
    const notLoggedInToDrupal = document.body.classList.contains('role--anonymous');
    const loggedInToDrupal = document.body.classList.contains('role--authenticated');

    // Setup Auth0 client and look for authentication cookie.
    await configureClient();
    let isAuthenticatedAuth0 = await auth0.isAuthenticated();

    // If we don't have an authentication cookie, reach out to Auth0 to check
    // authentication status with them.
    if (!isAuthenticatedAuth0) {
        try {
            await auth0.getTokenSilently();
        } catch (error) {
            if (error.error !== 'login_required') {
                throw error;
            }
        }
        isAuthenticatedAuth0 = await auth0.isAuthenticated();
    }

    // If they're logged into Auth0 and not into Drupal, we want to get them
    // logged into Drupal.
    if (isAuthenticatedAuth0 && notLoggedInToDrupal) {
        window.location.replace(loginUrl);
    }

    // If they're legged into Drupal but not Auth0, we want to log them out of
    // Drupal.
    if (!isAuthenticatedAuth0 && loggedInToDrupal) {
        window.location.replace(logoutUrl);
    }

    if (window.location.pathname === '/user/logout') {
        let spinner = document.getElementById('logout-page-processing-spinner'),
            message = document.getElementById('logout-page-message');

        setTimeout(function() {
            spinner.style.display = "none";
            message.textContent = message.dataset.successMessage;
        }, 3000)
    }
});