#!/bin/sh

# Copy the original configuration file to keep as a backup
cp /etc/squid3/squid.conf /etc/squid3/squid.conf.default

# Overwirte orginal configuration file by custom configuration file
cp /vagrant/puphpet/files/custom/squid.conf /etc/squid3/squid.conf

# Restart squid
service squid3 restart

