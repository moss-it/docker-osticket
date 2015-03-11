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
  * Automates initial installation

OSTicket is being served by [nginx](http://wiki.nginx.org/Main) using [PHP-FPM](http://php-fpm.org/) with PHP5.

# Quick Start

Ensure you have a MySQL container running that OSTicket can use to store it's data.

```bash
docker run --name osticket_mysql -d -e MYSQL_ROOT_PASSWORD=secret -e MYSQL_USER=osticket -e MYSQL_PASSWORD=secret -e MYSQL_DATABASE=osticket mysql
```

Now run this image and link the MySQL container.

```bash
docker run --name osticket -d --link osticketysql:mysql -p 8080:80 -e MYSQL_PASSWORD=secret campbellsoftwaresolutions/osticket
```

Wait for the installation to complete then browse to your OSTicket [staff control panel](http://localhost:8080/scp). Login with default admin user & password:

* username: **ostadmin**
* password: **Admin1**

Now configure as required. If you are intending on using this image in production, please make sure you change the
passwords and read the rest of this documentation!

# MySQL connection

You need to link your MySQL container to this image with the alias ```mysql```.

OSTicket requires that the MySQL connection specifies a user with full permissions to the specified database. This is required for the automatic
 database installation.

## Mandatory settings

`MYSQL_PASSWORD`

The password for the specified user used when connecting to the MySQL server.

## Optional settings

The following MySQL connection settings can be set if required but have sensible defaults.

`MYSQL_PREFIX`

The table prefix for this installation. Unlikely you will need to change this unless you are running on a shared
installation. Defaults to 'ost_'.

`MYSQL_DATABASE`

The name of the database to connect to. Defaults to 'osticket'.

`MYSQL_USER`

The user name to use when connecting to the MySQL server. Defaults to 'osticket'.


# Environmental Variables

`INSTALL_SECRET`

Secret string value for OST installation. A random value is generated on start-up and persisted within the container if this is not provided.

*If using in production you should specify this so that re-creating the container does not cause
your installation secret to be lost!*

The remaining environmental variables can be used as a convenience to provide defaults during the automated installation
but most of these settings can be changed through the admin panel if required. These are only used when creating
the initial database.

`INSTALL_NAME`

The name of the helpdesk to create if installing. Defaults to "My Helpdesk".

`INSTALL_EMAIL`

Helpdesk email account. Defaults to 'helpdesk@example.com'

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

