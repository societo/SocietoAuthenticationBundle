<?php

/**
 * SocietoAuthenticationBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\AuthenticationBundle;

final class RegistrationEvents
{
    const USERNAME_CHECK = 'societo_auth.registration_username_check';

    const FORM_BUILD = 'societo_auth.registration_form_build';
}
