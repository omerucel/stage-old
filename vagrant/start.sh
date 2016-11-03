#!/bin/bash
setenforce Permissive
systemctl start nginx
systemctl start php-fpm
systemctl start docker