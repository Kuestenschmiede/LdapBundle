# LdapBundle
> [!IMPORTANT]  
> We are not actively developing this con4gis module. Further development orders are possible.

This bundle adds LDAP functionality for Contao backend and frontend users/groups.

## Requirements

For everything to work, you need to install and activate the PHP extension `php-ldap` (`ext-ldap`). Without it, you cannot install and use this bundle.
Additionally, you need:
- PHP 8.2 or higher (PHP 8.2, 8.3, 8.4)
- Contao 5.3 or higher (including Contao 5.7)

## Installation

### Step 1: Install the bundle

You can install the bundle using Composer or the Contao Manager. For Composer use the following command:

```bash
composer require con4gis/ldap
```

In the Contao Manager, you can find the bundle under `con4gis/ldap`.

### Step 2: Configure the bundle

After installation, you need to add the configuration files (e.g. `security.yaml` and `services.yaml`) to your `config/` directory in the root of your Contao installation. If `config/config.yaml` doesn't exist, create it as well. Then add the following configurations:

**services.yaml:**
```yaml
services:
    Symfony\Component\Ldap\Ldap:
        arguments: ['@Symfony\Component\Ldap\Adapter\ExtLdap\Adapter']
    Symfony\Component\Ldap\Adapter\ExtLdap\Adapter:
        arguments:
            -   host: ad.yourldapserver.com
                port: 389
                encryption: tls
                options:
                    protocol_version: 3
                    referrals: false
```

In `services.yaml`, you need to adjust the host to where your LDAP server is available. If you don't use the standard port, change it here too (LDAPS / SSL is normally port 636). For encryption, you can choose between `none` (not recommended), `ssl`, and `tls`.

**security.yaml:**
```yaml
security:
    providers:
        con4gis_ldap:
            ldap:
                service: Symfony\Component\Ldap\Ldap
                base_dn: 'dc=ad,dc=yourldapserver,dc=com'
                search_dn: 'cn=Administrator,cn=Users,dc=ad,dc=yourldapserver,dc=com'
                search_password: '*Password*'
                default_roles: ROLE_USER
                uid_key: uid
                filter: '(objectClass=user)'

        chain_provider_backend:
            chain:
                providers: [contao.security.backend_user_provider, con4gis_ldap]

        chain_provider_frontend:
            chain:
                providers: [contao.security.frontend_user_provider, con4gis_ldap]

    firewalls:  
        contao_backend:
            provider: chain_provider_backend
            form_login_ldap:
                service: Symfony\Component\Ldap\Ldap
                dn_string: 'uid={username},ou=Users,dc=ad,dc=yourldapserver,dc=com'

        contao_frontend:
            provider: chain_provider_frontend     
            form_login_ldap:
                service: Symfony\Component\Ldap\Ldap
                dn_string: 'uid={username},ou=Users,dc=ad,dc=yourldapserver,dc=com'
```

In this configuration file, you need to adjust: `base_dn`, `search_dn`, `search_password`, `uid_key`, `filter`, and both `dn_string` values for `contao_backend` and `contao_frontend`.

The `uid_key` specifies the attribute containing the username (normally `uid` or `sAMAccountName` for Windows Active Directory). You can filter which users can log in with `filter` (optional).

The `dn_string` can contain the placeholders `{username}` and `{uid_key}`. These placeholders will be replaced with the username of the login request or the configured `uid_key`.

**config.yaml:**
```yaml
imports:
    - { resource: security.yaml }
    - { resource: services.yaml }
```

After these changes, clear the Symfony / Contao cache for the configuration to take effect.

### Step 3: Configure groups and settings

Now you can log in to the Contao backend and configure everything under LDAP settings. You can import user and member groups from your LDAP server, set field mappings, and select an admin group.

### Automatic Data Synchronization (Cronjob)

If you enable the **"Update data"** (`updateData`) option in the LDAP settings in the Contao backend, the bundle will automatically synchronize member accounts and data from your LDAP directory.

Under Contao 5.3 and 5.7, this synchronization runs automatically via Contao's built-in cron system (`contao.cronjob` service tag on a minutely interval). Ensure your server executes Contao's cronjob runner periodically (e.g. via system crontab):

```bash
* * * * * /usr/bin/php /path/to/contao/vendor/bin/contao-console contao:cron >/dev/null 2>&1
```
