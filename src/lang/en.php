<?php

declare(strict_types=1);

/*
 * English translations.
 * Every new feature adds its keys here AND in fr.php.
 */

return [
    // Navigation
    'nav.menu'         => 'Menu',
    'nav.user_section' => 'User',
    'nav.admin'        => 'Admin',
    'nav.home'     => 'Home',
    'nav.map'      => 'Map',
    'nav.pilots'   => 'Pilots list',
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

    // Home page
    'page.home.title' => 'Home',
    'home.title'      => 'Backcountry Pathfinders',
    'home.subtitle'   => 'The MSFS 2024 backcountry community: landing-spot surveys, flight sharing and an interactive map.',
    'home.cta_map'    => 'View the map',
    'home.letter_hi'  => "Hey there,",
    'home.letter_p1'  => "I’m Jim ‘Ridge’ Vance. They gave me that nickname because I’ve crossed a fair share of those damn ridges! Throughout my life as a bush pilot, I’ve logged every single wild spot I ever landed on in my logbook. And it sure came in handy when I had to make emergency landings to get myself out of tight spots… But since my logbook was just on paper, I couldn’t share it with other pilots.",
    'home.letter_p2'  => "My young friend Sitka, who knows way more about this stuff than I do, created this website where you can log all your bush flights, take photos, and leave comments on your flights or those of other pilots. Put it to good use.",
    'home.letter_bye' => "Happy flying and fly safe!",

    // "Map" page
    'page.map.title'  => 'Spots map',
    'map.heading'     => 'Landing spots map',
    'map.intro'       => 'Welcome to the community site skeleton. The interactive map (Leaflet) and the spot pages will be added in the next steps.',
    'map.placeholder' => 'The map will appear here',
    'map.loading'     => 'Loading spots…',
    'map.empty'       => 'No spots yet.',
    'map.error'       => 'Could not load spots.',
    'map.surveys'     => 'Surveys',
    'map.rating'      => 'Rating',
    'map.difficulty'  => 'Difficulty',
    'map.altitude'    => 'Altitude',
    'map.country'     => 'Country',
    'map.detail'      => 'View details',
    'map.layer_dark'  => 'Dark',

    // Spot detail page
    'place.untitled'         => 'Unnamed spot',
    'place.back_to_map'      => 'Back to the map',
    'place.back'             => 'Previous page',
    'place.edit'             => 'Edit place',
    'moderation.comment'     => 'Moderated comment',

    // Administration
    'page.admin.title'       => 'Administration',
    'admin.heading'          => 'Recent activity',
    'admin.empty'            => 'No activity yet.',
    'admin.filter_all'          => 'All activity',
    'admin.filter_membre'       => 'New members',
    'admin.filter_vol'          => 'Flights',
    'admin.filter_lieu'         => 'Places',
    'admin.filter_commentaire'  => 'Comments',
    'admin.filter_note'         => 'Ratings',

    // Two-factor authentication (TOTP)
    'page.2fa.title'         => 'Two-factor authentication',
    'page.2fa_setup.title'   => 'Set up two-factor authentication',
    '2fa.heading'            => 'Two-factor authentication',
    '2fa.intro'              => 'Enter the 6-digit code shown by your authenticator app.',
    '2fa.code_label'         => 'Verification code',
    '2fa.submit'             => 'Verify',
    '2fa.back'               => 'Back to login',
    '2fa.setup_heading'      => 'Secure your administrator account',
    '2fa.setup_intro'        => 'Your administrator account requires two-factor authentication. Set it up now:',
    '2fa.step_app'           => 'Install an authenticator app (Google Authenticator, Microsoft Authenticator, FreeOTP…).',
    '2fa.step_scan'          => 'Scan this QR code (or enter the key manually):',
    '2fa.secret_label'       => 'Manual key',
    '2fa.step_confirm'       => 'Enter the generated code to enable two-factor authentication:',
    '2fa.activate'           => 'Enable',
    'error.2fa_invalid'      => 'Incorrect code. Please try again.',
    'admin.by'               => 'by',
    'admin.on'               => 'on',
    'admin.ev_membre'        => 'New member',
    'admin.ev_vol'           => 'New flight',
    'admin.ev_lieu'          => 'New place',
    'admin.ev_commentaire'   => 'New comment',
    'admin.ev_note'          => 'New rating',
    'place.surveys_heading'  => 'Surveys',
    'place.no_surveys'       => 'No survey for this spot yet.',
    'place.comments_heading' => 'Comments',
    'place.no_comments'      => 'No comments yet.',
    'place.deleted_user'     => 'Deleted user',
    'place.pilot_comments_heading' => 'Pilot comments',
    'place.comment_by'       => 'Comment by',
    // Contribution (logged in): rate + comment
    'place.your_review'        => 'Your review',
    'place.rating_hint'        => 'Overall rating and landing difficulty (1 to 5 stars).',
    'place.save_rating'        => 'Save my rating',
    'place.add_comment'        => 'Add a comment',
    'place.comment_placeholder' => 'Share your experience about this spot…',
    'place.comment_submit'     => 'Post',
    'place.login_to_contribute' => 'Log in to rate this spot and leave a comment.',
    'place.comment_added'      => 'Comment posted.',
    'place.rating_saved'       => 'Rating saved.',

    // Survey fields
    'survey.surface'        => 'Surface',
    'survey.condition'      => 'Ground condition',
    'survey.touchdown_speed' => 'Touchdown speed',
    'survey.roll_distance'  => 'Roll-out distance',
    'survey.friction'       => 'Friction',
    'survey.max_slope'      => 'Max slope',
    'survey.elevation_gain' => 'Elevation change',
    'survey.heading'        => 'Landing heading',
    'survey.aircraft'       => 'Aircraft',
    'survey.relief_profile' => 'Relief profile',
    'survey.photo'          => 'Spot photo',

    // Surface types (MSFS)
    'surface.grass'    => 'Grass',
    'surface.dirt'     => 'Dirt',
    'surface.sand'     => 'Sand',
    'surface.snow'     => 'Snow',
    'surface.ice'      => 'Ice',
    'surface.water'    => 'Water',
    'surface.concrete' => 'Concrete',
    'surface.asphalt'  => 'Asphalt',
    'surface.unknown'  => 'Unknown',

    // 404 page
    'page.404.title'   => 'Page not found',
    'error404.heading' => '404 — Page not found',
    'error404.text'    => 'The requested page does not exist (anymore).',
    'error404.back'    => 'Back to the map',

    // Sign up
    'page.register.title'   => 'Sign up',
    'register.heading'      => 'Create an account',
    'register.password_hint' => '(8 chars min.: 1 uppercase, 1 lowercase, 1 digit, 1 special character)',
    'register.confirm'      => 'Confirm password',
    'register.submit'       => 'Create my account',
    'register.have_account' => 'Already registered?',
    'register.login_link'   => 'Log in',

    // Log in
    'page.login.title'    => 'Log in',
    'login.heading'       => 'Log in',
    'login.submit'        => 'Log in',
    'login.remember'      => 'Remember me (1 month)',
    'login.no_account'    => 'No account yet?',
    'login.register_link' => 'Create an account',

    // Navigation (continued)
    'nav.account' => 'My account',
    'nav.my_places' => 'My visited places',
    'nav.my_flights' => 'My flights',

    // My visited places
    'page.my_places.title' => 'My visited places',
    'myplaces.heading'     => 'My visited places',
    'myplaces.empty'       => 'You haven\'t visited any place yet. Land somewhere and send a survey from the app!',
    'myplaces.surveys'     => 'survey(s)',
    'myplaces.last_visit'  => 'last visit:',
    'myplaces.rename'      => 'Edit this place',
    'myplaces.rename_placeholder' => 'Place name',
    'myplaces.comment_label' => 'Your comment about this place',
    'myplaces.comment_placeholder' => 'Your comment about this place (tips, hazards, weather…)',
    'myplaces.rename_save' => 'Save',
    'myplaces.saved'       => 'Your changes have been saved.',

    // Pilots list
    'page.pilots.title' => 'Pilots',
    'pilots.heading'    => 'Pilots',
    'pilots.empty'      => 'No pilots yet.',
    'pilots.places'     => 'place(s)',
    'pilots.surveys'    => 'survey(s)',
    'pilots.flights'    => 'flight(s)',
    'pilots.since'      => 'member since',
    'pilots.back'       => 'Back to the pilots list',

    // Public pilot profile
    'profil.flights_heading' => 'Their flights',
    'profil.no_flights'      => 'This pilot hasn\'t sent any flight yet.',
    'profil.awards'          => 'Awards',

    // My flights
    'page.my_flights.title'   => 'My flights',
    'myflights.heading'       => 'My flights',
    'myflights.empty'         => 'You haven\'t sent any flight yet. Land somewhere and send your flight from the app!',
    'myflights.delete'        => 'Delete this flight',
    'myflights.delete_warn'   => 'The flight and its landings will be deleted. Shared places still visited by other pilots are kept; those left without any survey are removed. This cannot be undone.',
    'myflights.delete_confirm' => 'Confirm deletion',
    'myflights.deleted'       => 'The flight has been deleted.',

    // Flight detail
    'flight.detail_title' => 'Flight details',
    'flight.back'         => 'Previous page',
    'flight.no_route'     => 'Flight',
    'flight.time'         => 'Flight time',
    'flight.landings'     => 'landing(s)',
    'flight.no_landings'  => 'No landing in this flight.',
    'flight.landing_n'    => 'Landing {n}',
    'flight.photo_alt'    => 'Landing screenshot',
    'flight.touch_speed'  => 'Touchdown speed',
    'flight.roll_dist'    => 'Roll',
    'flight.see_place'    => 'View place',

    // Account area / API keys
    'page.account.title'        => 'My account',
    'account.heading'           => 'My account',
    'account.api_section'       => 'API keys',
    'account.api_intro'         => 'Generate an API key and enter it in the desktop application so it can send your surveys to the site.',
    'account.new_key_placeholder' => 'Key name (e.g. "Living room PC")',
    'account.create_key'        => 'Generate a key',
    'account.key_created_warning' => 'Copy this key now: for security reasons, it will never be shown again.',
    'account.copy'              => 'Copy',
    'account.no_keys'           => 'No keys yet.',
    'account.col_label'         => 'Name',
    'account.col_created'       => 'Created',
    'account.col_last_used'     => 'Last used',
    'account.never_used'        => 'Never',
    'account.unnamed'           => 'unnamed',
    'account.delete_key'        => 'Delete',
    'account.delete_confirm'    => 'Delete this key? The application using it will no longer be able to send surveys.',

    // My account — profile / password / avatar
    'account.profile_section'   => 'Profile',
    'account.profile_intro'     => 'Your username is shown on every place and comment you post.',
    'account.save_profile'      => 'Save profile',
    'account.profile_saved'     => 'Profile updated.',
    'account.password_section'  => 'Password',
    'account.new_password'      => 'New password',
    'account.confirm_password'  => 'Confirm password',
    'account.save_password'     => 'Change password',
    'account.password_saved'    => 'Password updated.',
    'account.avatar_section'    => 'Avatar',
    'account.avatar_intro'      => 'PNG or JPG image, 500 × 500 px max, 500 KB max.',
    'account.avatar_choose'     => 'Choose an image',
    'account.save_avatar'       => 'Update avatar',
    'account.avatar_saved'      => 'Avatar updated.',
    'account.no_avatar'         => 'No avatar yet.',

    // Error messages (validation)
    'error.csrf'             => 'Your session has expired, please try again.',
    'error.pseudo_length'    => 'The username must be between 3 and 40 characters.',
    'error.email_invalid'    => 'Invalid email address.',
    'error.password_short'   => 'The password must be at least 8 characters.',
    'error.password_weak'    => 'The password must be at least 8 characters, with at least one uppercase, one lowercase, one digit and one special character.',
    'error.password_mismatch' => 'The two passwords do not match.',
    'error.duplicate'        => 'This username or email is already in use.',
    'error.pseudo_taken'     => 'This username is already taken.',
    'error.email_taken'      => 'This email is already in use.',
    'error.avatar_required'  => 'No image selected.',
    'error.avatar_failed'    => 'Image upload failed.',
    'error.avatar_size'      => 'Image too large (500 KB max).',
    'error.avatar_type'      => 'Format not allowed (PNG or JPG only).',
    'error.avatar_dims'      => 'Image too large (500 × 500 px max).',
    'error.place_name_length' => 'The place name cannot exceed 120 characters.',
    'error.place_not_yours'  => 'You can only rename a place you have visited.',
    'error.flight_not_yours' => 'You can only delete one of your own flights.',
    'error.login_failed'     => 'Incorrect email or password.',
    'error.captcha'          => 'Anti-bot check failed, please try again.',
    'error.comment_empty'    => 'The comment cannot be empty.',
    'error.rating_invalid'   => 'Invalid rating (1 to 5).',
    'error.rating_empty'     => 'Choose at least a rating or a difficulty.',
];
