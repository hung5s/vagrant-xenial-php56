apt-get update

## Section: Nginx
apt-get install -y nginx

sudo cp /tmp/default /etc/nginx/sites-available/
sudo cp /tmp/indition.conf /etc/nginx/snippets/
sudo cp /tmp/www.conf /etc/php/5.6/fpm/pool.d/

sudo openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -keyout /etc/ssl/private/ssl-cert-snakeoil.key -out /etc/ssl/certs/ssl-cert-snakeoil.pem

sudo systemctl restart nginx

## Section: PHP

# remove old PHP
sudo apt-get purge `dpkg -l | grep php| awk '{print $2}' |tr "\n" " "` 

# Add PPA source
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update 

# Install
sudo apt-get install -y php5.6 php5.6-cli php5.6-fpm
sudo apt-get install -y php5.6-mbstring php5.6-mcrypt php5.6-mysql php5.6-xml php5.6-pgsql php5.6-curl php5.6-soap php5.6-zip

## Section Subversion
sudo apt-get install -y subversion
