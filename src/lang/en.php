<?php

declare(strict_types=1);

/*
 * English translations.
 * Every new feature adds its keys here AND in fr.php.
 */

return [
    // Navigation
    'nav.map'      => 'Map',
    'nav.login'    => 'Log in',
    'nav.register' => 'Sign up',
    'nav.logout'   => 'Log out',
    'lang.switch'  => 'Switch language',

    // Footer
    'footer.tagline' => 'Backcountry Pathfinders community — MSFS 2024 landing spot surveys',

    // Common labels
    'common.email'    => 'Email',
    'common.password' => 'Password',
    'common.pseudo'   => 'Username',

    // "Map" page
    'page.map.title'  => 'Spots map',
    'map.heading'     => 'Landing spots map',
    'map.intro'       => 'Welcome to the community site skeleton. The interactive map (Leaflet) and the spot pages will be added in the next steps.',
    'map.placeholder' => 'The map will appear here',

    // 404 page
    'page.404.title'   => 'Page not found',
    'error404.heading' => '404 — Page not found',
    'error404.text'    => 'The requested page does not exist (anymore).',
    'error404.back'    => '← Back to the map',

    // Sign up
    'page.register.title'   => 'Sign up',
    'register.heading'      => 'Create an account',
    'register.password_hint' => '(8 characters minimum)',
    'register.confirm'      => 'Confirm password',
    'register.submit'       => 'Create my account',
    'register.have_account' => 'Already registered?',
    'register.login_link'   => 'Log in',

    // Log in
    'page.login.title'    => 'Log in',
    'login.heading'       => 'Log in',
    'login.submit'        => 'Log in',
    'login.no_account'    => 'No account yet?',
    'login.register_link' => 'Create an account',

    // Error messages (validation)
    'error.csrf'             => 'Your session has expired, please try again.',
    'error.pseudo_length'    => 'The username must be between 3 and 40 characters.',
    'error.email_invalid'    => 'Invalid email address.',
    'error.password_short'   => 'The password must be at least 8 characters.',
    'error.password_mismatch' => 'The two passwords do not match.',
    'error.duplicate'        => 'This username or email is already in use.',
    'error.login_failed'     => 'Incorrect email or password.',
];
