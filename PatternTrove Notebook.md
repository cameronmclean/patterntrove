PatternTrove Notebook
--------------------
Getting things to work for protocols, patterns, RDFace, and LabTrove


###### 20140612

OK - so first notes getting RDFaCE into LabTrove

LabTrove uses jquery to call tinyMCE (v3)
So needed to use/reference jquery.tinymce.min.js in the lab trove .php scripts.

Using TinyMCE 4.0.28 jQuery package
jquery.tinymce.min.js was coptied from here - http://www.tinymce.com/download/download.php

Copied the above script into the RDFaCE verstion/folder of tinyMCE, and referenced it in lib/functions/blog.php and docs/page.php - see below


RDFaCE from here - https://bitbucket.org/ali1k/rdface/downloads
used the download repository option... unclear how this differs from the lastest schema.org version - seems functionally equiv...

Modified the **labtrove** software in the following way -(note took ages to find the right files and locations!)

in /lib/functions/blog.php
line 629 - this is the function to add_tiny_mce()
chnaged plugins to be 
`plugins : "{$extraplugins},code,autolink,lists,spellchecker,pagebreak,layer,table,save,insertdatetime,media,searchreplace,preview,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,template,rdface,image,link,emoticons,textcolor",`
had to delete some plugins from v3 that are no longer in ver 4

NOTE - doesnt quite look right in labtrove - need to compare all tinyMCE pugins between labtrove and the RDFaCE demo (index.html).

second - changed docs/page.php to the following at line 42

```
if(isset($jquery['tinymce'])){
			//	$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/tinymce/jquery.tinymce.js\"></script>\n";
						$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/jquery/js/jquery.url.js\"></script>\n";
						$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/tinymce/jquery.cookie.js\"></script>\n";
						$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/tinymce/tinymce.min.js\"></script>\n";
						$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/tinymce/jquery.tinymce.min.js\"></script>\n";
		}
```
NOTE - need also to reference and copy/add .js files for jquery.cookie.js - as RDFaCE depends on this. (original tinyMCE doesnt)

Thats pretty much it at this stage.

Getting labtrove to work was a bit tricky
followed instruction here
http://docs.labtrove.org/2.3/lt/Installing_LabTrove

Setting up trove as a vrtual (local)host required some googling.

Ended up using a ubuntu 12.04 LTS VM, with lamp etc...

put trove at /var/www/labtrove.cam and followed labtrove docs...

This post was useful.
https://www.digitalocean.com/community/tutorials/how-to-set-up-apache-virtual-hosts-on-ubuntu-12-04-lts

needed to `nano /etc/hosts`
and added

```
127.0.0.1       localhost
127.0.1.1       cameron-VirtualBox

#Virtual Hosts
127.0.0.1 labtrove.cam

# The following lines are desirable for IPv6 capable hosts
::1     ip6-localhost ip6-loopback
fe00::0 ip6-localnet
ff00::0 ip6-mcastprefix
ff02::1 ip6-allnodes
ff02::2 ip6-allrouters
```
now pointing browser to labtrove.cam > all good!


also discovered - apache2 logs are at /var/logs/apache2 - for future use...

Also also

Git by default does not track empty folders.
need to add an empty .gitignore into (empty) folders we want tracked/added to the repo.
This was a gotcha for me - cloning the repo and reinstalling patterntrove was missing updates and docs/cache dirs (empty) which caused the app to crash....

Also also also - 

Reinstalling the cloned patterntrove repo - need to
1) copy over config.php
2) set access/own and www-data:www-data for upadate and docs/cache as per the labtrove install docs
and rerun `php /lib/scripts/htaccess.php` !!



