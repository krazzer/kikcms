<h2>Setting up dev environment</h2>

<h3>Docker</h3> 
<p>Install Docker CE: https://www.docker.com/community-edition</p>

Make sure MySQL and Log dirs are created:

`mkdir ~/.docker-kikdev && mkdir ~/.docker-kikdev/mysql && mkdir ~/.docker-kikdev/logs`

<h4>Complete stack</h4> 

<p>To set up a complete environment, with:</p>

<ul>
<li>The website available on port 443 (https://localhost)</li>
<li>MySQL available on 3306</li>
<li>MailHog (to test emails) GUI available on port 8025
</ul> 

<p>Use this command (from the website root):</p>

`docker stack deploy -c vendor/kiksaus/kikcms/docker-compose.yml kikdev`

<h4>Multiple websites</h4> 
<p>To run multiple websites, with a user specified port per website:</p>

Run (from the website root): 

`docker network create kikdev && docker-compose -f vendor/kiksaus/kikcms/docker/docker-compose-services.yml up -d`

once, and:

`SITE_PORT=[PORT] docker-compose -f vendor/kiksaus/kikcms/docker/docker-compose-site.yml -p [NAME] up -d`

per site, where you replace [PORT] with the desired port and [NAME] with a unique name.

<h4>Down</h4> 
To take an environment down, run the same command, but replace `up -d` with `down`

<h4>Database</h4>

Make sure you have this in your env/config.ini file, replacing [DB_NAME] with your database name:

<pre>
[application]
env = dev
sendmailCommand = /usr/bin/mhsendmail -t --smtp-addr mail:1025
 
[database]
username = root
password = adminkik12
dbname = [DB_NAME] 
host = mysql
</pre>
<h3>Alternatives</h3>
<p>While Docker is recommended, you can also set-up a environment using MAMP or even on 
MacOS itself. Make sure to install the following plugins:
</p>
<ul>
<li>Phalcon</li>
<li>ImageMagick</li>
<li>APCu</li>
<li>XDebug (optional)</li>
</ul>
And set up:
<ul>
<li>MySQL</li>
<li>MailHog</li>
</ul>