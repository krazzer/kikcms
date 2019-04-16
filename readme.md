#What is KikCMS and who is it for?

KikCMS is a CMS and high level framework based on the [Phalcon framework](https://phalconphp.com/).

I created it to allow myself to quickly build websites and webapplications without repeating myself, this includes:

* Login to a backend
* Managing an online file database
* Handling multilingual pages and editing content (CMS functionality)
* Handling pages and templates in the frontend (Frontend CMS functionality)
* Creating editable DataTables (enabling CRUD, search, sort with a few lines of code)
* Forms
* Storing forms data DB
* Resizing images

KikCMS is for anyone who wants to create a website or webapplication <i>fast</i> without any 
constriction to style the frontend, while not having to do much to create a really powerful
backend.

I estimate that the framework can be used for any project where your client would pay you 
between $1.000 and $100.000. In cases lower than 1.000 Wordpress might be a better choice,
and in cases above 100.000 a lower-level framework might be a better choice for more flexibility.
But who knows, I myself haven't reached the upper limit of it's capabilities yet.  

#Required knowledge

You'll need to know PHP 7.1+, MySQL and HTML. Those are the most important. Twig is used for templates so that might come in handy but is very easy to learn.

Other technologies you don't <i>need</i> to know but might come in handy if you do:

* SCSS (Styling, like CSS but more powerful)
* JavaScript (Frontend development)
* Composer (for loading additional packages)
* Git (Version control)
* Gulp (Concat JS/CSS)
* Docker (For dev enviroment, or even production, though I don't do this myself)
* Phalcon (The framework KikCMS is build upon)

#Guide to setting up a new project from scratch

##Boilerplate code
1. Let's get some boilerplate code, run this in the directory you want your project to be: `git archive --format=tar --remote=git@bitbucket.org:kiksaus/boilerplate.git HEAD | tar xf - && sh createdirs.sh`
2. If you haven't already, install [composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos) and make sure the `composer` command works.
3. Run `composer install`
4. Create symlink for cms assets `ln -s ../vendor/kiksaus/kikcms/resources public_html/cmsassets`

##Docker 
1. Install Docker: https://www.docker.com/get-started
2. Start Docker, and make sure it is running
3. Make sure MySQL and Log dirs are created:
`mkdir ~/.docker-kikdev && mkdir ~/.docker-kikdev/mysql && mkdir ~/.docker-kikdev/logs`

4. and a network is started: `docker network create kikdev`
5. Create MySQL and Mailhog containers, replace `<password>` with your desired password: `PASS=<password> docker-compose -f vendor/kiksaus/kikcms/docker/docker-compose-services.yml up -d`

6. Create app container, replacing `<password>` with desired password again, and `<port>` with the desired port (e.g. 9001), and `<name>` with the name of your project:
`SITE_PORT=<port> docker-compose -f vendor/kiksaus/kikcms/docker/docker-compose-site.yml -p <name> up -d`

##Setting up DB
Use your favorite GUI like (SequalPro, Navicat, Workbench or PHPMyAdmin) and connect to 
the MySQL container with these settings, where `<password>` is the same a you used to setup the MySQL container:

```
Host: localhost  
Port: 3306
User: root
Pass: <password>
```

1. Create a database
2. Now run the sql from `install.sql` which came with your boilerplate code. You can remove this file afterwards.
3. Now edit `env/config.ini` and replace `[DB-PASS]` and `[DB-NAME]`

##Test run

Now you're good to go! Test if the app is working in the browser: [https://localhost:9001](https://localhost:9001) (or another port if you chose to)