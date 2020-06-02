# LdapBundle

This bundle adds LDAP functionality for Contao backend and frontend users/groups.

## Requirements

For everything to work you need to install and activate the PHP extension "php-ldap". Without it you can not install and use this Bundle Beside that you need at least the Contao Version 4.9.

## Installation

### Step 1: Install the bundle

You can install the bundle using composer or the contao manager. For composer use the following command:

```bash
$ composer require con4gis/ldap
```

In the contao manager, you can find the bundle under "con4gis/ldap".

### Step 2: Configure the bundle

After the installationy you need to add the files "security.yml" and "services.yml" to your config folder in the root folder of your installation. If the file "config.yml" doesn't exist create it too. Then you need to add the following configurations to these files:

**services.yml:**
```yml
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

In the services.yml you need to change the host to where your LDAP server is available. If you don't use the standard port change it here to (SSL is normally port 636). For the encryption, you can choose between "none" (not recommended), "ssl" and "tls".

**security.yml:**
```yml
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
                provider: [contao.security.backend_user_provider, con4gis_ldap]

        chain_provider_frontend:
            chain:
                provider: [contao.security.frontend_user_provider, con4gis_ldap]        

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

In this configuration file, you need to change a few things. These include the "base_dn", "search_dn", "search_password", "uid_key", "filter" and both "dn_string" at ""contao_backend" and "contao_frontend".

The uid_key needs to contain the attribute which contains the username. Normally it's "uid" or "sAMAccountName" (for Windows AD). You can filter which user can log in with the "filter". This is completely optional. If you don't want this delete the line.

The dn_string can contain two placeholders: "{username}" and "{uid_key}". These placeholders will be replaced with the username of the login request respectively the uid_key you set up earlier.

**config.yml:**
```yml
imports:
    - { resource: security.yml }
    - { resource: services.yml }
```

Here you need to import the newly created files.

After these changes, you need to clear the Symfony cache for everything to work.

### Step 3: Configure the groups

Now you can login to the Backend and configure everything else from there. You can import user and member groups from your LDAP server and select an admin group.
