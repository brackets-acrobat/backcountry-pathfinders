<?php

declare(strict_types=1);

/*
 * English translations.
 * Every new feature adds its keys here AND in fr.php.
 */

return [
    // Navigation
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
    'pilots.since'      => 'member since',

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
    'error.login_failed'     => 'Incorrect email or password.',
    'error.captcha'          => 'Anti-bot check failed, please try again.',
    'error.comment_empty'    => 'The comment cannot be empty.',
    'error.rating_invalid'   => 'Invalid rating (1 to 5).',
    'error.rating_empty'     => 'Choose at least a rating or a difficulty.',
];
