#Setting up dev environment

##Docker 
Install Docker CE: https://www.docker.com/community-edition

Make sure MySQL and Log dirs are created:

`mkdir ~/.docker-kikdev && mkdir ~/.docker-kikdev/mysql && mkdir ~/.docker-kikdev/logs`

and a network is started:

`docker network create kikdev`

###Single website

To set up a complete environment, with:

* The website available on port 443 (https://localhost)
* MySQL available on 3306
* MailHog (to test emails) GUI available on port 8025

Use this command (from the website root):

`PASS=<password> docker-compose -f vendor/kiksaus/kikcms/docker/docker-compose.yml up -d`

Replacing `<password>` with desired password for MySQL

###Multiple websites 
To run multiple websites, with a different port (like: https://localhost:9000) per website:

Run (from the website root) to start MySQL and Mailhog containers: 

`PASS=<password> docker-compose -f vendor/kiksaus/kikcms/docker/docker-compose-services.yml up -d`

once, replacing `<password>` with desired password, and:

`SITE_PORT=<port> docker-compose -f vendor/kiksaus/kikcms/docker/docker-compose-site.yml -p <name> up -d`

per site, where you replace `<port>` with the desired port and `<name>` with a unique name.

###Down 
To take an environment down, run the same command, but replace `up -d` with `down`


##Alternatives
While Docker is recommended, you can also set-up a environment using MAMP or even on 
MacOS itself. Make sure to install the following plugins:

* Phalcon
* ImageMagick
* APCu
* XDebug (optional)

And set up:
* MySQL
* MailHog

#Create a new website
To start building on a new website, we need some database tables. Also some boilerplate code and data will be extremely 
helpful if you're just starting out.

##Boilerplate code
Let's get some boilerplate code, run (from project root):

`git archive --format=tar --remote=git@bitbucket.org:kiksaus/boilerplate.git HEAD | tar xf - && sh createdirs.sh && rm 
createdirs.sh`


##Setting up DB
Let's start with the Database. Use your favorite GUI like (SequalPro, Navicat, Workbench or PHPMyAdmin) and connect to 
the MySQL container with these settings, where `<password>` is the same a you used to setup the MySQL container:

```
Host: localhost  
Port: 3306
User: root
Pass: <password>
```

Now run the sql from `install.sql` which came with your boilerplate code. You can remove this file afterwards.

If you already create a development environment with the steps above, you're good to go.