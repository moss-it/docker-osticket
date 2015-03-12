docker-osticket
===============

# Introduction

Docker image for [OSTicket](http://osticket.com).

This image has been created from the original docker-osticket image by [Petter A. Helset](mailto:petter@helset.eu).

It has a few modifications:

  * Documentation added, hurray!
  * Base OS image fixed to Ubuntu 14.04
  * AJAX issues fixed that made original image unusable
  * Now designed to work with a linked [MySQL](https://registry.hub.docker.com/u/library/mysql/) docker container.
  * Automates configuration file & database installation

OSTicket is being served by [nginx](http://wiki.nginx.org/Main) using [PHP-FPM](http://php-fpm.org/) with PHP5.

The `setup/` directory has been renamed as `setup_hidden/` and the file system permissions deny nginx access to this
location. It was not removed as the setup files are required as part of the automatic configuration during container
start.

# Quick Start

Ensure you have a MySQL container running that OSTicket can use to store its data.

```bash
docker run --name osticket_mysql -d -e MYSQL_ROOT_PASSWORD=secret -e MYSQL_USER=osticket -e MYSQL_PASSWORD=secret -e MYSQL_DATABASE=osticket mysql
```

Now run this image and link the MySQL container.

```bash
docker run --name osticket -d --link osticket_mysql:mysql -p 8080:80 -e MYSQL_PASSWORD=secret campbellsoftwaresolutions/osticket
```

Wait for the installation to complete then browse to your OSTicket staff control panel at `http://localhost:8080/scp`. Login with default admin user & password:

* username: **ostadmin**
* password: **Admin1**

Now configure as required. If you are intending on using this image in production, please make sure you change the
passwords above and read the rest of this documentation!

# MySQL connection

You need to link your MySQL container to this image with the alias name ```mysql```.

OSTicket requires that the MySQL connection specifies a user with full permissions to the specified database. This is required for the automatic
 database installation.

The OSTicket configuration file is re-created from the template every time the container is started. This ensures the
MySQL connection details are always kept up to date automatically, e.g. when linked container IP addresses changes.

## Mandatory settings

`MYSQL_PASSWORD`

The password for the specified user used when connecting to the MySQL server.

## Optional settings

The following MySQL connection settings can be set if required but have sensible defaults.

`MYSQL_PREFIX`

The table prefix for this installation. Unlikely you will need to change this as customisable table prefixes are
designed for shared hosting with only a single MySQL database available. Defaults to 'ost_'.

`MYSQL_DATABASE`

The name of the database to connect to. Defaults to 'osticket'.

`MYSQL_USER`

The user name to use when connecting to the MySQL server. Defaults to 'osticket'.

# Volumes

This image currently supports two volumes. None of these need to used if you do not require them.

`/data/upload/include/plugins`

This is the location where any OSTicket plugins, like [the core plugins](https://github.com/osTicket/core-plugins),
can be placed. Plugins are not included in this image and hence should be maintained in a separate linked Docker
container or the host filesystem.

`/var/log/nginx`

nginx will store it's access & error logs in this location. If you wish to expose these to automatic log
collection tools then you should mount this volume.

# Environmental Variables

`INSTALL_SECRET`

Secret string value for OST installation. A random value is generated on start-up and persisted within the container if this is not provided.

*If using in production you should specify this so that re-creating the container does not cause
your installation secret to be lost!*

`INSTALL_CONFIG`

If you require a configuration file for OSTicket with custom content then you should create one and mount it in your
container as a volume. The placeholders for the MySQL connection must be retained as these will be populated automatically
when the container starts. Set this environmental variable to the fully qualified file name of your custom configuration.
If not specified, the default OSTicket sample configuration file is used.

`INSTALL_EMAIL`

Helpdesk email account. This is placed in the configuration file as well as the DB during installation.
Defaults to 'helpdesk@example.com'

## Database Installation Only

The remaining environmental variables can be used as a convenience to provide defaults during the automated database
installation but most of these settings can be changed through the admin panel if required. These are only used when creating
the initial database.

`INSTALL_NAME`

The name of the helpdesk to create if installing. Defaults to "My Helpdesk".

`INSTALL_FIRSTNAME`

First name of automatically created administrative user. Defaults to 'Admin'.

`INSTALL_LASTNAME`

Last name of automatically created administrative user. Defaults to 'User'.

`INSTALL_EMAIL`

Email address of automatically created administrative user. Defaults to 'admin@example.com'.

`INSTALL_USERNAME`

User name to use for automatically created administrative user. Defaults to 'ostadmin'.

`INSTALL_PASSWORD`

Password to use for automatically created administrative user. Defaults to 'Admin1'.

# Modifications

This image was put together relatively quickly and could probably be improved to meet other use cases.

Please feel free to open an issue if you have any changes you would like to see. All pull requests are also appreciated!

# License

This image and source code is made available under the MIT licence. See the LICENSE file for details.

